<script setup>
import AttributeFormPanel from '@/Components/Admin/AttributeFormPanel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconGripVertical,
    IconHistory,
    IconInfoCircle,
    IconPlus,
    IconTrash,
    IconArrowsMove,
} from '@tabler/icons-vue';
import Sortable from 'sortablejs';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    attributes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.attributes ?? {},
);
const isSuperAdmin = computed(
    () => page.props.auth?.user?.role === 'super_admin',
);
const canCreate = computed(
    () => isSuperAdmin.value || permissions.value.create === true,
);
const canEdit = computed(
    () => isSuperAdmin.value || permissions.value.edit === true,
);
const canDelete = computed(
    () => isSuperAdmin.value || permissions.value.delete === true,
);

const editingId = ref(null);

const blankValue = () => ({
    id: null,
    value: '',
    color_swatch: null,
    sort_order: 1,
});

const form = useForm({
    name: '',
    type: 'text',
    display_type: 'pills',
    values: [blankValue()],
});

const editingAttribute = computed(() =>
    props.attributes.find((item) => item.id === editingId.value) ?? null,
);

const isEditing = computed(() => editingId.value !== null);

const fillFormFromAttribute = (attribute) => {
    form.clearErrors();
    form.name = attribute.name;
    form.type = attribute.type;
    form.display_type = attribute.display_type;

    const values = Array.isArray(attribute.values)
        ? attribute.values
        : (attribute.values?.data ?? []);

    form.values =
        values.length > 0
            ? values.map((value, index) => ({
                  id: value.id,
                  value: value.value,
                  color_swatch: value.color_swatch,
                  sort_order: value.sort_order ?? index + 1,
              }))
            : [blankValue()];
};

const resetCreateForm = () => {
    editingId.value = null;
    form.clearErrors();
    form.name = '';
    form.type = 'text';
    form.display_type = 'pills';
    form.values = [blankValue()];
};

const startCreate = () => {
    if (!canCreate.value) {
        return;
    }
    resetCreateForm();
};

const startEdit = (attribute) => {
    if (!canEdit.value) {
        return;
    }
    editingId.value = attribute.id;
    fillFormFromAttribute(attribute);
};

const discard = () => {
    if (editingAttribute.value) {
        fillFormFromAttribute(editingAttribute.value);
        return;
    }
    resetCreateForm();
};

const save = () => {
    const preparedValues = form.values
        .filter((row) => row.value?.trim())
        .map((row, index) => ({
            ...(row.id ? { id: row.id } : {}),
            value: row.value.trim(),
            color_swatch:
                form.type === 'color' ? row.color_swatch || null : null,
            sort_order: index + 1,
        }));

    form.values = preparedValues;

    if (isEditing.value) {
        form.put(route('admin.attributes.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                nextTick(() => {
                    const current = props.attributes.find(
                        (item) => item.id === editingId.value,
                    );
                    if (current) {
                        fillFormFromAttribute(current);
                    }
                });
            },
        });
        return;
    }

    form.post(route('admin.attributes.store'), {
        preserveScroll: true,
        onSuccess: () => resetCreateForm(),
    });
};

const deleteAttribute = (attribute) => {
    if (!canDelete.value) {
        return;
    }

    if ((attribute.products_count ?? 0) > 0) {
        alert(
            `"${attribute.name}" is used by ${attribute.products_count} product(s) and cannot be deleted until those associations are removed.`,
        );
        return;
    }

    if (!confirm(`Delete attribute "${attribute.name}"? This cannot be undone.`)) {
        return;
    }

    router.delete(route('admin.attributes.destroy', attribute.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (editingId.value === attribute.id) {
                resetCreateForm();
            }
        },
    });
};

const typeBadgeClass = (type) =>
    type === 'color'
        ? 'bg-secondary-fixed text-on-secondary-fixed-variant'
        : 'bg-outline-variant/30 text-on-surface-variant';

const tableBodyRef = ref(null);
let tableSortable = null;

const initTableSortable = () => {
    if (!tableBodyRef.value || !canEdit.value) {
        tableSortable?.destroy();
        tableSortable = null;
        return;
    }

    tableSortable?.destroy();

    tableSortable = Sortable.create(tableBodyRef.value, {
        handle: '.attr-drag-handle',
        animation: 150,
        draggable: '.attribute-row',
        onEnd() {
            const ordered_ids = [
                ...tableBodyRef.value.querySelectorAll('.attribute-row'),
            ].map((el) => Number(el.dataset.attributeId));

            router.patch(
                route('admin.attributes.reorder'),
                { ordered_ids },
                { preserveScroll: true },
            );
        },
    });
};

watch(
    () => props.attributes,
    (list) => {
        if (editingId.value) {
            const current = list.find((item) => item.id === editingId.value);
            if (!current) {
                resetCreateForm();
            }
        }
        nextTick(initTableSortable);
    },
    { deep: true },
);

onMounted(() => nextTick(initTableSortable));
onBeforeUnmount(() => tableSortable?.destroy());
</script>

<template>
    <AdminLayout title="Attributes" breadcrumb="Attributes">
        <Head title="Attributes" />

        <section
            class="mb-xl flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end"
        >
            <div>
                <nav
                    class="mb-xs flex items-center gap-xs text-body-sm text-on-surface-variant"
                >
                    <span>Catalogue</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-secondary">Attributes</span>
                </nav>
                <h1 class="font-serif text-headline-lg text-primary">
                    Global Attributes
                </h1>
            </div>

            <button
                v-if="canCreate"
                type="button"
                class="inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-secondary px-8 font-sans text-title-lg text-on-secondary shadow-md transition-all hover:brightness-110"
                @click="startCreate"
            >
                <IconPlus class="size-5" stroke="2" />
                Add New Attribute
            </button>
        </section>

        <div class="grid grid-cols-12 items-start gap-lg">
            <!-- Left: table -->
            <div class="col-span-12 space-y-md lg:col-span-7">
                <div
                    class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-card"
                >
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-outline-variant bg-surface-container"
                            >
                                <th
                                    class="p-4 font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Sort
                                </th>
                                <th
                                    class="p-4 font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Name
                                </th>
                                <th
                                    class="p-4 font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Type
                                </th>
                                <th
                                    class="p-4 text-center font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Values
                                </th>
                                <th
                                    class="p-4 text-center font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Usage
                                </th>
                                <th
                                    class="p-4 text-right font-sans text-label-caps uppercase text-on-surface-variant"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            ref="tableBodyRef"
                            class="divide-y divide-outline-variant"
                        >
                            <tr
                                v-for="attribute in attributes"
                                :key="attribute.id"
                                class="attribute-row transition-colors hover:bg-surface-container-low"
                                :class="{
                                    'bg-secondary-fixed/30':
                                        editingId === attribute.id,
                                }"
                                :data-attribute-id="attribute.id"
                            >
                                <td class="p-4">
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="attr-drag-handle cursor-grab text-outline active:cursor-grabbing"
                                        title="Drag to reorder"
                                    >
                                        <IconGripVertical
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                    <span v-else class="text-outline">
                                        <IconGripVertical
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                    </span>
                                </td>
                                <td
                                    class="p-4 font-sans text-title-lg text-primary"
                                >
                                    {{ attribute.name }}
                                </td>
                                <td class="p-4">
                                    <span
                                        class="rounded-md px-3 py-1 font-sans text-label-caps font-bold uppercase"
                                        :class="typeBadgeClass(attribute.type)"
                                    >
                                        {{ attribute.type }}
                                    </span>
                                </td>
                                <td class="p-4 text-center text-body-lg">
                                    {{ attribute.values_count ?? 0 }}
                                </td>
                                <td class="p-4 text-center text-body-lg">
                                    {{ attribute.products_count ?? 0 }}
                                </td>
                                <td class="space-x-3 p-4 text-right">
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="font-sans text-label-caps uppercase hover:underline"
                                        :class="
                                            editingId === attribute.id
                                                ? 'text-secondary'
                                                : 'text-on-surface-variant hover:text-secondary'
                                        "
                                        @click="startEdit(attribute)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="canDelete"
                                        type="button"
                                        class="inline-flex items-center text-error hover:underline"
                                        title="Delete"
                                        @click="deleteAttribute(attribute)"
                                    >
                                        <IconTrash
                                            :size="16"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="attributes.length === 0">
                                <td
                                    colspan="6"
                                    class="p-8 text-center text-body-sm text-on-surface-variant"
                                >
                                    No attributes yet. Create your first global
                                    attribute to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: form panel -->
            <div class="col-span-12 sticky top-24 lg:col-span-5">
                <AttributeFormPanel
                    v-if="canCreate || canEdit"
                    :form="form"
                    :is-editing="isEditing"
                    :editing-name="editingAttribute?.name ?? ''"
                    @save="save"
                    @discard="discard"
                />
                <div
                    v-else
                    class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-lg shadow-card"
                >
                    <p class="text-body-sm text-on-surface-variant">
                        You do not have permission to create or edit attributes.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footnotes -->
        <div
            class="mt-xxl grid grid-cols-1 gap-lg border-t border-outline-variant pt-lg md:grid-cols-3"
        >
            <div class="flex items-start gap-4">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary-fixed text-secondary"
                >
                    <IconInfoCircle :size="20" stroke-width="1.5" />
                </div>
                <div>
                    <h4 class="font-sans text-title-lg text-primary">
                        Global Scope
                    </h4>
                    <p class="text-body-sm text-on-surface-variant">
                        Attributes defined here are available across all product
                        categories including cakes, pastries, and artisanal
                        breads.
                    </p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary-fixed text-secondary"
                >
                    <IconArrowsMove :size="20" stroke-width="1.5" />
                </div>
                <div>
                    <h4 class="font-sans text-title-lg text-primary">
                        Sort Order
                    </h4>
                    <p class="text-body-sm text-on-surface-variant">
                        The arrangement here dictates how filters appear to
                        customers on the frontend shop pages.
                    </p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary-fixed text-secondary"
                >
                    <IconHistory :size="20" stroke-width="1.5" />
                </div>
                <div>
                    <h4 class="font-sans text-title-lg text-primary">
                        Usage Metrics
                    </h4>
                    <p class="text-body-sm text-on-surface-variant">
                        Attributes currently in use by active products cannot be
                        deleted until associations are removed.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
