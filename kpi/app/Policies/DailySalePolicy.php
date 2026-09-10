<?php

namespace App\Policies;

use App\Models\DailySale;
use App\Models\User;

/**
 * Sales entry is a supervisor job (decision K3). Letting staff key in their own
 * sales would let them move their own 40% without a verification step.
 */
class DailySalePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // staff see their own rows; scoping happens in the query
    }

    public function view(User $user, DailySale $sale): bool
    {
        if ($user->business_id !== $sale->business_id) {
            return false;
        }

        return $user->canEvaluate() || $sale->staff->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->canEvaluate();
    }

    public function update(User $user, DailySale $sale): bool
    {
        return $user->canEvaluate() && $user->business_id === $sale->business_id;
    }

    public function delete(User $user, DailySale $sale): bool
    {
        return $user->isAdmin() && $user->business_id === $sale->business_id;
    }
}
