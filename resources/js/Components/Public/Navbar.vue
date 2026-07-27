<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    IconHeart,
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

const isAdminUser = computed(() =>
    ['admin', 'super_admin'].includes(user.value?.role),
);

const accountHref = computed(() =>
    isAdminUser.value
        ? route('admin.dashboard')
        : route('profile.edit'),
);

const accountLabel = computed(() =>
    isAdminUser.value ? 'Admin Dashboard' : 'My Profile',
);

const favouritesHref = computed(() =>
    user.value ? route('favourites.index') : route('login'),
);

const navLinks = computed(() => {
    const categoryLinks = (page.props.navCategories ?? []).map((category) => ({
        label: category.name,
        href: route('products.index', { category_id: category.id }),
        match: null,
        isCategory: true,
        categoryId: category.id,
    }));

    return [
        { label: 'Home', href: route('home'), match: '/', isCategory: false },
        {
            label: 'Catalogue',
            href: route('products.index'),
            match: '/products',
            isCategory: false,
        },
        ...categoryLinks,
        {
            label: 'Contact Us',
            href: '/#contact',
            match: null,
            isCategory: false,
        },
    ];
});

const cartLabel = computed(() => {
    const count = cartCount.value;
    const padded = String(count).padStart(1, '0');

    return `Cart (${padded})`;
});

const isActive = (link) => {
    if (link.isCategory) {
        const params = new URLSearchParams(page.url.split('?')[1] ?? '');
        return (
            currentUrl.value.startsWith('/products') &&
            params.get('category_id') === String(link.categoryId)
        );
    }

    if (link.match === null) {
        return false;
    }

    if (link.match === '/') {
        return currentUrl.value === '/';
    }

    if (link.match === '/products') {
        const params = new URLSearchParams(page.url.split('?')[1] ?? '');
        return (
            currentUrl.value.startsWith('/products') &&
            !params.get('category_id')
        );
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
        class="sticky top-0 z-50 w-full shadow-md transition-all duration-300 "
        :class="
            scrolled
                ? 'bg-primary/95  backdrop-blur-[10px]'
                : 'bg-primary py-sm'
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
                    class="h-16 w-auto"
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
                    :href="route('cart.index')"
                    class="hidden items-center gap-sm font-sans text-body-sm text-on-primary transition-all duration-150 hover:opacity-90 active:scale-95 sm:flex"
                >
                    <IconShoppingBag :size="22" stroke-width="1.5" />
                    {{ cartLabel }}
                </Link>

                <Link
                    :href="favouritesHref"
                    class="hidden items-center justify-center text-on-primary transition-all duration-150 hover:opacity-90 active:scale-95 sm:flex"
                    aria-label="Favourites"
                >
                    <IconHeart :size="22" stroke-width="1.5" />
                </Link>

                <Link
                    v-if="user"
                    :href="accountHref"
                    class="hidden items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10 sm:flex"
                >
                    <IconUser :size="18" stroke-width="1.5" />
                    <span class="max-w-[120px] truncate">{{ user.name }}</span>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="rounded-full bg-accent px-xl py-sm font-sans text-body-sm font-bold text-on-primary transition-all duration-150 hover:opacity-90 active:scale-95"
                >
                    Login
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
                    :href="route('cart.index')"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10 sm:hidden"
                    @click="closeMobile"
                >
                    {{ cartLabel }}
                </Link>
                <Link
                    :href="favouritesHref"
                    class="flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10 sm:hidden"
                    @click="closeMobile"
                >
                    <IconHeart :size="18" stroke-width="1.5" />
                    Favourites
                </Link>
                <Link
                    v-if="user"
                    :href="accountHref"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-on-primary/90 transition-colors hover:bg-white/10"
                    @click="closeMobile"
                >
                    {{ accountLabel }}
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
