<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { IconCheck, IconCopy, IconShoppingBag } from '@tabler/icons-vue';

const props = defineProps({
    brand: {
        type: String,
        default: 'Crave Bakery',
    },
    promo: {
        type: Object,
        required: true,
    },
    /** Match section background so ticket notches punch correctly */
    notchClass: {
        type: String,
        default: 'bg-surface',
    },
    showApply: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['apply']);

const copied = ref(false);
const nowMs = ref(Date.now());

let copiedTimer;
let countdownTimer;

const headline = computed(() => props.promo.title ?? '');
const code = computed(() => props.promo.code ?? '');

const minimumOrderLabel = computed(() => {
    if (props.promo.min_order_amount == null) {
        return null;
    }

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.promo.min_order_amount);
});

const countdownLabel = computed(() => {
    if (!props.promo.expires_at) {
        return null;
    }

    const expiresAt = new Date(props.promo.expires_at).getTime();
    if (Number.isNaN(expiresAt)) {
        return null;
    }

    const remainingMs = expiresAt - nowMs.value;
    if (remainingMs <= 0) {
        return null;
    }

    const minuteMs = 60 * 1000;
    const hourMs = 60 * minuteMs;
    const dayMs = 24 * hourMs;

    if (remainingMs >= dayMs) {
        const days = Math.floor(remainingMs / dayMs);
        return `${days}d left`;
    }

    if (remainingMs >= hourMs) {
        const hours = Math.floor(remainingMs / hourMs);
        return `${hours}h left`;
    }

    if (remainingMs >= minuteMs) {
        const minutes = Math.floor(remainingMs / minuteMs);
        return `${minutes}m left`;
    }

    return 'Ending';
});

const copy = async () => {
    if (!code.value) {
        return;
    }

    const text = code.value;
    let ok = false;

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            ok = true;
        }
    } catch {
        ok = false;
    }

    if (!ok) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '0';
        document.body.appendChild(textarea);
        textarea.select();
        textarea.setSelectionRange(0, text.length);
        try {
            ok = document.execCommand('copy');
        } catch {
            ok = false;
        }
        document.body.removeChild(textarea);
    }

    if (!ok) {
        return;
    }

    copied.value = true;
    clearTimeout(copiedTimer);
    copiedTimer = setTimeout(() => {
        copied.value = false;
    }, 2000);
};

/** Distinguish tap from horizontal scroll drag on the parent track. */
const pointerOrigin = ref(null);

const onCopyPointerDown = (event) => {
    event.stopPropagation();
    pointerOrigin.value = {
        x: event.clientX,
        y: event.clientY,
        id: event.pointerId,
    };
};

const onCopyPointerUp = async (event) => {
    event.stopPropagation();
    const origin = pointerOrigin.value;
    pointerOrigin.value = null;

    if (!origin || origin.id !== event.pointerId) {
        return;
    }

    const dx = Math.abs(event.clientX - origin.x);
    const dy = Math.abs(event.clientY - origin.y);
    if (dx > 10 || dy > 10) {
        return;
    }

    await copy();
};

const onCopyPointerCancel = () => {
    pointerOrigin.value = null;
};

onMounted(() => {
    countdownTimer = setInterval(() => {
        nowMs.value = Date.now();
    }, 30_000);
});

onUnmounted(() => {
    clearTimeout(copiedTimer);
    clearInterval(countdownTimer);
});
</script>

<template>
    <article
        class="group relative h-[148px] w-[min(100%,18.5rem)] shrink-0 snap-start sm:h-[152px] sm:w-[20.5rem]"
    >
        <div
            class="relative flex h-full overflow-hidden rounded-xl bg-surface-container-low shadow-card transition-shadow duration-300 hover:shadow-interactive"
        >
            <!-- Left: offer -->
            <div
                class="flex min-w-0 flex-1 flex-col justify-between bg-card p-3.5 sm:p-4"
            >
                <div class="min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <span
                            class="font-sans text-[10px] font-bold uppercase tracking-[0.06em] text-accent"
                        >
                            {{ brand }}
                        </span>
                        <span
                            v-if="countdownLabel"
                            class="shrink-0 rounded-badge bg-accent/10 px-2 py-0.5 font-sans text-[10px] font-bold text-accent"
                        >
                            {{ countdownLabel }}
                        </span>
                    </div>
                    <h3
                        class="mt-1 line-clamp-2 font-serif text-[17px] font-bold leading-snug tracking-[-0.01em] text-primary sm:text-[18px]"
                    >
                        {{ headline }}
                    </h3>
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span
                        v-if="minimumOrderLabel"
                        class="inline-flex items-center gap-1 rounded-badge bg-surface-container-low px-2 py-1 font-sans text-[10px] font-semibold text-on-surface-variant"
                    >
                        <IconShoppingBag
                            class="size-3 shrink-0"
                            stroke="1.5"
                            aria-hidden="true"
                        />
                        Min. order {{ minimumOrderLabel }}
                    </span>
                </div>
            </div>

            <!-- Right: code -->
            <div
                class="relative z-10 flex w-[6.75rem] shrink-0 flex-col items-center justify-center gap-2 border-l border-dashed border-outline-variant bg-surface-container-low px-2.5 py-3 sm:w-[7.25rem]"
            >
                <button
                    type="button"
                    class="flex w-full touch-manipulation items-center justify-center gap-1.5 rounded-lg border border-dashed border-outline-variant bg-card px-2 py-2.5 font-sans text-[12px] font-bold tracking-[0.04em] text-accent shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/30"
                    :class="
                        copied
                            ? 'flex-row'
                            : 'flex-col gap-1 border-accent/40'
                    "
                    :aria-label="`Copy promo code ${code}`"
                    @pointerdown="onCopyPointerDown"
                    @pointerup="onCopyPointerUp"
                    @pointercancel="onCopyPointerCancel"
                    @click.stop.prevent
                >
                    <IconCheck
                        v-if="copied"
                        class="size-3.5 shrink-0"
                        stroke="1.5"
                        aria-hidden="true"
                    />
                    <IconCopy
                        v-else
                        class="size-3.5 shrink-0 opacity-70"
                        stroke="1.5"
                        aria-hidden="true"
                    />
                    <span class="max-w-full truncate">{{
                        copied ? 'Copied!' : code
                    }}</span>
                </button>
                <button
                    v-if="showApply"
                    type="button"
                    class="font-sans text-[11px] font-bold text-primary underline-offset-2 hover:underline"
                    @click.stop="emit('apply', code)"
                >
                    Use code
                </button>
            </div>
        </div>

        <!-- Ticket notches -->
        <span
            class="pointer-events-none absolute -top-2 right-[6.75rem] size-4 translate-x-1/2 rounded-full sm:right-[7.25rem]"
            :class="notchClass"
            aria-hidden="true"
        />
        <span
            class="pointer-events-none absolute -bottom-2 right-[6.75rem] size-4 translate-x-1/2 rounded-full sm:right-[7.25rem]"
            :class="notchClass"
            aria-hidden="true"
        />
    </article>
</template>
