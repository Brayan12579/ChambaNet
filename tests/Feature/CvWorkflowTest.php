<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class CvWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        // Estas pruebas no se conectan a la base de datos MySQL del proyecto.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('app.url', 'http://localhost');
        $app['url']->forceRootUrl('http://localhost');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
        $this->withoutVite();
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    }

    private function candidateWithCv(): User
    {
        $user = User::factory()->candidate()->create();
        $user->candidateProfile()->create(['cv_path' => $this->pdf()->store('cvs/profiles', 'local')]);

        return $user;
    }

    public function test_candidate_can_delete_only_their_profile_cv_and_keep_application_snapshot(): void
    {
        $user = $this->candidateWithCv();
        $other = $this->candidateWithCv();
        $path = $user->candidateProfile->cv_path;
        $job = JobOffer::factory()->create();
        $this->actingAs($user)->post(route('applications.store', $job))->assertSessionHasNoErrors();
        $application = Application::sole();
        $this->assertNotSame($path, $application->cv_path);
        $this->get(route('candidate.profile.edit'))->assertOk()->assertSee('Eliminar CV')->assertSee('data-confirm=', false);

        $this->delete(route('candidate.cv.destroy'), ['user_id' => $other->id, 'cv_path' => $other->candidateProfile->cv_path])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertNull($user->candidateProfile->fresh()->cv_path);
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists($other->candidateProfile->cv_path);
        Storage::disk('local')->assertExists($application->cv_path);
        $this->get(route('applications.cv', $application))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('candidate.profile.edit'))->assertOk()->assertSee('Subir mi CV en PDF')->assertDontSee('Eliminar CV');
        $this->get(route('candidate.cv'))->assertNotFound();
    }

    public function test_profile_upload_and_replacement_preserve_previous_application(): void
    {
        $user = User::factory()->candidate()->create();
        $payload = ['name' => $user->name, 'email' => $user->email, 'cv' => $this->pdf()];
        $this->actingAs($user)->put(route('candidate.profile.update'), $payload)->assertSessionHasNoErrors();
        $oldPath = $user->candidateProfile()->firstOrFail()->cv_path;
        Storage::disk('local')->assertExists($oldPath);
        $job = JobOffer::factory()->create();
        $this->post(route('applications.store', $job))->assertSessionHasNoErrors();
        $snapshot = Application::sole()->cv_path;

        $payload['cv'] = $this->pdf();
        $this->put(route('candidate.profile.update'), $payload)->assertSessionHasNoErrors();
        $newPath = $user->candidateProfile()->firstOrFail()->cv_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertExists($snapshot);
    }

    public function test_required_offer_rejects_missing_or_missing_on_disk_cv(): void
    {
        $user = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $this->assertTrue($job->fresh()->cv_required);
        $this->actingAs($user)->post(route('applications.store', $job))->assertSessionHasErrors('cv');
        $user->candidateProfile()->create(['cv_path' => 'cvs/profiles/missing.pdf']);
        $this->post(route('applications.store', $job))->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_optional_offer_accepts_no_cv_and_both_inboxes_show_fallback(): void
    {
        $user = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create(['cv_required' => false]);
        $this->actingAs($user)->post(route('applications.store', $job))
            ->assertRedirect(route('candidate.applications.index'))->assertSessionHasNoErrors();
        $application = Application::sole();
        $this->assertNull($application->cv_path);
        $this->get(route('candidate.applications.index'))->assertOk()->assertSee('Sin CV adjunto')->assertDontSee('Ver CV enviado');
        $this->get(route('applications.cv', $application))->assertNotFound();
        $this->actingAs($job->company->user)->get(route('employer.jobs.applicants', $job))
            ->assertOk()->assertSee('Sin CV adjunto')->assertDontSee('Revisar CV en PDF');
    }

    public function test_optional_offer_attaches_an_uploaded_pdf(): void
    {
        $user = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create(['cv_required' => false]);
        $this->actingAs($user)->post(route('applications.store', $job), ['cv' => $this->pdf()])->assertSessionHasNoErrors();
        Storage::disk('local')->assertExists(Application::sole()->cv_path);
    }

    public function test_optional_offer_copies_profile_cv_and_rejects_invalid_upload(): void
    {
        $user = $this->candidateWithCv();
        $job = JobOffer::factory()->create(['cv_required' => false]);
        $this->actingAs($user)->post(route('applications.store', $job), [
            'cv' => UploadedFile::fake()->createWithContent('cv.pdf', 'invalid document'),
        ])->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
        $this->post(route('applications.store', $job))->assertRedirect(route('candidate.applications.index'));
        $snapshot = Application::sole()->cv_path;
        $this->assertNotSame($user->candidateProfile->cv_path, $snapshot);
        Storage::disk('local')->assertExists($snapshot);
    }

    public function test_employer_can_create_and_edit_cv_requirement_but_cannot_edit_another_offer(): void
    {
        $job = JobOffer::factory()->create();
        $employer = $job->company->user;
        $payload = $job->only(['title', 'category_id', 'district_id', 'description', 'requirements', 'conditions', 'schedule', 'contract_type', 'salary_min', 'salary_max', 'vacancies', 'status']);
        $payload['expires_at'] = $job->expires_at->toDateString();
        $payload['cv_required'] = '0';
        $this->actingAs($employer)->put(route('employer.jobs.update', $job), $payload)->assertSessionHasNoErrors();
        $this->assertFalse($job->fresh()->cv_required);
        $payload['cv_required'] = '1';
        $this->put(route('employer.jobs.update', $job), $payload)->assertSessionHasNoErrors();
        $this->assertTrue($job->fresh()->cv_required);
        $payload['cv_required'] = '0';
        $this->post(route('employer.jobs.store'), $payload)->assertSessionHasNoErrors();
        $this->assertFalse(JobOffer::latest('id')->first()->cv_required);
        $this->actingAs(User::factory()->employer()->create())
            ->put(route('employer.jobs.update', $job), $payload)->assertForbidden();
        $this->assertTrue($job->fresh()->cv_required);
    }

    public function test_job_badges_and_frontend_requirement_follow_offer_and_profile(): void
    {
        $user = User::factory()->candidate()->create();
        $job = JobOffer::factory()->create();
        $this->get(route('jobs.index'))->assertOk()->assertSee('CV obligatorio');
        $html = $this->actingAs($user)->get(route('jobs.show', $job))->assertOk()->assertSee('CV obligatorio')->getContent();
        $this->assertMatchesRegularExpression('/id="cv"[^>]*required/', $html);
        $job->update(['cv_required' => false]);
        $html = $this->get(route('jobs.show', $job))->assertOk()->assertSee('CV opcional')->getContent();
        $this->assertDoesNotMatchRegularExpression('/id="cv"[^>]*required/', $html);
        $this->get(route('jobs.index'))->assertOk()->assertSee('CV opcional');
        $user->candidateProfile()->create(['cv_path' => $this->pdf()->store('cvs/profiles', 'local')]);
        $user->unsetRelation('candidateProfile');
        $job->update(['cv_required' => true]);
        $html = $this->get(route('jobs.show', $job))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/id="cv"[^>]*required/', $html);
    }

    public function test_private_documents_are_not_accessible_to_other_users_or_employers(): void
    {
        $user = $this->candidateWithCv();
        $job = JobOffer::factory()->create();
        $this->actingAs($user)->post(route('applications.store', $job))->assertSessionHasNoErrors();
        $application = Application::sole();
        $this->actingAs($job->company->user)->get(route('applications.cv', $application))->assertOk();
        $this->delete(route('candidate.cv.destroy'))->assertForbidden();
        $this->actingAs(User::factory()->candidate()->create())->get(route('applications.cv', $application))->assertForbidden();
        $this->actingAs(User::factory()->employer()->create())->get(route('applications.cv', $application))->assertForbidden();
        Storage::disk('local')->assertExists($user->candidateProfile->cv_path);
    }

    public function test_legacy_shared_cv_is_preserved_when_removing_profile_reference(): void
    {
        $user = $this->candidateWithCv();
        $path = $user->candidateProfile->cv_path;
        $application = Application::factory()->create(['user_id' => $user->id, 'cv_path' => $path]);
        $this->actingAs($user)->delete(route('candidate.cv.destroy'))->assertSessionHas('success');
        $this->assertNull($user->candidateProfile->fresh()->cv_path);
        Storage::disk('local')->assertExists($path);
        $this->get(route('applications.cv', $application))->assertOk();
    }

    public function test_invalid_profile_upload_does_not_replace_current_cv(): void
    {
        $user = $this->candidateWithCv();
        $path = $user->candidateProfile->cv_path;
        $this->actingAs($user)->put(route('candidate.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'cv' => UploadedFile::fake()->createWithContent('cv.pdf', 'invalid document'),
        ])->assertSessionHasErrors('cv');
        $this->assertSame($path, $user->candidateProfile->fresh()->cv_path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_migration_keeps_existing_offers_required_and_preserves_applications(): void
    {
        $application = Application::factory()->create();
        $before = $application->fresh()->getAttributes();
        $migration = require database_path('migrations/2026_11_10_000001_add_cv_requirement_to_job_offers.php');
        $migration->down();
        $migration->up();

        $this->assertSame($before, $application->fresh()->getAttributes());
        $this->assertTrue($application->jobOffer->fresh()->cv_required);
        $this->assertTrue((bool) DB::table('job_offers')->value('cv_required'));
    }
}
