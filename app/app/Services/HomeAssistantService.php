<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class HomeAssistantService
{
    protected string $url;
    protected string $apiKey;

    public function __construct()
    {
        $this->url = config('homeassistant.url');
        $this->apiKey = config('homeassistant.api_key');
    }

    /**
     * Check if Home Assistant is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->apiKey);
    }

    /**
     * Get all entities from Home Assistant
     */
    public function getEntities(): array
    {
        $response = $this->makeRequest('/api/states');
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch entities from Home Assistant. Status: ' . $response->status());
        }

        $entities = $response->json();

        // Add type field to each entity based on entity_id
        return array_map(function ($entity) {
            if (isset($entity['entity_id'])) {
                $parts = explode('.', $entity['entity_id']);
                $entity['type'] = $parts[0] ?? null;
            }
            return $entity;
        }, $entities);
    }

    /**
     * Get entities by type
     */
    public function getEntitiesByType(string $type): array
    {
        $allEntities = $this->getEntities();
        return array_values(array_filter($allEntities, function ($entity) use ($type) {
            return isset($entity['type']) && $entity['type'] === $type;
        }));
    }

    /**
     * Get a single entity by entity_id
     */
    public function getEntity(string $entityId): ?array
    {
        $response = $this->makeRequest("/api/states/{$entityId}");
        
        if (!$response->successful()) {
            return null;
        }

        $entity = $response->json();
        if (isset($entity['entity_id'])) {
            $parts = explode('.', $entity['entity_id']);
            $entity['type'] = $parts[0] ?? null;
        }
        
        return $entity;
    }

    /**
     * Get all areas
     */
    public function getAreas(): array
    {
        // Home Assistant REST API doesn't expose area registry directly
        // Extract unique areas from entities
        $entities = $this->getEntities();
        $areas = [];
        
        foreach ($entities as $entity) {
            // Try to extract area from attributes
            if (isset($entity['attributes']['area_id'])) {
                $areaId = $entity['attributes']['area_id'];
                if (!isset($areas[$areaId])) {
                    $areas[$areaId] = [
                        'area_id' => $areaId,
                        'name' => ucfirst(str_replace('_', ' ', $areaId)),
                    ];
                }
            }
            
            // Also extract from friendly_name if it contains room indicators
            if (isset($entity['attributes']['friendly_name'])) {
                $name = $entity['attributes']['friendly_name'];
                // Common room patterns: "Kitchen Light", "Bedroom Fan", etc.
                $words = explode(' ', $name);
                if (count($words) > 1) {
                    $potentialArea = strtolower($words[0]);
                    // Common room names
                    $commonRooms = ['kitchen', 'bedroom', 'living', 'bathroom', 'hall', 'garage', 'office', 'dining'];
                    if (in_array($potentialArea, $commonRooms)) {
                        if (!isset($areas[$potentialArea])) {
                            $areas[$potentialArea] = [
                                'area_id' => $potentialArea,
                                'name' => ucfirst($potentialArea),
                            ];
                        }
                    }
                }
            }
        }
        
        return array_values($areas);
    }

    /**
     * Call a service (e.g., turn on/off, set brightness)
     */
    public function callService(string $domain, string $service, array $data = []): bool
    {
        $response = $this->postRequest("/api/services/{$domain}/{$service}", $data);
        return $response->successful();
    }

    /**
     * Turn on an entity
     */
    public function turnOn(string $entityId, array $extraData = []): bool
    {
        $parts = explode('.', $entityId);
        $domain = $parts[0] ?? 'homeassistant';
        
        return $this->callService($domain, 'turn_on', array_merge(['entity_id' => $entityId], $extraData));
    }

    /**
     * Turn off an entity
     */
    public function turnOff(string $entityId): bool
    {
        $parts = explode('.', $entityId);
        $domain = $parts[0] ?? 'homeassistant';
        
        return $this->callService($domain, 'turn_off', ['entity_id' => $entityId]);
    }

    /**
     * Set brightness for a light (0-255)
     */
    public function setBrightness(string $entityId, int $brightness): bool
    {
        return $this->turnOn($entityId, ['brightness' => max(0, min(255, $brightness))]);
    }

    /**
     * Set temperature for climate entity
     */
    public function setTemperature(string $entityId, float $temperature): bool
    {
        return $this->callService('climate', 'set_temperature', [
            'entity_id' => $entityId,
            'temperature' => $temperature,
        ]);
    }

    /**
     * Get entities with specific attribute
     */
    public function getEntitiesWithAttribute(string $attribute): array
    {
        $allEntities = $this->getEntities();
        return array_values(array_filter($allEntities, function ($entity) use ($attribute) {
            return isset($entity['attributes'][$attribute]);
        }));
    }

    /**
     * Get entities by area
     */
    public function getEntitiesByArea(string $areaName): array
    {
        $allEntities = $this->getEntities();
        $areaNameLower = strtolower($areaName);
        
        return array_values(array_filter($allEntities, function ($entity) use ($areaNameLower) {
            // Check area_id attribute
            if (isset($entity['attributes']['area_id'])) {
                if (strtolower($entity['attributes']['area_id']) === $areaNameLower) {
                    return true;
                }
            }
            
            // Check friendly_name for room name
            if (isset($entity['attributes']['friendly_name'])) {
                $friendlyName = strtolower($entity['attributes']['friendly_name']);
                if (stripos($friendlyName, $areaNameLower) !== false) {
                    return true;
                }
            }
            
            return false;
        }));
    }

    /**
     * Make a GET request to Home Assistant API
     */
    protected function makeRequest(string $endpoint): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->get($this->url . $endpoint);
    }

    /**
     * Make a POST request to Home Assistant API
     */
    protected function postRequest(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->url . $endpoint, $data);
    }
}
