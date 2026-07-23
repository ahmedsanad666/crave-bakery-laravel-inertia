<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { IconArrowLeft, IconArrowRight } from '@tabler/icons-vue';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
    /** Current filter query (without page) preserved when changing pages */
    query: {
        type: Object,
        default: () => ({}),
    },
    routeName: {
        type: String,
        default: 'products.index',
    },
});

const pages = computed(() => {
    const current = props.meta.current_page ?? 1;
    const last = props.meta.last_page ?? 1;
    const items = [];

    for (let page = 1; page <= last; page++) {
        if (
            page === 1
            || page === last
            || (page >= current - 1 && page <= current + 1)
        ) {
            items.push({ type: 'page', page });
        } else if (items[items.length - 1]?.type !== 'ellipsis') {
            items.push({ type: 'ellipsis' });
        }
    }

    return items;
});

const goToPage = (page) => {
    if (page < 1 || page > (props.meta.last_page ?? 1)) {
        return;
    }

    router.get(
        route(props.routeName),
        {
            ...props.query,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>

<template>
    <nav
        v-if="meta.last_page > 1"
        class="mt-xxl flex items-center justify-center gap-md"
        aria-label="Pagination"
    >
        <button
            type="button"
            class="flex items-center gap-xs rounded-full border border-border-base px-md py-2 font-sans text-body-sm text-on-surface-variant transition-colors hover:bg-surface-container-high disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="meta.current_page <= 1"
            @click="goToPage(meta.current_page - 1)"
        >
            <IconArrowLeft :size="18" stroke-width="1.5" />
            Previous
        </button>

        <div class="flex items-center gap-sm">
            <div
                v-for="(item, index) in pages"
                :key="item.type === 'page' ? `page-${item.page}` : `ellipsis-${index}`"
                class="contents"
            >
                <span
                    v-if="item.type === 'ellipsis'"
                    class="text-on-surface-variant"
                >
                    …
                </span>
                <button
                    v-else
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full font-sans text-body-sm transition-colors"
                    :class="
                        item.page === meta.current_page
                            ? 'bg-primary-container font-bold text-white'
                            : 'hover:bg-surface-container-high'
                    "
                    @click="goToPage(item.page)"
                >
                    {{ item.page }}
                </button>
            </div>
        </div>

        <button
            type="button"
            class="flex items-center gap-xs rounded-full border border-border-base px-md py-2 font-sans text-body-sm text-on-surface-variant transition-colors hover:bg-surface-container-high disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="meta.current_page >= meta.last_page"
            @click="goToPage(meta.current_page + 1)"
        >
            Next
            <IconArrowRight :size="18" stroke-width="1.5" />
        </button>
    </nav>
</template>
