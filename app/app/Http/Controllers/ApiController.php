<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * API status check with Home Assistant connectivity
     */
    public function status(): JsonResponse
    {
        $status = [
            'api' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ];

        // Check Home Assistant configuration
        if (!$this->homeAssistant->isConfigured()) {
            $status['homeassistant'] = [
                'status' => 'not_configured',
                'message' => 'Home Assistant URL or API key not set',
            ];
        } else {
            // Try to connect to Home Assistant
            try {
                $entities = $this->homeAssistant->getEntities();
                $status['homeassistant'] = [
                    'status' => 'connected',
                    'entity_count' => count($entities),
                    'url' => config('homeassistant.url'),
                ];
            } catch (\Exception $e) {
                $status['homeassistant'] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'url' => config('homeassistant.url'),
                ];
            }
        }

        return response()->json($status);
    }
}
