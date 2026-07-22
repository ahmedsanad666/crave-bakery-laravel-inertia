<script setup>
import PermissionsMatrix from '@/Components/Admin/PermissionsMatrix.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    IconAlertTriangle,
    IconArrowLeft,
    IconShieldOff,
    IconTrash,
} from '@tabler/icons-vue';
import { computed, watch } from 'vue';

const props = defineProps({
    admin: {
        type: Object,
        required: true,
    },
    schema: {
        type: Object,
        default: () => ({}),
    },
    templates: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);
const isSelf = computed(() => currentUserId.value === props.admin.id);
const isSuperAdmin = computed(() => props.admin.role === 'super_admin');

const emptyMatrix = () => {
    const matrix = {};
    for (const [scope, definition] of Object.entries(props.schema ?? {})) {
        matrix[scope] = {};
        for (const action of Object.keys(definition.actions ?? {})) {
            matrix[scope][action] = false;
        }
    }
    return matrix;
};

const cloneMatrix = (source) => {
    if (!source || typeof source !== 'object') {
        return emptyMatrix();
    }
    const base = emptyMatrix();
    for (const scope of Object.keys(base)) {
        for (const action of Object.keys(base[scope])) {
            base[scope][action] = Boolean(source?.[scope]?.[action]);
        }
    }
    return base;
};

const fullMatrix = () => {
    const matrix = emptyMatrix();
    for (const scope of Object.keys(matrix)) {
        for (const action of Object.keys(matrix[scope])) {
            matrix[scope][action] = true;
        }
    }
    return matrix;
};

const form = useForm({
    permissions: isSuperAdmin.value
        ? emptyMatrix()
        : cloneMatrix(props.admin.permissions),
});

watch(
    () => props.admin.permissions,
    (value) => {
        if (!isSuperAdmin.value) {
            form.permissions = cloneMatrix(value);
        }
    },
);

const initials = computed(() => {
    const name = props.admin.name ?? '';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
});

const roleLabel = computed(() =>
    isSuperAdmin.value ? 'Super Admin' : 'Admin',
);

const accessSummary = computed(() => {
    if (isSuperAdmin.value) {
        return Object.values(props.schema ?? {}).map((def) => ({
            label: def.label ?? 'Scope',
            level: 'Full',
        }));
    }

    return Object.entries(props.schema ?? {}).map(([scope, def]) => {
        const actions = Object.keys(def.actions ?? {});
        const granted = actions.filter(
            (action) => form.permissions?.[scope]?.[action],
        );
        let level = 'None';
        if (granted.length === actions.length && actions.length > 0) {
            level = 'Full';
        } else if (granted.length === 1 && granted[0] === 'view') {
            level = 'View Only';
        } else if (granted.length > 0) {
            level = 'Limited';
        }
        return { label: def.label ?? scope, level };
    }).filter((item) => item.level !== 'None');
});

const templateButtons = computed(() => {
    const items = [
        { id: 'read_only', label: 'Read only' },
        { id: 'full', label: 'Full access' },
        { id: 'none', label: 'No access' },
    ];
    for (const name of props.templates ?? []) {
        if (name === 'read_only' || name === 'full_admin') {
            continue;
        }
        items.push({
            id: name,
            label: name
                .split('_')
                .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                .join(' '),
        });
    }
    return items;
});

const applyTemplate = (id) => {
    if (isSuperAdmin.value) {
        return;
    }

    if (id === 'none') {
        form.permissions = emptyMatrix();
        return;
    }
    if (id === 'full') {
        form.permissions = fullMatrix();
        return;
    }
    if (id === 'read_only') {
        const matrix = emptyMatrix();
        for (const scope of Object.keys(matrix)) {
            if ('view' in matrix[scope]) {
                matrix[scope].view = true;
            }
        }
        form.permissions = matrix;
        return;
    }

    // Known named templates: build client-side from schema + common patterns
    // Prefer mirroring backend full_admin / catalogue / orders via heuristics
    if (id === 'full_admin') {
        form.permissions = fullMatrix();
        return;
    }

    // For other templates, fetch isn't available — apply best-effort empty then
    // enable all actions listed in a static map matching config/permissions.php
    const TEMPLATE_MAP = {
        catalogue_manager: {
            products: ['view', 'create', 'edit', 'delete', 'manage_stock'],
            categories: ['view', 'create', 'edit', 'delete'],
            attributes: ['view', 'create', 'edit', 'delete'],
        },
        orders_support: {
            orders: ['view', 'update_status', 'refund'],
            reviews: ['view', 'approve', 'delete', 'respond'],
            customers: ['view', 'edit', 'export'],
        },
        read_only: Object.fromEntries(
            Object.keys(props.schema ?? {}).map((scope) => [scope, ['view']]),
        ),
        full_admin: Object.fromEntries(
            Object.entries(props.schema ?? {}).map(([scope, def]) => [
                scope,
                Object.keys(def.actions ?? {}),
            ]),
        ),
    };

    const definition = TEMPLATE_MAP[id];
    if (!definition) {
        return;
    }

    const matrix = emptyMatrix();
    for (const [scope, actions] of Object.entries(definition)) {
        if (!matrix[scope]) {
            continue;
        }
        for (const action of actions) {
            if (action in matrix[scope]) {
                matrix[scope][action] = true;
            }
        }
    }
    form.permissions = matrix;
};

const discard = () => {
    form.permissions = isSuperAdmin.value
        ? emptyMatrix()
        : cloneMatrix(props.admin.permissions);
    form.clearErrors();
};

const save = () => {
    if (isSuperAdmin.value) {
        return;
    }
    form.patch(route('admin.users.permissions.update', props.admin.id), {
        preserveScroll: true,
    });
};

const deactivate = () => {
    if (isSelf.value) {
        return;
    }
    if (!window.confirm(`Deactivate ${props.admin.name}?`)) {
        return;
    }
    router.patch(route('admin.users.deactivate', props.admin.id), {}, {
        preserveScroll: true,
    });
};

const removeAdmin = () => {
    if (isSelf.value) {
        return;
    }
    const typed = window.prompt(
        `Type "${props.admin.name}" to permanently remove this admin:`,
    );
    if (typed !== props.admin.name) {
        return;
    }
    router.delete(route('admin.users.destroy', props.admin.id));
};
</script>

<template>
    <AdminLayout title="Edit Permissions" breadcrumb="Admin Users">
        <Head :title="`Permissions — ${admin.name}`" />

        <header class="mb-xl pt-4">
            <Link
                :href="route('admin.users.index')"
                class="group mb-4 inline-flex items-center gap-2 text-on-surface-variant transition-colors hover:text-primary"
            >
                <IconArrowLeft class="size-5" stroke="1.5" />
                <span class="font-sans text-label-caps uppercase">
                    Back to Admin Users
                </span>
            </Link>

            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
            >
                <div>
                    <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                        Edit Admin Permissions
                    </h2>
                    <p class="text-on-surface-variant">
                        Modifying access levels for
                        <span class="font-bold text-on-surface">{{ admin.name }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-full border border-outline px-6 py-2 font-bold text-primary transition-all hover:bg-surface-container disabled:opacity-50"
                        :disabled="form.processing || isSuperAdmin"
                        @click="discard"
                    >
                        Discard
                    </button>
                    <button
                        type="button"
                        class="rounded-full bg-secondary px-8 py-2 font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing || isSuperAdmin"
                        @click="save"
                    >
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </header>

        <div
            v-if="isSuperAdmin"
            class="mb-lg flex items-start gap-4 rounded-lg border-l-4 border-error bg-error-container p-lg"
        >
            <IconAlertTriangle class="mt-0.5 size-6 shrink-0 text-error" stroke="1.5" />
            <div>
                <h4 class="font-sans text-title-lg text-error">
                    Super Admin access
                </h4>
                <p class="font-sans text-body-sm text-on-error-container">
                    Super admins always have full access. Permissions cannot be
                    restricted from this screen.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-xl lg:grid-cols-3">
            <div class="space-y-lg lg:col-span-2">
                <section
                    class="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm"
                >
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-outline-variant bg-surface-container-low p-lg sm:flex-row sm:items-center"
                    >
                        <h3 class="font-serif text-headline-sm text-primary">
                            Permissions Matrix
                        </h3>
                        <div
                            v-if="!isSuperAdmin"
                            class="flex flex-wrap items-center gap-2"
                        >
                            <span class="text-body-sm font-sans uppercase tracking-wide text-on-surface-variant">
                                Bulk:
                            </span>
                            <button
                                v-for="btn in templateButtons"
                                :key="btn.id"
                                type="button"
                                class="rounded-full border border-outline-variant px-3 py-1 text-[12px] font-semibold text-on-surface-variant transition-colors hover:border-secondary hover:text-secondary"
                                @click="applyTemplate(btn.id)"
                            >
                                {{ btn.label }}
                            </button>
                        </div>
                    </div>
                    <div class="p-lg">
                        <PermissionsMatrix
                            v-model="form.permissions"
                            :schema="schema"
                            :disabled="isSuperAdmin"
                        />
                    </div>
                </section>

                <section
                    v-if="!isSelf"
                    class="rounded-xl border border-error/20 bg-surface-container p-lg"
                >
                    <h3
                        class="mb-md flex items-center gap-2 font-serif text-headline-sm text-error"
                    >
                        <IconShieldOff class="size-6" stroke="1.5" />
                        Danger Zone
                    </h3>
                    <div class="grid grid-cols-1 gap-lg md:grid-cols-2">
                        <div
                            class="flex flex-col justify-between gap-3 rounded-lg border border-outline-variant bg-white p-md sm:flex-row sm:items-center"
                        >
                            <div>
                                <h4 class="font-sans text-title-lg text-primary">
                                    Deactivate Account
                                </h4>
                                <p class="text-body-sm text-on-surface-variant">
                                    Temporarily disable all access
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg bg-outline px-4 py-2 text-white transition-all hover:bg-primary disabled:opacity-50"
                                :disabled="admin.status === 'inactive'"
                                @click="deactivate"
                            >
                                Deactivate
                            </button>
                        </div>
                        <div
                            class="flex flex-col justify-between gap-3 rounded-lg border border-error/30 bg-white p-md sm:flex-row sm:items-center"
                        >
                            <div>
                                <h4 class="font-sans text-title-lg text-error">
                                    Remove Admin Access
                                </h4>
                                <p class="text-body-sm text-on-surface-variant">
                                    Permanently delete admin profile
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center gap-1 rounded-lg border-2 border-error px-4 py-2 text-error transition-all hover:bg-error hover:text-white"
                                @click="removeAdmin"
                            >
                                <IconTrash class="size-4" stroke="1.5" />
                                Remove
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-lg">
                <div
                    class="rounded-xl border border-outline-variant bg-white p-lg text-center shadow-sm"
                >
                    <div
                        class="mx-auto mb-md flex size-28 items-center justify-center overflow-hidden rounded-full border-4 border-primary-fixed bg-surface-container-high font-sans text-2xl font-semibold text-primary shadow-inner"
                    >
                        <img
                            v-if="admin.avatar"
                            :src="admin.avatar"
                            :alt="admin.name"
                            class="size-full object-cover"
                        />
                        <span v-else>{{ initials }}</span>
                    </div>
                    <h3 class="font-serif text-headline-sm text-primary">
                        {{ admin.name }}
                    </h3>
                    <p class="mb-md text-on-surface-variant">{{ admin.email }}</p>
                    <div
                        class="inline-block rounded-full bg-primary-fixed px-4 py-1 font-sans text-label-caps uppercase text-on-primary-fixed"
                    >
                        {{ roleLabel }}
                    </div>
                </div>

                <div
                    class="rounded-xl border border-outline-variant bg-white p-lg shadow-sm"
                >
                    <h4 class="mb-md font-sans text-title-lg text-primary">
                        Current Access Summary
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="item in accessSummary"
                            :key="item.label"
                            class="rounded-full border border-outline-variant bg-tertiary-fixed px-3 py-1 text-[12px] font-bold text-on-tertiary-fixed"
                        >
                            {{ item.label }}: {{ item.level }}
                        </span>
                        <span
                            v-if="!accessSummary.length"
                            class="text-body-sm text-on-surface-variant"
                        >
                            No scopes granted
                        </span>
                    </div>
                </div>
            </aside>
        </div>
    </AdminLayout>
</template>
