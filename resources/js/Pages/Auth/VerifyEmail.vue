<script setup>
import { computed } from 'vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout
        title="Verify your email"
        subtitle="Thanks for signing up! Please verify your email address to get started."
    >
        <Head title="Email Verification" />

        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-input border border-success/30 bg-success/10 px-4 py-3 text-sm text-success"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Resend verification email
            </AppButton>

            <p class="text-center text-body-sm text-text-muted">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="font-medium text-accent hover:underline"
                >
                    Log out
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
