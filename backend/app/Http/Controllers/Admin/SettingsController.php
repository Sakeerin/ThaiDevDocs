<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Get all settings.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($group = $request->input('group')) {
            $query->group($group);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        // Group by group name
        $grouped = $settings->groupBy('group')->map(function ($items) {
            return $items->mapWithKeys(function ($item) {
                return [$item->key => [
                    'value' => $this->castValue($item->value, $item->type),
                    'type' => $item->type,
                    'description' => $item->description,
                    'is_public' => $item->is_public,
                ]];
            });
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.group' => ['required', 'string', 'max:100'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['sometimes', 'in:string,integer,boolean,json,text'],
            'settings.*.description' => ['sometimes', 'nullable', 'string'],
            'settings.*.is_public' => ['sometimes', 'boolean'],
        ]);

        foreach ($validated['settings'] as $setting) {
            $value = $setting['value'];

            // Encode JSON values
            if (($setting['type'] ?? 'string') === 'json' && is_array($value)) {
                $value = json_encode($value);
            }

            Setting::updateOrCreate(
                [
                    'group' => $setting['group'],
                    'key' => $setting['key'],
                ],
                [
                    'value' => $value,
                    'type' => $setting['type'] ?? 'string',
                    'description' => $setting['description'] ?? null,
                    'is_public' => $setting['is_public'] ?? false,
                ]
            );

            // Clear cache
            Cache::forget('setting.' . $setting['key']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);
    }

    /**
     * Cast value based on type.
     */
    protected function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
