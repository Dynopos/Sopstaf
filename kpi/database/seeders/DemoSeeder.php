<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Business;
use App\Models\DailySale;
use App\Models\Staff;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Sample staff and a month of sales, for demonstrating the system before real
 * data goes in. Not part of the production seed - run it explicitly.
 *
 * The monthly totals match the example leaderboard in the source proposal, so
 * the screens can be checked against the document.
 */
class DemoSeeder extends Seeder
{
    private const PEOPLE = [
        // name, code, monthly sales (RM), focus product units
        ['Ahmad', 'S001', 46_800, 18],
        ['Ali', 'S002', 43_200, 22],
        ['Siti', 'S003', 39_500, 12],
        ['Amin', 'S004', 35_800, 9],
        ['Mira', 'S005', 32_100, 14],
    ];

    public function run(): void
    {
        $business = Business::where('slug', config('kpi.business.slug'))->firstOrFail();

        $supervisorUser = User::firstOrCreate(
            ['business_id' => $business->id, 'login_code' => 'sv'],
            [
                'name' => 'Supervisor',
                'password' => Hash::make('123456'),
                'role' => Role::Supervisor,
                'is_active' => true,
            ],
        );

        $supervisorStaff = Staff::firstOrCreate(
            ['business_id' => $business->id, 'employee_code' => 'SV01'],
            [
                'user_id' => $supervisorUser->id,
                'name' => 'Supervisor',
                'joined_on' => Carbon::now()->subYears(2)->startOfYear(),
                'is_active' => true,
                // Runs the shop, does not carry a sales target of their own.
                'is_assessed' => false,
            ],
        );

        // Two months: the earlier one gets closed off, the later one is still live.
        $months = [
            Carbon::now()->subMonthsNoOverflow(2)->startOfMonth(),
            Carbon::now()->subMonthNoOverflow()->startOfMonth(),
        ];

        foreach (self::PEOPLE as [$name, $code, $sales, $focusQty]) {
            $user = User::firstOrCreate(
                ['business_id' => $business->id, 'login_code' => strtolower($code)],
                [
                    'name' => $name,
                    'password' => Hash::make('123456'),
                    'role' => Role::Staff,
                    'is_active' => true,
                ],
            );

            $staff = Staff::firstOrCreate(
                ['business_id' => $business->id, 'employee_code' => $code],
                [
                    'user_id' => $user->id,
                    'name' => $name,
                    'supervisor_id' => $supervisorStaff->id,
                    'joined_on' => Carbon::now()->subYear()->startOfYear(),
                    'is_active' => true,
                ],
            );

            foreach ($months as $i => $month) {
                // Slightly weaker figures in the earlier month so the trend moves.
                $scale = $i === 0 ? 0.92 : 1.0;
                $this->seedMonth(
                    $staff, $month,
                    Money::fromRinggit(round($sales * $scale)),
                    (int) round($focusQty * $scale),
                    $supervisorUser->id,
                );
            }
        }

        $labels = collect($months)->map->translatedFormat('F Y')->join(' dan ');
        $this->command?->info("Data demo disemai untuk {$labels}.");
    }

    /** Spread a monthly total across the days so the daily rows sum exactly to it. */
    private function seedMonth(Staff $staff, Carbon $month, int $totalCents, int $focusQty, int $enteredBy): void
    {
        $days = $month->daysInMonth;
        $perDay = intdiv($totalCents, $days);

        // Vary the daily figures a little so the trend chart is not a flat line,
        // then put the whole difference on the last day. The month has to add up
        // to the figure exactly - a summary that disagrees with its detail is
        // the bug this design exists to avoid.
        $amounts = [];
        for ($day = 1; $day < $days; $day++) {
            $wobble = (int) round($perDay * (($day % 5) - 2) * 0.08);
            $amounts[$day] = max(0, $perDay + $wobble);
        }
        $amounts[$days] = $totalCents - array_sum($amounts);

        $qtyPerDay = intdiv($focusQty, $days);
        $qtyRemainder = $focusQty - ($qtyPerDay * $days);

        foreach ($amounts as $day => $amount) {
            $date = $month->copy()->day($day);

            DailySale::updateOrCreate(
                ['staff_id' => $staff->id, 'sold_on' => $date->toDateString()],
                [
                    'business_id' => $staff->business_id,
                    'amount_cents' => $amount,
                    'focus_qty' => $qtyPerDay + ($day <= $qtyRemainder ? 1 : 0),
                    'source' => 'manual',
                    'entered_by' => $enteredBy,
                ],
            );
        }
    }
}
