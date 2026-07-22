<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    IconAt,
    IconBrandInstagram,
    IconShare,
    IconWorld,
} from '@tabler/icons-vue';

const page = usePage();
const site = computed(() => page.props.siteSettings ?? {});
const year = new Date().getFullYear();
const email = ref('');

const exploreLinks = [
    { label: 'Catalogue', href: '/products' },
    { label: 'About Us', href: '/#about' },
    { label: 'Locations', href: '/#contact' },
    { label: 'Careers', href: '/#contact' },
];

const supportLinks = [
    { label: 'Contact Us', href: '/#contact' },
    { label: 'Shipping', href: '/#contact' },
    { label: 'FAQs', href: '/#contact' },
    { label: 'Help Center', href: '/#contact' },
];

const handleSubscribe = () => {
    // Newsletter wiring comes with site settings / marketing phase
    email.value = '';
};
</script>

<template>
    <footer
        id="contact"
        class="hero-pattern w-full border-t border-outline-variant bg-primary px-lg py-32"
    >
        <div class="container-page">
            <div
                class="mb-xxl grid grid-cols-1 gap-xxl md:grid-cols-2 lg:grid-cols-4"
            >
                <div class="space-y-lg">
                    <div class="space-y-sm">
                        <span
                            class="block font-serif text-headline-sm font-bold text-on-primary"
                        >
                            {{ site.site_name ?? 'Crave Bakery' }}
                        </span>
                        <p
                            class="font-sans text-body-sm leading-relaxed text-on-primary/70"
                        >
                            {{
                                site.about ??
                                'Crafting smiles one pastry at a time since 1994. Our commitment to artisanal methods and premium ingredients brings the warmth of the oven to your doorstep.'
                            }}
                        </p>
                    </div>
                    <div class="flex gap-md">
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-on-primary transition-all hover:bg-accent"
                            aria-label="Website"
                        >
                            <IconWorld :size="20" stroke-width="1.5" />
                        </a>
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-on-primary transition-all hover:bg-accent"
                            aria-label="Share"
                        >
                            <IconShare :size="20" stroke-width="1.5" />
                        </a>
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-on-primary transition-all hover:bg-accent"
                            aria-label="Instagram"
                        >
                            <IconBrandInstagram :size="20" stroke-width="1.5" />
                        </a>
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-on-primary transition-all hover:bg-accent"
                            aria-label="Email"
                        >
                            <IconAt :size="20" stroke-width="1.5" />
                        </a>
                    </div>
                </div>

                <div class="space-y-lg">
                    <h4
                        class="text-xs font-bold uppercase tracking-widest text-on-primary"
                    >
                        Explore
                    </h4>
                    <ul class="space-y-md">
                        <li v-for="link in exploreLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="font-sans text-body-sm text-on-primary/80 transition-colors hover:text-accent"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="space-y-lg">
                    <h4
                        class="text-xs font-bold uppercase tracking-widest text-on-primary"
                    >
                        Support
                    </h4>
                    <ul class="space-y-md">
                        <li v-for="link in supportLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="font-sans text-body-sm text-on-primary/80 transition-colors hover:text-accent"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="space-y-lg">
                    <h4
                        class="text-xs font-bold uppercase tracking-widest text-on-primary"
                    >
                        Join Our Community
                    </h4>
                    <p class="font-sans text-body-sm text-on-primary/70">
                        Subscribe for fresh updates and exclusive offers.
                    </p>
                    <form
                        class="flex flex-col gap-sm"
                        @submit.prevent="handleSubscribe"
                    >
                        <input
                            v-model="email"
                            type="email"
                            placeholder="Your email address"
                            class="rounded-lg border border-white/10 bg-white/5 px-md py-sm font-sans text-body-sm text-on-primary outline-none transition-all placeholder:text-on-primary/40 focus:border-accent focus:ring-2 focus:ring-accent"
                            required
                        />
                        <button
                            type="submit"
                            class="rounded-lg bg-accent py-sm font-sans text-body-sm font-bold text-on-primary transition-all hover:opacity-90 active:scale-95"
                        >
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-between gap-md border-t border-outline-variant pt-xl md:flex-row"
            >
                <p class="text-xs text-on-primary/60 transition-colors hover:text-on-primary">
                    © {{ year }}
                    {{ site.site_name ?? 'Crave Bakery' }}. All rights reserved.
                </p>
                <div class="flex gap-lg">
                    <a
                        href="#"
                        class="text-xs text-on-primary/60 transition-colors hover:text-on-primary"
                    >
                        Privacy Policy
                    </a>
                    <a
                        href="#"
                        class="text-xs text-on-primary/60 transition-colors hover:text-on-primary"
                    >
                        Terms of Service
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
