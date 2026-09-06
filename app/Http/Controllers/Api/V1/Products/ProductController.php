<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\Products\ProductCollection;
use App\Http\Resources\Products\ProductImageResource;
use App\Http\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images'])
            ->when(! $request->user(), fn ($q) => $q->active())
            ->when($request->category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->category)))
            ->when($request->boolean('featured'), fn ($q) => $q->featured())
            ->when($request->search, fn ($q) => $q->where(function ($sq) use ($request) {
                $sq->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            }))
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        $products = $query->paginate(15);

        return $this->paginatedResponse($products, new ProductCollection($products));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $product = Product::with(['category', 'images'])->where('slug', $slug)->firstOrFail();

        $this->authorize('view', $product);

        return $this->successResponse(new ProductResource($product));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), null);
        if (empty($data['sku'])) {
            $data['sku'] = 'HB-'.strtoupper(Str::random(6));
        }

        $product = Product::create($data);

        return $this->successResponse(
            new ProductResource($product->load('category')),
            'Producto creado correctamente.',
            201
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $product->id);
        }

        $product->update($data);

        return $this->successResponse(
            new ProductResource($product->fresh(['category', 'images'])),
            'Producto actualizado correctamente.'
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->update(['status' => 0]);

        return $this->successResponse(null, 'Producto desactivado correctamente.');
    }

    public function uploadImages(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $uploaded = [];
        $hasCover = $product->images()->where('is_cover', 1)->exists();

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('products', 'public');
            $image = ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_cover' => ! $hasCover && $index === 0 ? 1 : 0,
                'sort_order' => $product->images()->max('sort_order') + $index + 1,
            ]);
            $hasCover = true;
            $uploaded[] = new ProductImageResource($image);
        }

        return $this->successResponse($uploaded, 'Imágenes subidas correctamente.', 201);
    }

    public function destroyImage(Product $product, ProductImage $image): JsonResponse
    {
        $this->authorize('update', $product);

        if ($image->product_id !== $product->id) {
            return $this->errorResponse('Imagen no pertenece a este producto.', 403);
        }

        Storage::disk('public')->delete($image->path);
        $wasCover = $image->is_cover;
        $image->delete();

        if ($wasCover) {
            $product->images()->oldest('sort_order')->first()?->update(['is_cover' => 1]);
        }

        return $this->successResponse(null, 'Imagen eliminada correctamente.');
    }

    public function setCover(Product $product, ProductImage $image): JsonResponse
    {
        $this->authorize('update', $product);

        if ($image->product_id !== $product->id) {
            return $this->errorResponse('Imagen no pertenece a este producto.', 403);
        }

        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_cover' => 0]);
            $image->update(['is_cover' => 1]);
        });

        return $this->successResponse(
            new ProductImageResource($image->fresh()),
            'Imagen de portada actualizada.'
        );
    }

    private function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $original = $slug;
        $count = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
