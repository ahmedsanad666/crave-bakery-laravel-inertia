<script setup>
import { computed } from 'vue';

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['select']);

const thumbs = computed(() => {
    const products = props.collection.products ?? [];
    const slots = [...products.slice(0, 4)];
    while (slots.length < 4) {
        slots.push(null);
    }
    return slots;
});

const itemsLabel = computed(() => {
    const count = Number(props.collection.products_count ?? 0);
    return `${count} item${count === 1 ? '' : 's'}`;
});
</script>

<template>
    <button
        type="button"
        class="group w-full cursor-pointer text-left"
        @click="emit('select', collection)"
    >
        <div
            class="mb-sm aspect-square grid grid-cols-2 grid-rows-2 gap-1 overflow-hidden rounded-xl border border-outline-variant bg-surface-container transition-transform group-hover:scale-[1.02]"
        >
            <div
                v-for="(item, index) in thumbs"
                :key="item?.id ?? `empty-${index}`"
                class="h-full w-full overflow-hidden bg-surface-container-high"
            >
                <img
                    v-if="item?.thumbnail"
                    :src="item.thumbnail"
                    :alt="item.name"
                    class="h-full w-full object-cover"
                />
            </div>
        </div>
        <h4 class="font-sans text-title-lg text-on-surface">
            {{ collection.name }}
        </h4>
        <p class="font-sans text-body-sm text-on-surface-variant">
            {{ itemsLabel }}
        </p>
    </button>
</template>
