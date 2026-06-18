<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { IconCalendar, IconTruck } from '@tabler/icons-vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const siteName = computed(() => page.props.siteSettings?.site_name ?? 'Crave Bakery');

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
</script>

<template>
    <AdminLayout title="Dashboard">
        <Head title="Dashboard" />

        <!-- Welcome banner -->
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
                        Deliveries coming soon
                    </span>
                </div>
            </div>

            <div
                class="pointer-events-none absolute right-0 top-0 h-full w-1/2 opacity-10"
            >
                <div
                    class="absolute inset-0 bg-gradient-to-l from-secondary-container to-transparent"
                />
            </div>
        </section>

        <!-- Placeholder for upcoming dashboard widgets -->
        <section class="card-elevated p-xl text-center">
            <h3 class="font-serif text-headline-sm text-primary">
                Dashboard widgets coming soon
            </h3>
            <p class="mt-2 text-body-sm text-on-surface-variant">
                KPI cards, charts, and recent activity will be added in the next step.
            </p>
        </section>
    </AdminLayout>
</template>
