<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $view = $request->input('view', 'tree');
        $search = $request->input('search');
        $status = $request->input('status');

        $payload = [
            'stats' => $this->categoryService->stats(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'view' => $view,
            ],
            'view' => $view,
        ];

        if ($view === 'list') {
            $categories = Category::query()
                ->with('parent')
                ->withCount('products')
                ->search($search)
                ->status($status)
                ->ordered()
                ->paginate(5)
                ->withQueryString();

            $payload['categories'] = CategoryResource::collection($categories);
            $payload['categoryTree'] = [];
            $payload['rootPagination'] = null;
        } else {
            $treeResult = $this->categoryService->treePaginated($search, $status);

            $payload['categoryTree'] = $treeResult['tree'];
            $payload['rootPagination'] = $treeResult['pagination'];
            $payload['categories'] = [
                'data' => [],
                'links' => [],
                'meta' => [],
            ];
        }

        return Inertia::render('Admin/Categories/Index', $payload);
    }

    /**
     * Immediately persist drag-and-drop tree changes.
     */
    public function reorder(ReorderCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categoryService->reorder(
            $category,
            $request->validated('parent_id'),
            $request->validated('ordered_ids'),
        );

        return back()->with('success', 'Category order updated.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create', [
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'banner_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories/thumbnails', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('categories/banners', 'public');
        }

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        $category->load('parent')->loadCount('products');

        return Inertia::render('Admin/Categories/Edit', [
            'category' => (new CategoryResource($category))->resolve(),
            'parentOptions' => $this->parentOptions($category),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'banner_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->replacePublicImage(
                $category->image,
                $request->file('image'),
                'categories/thumbnails',
            );
        }

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $this->replacePublicImage(
                $category->banner_image,
                $request->file('banner_image'),
                'categories/banners',
            );
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Parent categories for select dropdown (excludes self on edit).
     *
     * @return array<int, array<string, mixed>>
     */
    private function parentOptions(?Category $exclude = null): array
    {
        return CategoryResource::collection(
            Category::query()
                ->when(
                    $exclude,
                    fn ($query) => $query->where('id', '!=', $exclude->id)
                )
                ->orderBy('name')
                ->get()
        )->resolve();
    }

    private function replacePublicImage(?string $currentPath, UploadedFile $file, string $directory): string
    {
        if (
            $currentPath
            && ! str_starts_with($currentPath, 'http://')
            && ! str_starts_with($currentPath, 'https://')
            && ! str_starts_with($currentPath, '/')
        ) {
            Storage::disk('public')->delete($currentPath);
        }

        return $file->store($directory, 'public');
    }
}
