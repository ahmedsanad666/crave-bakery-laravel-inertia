<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    IconBread,
    IconClock,
    IconTruckDelivery,
} from '@tabler/icons-vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const page = usePage();

const site = computed(() => page.props.siteSettings ?? {});
const siteName = computed(() => site.value.site_name ?? 'Crave Bakery');
const siteLogo = computed(() => site.value.logo ?? null);
const siteTagline = computed(
    () => site.value.tagline ?? 'Baking Smiles, One Pastry At A Time',
);

const trustPoints = [
    {
        icon: IconBread,
        title: 'Fresh Daily',
        text: 'Baked every morning with premium ingredients.',
    },
    {
        icon: IconTruckDelivery,
        title: 'Fast Delivery',
        text: 'Same-day delivery across the city.',
    },
    {
        icon: IconClock,
        title: 'Order Anytime',
        text: 'Browse and order 24/7 from any device.',
    },
];
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Brand panel -->
        <aside
            class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-primary p-12 lg:flex"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-10"
                aria-hidden="true"
            >
                <div
                    class="absolute -left-20 top-20 h-64 w-64 rounded-full border border-white/30"
                />
                <div
                    class="absolute bottom-10 right-0 h-48 w-48 rounded-full border border-white/20"
                />
            </div>

            <div class="relative">
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-3"
                >
                    <img
                        v-if="siteLogo"
                        :src="siteLogo"
                        :alt="siteName"
                        class="h-24 w-auto object-contain"
                    />
                    <div
                        v-else
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-accent/20 text-2xl"
                    >
                        🥐
                    </div>
                    <span class="font-serif text-headline-sm text-white">
                        {{ siteName }}
                    </span>
                </Link>
            </div>

            <div class="relative space-y-8">
                <div>
                    <p class="text-label-caps uppercase text-accent">
                        Welcome
                    </p>
                    <h1 class="mt-2 font-serif text-headline-lg text-white">
                        {{ siteTagline }}
                    </h1>
                </div>

                <ul class="space-y-5">
                    <li
                        v-for="point in trustPoints"
                        :key="point.title"
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-accent"
                        >
                            <component :is="point.icon" :size="20" stroke-width="1.5" />
                        </div>
                        <div>
                            <p class="font-sans text-sm font-semibold text-white">
                                {{ point.title }}
                            </p>
                            <p class="mt-0.5 text-body-sm text-white/70">
                                {{ point.text }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            <p class="relative text-body-sm text-white/50">
                &copy; {{ new Date().getFullYear() }}
                {{ siteName }}
            </p>
        </aside>

        <!-- Form panel -->
        <main
            class="flex w-full flex-col justify-center bg-surface px-6 py-10 lg:w-1/2 lg:px-16 lg:py-12"
        >
            <div class="mx-auto w-full max-w-md">
                <Link
                    :href="route('home')"
                    class="mb-8 inline-flex items-center gap-3 font-serif text-headline-sm text-primary lg:hidden"
                >
                    <img
                        v-if="siteLogo"
                        :src="siteLogo"
                        :alt="siteName"
                        class="h-10 w-auto object-contain"
                    />
                    <span v-else class="text-xl">🥐</span>
                    {{ siteName }}
                </Link>

                <header class="mb-8">
                    <h2 class="font-serif text-headline-md text-primary">
                        {{ title }}
                    </h2>
                    <p v-if="subtitle" class="mt-2 text-body-sm text-text-muted">
                        {{ subtitle }}
                    </p>
                </header>

                <slot />
            </div>
        </main>
    </div>
</template>
