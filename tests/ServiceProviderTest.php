<?php

namespace Tofandel\Redirects\Tests;

use ArrayAccess;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Mockery;
use Tofandel\Redirects\Contracts\RedirectModelContract;
use Tofandel\Redirects\Middleware\RedirectRequests;
use Tofandel\Redirects\Models\Redirect;

class ServiceProviderTest extends TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $application_mock;
    /**
     * @var Mockery\Mock
     */
    protected $router_mock;

    /**
     * @var ServiceProvider
     */
    protected $service_provider;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $config;

    public function setUp(): void
    {

        $this->config   = new Repository();

        $this->application_mock = Mockery::mock(Application::class);
        $this->application_mock->shouldReceive('make')->zeroOrMoreTimes()->with('path.config')->andReturn('/some/config/path');
        $this->application_mock->shouldReceive('make')->zeroOrMoreTimes()->with('config')->andReturn($this->config);

        $this->router_mock = Mockery::mock(Router::class);

        $this->service_provider = new \Tofandel\Redirects\ServiceProvider($this->application_mock);

        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(ServiceProvider::class, $this->service_provider);
    }

    /**
     * @test
     */
    public function it_register_aliases_method()
    {
        $this->application_mock->shouldReceive('bind')->once()->with(
            RedirectModelContract::class,
            Redirect::class
        )->andReturnNull();
        $this->application_mock->shouldReceive('alias')->once()->with(
            RedirectModelContract::class,
            'redirect.model'
        )->andReturnNull();
        $this->service_provider->register();
    }

    /**
     * @test
     */
    public function it_performs_a_boot_method()
    {
        $this->router_mock->shouldReceive('aliasMiddleware')
            ->once()
            ->with('redirect.requests', RedirectRequests::class);

        $this->service_provider->boot($this->router_mock);
    }
}
