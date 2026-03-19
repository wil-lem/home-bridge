<?php

namespace App\Http\Controllers;

use App\Services\HomeAssistantService;
use Illuminate\Http\JsonResponse;

class AreasController extends Controller
{
    protected HomeAssistantService $homeAssistant;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->homeAssistant = $homeAssistant;
    }

    /**
     * Get all areas
     */
    public function index(): JsonResponse
    {
        try {
            $areas = $this->homeAssistant->getAreas();
            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get entities in a specific area
     */
    public function entities(string $area): JsonResponse
    {
        try {
            $entities = $this->homeAssistant->getEntitiesByArea($area);
            return response()->json([
                'area' => $area,
                'count' => count($entities),
                'entities' => $entities,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
