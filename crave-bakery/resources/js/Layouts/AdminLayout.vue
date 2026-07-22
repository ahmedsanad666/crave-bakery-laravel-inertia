<script setup>
import AdminNotificationPanel from '@/Components/Admin/AdminNotificationPanel.vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopBar from '@/Components/Admin/AdminTopBar.vue';
import { usePage } from '@inertiajs/vue3';
import { useLocalStorage, useMediaQuery } from '@vueuse/core';
import { computed, onUnmounted, ref, watch } from 'vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    breadcrumb: {
        type: String,
        default: '',
    },
});

const page = usePage();
const sidebarCollapsed = useLocalStorage('admin-sidebar-collapsed', false);
const mobileSidebarOpen = ref(false);
const notificationsOpen = ref(false);

const isDesktop = useMediaQuery('(min-width: 1024px)');

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

/** Desktop uses persisted collapse; below lg, open drawer/rail = expanded labels. */
const effectiveCollapsed = computed(() => {
    if (isDesktop.value) {
        return sidebarCollapsed.value;
    }

    return !mobileSidebarOpen.value;
});

const showSidebarClose = computed(
    () => mobileSidebarOpen.value && !isDesktop.value,
);

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};

const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

watch(
    () => page.url,
    () => {
        mobileSidebarOpen.value = false;
    },
);

watch(isDesktop, (desktop) => {
    if (desktop) {
        mobileSidebarOpen.value = false;
        document.body.style.overflow = '';
    }
});

watch([mobileSidebarOpen, isDesktop], ([open, desktop]) => {
    if (desktop) {
        document.body.style.overflow = '';
        return;
    }

    document.body.style.overflow = open ? 'hidden' : '';
});

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-surface font-sans text-body-lg text-on-background">
        <!-- Backdrop: mobile drawer + tablet expanded rail -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen && !isDesktop"
                class="no-print overlay-backdrop fixed inset-0 z-40 lg:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <!--
            Mobile: off-canvas slide-in.
            Tablet+: always visible as rail (collapsed) or expanded overlay.
            Desktop: always visible; width from persisted collapse.
        -->
        <div
            class="no-print fixed left-0 top-0 z-50 h-full transition-transform duration-300 md:translate-x-0"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <AdminSidebar
                :collapsed="effectiveCollapsed"
                :show-close="showSidebarClose"
                @close="closeMobileSidebar"
            />
        </div>

        <div class="no-print">
            <AdminTopBar
                :title="title"
                :breadcrumb="breadcrumb"
                :sidebar-collapsed="sidebarCollapsed"
                @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
                @toggle-mobile-sidebar="toggleMobileSidebar"
                @toggle-notifications="notificationsOpen = !notificationsOpen"
            />
        </div>

        <main
            class="admin-main min-h-screen bg-surface p-md pt-14 transition-all duration-300 md:p-lg md:pt-16 md:ml-[4.5rem]"
            :class="sidebarCollapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-64'"
        >
            <div class="mx-auto max-w-[1400px] space-y-lg">
                <slot />
            </div>
        </main>

        <div class="no-print">
            <AdminNotificationPanel
                :open="notificationsOpen"
                @close="notificationsOpen = false"
            />
        </div>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="flashSuccess"
                class="no-print fixed bottom-6 right-6 z-50 max-w-sm rounded-card bg-success px-5 py-3 text-sm font-medium text-white shadow-modal"
                role="alert"
            >
                {{ flashSuccess }}
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="flashError"
                class="no-print fixed bottom-6 right-6 z-50 max-w-sm rounded-card bg-error px-5 py-3 text-sm font-medium text-white shadow-modal"
                role="alert"
            >
                {{ flashError }}
            </div>
        </Transition>
    </div>
</template>
