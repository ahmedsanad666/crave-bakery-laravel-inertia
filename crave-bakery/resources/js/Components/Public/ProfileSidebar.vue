<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    IconHeart,
    IconHistory,
    IconLogout,
    IconMapPin,
    IconUser,
} from '@tabler/icons-vue';
import { computed } from 'vue';

defineProps({
    variant: {
        type: String,
        default: 'sidebar',
        validator: (value) => ['sidebar', 'tabs'].includes(value),
    },
});

const page = usePage();

const profileUser = computed(() => page.props.user ?? null);
const authUser = computed(() => page.props.auth?.user ?? null);

const displayName = computed(
    () => profileUser.value?.name || authUser.value?.name || 'Customer',
);

const avatarUrl = computed(
    () => profileUser.value?.avatar || authUser.value?.avatar || null,
);

const memberSince = computed(() => {
    const iso = profileUser.value?.created_at;
    if (!iso) {
        return 'Member';
    }
    const year = new Date(iso).getFullYear();
    return Number.isNaN(year) ? 'Member' : `Member since ${year}`;
});

const initials = computed(() =>
    displayName.value
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase(),
);

const navItems = [
    {
        key: 'profile',
        label: 'Profile Details',
        icon: IconUser,
        href: () => route('profile.edit'),
        active: () => route().current('profile.edit'),
        enabled: true,
    },
    {
        key: 'orders',
        label: 'Order History',
        icon: IconHistory,
        href: () => route('orders.index'),
        active: () => route().current('orders.index') || route().current('orders.show'),
        enabled: true,
    },
    {
        key: 'favourites',
        label: 'Favourites',
        icon: IconHeart,
        href: () => route('favourites.index'),
        active: () => route().current('favourites.*'),
        enabled: true,
    },
    {
        key: 'addresses',
        label: 'Addresses',
        icon: IconMapPin,
        href: () => route('addresses.index'),
        active: () => route().current('addresses.*'),
        enabled: true,
    },
];

const linkAttrs = (item) => (item.enabled ? { href: item.href() } : {});
</script>

<template>
    <!-- Desktop / tablet sidebar -->
    <aside
        v-if="variant === 'sidebar'"
        class="hidden min-h-[calc(100vh-5rem)] w-64 shrink-0 flex-col space-y-4 border-r border-outline-variant/30 bg-surface-container-low p-4 md:flex"
    >
        <div
            class="mb-4 flex flex-col items-center border-b border-outline-variant/30 py-6"
        >
            <img
                v-if="avatarUrl"
                :src="avatarUrl"
                :alt="displayName"
                class="mb-4 h-16 w-16 rounded-full object-cover shadow-sm"
            />
            <div
                v-else
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-lg font-bold text-on-primary shadow-sm"
            >
                {{ initials }}
            </div>
            <h3 class="font-serif text-headline-sm text-primary">
                {{ displayName }}
            </h3>
            <p class="font-sans text-body-sm text-on-surface-variant">
                {{ memberSince }}
            </p>
        </div>

        <nav class="flex flex-1 flex-col gap-1">
            <component
                :is="item.enabled ? Link : 'span'"
                v-for="item in navItems"
                :key="item.key"
                v-bind="linkAttrs(item)"
                class="flex min-h-11 items-center gap-4 px-4 py-3 transition-all duration-200"
                :class="
                    item.enabled
                        ? item.active()
                            ? 'border-r-4 border-secondary bg-surface-variant/20 font-bold text-secondary'
                            : 'text-on-surface-variant hover:bg-surface-variant/10 hover:text-secondary'
                        : 'cursor-not-allowed text-on-surface-variant/50'
                "
                :title="
                    item.enabled
                        ? undefined
                        : `${item.label} — Coming soon`
                "
            >
                <component :is="item.icon" :size="22" stroke-width="1.5" />
                <span class="font-sans text-body-lg">{{ item.label }}</span>
                <span
                    v-if="!item.enabled"
                    class="ml-auto text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/40"
                >
                    Soon
                </span>
            </component>

            <div class="mt-auto border-t border-outline-variant/30 pt-4">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex min-h-11 w-full items-center gap-4 px-4 py-3 text-left text-error transition-all duration-200 hover:bg-error/5"
                >
                    <IconLogout :size="22" stroke-width="1.5" />
                    <span class="font-sans text-body-lg font-semibold">
                        Log out
                    </span>
                </Link>
            </div>
        </nav>
    </aside>

    <!-- Mobile horizontal tabs -->
    <nav
        v-else
        class="flex gap-2 overflow-x-auto border-b border-outline-variant/30 bg-surface-container-low px-4 py-3 md:hidden [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        aria-label="Account navigation"
    >
        <component
            :is="item.enabled ? Link : 'span'"
            v-for="item in navItems"
            :key="item.key"
            v-bind="linkAttrs(item)"
            class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-colors"
            :class="
                item.enabled
                    ? item.active()
                        ? 'bg-secondary text-on-secondary'
                        : 'bg-surface-container-lowest text-on-surface-variant hover:text-secondary'
                    : 'cursor-not-allowed bg-surface-container text-on-surface-variant/40'
            "
            :title="
                item.enabled ? undefined : `${item.label} — Coming soon`
            "
        >
            <component :is="item.icon" :size="18" stroke-width="1.5" />
            {{ item.label }}
        </component>
        <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full border border-error/20 bg-error/5 px-4 py-2 text-sm font-semibold text-error transition-colors hover:bg-error/10"
        >
            <IconLogout :size="18" stroke-width="1.5" />
            Log out
        </Link>
    </nav>
</template>
