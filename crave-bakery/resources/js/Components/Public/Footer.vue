<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    IconBrandFacebook,
    IconBrandInstagram,
    IconBrandX,
    IconBrandYoutube,
    IconMail,
    IconMapPin,
    IconPhone,
} from '@tabler/icons-vue';

const page = usePage();
const site = computed(() => page.props.siteSettings ?? {});
const year = new Date().getFullYear();
const newsletterEmail = ref('');

const siteName = computed(
    () => site.value.name || site.value.site_name || 'Crave Bakery',
);

const overview = computed(
    () =>
        site.value.overview?.trim() ||
        'Artisan bakery crafting fresh pastries, cakes, and breads daily with premium ingredients.',
);

const address = computed(() => site.value.address?.trim() || '');

const contactEmail = computed(() => site.value.email?.trim() || '');

const contactPhone = computed(() => site.value.phone?.trim() || '');

const whatsappHref = computed(() => {
    const digits = contactPhone.value.replace(/\D/g, '');
    if (!digits) {
        return null;
    }

    return `https://api.whatsapp.com/send/?phone=${digits}`;
});

const socialLinks = computed(() => {
    const links = site.value.social_links ?? {};
    const items = [
        {
            key: 'facebook',
            href: links.facebook,
            label: 'Facebook',
            icon: IconBrandFacebook,
            external: true,
        },
        {
            key: 'instagram',
            href: links.instagram,
            label: 'Instagram',
            icon: IconBrandInstagram,
            external: true,
        },
        {
            key: 'twitter',
            href: links.twitter,
            label: 'Twitter',
            icon: IconBrandX,
            external: true,
        },
        {
            key: 'youtube',
            href: links.youtube,
            label: 'YouTube',
            icon: IconBrandYoutube,
            external: true,
        },
    ];

    return items.filter((item) => typeof item.href === 'string' && item.href.trim() !== '');
});

const exploreLinks = [
    { label: 'Home', href: route('home') },
    { label: 'Catalog', href: route('products.index') },
    { label: 'Contact Us', href: '/#contact' },
];

const footerCategories = computed(() => page.props.footerCategories ?? []);

const handleSubscribe = () => {
    // Newsletter wiring comes with site settings / marketing phase
    newsletterEmail.value = '';
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
                    <div class="space-y-md">
                        <span
                            class="block font-serif text-headline-md font-bold text-on-primary"
                        >
                            {{ siteName }}
                        </span>
                        <p
                            class="max-w-sm font-sans text-body-lg leading-relaxed text-on-primary/70"
                        >
                            {{ overview }}
                        </p>
                        <p
                            v-if="address"
                            class="flex items-start gap-sm font-sans text-body-lg leading-relaxed text-on-primary/70"
                        >
                            <IconMapPin
                                class="mt-1 shrink-0 text-accent"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <span>{{ address }}</span>
                        </p>
                        <a
                            v-if="contactPhone && whatsappHref"
                            :href="whatsappHref"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-sm font-sans text-body-lg leading-relaxed text-on-primary/70 transition-colors hover:text-accent"
                            aria-label="Chat on WhatsApp"
                        >
                            <IconPhone
                                class="shrink-0 text-accent"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <span>{{ contactPhone }}</span>
                        </a>
                        <a
                            v-if="contactEmail"
                            :href="`mailto:${contactEmail}`"
                            class="flex items-center gap-sm font-sans text-body-lg leading-relaxed text-on-primary/70 transition-colors hover:text-accent"
                        >
                            <IconMail
                                class="shrink-0 text-accent"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <span>{{ contactEmail }}</span>
                        </a>
                    </div>
                    <div v-if="socialLinks.length" class="flex flex-wrap gap-md">
                        <a
                            v-for="item in socialLinks"
                            :key="item.key"
                            :href="item.href"
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-on-primary transition-all hover:bg-accent"
                            :aria-label="item.label"
                            :target="item.external ? '_blank' : undefined"
                            :rel="item.external ? 'noopener noreferrer' : undefined"
                        >
                            <component
                                :is="item.icon"
                                :size="22"
                                stroke-width="1.5"
                            />
                        </a>
                    </div>
                </div>

                <div class="space-y-lg">
                    <h4
                        class="font-serif text-title-lg font-bold uppercase tracking-wider text-on-primary"
                    >
                        Explore
                    </h4>
                    <ul class="space-y-md">
                        <li v-for="link in exploreLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="font-sans text-body-lg text-on-primary/80 transition-colors hover:text-accent"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div v-if="footerCategories.length" class="space-y-lg">
                    <h4
                        class="font-serif text-title-lg font-bold uppercase tracking-wider text-on-primary"
                    >
                        Categories
                    </h4>
                    <ul class="space-y-md">
                        <li
                            v-for="category in footerCategories"
                            :key="category.id"
                        >
                            <Link
                                :href="
                                    route('products.index', {
                                        category_id: category.id,
                                    })
                                "
                                class="font-sans text-body-lg text-on-primary/80 transition-colors hover:text-accent"
                            >
                                {{ category.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="space-y-lg">
                    <h4
                        class="font-serif text-title-lg font-bold uppercase tracking-wider text-on-primary"
                    >
                        Join Our Community
                    </h4>
                    <p class="font-sans text-body-lg leading-relaxed text-on-primary/70">
                        Subscribe for fresh updates and exclusive offers.
                    </p>
                    <form
                        class="flex flex-col gap-md"
                        @submit.prevent="handleSubscribe"
                    >
                        <input
                            v-model="newsletterEmail"
                            type="email"
                            placeholder="Your email address"
                            class="h-12 rounded-lg border border-white/10 bg-white/5 px-md font-sans text-body-lg text-on-primary outline-none transition-all placeholder:text-on-primary/40 focus:border-accent focus:ring-2 focus:ring-accent"
                            required
                        />
                        <button
                            type="submit"
                            class="h-12 rounded-lg bg-accent font-sans text-body-lg font-bold text-on-primary transition-all hover:opacity-90 active:scale-95"
                        >
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-between gap-md border-t border-outline-variant pt-xl md:flex-row"
            >
                <p class="font-sans text-body-sm text-on-primary/60">
                    © {{ year }}
                    {{ siteName }}. All rights reserved.
                </p>
                <div class="flex gap-lg">
                    <a
                        href="#"
                        class="font-sans text-body-sm text-on-primary/60 transition-colors hover:text-on-primary"
                    >
                        Privacy Policy
                    </a>
                    <a
                        href="#"
                        class="font-sans text-body-sm text-on-primary/60 transition-colors hover:text-on-primary"
                    >
                        Terms of Service
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
