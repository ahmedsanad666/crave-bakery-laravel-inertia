<script setup>
import { Link } from '@inertiajs/vue3';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
    showingLabel: {
        type: String,
        default: '',
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

const defaultShowingLabel = computed(() => {
    const { from, to, total } = props.pagination;
    if (!total) {
        return '';
    }
    return `Showing ${from}–${to} of ${total}`;
});

const mobileShowingLabel = computed(
    () => props.showingLabel || defaultShowingLabel.value,
);
</script>

<template>
    <nav
        v-if="pagination.last_page > 1"
        aria-label="Pagination"
    >
        <!-- Mobile: Prev / Next -->
        <div class="flex flex-col gap-sm md:hidden">
            <p
                v-if="mobileShowingLabel"
                class="text-center text-body-sm text-on-surface-variant"
            >
                <template v-if="pagination.from != null">
                    Showing
                    <span class="font-bold text-primary">
                        {{ pagination.from }}–{{ pagination.to }}
                    </span>
                    of {{ pagination.total }}
                </template>
                <template v-else>
                    {{ mobileShowingLabel }}
                </template>
            </p>
            <div class="grid grid-cols-2 gap-sm">
                <Link
                    v-if="pagination.current_page > 1"
                    :href="pageUrl(pagination.current_page - 1)"
                    preserve-scroll
                    class="inline-flex h-11 items-center justify-center gap-1 rounded-lg border border-outline-variant font-bold text-on-surface-variant transition-colors active:bg-surface-container"
                >
                    <IconChevronLeft class="size-4" stroke="1.5" />
                    Previous
                </Link>
                <span
                    v-else
                    class="inline-flex h-11 cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-outline-variant font-bold text-on-surface-variant opacity-30"
                    aria-disabled="true"
                >
                    <IconChevronLeft class="size-4" stroke="1.5" />
                    Previous
                </span>

                <Link
                    v-if="pagination.current_page < pagination.last_page"
                    :href="pageUrl(pagination.current_page + 1)"
                    preserve-scroll
                    class="inline-flex h-11 items-center justify-center gap-1 rounded-lg border border-outline-variant font-bold text-primary transition-colors active:bg-surface-container"
                >
                    Next
                    <IconChevronRight class="size-4" stroke="1.5" />
                </Link>
                <span
                    v-else
                    class="inline-flex h-11 cursor-not-allowed items-center justify-center gap-1 rounded-lg border border-outline-variant font-bold text-on-surface-variant opacity-30"
                    aria-disabled="true"
                >
                    Next
                    <IconChevronRight class="size-4" stroke="1.5" />
                </span>
            </div>
        </div>

        <!-- Tablet / desktop: numbered pages -->
        <div class="hidden items-center gap-1 md:flex">
            <Link
                v-if="pagination.current_page > 1"
                :href="pageUrl(pagination.current_page - 1)"
                preserve-scroll
                class="inline-flex size-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition-colors hover:bg-surface-container"
                aria-label="Previous page"
            >
                <IconChevronLeft class="size-4" stroke="1.5" />
            </Link>
            <span
                v-else
                class="inline-flex size-8 items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40"
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
                    class="inline-flex size-8 min-w-8 items-center justify-center rounded-lg text-[12px] font-bold transition-colors"
                    :class="
                        item.page === pagination.current_page
                            ? 'bg-secondary text-on-secondary'
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
                class="inline-flex size-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition-colors hover:bg-surface-container"
                aria-label="Next page"
            >
                <IconChevronRight class="size-4" stroke="1.5" />
            </Link>
            <span
                v-else
                class="inline-flex size-8 items-center justify-center rounded-lg border border-outline-variant text-outline opacity-40"
                aria-hidden="true"
            >
                <IconChevronRight class="size-4" stroke="1.5" />
            </span>
        </div>
    </nav>
</template>
