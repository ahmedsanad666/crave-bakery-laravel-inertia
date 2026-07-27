<script setup>
import Sortable from 'sortablejs';
import { Link, router } from '@inertiajs/vue3';
import {
    IconChevronDown,
    IconChevronRight,
    IconCirclePlus,
    IconCategory,
    IconEdit,
    IconGripVertical,
    IconTrash,
} from '@tabler/icons-vue';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    nodes: {
        type: Array,
        default: () => [],
    },
    parentId: {
        type: [Number, null],
        default: null,
    },
    depth: {
        type: Number,
        default: 0,
    },
    expandedIds: {
        type: Object,
        required: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    canDelete: {
        type: Boolean,
        default: false,
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const listRef = ref(null);
let sortable = null;

const statusClass = (status) =>
    ({
        active: 'bg-success/10 text-success',
        draft: 'bg-surface-container-highest text-on-surface-variant',
        archived: 'bg-surface-container-high text-on-surface-variant',
    })[status] ?? 'bg-surface-container-high text-on-surface-variant';

const hasChildren = (node) => (node.children?.length ?? 0) > 0;
const isExpanded = (id) => props.expandedIds.has(id);

const toggle = (id) => {
    if (props.expandedIds.has(id)) {
        props.expandedIds.delete(id);
    } else {
        props.expandedIds.add(id);
    }
};

const slugPath = (slug) => `/${slug}`;

const initSortable = () => {
    if (!listRef.value || !props.canEdit) {
        return;
    }

    sortable?.destroy();

    sortable = Sortable.create(listRef.value, {
        group: 'category-tree',
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'category-drag-ghost',
        draggable: '.category-tree-item',
        onEnd(evt) {
            const parentRaw = evt.to.dataset.parentId;
            const parent_id =
                parentRaw === 'null' || parentRaw === '' ? null : Number(parentRaw);

            const ordered_ids = [
                ...evt.to.querySelectorAll(':scope > .category-tree-item'),
            ].map((el) => Number(el.dataset.categoryId));

            const movedId = Number(evt.item.dataset.categoryId);

            router.patch(
                route('admin.categories.reorder', movedId),
                {
                    parent_id,
                    ordered_ids,
                },
                { preserveScroll: true },
            );
        },
    });
};

const deleteCategory = (node) => {
    if (!confirm(`Delete "${node.name}"? This cannot be undone.`)) {
        return;
    }

    router.delete(route('admin.categories.destroy', node.id), {
        preserveScroll: true,
    });
};

onMounted(() => nextTick(initSortable));

watch(
    () => [props.nodes, props.canEdit],
    () => nextTick(initSortable),
    { deep: true },
);

onBeforeUnmount(() => sortable?.destroy());
</script>

<template>
    <div
        ref="listRef"
        class="space-y-1"
        :data-parent-id="parentId ?? 'null'"
    >
        <div
            v-for="node in nodes"
            :key="node.id"
            :data-category-id="node.id"
            class="category-tree-item group"
        >
            <div
                class="grid grid-cols-[1fr_200px_150px_150px_180px] items-center rounded-lg p-md transition-colors hover:bg-surface-container"
                :class="[
                    depth > 0 ? 'category-tree-connector relative' : '',
                    node.status === 'draft' ? 'bg-surface-container-low opacity-80' : '',
                ]"
            >
                <div class="flex min-w-0 items-center gap-md">
                    <button
                        v-if="canEdit"
                        type="button"
                        class="drag-handle cursor-grab text-outline transition-colors hover:text-primary active:cursor-grabbing"
                        title="Drag to reorder"
                    >
                        <IconGripVertical class="size-5" stroke="1.5" />
                    </button>
                    <span
                        v-else
                        class="size-5 shrink-0"
                    />

                    <button
                        v-if="hasChildren(node)"
                        type="button"
                        class="text-on-surface-variant transition-colors hover:text-primary"
                        @click="toggle(node.id)"
                    >
                        <IconChevronDown
                            v-if="isExpanded(node.id)"
                            class="size-5"
                            stroke="1.5"
                        />
                        <IconChevronRight
                            v-else
                            class="size-5"
                            stroke="1.5"
                        />
                    </button>
                    <span
                        v-else
                        class="size-5 shrink-0 text-outline opacity-30"
                    >
                        <IconChevronRight class="size-5" stroke="1.5" />
                    </span>

                    <div
                        class="shrink-0 overflow-hidden rounded-lg border border-outline-variant bg-surface-variant"
                        :class="depth === 0 ? 'size-10' : 'size-8'"
                    >
                        <img
                            v-if="node.image"
                            :src="node.image"
                            :alt="node.name"
                            class="size-full object-cover"
                        />
                        <div
                            v-else
                            class="flex size-full items-center justify-center"
                        >
                            <IconCategory
                                class="size-4 text-outline"
                                stroke="1.5"
                            />
                        </div>
                    </div>

                    <span
                        class="truncate text-primary"
                        :class="
                            depth === 0
                                ? 'font-serif text-headline-sm'
                                : 'font-sans text-title-lg text-on-surface'
                        "
                    >
                        {{ node.name }}
                    </span>
                </div>

                <div class="truncate text-body-sm italic text-on-surface-variant">
                    {{ slugPath(node.slug) }}
                </div>

                <div class="text-center">
                    <span
                        class="inline-flex rounded-full px-3 py-1 font-sans text-body-sm font-bold"
                        :class="
                            depth === 0
                                ? 'bg-surface-container-high text-on-surface'
                                : 'bg-surface-container text-on-surface-variant font-medium text-xs'
                        "
                    >
                        {{ node.products_count ?? 0 }}
                    </span>
                </div>

                <div class="text-center">
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider"
                        :class="statusClass(node.status)"
                    >
                        {{ node.status }}
                    </span>
                </div>

                <div class="flex justify-end gap-1">
                    <Link
                        v-if="canEdit"
                        :href="route('admin.categories.edit', node.id)"
                        class="rounded-lg p-2 transition-colors hover:bg-primary-fixed-dim"
                        title="Edit"
                    >
                        <IconEdit
                            class="size-5 text-primary"
                            :class="depth > 0 ? 'size-[18px]' : ''"
                            stroke="1.5"
                        />
                    </Link>
                    <Link
                        v-if="canCreate"
                        :href="`${route('admin.categories.create')}?parent_id=${node.id}`"
                        class="rounded-lg p-2 transition-colors hover:bg-primary-fixed-dim"
                        title="Add child category"
                    >
                        <IconCirclePlus
                            class="size-5 text-primary"
                            stroke="1.5"
                        />
                    </Link>
                    <button
                        v-if="canDelete"
                        type="button"
                        class="rounded-lg p-2 transition-colors hover:text-error"
                        title="Delete"
                        @click="deleteCategory(node)"
                    >
                        <IconTrash
                            class="size-5"
                            :class="depth > 0 ? 'size-[18px]' : ''"
                            stroke="1.5"
                        />
                    </button>
                </div>
            </div>

            <div
                v-if="hasChildren(node) && isExpanded(node.id)"
                class="ml-8 border-l border-outline-variant py-1 pl-4"
            >
                <CategoryTreeList
                    :nodes="node.children"
                    :parent-id="node.id"
                    :depth="depth + 1"
                    :expanded-ids="expandedIds"
                    :can-edit="canEdit"
                    :can-delete="canDelete"
                    :can-create="canCreate"
                />
            </div>
        </div>
    </div>
</template>
