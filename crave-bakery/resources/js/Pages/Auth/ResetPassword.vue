<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout
        title="Reset your password"
        subtitle="Choose a new password for your account."
    >
        <Head title="Reset Password" />

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

            <div>
                <AppInputLabel for="password" value="New password" />
                <AppInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.password"
                    required
                    autocomplete="new-password"
                />
                <AppInputError :message="form.errors.password" />
            </div>

            <div>
                <AppInputLabel
                    for="password_confirmation"
                    value="Confirm new password"
                />
                <AppInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <AppInputError :message="form.errors.password_confirmation" />
            </div>

            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Reset password
            </AppButton>

            <p class="text-center text-body-sm text-text-muted">
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
