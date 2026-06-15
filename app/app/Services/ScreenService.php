<?php

namespace App\Services;

class ScreenService
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    public function getAllScreens(string $hardwareId, array $screenSize): array
    {
        $powerConsumption = $this->getEntityReading('sensor.electricity_meter_power_consumption');
        $powerProduction = $this->getEntityReading('sensor.electricity_meter_power_production');
        $currentPower = $this->getEntityReading('sensor.hoymiles_dtu_412110001159133_current_power');

        return [
            'screens' => [
                [
                    'id' => 'home',
                    'title' => 'Main',
                    'description' => 'Tap to open energy',
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
                    ],
                ],
                [
                    'id' => 'energy',
                    'title' => 'Energy',
                    'description' => 'Live Home Assistant values',
                    'items' => [
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
            ],
        ];
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
