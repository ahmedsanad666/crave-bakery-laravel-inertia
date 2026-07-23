<script setup>
import { computed } from 'vue';
import {
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

const onPriceInput = (event) => {
    emit('update:priceMax', Number(event.target.value));
};
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
                <li v-for="option in categoryOptions" :key="String(option.id)">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded px-xs py-1 font-sans text-body-sm transition-all hover:translate-x-1"
                        :class="
                            isCategoryActive(option)
                                ? 'font-bold text-secondary'
                                : 'text-on-surface-variant hover:bg-surface-container-high'
                        "
                        @click="
                            emit(
                                'update:categoryId',
                                option.id == null ? null : option.id,
                            )
                        "
                    >
                        <span>{{ option.name }}</span>
                        <span>({{ option.products_count }})</span>
                    </button>
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
