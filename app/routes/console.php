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

    $results = [];

    try {
        $configured = $kimai->isConfigured();
        $results[] = [
            'method' => 'isConfigured()',
            'status' => $configured ? 'PASS' : 'FAIL',
            'details' => $configured
                ? 'Kimai configuration is present'
                : 'Missing one or more required env values: KIMAI_URL, KIMAI_USERNAME, KIMAI_ACCESS_TOKEN',
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
        return 1;
    }

    $this->info('Kimai service checks completed successfully.');
    return 0;
})->purpose('Test all public Kimai service functions');
