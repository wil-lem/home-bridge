<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LightsController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all lights
     */
    public function index(): JsonResponse
    {
        try {
            $lights = $this->homeAssistant->getEntitiesByType('light');
            
            // Simplify the response for AI training
            $simplifiedLights = array_map(function($light) {
                return [
                    'entity_id' => $light['entity_id'],
                    'name' => $light['attributes']['friendly_name'] ?? $light['entity_id'],
                    'state' => $light['state'],
                    'brightness' => $light['attributes']['brightness'] ?? null,
                    'color_temp' => $light['attributes']['color_temp'] ?? null,
                    'rgb_color' => $light['attributes']['rgb_color'] ?? null,
                ];
            }, $lights);

            return response()->json([
                'count' => count($simplifiedLights),
                'lights' => $simplifiedLights,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific light
     */
    public function show(string $entityId): JsonResponse
    {
        try {
            $light = $this->homeAssistant->getEntity("light.{$entityId}");
            
            if (!$light) {
                return response()->json(['error' => 'Light not found'], 404);
            }

            return response()->json($light);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Turn on a light
     */
    public function turnOn(string $entityId, Request $request): JsonResponse
    {
        try {
            $brightness = $request->input('brightness');
            $extraData = [];
            
            if ($brightness !== null) {
                $extraData['brightness'] = max(0, min(255, (int)$brightness));
            }

            $success = $this->homeAssistant->turnOn("light.{$entityId}", $extraData);
            
            return response()->json([
                'success' => $success,
                'entity_id' => "light.{$entityId}",
                'action' => 'turned_on',
                'brightness' => $extraData['brightness'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Turn off a light
     */
    public function turnOff(string $entityId): JsonResponse
    {
        try {
            $success = $this->homeAssistant->turnOff("light.{$entityId}");
            
            return response()->json([
                'success' => $success,
                'entity_id' => "light.{$entityId}",
                'action' => 'turned_off',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set brightness
     */
    public function setBrightness(string $entityId, Request $request): JsonResponse
    {
        try {
            $brightness = (int)$request->input('brightness', 255);
            $success = $this->homeAssistant->setBrightness("light.{$entityId}", $brightness);
            
            return response()->json([
                'success' => $success,
                'entity_id' => "light.{$entityId}",
                'action' => 'brightness_set',
                'brightness' => $brightness,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
