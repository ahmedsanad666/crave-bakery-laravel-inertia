<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

type Segment = {
    id: number;
    name: string;
    count: number;
    percentage: number;
};

const props = defineProps<{
    segments: Segment[];
    totalOrders: number;
}>();

const palette = [
    '#3D1A0E',
    '#E8572A',
    '#F6B8A5',
    '#FFDBD1',
    '#84746F',
    '#514440',
];

const legendPalette = [
    'bg-primary',
    'bg-accent',
    'bg-primary-fixed-dim',
    'bg-secondary-fixed',
    'bg-outline',
    'bg-on-surface-variant',
];

const hasData = computed(
    () => props.segments.length > 0 && props.totalOrders > 0,
);

const chartData = computed(() => ({
    labels: props.segments.map((segment) => segment.name),
    datasets: [
        {
            data: props.segments.map((segment) => segment.count),
            backgroundColor: props.segments.map(
                (_, index) => palette[index % palette.length],
            ),
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#3D1A0E',
            titleFont: { family: 'Inter', size: 12 },
            bodyFont: { family: 'Inter', size: 12 },
        },
    },
};
</script>

<template>
    <div class="flex flex-col items-center">
        <div v-if="hasData" class="relative flex h-48 w-48 items-center justify-center">
            <Doughnut :data="chartData" :options="chartOptions" />
            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                <span class="font-headline-sm text-primary">{{ totalOrders }}</span>
                <span class="text-[10px] font-bold uppercase text-outline">Orders</span>
            </div>
        </div>

        <EmptyState
            v-else
            title="No category data"
            description="Category breakdown appears when orders include products."
            class="!w-full !py-10 !shadow-none"
        />

        <div v-if="hasData" class="mt-6 grid w-full grid-cols-2 gap-y-3">
            <div
                v-for="(segment, index) in segments"
                :key="segment.id"
                class="flex items-center gap-2"
            >
                <span
                    class="h-3 w-3 shrink-0 rounded-sm"
                    :class="legendPalette[index % legendPalette.length]"
                />
                <span class="text-xs font-semibold text-primary">
                    {{ segment.name }} ({{ segment.percentage }}%)
                </span>
            </div>
        </div>
    </div>
</template>
