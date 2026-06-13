<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppCheckbox from '@/Components/Shared/AppCheckbox.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout
        title="Welcome back"
        subtitle="Sign in to your account to continue shopping."
    >
        <Head title="Log in" />

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

            <div>
                <AppInputLabel for="password" value="Password" />
                <AppInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.password"
                    required
                    autocomplete="current-password"
                />
                <AppInputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2">
                    <AppCheckbox v-model:checked="form.remember" name="remember" />
                    <span class="text-body-sm text-text-muted">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-body-sm text-accent hover:underline"
                >
                    Forgot password?
                </Link>
            </div>

            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Log in
            </AppButton>

            <p class="text-center text-body-sm text-text-muted">
                Don't have an account?
                <Link
                    :href="route('register')"
                    class="font-medium text-accent hover:underline"
                >
                    Sign up
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
