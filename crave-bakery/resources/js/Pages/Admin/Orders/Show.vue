<script setup>
import UpdateOrderStatusModal from '@/Components/Admin/UpdateOrderStatusModal.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCheck,
    IconChevronRight,
    IconCircleX,
    IconCooker,
    IconFileDescription,
    IconMail,
    IconPhone,
    IconPrinter,
    IconReceipt,
    IconTruck,
    IconConfetti,
    IconCircleCheck,
} from '@tabler/icons-vue';
import { computed, ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.orders ?? {},
);
const isSuperAdmin = computed(
    () => page.props.auth?.user?.role === 'super_admin',
);
const canUpdateStatus = computed(
    () => isSuperAdmin.value || permissions.value.update_status === true,
);
const canRefund = computed(
    () => isSuperAdmin.value || permissions.value.refund === true,
);

const showStatusModal = ref(false);
const modalInitialStatus = ref(null);
const internalNote = ref('');
const savingNote = ref(false);
const refunding = ref(false);

const placeholderImage =
    'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=160&q=80';

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value ?? 0));

const formatDateTime = (iso, options = {}) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        ...options,
    });
};

const formatTimelineTime = (iso) => {
    if (!iso) {
        return '';
    }
    return new Date(iso).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const customerName = computed(() => {
    if (props.order.customer?.name) {
        return props.order.customer.name;
    }
    return (
        `${props.order.first_name ?? ''} ${props.order.last_name ?? ''}`.trim() ||
        'Guest'
    );
});

const customerEmail = computed(
    () => props.order.customer?.email || props.order.email || '—',
);
const customerPhone = computed(
    () => props.order.customer?.phone || props.order.phone || '—',
);
const customerAvatar = computed(() => props.order.customer?.avatar || null);

const customerInitials = computed(() =>
    customerName.value
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase(),
);

const statusMeta = computed(() => {
    const map = {
        pending: {
            label: 'Pending',
            wrap: 'bg-amber-50 text-amber-800 border-amber-200',
            pulse: true,
        },
        processing: {
            label: 'Processing',
            wrap: 'bg-secondary-container text-on-secondary-container',
            pulse: true,
        },
        shipped: {
            label: 'Shipped',
            wrap: 'bg-purple-50 text-purple-800 border-purple-200',
            pulse: false,
        },
        delivered: {
            label: 'Delivered',
            wrap: 'bg-green-50 text-green-800 border-green-200',
            pulse: false,
        },
        cancelled: {
            label: 'Cancelled',
            wrap: 'bg-error-container/30 text-error border-error/20',
            pulse: false,
        },
    };
    return (
        map[props.order.status] ?? {
            label: props.order.status,
            wrap: 'bg-surface-container text-on-surface',
            pulse: false,
        }
    );
});

const paymentStatusLabel = computed(() => {
    const map = {
        pending: 'Pending',
        paid: 'Authorized & Paid',
        failed: 'Failed',
        refunded: 'Refunded',
    };
    return map[props.order.payment_status] ?? props.order.payment_status;
});

const paymentMethodLabel = computed(() => {
    const map = {
        cod: 'Cash on Delivery',
        stripe: 'Stripe',
        card: 'Card',
        paypal: 'PayPal',
        apple_pay: 'Apple Pay',
        google_pay: 'Google Pay',
        cash: 'Cash',
    };
    return map[props.order.payment_method] ?? (props.order.payment_method || '—');
});

const refundHelpText = computed(() => {
    if (props.order.payment_method === 'stripe') {
        return 'This will refund the charge via Stripe and mark the order as refunded.';
    }

    return 'This marks the COD payment as refunded in your records (no card charge).';
});

const deliveryMethodLabel = computed(() => {
    const map = {
        standard: 'Standard Delivery',
        express: "Baker's Express",
        pickup: 'Store Pickup',
    };
    return (
        map[props.order.delivery_method] ??
        (props.order.delivery_method || '—')
    );
});

const isExpress = computed(() => props.order.delivery_method === 'express');

const isCancelled = computed(() => props.order.status === 'cancelled');

const isCompleted = computed(() => props.order.status === 'delivered');

const paymentConfirmed = computed(
    () =>
        ['paid', 'refunded'].includes(props.order.payment_status) ||
        Boolean(props.order.paid_at),
);

const progressSteps = computed(() => {
    const status = props.order.status;
    const rank = {
        pending: 1,
        processing: 3,
        shipped: 4,
        delivered: 5,
        cancelled: 0,
    }[status] ?? 1;

    const steps = [
        {
            id: 'placed',
            label: 'Order Placed',
            icon: IconCheck,
            done: true,
            current: false,
        },
        {
            id: 'paid',
            label: 'Payment Confirmed',
            icon: IconCheck,
            // Stripe must be paid; COD may show confirmed once fulfillment starts
            // (cash collected at delivery) or when marked paid on deliver.
            done:
                paymentConfirmed.value ||
                (props.order.payment_method !== 'stripe' && rank >= 3),
            current: false,
        },
        {
            id: 'processing',
            label: 'Processing',
            icon: IconCooker,
            done: rank >= 3,
            current: status === 'processing',
        },
        {
            id: 'shipped',
            label: 'Out for Delivery',
            icon: IconTruck,
            done: rank >= 4,
            current: status === 'shipped',
        },
        {
            id: 'delivered',
            label: 'Delivered',
            icon: IconConfetti,
            done: rank >= 5,
            current: status === 'delivered',
        },
    ];

    // Mark earliest incomplete as current when not cancelled and no explicit current
    if (!isCancelled.value && !steps.some((s) => s.current)) {
        const next = steps.find((s) => !s.done);
        if (next) {
            next.current = true;
        } else if (status === 'delivered') {
            steps[steps.length - 1].current = true;
        }
    }

    return steps;
});

const progressPercent = computed(() => {
    if (isCancelled.value) {
        return 0;
    }
    const doneCount = progressSteps.value.filter((s) => s.done).length;
    const currentBonus = progressSteps.value.some((s) => s.current && !s.done)
        ? 0.5
        : 0;
    const total = progressSteps.value.length - 1;
    return Math.min(100, ((doneCount - 1 + currentBonus) / total) * 100);
});

const formatOptions = (attrs) => {
    if (!attrs || typeof attrs !== 'object') {
        return '';
    }
    if (Array.isArray(attrs)) {
        return attrs
            .map((a) =>
                typeof a === 'string'
                    ? a
                    : `${a.name ?? a.label ?? ''}: ${a.value ?? ''}`.trim(),
            )
            .filter(Boolean)
            .join(' | ');
    }
    return Object.entries(attrs)
        .map(([key, value]) => {
            if (value && typeof value === 'object') {
                return `${value.name ?? key}: ${value.value ?? value.label ?? ''}`;
            }
            return `${key}: ${value}`;
        })
        .join(' | ');
};

const activityItems = computed(() => {
    const items = [];

    items.push({
        id: 'received',
        title: 'Order Received',
        at: props.order.created_at,
        body: `Customer ${customerName.value} placed order ${props.order.order_number}.`,
        active: false,
        pending: false,
    });

    if (props.order.paid_at || paymentConfirmed.value) {
        items.push({
            id: 'paid',
            title: 'Payment Successful',
            at: props.order.paid_at || props.order.created_at,
            body: props.order.transaction_id
                ? `Transaction ${props.order.transaction_id} approved.`
                : `Payment marked as ${props.order.payment_status}.`,
            active: false,
            pending: false,
        });
    }

    const notes = Array.isArray(props.order.notes) ? [...props.order.notes] : [];
    notes.forEach((note, index) => {
        const type = note.type ?? 'note';
        let title = 'Internal Note';
        if (type === 'status_change') {
            title = note.status
                ? `Status → ${String(note.status).charAt(0).toUpperCase()}${String(note.status).slice(1)}`
                : 'Status Updated';
        } else if (type === 'refund') {
            title = 'Refund Issued';
        }

        items.push({
            id: `note-${index}`,
            title,
            at: note.created_at,
            body: note.body || '',
            active: false,
            pending: false,
        });
    });

    if (props.order.delivered_at) {
        items.push({
            id: 'delivered',
            title: 'Delivered',
            at: props.order.delivered_at,
            body: 'Order marked as delivered.',
            active: false,
            pending: false,
        });
    }

    if (
        props.order.estimated_delivery_at &&
        !props.order.delivered_at &&
        !isCancelled.value
    ) {
        items.push({
            id: 'eta',
            title: 'Delivery Pending',
            at: null,
            body: `Estimated: ${formatTimelineTime(props.order.estimated_delivery_at)}`,
            active: false,
            pending: true,
        });
    }

    // Sort chronological desc (newest first), pending last
    const dated = items.filter((i) => !i.pending);
    dated.sort((a, b) => new Date(b.at || 0) - new Date(a.at || 0));
    if (dated.length) {
        dated[0].active = true;
    }
    const pending = items.filter((i) => i.pending);
    return [...dated, ...pending];
});

const openStatusModal = (preset = null) => {
    if (!canUpdateStatus.value) {
        return;
    }
    modalInitialStatus.value = preset;
    showStatusModal.value = true;
};

const closeStatusModal = () => {
    showStatusModal.value = false;
    modalInitialStatus.value = null;
};

const saveInternalNote = () => {
    const body = internalNote.value.trim();
    if (!body || !canUpdateStatus.value || savingNote.value) {
        return;
    }

    savingNote.value = true;
    router.patch(
        route('admin.orders.update', props.order.id),
        {
            status: props.order.status,
            note: body,
            notify_customer: false,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                savingNote.value = false;
            },
            onSuccess: () => {
                internalNote.value = '';
            },
        },
    );
};

const refundOrder = () => {
    if (
        !canRefund.value ||
        props.order.payment_status !== 'paid' ||
        refunding.value
    ) {
        return;
    }
    if (
        !confirm(
            `Refund order ${props.order.order_number}? This marks payment as refunded.`,
        )
    ) {
        return;
    }

    refunding.value = true;
    router.post(
        route('admin.orders.refund', props.order.id),
        { reason: 'Admin refund from order detail' },
        {
            preserveScroll: true,
            onFinish: () => {
                refunding.value = false;
            },
        },
    );
};
</script>

<template>
    <AdminLayout title="Order Detail" breadcrumb="Orders">
        <Head :title="`Order ${order.order_number}`" />

        <section class="max-w-[1200px] mx-auto pb-10">
            <!-- Header -->
            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 md:mb-10"
            >
                <div>
                    <Link
                        :href="route('admin.orders.index')"
                        class="inline-flex items-center gap-1 text-secondary font-bold text-sm hover:underline mb-4"
                    >
                        <IconArrowLeft class="h-4 w-4" />
                        Back to Orders
                    </Link>
                    <h2 class="font-serif text-headline-md md:text-headline-lg text-primary">
                        Order {{ order.order_number }}
                    </h2>
                    <p class="text-on-surface-variant mt-1 text-body-sm">
                        Placed on {{ formatDateTime(order.created_at) }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        disabled
                        title="Coming soon"
                        class="px-5 py-2 border border-outline-variant rounded-full text-sm font-bold flex items-center gap-2 opacity-50 cursor-not-allowed"
                    >
                        <IconPrinter class="h-4 w-4" />
                        Print
                    </button>
                    <Link
                        v-if="isCompleted"
                        :href="route('admin.orders.invoice', order.id)"
                        class="px-5 py-2 border border-outline-variant rounded-full text-sm font-bold hover:bg-surface-variant transition-colors flex items-center gap-2"
                    >
                        <IconFileDescription class="h-4 w-4" />
                        Invoice
                    </Link>
                    <button
                        v-if="canUpdateStatus"
                        type="button"
                        class="px-5 py-2 rounded-full font-bold flex items-center gap-3 border transition-all"
                        :class="statusMeta.wrap"
                        @click="openStatusModal()"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-current"
                            :class="{ 'animate-pulse': statusMeta.pulse }"
                        />
                        {{ statusMeta.label }}
                        <IconChevronRight class="h-4 w-4 rotate-90" />
                    </button>
                    <span
                        v-else
                        class="px-5 py-2 rounded-full font-bold flex items-center gap-3 border text-sm"
                        :class="statusMeta.wrap"
                    >
                        <span class="w-2.5 h-2.5 rounded-full bg-current" />
                        {{ statusMeta.label }}
                    </span>
                </div>
            </div>

            <!-- Progress -->
            <div
                class="bg-surface-container-lowest rounded-xl shadow-sm p-6 sm:p-8 mb-8"
                :class="{ 'opacity-60': isCancelled }"
            >
                <p
                    v-if="isCancelled"
                    class="text-center text-sm font-bold text-error mb-6"
                >
                    This order was cancelled
                </p>
                <div class="flex items-center justify-between relative">
                    <div
                        class="absolute top-5 left-0 w-full h-0.5 bg-surface-container-high z-0"
                    />
                    <div
                        class="absolute top-5 left-0 h-0.5 bg-secondary-container z-0 transition-all duration-700"
                        :style="{ width: `${progressPercent}%` }"
                    />
                    <div
                        v-for="step in progressSteps"
                        :key="step.id"
                        class="relative z-10 flex flex-col items-center gap-2 sm:gap-3 flex-1"
                        :class="{ 'opacity-40': !step.done && !step.current }"
                    >
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center"
                            :class="{
                                'bg-secondary text-white':
                                    step.done && !step.current,
                                'bg-secondary-container text-on-secondary-container ring-4 ring-secondary-container/20 animate-pulse':
                                    step.current,
                                'bg-surface-container-high text-on-surface':
                                    !step.done && !step.current,
                            }"
                        >
                            <component
                                :is="
                                    step.done && !step.current
                                        ? IconCheck
                                        : step.icon
                                "
                                class="h-4 w-4 sm:h-5 sm:w-5"
                            />
                        </div>
                        <span
                            class="text-[10px] sm:text-xs font-bold text-center px-1"
                            :class="
                                step.current
                                    ? 'text-secondary'
                                    : 'text-on-surface'
                            "
                        >
                            {{ step.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Three-column content -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left: items + pricing + refund -->
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden"
                    >
                        <div class="p-6 border-b border-surface-container">
                            <h3 class="font-serif text-headline-sm text-primary">
                                Order Items
                            </h3>
                        </div>
                        <div class="divide-y divide-surface-container">
                            <div
                                v-for="item in order.items ?? []"
                                :key="item.id"
                                class="p-4 sm:p-6 flex items-center gap-4 hover:bg-surface-container-low transition-colors"
                            >
                                <img
                                    :src="item.thumbnail || placeholderImage"
                                    :alt="item.product_name"
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg object-cover shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="font-semibold text-title-lg text-primary truncate"
                                    >
                                        {{ item.product_name }}
                                    </h4>
                                    <p
                                        v-if="formatOptions(item.selected_attributes)"
                                        class="text-xs text-on-surface-variant mt-0.5"
                                    >
                                        {{ formatOptions(item.selected_attributes) }}
                                    </p>
                                    <p
                                        v-else-if="item.product_sku"
                                        class="text-xs text-on-surface-variant mt-0.5"
                                    >
                                        SKU: {{ item.product_sku }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-bold text-primary text-sm">
                                        {{ item.quantity }} ×
                                        {{ formatMoney(item.unit_price) }}
                                    </p>
                                    <p class="text-secondary font-bold">
                                        {{ formatMoney(item.line_total) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="!(order.items?.length)"
                                class="p-8 text-center text-on-surface-variant text-sm"
                            >
                                No items on this order.
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-surface-container-lowest rounded-xl shadow-sm p-6 space-y-4"
                    >
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Subtotal</span>
                            <span>{{ formatMoney(order.subtotal) }}</span>
                        </div>
                        <div
                            v-if="Number(order.discount_amount) > 0"
                            class="flex justify-between items-center"
                        >
                            <span class="flex items-center gap-2 flex-wrap">
                                <IconReceipt class="h-4 w-4 text-secondary" />
                                Promo Code
                                <span
                                    v-if="order.promo_code"
                                    class="bg-secondary/10 text-secondary px-2 py-0.5 rounded font-bold text-xs"
                                >
                                    {{ order.promo_code }}
                                </span>
                            </span>
                            <span class="text-secondary font-bold">
                                -{{ formatMoney(order.discount_amount) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Tax</span>
                            <span>{{ formatMoney(order.tax_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>Delivery Fee</span>
                            <span>{{ formatMoney(order.delivery_fee) }}</span>
                        </div>
                        <div
                            class="pt-4 border-t border-surface-container flex justify-between items-center"
                        >
                            <span class="font-serif text-headline-sm text-primary">
                                Total Paid
                            </span>
                            <span
                                class="font-serif text-headline-sm text-secondary"
                            >
                                {{ formatMoney(order.total) }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="canRefund && order.payment_status === 'paid'"
                        class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-xl p-6 text-center"
                    >
                        <IconReceipt class="h-6 w-6 text-outline mx-auto mb-2" />
                        <h4 class="font-bold text-on-surface">Issue a Refund?</h4>
                        <p class="text-xs text-on-surface-variant mb-4">
                            {{ refundHelpText }}
                        </p>
                        <button
                            type="button"
                            class="px-6 py-2 bg-on-surface text-white rounded-full text-xs font-bold hover:bg-primary transition-colors disabled:opacity-50"
                            :disabled="refunding"
                            @click="refundOrder"
                        >
                            {{ refunding ? 'Refunding…' : 'Adjust Order' }}
                        </button>
                    </div>
                    <div
                        v-else-if="order.payment_status === 'refunded'"
                        class="bg-surface-container-low border border-outline-variant rounded-xl p-4 text-center text-sm text-on-surface-variant"
                    >
                        This order has been refunded.
                    </div>
                </div>

                <!-- Middle: customer / delivery / payment -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-title-lg text-primary mb-6">
                            Customer Info
                        </h3>
                        <div class="flex items-center gap-4 mb-6">
                            <img
                                v-if="customerAvatar"
                                :src="customerAvatar"
                                :alt="customerName"
                                class="w-12 h-12 rounded-full object-cover"
                            />
                            <div
                                v-else
                                class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm"
                            >
                                {{ customerInitials }}
                            </div>
                            <div>
                                <p class="font-bold text-primary">
                                    {{ customerName }}
                                </p>
                                <p class="text-xs text-on-surface-variant">
                                    {{
                                        order.customer
                                            ? 'Registered customer'
                                            : 'Guest checkout'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <IconMail class="h-5 w-5 text-outline shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-on-surface">
                                        Email
                                    </p>
                                    <p
                                        class="text-sm text-on-surface-variant break-all"
                                    >
                                        {{ customerEmail }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <IconPhone class="h-5 w-5 text-outline shrink-0" />
                                <div>
                                    <p class="text-xs font-bold text-on-surface">
                                        Phone
                                    </p>
                                    <p class="text-sm text-on-surface-variant">
                                        {{ customerPhone }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="pt-4 border-t border-surface-container grid grid-cols-2 gap-4"
                            >
                                <div>
                                    <p
                                        class="text-[10px] uppercase font-bold text-outline tracking-wider"
                                    >
                                        Total Orders
                                    </p>
                                    <p class="text-lg font-bold text-primary">
                                        {{ order.customer_orders_count ?? 0 }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase font-bold text-outline tracking-wider"
                                    >
                                        LTV
                                    </p>
                                    <p class="text-lg font-bold text-secondary">
                                        {{ formatMoney(order.customer_ltv) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4 gap-2">
                            <h3 class="font-semibold text-title-lg text-primary">
                                Delivery
                            </h3>
                            <span
                                v-if="isExpress"
                                class="text-[10px] bg-primary text-white px-2 py-0.5 rounded font-bold uppercase tracking-wider"
                            >
                                Priority
                            </span>
                        </div>
                        <div class="space-y-4">
                            <div
                                class="p-3 bg-surface-container-low rounded-lg border border-surface-container"
                            >
                                <p class="text-xs font-bold text-on-surface mb-1">
                                    Shipping Address
                                </p>
                                <p
                                    class="text-sm text-on-surface-variant leading-relaxed"
                                >
                                    {{ order.address_line1
                                    }}<template v-if="order.address_line2"
                                        ><br />{{ order.address_line2 }}</template
                                    ><br />
                                    {{ order.city
                                    }}<template v-if="order.state"
                                        >, {{ order.state }}</template
                                    >
                                    {{ order.postal_code }}<br />
                                    {{ order.country }}
                                </p>
                            </div>
                            <div class="flex justify-between text-sm gap-2">
                                <span class="text-on-surface-variant">Method:</span>
                                <span class="font-bold text-right">
                                    {{ deliveryMethodLabel }}
                                </span>
                            </div>
                            <div v-if="order.delivery_notes">
                                <p class="text-xs font-bold text-on-surface mb-1">
                                    Notes
                                </p>
                                <p class="text-xs italic text-on-surface-variant">
                                    "{{ order.delivery_notes }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-title-lg text-primary mb-4">
                            Payment
                        </h3>
                        <div class="flex items-center justify-between mb-4 gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-10 h-6 bg-primary rounded flex items-center justify-center text-[8px] text-white font-bold shrink-0 uppercase"
                                >
                                    {{
                                        order.payment_method === 'cod'
                                            ? 'COD'
                                            : order.payment_method === 'stripe'
                                              ? 'CARD'
                                              : (order.payment_method || 'pay').slice(0, 4)
                                    }}
                                </div>
                                <p class="text-sm font-bold truncate">
                                    {{ paymentMethodLabel }}
                                </p>
                            </div>
                            <IconCircleCheck
                                v-if="order.payment_status === 'paid'"
                                class="h-5 w-5 text-secondary shrink-0"
                            />
                        </div>
                        <div class="flex justify-between text-sm mb-2 gap-2">
                            <span class="text-on-surface-variant">Status:</span>
                            <span
                                class="font-bold text-right"
                                :class="{
                                    'text-secondary':
                                        order.payment_status === 'paid',
                                    'text-error':
                                        order.payment_status === 'failed' ||
                                        order.payment_status === 'refunded',
                                }"
                            >
                                {{ paymentStatusLabel }}
                            </span>
                        </div>
                        <p
                            v-if="order.transaction_id"
                            class="text-[10px] text-on-surface-variant break-all"
                        >
                            Stripe ID: {{ order.transaction_id }}
                        </p>
                        <p
                            v-if="order.paid_at"
                            class="mt-1 text-[10px] text-on-surface-variant"
                        >
                            Paid at:
                            {{
                                new Date(order.paid_at).toLocaleString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                })
                            }}
                        </p>
                    </div>
                </div>

                <!-- Right: activity / notes / actions -->
                <div class="lg:col-span-3 space-y-6">
                    <div
                        class="bg-surface-container-lowest rounded-xl shadow-sm p-6 flex flex-col h-[420px] sm:h-[500px]"
                    >
                        <h3 class="font-semibold text-title-lg text-primary mb-6">
                            Activity Log
                        </h3>
                        <div
                            class="flex-1 overflow-y-auto admin-scrollbar space-y-6 pr-2"
                        >
                            <div
                                v-for="event in activityItems"
                                :key="event.id"
                                class="flex gap-4 relative"
                            >
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-2.5 h-2.5 rounded-full z-10"
                                        :class="{
                                            'bg-secondary ring-4 ring-secondary/20':
                                                event.active,
                                            'bg-surface-container':
                                                !event.active && !event.pending,
                                            'bg-surface-container-low border border-surface-container':
                                                event.pending,
                                        }"
                                    />
                                    <div
                                        class="flex-1 w-0.5 bg-surface-container mt-1 min-h-[1.5rem]"
                                    />
                                </div>
                                <div class="pb-2">
                                    <p
                                        class="text-xs font-bold"
                                        :class="
                                            event.pending
                                                ? 'text-outline'
                                                : 'text-on-surface'
                                        "
                                    >
                                        {{ event.title }}
                                    </p>
                                    <p
                                        v-if="event.at"
                                        class="text-[10px] text-on-surface-variant mb-1"
                                    >
                                        {{ formatTimelineTime(event.at) }}
                                    </p>
                                    <p
                                        v-else-if="event.pending"
                                        class="text-[10px] text-outline mb-1"
                                    >
                                        {{ event.body }}
                                    </p>
                                    <p
                                        v-if="!event.pending && event.body"
                                        class="text-xs text-on-surface-variant"
                                    >
                                        {{ event.body }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-title-lg text-primary">
                                Internal Notes
                            </h3>
                            <button
                                v-if="canUpdateStatus"
                                type="button"
                                class="text-secondary text-xs font-bold hover:underline disabled:opacity-50"
                                :disabled="!internalNote.trim() || savingNote"
                                @click="saveInternalNote"
                            >
                                {{ savingNote ? 'Saving…' : 'Save' }}
                            </button>
                        </div>
                        <textarea
                            v-model="internalNote"
                            :disabled="!canUpdateStatus"
                            class="w-full h-24 p-3 bg-surface-container-low border-none rounded-lg text-xs focus:ring-1 focus:ring-secondary/50 resize-none mb-3 disabled:opacity-60"
                            placeholder="Add a private note for staff..."
                        />
                        <p class="text-[10px] text-on-surface-variant italic">
                            Only visible to administrators and kitchen staff.
                        </p>
                    </div>

                    <div
                        v-if="canUpdateStatus"
                        class="bg-surface-container-lowest rounded-xl shadow-sm p-6"
                    >
                        <h3 class="font-semibold text-title-lg text-primary mb-4">
                            Action Center
                        </h3>
                        <div class="space-y-2">
                            <button
                                type="button"
                                class="w-full text-left p-3 rounded-lg border border-surface-container hover:bg-secondary-container hover:text-on-secondary-container hover:border-transparent transition-all flex items-center justify-between group"
                                @click="openStatusModal('processing')"
                            >
                                <span class="text-xs font-bold">
                                    Ready for Pickup
                                </span>
                                <IconChevronRight
                                    class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                />
                            </button>
                            <button
                                type="button"
                                class="w-full text-left p-3 rounded-lg border border-surface-container hover:bg-secondary-container hover:text-on-secondary-container hover:border-transparent transition-all flex items-center justify-between group"
                                @click="openStatusModal('shipped')"
                            >
                                <span class="text-xs font-bold">
                                    Assign Courier
                                </span>
                                <IconChevronRight
                                    class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                />
                            </button>
                            <button
                                type="button"
                                class="w-full text-left p-3 rounded-lg border border-error/20 text-error hover:bg-error hover:text-white hover:border-transparent transition-all flex items-center justify-between group"
                                @click="openStatusModal('cancelled')"
                            >
                                <span class="text-xs font-bold">Cancel Order</span>
                                <IconCircleX
                                    class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <UpdateOrderStatusModal
            :show="showStatusModal"
            :order="order"
            :initial-status="modalInitialStatus"
            @close="closeStatusModal"
        />
    </AdminLayout>
</template>
