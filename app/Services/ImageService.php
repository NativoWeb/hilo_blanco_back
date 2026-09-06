<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Sube imagen a Cloudinary (CDN) o a storage local según configuración.
     * Retorna ['path' => 'url_o_path', 'public_id' => 'cloudinary_id_o_null'].
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
        if ($this->useCloudinary() && str_starts_with($path, 'http')) {
            $publicId = $this->extractPublicId($path);
            if ($publicId) {
                Cloudinary::destroy($publicId);
            }

            return;
        }

        // Local: eliminar original + thumbnail
        Storage::disk('public')->delete($path);
        $dir = dirname($path);
        $file = basename($path);
        Storage::disk('public')->delete("{$dir}/thumbs/{$file}");
    }

    /**
     * Genera URL del thumbnail.
     * Cloudinary: transforma vía URL (w_400, calidad auto, formato auto).
     * Local: convención {dir}/thumbs/{file}.
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
     * Si es URL completa (Cloudinary), la devuelve tal cual.
     * Si es path relativo (local), genera con asset().
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
        return ! empty(config('cloudinary.cloud_url'));
    }

    private function processCloudinary(UploadedFile $file, string $directory): array
    {
        $result = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'hiloblanco/'.$directory,
            'transformation' => [
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
        ]);

        return [
            'path' => $result->getSecurePath(),
            'public_id' => $result->getPublicId(),
        ];
    }

    private function processLocal(UploadedFile $file, string $directory): array
    {
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = \Illuminate\Support\Str::random(40).'.'.$extension;

        // Redimensionar si intervention/image está disponible
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

            // Thumbnail
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
        // URL: https://res.cloudinary.com/{cloud}/image/upload/v123/hiloblanco/products/abc.jpg
        if (preg_match('#/upload/(?:v\d+/)?(.+)\.\w+$#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
