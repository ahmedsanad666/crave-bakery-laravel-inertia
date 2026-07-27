<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderAttributeRequest;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttributeController extends Controller
{
    public function __construct(
        private readonly AttributeService $attributeService,
    ) {
        $this->authorizeResource(Attribute::class, 'attribute');
    }

    public function index(Request $request): Response
    {
        $attributes = $this->attributeService->list(
            $request->string('search')->toString() ?: null,
        );

        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => AttributeResource::collection($attributes)->resolve(),
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        $this->attributeService->create($request->validated());

        return redirect()
            ->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $this->attributeService->update($attribute, $request->validated());

        return redirect()
            ->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->delete();

        return redirect()
            ->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    public function reorder(ReorderAttributeRequest $request): RedirectResponse
    {
        $this->attributeService->reorder($request->validated('ordered_ids'));

        return back()->with('success', 'Attribute order updated.');
    }
}
