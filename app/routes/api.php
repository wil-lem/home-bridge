<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\HomeAssistantController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\LightsController;
use App\Http\Controllers\SwitchesController;
use App\Http\Controllers\SensorsController;
use App\Http\Controllers\ClimateController;
use App\Http\Controllers\KitchenOwlController;
use Illuminate\Support\Facades\Route;

// System status
Route::get('/status', [ApiController::class, 'status']);

// All entities (raw data)
Route::get('/entities', [HomeAssistantController::class, 'entities']);

// Areas
Route::get('/areas', [AreasController::class, 'index']);
Route::get('/areas/{area}/entities', [AreasController::class, 'entities']);

// Lights
Route::get('/lights', [LightsController::class, 'index']);
Route::get('/lights/{entityId}', [LightsController::class, 'show']);
Route::post('/lights/{entityId}/on', [LightsController::class, 'turnOn']);
Route::post('/lights/{entityId}/off', [LightsController::class, 'turnOff']);
Route::post('/lights/{entityId}/brightness', [LightsController::class, 'setBrightness']);

// Switches
Route::get('/switches', [SwitchesController::class, 'index']);
Route::post('/switches/{entityId}/on', [SwitchesController::class, 'turnOn']);
Route::post('/switches/{entityId}/off', [SwitchesController::class, 'turnOff']);

// Sensors
Route::get('/sensors', [SensorsController::class, 'index']);
Route::get('/sensors/battery', [SensorsController::class, 'battery']);
Route::get('/sensors/temperature', [SensorsController::class, 'temperature']);
Route::get('/sensors/humidity', [SensorsController::class, 'humidity']);

// Climate
Route::get('/climate', [ClimateController::class, 'index']);
Route::post('/climate/{entityId}/temperature', [ClimateController::class, 'setTemperature']);

// Kitchen Owl
Route::get('/kitchenowl/tool.json', [KitchenOwlController::class, 'toolSchema']);
Route::get('/kitchenowl/households', [KitchenOwlController::class, 'households']);
Route::get('/kitchenowl/items', [KitchenOwlController::class, 'items']);
Route::get('/kitchenowl/inventory', [KitchenOwlController::class, 'inventory']);
// Simplified endpoints (default to list ID 1)
Route::get('/kitchenowl/shopping-list-items', [KitchenOwlController::class, 'defaultShoppingListItems']);
Route::post('/kitchenowl/shopping-list-items', [KitchenOwlController::class, 'addItemToDefault']);
Route::delete('/kitchenowl/shopping-list-items/{itemId}', [KitchenOwlController::class, 'removeItemFromDefault']);
// Full endpoints with list ID
Route::get('/kitchenowl/shopping-lists', [KitchenOwlController::class, 'shoppingLists']);
Route::get('/kitchenowl/shopping-lists/{listId}', [KitchenOwlController::class, 'shoppingList']);
Route::get('/kitchenowl/shopping-lists/{listId}/items', [KitchenOwlController::class, 'shoppingListItems']);
Route::post('/kitchenowl/shopping-lists/{listId}/items', [KitchenOwlController::class, 'addItem']);
Route::delete('/kitchenowl/shopping-lists/{listId}/items/{itemId}', [KitchenOwlController::class, 'removeItem']);
Route::get('/kitchenowl/recipes', [KitchenOwlController::class, 'recipes']);
Route::get('/kitchenowl/recipes/{recipeId}', [KitchenOwlController::class, 'recipe']);

