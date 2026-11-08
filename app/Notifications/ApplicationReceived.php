<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class ApplicationReceived extends Notification
{
    public function __construct(public Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, int|string|bool> */
    public function toDatabase(object $notifiable): array
    {
        $job = $this->application->jobOffer;

        return [
            'title' => 'Nueva postulación recibida',
            'message' => 'Recibiste una nueva postulación para «'.$job->title.'». Revisa los candidatos en tu panel.',
            'url' => route('employer.jobs.applicants', $job, absolute: false),
            'application_id' => $this->application->id,
            'job_offer_id' => $job->id,
            'is_demo' => $job->is_demo,
        ];
    }
}
