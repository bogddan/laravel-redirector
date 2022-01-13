<?php

namespace Tofandel\Redirects\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Tofandel\Redirects\Middleware\RedirectRequests;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->setUpMiddleware($this->app);
    }

    /**
     * Register the service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Set up the database and migrate the necessary tables.
     *
     * @param  $app
     */
    protected function setUpDatabase(Application $app)
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the middleware.
     *
     * @param  Application  $app
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUpMiddleware(Application $app)
    {
        $app->make(Kernel::class)->pushMiddleware(RedirectRequests::class);
    }
}
