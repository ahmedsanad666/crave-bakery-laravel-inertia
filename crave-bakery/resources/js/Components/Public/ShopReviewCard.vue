<script setup>
import { computed } from 'vue';
import { IconCircleCheck, IconStarFilled } from '@tabler/icons-vue';

const props = defineProps({
    review: {
        type: Object,
        required: true,
    },
});

const initials = computed(() => {
    const name = props.review.user?.name ?? 'Customer';
    const parts = name.trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0][0] ?? ''}${parts[1][0] ?? ''}`.toUpperCase();
});

const relativeDate = computed(() => {
    if (!props.review.created_at) {
        return '';
    }

    const created = new Date(props.review.created_at);
    const diffMs = Date.now() - created.getTime();
    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (days <= 0) {
        return 'Today';
    }

    if (days === 1) {
        return '1 day ago';
    }

    if (days < 7) {
        return `${days} days ago`;
    }

    if (days < 30) {
        const weeks = Math.floor(days / 7);

        return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
    }

    return created.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
});

const starCount = computed(() =>
    Math.min(5, Math.max(0, Math.round(Number(props.review.rating) || 0))),
);
</script>

<template>
    <article
        class="rounded-xl border border-outline-variant/30 bg-white p-xl shadow-card"
    >
        <div class="mb-md flex items-start justify-between gap-md">
            <div class="flex items-center gap-md">
                <div
                    v-if="review.user?.avatar"
                    class="h-12 w-12 overflow-hidden rounded-full bg-surface-container"
                >
                    <img
                        :src="review.user.avatar"
                        :alt="review.user.name"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div
                    v-else
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary-fixed font-bold text-primary"
                >
                    {{ initials }}
                </div>
                <div>
                    <h5 class="font-sans text-title-lg text-primary">
                        {{ review.user?.name ?? 'Customer' }}
                    </h5>
                    <span
                        v-if="review.is_verified_purchase"
                        class="mt-1 inline-flex items-center gap-xs rounded-lg border border-green-200 bg-green-50 px-sm py-xs text-[10px] font-bold uppercase tracking-wider text-green-700"
                    >
                        <IconCircleCheck :size="14" stroke-width="1.5" />
                        Verified Purchase
                    </span>
                </div>
            </div>
            <span class="shrink-0 font-sans text-body-sm text-on-surface-variant">
                {{ relativeDate }}
            </span>
        </div>

        <div class="mb-sm flex text-accent">
            <IconStarFilled
                v-for="i in starCount"
                :key="i"
                :size="18"
            />
        </div>

        <h6
            v-if="review.title"
            class="mb-sm font-sans text-title-lg text-primary"
        >
            {{ review.title }}
        </h6>

        <p class="leading-relaxed text-on-surface-variant">
            {{ review.body }}
        </p>

        <div
            v-if="review.admin_response"
            class="mt-md rounded-lg border border-outline-variant/40 bg-surface-container-low p-md"
        >
            <p class="mb-xs font-sans text-body-sm font-bold text-primary">
                Bakery response
            </p>
            <p class="text-body-sm text-on-surface-variant">
                {{ review.admin_response }}
            </p>
        </div>
    </article>
</template>
