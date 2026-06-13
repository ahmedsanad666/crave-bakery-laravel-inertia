<script setup>
import { ref } from 'vue';
import { IconX } from '@tabler/icons-vue';

const visible = ref(true);

const dismiss = () => {
    visible.value = false;
};
</script>

<template>
    <div
        v-if="visible"
        class="relative bg-accent px-4 py-2.5 text-center text-sm font-medium text-white"
        role="status"
    >
        Free delivery on orders over £30 — use code WELCOME10 at checkout
        <button
            type="button"
            class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full p-1 transition-colors hover:bg-white/20"
            aria-label="Dismiss promotion"
            @click="dismiss"
        >
            <IconX :size="16" stroke-width="1.5" />
        </button>
    </div>
</template>
