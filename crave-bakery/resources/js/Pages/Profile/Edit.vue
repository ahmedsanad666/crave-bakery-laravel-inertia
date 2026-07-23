<script setup>
import ProfileLayout from '@/Layouts/ProfileLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    status: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <ProfileLayout>
        <Head title="My Profile" />

        <section class="space-y-1">
            <h1 class="font-serif text-headline-md text-primary">My Profile</h1>
            <p class="font-sans text-body-lg text-on-surface-variant">
                Keep your details up to date
            </p>
        </section>

        <div class="grid grid-cols-1 gap-8">
            <UpdateProfileInformationForm :user="user" />
            <UpdatePasswordForm />
            <DeleteUserForm />
        </div>
    </ProfileLayout>
</template>
