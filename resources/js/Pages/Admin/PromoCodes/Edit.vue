<script setup>
import PromoCodeForm from '@/Components/Admin/PromoCodeForm.vue';

defineProps({
    promoCode: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <PromoCodeForm mode="edit" :promo-code="promoCode" />
</template>
