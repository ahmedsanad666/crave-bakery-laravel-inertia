<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    IconMail,
    IconNotes,
    IconX,
} from '@tabler/icons-vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    order: {
        type: Object,
        required: true,
    },
    initialStatus: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

const STATUS_CARDS = [
    {
        id: 'pending',
        label: 'Pending',
        description: 'Awaiting payment or initial review',
        dot: 'bg-outline-variant',
    },
    {
        id: 'processing',
        label: 'Processing',
        description: 'Bakery is preparing the artisanal items',
        dot: 'bg-secondary-container',
    },
    {
        id: 'shipped',
        label: 'Shipped',
        description: 'Order has been dispatched via courier',
        dot: 'bg-secondary',
    },
    {
        id: 'delivered',
        label: 'Delivered',
        description: 'Package confirmed at destination',
        dot: 'bg-green-600',
    },
    {
        id: 'cancelled',
        label: 'Cancelled',
        description: 'Order voided by admin or customer',
        dot: 'bg-error',
    },
];

const selectedStatus = ref(props.order.status);
const notifyCustomer = ref(true);
const note = ref('');
const submitting = ref(false);

watch(
    () => props.show,
    (open) => {
        if (open) {
            selectedStatus.value = props.initialStatus || props.order.status;
            notifyCustomer.value = true;
            note.value = '';
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);

const customerFirstName = computed(() => {
    const name =
        props.order.customer?.name ||
        `${props.order.first_name ?? ''} ${props.order.last_name ?? ''}`.trim();
    return name.split(' ')[0] || 'there';
});

const emailSubject = computed(() => {
    const map = {
        pending: 'We received your order',
        processing: 'Your order is being prepared',
        shipped: 'Your delicious order is on its way!',
        delivered: 'Your order has been delivered',
        cancelled: 'Update on your order',
    };
    return map[selectedStatus.value] ?? 'Update on your order';
});

const emailBody = computed(() => {
    const map = {
        pending: `Hi ${customerFirstName.value}, we've received your order **#${props.order.order_number}** and will update you soon.`,
        processing: `Hi ${customerFirstName.value}, great news! Your order **#${props.order.order_number}** is now being prepared in our kitchen.`,
        shipped: `Hi ${customerFirstName.value}, good news! Your order **#${props.order.order_number}** has been handed over to our premium courier.`,
        delivered: `Hi ${customerFirstName.value}, your order **#${props.order.order_number}** has been delivered. Enjoy!`,
        cancelled: `Hi ${customerFirstName.value}, your order **#${props.order.order_number}** has been cancelled. Reach out if you need help.`,
    };
    return map[selectedStatus.value] ?? `Hi ${customerFirstName.value}, there's an update on order **#${props.order.order_number}**.`;
});

const currentStatusLabel = computed(() => {
    const card = STATUS_CARDS.find((c) => c.id === props.order.status);
    return card?.label ?? props.order.status;
});

const currentStatusDot = computed(() => {
    const card = STATUS_CARDS.find((c) => c.id === props.order.status);
    return card?.dot ?? 'bg-outline';
});

const close = () => {
    if (!submitting.value) {
        emit('close');
    }
};

const submit = () => {
    if (submitting.value) {
        return;
    }

    submitting.value = true;
    router.patch(
        route('admin.orders.update', props.order.id),
        {
            status: selectedStatus.value,
            note: note.value || null,
            notify_customer: notifyCustomer.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                submitting.value = false;
            },
            onSuccess: () => {
                emit('close');
            },
        },
    );
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-primary/40 backdrop-blur-md"
            role="dialog"
            aria-modal="true"
            aria-labelledby="update-order-status-title"
            @click.self="close"
        >
            <div
                class="bg-surface-container-lowest w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            >
                <!-- Header -->
                <div
                    class="px-6 sm:px-8 py-5 sm:py-6 border-b border-outline-variant/30 flex justify-between items-center bg-surface-container-lowest shrink-0"
                >
                    <h3
                        id="update-order-status-title"
                        class="font-serif text-headline-sm sm:text-headline-md text-primary"
                    >
                        Update Order Status
                    </h3>
                    <button
                        type="button"
                        class="p-2 hover:bg-surface-variant rounded-full transition-colors"
                        aria-label="Close"
                        @click="close"
                    >
                        <IconX class="h-5 w-5 text-on-surface-variant" />
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto px-6 sm:px-8 py-6 space-y-8 admin-scrollbar">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-body-sm text-on-surface-variant">
                            Current Status:
                        </span>
                        <div
                            class="px-3 py-1 bg-surface-variant rounded-full flex items-center gap-2"
                        >
                            <span
                                class="w-2 h-2 rounded-full"
                                :class="currentStatusDot"
                            />
                            <span class="text-body-sm font-bold text-on-surface">
                                {{ currentStatusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                        <button
                            v-for="card in STATUS_CARDS"
                            :key="card.id"
                            type="button"
                            class="flex flex-col items-start p-4 bg-white rounded-xl text-left transition-all"
                            :class="
                                selectedStatus === card.id
                                    ? 'ring-2 ring-secondary ring-offset-2 shadow-[0_2px_12px_rgba(0,0,0,0.07)]'
                                    : 'border border-outline-variant hover:border-secondary-container'
                            "
                            @click="selectedStatus = card.id"
                        >
                            <span
                                class="w-3 h-3 rounded-full mb-3"
                                :class="card.dot"
                            />
                            <span
                                class="font-semibold text-title-lg mb-1 block"
                                :class="
                                    selectedStatus === card.id
                                        ? 'text-secondary'
                                        : 'text-primary'
                                "
                            >
                                {{ card.label }}
                            </span>
                            <span
                                class="text-[11px] leading-tight text-on-surface-variant"
                            >
                                {{ card.description }}
                            </span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-2">
                        <!-- Notify -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <label
                                    class="font-semibold text-title-lg flex items-center gap-2"
                                >
                                    <IconMail class="h-5 w-5 text-secondary" />
                                    Notify customer
                                </label>
                                <button
                                    type="button"
                                    class="w-12 h-6 rounded-full relative transition-colors shrink-0"
                                    :class="
                                        notifyCustomer
                                            ? 'bg-secondary-container'
                                            : 'bg-outline-variant'
                                    "
                                    role="switch"
                                    :aria-checked="notifyCustomer"
                                    @click="notifyCustomer = !notifyCustomer"
                                >
                                    <span
                                        class="absolute top-1 bg-white w-4 h-4 rounded-full shadow-sm transition-all"
                                        :class="
                                            notifyCustomer ? 'right-1' : 'left-1'
                                        "
                                    />
                                </button>
                            </div>

                            <div
                                class="bg-surface-container p-4 rounded-xl space-y-3 relative overflow-hidden transition-opacity"
                                :class="
                                    notifyCustomer
                                        ? 'opacity-100'
                                        : 'opacity-40 pointer-events-none'
                                "
                            >
                                <div
                                    class="absolute top-0 right-0 text-[10px] bg-secondary/10 text-secondary font-bold px-2 py-1 rounded-bl-lg"
                                >
                                    PREVIEW
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-[10px] font-bold text-white shrink-0"
                                    >
                                        CB
                                    </div>
                                    <div>
                                        <p class="text-[12px] font-bold text-primary">
                                            Crave Bakery
                                        </p>
                                        <p class="text-[10px] text-outline">
                                            Subject: {{ emailSubject }}
                                        </p>
                                    </div>
                                </div>
                                <p
                                    class="text-[11px] text-on-surface-variant leading-relaxed"
                                >
                                    {{ emailBody }}
                                </p>
                                <div
                                    class="pt-2 border-t border-outline-variant/30 flex justify-between items-center"
                                >
                                    <span
                                        class="text-[9px] text-outline uppercase tracking-wider font-bold"
                                    >
                                        Preview
                                    </span>
                                    <span
                                        class="text-[10px] text-secondary font-bold cursor-default opacity-60"
                                        title="Coming soon"
                                    >
                                        Edit content
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Internal note -->
                        <div class="space-y-4">
                            <label
                                class="font-semibold text-title-lg flex items-center gap-2"
                                for="status-internal-note"
                            >
                                <IconNotes class="h-5 w-5 text-on-surface-variant" />
                                Internal admin note
                            </label>
                            <textarea
                                id="status-internal-note"
                                v-model="note"
                                rows="6"
                                class="w-full p-4 bg-white border border-outline-variant rounded-xl text-body-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all resize-none placeholder:text-outline-variant"
                                placeholder="Add details about the shipping carrier, tracking number, or special handling instructions..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="px-6 sm:px-8 py-5 sm:py-6 bg-surface-container-low flex justify-end items-center gap-3 sm:gap-4 shrink-0"
                >
                    <button
                        type="button"
                        class="px-6 sm:px-8 h-12 text-on-surface font-bold hover:bg-surface-variant rounded-full transition-colors text-body-sm"
                        :disabled="submitting"
                        @click="close"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-8 sm:px-10 h-12 bg-secondary text-on-secondary font-bold rounded-full shadow-lg hover:shadow-secondary/20 active:scale-95 transition-all text-body-sm disabled:opacity-50"
                        :disabled="submitting"
                        @click="submit"
                    >
                        {{ submitting ? 'Updating…' : 'Update Status' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
