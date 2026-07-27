<script setup>
import { Link } from '@inertiajs/vue3';
import { IconArrowRight } from '@tabler/icons-vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="`/categories/${category.slug}`"
        class="group relative block overflow-hidden rounded-card bg-card shadow-card transition-shadow hover:shadow-interactive"
    >
        <div class="aspect-[4/3] overflow-hidden bg-surface-container">
            <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-container to-primary text-5xl"
            >
                🍰
            </div>
        </div>

        <div
            class="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/20 to-transparent"
        />

        <div class="absolute inset-x-0 bottom-0 p-5">
            <h3 class="font-serif text-headline-sm text-white">
                {{ category.name }}
            </h3>
            <p
                v-if="category.description"
                class="mt-1 line-clamp-2 text-body-sm text-white/80"
            >
                {{ category.description }}
            </p>
            <span
                class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-accent"
            >
                Explore
                <IconArrowRight
                    :size="16"
                    stroke-width="1.5"
                    class="transition-transform group-hover:translate-x-1"
                />
            </span>
        </div>
    </Link>
</template>
