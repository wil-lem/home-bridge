<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class KimaiService
{
    protected string $url;
    protected string $username;
    protected string $accessToken;
    protected ?int $userId;

    public function __construct()
    {
        $this->url = rtrim((string) config('kimai.url'), '/');
        $this->username = (string) config('kimai.username');
        $this->accessToken = $this->normalizeToken((string) config('kimai.access_token'));

        $configuredUserId = config('kimai.user_id');
        $this->userId = is_numeric($configuredUserId) ? (int) $configuredUserId : null;
    }

    /**
     * Check if Kimai is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->accessToken);
    }

    /**
     * Return sanitized configuration values for troubleshooting.
     */
    public function getDebugConfig(): array
    {
        return [
            'url' => $this->url,
            'username_present' => $this->username !== '',
            'token_present' => $this->accessToken !== '',
            'token_length' => strlen($this->accessToken),
            'token_preview' => $this->maskToken($this->accessToken),
            'default_user_id' => $this->userId,
        ];
    }

    /**
     * Run a lightweight request and return detailed diagnostics.
     */
    public function getTimesheetDiagnostics(?int $userId = null): array
    {
        $query = [
            'page' => 1,
            'size' => 1,
            'full' => '1',
            'orderBy' => 'begin',
            'order' => 'DESC',
        ];

        $effectiveUserId = $userId ?? $this->userId;
        if (!is_null($effectiveUserId)) {
            $query['user'] = $effectiveUserId;
        }

        $baseUrl = $this->url . '/api/timesheets';
        $attempts = [];

        $attempts[] = $this->runDiagnosticAttempt($baseUrl, $query, 'configured_url');

        if (str_starts_with($this->url, 'http://')) {
            $httpsUrl = 'https://' . substr($this->url, strlen('http://')) . '/api/timesheets';
            $attempts[] = $this->runDiagnosticAttempt($httpsUrl, $query, 'https_variant');
        }

        return [
            'config' => $this->getDebugConfig(),
            'query' => $query,
            'attempts' => $attempts,
        ];
    }

    /**
     * Get total logged hours for today.
     */
    public function getTodayLoggedHours(?int $userId = null, ?string $timezone = null): float
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Kimai is not fully configured. Please set KIMAI_URL and KIMAI_ACCESS_TOKEN.');
        }

        $tz = $timezone ?: (string) config('app.timezone', 'UTC');
        $now = CarbonImmutable::now($tz);
        $startOfDay = $now->startOfDay();
        $endOfDay = $now->endOfDay();

        $effectiveUserId = $userId ?? $this->userId;
        $entries = $this->getTimesheetsForRange($startOfDay, $endOfDay, $effectiveUserId);

        $totalSeconds = 0;
        foreach ($entries as $entry) {
            $duration = $entry['duration'] ?? null;
            if (is_numeric($duration)) {
                $totalSeconds += (int) $duration;
                continue;
            }

            $begin = isset($entry['begin']) ? CarbonImmutable::parse($entry['begin'])->setTimezone($tz) : null;
            if (!$begin) {
                continue;
            }

            $entryEnd = !empty($entry['end'])
                ? CarbonImmutable::parse($entry['end'])->setTimezone($tz)
                : $now;

            if ($entryEnd->lessThanOrEqualTo($startOfDay) || $begin->greaterThanOrEqualTo($endOfDay)) {
                continue;
            }

            $overlapStart = $begin->greaterThan($startOfDay) ? $begin : $startOfDay;
            $overlapEnd = $entryEnd->lessThan($endOfDay) ? $entryEnd : $endOfDay;

            if ($overlapEnd->greaterThan($overlapStart)) {
                $totalSeconds += $overlapStart->diffInSeconds($overlapEnd);
            }
        }

        return round($totalSeconds / 3600, 2);
    }

    /**
     * Get a human-readable summary of what the user is currently working on.
     */
    public function getCurrentWorkSummary(?int $userId = null, ?string $timezone = null): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Kimai is not fully configured. Please set KIMAI_URL and KIMAI_ACCESS_TOKEN.');
        }

        $tz = $timezone ?: (string) config('app.timezone', 'UTC');
        $now = CarbonImmutable::now($tz);
        $effectiveUserId = $userId ?? $this->userId;

        // Include a short history window in case an active entry started yesterday.
        $entries = $this->getTimesheetsForRange($now->subDays(2)->startOfDay(), $now->endOfDay(), $effectiveUserId);

        $activeEntries = array_values(array_filter($entries, function ($entry) use ($now, $tz) {
            if (!empty($entry['end'])) {
                return false;
            }

            if (empty($entry['begin'])) {
                return false;
            }

            $begin = CarbonImmutable::parse($entry['begin'])->setTimezone($tz);
            return $begin->lessThanOrEqualTo($now);
        }));

        if (empty($activeEntries)) {
            return 'Not tracking time';
        }

        usort($activeEntries, function ($a, $b) {
            return strcmp((string) ($b['begin'] ?? ''), (string) ($a['begin'] ?? ''));
        });

        $entry = $activeEntries[0];
        $project = (string) ($entry['project']['name'] ?? '');
        $activity = (string) ($entry['activity']['name'] ?? '');
        $description = trim((string) ($entry['description'] ?? ''));

        $parts = array_values(array_filter([$project, $activity], fn ($value) => $value !== ''));
        if (!empty($parts)) {
            $summary = implode(' - ', $parts);
            return $description !== '' ? $summary . ': ' . $description : $summary;
        }

        return $description !== '' ? $description : 'Timer running';
    }

    /**
     * Get timesheet entries for a date range.
     */
    protected function getTimesheetsForRange(CarbonImmutable $begin, CarbonImmutable $end, ?int $userId = null): array
    {
        $allEntries = [];
        $page = 1;
        $size = 100;

        do {
            $query = [
                'begin' => $begin->format('Y-m-d\\TH:i:s'),
                'end' => $end->format('Y-m-d\\TH:i:s'),
                'page' => $page,
                'size' => $size,
                'full' => '1',
                'orderBy' => 'begin',
                'order' => 'DESC',
            ];

            if (!is_null($userId)) {
                $query['user'] = $userId;
            }

            $response = $this->makeRequest('/api/timesheets', $query);
            if (!$response->successful()) {
                $body = trim((string) $response->body());
                throw new \Exception('Failed to fetch timesheets from Kimai. Status: ' . $response->status() . ($body !== '' ? ' Body: ' . $body : ''));
            }

            $entries = $response->json() ?? [];
            if (!is_array($entries)) {
                break;
            }

            $allEntries = array_merge($allEntries, $entries);
            $page++;
        } while (count($entries) === $size && $page <= 50);

        return $allEntries;
    }

    /**
     * Make a GET request to Kimai API.
     */
    protected function makeRequest(string $endpoint, array $query = []): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
        ])->get($this->url . $endpoint, $query);
    }

    /**
     * Run one HTTP attempt and capture key request/response fields.
     */
    protected function runDiagnosticAttempt(string $url, array $query, string $label): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ])->get($url, $query);

            $body = trim((string) $response->body());
            return [
                'attempt' => $label,
                'request_url' => $url,
                'request_query' => $query,
                'status' => $response->status(),
                'location' => $response->header('Location'),
                'content_type' => $response->header('Content-Type'),
                'ok' => $response->successful(),
                'body_excerpt' => mb_substr($body, 0, 500),
            ];
        } catch (\Throwable $e) {
            return [
                'attempt' => $label,
                'request_url' => $url,
                'request_query' => $query,
                'status' => null,
                'location' => null,
                'content_type' => null,
                'ok' => false,
                'body_excerpt' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Strip optional wrapping quotes and leading "Bearer " from configured token.
     */
    protected function normalizeToken(string $token): string
    {
        $clean = trim($token);

        if (str_starts_with($clean, 'Bearer ')) {
            $clean = substr($clean, 7);
        }

        if (strlen($clean) >= 2) {
            $first = $clean[0];
            $last = $clean[strlen($clean) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $clean = substr($clean, 1, -1);
            }
        }

        return trim($clean);
    }

    protected function maskToken(string $token): string
    {
        $length = strlen($token);
        if ($length === 0) {
            return '';
        }

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($token, 0, 4) . str_repeat('*', $length - 8) . substr($token, -4);
    }
}