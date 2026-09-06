<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Sube imagen a Cloudinary (CDN) o a storage local según configuración.
     */
    public function process(UploadedFile $file, string $directory = 'products'): array
    {
        if ($this->useCloudinary()) {
            return $this->processCloudinary($file, $directory);
        }

        return $this->processLocal($file, $directory);
    }

    /**
     * Elimina imagen de Cloudinary o storage local.
     */
    public function delete(string $path): void
    {
        if (str_starts_with($path, 'http') && str_contains($path, 'cloudinary')) {
            $publicId = $this->extractPublicId($path);
            if ($publicId && $this->useCloudinary()) {
                $this->cloudinaryClient()->uploadApi()->destroy($publicId);
            }

            return;
        }

        Storage::disk('public')->delete($path);
        $dir = dirname($path);
        $file = basename($path);
        Storage::disk('public')->delete("{$dir}/thumbs/{$file}");
    }

    /**
     * Genera URL del thumbnail.
     */
    public static function thumbnailUrl(string $path): string
    {
        if (str_starts_with($path, 'http') && str_contains($path, 'cloudinary')) {
            return str_replace('/upload/', '/upload/w_400,c_scale,q_auto,f_auto/', $path);
        }

        $dir = dirname($path);
        $file = basename($path);

        return asset("storage/{$dir}/thumbs/{$file}");
    }

    /**
     * Genera la URL pública de una imagen.
     */
    public static function publicUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    private function useCloudinary(): bool
    {
        return ! empty(config('filesystems.disks.cloudinary.cloud'));
    }

    private function cloudinaryClient(): Cloudinary
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => config('filesystems.disks.cloudinary.cloud'),
                'api_key' => config('filesystems.disks.cloudinary.key'),
                'api_secret' => config('filesystems.disks.cloudinary.secret'),
            ],
            'url' => ['secure' => true],
        ]);
    }

    private function processCloudinary(UploadedFile $file, string $directory): array
    {
        $result = $this->cloudinaryClient()->uploadApi()->upload($file->getRealPath(), [
            'folder' => 'hiloblanco/'.$directory,
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ]);

        return [
            'path' => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    private function processLocal(UploadedFile $file, string $directory): array
    {
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = Str::random(40).'.'.$extension;

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            $image = $manager->read($file->getPathname());
            if ($image->width() > 1920) {
                $image->scaleDown(width: 1920);
            }

            $path = "{$directory}/{$filename}";
            $encoded = match ($extension) {
                'png' => $image->toPng(),
                'webp' => $image->toWebp(85),
                default => $image->toJpeg(85),
            };
            Storage::disk('public')->put($path, (string) $encoded);

            $thumb = $manager->read($file->getPathname());
            $thumb->scaleDown(width: 400);
            $thumbEncoded = match ($extension) {
                'png' => $thumb->toPng(),
                'webp' => $thumb->toWebp(80),
                default => $thumb->toJpeg(80),
            };
            Storage::disk('public')->put("{$directory}/thumbs/{$filename}", (string) $thumbEncoded);
        } else {
            $path = $file->store($directory, 'public');
        }

        return [
            'path' => $path,
            'public_id' => null,
        ];
    }

    private function extractPublicId(string $url): ?string
    {
        if (preg_match('#/upload/(?:v\d+/)?(.+)\.\w+$#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
