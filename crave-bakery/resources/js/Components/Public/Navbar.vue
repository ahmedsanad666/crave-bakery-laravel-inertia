<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    IconMenu2,
    IconShoppingBag,
    IconUser,
    IconX,
} from '@tabler/icons-vue';

const page = usePage();
const mobileOpen = ref(false);
const scrolled = ref(false);

const site = computed(() => page.props.siteSettings ?? {});
const cartCount = computed(() => page.props.cart?.count ?? 0);
const user = computed(() => page.props.auth?.user ?? null);
const currentUrl = computed(() => page.url.split('?')[0]);

const navLinks = [
    { label: 'Home', href: route('home'), match: '/' },
    { label: 'Catalogue', href: '/products', match: '/products' },
    { label: 'About Us', href: '/#about', match: null },
    { label: 'Contact Us', href: '/#contact', match: null },
];

const cartLabel = computed(() => {
    const count = cartCount.value;
    const padded = String(count).padStart(2, '0');

    return `Cart (${padded})`;
});

const isActive = (link) => {
    if (link.match === null) {
        return false;
    }

    if (link.match === '/') {
        return currentUrl.value === '/';
    }

    return currentUrl.value.startsWith(link.match);
};

const onScroll = () => {
    scrolled.value = window.scrollY > 50;
};

const closeMobile = () => {
    mobileOpen.value = false;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>

<template>
    <nav
        class="sticky top-0 z-50 w-full shadow-md transition-all duration-300"
        :class="
            scrolled
                ? 'bg-primary/95 py-sm backdrop-blur-[10px]'
                : 'bg-primary py-md'
        "
    >
        <div
            class="container-page flex items-center justify-between"
        >
            <Link
                :href="route('home')"
                class="font-serif text-headline-md font-bold text-on-primary"
            >
                <img
                    v-if="site.logo"
                    :src="site.logo"
                    :alt="site.site_name ?? 'Crave Bakery'"
                    class="h-9 w-auto"
                />
                <span v-else>
                    {{ site.site_name ?? 'Crave Bakery' }}
                </span>
            </Link>

            <div class="hidden items-center gap-xl md:flex">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="font-sans text-body-sm transition-opacity"
                    :class="
                        isActive(link)
                            ? 'border-b-2 border-secondary-container pb-1 text-on-primary'
                            : 'text-on-primary/80 hover:text-on-primary'
                    "
                >
                    {{ link.label }}
                </Link>
            </div>

            <div class="flex items-center gap-lg">
                <Link
                    href="/cart"
                    class="hidden items-center gap-sm font-sans text-body-sm text-on-primary transition-all duration-150 hover:opacity-90 active:scale-95 sm:flex"
                >
                    <IconShoppingBag :size="22" stroke-width="1.5" />
                    {{ cartLabel }}
                </Link>

                <Link
                    v-if="user"
                    :href="route('profile.edit')"
                    class="hidden items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10 sm:flex"
                >
                    <IconUser :size="18" stroke-width="1.5" />
                    <span class="max-w-[120px] truncate">{{ user.name }}</span>
                </Link>
                <Link
                    v-else
                    :href="route('register')"
                    class="rounded-full bg-accent px-xl py-sm font-sans text-body-sm font-bold text-on-primary transition-all duration-150 hover:opacity-90 active:scale-95"
                >
                    Sign Up
                </Link>

                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full text-on-primary transition-colors hover:bg-white/10 md:hidden"
                    :aria-expanded="mobileOpen"
                    aria-label="Toggle menu"
                    @click="mobileOpen = !mobileOpen"
                >
                    <IconMenu2 v-if="!mobileOpen" :size="22" stroke-width="1.5" />
                    <IconX v-else :size="22" stroke-width="1.5" />
                </button>
            </div>
        </div>

        <div
            v-show="mobileOpen"
            class="border-t border-white/10 bg-primary md:hidden"
        >
            <div class="container-page flex flex-col gap-1 py-4">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10"
                    @click="closeMobile"
                >
                    {{ link.label }}
                </Link>
                <Link
                    href="/cart"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10 sm:hidden"
                    @click="closeMobile"
                >
                    {{ cartLabel }}
                </Link>
                <Link
                    v-if="!user"
                    :href="route('login')"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 hover:bg-white/10"
                    @click="closeMobile"
                >
                    Log in
                </Link>
                <Link
                    v-if="!user"
                    :href="route('register')"
                    class="mx-4 mt-2 justify-center rounded-full bg-accent px-xl py-sm text-center font-sans text-body-sm font-bold text-on-primary"
                    @click="closeMobile"
                >
                    Sign Up
                </Link>
            </div>
        </div>
    </nav>
</template>
