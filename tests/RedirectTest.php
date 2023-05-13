<?php

namespace Bogddan\Redirects\Tests;

use Illuminate\Support\Facades\Route;
use Bogddan\Redirects\Exceptions\RedirectException;
use Bogddan\Redirects\Models\Redirect;

class RedirectTest extends TestCase
{
    /** @test */
    public function it_redirects_a_request(): void
    {
        Redirect::create([
            'old_url' => 'old-url',
            'new_url' => 'https://example.com/new/url',
        ]);

        $response = $this->get('old-url');
        $response->assertRedirect('new/url');
    }

    /** @test */
    public function it_redirects_a_request_with_querystring()
    {
        Route::get('old-url', function () {
            return response('Hi from old-url');
        });

        Redirect::create([
            'old_url' => 'old-url?obsolete=1#frag',
            'new_url' => 'new/url?12=a#frag',
        ]);

        $response = $this->get('old-url');
        $response->assertSeeText('old-url');

        $response = $this->get('old-url?obsolete=1');
        $response->assertRedirect('new/url?12=a#frag');
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

    /** @test */
    public function it_excludes_redirects_from_happening()
    {
        config(['redirects.exclude' => [
            'excluded',
        ]]);

        Route::get('real-url', function () {
            return response('Hi from real-url');
        });
        Route::get('excluded', function () {
            return response('Hi from excluded');
        });

        Redirect::create([
            'old_url' => 'excluded',
            'new_url' => 'real-url',
        ]);

        $response = $this->get('excluded');
        $response->assertSuccessful();
        $response->assertSeeText('excluded');
    }

    /** @test */
    public function it_does_business_as_usual()
    {
        Route::get('some-normal-url', function () {
            return response('Hi');
        });
        $response = $this->get('some-normal-url');
        $response->assertSuccessful();
    }
}
