<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    values: number[];
}>();

const bars = computed(() => {
    const values = props.values ?? [];

    if (values.length === 0) {
        return [];
    }

    const max = Math.max(...values, 1);

    return values.map((value, index) => ({
        height: Math.max(12, Math.round((value / max) * 100)),
        isLast: index === values.length - 1,
    }));
});
</script>

<template>
    <div
        v-if="bars.length"
        class="mt-4 flex h-8 items-end gap-0.5"
        aria-hidden="true"
    >
        <div
            v-for="(bar, index) in bars"
            :key="index"
            class="w-full min-w-[4px] rounded-t-sm transition-all"
            :class="bar.isLast ? 'bg-accent' : 'bg-accent/10'"
            :style="{ height: `${bar.height}%` }"
        />
    </div>
</template>
