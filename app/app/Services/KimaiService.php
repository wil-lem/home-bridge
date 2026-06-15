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
        $this->accessToken = (string) config('kimai.access_token');

        $configuredUserId = config('kimai.user_id');
        $this->userId = is_numeric($configuredUserId) ? (int) $configuredUserId : null;
    }

    /**
     * Check if Kimai is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->username) && !empty($this->accessToken);
    }

    /**
     * Get total logged hours for today.
     */
    public function getTodayLoggedHours(?int $userId = null, ?string $timezone = null): float
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Kimai is not fully configured. Please set KIMAI_URL, KIMAI_USERNAME and KIMAI_ACCESS_TOKEN.');
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
     * Get timesheet entries for a date range.
     */
    protected function getTimesheetsForRange(CarbonImmutable $begin, CarbonImmutable $end, ?int $userId = null): array
    {
        $allEntries = [];
        $page = 1;
        $size = 100;

        do {
            $query = [
                'begin' => $begin->toIso8601String(),
                'end' => $end->toIso8601String(),
                'page' => $page,
                'size' => $size,
            ];

            if (!is_null($userId)) {
                $query['user'] = $userId;
            }

            $response = $this->makeRequest('/api/timesheets', $query);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch timesheets from Kimai. Status: ' . $response->status());
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
            'X-AUTH-USER' => $this->username,
            'X-AUTH-TOKEN' => $this->accessToken,
            'Accept' => 'application/json',
        ])->get($this->url . $endpoint, $query);
    }
}