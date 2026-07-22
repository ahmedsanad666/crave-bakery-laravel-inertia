<script setup>
import { onUnmounted, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

watch(
    () => props.open,
    (isOpen) => {
        document.body.style.overflow = isOpen ? 'hidden' : '';
    },
);

onUnmounted(() => {
    if (props.open) {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[60] bg-inverse-surface/40 md:hidden"
                aria-hidden="true"
                @click="emit('close')"
            />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-[400ms] ease-[cubic-bezier(0.32,0.72,0,1)]"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-[400ms] ease-[cubic-bezier(0.32,0.72,0,1)]"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="open"
                class="fixed inset-x-0 bottom-0 z-[70] max-h-[90vh] overflow-y-auto rounded-t-[24px] bg-surface-container-lowest p-lg pb-xl shadow-2xl md:hidden"
                role="dialog"
                aria-modal="true"
            >
                <div class="mx-auto mb-lg h-1 w-12 rounded-full bg-outline-variant/50" />
                <slot />
            </div>
        </Transition>
    </Teleport>
</template>
