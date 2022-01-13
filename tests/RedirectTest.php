<?php

namespace Tofandel\Redirects\Tests;

use Tofandel\Redirects\Exceptions\RedirectException;
use Tofandel\Redirects\Models\Redirect;

class RedirectTest extends TestCase
{
    /** @test */
    public function it_redirects_a_request()
    {
        Redirect::create([
            'old_url' => 'old-url',
            'new_url' => 'https://example.com/new/url',
        ]);

        $response = $this->get('old-url');
        $response->assertRedirect('new/url');
    }

    /** @test */
    public function it_redirects_to_an_external_url()
    {
        Redirect::create([
            'old_url' => 'old-url',
            'new_url_external' => 'https://example.com/new/url',
        ]);

        $response = $this->get('old-url');
        $response->assertRedirect('https://example.com/new/url');
    }

    /** @test */
    public function it_redirects_nested_requests()
    {
        Redirect::create([
            'old_url' => '1',
            'new_url' => '2',
        ]);

        $response = $this->get('1');
        $response->assertRedirect('2');

        Redirect::create([
            'old_url' => '2',
            'new_url' => '3',
        ]);

        $response = $this->get('1');
        $response->assertRedirect('3');

        $response = $this->get('2');
        $response->assertRedirect('3');

        Redirect::create([
            'old_url' => '3',
            'new_url' => '4',
        ]);

        $response = $this->get('1');
        $response->assertRedirect('4');

        $response = $this->get('2');
        $response->assertRedirect('4');

        $response = $this->get('3');
        $response->assertRedirect('4');

        Redirect::create([
            'old_url' => '4',
            'new_url' => '1',
        ]);

        $response = $this->get('2');
        $response->assertRedirect('1');

        $response = $this->get('3');
        $response->assertRedirect('1');

        $response = $this->get('4');
        $response->assertRedirect('1');
    }

    /** @test */
    public function it_guards_against_creating_redirect_loops()
    {
        $this->expectException(RedirectException::class);

        Redirect::create([
            'old_url' => 'same-url',
            'new_url' => 'same-url',
        ]);
    }

    /** @test */
    public function it_guards_against_creating_long_redirect_loops()
    {
        $redirect1 = Redirect::create([
            'old_url' => 'old_url',
            'new_url' => 'new_url',
        ]);

        $redirect2 = Redirect::create([
            'old_url' => 'new_url',
            'new_url' => 'old_url',
        ]);
        /** @var Redirect $redirect1 */
        $this->assertNull($redirect1->fresh());
        $this->assertInstanceOf(Redirect::class, $redirect2->fresh());
        $this->assertDatabaseCount('redirects', 1);
    }
}
