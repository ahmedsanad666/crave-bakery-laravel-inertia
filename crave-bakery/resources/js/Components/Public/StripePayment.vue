<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import { IconLock } from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';

const props = defineProps({
    orderTotal: {
        type: Number,
        required: true,
    },
    stripeKey: {
        type: String,
        required: true,
    },
});

const stripe = ref(null);
const elements = ref(null);
const paymentElement = ref(null);
const processing = ref(false);
const error = ref(null);
const ready = ref(false);

onMounted(async () => {
    await initStripe();
});

onUnmounted(() => {
    paymentElement.value?.destroy?.();
});

async function initStripe() {
    const key = props.stripeKey || import.meta.env.VITE_STRIPE_KEY;

    if (!key) {
        error.value = 'Payment is not configured. Please contact support.';
        return;
    }

    stripe.value = await loadStripe(key);

    if (!stripe.value) {
        error.value = 'Failed to load payment processor. Please refresh the page.';
        return;
    }

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ??
        decodeURIComponent(
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')
                .slice(1)
                .join('=') ?? '',
        );

    const response = await fetch(route('payment.intent'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-XSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({}),
    });

    const data = await response.json();

    if (data.error || !data.clientSecret) {
        error.value = data.error ?? 'Could not initialise payment. Please try again.';
        return;
    }

    elements.value = stripe.value.elements({
        clientSecret: data.clientSecret,
        appearance: {
            theme: 'stripe',
            variables: {
                colorPrimary: '#E8572A',
                colorBackground: '#FFFFFF',
                colorText: '#1A1A1A',
                colorDanger: '#C62828',
                fontFamily: 'Inter, sans-serif',
                borderRadius: '10px',
                fontSizeBase: '14px',
            },
        },
    });

    paymentElement.value = elements.value.create('payment');
    paymentElement.value.mount('#stripe-payment-element');
    paymentElement.value.on('ready', () => {
        ready.value = true;
    });
}

async function pay() {
    if (!stripe.value || !elements.value || processing.value) {
        return;
    }

    processing.value = true;
    error.value = null;

    const { error: stripeError, paymentIntent } = await stripe.value.confirmPayment({
        elements: elements.value,
        redirect: 'if_required',
    });

    if (stripeError) {
        error.value = stripeError.message ?? 'Payment failed. Please try again.';
        processing.value = false;
        return;
    }

    if (paymentIntent?.status === 'succeeded') {
        router.post(route('payment.confirm'), {
            payment_intent_id: paymentIntent.id,
        });
        return;
    }

    error.value = 'Payment was not completed. Please try again.';
    processing.value = false;
}
</script>

<template>
    <div class="space-y-5">
        <div v-if="!ready && !error" class="animate-pulse space-y-3">
            <div class="h-12 rounded-[10px] bg-gray-100" />
            <div class="h-12 rounded-[10px] bg-gray-100" />
            <div class="h-12 w-1/2 rounded-[10px] bg-gray-100" />
        </div>

        <div id="stripe-payment-element" :class="{ hidden: !ready }" />

        <div
            v-if="error"
            class="flex items-center gap-2 rounded-[10px] border border-error/20 bg-error/10 p-3"
        >
            <span class="font-sans text-sm text-error">{{ error }}</span>
        </div>

        <AppButton
            v-if="ready"
            type="button"
            variant="primary"
            class="flex h-12 w-full items-center justify-center gap-2"
            :loading="processing"
            :disabled="processing"
            @click="pay"
        >
            <IconLock :size="18" stroke-width="1.5" />
            <span>
                {{
                    processing
                        ? 'Processing payment...'
                        : `Pay $${Number(orderTotal).toFixed(2)}`
                }}
            </span>
        </AppButton>

        <p class="text-center font-sans text-xs text-on-surface-variant">
            Secured by Stripe — we never store your card details
        </p>
    </div>
</template>
