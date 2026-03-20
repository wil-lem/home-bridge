<?php

namespace App\Http\Controllers;

use App\Services\KitchenOwlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenOwlController extends Controller
{
    protected KitchenOwlService $kitchenOwl;

    private const DEFAULT_SHOPPING_LIST_ID = 1;

    public function __construct(KitchenOwlService $kitchenOwl)
    {
        $this->kitchenOwl = $kitchenOwl;
    }

    /**
     * Get OpenWebUI tool schema
     */
    public function toolSchema(): JsonResponse
    {
        $baseUrl = config('app.external_api_url') . '/kitchenowl';
        
        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Kitchen Owl API',
                'description' => 'API for managing Kitchen Owl shopping lists, items, and recipes',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                ],
            ],
            'paths' => [
                '/shopping-list-items' => [
                    'get' => [
                        'summary' => 'Get shopping list items',
                        'description' => 'Call this to see what items are currently on the shopping list. Returns all items that need to be purchased. Use this when the user asks "what\'s on my shopping list" or "show my shopping list".',
                        'operationId' => 'getShoppingListItems',
                        'responses' => [
                            '200' => [
                                'description' => 'Shopping list items retrieved successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'items' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'name' => ['type' => 'string'],
                                                            'description' => ['type' => 'string'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'example' => [
                                            'items' => [
                                                ['id' => 1, 'name' => 'Milk', 'description' => '2% fat'],
                                                ['id' => 2, 'name' => 'Bread', 'description' => 'Whole wheat'],
                                                ['id' => 3, 'name' => 'Eggs', 'description' => ''],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'post' => [
                        'summary' => 'Add item to shopping list',
                        'description' => 'Call this to add a new item to the shopping list. Use when the user says "add X to my shopping list" or "I need to buy X". Optionally include a description for details like quantity or brand.',
                        'operationId' => 'addItemToShoppingList',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name'],
                                        'properties' => [
                                            'name' => [
                                                'type' => 'string',
                                                'description' => 'Name of the item to add',
                                            ],
                                            'description' => [
                                                'type' => 'string',
                                                'description' => 'Optional details like quantity, brand, or size',
                                            ],
                                        ],
                                    ],
                                    'example' => [
                                        'name' => 'Milk',
                                        'description' => '2 liters, 2% fat',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Item added to shopping list successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                        'example' => [
                                            'success' => true,
                                            'message' => 'Item added successfully',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/shopping-list-items/{itemId}' => [
                    'delete' => [
                        'summary' => 'Remove item from shopping list',
                        'description' => 'Call this to remove an item from the shopping list. Use when the user says "remove X from my list" or "delete the milk". The itemId must be obtained first by calling getShoppingListItems to see all items and their IDs.',
                        'operationId' => 'removeItemFromShoppingList',
                        'parameters' => [
                            [
                                'name' => 'itemId',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                                'description' => 'ID of the item to remove (get this from getShoppingListItems)',
                                'example' => 1,
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Item removed from shopping list successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'success' => ['type' => 'boolean'],
                                                'message' => ['type' => 'string'],
                                            ],
                                        ],
                                        'example' => [
                                            'success' => true,
                                            'message' => 'Item removed successfully',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/recipes' => [
                    'get' => [
                        'summary' => 'Get all recipes',
                        'description' => 'Call this to see all available recipes in Kitchen Owl. Returns a list of recipes with basic information. Use when the user asks "what recipes do I have" or "show me recipes".',
                        'operationId' => 'getRecipes',
                        'responses' => [
                            '200' => [
                                'description' => 'List of recipes retrieved successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'recipes' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'name' => ['type' => 'string'],
                                                            'description' => ['type' => 'string'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'example' => [
                                            'recipes' => [
                                                ['id' => 1, 'name' => 'Spaghetti Carbonara', 'description' => 'Classic Italian pasta'],
                                                ['id' => 2, 'name' => 'Chicken Curry', 'description' => 'Spicy Indian curry'],
                                                ['id' => 3, 'name' => 'Chocolate Cake', 'description' => 'Rich dessert'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/recipes/{recipeId}' => [
                    'get' => [
                        'summary' => 'Get recipe details',
                        'description' => 'Call this to get full details about a specific recipe including ingredients and instructions. Use when the user asks "show me the recipe for X" or "how do I make X". First call getRecipes to find the recipe ID.',
                        'operationId' => 'getRecipe',
                        'parameters' => [
                            [
                                'name' => 'recipeId',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                                'description' => 'ID of the recipe (get this from getRecipes)',
                                'example' => 1,
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Recipe details retrieved successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'recipe' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer'],
                                                        'name' => ['type' => 'string'],
                                                        'description' => ['type' => 'string'],
                                                        'ingredients' => ['type' => 'array', 'items' => ['type' => 'string']],
                                                        'instructions' => ['type' => 'string'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'example' => [
                                            'recipe' => [
                                                'id' => 1,
                                                'name' => 'Spaghetti Carbonara',
                                                'description' => 'Classic Italian pasta dish',
                                                'ingredients' => ['400g spaghetti', '200g bacon', '4 eggs', '100g parmesan'],
                                                'instructions' => 'Cook pasta. Fry bacon. Mix eggs and cheese. Combine all.',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get all households
     */
    public function households(): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $households = $this->kitchenOwl->getHouseholds();
            return response()->json($households);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all items
     */
    public function items(): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }
        dump('Getting items from Kitchen Owl');

        try {
            $items = $this->kitchenOwl->getItems();
            return response()->json($items);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all shopping lists
     */
    public function shoppingLists(): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $lists = $this->kitchenOwl->getShoppingLists();
            return response()->json($lists);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific shopping list
     */
    public function shoppingList(int $listId): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $list = $this->kitchenOwl->getShoppingList($listId);
            
            if (!$list) {
                return response()->json([
                    'error' => 'Shopping list not found',
                ], 404);
            }

            return response()->json($list);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get shopping list items
     */
    public function shoppingListItems(int $listId): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $items = $this->kitchenOwl->getShoppingListItems($listId);
            return response()->json(['items' => $items]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add an item to a shopping list
     */
    public function addItem(Request $request, int $listId): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        try {
            $success = $this->kitchenOwl->addItemToShoppingList(
                $listId,
                $request->input('name'),
                $request->input('description', '')
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item added successfully',
                ]);
            }

            return response()->json([
                'error' => 'Failed to add item',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove an item from a shopping list
     */
    public function removeItem(int $listId, int $itemId): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $success = $this->kitchenOwl->removeItemFromShoppingList($listId, $itemId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed successfully',
                ]);
            }

            return response()->json([
                'error' => 'Failed to remove item',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all recipes
     */
    public function recipes(): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $recipes = $this->kitchenOwl->getRecipes();
            return response()->json(['recipes' => $recipes]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific recipe
     */
    public function recipe(int $recipeId): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $recipe = $this->kitchenOwl->getRecipe($recipeId);
            
            if (!$recipe) {
                return response()->json([
                    'error' => 'Recipe not found',
                ], 404);
            }

            return response()->json(['recipe' => $recipe]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get household inventory
     */
    public function inventory(): JsonResponse
    {
        if (!$this->kitchenOwl->isConfigured()) {
            return response()->json([
                'error' => 'Kitchen Owl configuration missing',
            ], 500);
        }

        try {
            $inventory = $this->kitchenOwl->getInventory();
            return response()->json($inventory);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default shopping list items (uses list ID 1)
     */
    public function defaultShoppingListItems(): JsonResponse
    {
        return $this->shoppingListItems(self::DEFAULT_SHOPPING_LIST_ID);
    }

    /**
     * Add item to default shopping list (uses list ID 1)
     */
    public function addItemToDefault(Request $request): JsonResponse
    {
        return $this->addItem($request, self::DEFAULT_SHOPPING_LIST_ID);
    }

    /**
     * Remove item from default shopping list (uses list ID 1)
     */
    public function removeItemFromDefault(int $itemId): JsonResponse
    {
        return $this->removeItem(self::DEFAULT_SHOPPING_LIST_ID, $itemId);
    }
}
