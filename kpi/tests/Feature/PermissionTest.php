<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatus;
use App\Enums\Role;
use App\Exceptions\WorkflowException;
use App\Models\Business;
use App\Models\DailySale;
use App\Models\KpiAssessment;
use App\Models\KpiCriteria;
use App\Models\Staff;
use App\Models\User;
use App\Services\AssessmentWorkflow;
use App\Services\PeriodService;
use App\Support\Money;
use Database\Seeders\ConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The rules that have to hold whatever the interface shows. Every check here
 * goes through an HTTP request or the workflow service, never through the view.
 */
class PermissionTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $admin;
    private User $supervisor;
    private User $otherAdmin;
    private Staff $ahmad;
    private Staff $ali;
    private $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConfigurationSeeder::class);
        $this->business = Business::first();
        $this->admin = User::where('role', Role::Admin)->first();

        $this->supervisor = $this->makeUser('Penyelia', 'sv', Role::Supervisor);
        $this->otherAdmin = $this->makeUser('Admin Dua', 'admin2', Role::Admin);

        $supervisorStaff = Staff::create([
            'business_id' => $this->business->id,
            'user_id' => $this->supervisor->id,
            'employee_code' => 'SV01', 'name' => 'Penyelia',
            'joined_on' => '2024-01-01', 'is_active' => true,
        ]);

        $this->ahmad = $this->makeStaff('Ahmad', 'S001', $supervisorStaff->id);
        $this->ali = $this->makeStaff('Ali', 'S002', $supervisorStaff->id);

        DailySale::create([
            'business_id' => $this->business->id, 'staff_id' => $this->ahmad->id,
            'sold_on' => '2026-08-15', 'amount_cents' => Money::fromRinggit(40_000),
            'entered_by' => $this->supervisor->id,
        ]);

        $this->period = app(PeriodService::class)->open($this->business, 2026, 8, $this->admin);
    }

    // --- staff boundaries -------------------------------------------------

    public function test_staff_cannot_open_another_persons_assessment(): void
    {
        $assessment = $this->assessmentFor($this->ali);

        $this->actingAs($this->ahmad->user)
            ->get(route('assessments.edit', $assessment))
            ->assertForbidden();
    }

    public function test_staff_cannot_edit_their_own_scores(): void
    {
        $assessment = $this->assessmentFor($this->ahmad);
        $criteria = KpiCriteria::where('business_id', $this->business->id)->individual()->first();

        $this->actingAs($this->ahmad->user)
            ->put(route('assessments.update', $assessment), [
                'items' => [$criteria->id => ['score' => 2]],
            ])
            ->assertForbidden();
    }

    public function test_staff_cannot_reach_the_sales_entry_screen(): void
    {
        $this->actingAs($this->ahmad->user)
            ->get(route('sales.index'))
            ->assertForbidden();
    }

    public function test_staff_cannot_reach_settings_or_the_audit_trail(): void
    {
        $this->actingAs($this->ahmad->user)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($this->ahmad->user)->get(route('audit.index'))->assertForbidden();
    }

    public function test_staff_see_only_their_own_reward_row(): void
    {
        $this->actingAs($this->ahmad->user)
            ->get(route('rewards.index'))
            ->assertOk()
            ->assertDontSee('Ali');
    }

    // --- supervisor boundaries -------------------------------------------

    public function test_a_supervisor_cannot_approve_an_assessment_they_scored(): void
    {
        $assessment = $this->submittedAssessment();

        $this->actingAs($this->supervisor)
            ->post(route('assessments.approve', $assessment))
            ->assertForbidden();
    }

    public function test_an_admin_cannot_approve_an_assessment_they_scored_themselves(): void
    {
        $assessment = $this->assessmentFor($this->ahmad);
        $this->scoreAll($assessment, $this->admin);          // admin does the scoring
        app(AssessmentWorkflow::class)->submit($assessment->refresh(), $this->admin);

        $this->actingAs($this->admin)
            ->post(route('assessments.approve', $assessment))
            ->assertForbidden();

        // A different admin can.
        $this->actingAs($this->otherAdmin)
            ->post(route('assessments.approve', $assessment))
            ->assertRedirect();

        $this->assertSame(AssessmentStatus::Approved, $assessment->refresh()->status);
    }

    public function test_a_supervisor_cannot_lock_a_period(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('periods.lock', $this->period))
            ->assertForbidden();
    }

    // --- immutability ------------------------------------------------------

    public function test_an_approved_assessment_cannot_be_edited(): void
    {
        $assessment = $this->submittedAssessment();
        app(AssessmentWorkflow::class)->approve($assessment->refresh(), $this->admin);

        $criteria = KpiCriteria::where('business_id', $this->business->id)->individual()->first();

        $this->expectException(WorkflowException::class);
        app(AssessmentWorkflow::class)->saveScores(
            $assessment->refresh(),
            [$criteria->id => ['score' => 0, 'note' => 'cuba ubah']],
            $this->supervisor,
        );
    }

    public function test_audit_records_cannot_be_changed_or_deleted(): void
    {
        $log = \App\Models\AuditLog::where('business_id', $this->business->id)->firstOrFail();

        $log->action = 'diubah';
        $this->expectException(\RuntimeException::class);
        $log->save();
    }

    public function test_audit_records_cannot_be_deleted(): void
    {
        $log = \App\Models\AuditLog::where('business_id', $this->business->id)->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $log->delete();
    }

    // --- submission guards -------------------------------------------------

    public function test_an_incomplete_assessment_cannot_be_submitted(): void
    {
        $assessment = $this->assessmentFor($this->ahmad);
        $criteria = KpiCriteria::where('business_id', $this->business->id)->individual()->first();

        app(AssessmentWorkflow::class)->saveScores(
            $assessment, [$criteria->id => ['score' => 2]], $this->supervisor,
        );

        $this->expectExceptionMessage('belum dinilai');
        app(AssessmentWorkflow::class)->submit($assessment->refresh(), $this->supervisor);
    }

    public function test_a_zero_without_a_note_blocks_submission(): void
    {
        $assessment = $this->assessmentFor($this->ahmad);
        $criteria = KpiCriteria::where('business_id', $this->business->id)->individual()->get();

        $items = [];
        foreach ($criteria as $i => $c) {
            $items[$c->id] = ['score' => $i === 0 ? 0 : 2, 'note' => null];
        }

        app(AssessmentWorkflow::class)->saveScores($assessment, $items, $this->supervisor);

        $this->expectExceptionMessage('Skor 0 mesti disertakan catatan');
        app(AssessmentWorkflow::class)->submit($assessment->refresh(), $this->supervisor);
    }

    // --- workflow transitions ---------------------------------------------

    public function test_every_disallowed_transition_is_rejected(): void
    {
        $workflow = app(AssessmentWorkflow::class);

        $allowed = [
            ['draft', 'submitted'], ['reopened', 'submitted'],
            ['submitted', 'draft'], ['submitted', 'approved'],
            ['approved', 'locked'], ['locked', 'reopened'],
        ];

        foreach (AssessmentStatus::cases() as $from) {
            foreach (AssessmentStatus::cases() as $to) {
                $expected = in_array([$from->value, $to->value], $allowed, true);

                $this->assertSame(
                    $expected,
                    $workflow->canTransition($from, $to),
                    "{$from->value} -> {$to->value}",
                );
            }
        }
    }

    public function test_a_reason_is_required_to_return_or_reopen(): void
    {
        $assessment = $this->submittedAssessment();

        $this->actingAs($this->admin)
            ->post(route('assessments.return', $assessment), ['reason' => ''])
            ->assertSessionHasErrors('reason');
    }

    // --- helpers ------------------------------------------------------------

    private function makeUser(string $name, string $code, Role $role): User
    {
        return User::create([
            'business_id' => $this->business->id,
            'name' => $name, 'login_code' => $code,
            'password' => Hash::make('123456'),
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function makeStaff(string $name, string $code, ?int $supervisorId = null): Staff
    {
        $user = $this->makeUser($name, strtolower($code), Role::Staff);

        return Staff::create([
            'business_id' => $this->business->id,
            'user_id' => $user->id,
            'employee_code' => $code, 'name' => $name,
            'supervisor_id' => $supervisorId,
            'joined_on' => '2024-01-01', 'is_active' => true,
        ]);
    }

    private function assessmentFor(Staff $staff): KpiAssessment
    {
        return $this->period->assessments()->where('staff_id', $staff->id)->firstOrFail();
    }

    private function scoreAll(KpiAssessment $assessment, User $actor): void
    {
        $criteria = KpiCriteria::where('business_id', $this->business->id)->individual()->get();

        $items = [];
        foreach ($criteria as $c) {
            $items[$c->id] = ['score' => 2, 'note' => null];
        }

        app(AssessmentWorkflow::class)->saveScores($assessment, $items, $actor);
    }

    private function submittedAssessment(): KpiAssessment
    {
        $assessment = $this->assessmentFor($this->ahmad);
        $this->scoreAll($assessment, $this->supervisor);
        app(AssessmentWorkflow::class)->submit($assessment->refresh(), $this->supervisor);

        return $assessment->refresh();
    }
}
