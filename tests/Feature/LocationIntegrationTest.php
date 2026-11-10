<?php

namespace Tests\Feature;

use App\Models\JobOffer;
use App\Models\User;
use App\Services\LocationSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LocationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('app.url', 'http://localhost');
        $app['url']->forceRootUrl('http://localhost');
        $app['config']->set('geocoding.cache_store', 'array');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->withoutVite();
        foreach (['ACOS' => 'acos', 'COS' => 'cos', 'SIN' => 'sin', 'RADIANS' => 'deg2rad'] as $sqlName => $function) {
            DB::connection()->getPdo()->sqliteCreateFunction($sqlName, $function, 1);
        }
    }

    private function place(): array
    {
        // Provider fixture for isolated tests; runtime always uses the HTTP service.
        return ['name' => 'Cusco', 'display_name' => 'Cusco, Provincia de Cusco, Cusco, Perú',
            'lat' => '-13.516', 'lon' => '-71.978', 'address' => ['country_code' => 'pe'],
            'boundingbox' => ['-13.7', '-13.2', '-72.2', '-71.5']];
    }

    private function selection(): string
    {
        return app(LocationSearch::class)->encode(['label' => 'Cusco, Perú', 'name' => 'Cusco',
            'latitude' => -13.516, 'longitude' => -71.978, 'bounds' => [-13.7, -13.2, -72.2, -71.5]]);
    }

    private function payload(JobOffer $job): array
    {
        return [...$job->only(['title', 'category_id', 'district_id', 'description', 'requirements', 'conditions', 'schedule', 'contract_type', 'salary_min', 'salary_max', 'vacancies', 'status', 'cv_required']),
            'expires_at' => $job->expires_at->toDateString()];
    }

    public function test_search_uses_http_peru_json_identification_and_cache(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([$this->place()])]);
        $first = $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertOk()
            ->assertJsonPath('cached', false)->assertJsonPath('source', 'Nominatim / OpenStreetMap')
            ->assertJsonPath('results.0.latitude', -13.516)->assertJsonPath('results.0.label', $this->place()['display_name']);
        $this->assertSame(-13.516, app(LocationSearch::class)->decode($first->json('results.0.selection'))['latitude']);
        $this->postJson(route('locations.search'), ['q' => '  CUSCO  '])->assertOk()->assertJsonPath('cached', true);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['countrycodes'] === 'pe' && $request['format'] === 'jsonv2'
            && $request['addressdetails'] === 1 && str_contains($request->header('User-Agent')[0], 'ChambaNet/'));
    }

    public function test_global_throttle_applies_to_different_searches_and_users(): void
    {
        Http::fake(['*' => Http::response([$this->place()])]);
        $this->actingAs(User::factory()->candidate()->create())->postJson(route('locations.search'), ['q' => 'Cusco'])->assertOk();
        $this->actingAs(User::factory()->employer()->create())->postJson(route('locations.search'), ['q' => 'Wanchaq'])
            ->assertStatus(429)->assertHeader('Retry-After');
        Http::assertSentCount(1);
    }

    public function test_provider_lock_prevents_concurrent_outbound_requests(): void
    {
        Http::fake();
        $lock = app(LocationSearch::class)->cache()->lock('geocoding:provider-lock', 20);
        $this->assertTrue($lock->get());
        try {
            $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertStatus(429);
            Http::assertNothingSent();
        } finally {
            $lock->release();
        }
    }

    public function test_empty_results_are_cached_and_search_validation_does_not_call_provider(): void
    {
        Http::fake(['*' => Http::response([])]);
        $this->postJson(route('locations.search'), ['q' => ''])->assertUnprocessable();
        $this->postJson(route('locations.search'), ['q' => ['Cusco']])->assertUnprocessable();
        Http::assertNothingSent();
        $this->postJson(route('locations.search'), ['q' => 'Lugar desconocido'])->assertOk()->assertJsonCount(0, 'results');
        $this->postJson(route('locations.search'), ['q' => 'Lugar desconocido'])->assertOk()->assertJsonPath('cached', true);
        Http::assertSentCount(1);
    }

    public function test_connection_failure_is_sanitized_and_does_not_retry(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertStatus(503)
            ->assertJsonStructure(['message'])->assertDontSee('stack')->assertDontSee('SQLSTATE');
        Http::assertSentCount(1);
    }

    public function test_provider_retry_after_is_respected_globally(): void
    {
        Http::fake(['*' => Http::response([], 429, ['Retry-After' => '120'])]);
        $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertStatus(503)->assertHeader('Retry-After', '120');
        $this->postJson(route('locations.search'), ['q' => 'Lima'])->assertStatus(429);
        Http::assertSentCount(1);
    }

    public function test_foreign_and_invalid_results_are_discarded(): void
    {
        $foreign = $this->place();
        $foreign['address']['country_code'] = 'cl';
        $invalid = $this->place();
        $invalid['lat'] = '999';
        Http::fake(['*' => Http::response([$foreign, $invalid, $this->place()])]);
        $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertOk()->assertJsonCount(1, 'results');
    }

    public function test_malformed_response_returns_clear_error(): void
    {
        Http::fake(['*' => Http::response(['error' => 'invalid'])]);
        $this->postJson(route('locations.search'), ['q' => 'Cusco'])->assertStatus(503)->assertJsonStructure(['message']);
    }

    public function test_expired_or_invalid_search_selection_falls_back_to_manual_search(): void
    {
        JobOffer::factory()->create(['title' => 'Trabajo en Cusco', 'location_name' => 'Cusco, Perú']);
        $this->get(route('jobs.index', ['place' => 'invalid', 'location' => 'Cusco']))->assertOk()
            ->assertSee('Trabajo en Cusco')->assertSee('La selección de ubicación venció');
    }

    public function test_employer_creates_and_edits_selected_location_without_trusting_raw_coordinates(): void
    {
        $job = JobOffer::factory()->create();
        $data = [...$this->payload($job), 'district_id' => null, 'location_selection' => $this->selection(), 'location_name' => 'Cusco',
            'work_mode' => 'hybrid', 'latitude' => 99, 'longitude' => 99];
        $this->actingAs($job->company->user)->post(route('employer.jobs.store'), $data)->assertSessionHasNoErrors();
        $created = JobOffer::latest('id')->first();
        $this->assertSame('Cusco, Perú', $created->location_name);
        $this->assertSame(-13.516, $created->latitude);
        $this->assertSame('hybrid', $created->work_mode);
        $this->assertNull($created->district_id);
        $this->get(route('jobs.show', $created))->assertOk()->assertSee('Cusco, Perú');
        $data['location_selection'] = '';
        $data['location_name'] = 'Centro de trabajo, Arequipa';
        $this->put(route('employer.jobs.update', $created), $data)->assertSessionHasNoErrors();
        $this->assertNull($created->fresh()->latitude);
        $this->assertSame('Centro de trabajo, Arequipa', $created->fresh()->location_name);
        $data['work_mode'] = 'remote';
        $data['location_selection'] = $this->selection();
        $data['location_name'] = '';
        $this->put(route('employer.jobs.update', $created), $data)->assertSessionHasNoErrors();
        $this->assertSame('Remoto', $created->fresh()->location_label);
        $this->assertNull($created->fresh()->longitude);
    }

    public function test_invalid_location_token_does_not_modify_an_offer(): void
    {
        $job = JobOffer::factory()->create();
        $before = $job->fresh()->getAttributes();
        $this->actingAs($job->company->user)->put(route('employer.jobs.update', $job), [
            ...$this->payload($job), 'location_name' => 'Cusco', 'location_selection' => 'tampered', 'work_mode' => 'onsite',
        ])->assertSessionHasErrors('location_name');
        $this->assertSame($before, $job->fresh()->getAttributes());
    }

    public function test_geographic_filter_distance_and_existing_filters_use_registered_offers(): void
    {
        $near = JobOffer::factory()->create(['title' => 'Oferta cercana', 'location_name' => 'Wanchaq, Cusco', 'latitude' => -13.52, 'longitude' => -71.98, 'salary_min' => 1800, 'salary_max' => 2000]);
        JobOffer::factory()->create(['title' => 'Oferta lejana', 'latitude' => -12.04, 'longitude' => -77.03, 'location_name' => 'Lima']);
        JobOffer::factory()->create(['title' => 'Oferta borrador', 'latitude' => -13.52, 'longitude' => -71.98, 'status' => 'draft']);
        $this->get(route('jobs.index', ['place' => $this->selection(), 'location' => 'Cusco', 'sort' => 'distance']))
            ->assertOk()->assertSee('Oferta cercana')->assertDontSee('Oferta lejana')->assertDontSee('Oferta borrador')
            ->assertSee('Aprox.')->assertViewHas('mapMarkers', fn ($markers) => $markers->count() === 1);
        $this->get(route('jobs.index', ['location' => 'Wanchaq', 'category' => $near->category_id, 'salary_min' => 1700, 'q' => 'cercana']))
            ->assertOk()->assertSee('Oferta cercana')->assertDontSee('Oferta lejana');
        $this->get(route('jobs.index', ['salary_min' => 90000]))->assertOk()->assertViewHas('jobs', fn ($jobs) => $jobs->total() === 0);
    }

    public function test_map_excludes_private_expired_demo_remote_and_unlocated_offers(): void
    {
        JobOffer::factory()->create(['latitude' => -13.52, 'longitude' => -71.98]);
        JobOffer::factory()->create(['latitude' => -13.53, 'longitude' => -71.99, 'work_mode' => 'remote']);
        JobOffer::factory()->create(['latitude' => -13.54, 'longitude' => -71.97, 'is_demo' => true]);
        JobOffer::factory()->create(['latitude' => -13.54, 'longitude' => -71.97, 'expires_at' => today()->subDay()]);
        JobOffer::factory()->create(['latitude' => -13.54, 'longitude' => -71.97, 'status' => 'draft']);
        JobOffer::factory()->create(['title' => 'Oferta antigua sin coordenadas']);
        $this->get(route('jobs.index'))->assertOk()->assertSee('Oferta antigua sin coordenadas')
            ->assertSee('Remoto')->assertViewHas('mapMarkers', fn ($markers) => $markers->count() === 1
                && array_keys($markers->first()) === ['latitude', 'longitude', 'title', 'company', 'location', 'url']);
    }

    public function test_nearby_uses_transient_approximate_session_bound_coordinates(): void
    {
        JobOffer::factory()->create(['title' => 'Empleo cercano', 'latitude' => -13.52, 'longitude' => -71.98]);
        JobOffer::factory()->create(['title' => 'Empleo en Lima', 'latitude' => -12.04, 'longitude' => -77.03]);
        $response = $this->postJson(route('locations.nearby'), ['latitude' => -13.516789, 'longitude' => -71.978654])
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $token = $response->json('nearby');
        $this->withCookie(config('session.cookie'), session()->getId());
        $origin = Cache::store('array')->get('geocoding:origin:'.$token);
        $this->assertSame(-13.52, $origin['latitude']);
        $this->assertSame(-71.98, $origin['longitude']);
        $this->get(route('jobs.index', ['nearby' => $token, 'radius' => 25, 'sort' => 'distance']))
            ->assertOk()->assertSee('Empleo cercano')->assertDontSee('Empleo en Lima');
        $this->travel(11)->minutes();
        $this->get(route('jobs.index', ['nearby' => $token, 'sort' => 'distance']))
            ->assertOk()->assertSee('Tu zona temporal venció');
    }

    public function test_three_roles_keep_their_permissions_and_admin_can_save_location(): void
    {
        $job = JobOffer::factory()->create();
        $data = [...$this->payload($job), 'work_mode' => 'onsite', 'location_selection' => $this->selection(), 'location_name' => 'Cusco'];
        $this->actingAs(User::factory()->candidate()->create())->put(route('employer.jobs.update', $job), $data)->assertForbidden();
        $this->actingAs(User::factory()->employer()->create())->put(route('employer.jobs.update', $job), $data)->assertForbidden();
        $this->assertNull($job->fresh()->latitude);
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.content.update', ['section' => 'jobs', 'record' => $job->id]), [...$data, 'company_id' => $job->company_id])
            ->assertSessionHasNoErrors();
        $this->assertSame(-13.516, $job->fresh()->latitude);
        $this->actingAs($job->company->user)->get(route('employer.jobs.edit', $job))->assertOk()->assertSee('data-location-picker', false);
    }
}
