<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout
        title="Confirm your password"
        subtitle="This is a secure area. Please confirm your password before continuing."
    >
        <Head title="Confirm Password" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <AppInputLabel for="password" value="Password" />
                <AppInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :has-error="!!form.errors.password"
                    required
                    autofocus
                    autocomplete="current-password"
                />
                <AppInputError :message="form.errors.password" />
            </div>

            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Confirm
            </AppButton>
        </form>
    </AuthLayout>
</template>
