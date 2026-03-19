<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;

class SensorsController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all sensors
     */
    public function index(): JsonResponse
    {
        try {
            $sensors = $this->homeAssistant->getEntitiesByType('sensor');
            
            $simplifiedSensors = array_map(function($sensor) {
                return [
                    'entity_id' => $sensor['entity_id'],
                    'name' => $sensor['attributes']['friendly_name'] ?? $sensor['entity_id'],
                    'state' => $sensor['state'],
                    'unit' => $sensor['attributes']['unit_of_measurement'] ?? null,
                    'device_class' => $sensor['attributes']['device_class'] ?? null,
                ];
            }, $sensors);

            return response()->json([
                'count' => count($simplifiedSensors),
                'sensors' => $simplifiedSensors,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all battery levels
     */
    public function battery(): JsonResponse
    {
        try {
            $entities = $this->homeAssistant->getEntitiesWithAttribute('battery_level');
            
            $batteryLevels = array_map(function($entity) {
                return [
                    'entity_id' => $entity['entity_id'],
                    'name' => $entity['attributes']['friendly_name'] ?? $entity['entity_id'],
                    'battery_level' => $entity['attributes']['battery_level'] ?? $entity['state'],
                    'unit' => '%',
                ];
            }, $entities);

            return response()->json([
                'count' => count($batteryLevels),
                'devices' => $batteryLevels,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all temperature sensors
     */
    public function temperature(): JsonResponse
    {
        try {
            $allSensors = $this->homeAssistant->getEntitiesByType('sensor');
            
            $tempSensors = array_values(array_filter($allSensors, function($sensor) {
                $deviceClass = $sensor['attributes']['device_class'] ?? '';
                $unit = $sensor['attributes']['unit_of_measurement'] ?? '';
                return $deviceClass === 'temperature' || in_array($unit, ['°C', '°F', 'C', 'F']);
            }));

            $simplified = array_map(function($sensor) {
                return [
                    'entity_id' => $sensor['entity_id'],
                    'name' => $sensor['attributes']['friendly_name'] ?? $sensor['entity_id'],
                    'temperature' => $sensor['state'],
                    'unit' => $sensor['attributes']['unit_of_measurement'] ?? '°C',
                ];
            }, $tempSensors);

            return response()->json([
                'count' => count($simplified),
                'sensors' => $simplified,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all humidity sensors
     */
    public function humidity(): JsonResponse
    {
        try {
            $allSensors = $this->homeAssistant->getEntitiesByType('sensor');
            
            $humiditySensors = array_values(array_filter($allSensors, function($sensor) {
                $deviceClass = $sensor['attributes']['device_class'] ?? '';
                $unit = $sensor['attributes']['unit_of_measurement'] ?? '';
                return $deviceClass === 'humidity' || $unit === '%';
            }));

            $simplified = array_map(function($sensor) {
                return [
                    'entity_id' => $sensor['entity_id'],
                    'name' => $sensor['attributes']['friendly_name'] ?? $sensor['entity_id'],
                    'humidity' => $sensor['state'],
                    'unit' => '%',
                ];
            }, $humiditySensors);

            return response()->json([
                'count' => count($simplified),
                'sensors' => $simplified,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
