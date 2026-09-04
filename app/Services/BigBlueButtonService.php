<?php

namespace App\Services;

use App\Exceptions\BigBlueButtonException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class BigBlueButtonService
{
    private const SUPPORTED_ALGORITHMS = ['sha1', 'sha256', 'sha384', 'sha512'];

    private string $url;

    private string $secret;

    private string $checksumAlgorithm;

    private ?string $resourcesPluginManifestUrl;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.bigbluebutton.url'), '/');
        $this->secret = (string) config('services.bigbluebutton.secret');
        $this->checksumAlgorithm = strtolower((string) config('services.bigbluebutton.checksum_algorithm', 'sha256'));
        $this->resourcesPluginManifestUrl = config('services.bigbluebutton.resources_plugin_manifest_url');
    }

    public function createMeeting(string $meetingId): void
    {
        $parameters = [
            'name' => 'Alchemy Technical Trial',
            'meetingID' => $meetingId,
        ];

        if (filled($this->resourcesPluginManifestUrl)) {
            $parameters['pluginManifests'] = json_encode(
                [['url' => $this->resourcesPluginManifestUrl]],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
            );
        }

        $url = $this->buildApiUrl('create', $parameters);

        try {
            $response = Http::accept('application/xml')
                ->connectTimeout(5)
                ->timeout(15)
                ->get($url);
        } catch (ConnectionException $exception) {
            Log::warning('BigBlueButton create request could not connect.', [
                'meeting_id' => $meetingId,
                'exception' => $exception->getMessage(),
            ]);

            throw new BigBlueButtonException('Unable to connect to BigBlueButton.', previous: $exception);
        }

        $this->assertSuccessfulCreateResponse($response, $meetingId);
    }

    public function generateJoinUrl(string $meetingId, string $fullName = 'Trial Tutor'): string
    {
        return $this->buildApiUrl('join', [
            'fullName' => $fullName,
            'meetingID' => $meetingId,
            'role' => 'MODERATOR',
        ]);
    }

    public function buildApiUrl(string $callName, array $parameters): string
    {
        $this->assertConfigured();

        $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC1738);
        $checksum = hash($this->checksumAlgorithm, $callName.$query.$this->secret);

        return sprintf('%s/api/%s?%s&checksum=%s', $this->url, $callName, $query, $checksum);
    }

    private function assertConfigured(): void
    {
        if ($this->url === '' || $this->secret === '') {
            throw new BigBlueButtonException('BigBlueButton is not configured.');
        }

        if (! in_array($this->checksumAlgorithm, self::SUPPORTED_ALGORITHMS, true)) {
            throw new BigBlueButtonException('The configured checksum algorithm is not supported.');
        }
    }

    private function assertSuccessfulCreateResponse(Response $response, string $meetingId): void
    {
        if (! $response->successful()) {
            Log::warning('BigBlueButton create request returned an HTTP error.', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
            ]);

            throw new BigBlueButtonException('BigBlueButton returned an HTTP error.');
        }

        try {
            $xml = simplexml_load_string($response->body());
        } catch (Throwable $exception) {
            $xml = false;
        }

        if ($xml === false || ! isset($xml->returncode)) {
            throw new BigBlueButtonException('BigBlueButton returned malformed XML.');
        }

        if ((string) $xml->returncode !== 'SUCCESS') {
            Log::warning('BigBlueButton rejected the create request.', [
                'meeting_id' => $meetingId,
                'message_key' => isset($xml->messageKey) ? (string) $xml->messageKey : null,
                'message' => isset($xml->message) ? (string) $xml->message : null,
            ]);

            throw new BigBlueButtonException('BigBlueButton did not create the meeting.');
        }
    }
}
