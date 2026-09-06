<?php

namespace App\Http\Controllers\Api\V1\TrunkShows;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrunkShows\StoreTrunkShowRequest;
use App\Http\Requests\TrunkShows\UpdateTrunkShowRequest;
use App\Http\Resources\TrunkShows\TrunkShowResource;
use App\Models\TrunkShow;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrunkShowController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = TrunkShow::query()
            ->when(! $request->user(), fn ($q) => $q->active())
            ->when($request->filter === 'upcoming', fn ($q) => $q->upcoming())
            ->when($request->filter === 'past', fn ($q) => $q->past())
            ->orderByDesc('start_date');

        $shows = $query->paginate(20);

        return $this->paginatedResponse($shows, TrunkShowResource::collection($shows));
    }

    public function show(string $slug): JsonResponse
    {
        $show = TrunkShow::where('slug', $slug)->firstOrFail();

        return $this->successResponse(new TrunkShowResource($show));
    }

    public function store(StoreTrunkShowRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $show = TrunkShow::create($data);

        return $this->successResponse(new TrunkShowResource($show), 'Trunk Show creado correctamente.', 201);
    }

    public function update(UpdateTrunkShowRequest $request, TrunkShow $trunkShow): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['title']) && $data['title'] !== $trunkShow->title) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $trunkShow->id);
        }

        $trunkShow->update($data);

        return $this->successResponse(new TrunkShowResource($trunkShow->fresh()), 'Trunk Show actualizado.');
    }

    public function destroy(TrunkShow $trunkShow): JsonResponse
    {
        $trunkShow->update(['status' => 0]);

        return $this->successResponse(null, 'Trunk Show desactivado.');
    }

    public function uploadImage(Request $request, TrunkShow $trunkShow): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($trunkShow->image) {
            app(ImageService::class)->delete($trunkShow->image);
        }

        $result = app(ImageService::class)->process($request->file('image'), 'trunk-shows');
        $trunkShow->update(['image' => $result['path']]);

        return $this->successResponse(new TrunkShowResource($trunkShow->fresh()), 'Imagen actualizada.');
    }

    public function destroyImage(TrunkShow $trunkShow): JsonResponse
    {
        if (! $trunkShow->image) {
            return $this->errorResponse('El Trunk Show no tiene imagen.', 404);
        }

        app(ImageService::class)->delete($trunkShow->image);
        $trunkShow->update(['image' => null]);

        return $this->successResponse(null, 'Imagen eliminada.');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $original = $slug;
        $count = 1;

        while (
            TrunkShow::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
