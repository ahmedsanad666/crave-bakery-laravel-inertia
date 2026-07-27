<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        default: () => [],
    },
    alt: {
        type: String,
        default: 'Product image',
    },
});

const selectedIndex = ref(0);

watch(
    () => props.images,
    () => {
        selectedIndex.value = 0;
    },
);

const hasImages = computed(() => props.images.length > 0);

const activeImage = computed(() => {
    if (!hasImages.value) {
        return null;
    }

    return props.images[selectedIndex.value] ?? props.images[0];
});

const selectImage = (index) => {
    selectedIndex.value = index;
};
</script>

<template>
    <div class="flex flex-col gap-lg">
        <div
            class="overflow-hidden rounded-xl border border-outline-variant/30 bg-white shadow-card"
        >
            <img
                v-if="activeImage?.url"
                :src="activeImage.url"
                :alt="alt"
                class="aspect-[1.1] h-auto w-full object-cover"
            />
            <div
                v-else
                class="flex aspect-[1.1] w-full items-center justify-center bg-surface-container text-6xl"
            >
                🧁
            </div>
        </div>

        <div
            v-if="images.length > 1"
            class="grid grid-cols-4 gap-md"
        >
            <button
                v-for="(image, index) in images"
                :key="image.id ?? image.url"
                type="button"
                class="overflow-hidden rounded-lg transition-colors"
                :class="
                    index === selectedIndex
                        ? 'border-2 border-accent ring-2 ring-accent/10'
                        : 'border border-outline-variant hover:border-accent'
                "
                @click="selectImage(index)"
            >
                <img
                    :src="image.url"
                    :alt="`${alt} thumbnail ${index + 1}`"
                    class="h-24 w-full object-cover"
                />
            </button>
        </div>
    </div>
</template>
