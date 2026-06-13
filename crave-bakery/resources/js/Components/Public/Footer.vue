<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    IconBrandFacebook,
    IconBrandInstagram,
    IconMail,
    IconMapPin,
    IconPhone,
} from '@tabler/icons-vue';

const page = usePage();
const site = computed(() => page.props.siteSettings ?? {});
const year = new Date().getFullYear();

const shopLinks = [
    { label: 'All Products', href: '/products' },
    { label: 'Categories', href: '/categories' },
    { label: 'Favourites', href: '/favourites' },
];

const helpLinks = [
    { label: 'My Orders', href: '/orders' },
    { label: 'Contact Us', href: '/#contact' },
    { label: 'FAQs', href: '/faq' },
];
</script>

<template>
    <footer id="contact" class="border-t border-border-base bg-surface-container-low">
        <div class="container-page section-spacing">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <h3 class="font-serif text-headline-sm text-primary">
                        {{ site.site_name ?? 'Crave Bakery' }}
                    </h3>
                    <p class="text-body-sm text-text-muted">
                        {{
                            site.about ??
                            'Baking Smiles, One Pastry At A Time'
                        }}
                    </p>
                    <div class="flex gap-3">
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-card text-primary shadow-card transition-colors hover:text-accent"
                            aria-label="Instagram"
                        >
                            <IconBrandInstagram :size="20" stroke-width="1.5" />
                        </a>
                        <a
                            href="#"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-card text-primary shadow-card transition-colors hover:text-accent"
                            aria-label="Facebook"
                        >
                            <IconBrandFacebook :size="20" stroke-width="1.5" />
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="mb-4 font-sans text-label-caps uppercase text-primary">
                        Shop
                    </h4>
                    <ul class="space-y-3">
                        <li v-for="link in shopLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="text-body-sm text-text-muted transition-colors hover:text-accent"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-sans text-label-caps uppercase text-primary">
                        Help
                    </h4>
                    <ul class="space-y-3">
                        <li v-for="link in helpLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="text-body-sm text-text-muted transition-colors hover:text-accent"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-sans text-label-caps uppercase text-primary">
                        Contact
                    </h4>
                    <ul class="space-y-3 text-body-sm text-text-muted">
                        <li
                            v-if="site.email"
                            class="flex items-start gap-2"
                        >
                            <IconMail
                                :size="18"
                                stroke-width="1.5"
                                class="mt-0.5 shrink-0 text-accent"
                            />
                            <a
                                :href="`mailto:${site.email}`"
                                class="transition-colors hover:text-accent"
                            >
                                {{ site.email }}
                            </a>
                        </li>
                        <li
                            v-else
                            class="flex items-start gap-2"
                        >
                            <IconMail
                                :size="18"
                                stroke-width="1.5"
                                class="mt-0.5 shrink-0 text-accent"
                            />
                            <span>hello@cravebakery.com</span>
                        </li>
                        <li
                            v-if="site.phone"
                            class="flex items-start gap-2"
                        >
                            <IconPhone
                                :size="18"
                                stroke-width="1.5"
                                class="mt-0.5 shrink-0 text-accent"
                            />
                            <a
                                :href="`tel:${site.phone}`"
                                class="transition-colors hover:text-accent"
                            >
                                {{ site.phone }}
                            </a>
                        </li>
                        <li
                            v-if="site.address"
                            class="flex items-start gap-2"
                        >
                            <IconMapPin
                                :size="18"
                                stroke-width="1.5"
                                class="mt-0.5 shrink-0 text-accent"
                            />
                            <span>{{ site.address }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-xxl border-t border-border-base pt-md text-center text-body-sm text-text-muted"
            >
                © {{ year }} {{ site.site_name ?? 'Crave Bakery' }}. All rights
                reserved.
            </div>
        </div>
    </footer>
</template>
