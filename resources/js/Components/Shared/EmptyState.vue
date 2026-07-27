<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-card bg-card px-6 py-xxl text-center shadow-card"
    >
        <h3 class="font-serif text-headline-sm text-primary">
            {{ title }}
        </h3>
        <p v-if="description" class="mt-2 max-w-md text-body-sm text-text-muted">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-6">
            <slot />
        </div>
    </div>
</template>
