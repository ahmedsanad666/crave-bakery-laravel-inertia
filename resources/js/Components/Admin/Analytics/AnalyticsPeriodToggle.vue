<script setup lang="ts">
const props = defineProps<{
    value: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:period': [period: string];
}>();

const options = [
    { value: '7d', label: '7D' },
    { value: '1m', label: '1M' },
    { value: '3m', label: '3M' },
    { value: '1y', label: '1Y' },
];

function select(period: string) {
    if (props.disabled || period === props.value) {
        return;
    }

    emit('update:period', period);
}
</script>

<template>
    <div
        class="flex rounded-lg bg-surface-container-low p-1"
        role="group"
        aria-label="Analytics period"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            class="rounded-md px-4 py-1.5 text-xs font-bold transition-colors disabled:cursor-not-allowed disabled:opacity-50"
            :class="
                value === option.value
                    ? 'bg-white text-primary shadow-sm'
                    : 'text-outline hover:text-primary'
            "
            :disabled="disabled"
            @click="select(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
