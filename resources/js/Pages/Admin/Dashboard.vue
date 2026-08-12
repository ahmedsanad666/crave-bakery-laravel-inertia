<script setup lang="ts">
import AnalyticsAreaChart from '@/Components/Admin/Analytics/AnalyticsAreaChart.vue';
import AnalyticsDonutChart from '@/Components/Admin/Analytics/AnalyticsDonutChart.vue';
import AnalyticsKpiCard from '@/Components/Admin/Analytics/AnalyticsKpiCard.vue';
import AnalyticsLowStockList from '@/Components/Admin/Analytics/AnalyticsLowStockList.vue';
import AnalyticsPeriodToggle from '@/Components/Admin/Analytics/AnalyticsPeriodToggle.vue';
import AnalyticsTopProductsList from '@/Components/Admin/Analytics/AnalyticsTopProductsList.vue';
import DashboardRecentActivity from '@/Components/Admin/Analytics/DashboardRecentActivity.vue';
import DashboardRecentOrders from '@/Components/Admin/Analytics/DashboardRecentOrders.vue';
import DashboardRecentReviews from '@/Components/Admin/Analytics/DashboardRecentReviews.vue';
import { useAdminFormat } from '@/Composables/useAdminFormat';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    IconCalendar,
    IconChartBar,
    IconCurrencyDollar,
    IconShoppingCart,
    IconTruck,
    IconUserPlus,
} from '@tabler/icons-vue';
import { computed } from 'vue';

type KpiMetric = {
    value: number;
    change_percent: number | null;
    sparkline: number[];
};

type AnalyticsPayload = {
    period: string;
    kpis: {
        revenue: KpiMetric;
        orders: KpiMetric;
        new_customers: KpiMetric;
        avg_order_value: KpiMetric;
    };
    revenueSeries: Array<{ label: string; revenue: number; orders: number }>;
    ordersByCategory: Array<{
        id: number;
        name: string;
        count: number;
        percentage: number;
    }>;
    topProducts: Array<{
        id: number;
        name: string;
        slug: string;
        thumbnail: string | null;
        sold_count: number;
        revenue: number;
    }>;
    lowStock: Array<{
        id: number;
        name: string;
        slug: string;
        stock_quantity: number;
        low_stock_threshold: number;
    }>;
    recentOrders: Array<{
        id: number;
        order_number: string;
        customer_name: string;
        total: number;
        status: string;
    }>;
    recentReviews: Array<{
        id: number;
        rating: number;
        title: string | null;
        body_excerpt: string;
        status: string;
        customer: { id: number; name: string; avatar: string | null } | null;
    }>;
    recentActivity: Array<{ type: string; title: string; at: string | null }>;
    activeDeliveries: number;
};

const props = defineProps<{
    analytics: AnalyticsPayload;
}>();

const page = usePage();
const { formatMoney } = useAdminFormat();

const user = computed(() => page.props.auth?.user ?? null);
const siteName = computed(() => page.props.siteSettings?.site_name ?? 'Crave Bakery');
const processing = computed(() => router.processing);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) {
        return 'Good morning';
    }
    if (hour < 17) {
        return 'Good afternoon';
    }
    return 'Good evening';
});

const today = computed(() =>
    new Date().toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }),
);

const firstName = computed(() => user.value?.name?.split(' ')[0] ?? 'Admin');

const categoryTotal = computed(() =>
    props.analytics.ordersByCategory.reduce((sum, row) => sum + row.count, 0),
);

const donutTotal = computed(() =>
    categoryTotal.value > 0
        ? categoryTotal.value
        : props.analytics.kpis.orders.value,
);

function setPeriod(period: string) {
    router.get(
        route('admin.dashboard'),
        { period },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <AdminLayout title="Dashboard" breadcrumb="Dashboard">
        <Head title="Dashboard" />

        <div class="space-y-6">
            <section
                class="relative flex flex-col items-center justify-between overflow-hidden rounded-card bg-primary-container p-xl shadow-lg md:flex-row"
            >
                <div class="relative z-10 space-y-2">
                    <h2 class="font-serif text-headline-md text-white">
                        {{ greeting }}, {{ firstName }} 👋
                    </h2>
                    <p class="text-body-lg text-tertiary-fixed-dim">
                        Here's what's happening at {{ siteName }} today.
                    </p>
                </div>

                <div class="relative z-10 mt-6 flex flex-wrap gap-4 md:mt-0">
                    <div
                        class="flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 backdrop-blur-md"
                    >
                        <IconCalendar class="size-4 text-white/70" stroke="1.5" />
                        <span class="text-sm font-semibold text-white">{{ today }}</span>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 backdrop-blur-md"
                    >
                        <span class="size-2 animate-pulse rounded-full bg-success" />
                        <span class="text-sm font-semibold text-white">Store Open</span>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-full bg-secondary-container px-4 py-2 text-white"
                    >
                        <IconTruck class="size-4" stroke="1.5" />
                        <span class="text-sm font-bold uppercase tracking-wider">
                            {{ analytics.activeDeliveries }} Active Deliveries
                        </span>
                    </div>
                </div>

                <div class="pointer-events-none absolute right-0 top-0 h-full w-1/2 opacity-10">
                    <div class="absolute inset-0 bg-gradient-to-l from-secondary-container to-transparent" />
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                <AnalyticsKpiCard
                    label="Total Revenue"
                    :value="formatMoney(analytics.kpis.revenue.value)"
                    :change-percent="analytics.kpis.revenue.change_percent"
                    :sparkline="analytics.kpis.revenue.sparkline"
                    :icon="IconCurrencyDollar"
                    icon-bg-class="bg-secondary-fixed"
                    icon-color-class="text-accent"
                />
                <AnalyticsKpiCard
                    label="Total Orders"
                    :value="String(analytics.kpis.orders.value)"
                    :change-percent="analytics.kpis.orders.change_percent"
                    :sparkline="analytics.kpis.orders.sparkline"
                    :icon="IconShoppingCart"
                    icon-bg-class="bg-surface-container"
                    icon-color-class="text-primary"
                />
                <AnalyticsKpiCard
                    label="New Customers"
                    :value="String(analytics.kpis.new_customers.value)"
                    :change-percent="analytics.kpis.new_customers.change_percent"
                    :sparkline="analytics.kpis.new_customers.sparkline"
                    :icon="IconUserPlus"
                    icon-bg-class="bg-info/10"
                    icon-color-class="text-info"
                />
                <AnalyticsKpiCard
                    label="Avg. Order Value"
                    :value="formatMoney(analytics.kpis.avg_order_value.value)"
                    :change-percent="analytics.kpis.avg_order_value.change_percent"
                    :sparkline="analytics.kpis.avg_order_value.sparkline"
                    :icon="IconChartBar"
                    icon-bg-class="bg-warning/10"
                    icon-color-class="text-warning"
                />
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <article class="card-elevated rounded-xl p-6 xl:col-span-2">
                    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <h2 class="font-title-lg text-primary">Revenue Overview</h2>
                        <AnalyticsPeriodToggle
                            :value="analytics.period"
                            :disabled="processing"
                            @update:period="setPeriod"
                        />
                    </div>
                    <AnalyticsAreaChart :series="analytics.revenueSeries" />
                </article>

                <article class="card-elevated rounded-xl p-6">
                    <h2 class="mb-6 font-title-lg text-primary">Orders by Category</h2>
                    <AnalyticsDonutChart
                        :segments="analytics.ordersByCategory"
                        :total-orders="donutTotal"
                    />
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-10">
                <article class="card-elevated rounded-xl p-6 lg:col-span-4">
                    <DashboardRecentOrders :orders="analytics.recentOrders" />
                </article>
                <article class="card-elevated rounded-xl p-6 lg:col-span-3">
                    <h2 class="mb-6 font-title-lg text-primary">Top Products</h2>
                    <AnalyticsTopProductsList :products="analytics.topProducts" />
                </article>
                <article class="card-elevated rounded-xl p-6 lg:col-span-3">
                    <AnalyticsLowStockList :items="analytics.lowStock" />
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 pb-12 lg:grid-cols-2">
                <article class="card-elevated rounded-xl p-6">
                    <DashboardRecentReviews :reviews="analytics.recentReviews" />
                </article>
                <article class="card-elevated rounded-xl p-6">
                    <DashboardRecentActivity :items="analytics.recentActivity" />
                </article>
            </section>
        </div>
    </AdminLayout>
</template>
