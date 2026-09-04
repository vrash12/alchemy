<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ClassroomTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.bigbluebutton', [
            'url' => 'https://bbb.example.test/bigbluebutton',
            'secret' => 'test-secret',
            'checksum_algorithm' => 'sha256',
            'resources_plugin_manifest_url' => 'https://app.example.test/bbb-plugins/resources/manifest.json',
        ]);
    }

    public function test_the_classroom_launcher_is_available(): void
    {
        $this->get('/classroom')
            ->assertOk()
            ->assertSee('Start a new online lesson.')
            ->assertSee('Join Meeting');
    }

    public function test_join_creates_a_meeting_and_redirects_as_moderator(): void
    {
        Http::fake([
            'https://bbb.example.test/*' => Http::response(
                '<response><returncode>SUCCESS</returncode></response>',
                200,
                ['Content-Type' => 'application/xml'],
            ),
        ]);

        $response = $this->post('/classroom/join');

        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://bbb.example.test/bigbluebutton/api/join?', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $joinParameters);

        $this->assertSame('MODERATOR', $joinParameters['role']);
        $this->assertSame('Trial Tutor', $joinParameters['fullName']);
        $this->assertMatchesRegularExpression('/^alchemy_trial_[0-9a-f-]{36}$/', $joinParameters['meetingID']);

        Http::assertSent(function (Request $request) use ($joinParameters): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $createParameters);

            return str_contains($request->url(), '/api/create?')
                && $createParameters['meetingID'] === $joinParameters['meetingID']
                && $createParameters['name'] === 'Alchemy Technical Trial'
                && json_decode($createParameters['pluginManifests'], true) === [[
                    'url' => 'https://app.example.test/bbb-plugins/resources/manifest.json',
                ]];
        });
    }

    public function test_each_click_uses_a_different_meeting_id(): void
    {
        Http::fake([
            '*' => Http::response('<response><returncode>SUCCESS</returncode></response>'),
        ]);

        $first = $this->post('/classroom/join')->headers->get('Location');
        $second = $this->post('/classroom/join')->headers->get('Location');

        parse_str((string) parse_url($first, PHP_URL_QUERY), $firstParameters);
        parse_str((string) parse_url($second, PHP_URL_QUERY), $secondParameters);

        $this->assertNotSame($firstParameters['meetingID'], $secondParameters['meetingID']);
    }

    public function test_a_failed_bbb_response_returns_a_safe_error(): void
    {
        Http::fake([
            '*' => Http::response(
                '<response><returncode>FAILED</returncode><messageKey>checksumError</messageKey><message>Invalid checksum</message></response>',
            ),
        ]);

        $this->from('/classroom')
            ->post('/classroom/join')
            ->assertRedirect('/classroom')
            ->assertSessionHasErrors('meeting');
    }

    public function test_missing_configuration_returns_a_safe_error_without_an_http_call(): void
    {
        config()->set('services.bigbluebutton.url', null);
        config()->set('services.bigbluebutton.secret', null);
        Http::fake();

        $this->from('/classroom')
            ->post('/classroom/join')
            ->assertRedirect('/classroom')
            ->assertSessionHasErrors('meeting');

        Http::assertNothingSent();
    }
}
