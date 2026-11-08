<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\User;
use App\Notifications\ApplicationReceived;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['filter' => ['nullable', Rule::in(['unread'])]]);
        $filter = (string) ($validated['filter'] ?? '');
        $user = $request->user();
        $notifications = $user->notifications()
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()->orderByDesc('id')->paginate(20)->withQueryString();
        $notifications->through(function (DatabaseNotification $notification) use ($user): DatabaseNotification {
            $notification->setAttribute('action_url', $this->actionUrl($user, $notification));

            return $notification;
        });

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'filter' => $filter,
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return redirect()->route('notifications.index')->with('status', 'Notificación marcada como leída.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now(), 'updated_at' => now()]);

        return redirect()->route('notifications.index')->with('status', 'Todas tus notificaciones están al día.');
    }

    private function actionUrl(User $user, DatabaseNotification $notification): ?string
    {
        if ($notification->type === ApplicationStatusChanged::class && $user->role === 'candidate') {
            return route('candidate.applications.index', absolute: false);
        }
        if ($notification->type === ApplicationReceived::class && $user->role === 'employer') {
            $job = JobOffer::whereKey((int) ($notification->data['job_offer_id'] ?? 0))
                ->whereHas('company', fn ($query) => $query->where('user_id', $user->id))->first();

            return $job ? route('employer.jobs.applicants', $job, absolute: false) : null;
        }

        return null;
    }
}
