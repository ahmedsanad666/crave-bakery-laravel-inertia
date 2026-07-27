<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\AdminProductResource;
use App\Http\Resources\AttributeResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\AttributeService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly AttributeService $attributeService,
    ) {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'stock_status' => $request->string('stock_status')->toString(),
            'category_id' => $request->input('category_id'),
            'featured' => $request->input('featured'),
            'per_page' => $request->integer('per_page', 15),
        ];

        $products = $this->productService->paginate($filters);

        return Inertia::render('Admin/Products/Index', [
            'products' => AdminProductResource::collection($products),
            'stats' => $this->productService->stats(),
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
                'stock_status' => $filters['stock_status'],
                'category_id' => $filters['category_id'] !== null && $filters['category_id'] !== ''
                    ? (int) $filters['category_id']
                    : null,
                'featured' => $filters['featured'],
            ],
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categoryOptions' => $this->categoryOptions(),
            'attributeOptions' => $this->attributeOptions(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create(
            $request->safe()->except(['thumbnail', 'og_image', 'images', 'remove_image_ids']),
            $request->file('thumbnail'),
            $request->file('og_image'),
            $request->file('images') ?? [],
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        $product->load(['categories', 'attributeValues', 'images']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => (new AdminProductResource($product))->resolve(),
            'categoryOptions' => $this->categoryOptions(),
            'attributeOptions' => $this->attributeOptions(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update(
            $product,
            $request->safe()->except(['thumbnail', 'og_image', 'images', 'remove_image_ids']),
            $request->file('thumbnail'),
            $request->file('og_image'),
            $request->file('images') ?? [],
            $request->validated('remove_image_ids') ?? [],
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function attributeOptions(): array
    {
        return AttributeResource::collection(
            $this->attributeService->list()
        )->resolve();
    }
}
