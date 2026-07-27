<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { IconLock } from '@tabler/icons-vue';

const page = usePage();
const siteName = computed(
    () => page.props.siteSettings?.site_name ?? 'Crave Bakery',
);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface-bright text-on-surface">
        <header
            class="sticky top-0 z-50 flex w-full items-center justify-center gap-4 bg-primary px-container-margin py-md shadow-md"
        >
            <Link
                :href="route('home')"
                class="font-serif text-headline-sm text-on-primary"
            >
                {{ siteName }}
            </Link>
            <div class="h-6 w-px bg-on-primary/30"></div>
            <div class="flex items-center gap-2 text-on-primary/90">
                <IconLock :size="20" stroke-width="1.5" />
                <span class="font-sans text-body-lg font-semibold">
                    Secure Checkout
                </span>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer
            class="flex w-full flex-col items-center justify-between border-t border-outline-variant bg-surface-container-highest px-container-margin py-xl md:flex-row"
        >
            <div class="flex flex-col items-center gap-sm md:items-start">
                <span class="font-serif text-headline-sm text-on-surface">
                    {{ siteName }}
                </span>
                <p class="font-sans text-body-sm text-on-surface-variant/70">
                    © {{ new Date().getFullYear() }} {{ siteName }}. All rights
                    reserved.
                </p>
            </div>
            <div class="mt-xl flex gap-lg md:mt-0">
                <Link
                    :href="route('home')"
                    class="font-sans text-body-sm text-on-surface-variant transition-colors hover:text-secondary"
                >
                    Home
                </Link>
                <Link
                    :href="route('products.index')"
                    class="font-sans text-body-sm text-on-surface-variant transition-colors hover:text-secondary"
                >
                    Catalogue
                </Link>
                <Link
                    :href="route('cart.index')"
                    class="font-sans text-body-sm text-on-surface-variant transition-colors hover:text-secondary"
                >
                    Cart
                </Link>
            </div>
        </footer>

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
