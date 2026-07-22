<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import InviteAdminModal from '@/Components/Admin/InviteAdminModal.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconBook,
    IconCategory,
    IconChevronRight,
    IconDotsVertical,
    IconMail,
    IconReceipt,
    IconSearch,
    IconShieldCheck,
    IconStar,
    IconTag,
    IconTrash,
    IconUser,
    IconUserCheck,
    IconUserOff,
    IconUserPlus,
    IconUsers,
    IconAdjustments,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    admins: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    pendingInvitations: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            super_admins: 0,
            active: 0,
            pending_invites: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            role: '',
            status: '',
        }),
    },
    permissionTemplates: {
        type: Array,
        default: () => [],
    },
    permissionSchema: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? '');
const statusTab = ref(props.filters.status ?? '');
const showInvite = ref(false);

const adminRows = computed(() => props.admins?.data ?? []);
const invitations = computed(() => {
    const list = Array.isArray(props.pendingInvitations)
        ? props.pendingInvitations
        : (props.pendingInvitations?.data ?? []);
    const q = search.value.trim().toLowerCase();
    if (!q) {
        return list;
    }
    return list.filter((inv) => inv.email?.toLowerCase().includes(q));
});

const pagination = computed(() => {
    const meta = props.admins?.meta ?? {};
    return {
        current_page: meta.current_page ?? 1,
        last_page: meta.last_page ?? 1,
        from: meta.from ?? null,
        to: meta.to ?? null,
        total: meta.total ?? adminRows.value.length,
    };
});

const showAdmins = computed(() => statusTab.value !== 'pending');
const showPending = computed(
    () => statusTab.value === '' || statusTab.value === 'pending',
);

const statusTabs = [
    { value: '', label: 'All' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'pending', label: 'Pending' },
];

const roleOptions = [
    { id: '', name: 'All Roles' },
    { id: 'super_admin', name: 'Super Admin' },
    { id: 'admin', name: 'Admin' },
];

const scopeIcons = {
    products: IconBook,
    categories: IconCategory,
    attributes: IconAdjustments,
    orders: IconReceipt,
    reviews: IconStar,
    customers: IconUsers,
    promo_codes: IconTag,
};

const applyFilters = () => {
    router.get(
        route('admin.users.index'),
        {
            search: search.value || undefined,
            role: role.value || undefined,
            status: statusTab.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const setStatusTab = (value) => {
    statusTab.value = value;
    applyFilters();
};

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});

watch(role, () => applyFilters());

const initials = (name) => {
    if (!name) {
        return '?';
    }
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
};

const roleBadgeClass = (roleName) => {
    if (roleName === 'super_admin') {
        return 'bg-primary-container text-white';
    }
    return 'bg-secondary text-white';
};

const roleLabel = (roleName) => {
    if (roleName === 'super_admin') {
        return 'Super Admin';
    }
    return 'Admin';
};

const statusLabel = (value) => {
    if (value === 'inactive') {
        return 'Inactive';
    }
    return 'Active';
};

const grantedScopes = (admin) => {
    if (admin.role === 'super_admin') {
        return Object.keys(props.permissionSchema ?? {});
    }
    const perms = admin.permissions ?? {};
    return Object.keys(props.permissionSchema ?? {}).filter((scope) => {
        const actions = perms[scope] ?? {};
        return Object.values(actions).some(Boolean);
    });
};

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const resendInvitation = (invitation) => {
    router.post(route('admin.users.invitations.resend', invitation.id), {}, {
        preserveScroll: true,
    });
};

const revokeInvitation = (invitation) => {
    if (!window.confirm(`Revoke invitation for ${invitation.email}?`)) {
        return;
    }
    router.delete(route('admin.users.invitations.revoke', invitation.id), {
        preserveScroll: true,
    });
};

const deactivateAdmin = (admin) => {
    if (!window.confirm(`Deactivate ${admin.name}?`)) {
        return;
    }
    router.patch(route('admin.users.deactivate', admin.id), {}, {
        preserveScroll: true,
    });
};

const removeAdmin = (admin) => {
    if (
        !window.confirm(
            `Permanently remove ${admin.name}? Type their name to confirm in the next prompt.`,
        )
    ) {
        return;
    }
    const typed = window.prompt(`Type "${admin.name}" to confirm removal:`);
    if (typed !== admin.name) {
        return;
    }
    router.delete(route('admin.users.destroy', admin.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout title="Admin Users" breadcrumb="Admin Users">
        <Head title="Admin Users" />

        <section class="mb-xl flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end">
            <div>
                <nav
                    class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
                >
                    <span>Admin</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-secondary">Admin Users</span>
                </nav>
                <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                    Admin Users
                </h2>
            </div>
            <button
                type="button"
                class="flex h-12 items-center gap-2 rounded-full bg-secondary px-8 font-sans text-title-lg text-white shadow-md transition-all hover:scale-105 active:scale-95"
                @click="showInvite = true"
            >
                <IconUserPlus class="size-5" stroke="1.5" />
                Invite Admin
            </button>
        </section>

        <section class="mb-xl grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
            <div
                class="flex items-center gap-lg rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-primary-container text-on-primary-fixed"
                >
                    <IconUsers class="size-7" stroke="1.5" />
                </div>
                <div>
                    <p class="font-sans text-label-caps uppercase text-on-surface-variant">
                        Total Admins
                    </p>
                    <h3 class="font-serif text-headline-sm">{{ stats.total }}</h3>
                </div>
            </div>
            <div
                class="flex items-center gap-lg rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-secondary-container text-on-secondary-fixed"
                >
                    <IconShieldCheck class="size-7" stroke="1.5" />
                </div>
                <div>
                    <p class="font-sans text-label-caps uppercase text-on-surface-variant">
                        Super Admins
                    </p>
                    <h3 class="font-serif text-headline-sm">{{ stats.super_admins }}</h3>
                </div>
            </div>
            <div
                class="flex items-center gap-lg rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-green-100 text-green-700"
                >
                    <IconUserCheck class="size-7" stroke="1.5" />
                </div>
                <div>
                    <p class="font-sans text-label-caps uppercase text-on-surface-variant">
                        Active
                    </p>
                    <h3 class="font-serif text-headline-sm">{{ stats.active }}</h3>
                </div>
            </div>
            <div
                class="flex items-center gap-lg rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-lg bg-tertiary-fixed text-on-tertiary-fixed"
                >
                    <IconMail class="size-7" stroke="1.5" />
                </div>
                <div>
                    <p class="font-sans text-label-caps uppercase text-on-surface-variant">
                        Pending Invites
                    </p>
                    <h3 class="font-serif text-headline-sm">{{ stats.pending_invites }}</h3>
                </div>
            </div>
        </section>

        <section
            class="mb-lg flex flex-col items-stretch justify-between gap-md md:flex-row md:items-center"
        >
            <div class="flex flex-wrap rounded-lg bg-surface-container-low p-1">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value || 'all'"
                    type="button"
                    class="rounded-md px-5 py-2 font-sans text-title-lg transition-colors"
                    :class="
                        statusTab === tab.value
                            ? 'bg-white text-secondary shadow-sm'
                            : 'text-on-surface-variant hover:text-secondary'
                    "
                    @click="setStatusTab(tab.value)"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div class="flex flex-col gap-sm sm:flex-row sm:items-center">
                <div class="relative min-w-[200px] flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-outline"
                        stroke="1.5"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search admins…"
                        class="h-10 w-full rounded-lg border-outline-variant bg-white pl-10 pr-4 text-body-sm focus:border-secondary focus:ring-secondary/20"
                    />
                </div>
                <div class="flex items-center gap-sm">
                    <span class="whitespace-nowrap text-body-sm text-on-surface-variant">
                        Filter Role:
                    </span>
                    <div class="min-w-[160px]">
                        <AppSelect
                            v-model="role"
                            :options="roleOptions"
                            size="sm"
                            placeholder="All Roles"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-xl pb-xxl md:grid-cols-2 lg:grid-cols-3">
            <template v-if="showPending">
                <div
                    v-for="invitation in invitations"
                    :key="`inv-${invitation.id}`"
                    class="flex flex-col rounded-xl border-2 border-dashed border-outline-variant bg-white p-lg opacity-90 transition-opacity hover:opacity-100"
                >
                    <div class="mb-md flex items-start justify-between gap-3">
                        <div class="flex items-center gap-md">
                            <div
                                class="flex size-14 items-center justify-center rounded-full bg-surface-container-high text-outline"
                            >
                                <IconUser class="size-7" stroke="1.5" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="truncate font-serif text-headline-sm">
                                    {{ invitation.email }}
                                </h4>
                                <p class="text-body-sm italic text-outline">
                                    Expires {{ formatDate(invitation.expires_at) }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-700"
                        >
                            Pending
                        </span>
                    </div>
                    <p class="mb-lg text-body-sm text-on-surface-variant">
                        Role: {{ roleLabel(invitation.role) }}
                    </p>
                    <div class="mt-auto flex gap-2">
                        <button
                            type="button"
                            class="h-12 flex-1 rounded-full bg-surface-container-high font-sans text-title-lg text-on-surface-variant transition-colors hover:bg-surface-container-highest"
                            @click="resendInvitation(invitation)"
                        >
                            Resend
                        </button>
                        <button
                            type="button"
                            class="flex size-12 items-center justify-center rounded-full border border-error text-error transition-colors hover:bg-error hover:text-white"
                            aria-label="Revoke invitation"
                            @click="revokeInvitation(invitation)"
                        >
                            <IconTrash class="size-5" stroke="1.5" />
                        </button>
                    </div>
                </div>
            </template>

            <template v-if="showAdmins">
                <div
                    v-for="admin in adminRows"
                    :key="admin.id"
                    class="flex flex-col rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm transition-shadow duration-300 hover:shadow-lg"
                >
                    <div class="mb-md flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-md">
                            <div
                                class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 font-sans text-sm font-semibold"
                                :class="
                                    admin.role === 'super_admin'
                                        ? 'border-secondary bg-secondary-fixed text-primary'
                                        : 'border-outline-variant bg-surface-container-high text-primary'
                                "
                            >
                                <img
                                    v-if="admin.avatar"
                                    :src="admin.avatar"
                                    :alt="admin.name"
                                    class="size-full object-cover"
                                />
                                <span v-else>{{ initials(admin.name) }}</span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="truncate font-serif text-headline-sm">
                                    {{ admin.name }}
                                </h4>
                                <p class="truncate text-body-sm text-outline">
                                    {{ admin.email }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-start gap-1">
                            <span
                                class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider"
                                :class="roleBadgeClass(admin.role)"
                            >
                                {{ roleLabel(admin.role) }}
                            </span>
                            <Menu
                                v-if="admin.id !== currentUserId"
                                as="div"
                                class="relative"
                            >
                                <MenuButton
                                    class="rounded-full p-1.5 text-on-surface-variant transition-colors hover:bg-surface-container"
                                    aria-label="Admin actions"
                                >
                                    <IconDotsVertical class="size-4" stroke="1.5" />
                                </MenuButton>
                                <MenuItems
                                    class="absolute right-0 z-10 mt-1 w-44 origin-top-right rounded-lg border border-outline-variant/40 bg-white py-1 shadow-lg focus:outline-none"
                                >
                                    <MenuItem
                                        v-if="admin.status !== 'inactive'"
                                        v-slot="{ active }"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-body-sm"
                                            :class="
                                                active
                                                    ? 'bg-surface-container-low'
                                                    : ''
                                            "
                                            @click="deactivateAdmin(admin)"
                                        >
                                            <IconUserOff class="size-4" stroke="1.5" />
                                            Deactivate
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-body-sm text-error"
                                            :class="
                                                active
                                                    ? 'bg-error-container/40'
                                                    : ''
                                            "
                                            @click="removeAdmin(admin)"
                                        >
                                            <IconTrash class="size-4" stroke="1.5" />
                                            Remove
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </Menu>
                        </div>
                    </div>

                    <div class="mb-2">
                        <span
                            class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                            :class="
                                admin.status === 'inactive'
                                    ? 'bg-surface-container-high text-on-surface-variant'
                                    : 'bg-green-100 text-green-700'
                            "
                        >
                            {{ statusLabel(admin.status) }}
                        </span>
                    </div>

                    <div class="mb-lg">
                        <p
                            class="mb-sm text-[10px] font-bold uppercase tracking-widest text-outline-variant"
                        >
                            Permissions
                        </p>
                        <div class="flex flex-wrap gap-md">
                            <component
                                :is="scopeIcons[scope] ?? IconAdjustments"
                                v-for="scope in grantedScopes(admin)"
                                :key="scope"
                                class="size-5 text-green-600"
                                stroke="1.5"
                                :title="permissionSchema[scope]?.label ?? scope"
                            />
                            <span
                                v-if="!grantedScopes(admin).length"
                                class="text-body-sm text-on-surface-variant"
                            >
                                No scopes granted
                            </span>
                        </div>
                    </div>

                    <Link
                        :href="route('admin.users.permissions', admin.id)"
                        class="mt-auto flex h-12 w-full items-center justify-center rounded-full border border-primary-container font-sans text-title-lg text-primary-container transition-colors hover:bg-primary-container hover:text-white"
                    >
                        Edit Permissions
                    </Link>
                </div>
            </template>

            <div
                v-if="
                    (showAdmins && !adminRows.length && !showPending) ||
                    (statusTab === 'pending' && !invitations.length) ||
                    (statusTab === '' && !adminRows.length && !invitations.length)
                "
                class="col-span-full rounded-xl border border-dashed border-outline-variant bg-surface-container-low px-lg py-xxl text-center"
            >
                <p class="font-sans text-title-lg text-on-surface-variant">
                    No admins found
                </p>
                <p class="mt-1 text-body-sm text-outline">
                    Invite a teammate or adjust your filters.
                </p>
            </div>
        </section>

        <div v-if="showAdmins && pagination.last_page > 1" class="pb-xl">
            <AdminPagination :pagination="pagination" />
        </div>

        <InviteAdminModal
            :show="showInvite"
            :schema="permissionSchema"
            :templates="permissionTemplates"
            @close="showInvite = false"
        />
    </AdminLayout>
</template>
