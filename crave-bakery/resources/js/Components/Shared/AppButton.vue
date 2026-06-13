<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const variantClasses = {
    primary: 'btn-primary',
    secondary: 'btn-secondary',
    ghost: 'btn-ghost',
    danger: 'btn-danger',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            variantClasses[variant] ?? variantClasses.primary,
            { 'opacity-50 cursor-not-allowed': disabled || loading },
        ]"
    >
        <slot />
    </button>
</template>
