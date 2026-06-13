<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    IconMenu2,
    IconShoppingCart,
    IconUser,
    IconX,
} from '@tabler/icons-vue';

const page = usePage();
const mobileOpen = ref(false);

const site = computed(() => page.props.siteSettings ?? {});
const cartCount = computed(() => page.props.cart?.count ?? 0);
const user = computed(() => page.props.auth?.user ?? null);

const navLinks = [
    { label: 'Home', href: route('home') },
    { label: 'Shop', href: '/products' },
    { label: 'About', href: '/#about' },
    { label: 'Contact', href: '/#contact' },
];

const closeMobile = () => {
    mobileOpen.value = false;
};
</script>

<template>
    <header class="sticky top-0 z-40 bg-primary shadow-navbar">
        <div class="container-page">
            <div class="flex h-16 items-center justify-between lg:h-[72px]">
                <Link :href="route('home')" class="flex shrink-0 items-center gap-3">
                    <img
                        v-if="site.logo"
                        :src="site.logo"
                        :alt="site.site_name ?? 'Crave Bakery'"
                        class="h-9 w-auto"
                    />
                    <span
                        v-else
                        class="font-serif text-xl font-bold text-white lg:text-2xl"
                    >
                        {{ site.site_name ?? 'Crave Bakery' }}
                    </span>
                </Link>

                <nav class="hidden items-center gap-8 lg:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href"
                        class="font-sans text-sm font-medium text-white/85 transition-colors hover:text-white"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="flex items-center gap-2 sm:gap-4">
                    <Link
                        href="/cart"
                        class="relative flex h-10 w-10 items-center justify-center rounded-full text-white transition-colors hover:bg-white/10"
                        aria-label="Shopping cart"
                    >
                        <IconShoppingCart :size="22" stroke-width="1.5" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1 text-[11px] font-bold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <Link
                        v-if="user"
                        :href="route('profile.edit')"
                        class="hidden items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-white/90 transition-colors hover:bg-white/10 sm:flex"
                    >
                        <IconUser :size="18" stroke-width="1.5" />
                        <span class="max-w-[120px] truncate">{{ user.name }}</span>
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden text-sm font-medium text-white/90 transition-colors hover:text-white sm:inline"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="btn-primary btn-sm hidden sm:inline-flex"
                        >
                            Sign Up
                        </Link>
                    </template>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-white transition-colors hover:bg-white/10 lg:hidden"
                        :aria-expanded="mobileOpen"
                        aria-label="Toggle menu"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <IconMenu2 v-if="!mobileOpen" :size="22" stroke-width="1.5" />
                        <IconX v-else :size="22" stroke-width="1.5" />
                    </button>
                </div>
            </div>
        </div>

        <div
            v-show="mobileOpen"
            class="border-t border-white/10 bg-primary lg:hidden"
        >
            <nav class="container-page flex flex-col gap-1 py-4">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-white/90 transition-colors hover:bg-white/10"
                    @click="closeMobile"
                >
                    {{ link.label }}
                </Link>
                <Link
                    v-if="!user"
                    :href="route('login')"
                    class="mx-4 mt-1 rounded-lg px-4 py-3 text-sm font-medium text-white/90 hover:bg-white/10"
                    @click="closeMobile"
                >
                    Log in
                </Link>
                <Link
                    v-if="!user"
                    :href="route('register')"
                    class="btn-primary mx-4 mt-2 justify-center"
                    @click="closeMobile"
                >
                    Sign Up
                </Link>
            </nav>
        </div>
    </header>
</template>
