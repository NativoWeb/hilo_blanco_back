<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BulkSettingRequest;
use App\Http\Requests\Settings\UpdateSettingRequest;
use App\Http\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return $this->successResponse(SettingResource::collection($settings));
    }

    public function public(): JsonResponse
    {
        $settings = Setting::public()->orderBy('group')->orderBy('key')->get();

        return $this->successResponse(SettingResource::collection($settings));
    }

    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $this->authorize('update', $setting);

        $setting->update($request->validated());

        return $this->successResponse(new SettingResource($setting->fresh()), 'Configuración actualizada.');
    }

    public function bulk(BulkSettingRequest $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        DB::transaction(function () use ($request) {
            foreach ($request->validated()['settings'] as $item) {
                Setting::where('key', $item['key'])->update(['value' => $item['value']]);
            }
        });

        $keys = collect($request->validated()['settings'])->pluck('key');
        $settings = Setting::whereIn('key', $keys)->get();

        return $this->successResponse(
            SettingResource::collection($settings),
            'Configuraciones actualizadas correctamente.'
        );
    }
}
