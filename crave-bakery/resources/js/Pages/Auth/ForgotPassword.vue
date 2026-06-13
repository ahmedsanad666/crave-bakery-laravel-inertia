<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout
        title="Forgot your password?"
        subtitle="Enter your email and we'll send you a reset link."
    >
        <Head title="Forgot Password" />

        <div
            v-if="status"
            class="mb-6 rounded-input border border-success/30 bg-success/10 px-4 py-3 text-sm text-success"
        >
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <AppInputLabel for="email" value="Email" />
                <AppInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <AppInputError :message="form.errors.email" />
            </div>

            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Email reset link
            </AppButton>

            <p class="text-center text-body-sm text-text-muted">
                Remember your password?
                <Link
                    :href="route('login')"
                    class="font-medium text-accent hover:underline"
                >
                    Back to login
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
