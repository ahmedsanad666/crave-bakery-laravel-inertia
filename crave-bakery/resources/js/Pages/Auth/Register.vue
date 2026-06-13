<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout
        title="Create your account"
        subtitle="Join Crave Bakery and start enjoying fresh pastries delivered to your door."
    >
        <Head title="Register" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <AppInputLabel for="name" value="Full name" />
                <AppInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <AppInputError :message="form.errors.name" />
            </div>

            <div>
                <AppInputLabel for="email" value="Email" />
                <AppInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.email"
                    required
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
                    autocomplete="new-password"
                />
                <AppInputError :message="form.errors.password" />
            </div>

            <div>
                <AppInputLabel
                    for="password_confirmation"
                    value="Confirm password"
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
                Create account
            </AppButton>

            <p class="text-center text-body-sm text-text-muted">
                Already have an account?
                <Link
                    :href="route('login')"
                    class="font-medium text-accent hover:underline"
                >
                    Log in
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
