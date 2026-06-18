<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    IconBook,
    IconCategory,
    IconChartBar,
    IconLayoutDashboard,
    IconLogout,
    IconReceipt,
    IconSettings,
    IconStar,
    IconTag,
    IconUsers,
} from '@tabler/icons-vue';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const siteName = computed(() => page.props.siteSettings?.site_name ?? 'Crave Bakery');

const roleLabel = computed(() => {
    if (user.value?.role === 'super_admin') {
        return 'Super Admin';
    }
    if (user.value?.role === 'admin') {
        return 'Store Manager';
    }
    return 'Admin';
});

const userInitials = computed(() => {
    const name = user.value?.name ?? 'A';
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});

const navSections = [
    {
        label: 'Main',
        items: [
            { label: 'Dashboard', icon: IconLayoutDashboard, route: 'admin.dashboard' },
            { label: 'Analytics', icon: IconChartBar, route: 'admin.analytics.index' },
        ],
    },
    {
        label: 'Catalogue',
        items: [
            { label: 'Products', icon: IconBook, route: 'admin.products.index' },
            { label: 'Categories', icon: IconCategory, route: 'admin.categories.index' },
        ],
    },
    {
        label: 'Sales',
        items: [
            { label: 'Orders', icon: IconReceipt, route: 'admin.orders.index' },
            { label: 'Promo Codes', icon: IconTag, route: 'admin.promo-codes.index' },
        ],
    },
    {
        label: 'Customers',
        items: [
            { label: 'Customers', icon: IconUsers, route: 'admin.customers.index' },
            { label: 'Reviews', icon: IconStar, route: 'admin.reviews.index' },
        ],
    },
];

const hasRoute = (name) => {
    try {
        return route().has(name);
    } catch {
        return false;
    }
};

const isActive = (routeName) => {
    if (!hasRoute(routeName)) {
        return false;
    }

    return route().current(routeName) || route().current(`${routeName}.*`);
};
</script>

<template>
    <aside
        class="admin-scrollbar fixed left-0 top-0 z-50 flex h-full flex-col overflow-y-auto bg-primary-container py-md shadow-md transition-all duration-300"
        :class="collapsed ? 'w-[4.5rem]' : 'w-64'"
    >
        <!-- Branding -->
        <div
            class="flex items-center justify-between px-lg pb-xxl"
            :class="collapsed ? 'flex-col gap-2 px-2' : ''"
        >
            <div class="flex flex-col" :class="collapsed ? 'items-center' : ''">
                <span
                    class="font-serif text-headline-sm text-on-primary-fixed"
                    :class="collapsed ? 'text-center text-sm' : ''"
                >
                    {{ collapsed ? 'CB' : siteName }}
                </span>
                <span
                    v-if="!collapsed"
                    class="text-[10px] uppercase tracking-widest text-secondary-fixed opacity-70"
                >
                    Artisan Admin
                </span>
            </div>
            <span
                v-if="!collapsed"
                class="rounded-full bg-secondary px-2 py-0.5 text-[10px] font-bold text-white"
            >
                ADMIN
            </span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-sm px-2">
            <div
                v-for="section in navSections"
                :key="section.label"
                class="mb-lg"
            >
                <p
                    v-if="!collapsed"
                    class="mb-2 px-4 text-[10px] font-bold uppercase tracking-widest text-on-primary-container"
                >
                    {{ section.label }}
                </p>

                <div v-for="item in section.items" :key="item.label">
                    <Link
                        v-if="hasRoute(item.route)"
                        :href="route(item.route)"
                        class="mx-2 flex items-center gap-3 rounded-lg px-4 py-3 text-body-lg transition-colors"
                        :class="[
                            isActive(item.route)
                                ? 'admin-sidebar-link-active text-white'
                                : 'text-tertiary-fixed-dim hover:bg-white/5 hover:text-white',
                            collapsed ? 'justify-center px-2' : '',
                        ]"
                        :title="collapsed ? item.label : undefined"
                    >
                        <component :is="item.icon" class="size-5 shrink-0" stroke="1.5" />
                        <span v-if="!collapsed">{{ item.label }}</span>
                    </Link>
                    <span
                        v-else
                        class="mx-2 flex cursor-not-allowed items-center gap-3 rounded-lg px-4 py-3 text-body-lg text-tertiary-fixed-dim/50"
                        :class="collapsed ? 'justify-center px-2' : ''"
                        :title="collapsed ? `${item.label} (coming soon)` : undefined"
                    >
                        <component :is="item.icon" class="size-5 shrink-0" stroke="1.5" />
                        <span v-if="!collapsed">{{ item.label }}</span>
                    </span>
                </div>
            </div>
        </nav>

        <!-- Footer -->
        <div class="mt-md border-t border-white/10 pt-md">
            <Link
                v-if="hasRoute('admin.settings.index')"
                :href="route('admin.settings.index')"
                class="mx-2 flex items-center gap-3 rounded-lg px-4 py-3 text-body-lg text-tertiary-fixed-dim transition-colors hover:bg-white/5 hover:text-white"
                :class="[
                    isActive('admin.settings.index') ? 'admin-sidebar-link-active text-white' : '',
                    collapsed ? 'justify-center px-2' : '',
                ]"
                :title="collapsed ? 'Settings' : undefined"
            >
                <IconSettings class="size-5 shrink-0" stroke="1.5" />
                <span v-if="!collapsed">Settings</span>
            </Link>
            <span
                v-else
                class="mx-2 flex cursor-not-allowed items-center gap-3 rounded-lg px-4 py-3 text-body-lg text-tertiary-fixed-dim/50"
                :class="collapsed ? 'justify-center px-2' : ''"
            >
                <IconSettings class="size-5 shrink-0" stroke="1.5" />
                <span v-if="!collapsed">Settings</span>
            </span>

            <div
                class="mx-2 mt-2 flex items-center gap-3 rounded-xl bg-black/20 px-4 py-4"
                :class="collapsed ? 'flex-col px-2' : ''"
            >
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full border border-white/20 bg-secondary-fixed font-sans text-sm font-semibold text-primary"
                >
                    <img
                        v-if="user?.avatar"
                        :src="user.avatar"
                        :alt="user.name"
                        class="size-10 rounded-full object-cover"
                    />
                    <span v-else>{{ userInitials }}</span>
                </div>

                <div v-if="!collapsed" class="min-w-0 flex-1 flex-col">
                    <span class="truncate text-sm font-semibold text-white">
                        {{ user?.name }}
                    </span>
                    <span class="truncate text-xs opacity-60">{{ roleLabel }}</span>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="shrink-0 text-tertiary-fixed-dim transition-colors hover:text-white"
                    :class="collapsed ? 'mt-1' : 'ml-auto'"
                    title="Log out"
                >
                    <IconLogout class="size-5" stroke="1.5" />
                </Link>
            </div>
        </div>
    </aside>
</template>
