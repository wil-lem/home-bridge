<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\HomeAssistantController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\LightsController;
use App\Http\Controllers\SwitchesController;
use App\Http\Controllers\SensorsController;
use App\Http\Controllers\ClimateController;
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

