<script setup>
import { Link } from '@inertiajs/vue3';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});

const pages = computed(() => {
    const { current_page: current, last_page: last } = props.pagination;
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

const pageUrl = (page) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', String(page));

    return `${url.pathname}${url.search}`;
};
</script>

<template>
    <nav
        v-if="pagination.last_page > 1"
        class="flex items-center gap-1"
        aria-label="Pagination"
    >
        <Link
            v-if="pagination.current_page > 1"
            :href="pageUrl(pagination.current_page - 1)"
            preserve-scroll
            class="inline-flex size-9 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition-colors hover:bg-surface-container"
            aria-label="Previous page"
        >
            <IconChevronLeft class="size-4" stroke="1.5" />
        </Link>
        <span
            v-else
            class="inline-flex size-9 items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40"
            aria-hidden="true"
        >
            <IconChevronLeft class="size-4" stroke="1.5" />
        </span>

        <span
            v-for="(item, index) in pages"
            :key="`${item.type}-${item.page ?? index}`"
        >
            <span
                v-if="item.type === 'ellipsis'"
                class="inline-block px-1 text-on-surface-variant"
            >…</span>
            <Link
                v-else
                :href="pageUrl(item.page)"
                preserve-scroll
                class="inline-flex min-w-9 items-center justify-center rounded-lg px-2 py-1.5 text-sm font-medium transition-colors"
                :class="
                    item.page === pagination.current_page
                        ? 'bg-primary text-on-primary'
                        : 'border border-outline-variant text-on-surface-variant hover:bg-surface-container'
                "
                :aria-current="item.page === pagination.current_page ? 'page' : undefined"
            >
                {{ item.page }}
            </Link>
        </span>

        <Link
            v-if="pagination.current_page < pagination.last_page"
            :href="pageUrl(pagination.current_page + 1)"
            preserve-scroll
            class="inline-flex size-9 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition-colors hover:bg-surface-container"
            aria-label="Next page"
        >
            <IconChevronRight class="size-4" stroke="1.5" />
        </Link>
        <span
            v-else
            class="inline-flex size-9 items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40"
            aria-hidden="true"
        >
            <IconChevronRight class="size-4" stroke="1.5" />
        </span>
    </nav>
</template>
