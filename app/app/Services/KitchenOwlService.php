<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class KitchenOwlService
{
    protected string $url;
    protected string $accessToken;
    protected ?int $householdId = null;

    public function __construct()
    {
        $this->url = config('kitchenowl.url');
        $this->accessToken = config('kitchenowl.access_token');
        $this->householdId = config('kitchenowl.household_id');
    }

    /**
     * Check if Kitchen Owl is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->accessToken) && !empty($this->householdId);
    }

    /**
     * Get the configured household ID
     */
    protected function getHouseholdId(): ?int
    {
        return $this->householdId;
    }

    /**
     * Get all households
     */
    public function getHouseholds(): array
    {
        $response = $this->makeRequest('/api/household');
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch households from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Get all items from Kitchen Owl
     */
    public function getItems(): array
    {
        $householdId = $this->getHouseholdId();
        if (!$householdId) {
            throw new \Exception('No household found');
        }

        $response = $this->makeRequest("/api/household/{$householdId}/item");
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch items from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Get shopping lists
     */
    public function getShoppingLists(): array
    {
        $householdId = $this->getHouseholdId();
        if (!$householdId) {
            throw new \Exception('No household found');
        }

        $response = $this->makeRequest("/api/household/{$householdId}/shoppinglist");
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch shopping lists from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Get a specific shopping list
     */
    public function getShoppingList(int $listId): ?array
    {
        $response = $this->makeRequest("/api/shoppinglist/{$listId}");
        
        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Get shopping list items
     */
    public function getShoppingListItems(int $listId): array
    {
        $response = $this->makeRequest("/api/shoppinglist/{$listId}/items");
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch shopping list items from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Add an item to a shopping list
     */
    public function addItemToShoppingList(int $listId, string $itemName, ?string $description = ''): bool
    {
        $response = $this->postRequest("/api/shoppinglist/{$listId}/add-item-by-name", [
            'name' => $itemName,
            'description' => $description,
        ]);

        return $response->successful();
    }

    /**
     * Remove an item from a shopping list
     */
    public function removeItemFromShoppingList(int $listId, int $itemId): bool
    {
        $response = $this->deleteRequest("/api/shoppinglist/{$listId}/item", [
            'item_id' => $itemId,
        ]);

        return $response->successful();
    }

    /**
     * Get recipes
     */
    public function getRecipes(): array
    {
        $householdId = $this->getHouseholdId();
        if (!$householdId) {
            throw new \Exception('No household found');
        }

        $response = $this->makeRequest("/api/household/{$householdId}/recipe");
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch recipes from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Get a specific recipe
     */
    public function getRecipe(int $recipeId): ?array
    {
        $householdId = $this->getHouseholdId();
        if (!$householdId) {
            return null;
        }

        $response = $this->makeRequest("/api/household/{$householdId}/recipe/{$recipeId}");
        
        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Get household items/inventory
     */
    public function getInventory(): array
    {
        $householdId = $this->getHouseholdId();
        if (!$householdId) {
            throw new \Exception('No household found');
        }

        $response = $this->makeRequest("/api/household/{$householdId}/item");
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch inventory from Kitchen Owl. Status: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Make a GET request to Kitchen Owl API
     */
    protected function makeRequest(string $endpoint): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
        ])->get($this->url . $endpoint);
    }

    /**
     * Make a POST request to Kitchen Owl API
     */
    protected function postRequest(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
        ])->post($this->url . $endpoint, $data);
    }

    /**
     * Make a DELETE request to Kitchen Owl API
     */
    protected function deleteRequest(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
        ])->delete($this->url . $endpoint, $data);
    }
}
