<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type ProductRow = {
    id: number;
    name: string;
    slug: string;
    thumbnail: string | null;
    sold_count: number;
    revenue: number;
};

const props = defineProps<{
    products: ProductRow[];
}>();

const page = usePage();

const permissions = computed(
    () => (page.props.auth as { user?: { permissions?: { products?: { edit?: boolean } }; role?: string } })?.user?.permissions?.products ?? {},
);
const isSuperAdmin = computed(
    () => (page.props.auth as { user?: { role?: string } })?.user?.role === 'super_admin',
);
const canEditProducts = computed(
    () => isSuperAdmin.value || permissions.value.edit === true,
);

const maxSold = computed(() =>
    Math.max(...props.products.map((product) => product.sold_count), 1),
);

function progressWidth(soldCount: number): string {
    return `${Math.round((soldCount / maxSold.value) * 100)}%`;
}
</script>

<template>
    <div v-if="products.length" class="space-y-6">
        <div
            v-for="(product, index) in products"
            :key="product.id"
            class="flex items-center gap-4"
        >
            <span class="w-6 text-lg font-bold text-outline">{{ index + 1 }}</span>

            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-12 w-12 shrink-0 rounded-lg object-cover"
            />
            <div
                v-else
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-surface-container-low text-xs font-bold text-outline"
            >
                —
            </div>

            <div class="min-w-0 flex-1">
                <component
                    :is="canEditProducts ? Link : 'p'"
                    :href="canEditProducts ? route('admin.products.edit', product.id) : undefined"
                    class="truncate text-sm font-bold text-primary"
                    :class="canEditProducts ? 'hover:text-accent' : ''"
                >
                    {{ product.name }}
                </component>
                <div class="mt-1 h-1.5 w-full rounded-full bg-surface-container-low">
                    <div
                        class="h-full rounded-full bg-accent"
                        :style="{ width: progressWidth(product.sold_count) }"
                    />
                </div>
            </div>

            <span class="shrink-0 text-xs font-bold text-primary">
                {{ product.sold_count }} sold
            </span>
        </div>
    </div>

    <EmptyState
        v-else
        title="No product sales"
        description="Top sellers will show once products are ordered in this period."
        class="!shadow-none !py-10"
    />
</template>
