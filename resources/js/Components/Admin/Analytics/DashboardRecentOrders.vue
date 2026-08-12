<script setup lang="ts">
import EmptyState from '@/Components/Shared/EmptyState.vue';
import { useAdminFormat } from '@/Composables/useAdminFormat';
import { Link } from '@inertiajs/vue3';

type RecentOrder = {
    id: number;
    order_number: string;
    customer_name: string;
    total: number;
    status: string;
};

defineProps<{
    orders: RecentOrder[];
}>();

const { formatMoney } = useAdminFormat();

const statusStyles: Record<string, string> = {
    pending: 'bg-outline-variant text-on-surface-variant',
    processing: 'bg-amber-100 text-amber-700',
    shipped: 'bg-blue-100 text-blue-700',
    delivered: 'bg-green-100 text-green-700',
    cancelled: 'bg-error/10 text-error',
};
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <h2 class="font-title-lg text-primary">Recent Orders</h2>
            <Link
                :href="route('admin.orders.index')"
                class="text-sm font-bold text-accent hover:underline"
            >
                View All
            </Link>
        </div>

        <div v-if="orders.length" class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-outline-variant text-label-caps text-outline">
                    <tr>
                        <th class="pb-3 font-bold">ID</th>
                        <th class="pb-3 font-bold">Customer</th>
                        <th class="pb-3 font-bold">Amount</th>
                        <th class="pb-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant text-sm">
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="transition-colors hover:bg-surface-container-low"
                    >
                        <td class="py-4 font-semibold">
                            <Link
                                :href="route('admin.orders.show', order.id)"
                                class="hover:text-accent"
                            >
                                {{ order.order_number }}
                            </Link>
                        </td>
                        <td class="py-4">{{ order.customer_name }}</td>
                        <td class="py-4 font-bold text-primary">
                            {{ formatMoney(order.total) }}
                        </td>
                        <td class="py-4">
                            <span
                                class="rounded px-2 py-1 text-[10px] font-bold uppercase"
                                :class="statusStyles[order.status] ?? 'bg-surface-container text-on-surface-variant'"
                            >
                                {{ order.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <EmptyState
            v-else
            title="No recent orders"
            description="New orders will show up here as customers check out."
            class="!py-10 !shadow-none"
        />
    </div>
</template>
