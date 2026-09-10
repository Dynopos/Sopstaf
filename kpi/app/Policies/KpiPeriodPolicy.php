<?php

namespace App\Policies;

use App\Models\KpiPeriod;
use App\Models\User;

class KpiPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canEvaluate();
    }

    public function view(User $user, KpiPeriod $period): bool
    {
        return $user->business_id === $period->business_id;
    }

    public function open(User $user): bool
    {
        return $user->isAdmin();
    }

    public function lock(User $user, KpiPeriod $period): bool
    {
        return $user->isAdmin() && $user->business_id === $period->business_id;
    }

    public function reopen(User $user, KpiPeriod $period): bool
    {
        return $this->lock($user, $period);
    }

    public function manageSettings(User $user): bool
    {
        return $user->isAdmin();
    }
}
