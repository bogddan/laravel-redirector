<?php

namespace Bogddan\Redirects;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Bogddan\Redirects\Contracts\RedirectModelContract;
use Bogddan\Redirects\Middleware\RedirectRequests;
use Bogddan\Redirects\Models\Redirect;

class ServiceProvider extends BaseServiceProvider
{
    protected Router $router;

    /**
     * Create a new service provider instance.
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(Router $router): void
    {
        $this->router = $router;

        $this->publishConfigs();
        $this->publishMigrations();
        $this->registerMiddleware();
        $this->registerRouteBindings();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    protected function publishConfigs(): void
    {
        $this->mergeConfigFrom(\dirname(__DIR__) . '/config/redirects.php', 'redirects');

        $this->publishes([
            __DIR__ . '/../config/redirects.php' => config_path('redirects.php'),
        ], 'config');
    }

    protected function publishMigrations(): void
    {
        if (empty(File::glob(database_path('migrations/*_create_redirects_table.php')))) {
            $timestamp = date('Y_m_d_His');
            $migration = database_path("migrations/{$timestamp}_create_redirects_table.php");

            $this->publishes([
                __DIR__ . '/../database/migrations/0000_00_00_000000_create_redirects_table.php' => $migration,
            ], 'migrations');
        }
    }

    protected function registerMiddleware(): void
    {
        $this->router->aliasMiddleware('redirect.requests', RedirectRequests::class);
    }

    protected function registerRouteBindings(): void
    {
        Route::model('redirect', RedirectModelContract::class);
    }

    protected function registerBindings(): void
    {
        $this->app->bind(RedirectModelContract::class, $this->config['redirects']['redirect_model'] ?? Redirect::class);
        $this->app->alias(RedirectModelContract::class, 'redirect.model');
    }
}
