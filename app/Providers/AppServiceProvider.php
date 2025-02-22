<?php

namespace App\Providers;

use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    protected $policies = [User::class => UserPolicy::class];
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('hide-revenue', function ($user) {
            return $user->role === 'admin'; // Only admin can access full panel
        });
        Gate::define('viewAny', [UserPolicy::class, 'viewAny']);
        Gate::define('delete', [UserPolicy::class, 'delete']);
        Gate::define('update', [UserPolicy::class, 'update']);
        Gate::define('create', [UserPolicy::class, 'create']);
    }
}
