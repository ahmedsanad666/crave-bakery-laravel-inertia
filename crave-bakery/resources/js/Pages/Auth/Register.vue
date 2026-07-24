<script setup>
import { ref } from 'vue';
import { IconEye, IconEyeOff } from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

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
                <div class="relative mt-1">
                    <AppInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-12"
                        :has-error="!!form.errors.password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors hover:text-primary"
                        :aria-label="
                            showPassword ? 'Hide password' : 'Show password'
                        "
                        @click="showPassword = !showPassword"
                    >
                        <IconEyeOff
                            v-if="showPassword"
                            :size="20"
                            stroke-width="1.5"
                        />
                        <IconEye v-else :size="20" stroke-width="1.5" />
                    </button>
                </div>
                <AppInputError :message="form.errors.password" />
            </div>

            <div>
                <AppInputLabel
                    for="password_confirmation"
                    value="Confirm password"
                />
                <div class="relative mt-1">
                    <AppInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        class="block w-full pr-12"
                        :has-error="!!form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors hover:text-primary"
                        :aria-label="
                            showPasswordConfirmation
                                ? 'Hide confirm password'
                                : 'Show confirm password'
                        "
                        @click="
                            showPasswordConfirmation = !showPasswordConfirmation
                        "
                    >
                        <IconEyeOff
                            v-if="showPasswordConfirmation"
                            :size="20"
                            stroke-width="1.5"
                        />
                        <IconEye v-else :size="20" stroke-width="1.5" />
                    </button>
                </div>
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
