<?php

use App\Support\Money;

/**
 * Product defaults for the KPI module.
 *
 * These are starting values, not fixed rules - everything here is copied into
 * the settings table on seed and can be changed from the settings screen
 * afterwards. The client's own name and targets come from .env so they are
 * never committed.
 */
return [
    'business' => [
        'name' => env('KPI_BUSINESS_NAME', 'Bisnes Demo'),
        'slug' => env('KPI_BUSINESS_SLUG', 'demo'),
        'timezone' => env('KPI_TIMEZONE', 'Asia/Kuala_Lumpur'),
    ],

    // What the focus-product column on the daily entry form is called. Recorded
    // and ranked, but deliberately not part of the KPI score (decision K2) -
    // it already shows up through the sales value.
    'focus_product_label' => env('KPI_FOCUS_PRODUCT_LABEL', 'Produk Fokus'),

    'defaults' => [
        'individual_target_ringgit' => (float) env('KPI_INDIVIDUAL_TARGET', 40_000),
        'team_target_ringgit' => (float) env('KPI_TEAM_TARGET', 200_000),

        'sales_weight' => 40,
        'prorate_target' => true,
        'require_note_on_zero' => true,
        'team_bonus_min_working_days' => 15,

        // Bands are matched with "greater than or equal to" (decision K1). The
        // source tables were written as 80-89 and 90-100, which left 89.5 in no
        // band at all - and scores do land there.
        'bonus_bands_ringgit' => [
            ['min_score' => 90, 'amount' => 300],
            ['min_score' => 80, 'amount' => 200],
            ['min_score' => 70, 'amount' => 100],
        ],

        'incentive_bands_ringgit' => [
            ['min_sales' => 70_000, 'amount' => 300],
            ['min_sales' => 60_000, 'amount' => 200],
            ['min_sales' => 50_000, 'amount' => 100],
        ],

        'team_bonus_bands_ringgit' => [
            ['min_score' => 90, 'amount' => 300],
            ['min_score' => 80, 'amount' => 200],
            ['min_score' => 70, 'amount' => 100],
        ],
    ],

    // Monthly calendar (decision K12), in working days after the month ends.
    'calendar' => [
        'submit_within_days' => (int) env('KPI_SUBMIT_WITHIN_DAYS', 5),
        'approve_within_days' => (int) env('KPI_APPROVE_WITHIN_DAYS', 3),
    ],
];
