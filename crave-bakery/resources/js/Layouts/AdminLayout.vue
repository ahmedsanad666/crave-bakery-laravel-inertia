<script setup>
import AdminNotificationPanel from '@/Components/Admin/AdminNotificationPanel.vue';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopBar from '@/Components/Admin/AdminTopBar.vue';
import { usePage } from '@inertiajs/vue3';
import { useLocalStorage } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

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

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};

watch(
    () => page.url,
    () => {
        mobileSidebarOpen.value = false;
    },
);
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-surface font-sans text-body-lg text-on-background">
        <!-- Mobile backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="overlay-backdrop fixed inset-0 z-40 lg:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <!-- Sidebar: desktop always visible, mobile slides in -->
        <div
            class="fixed left-0 top-0 z-50 h-full transition-transform duration-300 lg:translate-x-0"
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <AdminSidebar :collapsed="sidebarCollapsed" />
        </div>

        <AdminTopBar
            :title="title"
            :breadcrumb="breadcrumb"
            :sidebar-collapsed="sidebarCollapsed"
            @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed"
            @toggle-mobile-sidebar="mobileSidebarOpen = !mobileSidebarOpen"
            @toggle-notifications="notificationsOpen = !notificationsOpen"
        />

        <main
            class="min-h-screen bg-surface p-lg pt-16 transition-all duration-300"
            :class="sidebarCollapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-64'"
        >
            <div class="mx-auto max-w-[1400px] space-y-lg">
                <slot />
            </div>
        </main>

        <AdminNotificationPanel
            :open="notificationsOpen"
            @close="notificationsOpen = false"
        />

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
                class="fixed bottom-6 right-6 z-50 max-w-sm rounded-card bg-success px-5 py-3 text-sm font-medium text-white shadow-modal"
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
                class="fixed bottom-6 right-6 z-50 max-w-sm rounded-card bg-error px-5 py-3 text-sm font-medium text-white shadow-modal"
                role="alert"
            >
                {{ flashError }}
            </div>
        </Transition>
    </div>
</template>
