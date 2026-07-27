<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { IconMinus, IconPlus, IconTrash } from '@tabler/icons-vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    isLast: {
        type: Boolean,
        default: false,
    },
});

const { update, remove } = useCart();

const product = computed(() => props.item.product ?? {});

const attributeSummary = computed(() => {
    const attrs = props.item.selected_attributes ?? [];

    if (!attrs.length) {
        return '';
    }

    return attrs
        .map((attr) => `${attr.attribute_name}: ${attr.value_label}`)
        .join(' | ');
});

const maxQuantity = computed(() => {
    if (product.value.allow_backorders) {
        return 99;
    }

    return Math.max(1, Number(product.value.stock_quantity) || 1);
});

const isOnSale = computed(() => Boolean(props.item.is_on_sale));

const formatMoney = (price) => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return '$0.00';
    }

    return `$${value.toFixed(2)}`;
};

const decreaseQty = () => {
    const next = props.item.quantity - 1;
    update(props.item.id, next);
};

const increaseQty = () => {
    if (props.item.quantity >= maxQuantity.value) {
        return;
    }

    update(props.item.id, props.item.quantity + 1);
};

const handleRemove = () => {
    remove(props.item.id);
};
</script>

<template>
    <div
        class="flex flex-col items-center gap-md p-lg md:flex-row"
        :class="!isLast ? 'border-b border-outline-variant' : ''"
    >
        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-surface-container">
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-2xl"
                aria-hidden="true"
            >
                🧁
            </div>
        </div>

        <div class="w-full flex-grow">
            <div class="flex items-start justify-between gap-md">
                <div>
                    <span
                        v-if="product.category"
                        class="mb-xs block font-sans text-label-caps uppercase tracking-wider text-secondary"
                    >
                        {{ product.category }}
                    </span>
                    <h3 class="font-serif text-headline-sm text-primary">
                        <Link
                            v-if="product.slug"
                            :href="route('products.show', product.slug)"
                            class="hover:text-accent"
                        >
                            {{ product.name }}
                        </Link>
                        <span v-else>{{ product.name }}</span>
                    </h3>
                    <p
                        v-if="attributeSummary"
                        class="font-sans text-body-sm text-on-surface-variant"
                    >
                        {{ attributeSummary }}
                    </p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="font-sans text-title-lg text-primary">
                        {{ formatMoney(item.unit_price) }}
                        <span class="font-sans text-body-sm font-normal text-on-surface-variant">
                            / unit
                        </span>
                    </p>
                    <p
                        v-if="isOnSale"
                        class="font-sans text-body-sm text-on-surface-variant line-through"
                    >
                        {{ formatMoney(item.regular_unit_price) }}
                    </p>
                </div>
            </div>

            <div class="mt-md flex items-center justify-between gap-md">
                <div
                    class="flex items-center rounded-full border border-outline-variant px-sm py-1"
                >
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center text-on-surface-variant transition-colors hover:text-primary disabled:opacity-40"
                        :disabled="item.quantity <= 1"
                        aria-label="Decrease quantity"
                        @click="decreaseQty"
                    >
                        <IconMinus :size="16" stroke-width="1.5" />
                    </button>
                    <span class="min-w-[2rem] px-md text-center font-sans text-body-lg font-bold">
                        {{ item.quantity }}
                    </span>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center text-on-surface-variant transition-colors hover:text-primary disabled:opacity-40"
                        :disabled="item.quantity >= maxQuantity"
                        aria-label="Increase quantity"
                        @click="increaseQty"
                    >
                        <IconPlus :size="16" stroke-width="1.5" />
                    </button>
                </div>

                <div class="flex items-center gap-lg">
                    <div class="text-right">
                        <span class="block font-sans text-title-lg font-bold text-primary">
                            {{ formatMoney(item.line_total) }}
                        </span>
                        <span
                            v-if="isOnSale"
                            class="block font-sans text-body-sm text-on-surface-variant line-through"
                        >
                            {{ formatMoney(item.regular_line_total) }}
                        </span>
                    </div>
                    <button
                        type="button"
                        class="text-on-surface-variant transition-colors hover:text-error"
                        aria-label="Remove item"
                        @click="handleRemove"
                    >
                        <IconTrash :size="20" stroke-width="1.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
