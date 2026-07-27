<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: {
        type: Object,
        default: null,
    },
    token: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: null,
    },
});

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin-invitations.accept', props.token), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout
        :title="invitation ? 'Accept your invitation' : 'Invitation unavailable'"
        :subtitle="
            invitation
                ? 'Create your admin account to join the Crave Bakery team.'
                : 'This invite link is invalid or has expired.'
        "
    >
        <Head title="Accept Invitation" />

        <div
            v-if="error || !invitation"
            class="space-y-md text-center"
        >
            <p class="rounded-lg bg-error-container px-4 py-3 text-body-sm text-on-error-container">
                {{ error || 'This invitation is no longer valid.' }}
            </p>
            <Link
                :href="route('login')"
                class="inline-flex font-semibold text-secondary underline-offset-2 hover:underline"
            >
                Back to login
            </Link>
        </div>

        <form v-else class="space-y-md" @submit.prevent="submit">
            <div>
                <AppInputLabel for="invite-email" value="Email" />
                <input
                    id="invite-email"
                    type="email"
                    :value="invitation.email"
                    disabled
                    class="input-field mt-1 opacity-80"
                />
            </div>

            <div>
                <AppInputLabel for="invite-name" value="Full name" />
                <AppInput
                    id="invite-name"
                    v-model="form.name"
                    type="text"
                    class="mt-1"
                    required
                    autocomplete="name"
                    autofocus
                />
                <AppInputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <AppInputLabel for="invite-password" value="Password" />
                <AppInput
                    id="invite-password"
                    v-model="form.password"
                    type="password"
                    class="mt-1"
                    required
                    autocomplete="new-password"
                />
                <AppInputError class="mt-1" :message="form.errors.password" />
            </div>

            <div>
                <AppInputLabel
                    for="invite-password-confirmation"
                    value="Confirm password"
                />
                <AppInput
                    id="invite-password-confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1"
                    required
                    autocomplete="new-password"
                />
            </div>

            <AppButton
                type="submit"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Creating account…' : 'Accept & continue' }}
            </AppButton>
        </form>
    </AuthLayout>
</template>
