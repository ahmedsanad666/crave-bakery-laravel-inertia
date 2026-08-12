<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import { IconStar, IconStarFilled } from '@tabler/icons-vue';

type RecentReview = {
    id: number;
    rating: number;
    title: string | null;
    body_excerpt: string;
    status: string;
    customer: { id: number; name: string; avatar: string | null } | null;
};

defineProps<{
    reviews: RecentReview[];
}>();
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <h2 class="font-title-lg text-primary">Recent Reviews</h2>
            <Link
                :href="route('admin.reviews.index')"
                class="text-sm font-bold text-accent hover:underline"
            >
                View All
            </Link>
        </div>

        <div v-if="reviews.length" class="space-y-6">
            <div
                v-for="(review, index) in reviews"
                :key="review.id"
                class="flex gap-4"
                :class="index > 0 ? 'border-t border-outline-variant/30 pt-4' : ''"
            >
                <img
                    v-if="review.customer?.avatar"
                    :src="review.customer.avatar"
                    :alt="review.customer.name"
                    class="h-10 w-10 shrink-0 rounded-full object-cover"
                />
                <div
                    v-else
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary-fixed text-xs font-bold text-primary"
                >
                    {{ (review.customer?.name ?? 'C').charAt(0).toUpperCase() }}
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-sm font-bold text-primary">
                            {{ review.customer?.name ?? 'Customer' }}
                        </span>
                        <div class="flex shrink-0 text-amber-400">
                            <component
                                :is="star <= review.rating ? IconStarFilled : IconStar"
                                v-for="star in 5"
                                :key="star"
                                :size="12"
                                stroke-width="1.5"
                            />
                        </div>
                    </div>
                    <p class="text-sm italic text-on-surface-variant">
                        “{{ review.body_excerpt || review.title || 'No comment' }}”
                    </p>
                </div>
            </div>
        </div>

        <EmptyState
            v-else
            title="No reviews yet"
            description="Customer reviews will appear here as they come in."
            class="!py-10 !shadow-none"
        />
    </div>
</template>
