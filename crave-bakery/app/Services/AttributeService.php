<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttributeService
{
    /**
     * @return Collection<int, Attribute>
     */
    public function list(?string $search = null): Collection
    {
        $attributes = Attribute::query()
            ->search($search)
            ->with(['attributeValues'])
            ->withCount('attributeValues')
            ->ordered()
            ->get();

        foreach ($attributes as $attribute) {
            $attribute->setAttribute('products_count', $this->productsCount($attribute));
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Attribute
    {
        return DB::transaction(function () use ($data) {
            $attribute = Attribute::query()->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'display_type' => $data['display_type'],
                'sort_order' => $data['sort_order'] ?? $this->nextSortOrder(),
            ]);

            $this->syncValues($attribute, $data['values'] ?? []);

            return $attribute->load('attributeValues')->loadCount('attributeValues');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Attribute $attribute, array $data): Attribute
    {
        return DB::transaction(function () use ($attribute, $data) {
            $attribute->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'display_type' => $data['display_type'],
                'sort_order' => $data['sort_order'] ?? $attribute->sort_order,
            ]);

            $this->syncValues($attribute, $data['values'] ?? []);

            return $attribute->fresh(['attributeValues'])->loadCount('attributeValues');
        });
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                Attribute::whereKey($id)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }

    public function productsCount(Attribute $attribute): int
    {
        return Product::query()
            ->whereHas('attributeValues', fn ($query) => $query->where('attribute_id', $attribute->id))
            ->count();
    }

    /**
     * @param  array<int, array<string, mixed>>  $values
     */
    private function syncValues(Attribute $attribute, array $values): void
    {
        $keptIds = [];

        foreach (array_values($values) as $index => $valueData) {
            $payload = [
                'value' => $valueData['value'],
                'color_swatch' => $valueData['color_swatch'] ?? null,
                'sort_order' => $valueData['sort_order'] ?? ($index + 1),
            ];

            if (! empty($valueData['id'])) {
                /** @var AttributeValue|null $existing */
                $existing = $attribute->attributeValues()
                    ->whereKey($valueData['id'])
                    ->first();

                if ($existing) {
                    $existing->update($payload);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }

            $created = $attribute->attributeValues()->create($payload);
            $keptIds[] = $created->id;
        }

        $attribute->attributeValues()
            ->when(
                count($keptIds) > 0,
                fn ($query) => $query->whereNotIn('id', $keptIds),
            )
            ->delete();
    }

    private function nextSortOrder(): int
    {
        return ((int) Attribute::query()->max('sort_order')) + 1;
    }
}
