<script setup>
import { computed, ref, watch } from 'vue';
import {
    IconChevronDown,
    IconChevronUp,
    IconLayoutGrid,
    IconSearch,
    IconStar,
    IconStarFilled,
} from '@tabler/icons-vue';

const props = defineProps({
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    priceBounds: {
        type: Object,
        default: () => ({ min: 0, max: 0 }),
    },
    search: {
        type: String,
        default: '',
    },
    categoryId: {
        type: [Number, String, null],
        default: null,
    },
    priceMax: {
        type: [Number, String, null],
        default: null,
    },
    minRating: {
        type: [Number, String, null],
        default: null,
    },
    inStock: {
        type: Boolean,
        default: false,
    },
    outOfStock: {
        type: Boolean,
        default: false,
    },
    showHeader: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits([
    'update:search',
    'update:categoryId',
    'update:priceMax',
    'update:minRating',
    'update:inStock',
    'update:outOfStock',
    'clear',
]);

const expandedIds = ref(new Set());

const findAncestorIds = (nodes, targetId, path = []) => {
    for (const node of nodes) {
        if (node.id == null) {
            const nested = findAncestorIds(node.children ?? [], targetId, path);
            if (nested) {
                return nested;
            }
            continue;
        }

        const nextPath = [...path, node.id];

        if (String(node.id) === String(targetId)) {
            return path;
        }

        const nested = findAncestorIds(node.children ?? [], targetId, nextPath);
        if (nested) {
            return nested;
        }
    }

    return null;
};

const expandAncestorsOfActive = () => {
    if (props.categoryId == null || props.categoryId === '') {
        return;
    }

    const ancestors = findAncestorIds(props.categoryOptions, props.categoryId);
    if (!ancestors?.length) {
        return;
    }

    const next = new Set(expandedIds.value);
    ancestors.forEach((id) => next.add(id));
    expandedIds.value = next;
};

watch(
    () => [props.categoryOptions, props.categoryId],
    () => expandAncestorsOfActive(),
    { immediate: true, deep: true },
);

const collectVisibleRows = (nodes, depth = 0) => {
    const rows = [];

    for (const node of nodes) {
        const children = node.children ?? [];
        const hasChildren = children.length > 0;
        const isExpanded =
            node.id != null && expandedIds.value.has(node.id);

        rows.push({
            id: node.id,
            name: node.name,
            products_count: node.products_count ?? 0,
            image: node.image ?? null,
            depth,
            hasChildren,
            isExpanded,
        });

        if (hasChildren && isExpanded) {
            rows.push(...collectVisibleRows(children, depth + 1));
        }
    }

    return rows;
};

const visibleCategoryRows = computed(() =>
    collectVisibleRows(props.categoryOptions),
);

const sliderMax = computed(() => {
    const max = Number(props.priceBounds?.max ?? 0);

    return Math.max(Math.ceil(max), 1);
});

const sliderMin = computed(() => {
    const min = Number(props.priceBounds?.min ?? 0);

    return Math.max(Math.floor(min), 0);
});

const sliderValue = computed(() => {
    if (props.priceMax != null && props.priceMax !== '') {
        return Number(props.priceMax);
    }

    return sliderMax.value;
});

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(value);

const isCategoryActive = (option) => {
    if (option.id == null) {
        return props.categoryId == null || props.categoryId === '';
    }

    return String(props.categoryId) === String(option.id);
};

const toggleExpanded = (categoryId) => {
    if (categoryId == null) {
        return;
    }

    const next = new Set(expandedIds.value);
    if (next.has(categoryId)) {
        next.delete(categoryId);
    } else {
        next.add(categoryId);
    }
    expandedIds.value = next;
};

const selectCategory = (option) => {
    emit('update:categoryId', option.id == null ? null : option.id);
};

const onPriceInput = (event) => {
    emit('update:priceMax', Number(event.target.value));
};

const categoryInitial = (name) =>
    (name?.trim()?.charAt(0) || '?').toUpperCase();
</script>

<template>
    <div class="flex flex-col gap-lg">
        <div v-if="showHeader">
            <h2 class="mb-sm font-serif text-headline-sm text-primary">
                Filters
            </h2>
            <p class="font-sans text-body-sm text-on-surface-variant">
                Refine your selection
            </p>
        </div>

        <div class="relative">
            <input
                :value="search"
                type="text"
                placeholder="Search pastries..."
                class="h-12 w-full rounded-xl border border-border-base bg-white px-md pr-xl font-sans text-body-sm transition-all focus:border-primary focus:outline-none focus:ring-0"
                @input="emit('update:search', $event.target.value)"
            />
            <IconSearch
                :size="20"
                stroke-width="1.5"
                class="pointer-events-none absolute right-3 top-3 text-outline"
            />
        </div>

        <div>
            <h3 class="mb-sm font-sans text-title-lg text-primary">
                Categories
            </h3>
            <ul class="flex flex-col gap-xs">
                <li
                    v-for="row in visibleCategoryRows"
                    :key="`${row.id ?? 'all'}-${row.depth}`"
                >
                    <div
                        class="flex w-full items-center gap-sm rounded-lg px-xs py-1.5 transition-colors"
                        :class="
                            isCategoryActive(row)
                                ? 'bg-secondary/10'
                                : 'hover:bg-surface-container-high'
                        "
                        :style="{ paddingLeft: `${0.25 + row.depth * 0.875}rem` }"
                    >
                        <button
                            type="button"
                            class="flex min-w-0 flex-1 items-center gap-sm text-left font-sans text-body-sm transition-colors"
                            :class="
                                isCategoryActive(row)
                                    ? 'font-bold text-secondary'
                                    : 'text-on-surface-variant'
                            "
                            @click="selectCategory(row)"
                        >
                            <span
                                class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full"
                                :class="
                                    row.image
                                        ? 'bg-surface-container'
                                        : 'bg-primary text-on-primary'
                                "
                            >
                                <img
                                    v-if="row.image"
                                    :src="row.image"
                                    :alt="row.name"
                                    class="h-full w-full object-cover"
                                />
                                <IconLayoutGrid
                                    v-else-if="row.id == null"
                                    :size="16"
                                    stroke-width="1.5"
                                />
                                <span
                                    v-else
                                    class="font-sans text-[11px] font-bold"
                                >
                                    {{ categoryInitial(row.name) }}
                                </span>
                            </span>
                            <span class="min-w-0 flex-1 truncate">
                                {{ row.name }}
                            </span>
                        </button>

                        <button
                            v-if="row.hasChildren"
                            type="button"
                            class="flex size-8 shrink-0 items-center justify-center rounded-md text-on-surface-variant transition-colors hover:bg-white/80 hover:text-primary"
                            :aria-expanded="row.isExpanded"
                            :aria-label="
                                row.isExpanded
                                    ? `Collapse ${row.name}`
                                    : `Expand ${row.name}`
                            "
                            @click.stop="toggleExpanded(row.id)"
                        >
                            <IconChevronUp
                                v-if="row.isExpanded"
                                :size="18"
                                stroke-width="1.5"
                            />
                            <IconChevronDown
                                v-else
                                :size="18"
                                stroke-width="1.5"
                            />
                        </button>
                    </div>
                </li>
            </ul>
        </div>

        <div>
            <h3 class="mb-sm font-sans text-title-lg text-primary">
                Price Range
            </h3>
            <input
                type="range"
                class="range-accent"
                :min="sliderMin"
                :max="sliderMax"
                :value="sliderValue"
                :step="1"
                @input="onPriceInput"
            />
            <div
                class="mt-sm flex justify-between font-sans text-body-sm text-on-surface-variant"
            >
                <span>{{ formatMoney(sliderMin) }}</span>
                <span>{{ formatMoney(sliderMax) }}</span>
            </div>
        </div>

        <div>
            <h3 class="mb-sm font-sans text-title-lg text-primary">Ratings</h3>
            <label class="group flex cursor-pointer items-center gap-sm">
                <input
                    type="checkbox"
                    class="rounded border-border-base text-accent focus:ring-accent"
                    :checked="Number(minRating) === 4"
                    @change="
                        emit(
                            'update:minRating',
                            $event.target.checked ? 4 : null,
                        )
                    "
                />
                <span class="flex gap-0.5 text-[#FCB001]">
                    <IconStarFilled
                        v-for="i in 4"
                        :key="i"
                        :size="18"
                    />
                    <IconStar :size="18" class="text-outline" />
                </span>
                <span class="font-sans text-body-sm text-on-surface-variant">
                    &amp; Up
                </span>
            </label>
        </div>

        <div>
            <h3 class="mb-sm font-sans text-title-lg text-primary">
                Availability
            </h3>
            <div class="flex flex-col gap-sm">
                <label class="flex cursor-pointer items-center gap-sm">
                    <input
                        type="checkbox"
                        class="rounded border-border-base text-accent focus:ring-accent"
                        :checked="inStock"
                        @change="emit('update:inStock', $event.target.checked)"
                    />
                    <span class="font-sans text-body-sm text-on-surface">
                        In Stock
                    </span>
                </label>
                <label class="flex cursor-pointer items-center gap-sm">
                    <input
                        type="checkbox"
                        class="rounded border-border-base text-accent focus:ring-accent"
                        :checked="outOfStock"
                        @change="
                            emit('update:outOfStock', $event.target.checked)
                        "
                    />
                    <span class="font-sans text-body-sm text-on-surface">
                        Out of Stock
                    </span>
                </label>
            </div>
        </div>

        <button
            type="button"
            class="mt-sm text-left font-sans text-body-sm font-bold text-accent hover:underline"
            @click="emit('clear')"
        >
            Clear all filters
        </button>
    </div>
</template>
