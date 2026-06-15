<?php

namespace App\Services;

class ScreenService
{
    public function getAllScreens(string $hardwareId, array $screenSize): array
    {
        return [
            'screens' => [
                [
                    'id' => 'loading',
                    'title' => 'Main',
                    'description' => 'Ready',
                    'items' => [
                        [
                            'type' => 'text',
                            'x' => 88,
                            'y' => 104,
                            'size' => 3,
                            'color' => 65535,
                            'text' => 'loading',
                        ],
                    ],
                    'interactions' => [],
                ],
            ],
        ];
    }
}
