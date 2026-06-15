<?php

namespace App\Services;

class ScreenService
{
    protected HomeAssistantService $homeAssistant;
    protected KimaiService $kimai;

    public function __construct(HomeAssistantService $homeAssistant, KimaiService $kimai)
    {
        $this->homeAssistant = $homeAssistant;
        $this->kimai = $kimai;
    }

    public function getAllScreens(string $hardwareId, array $screenSize): array
    {
        $powerConsumption = $this->getEntityReading('sensor.electricity_meter_power_consumption');
        $powerProduction = $this->getEntityReading('sensor.electricity_meter_power_production');
        $currentPower = $this->getEntityReading('sensor.hoymiles_dtu_412110001159133_current_power');
        $loggedHours = $this->getTodayLoggedHoursReading();
        $currentWork = $this->truncateText($this->getCurrentWorkReading(), 28);

        return [
            'screens' => [
                [
                    'id' => 'home',
                    'title' => 'Main',
                    'description' => 'Tap to open dashboards',
                    'items' => [
                        [
                            'type' => 'text',
                            'x' => 44,
                            'y' => 94,
                            'size' => 3,
                            'color' => 2016,
                            'text' => 'Energy',
                        ],
                        [
                            'type' => 'text',
                            'x' => 34,
                            'y' => 130,
                            'size' => 2,
                            'color' => 65535,
                            'text' => 'Open dashboard',
                        ],
                        [
                            'type' => 'text',
                            'x' => 44,
                            'y' => 190,
                            'size' => 3,
                            'color' => 2016,
                            'text' => 'Worklog',
                        ],
                        [
                            'type' => 'text',
                            'x' => 44,
                            'y' => 226,
                            'size' => 2,
                            'color' => 65535,
                            'text' => 'Kimai status',
                        ],
                    ],
                    'interactions' => [
                        [
                            'type' => 'touch',
                            'action' => 'navigate',
                            'target' => 'energy',
                            'area' => [
                                'x' => 20,
                                'y' => 70,
                                'w' => 280,
                                'h' => 110,
                            ],
                        ],
                        [
                            'type' => 'touch',
                            'action' => 'navigate',
                            'target' => 'worklog',
                            'area' => [
                                'x' => 20,
                                'y' => 166,
                                'w' => 280,
                                'h' => 90,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'energy',
                    'title' => 'Energy',
                    'description' => 'Live Home Assistant values',
                    'items' => [
                        [
                            'type' => 'text',
                            'x' => 10,
                            'y' => 10,
                            'size' => 2,
                            'color' => 65535,
                            'text' => '< Back',
                        ],
                        [
                            'type' => 'value',
                            'x' => 10,
                            'y' => 44,
                            'size' => 2,
                            'color' => 65535,
                            'label' => 'Consumption',
                            'value' => $powerConsumption['value'],
                            'unit' => $powerConsumption['unit'],
                        ],
                        [
                            'type' => 'value',
                            'x' => 10,
                            'y' => 90,
                            'size' => 2,
                            'color' => 65535,
                            'label' => 'Production',
                            'value' => $powerProduction['value'],
                            'unit' => $powerProduction['unit'],
                        ],
                        [
                            'type' => 'value',
                            'x' => 10,
                            'y' => 136,
                            'size' => 2,
                            'color' => 65535,
                            'label' => 'Current',
                            'value' => $currentPower['value'],
                            'unit' => $currentPower['unit'],
                        ],
                    ],
                    'interactions' => [
                        [
                            'type' => 'touch',
                            'action' => 'navigate',
                            'target' => 'home',
                            'area' => [
                                'x' => 0,
                                'y' => 0,
                                'w' => (int) ($screenSize['width'] ?? 320),
                                'h' => 30,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'worklog',
                    'title' => 'Worklog',
                    'description' => 'Kimai logged hours and current task',
                    'items' => [
                        [
                            'type' => 'text',
                            'x' => 10,
                            'y' => 10,
                            'size' => 2,
                            'color' => 65535,
                            'text' => '< Back',
                        ],
                        [
                            'type' => 'value',
                            'x' => 10,
                            'y' => 44,
                            'size' => 2,
                            'color' => 65535,
                            'label' => 'Today',
                            'value' => $loggedHours['value'],
                            'unit' => $loggedHours['unit'],
                        ],
                        [
                            'type' => 'text',
                            'x' => 10,
                            'y' => 96,
                            'size' => 2,
                            'color' => 65535,
                            'text' => 'Now',
                        ],
                        [
                            'type' => 'text',
                            'x' => 10,
                            'y' => 124,
                            'size' => 2,
                            'color' => 65535,
                            'text' => $currentWork,
                        ],
                    ],
                    'interactions' => [
                        [
                            'type' => 'touch',
                            'action' => 'navigate',
                            'target' => 'home',
                            'area' => [
                                'x' => 0,
                                'y' => 0,
                                'w' => (int) ($screenSize['width'] ?? 320),
                                'h' => 30,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getTodayLoggedHoursReading(): array
    {
        try {
            if (!$this->kimai->isConfigured()) {
                return ['value' => '--', 'unit' => 'h'];
            }

            $hours = $this->kimai->getTodayLoggedHours();
            return [
                'value' => number_format($hours, 2, '.', ''),
                'unit' => 'h',
            ];
        } catch (\Throwable $e) {
            return ['value' => '--', 'unit' => 'h'];
        }
    }

    private function getCurrentWorkReading(): string
    {
        try {
            if (!$this->kimai->isConfigured()) {
                return 'Kimai not configured';
            }

            return $this->kimai->getCurrentWorkSummary();
        } catch (\Throwable $e) {
            return 'Unavailable';
        }
    }

    private function truncateText(string $text, int $maxLength): string
    {
        $trimmed = trim($text);
        if (mb_strlen($trimmed) <= $maxLength) {
            return $trimmed;
        }

        return rtrim(mb_substr($trimmed, 0, $maxLength - 3)) . '...';
    }

    private function getEntityReading(string $entityId): array
    {
        try {
            if (!$this->homeAssistant->isConfigured()) {
                return ['value' => '--', 'unit' => ''];
            }

            $entity = $this->homeAssistant->getEntity($entityId);
            if (!$entity) {
                return ['value' => '--', 'unit' => ''];
            }

            return [
                'value' => (string) ($entity['state'] ?? '--'),
                'unit' => (string) ($entity['attributes']['unit_of_measurement'] ?? ''),
            ];
        } catch (\Throwable $e) {
            return ['value' => '--', 'unit' => ''];
        }
    }
}
