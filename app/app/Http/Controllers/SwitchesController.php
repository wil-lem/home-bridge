<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;

class SwitchesController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all switches
     */
    public function index(): JsonResponse
    {
        try {
            $switches = $this->homeAssistant->getEntitiesByType('switch');
            
            $simplifiedSwitches = array_map(function($switch) {
                return [
                    'entity_id' => $switch['entity_id'],
                    'name' => $switch['attributes']['friendly_name'] ?? $switch['entity_id'],
                    'state' => $switch['state'],
                ];
            }, $switches);

            return response()->json([
                'count' => count($simplifiedSwitches),
                'switches' => $simplifiedSwitches,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Turn on a switch
     */
    public function turnOn(string $entityId): JsonResponse
    {
        try {
            $success = $this->homeAssistant->turnOn("switch.{$entityId}");
            
            return response()->json([
                'success' => $success,
                'entity_id' => "switch.{$entityId}",
                'action' => 'turned_on',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Turn off a switch
     */
    public function turnOff(string $entityId): JsonResponse
    {
        try {
            $success = $this->homeAssistant->turnOff("switch.{$entityId}");
            
            return response()->json([
                'success' => $success,
                'entity_id' => "switch.{$entityId}",
                'action' => 'turned_off',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
