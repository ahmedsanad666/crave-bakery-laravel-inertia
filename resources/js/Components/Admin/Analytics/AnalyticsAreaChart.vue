<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
);

type SeriesPoint = {
    label: string;
    revenue: number;
    orders: number;
};

const props = defineProps<{
    series: SeriesPoint[];
}>();

const hasData = computed(() => props.series.some((point) => point.revenue > 0));

const chartData = computed(() => ({
    labels: props.series.map((point) => point.label),
    datasets: [
        {
            label: 'Revenue',
            data: props.series.map((point) => point.revenue),
            borderColor: '#E8572A',
            backgroundColor: 'rgba(232, 87, 42, 0.2)',
            fill: true,
            tension: 0.35,
            pointRadius: 0,
            pointHoverRadius: 5,
            borderWidth: 3,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#3D1A0E',
            titleFont: { family: 'Inter', size: 12 },
            bodyFont: { family: 'Inter', size: 12 },
            callbacks: {
                label: (context: { parsed: { y: number | null } }) => {
                    const value = context.parsed.y ?? 0;

                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                    }).format(value);
                },
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: {
                maxTicksLimit: 6,
                color: '#84746f',
                font: { size: 11, weight: 700 as const, family: 'Inter' },
            },
            border: { display: false },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#E5DDD4',
                borderDash: [4, 4],
            },
            ticks: {
                color: '#84746f',
                font: { size: 11, family: 'Inter' },
                callback: (value: string | number) => `$${value}`,
            },
            border: { display: false },
        },
    },
};
</script>

<template>
    <div>
        <div v-if="hasData" class="relative h-72 w-full">
            <Line :data="chartData" :options="chartOptions" />
        </div>

        <EmptyState
            v-else
            title="No revenue data"
            description="Revenue will appear here once paid orders exist in this period."
            class="!py-12 !shadow-none"
        />
    </div>
</template>
