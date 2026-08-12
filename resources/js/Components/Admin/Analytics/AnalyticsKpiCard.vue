<script setup lang="ts">
import AnalyticsSparkline from '@/Components/Admin/Analytics/AnalyticsSparkline.vue';
import { IconTrendingDown, IconTrendingUp } from '@tabler/icons-vue';
import type { Component } from 'vue';
import { computed } from 'vue';

const props = defineProps<{
    label: string;
    value: string;
    changePercent: number | null;
    sparkline: number[];
    icon: Component;
    iconBgClass: string;
    iconColorClass?: string;
}>();

const isPositive = computed(() => (props.changePercent ?? 0) >= 0);
const hasChange = computed(() => props.changePercent !== null);
</script>

<template>
    <article class="card-elevated group rounded-xl p-6 transition-shadow hover:shadow-interactive">
        <div class="mb-4 flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full"
                :class="iconBgClass"
            >
                <component
                    :is="icon"
                    :size="24"
                    stroke-width="1.5"
                    :class="iconColorClass ?? 'text-accent'"
                />
            </div>

            <div
                v-if="hasChange"
                class="flex items-center text-xs font-bold"
                :class="isPositive ? 'text-success' : 'text-error'"
            >
                <IconTrendingUp v-if="isPositive" :size="14" stroke-width="1.5" />
                <IconTrendingDown v-else :size="14" stroke-width="1.5" />
                <span>{{ isPositive && changePercent! > 0 ? '+' : '' }}{{ changePercent }}%</span>
            </div>
        </div>

        <p class="mb-1 text-label-caps uppercase text-outline">
            {{ label }}
        </p>
        <h3 class="font-headline-sm text-primary">
            {{ value }}
        </h3>

        <AnalyticsSparkline :values="sparkline" />
    </article>
</template>
