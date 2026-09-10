<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Business;
use App\Models\KpiCriteria;
use App\Models\Setting;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Everything the module needs to run: the business, its settings, the
 * assessment criteria, and one administrator to log in with.
 */
class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::firstOrCreate(
            ['slug' => config('kpi.business.slug')],
            [
                'name' => config('kpi.business.name'),
                'timezone' => config('kpi.business.timezone'),
            ],
        );

        $this->seedSettings($business);
        $this->seedCriteria($business);
        $this->seedAdmin($business);

        $this->command?->info("Konfigurasi disemai untuk: {$business->name}");
    }

    private function seedSettings(Business $business): void
    {
        $d = config('kpi.defaults');

        $bands = fn (array $rows, string $field, string $target) => array_map(
            fn ($row) => [
                $target => $field === 'min_sales'
                    ? Money::fromRinggit($row[$field])
                    : $row[$field],
                'amount_cents' => Money::fromRinggit($row['amount']),
            ],
            $rows,
        );

        $values = [
            'individual_target_cents' => Money::fromRinggit($d['individual_target_ringgit']),
            'team_target_cents' => Money::fromRinggit($d['team_target_ringgit']),
            'sales_weight' => $d['sales_weight'],
            'prorate_target' => $d['prorate_target'],
            'require_note_on_zero' => $d['require_note_on_zero'],
            'team_bonus_min_working_days' => $d['team_bonus_min_working_days'],
            'bonus_bands' => $bands($d['bonus_bands_ringgit'], 'min_score', 'min_score'),
            'team_bonus_bands' => $bands($d['team_bonus_bands_ringgit'], 'min_score', 'min_score'),
            'incentive_bands' => $bands($d['incentive_bands_ringgit'], 'min_sales', 'min_sales_cents'),
            'criteria_version' => 1,
        ];

        foreach ($values as $key => $value) {
            Setting::put($business->id, $key, $value);
        }
    }

    private function seedCriteria(Business $business): void
    {
        $data = require database_path('data/criteria.php');

        foreach (['individual', 'team'] as $scope) {
            $order = 0;

            foreach ($data[$scope] as $category) {
                foreach ($category['items'] as $item) {
                    [$label, $desc0, $desc1, $desc2] = $item;

                    KpiCriteria::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'scope' => $scope,
                            'category_key' => $category['key'],
                            'label' => $label,
                        ],
                        [
                            'category_label' => $category['label'],
                            'category_weight' => $category['weight'],
                            'desc_0' => $desc0,
                            'desc_1' => $desc1,
                            'desc_2' => $desc2,
                            'sort_order' => $order++,
                            'version' => 1,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }

    private function seedAdmin(Business $business): void
    {
        User::firstOrCreate(
            ['business_id' => $business->id, 'login_code' => env('KPI_ADMIN_CODE', 'admin')],
            [
                'name' => env('KPI_ADMIN_NAME', 'Admin'),
                'phone' => env('KPI_ADMIN_PHONE'),
                'password' => Hash::make(env('KPI_ADMIN_PIN', '123456')),
                'role' => Role::Admin,
                'is_active' => true,
            ],
        );
    }
}
