<?php

use App\Services\KimaiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('kimai:test {--user=} {--timezone=}', function () {
    /** @var KimaiService $kimai */
    $kimai = app(KimaiService::class);

    $userOption = $this->option('user');
    $userId = is_numeric($userOption) ? (int) $userOption : null;
    $timezoneOption = $this->option('timezone');
    $timezone = is_string($timezoneOption) && $timezoneOption !== '' ? $timezoneOption : null;

    $this->info('Running Kimai service checks...');

    $debugConfig = $kimai->getDebugConfig();
    $this->line('Found Kimai config:');
    $this->table(['Key', 'Value'], [
        ['url', (string) ($debugConfig['url'] ?? '')],
        ['username_present', (string) (($debugConfig['username_present'] ?? false) ? 'true' : 'false')],
        ['token_present', (string) (($debugConfig['token_present'] ?? false) ? 'true' : 'false')],
        ['token_length', (string) ($debugConfig['token_length'] ?? 0)],
        ['token_preview', (string) ($debugConfig['token_preview'] ?? '')],
        ['default_user_id', isset($debugConfig['default_user_id']) ? (string) $debugConfig['default_user_id'] : 'null'],
        ['option_user', is_null($userId) ? 'null' : (string) $userId],
        ['option_timezone', is_null($timezone) ? 'null' : $timezone],
    ]);

    $results = [];

    try {
        $configured = $kimai->isConfigured();
        $results[] = [
            'method' => 'isConfigured()',
            'status' => $configured ? 'PASS' : 'FAIL',
            'details' => $configured
                ? 'Kimai configuration is present'
                : 'Missing one or more required env values: KIMAI_URL, KIMAI_ACCESS_TOKEN',
        ];
    } catch (\Throwable $e) {
        $configured = false;
        $results[] = [
            'method' => 'isConfigured()',
            'status' => 'FAIL',
            'details' => $e->getMessage(),
        ];
    }

    if ($configured) {
        try {
            $hours = $kimai->getTodayLoggedHours($userId, $timezone);
            $results[] = [
                'method' => 'getTodayLoggedHours()',
                'status' => 'PASS',
                'details' => sprintf('Logged today: %.2f h', $hours),
            ];
        } catch (\Throwable $e) {
            $results[] = [
                'method' => 'getTodayLoggedHours()',
                'status' => 'FAIL',
                'details' => $e->getMessage(),
            ];
        }

        try {
            $currentWork = $kimai->getCurrentWorkSummary($userId, $timezone);
            $results[] = [
                'method' => 'getCurrentWorkSummary()',
                'status' => 'PASS',
                'details' => $currentWork,
            ];
        } catch (\Throwable $e) {
            $results[] = [
                'method' => 'getCurrentWorkSummary()',
                'status' => 'FAIL',
                'details' => $e->getMessage(),
            ];
        }
    } else {
        $results[] = [
            'method' => 'getTodayLoggedHours()',
            'status' => 'SKIP',
            'details' => 'Skipped because Kimai is not configured',
        ];
        $results[] = [
            'method' => 'getCurrentWorkSummary()',
            'status' => 'SKIP',
            'details' => 'Skipped because Kimai is not configured',
        ];
    }

    $this->table(['Method', 'Status', 'Details'], $results);

    $failed = collect($results)->contains(fn (array $result) => $result['status'] === 'FAIL');
    if ($failed) {
        $this->error('One or more Kimai checks failed.');

        try {
            $diagnostics = $kimai->getTimesheetDiagnostics($userId);
            $this->line('Kimai diagnostics:');

            $attemptRows = [];
            foreach (($diagnostics['attempts'] ?? []) as $attempt) {
                $attemptRows[] = [
                    (string) ($attempt['attempt'] ?? ''),
                    (string) ($attempt['request_url'] ?? ''),
                    isset($attempt['status']) ? (string) $attempt['status'] : 'n/a',
                    ((bool) ($attempt['ok'] ?? false)) ? 'true' : 'false',
                    (string) ($attempt['location'] ?? ''),
                ];
            }

            if (!empty($attemptRows)) {
                $this->table(['Attempt', 'Request URL', 'Status', 'OK', 'Location'], $attemptRows);
            }

            foreach (($diagnostics['attempts'] ?? []) as $attempt) {
                $this->line('Attempt: ' . (string) ($attempt['attempt'] ?? ''));
                $this->line('Query: ' . json_encode($attempt['request_query'] ?? [], JSON_UNESCAPED_SLASHES));
                $this->line('Content-Type: ' . (string) ($attempt['content_type'] ?? ''));
                if (!empty($attempt['error'])) {
                    $this->line('Error: ' . (string) $attempt['error']);
                }
                if (!empty($attempt['body_excerpt'])) {
                    $this->line('Body excerpt: ' . (string) $attempt['body_excerpt']);
                }
            }
        } catch (\Throwable $e) {
            $this->error('Failed to collect diagnostics: ' . $e->getMessage());
        }

        return 1;
    }

    $this->info('Kimai service checks completed successfully.');
    return 0;
})->purpose('Test all public Kimai service functions');
