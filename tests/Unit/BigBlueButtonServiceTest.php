<?php

namespace Tests\Unit;

use App\Exceptions\BigBlueButtonException;
use App\Services\BigBlueButtonService;
use Tests\TestCase;

final class BigBlueButtonServiceTest extends TestCase
{
    public function test_it_reuses_the_exact_query_string_when_signing_an_api_url(): void
    {
        config()->set('services.bigbluebutton', [
            'url' => 'https://bbb.example.test/bigbluebutton/',
            'secret' => 'shared-secret',
            'checksum_algorithm' => 'sha256',
            'resources_plugin_manifest_url' => null,
        ]);

        $url = app(BigBlueButtonService::class)->buildApiUrl('create', [
            'name' => 'Alchemy Technical Trial',
            'meetingID' => 'alchemy_trial_123',
        ]);

        $query = 'name=Alchemy+Technical+Trial&meetingID=alchemy_trial_123';
        $checksum = hash('sha256', 'create'.$query.'shared-secret');

        $this->assertSame(
            "https://bbb.example.test/bigbluebutton/api/create?{$query}&checksum={$checksum}",
            $url,
        );
    }

    public function test_it_rejects_an_unsupported_checksum_algorithm(): void
    {
        config()->set('services.bigbluebutton', [
            'url' => 'https://bbb.example.test/bigbluebutton',
            'secret' => 'shared-secret',
            'checksum_algorithm' => 'md5',
            'resources_plugin_manifest_url' => null,
        ]);

        $this->expectException(BigBlueButtonException::class);

        app(BigBlueButtonService::class)->buildApiUrl('create', ['meetingID' => 'test']);
    }
}
