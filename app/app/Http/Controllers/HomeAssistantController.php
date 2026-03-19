<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;

class HomeAssistantController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all entities from Home Assistant
     */
    public function entities(): JsonResponse
    {
        if (!$this->homeAssistant->isConfigured()) {
            return response()->json([
                'error' => 'Home Assistant configuration missing',
            ], 500);
        }

        try {
            $entities = $this->homeAssistant->getEntities();
            return response()->json($entities);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
