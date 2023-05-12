<?php

namespace Bogddan\Redirects\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Bogddan\Redirects\Contracts\RedirectModelContract;
use Bogddan\Redirects\Models\Redirect;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Create a new service provider instance.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->publishConfigs();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
    }

    /**
     * Setup the config.
     */
    protected function publishConfigs()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/redirects.php', 'redirects');
    }

    /**
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->bind(RedirectModelContract::class, $this->config['redirects']['redirect_model'] ?? Redirect::class);
        $this->app->alias(RedirectModelContract::class, 'redirect.model');
    }
}
