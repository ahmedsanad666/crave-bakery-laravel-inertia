<script setup>
import CartDrawer from '@/Components/Public/CartDrawer.vue';
import Footer from '@/Components/Public/Footer.vue';
import Navbar from '@/Components/Public/Navbar.vue';
import ProfileSidebar from '@/Components/Public/ProfileSidebar.vue';
import PromoBanner from '@/Components/Public/PromoBanner.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface-bright text-on-surface">
        <PromoBanner />
        <Navbar />

        <div class="mx-auto flex w-full max-w-[1200px] flex-1 flex-col md:flex-row">
            <ProfileSidebar variant="tabs" />
            <ProfileSidebar variant="sidebar" />

            <main class="min-w-0 flex-1 space-y-8 p-6 md:space-y-8 md:p-8">
                <slot />
            </main>
        </div>

        <Footer />
        <CartDrawer />

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
