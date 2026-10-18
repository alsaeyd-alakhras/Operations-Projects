<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('abilities', function() {
            return include base_path('data/abilities.php');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $baseUrl = request()->getSchemeAndHttpHost();
        config([
            'app.url' => $baseUrl,
            'app.asset_url' => $baseUrl . '/public/'
        ]);


        Paginator::useBootstrapFive();
        //Authouration
        Gate::before(function ($user, $ability) {
            if($user instanceof User) {
                if($user->super_admin) {
                    return true;
                }
            }
        });
        // the Authorization for Report Page
        Gate::define('report.view', function ($user) {
            if($user instanceof User) {
                if($user->roles->contains('role_name', 'report.view')) {
                    return true;
                }
                return false;
            }
        });
    }
}
