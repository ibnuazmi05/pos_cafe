<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User;
use App\Observers\ProductObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Deteksi N+1 saat development/testing: lazy loading akan melempar exception.
        Model::preventLazyLoading(! $this->app->isProduction());

        Product::observe(ProductObserver::class);

        // Policy ditemukan otomatis: App\Models\X => App\Policies\XPolicy.
        Gate::define('manage-master-data', fn (User $u) => $u->isAdmin());
        Gate::define('manage-employees', fn (User $u) => $u->isSuperadmin());
        Gate::define('view-reports', fn (User $u) => $u->isSuperadmin() || $u->isAdmin());
        Gate::define('view-audit-log', fn (User $u) => $u->isSuperadmin());
        Gate::define('export-reports', fn (User $u) => $u->isSuperadmin());
        Gate::define('manage-settings', fn (User $u) => $u->isSuperadmin());

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // @rupiah($angka) => "Rp 1.250.000"   |   @qty($angka) => "1,5"
        Blade::directive('rupiah', fn (string $expression) => "<?php echo \\App\\Support\\Format::rupiah({$expression}); ?>");
        Blade::directive('qty', fn (string $expression) => "<?php echo \\App\\Support\\Format::qty({$expression}); ?>");

        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.tailwind');
    }
}
