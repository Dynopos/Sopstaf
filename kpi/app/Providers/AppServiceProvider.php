<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Dates read in Malay; timestamps stay in UTC and are converted on display.
        Carbon::setLocale('ms');
        date_default_timezone_set(config('kpi.business.timezone', 'Asia/Kuala_Lumpur'));

        Blade::directive('rm', fn ($expr) => "<?php echo \\App\\Support\\Money::format($expr); ?>");
        Blade::directive('rmshort', fn ($expr) => "<?php echo \\App\\Support\\Money::formatShort($expr); ?>");

        View::composer('*', function ($view) {
            if (! $view->offsetExists('business')) {
                $view->with('business', auth()->user()?->business);
            }
        });
    }
}
