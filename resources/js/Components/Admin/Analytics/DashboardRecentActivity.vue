<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { IconShoppingCart, IconStar, IconUserPlus } from '@tabler/icons-vue';
import type { Component } from 'vue';

type ActivityItem = {
    type: string;
    title: string;
    at: string | null;
};

defineProps<{
    items: ActivityItem[];
}>();

const icons: Record<string, Component> = {
    order: IconShoppingCart,
    review: IconStar,
    user: IconUserPlus,
};

const iconClasses: Record<string, string> = {
    order: 'bg-accent text-white',
    review: 'bg-warning text-white',
    user: 'bg-info text-white',
};

function formatWhen(at: string | null): string {
    if (!at) {
        return '';
    }

    return new Date(at).toLocaleString('en-GB', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <div>
        <h2 class="mb-6 font-title-lg text-primary">Recent Activity</h2>

        <div
            v-if="items.length"
            class="relative space-y-8 before:absolute before:bottom-0 before:left-[11px] before:top-2 before:w-px before:bg-outline-variant"
        >
            <div v-for="(item, index) in items" :key="`${item.type}-${item.at}-${index}`" class="relative pl-8">
                <div
                    class="absolute left-0 top-1 flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white"
                    :class="iconClasses[item.type] ?? 'bg-primary text-white'"
                >
                    <component
                        :is="icons[item.type] ?? IconShoppingCart"
                        :size="12"
                        stroke-width="1.5"
                    />
                </div>
                <p class="text-sm font-semibold text-primary">{{ item.title }}</p>
                <p class="text-xs text-outline">{{ formatWhen(item.at) }}</p>
            </div>
        </div>

        <EmptyState
            v-else
            title="No recent activity"
            description="Orders, reviews, and signups will show up in this timeline."
            class="!py-10 !shadow-none"
        />
    </div>
</template>
