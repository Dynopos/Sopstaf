<?php

namespace App\Policies;

use App\Models\Reward;
use App\Models\User;

class RewardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canEvaluate();
    }

    public function view(User $user, Reward $reward): bool
    {
        if ($user->business_id !== $reward->staff->business_id) {
            return false;
        }

        return $user->canEvaluate() || $reward->staff->user_id === $user->id;
    }

    /** Paying out is management's call, never the evaluator's. */
    public function verify(User $user, Reward $reward): bool
    {
        return $user->isAdmin() && $user->business_id === $reward->staff->business_id;
    }

    public function markPaid(User $user, Reward $reward): bool
    {
        return $this->verify($user, $reward);
    }

    public function withhold(User $user, Reward $reward): bool
    {
        return $this->verify($user, $reward);
    }
}
