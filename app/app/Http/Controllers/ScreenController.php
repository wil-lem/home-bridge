<?php

namespace App\Http\Controllers;

use App\Services\ScreenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    protected ScreenService $screen;

    public function __construct(ScreenService $screen)
    {
        $this->screen = $screen;
    }

    /**
     * Return all screens for the requesting device.
     */
    public function all(Request $request): JsonResponse
    {
        $request->validate([
            'hardware_id' => 'required|string',
            'screen_size' => 'required|array',
            'screen_size.width' => 'required|integer',
            'screen_size.height' => 'required|integer',
        ]);

        $screens = $this->screen->getAllScreens(
            $request->input('hardware_id'),
            $request->input('screen_size'),
        );

        return response()->json($screens);
    }
}
