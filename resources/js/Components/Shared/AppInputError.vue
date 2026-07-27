<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <p v-show="message" class="mt-1 text-sm text-error">
        {{ message }}
    </p>
</template>
