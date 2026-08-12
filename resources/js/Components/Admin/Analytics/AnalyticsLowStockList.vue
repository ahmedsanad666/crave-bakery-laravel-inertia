<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { IconAlertTriangle } from '@tabler/icons-vue';
import { computed } from 'vue';

type LowStockItem = {
    id: number;
    name: string;
    slug: string;
    stock_quantity: number;
    low_stock_threshold: number;
};

const props = defineProps<{
    items: LowStockItem[];
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

function isCritical(item: LowStockItem): boolean {
    return item.stock_quantity <= item.low_stock_threshold / 2;
}
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <h2 class="font-title-lg text-primary">Low Stock Alerts</h2>
            <span
                v-if="items.length"
                class="rounded-full bg-error px-2 py-1 text-[10px] font-bold uppercase text-white"
            >
                {{ items.length }} items
            </span>
        </div>

        <div v-if="items.length" class="space-y-4">
            <component
                :is="canEditProducts ? Link : 'div'"
                v-for="item in items"
                :key="item.id"
                :href="canEditProducts ? route('admin.products.edit', item.id) : undefined"
                class="flex items-center justify-between rounded-lg border p-3 transition-colors"
                :class="[
                    isCritical(item)
                        ? 'border-error/20 bg-error/10'
                        : 'border-warning/20 bg-warning/10',
                    canEditProducts ? 'hover:opacity-90' : '',
                ]"
            >
                <div class="flex items-center gap-3">
                    <IconAlertTriangle
                        :size="20"
                        stroke-width="1.5"
                        :class="isCritical(item) ? 'text-error' : 'text-warning'"
                    />
                    <span class="text-sm font-semibold text-primary">{{ item.name }}</span>
                </div>
                <span
                    class="rounded bg-white px-2 py-1 text-xs font-bold"
                    :class="isCritical(item) ? 'text-error' : 'text-warning'"
                >
                    {{ item.stock_quantity }} left
                </span>
            </component>
        </div>

        <EmptyState
            v-else
            title="Stock levels healthy"
            description="No products are below their low stock threshold."
            class="!shadow-none !py-10"
        />
    </div>
</template>
