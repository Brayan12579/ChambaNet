<?php

namespace App\Policies;

use App\Models\JobOffer;
use App\Models\User;

class JobOfferPolicy
{
    public function update(User $user, JobOffer $job): bool
    {
        return $user->active && ($user->role === 'admin' || ($user->role === 'employer' && $job->company->user_id === $user->id));
    }

    public function delete(User $user, JobOffer $job): bool
    {
        return $this->update($user, $job);
    }
}
