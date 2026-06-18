<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
</script>

<template>
    <header
        class="fixed left-0 right-0 top-0 z-40 flex h-16 items-center justify-between bg-surface px-lg shadow-navbar transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:left-[4.5rem]' : 'lg:left-64'"
    >
        <div class="flex items-center gap-4">
            <button
                type="button"
                class="rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant lg:hidden"
                @click="emit('toggle-mobile-sidebar')"
            >
                <IconMenu2 class="size-5" stroke="1.5" />
            </button>

            <button
                type="button"
                class="hidden rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant lg:inline-flex"
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

            <div class="flex flex-col">
                <h1 class="font-sans text-title-lg text-primary">{{ title }}</h1>
                <nav class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                    Admin
                    <span class="mx-1">/</span>
                    <span class="text-secondary">{{ breadcrumbLabel }}</span>
                </nav>
            </div>
        </div>

        <div class="flex items-center gap-4 lg:gap-6">
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

            <div class="flex items-center gap-3 lg:gap-4">
                <button
                    type="button"
                    class="relative rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-variant"
                    @click="emit('toggle-notifications')"
                >
                    <IconBell class="size-5" stroke="1.5" />
                    <span
                        class="absolute right-1 top-1 flex size-4 items-center justify-center rounded-full border-2 border-white bg-error text-[10px] text-white"
                    >
                        3
                    </span>
                </button>

                <div class="hidden h-8 w-px bg-outline-variant/30 sm:block" />

                <div
                    class="flex size-8 items-center justify-center overflow-hidden rounded-full bg-secondary-fixed font-sans text-xs font-bold text-primary"
                >
                    <img
                        v-if="user?.avatar"
                        :src="user.avatar"
                        :alt="user.name"
                        class="size-8 object-cover"
                    />
                    <span v-else>{{ userInitials }}</span>
                </div>
            </div>
        </div>
    </header>
</template>
