<?php

namespace App\Services;

use App\Http\Resources\CategoryTreeResource;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    /**
     * @return array{total: int, active: int, empty: int}
     */
    public function stats(): array
    {
        return [
            'total' => Category::count(),
            'active' => Category::where('status', 'active')->count(),
            'empty' => Category::doesntHave('products')->count(),
        ];
    }

    /**
     * Build a paginated tree — only root categories are paginated; children load in full.
     *
     * @return array{tree: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function treePaginated(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 5,
    ): array {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $filtered = $this->filterForTree($categories, $search, $status);

        $roots = $filtered
            ->whereNull('parent_id')
            ->sortBy(fn (Category $category) => sprintf('%05d-%s', $category->sort_order, $category->name))
            ->values();

        $page = Paginator::resolveCurrentPage('page');

        $paginator = new LengthAwarePaginator(
            $roots->forPage($page, $perPage)->values(),
            $roots->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->except('page'),
            ],
        );

        $tree = collect($paginator->items())
            ->map(fn (Category $root) => array_merge(
                (new CategoryTreeResource($root))->resolve(),
                ['children' => $this->buildTree($filtered, $root->id)],
            ))
            ->all();

        return [
            'tree' => $tree,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Build a nested category tree for the admin hierarchy view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tree(?string $search = null, ?string $status = null): array
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $filtered = $this->filterForTree($categories, $search, $status);

        return $this->buildTree($filtered);
    }

    /**
     * Apply drag-and-drop reorder immediately (parent + sibling sort_order).
     *
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(Category $category, ?int $parentId, array $orderedIds): void
    {
        if ($this->wouldCreateCycle($category, $parentId)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Cannot move a category under one of its descendants.',
            ]);
        }

        DB::transaction(function () use ($parentId, $orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                Category::whereKey($id)->update([
                    'parent_id' => $parentId,
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }

    public function wouldCreateCycle(Category $category, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return false;
        }

        if ($newParentId === $category->id) {
            return true;
        }

        $current = Category::find($newParentId);

        while ($current) {
            if ($current->id === $category->id) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $categories, ?int $parentId = null): array
    {
        return $categories
            ->where('parent_id', $parentId)
            ->sortBy(fn (Category $category) => sprintf('%05d-%s', $category->sort_order, $category->name))
            ->values()
            ->map(function (Category $category) use ($categories) {
                return array_merge(
                    (new CategoryTreeResource($category))->resolve(),
                    [
                        'children' => $this->buildTree($categories, $category->id),
                    ]
                );
            })
            ->all();
    }

    /**
     * Keep matching nodes and their ancestors so the tree stays intact when filtering.
     */
    private function filterForTree(
        Collection $categories,
        ?string $search,
        ?string $status,
    ): Collection {
        if (blank($search) && blank($status)) {
            return $categories;
        }

        $matching = $categories->filter(function (Category $category) use ($search, $status) {
            if (filled($status) && $category->status !== $status) {
                return false;
            }

            if (blank($search)) {
                return true;
            }

            $needle = strtolower($search);

            return str_contains(strtolower($category->name), $needle)
                || str_contains(strtolower($category->slug), $needle);
        });

        $visibleIds = collect();

        foreach ($matching as $category) {
            $visibleIds->push($category->id);

            $parentId = $category->parent_id;

            while ($parentId) {
                $visibleIds->push($parentId);
                $parentId = $categories->firstWhere('id', $parentId)?->parent_id;
            }
        }

        return $categories->whereIn('id', $visibleIds->unique()->all());
    }
}
