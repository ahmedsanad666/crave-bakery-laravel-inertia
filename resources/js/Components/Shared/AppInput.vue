<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    default: '',
});

defineProps({
    type: {
        type: String,
        default: 'text',
    },
    hasError: {
        type: Boolean,
        default: false,
    },
});

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        v-model="model"
        :type="type"
        class="input-field"
        :class="{ 'input-field-error': hasError }"
    />
</template>
