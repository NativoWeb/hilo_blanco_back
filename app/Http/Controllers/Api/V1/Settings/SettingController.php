<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BulkSettingRequest;
use App\Http\Requests\Settings\UpdateSettingRequest;
use App\Http\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function uploadMedia(Request $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $this->authorize('update', $setting);

        $request->validate([
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
        ]);

        $file = $request->file('media');
        $isVideo = str_starts_with($file->getMimeType(), 'video/');

        if ($isVideo) {
            if (! empty(config('filesystems.disks.cloudinary.url'))) {
                $cloudinary = new \Cloudinary\Cloudinary(config('filesystems.disks.cloudinary.url'));
                $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'hiloblanco/media',
                    'resource_type' => 'video',
                ]);
                $url = $result['secure_url'];
            } else {
                $url = asset('storage/'.$file->store('media', 'public'));
            }
        } else {
            $imageService = app(ImageService::class);
            $result = $imageService->process($file, 'media');
            $url = str_starts_with($result['path'], 'http')
                ? $result['path']
                : asset('storage/'.$result['path']);
        }

        // Eliminar media anterior si existía
        $oldValue = $setting->value;
        if ($oldValue && str_starts_with($oldValue, 'http') && str_contains($oldValue, 'cloudinary')) {
            app(ImageService::class)->delete($oldValue);
        }

        $setting->update(['value' => $url]);

        return $this->successResponse(new SettingResource($setting->fresh()), 'Media actualizado correctamente.');
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
