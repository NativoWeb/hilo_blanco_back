<?php

namespace App\Http\Controllers\Api\V1\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Http\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return $this->paginatedResponse($categories, new CategoryCollection($categories));
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $this->authorize('view', $category);

        return $this->successResponse(new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['slug'] = $this->uniqueSlug($data['slug'], 'categories');

        $category = Category::create($data);

        return $this->successResponse(new CategoryResource($category), 'Categoría creada correctamente.', 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $data = $request->validated();

        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), 'categories', $category->id);
        }

        $category->update($data);

        return $this->successResponse(new CategoryResource($category->fresh()), 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->update(['status' => 0]);

        return $this->successResponse(null, 'Categoría desactivada correctamente.');
    }

    private function uniqueSlug(string $slug, string $table, ?int $ignoreId = null): string
    {
        $original = $slug;
        $count = 1;

        while (
            \DB::table($table)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
