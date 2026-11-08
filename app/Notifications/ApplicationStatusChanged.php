<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
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
        $label = Application::STATUSES[$this->application->status];

        return [
            'title' => 'Tu postulación tiene novedades',
            'message' => 'Tu postulación a «'.$job->title.'» cambió a «'.$label.'». Consulta el seguimiento en tu panel.',
            'url' => route('candidate.applications.index', absolute: false),
            'application_id' => $this->application->id,
            'job_offer_id' => $job->id,
            'status' => $this->application->status,
            'is_demo' => $job->is_demo,
        ];
    }
}
