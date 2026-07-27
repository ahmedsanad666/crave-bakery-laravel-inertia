<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { computed, ref } from 'vue';
import {
    IconBell,
    IconLayoutSidebarLeftCollapse,
    IconLayoutSidebarLeftExpand,
    IconMenu2,
    IconSearch,
} from '@tabler/icons-vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    breadcrumb: {
        type: String,
        default: '',
    },
    sidebarCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle-sidebar', 'toggle-mobile-sidebar', 'toggle-notifications']);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const siteName = computed(() => page.props.siteSettings?.site_name ?? 'Crave Bakery');
const brandShort = computed(() => {
    const name = siteName.value;
    if (name.toLowerCase().includes('crave')) {
        return 'Crave';
    }
    return name.split(' ')[0] || 'Crave';
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

const breadcrumbLabel = computed(() => props.breadcrumb || props.title);

const tabletSearchOpen = ref(false);
const tabletSearchRef = ref(null);
const tabletSearchInput = ref(null);

const openTabletSearch = () => {
    tabletSearchOpen.value = true;
    requestAnimationFrame(() => {
        tabletSearchInput.value?.focus();
    });
};

const closeTabletSearch = () => {
    tabletSearchOpen.value = false;
};

onClickOutside(tabletSearchRef, () => {
    closeTabletSearch();
});
</script>

<template>
    <header
        class="fixed left-0 right-0 top-0 z-40 flex items-center justify-between border-b border-outline-variant/30 bg-surface/80 px-md shadow-navbar backdrop-blur-md transition-all duration-300 md:left-[4.5rem] md:h-16 md:px-lg"
        :class="[
            sidebarCollapsed ? 'lg:left-[4.5rem]' : 'lg:left-64',
            'h-14',
        ]"
    >
        <!-- Left: menu + (tablet+) title -->
        <div class="flex min-w-0 items-center gap-2 md:gap-4">
            <button
                type="button"
                class="rounded-full p-2 text-secondary transition-colors hover:bg-surface-variant lg:hidden"
                aria-label="Open menu"
                @click="emit('toggle-mobile-sidebar')"
            >
                <IconMenu2 class="size-5" stroke="1.5" />
            </button>

            <button
                type="button"
                class="hidden rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant lg:inline-flex"
                aria-label="Toggle sidebar"
                @click="emit('toggle-sidebar')"
            >
                <IconLayoutSidebarLeftCollapse
                    v-if="!sidebarCollapsed"
                    class="size-5"
                    stroke="1.5"
                />
                <IconLayoutSidebarLeftExpand
                    v-else
                    class="size-5"
                    stroke="1.5"
                />
            </button>

            <!-- Mobile centered brand is absolute; tablet+ title here -->
            <div class="hidden min-w-0 flex-col md:flex">
                <h1 class="truncate font-sans text-title-lg text-primary">{{ title }}</h1>
                <nav class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                    Admin
                    <span class="mx-1">/</span>
                    <span class="text-secondary">{{ breadcrumbLabel }}</span>
                </nav>
            </div>
        </div>

        <!-- Mobile: centered brand -->
        <div class="pointer-events-none absolute inset-x-0 flex justify-center md:hidden">
            <span class="font-serif text-headline-sm leading-none text-secondary">
                {{ brandShort }}
            </span>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-1 md:gap-4 lg:gap-6">
            <!-- Tablet expandable search -->
            <div
                ref="tabletSearchRef"
                class="relative hidden items-center md:flex lg:hidden"
            >
                <button
                    type="button"
                    class="rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant"
                    aria-label="Search"
                    @click="openTabletSearch"
                >
                    <IconSearch class="size-5" stroke="1.5" />
                </button>
                <input
                    ref="tabletSearchInput"
                    type="search"
                    placeholder="Search..."
                    class="absolute left-10 rounded-full border border-outline-variant bg-surface-container-lowest py-1.5 px-4 font-sans text-body-sm outline-none transition-all duration-300 focus:ring-2 focus:ring-secondary-container/20"
                    :class="
                        tabletSearchOpen
                            ? 'w-48 opacity-100'
                            : 'pointer-events-none w-0 opacity-0'
                    "
                    @keydown.escape="closeTabletSearch"
                />
            </div>

            <!-- Mobile search icon (page-level filters handle search) -->
            <button
                type="button"
                class="rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant md:hidden"
                aria-label="Search"
            >
                <IconSearch class="size-5" stroke="1.5" />
            </button>

            <!-- Desktop full search -->
            <div class="relative hidden lg:block">
                <IconSearch
                    class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                    stroke="1.5"
                />
                <input
                    type="search"
                    placeholder="Search orders, customers..."
                    class="w-64 rounded-full border-none bg-surface-container-low py-2 pl-10 pr-4 text-sm text-on-surface placeholder:text-outline-variant focus:ring-2 focus:ring-secondary-container/20"
                />
            </div>

            <div class="flex items-center gap-2 md:gap-3 lg:gap-4">
                <button
                    type="button"
                    class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant"
                    aria-label="Notifications"
                    @click="emit('toggle-notifications')"
                >
                    <IconBell class="size-5" stroke="1.5" />
                    <span
                        class="absolute right-1.5 top-1.5 size-2 rounded-full border-2 border-surface bg-secondary"
                    />
                </button>

                <div class="hidden h-8 w-px bg-outline-variant/30 sm:block" />

                <Link
                    :href="route('admin.profile.edit')"
                    class="flex size-8 items-center justify-center overflow-hidden rounded-full border border-outline-variant bg-secondary-fixed font-sans text-xs font-bold text-primary transition-opacity hover:opacity-90 md:size-10 md:border-2 md:border-secondary/20"
                    title="My Profile"
                >
                    <img
                        v-if="user?.avatar"
                        :src="user.avatar"
                        :alt="user.name"
                        class="size-full object-cover"
                    />
                    <span v-else>{{ userInitials }}</span>
                </Link>
            </div>
        </div>
    </header>
</template>
