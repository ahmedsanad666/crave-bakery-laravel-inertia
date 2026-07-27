<script setup>
import { computed } from 'vue';
import {
    IconCircleCheck,
    IconHourglass,
    IconPackage,
    IconTruck,
} from '@tabler/icons-vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    createdAt: {
        type: String,
        default: null,
    },
    estimatedDeliveryAt: {
        type: String,
        default: null,
    },
    deliveredAt: {
        type: String,
        default: null,
    },
});

const formatDateTime = (iso) => {
    if (!iso) {
        return null;
    }
    return new Date(iso).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const formatDate = (iso) => {
    if (!iso) {
        return null;
    }
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const currentStep = computed(() => {
    switch (props.status) {
        case 'processing':
            return 2;
        case 'shipped':
            return 3;
        case 'delivered':
            return 4;
        case 'cancelled':
            return 1;
        case 'pending':
        default:
            return 1;
    }
});

const isCancelled = computed(() => props.status === 'cancelled');

const steps = computed(() => {
    const placed = formatDateTime(props.createdAt);
    const expected = formatDate(props.estimatedDeliveryAt);
    const delivered = formatDateTime(props.deliveredAt);

    return [
        {
            key: 'placed',
            label: 'Order Placed',
            icon: IconCircleCheck,
            subtitle: placed ?? '',
        },
        {
            key: 'processing',
            label: 'Processing',
            icon: IconHourglass,
            subtitle: currentStep.value >= 2 && !isCancelled.value
                ? (currentStep.value === 2 ? 'In Progress' : placed ? 'Done' : '')
                : '',
        },
        {
            key: 'shipped',
            label: 'Out for Delivery',
            icon: IconTruck,
            subtitle: currentStep.value === 3
                ? (expected ? `Expected ${expected}` : 'On the way')
                : currentStep.value > 3
                    ? (expected ? `Expected ${expected}` : '')
                    : expected
                        ? `Expected ${expected}`
                        : '',
        },
        {
            key: 'delivered',
            label: 'Delivered',
            icon: IconPackage,
            subtitle: currentStep.value >= 4 ? (delivered ?? 'Complete') : '',
        },
    ];
});

const stepState = (index) => {
    const stepNumber = index + 1;

    if (isCancelled.value) {
        return stepNumber === 1 ? 'done' : 'upcoming';
    }

    if (stepNumber < currentStep.value) {
        return 'done';
    }
    if (stepNumber === currentStep.value) {
        return 'current';
    }
    return 'upcoming';
};

const lineActive = (afterIndex) => {
    if (isCancelled.value) {
        return false;
    }
    return afterIndex + 1 < currentStep.value;
};
</script>

<template>
    <section
        class="rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm md:p-xl"
        :class="{ 'opacity-80': isCancelled }"
    >
        <div class="flex items-start justify-between gap-sm md:items-center">
            <div
                v-for="(step, index) in steps"
                :key="step.key"
                class="contents"
            >
                <div class="flex w-1/4 flex-col items-center text-center">
                    <div
                        class="mb-sm flex h-10 w-10 items-center justify-center rounded-full md:h-12 md:w-12"
                        :class="{
                            'bg-secondary text-white shadow-md ring-4 ring-secondary/20':
                                stepState(index) === 'current',
                            'bg-secondary text-white shadow-md':
                                stepState(index) === 'done',
                            'bg-surface-variant text-on-surface-variant':
                                stepState(index) === 'upcoming',
                        }"
                    >
                        <component
                            :is="step.icon"
                            :size="22"
                            stroke-width="1.5"
                        />
                    </div>
                    <span
                        class="font-sans text-[11px] font-semibold leading-tight md:text-title-lg md:font-semibold"
                        :class="
                            stepState(index) === 'upcoming'
                                ? 'text-on-surface-variant'
                                : 'text-primary'
                        "
                    >
                        {{ step.label }}
                    </span>
                    <span
                        v-if="step.subtitle"
                        class="mt-0.5 hidden font-sans text-body-sm text-on-surface-variant sm:block"
                    >
                        {{ step.subtitle }}
                    </span>
                </div>

                <div
                    v-if="index < steps.length - 1"
                    class="mt-5 h-0.5 min-w-[8px] flex-1 md:mt-6"
                    :class="
                        lineActive(index)
                            ? 'bg-secondary'
                            : 'bg-outline-variant/60'
                    "
                    aria-hidden="true"
                />
            </div>
        </div>

        <p
            v-if="isCancelled"
            class="mt-md text-center font-sans text-body-sm font-semibold text-error"
        >
            This order was cancelled.
        </p>
    </section>
</template>
