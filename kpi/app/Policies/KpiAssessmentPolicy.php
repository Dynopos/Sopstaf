<?php

namespace App\Policies;

use App\Models\KpiAssessment;
use App\Models\Staff;
use App\Models\User;

/**
 * Two rules here are absolute, whatever the interface shows:
 * staff never edit scores, and the person who scored an assessment is never the
 * person who approves it.
 */
class KpiAssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canEvaluate();
    }

    public function view(User $user, KpiAssessment $assessment): bool
    {
        if (! $this->sameBusiness($user, $assessment)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            return $this->supervises($user, $assessment->staff);
        }

        // Staff see their own record and nobody else's.
        return $assessment->staff->user_id === $user->id;
    }

    public function update(User $user, KpiAssessment $assessment): bool
    {
        if (! $user->canEvaluate() || ! $this->sameBusiness($user, $assessment)) {
            return false;
        }

        if (! $assessment->status->isEditable()) {
            return false;
        }

        return $user->isAdmin() || $this->supervises($user, $assessment->staff);
    }

    public function submit(User $user, KpiAssessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function approve(User $user, KpiAssessment $assessment): bool
    {
        if (! $user->isAdmin() || ! $this->sameBusiness($user, $assessment)) {
            return false;
        }

        // Self-approval defeats the point of having an approval step.
        if ($assessment->evaluated_by === $user->id) {
            return false;
        }

        return $assessment->status->value === 'submitted';
    }

    public function returnForCorrection(User $user, KpiAssessment $assessment): bool
    {
        return $user->isAdmin()
            && $this->sameBusiness($user, $assessment)
            && $assessment->status->value === 'submitted';
    }

    public function reopen(User $user, KpiAssessment $assessment): bool
    {
        return $user->isAdmin() && $this->sameBusiness($user, $assessment);
    }

    private function sameBusiness(User $user, KpiAssessment $assessment): bool
    {
        return $user->business_id === $assessment->staff->business_id;
    }

    private function supervises(User $user, Staff $staff): bool
    {
        $supervisorStaff = $user->staff;

        return $supervisorStaff !== null && $staff->supervisor_id === $supervisorStaff->id;
    }
}
