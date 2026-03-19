<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClimateController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all climate entities
     */
    public function index(): JsonResponse
    {
        try {
            $climateDevices = $this->homeAssistant->getEntitiesByType('climate');
            
            $simplified = array_map(function($device) {
                return [
                    'entity_id' => $device['entity_id'],
                    'name' => $device['attributes']['friendly_name'] ?? $device['entity_id'],
                    'state' => $device['state'],
                    'current_temperature' => $device['attributes']['current_temperature'] ?? null,
                    'target_temperature' => $device['attributes']['temperature'] ?? null,
                    'humidity' => $device['attributes']['current_humidity'] ?? null,
                    'hvac_mode' => $device['attributes']['hvac_mode'] ?? null,
                ];
            }, $climateDevices);

            return response()->json([
                'count' => count($simplified),
                'devices' => $simplified,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set temperature
     */
    public function setTemperature(string $entityId, Request $request): JsonResponse
    {
        try {
            $temperature = $request->input('temperature');
            
            if ($temperature === null) {
                return response()->json(['error' => 'Temperature is required'], 400);
            }

            $success = $this->homeAssistant->setTemperature("climate.{$entityId}", (float)$temperature);
            
            return response()->json([
                'success' => $success,
                'entity_id' => "climate.{$entityId}",
                'action' => 'temperature_set',
                'temperature' => (float)$temperature,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
