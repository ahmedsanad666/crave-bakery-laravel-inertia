<script setup>
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    IconCamera,
    IconChevronRight,
    IconLock,
    IconTrash,
    IconUser,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);

const profileForm = useForm({
    name: props.profile.name ?? '',
    email: props.profile.email ?? '',
    avatar: null,
    remove_avatar: false,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const avatarPreview = ref(props.profile.avatar ?? null);
const fileInput = ref(null);

watch(
    () => props.profile,
    (value) => {
        profileForm.name = value.name ?? '';
        profileForm.email = value.email ?? '';
        profileForm.avatar = null;
        profileForm.remove_avatar = false;
        avatarPreview.value = value.avatar ?? null;
    },
    { deep: true },
);

const initials = computed(() => {
    const name = props.profile.name ?? '';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
});

const roleLabel = computed(() => {
    if (props.profile.role === 'super_admin') {
        return 'Super Admin';
    }
    if (props.profile.role === 'admin') {
        return 'Admin';
    }
    return props.profile.role ?? 'Admin';
});

const onAvatarChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    profileForm.avatar = file;
    profileForm.remove_avatar = false;

    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const clearAvatar = () => {
    profileForm.avatar = null;
    profileForm.remove_avatar = true;
    avatarPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitProfile = () => {
    profileForm.post(route('admin.profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            profileForm.avatar = null;
            profileForm.remove_avatar = false;
        },
    });
};

const submitPassword = () => {
    passwordForm.put(route('admin.profile.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <AdminLayout title="My Profile" breadcrumb="My Profile">
        <Head title="My Profile" />

        <section class="mb-xl pt-4">
            <nav
                class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
            >
                <span>Admin</span>
                <IconChevronRight class="size-3.5" stroke="2" />
                <span class="text-secondary">My Profile</span>
            </nav>
            <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                My Profile
            </h2>
            <p class="mt-1 text-body-sm text-on-surface-variant">
                Update your personal information and password.
            </p>
        </section>

        <div
            v-if="flashSuccess"
            class="mb-lg rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-body-sm text-green-800"
        >
            {{ flashSuccess }}
        </div>

        <div class="grid grid-cols-1 gap-xl pb-xxl lg:grid-cols-3">
            <aside class="space-y-lg">
                <div
                    class="rounded-xl border border-outline-variant bg-white p-lg text-center shadow-sm"
                >
                    <div
                        class="mx-auto mb-md flex size-28 items-center justify-center overflow-hidden rounded-full border-4 border-primary-fixed bg-surface-container-high font-sans text-2xl font-semibold text-primary"
                    >
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            :alt="profile.name"
                            class="size-full object-cover"
                        />
                        <span v-else>{{ initials }}</span>
                    </div>
                    <h3 class="font-serif text-headline-sm text-primary">
                        {{ profileForm.name || profile.name }}
                    </h3>
                    <p class="mb-md truncate text-on-surface-variant">
                        {{ profileForm.email || profile.email }}
                    </p>
                    <div
                        class="inline-block rounded-full bg-primary-fixed px-4 py-1 font-sans text-label-caps uppercase text-on-primary-fixed"
                    >
                        {{ roleLabel }}
                    </div>
                </div>
            </aside>

            <div class="space-y-lg lg:col-span-2">
                <section
                    class="rounded-xl border border-outline-variant bg-white p-lg shadow-sm"
                >
                    <div class="mb-lg flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-secondary-fixed text-on-secondary-fixed"
                        >
                            <IconUser class="size-5" stroke="1.5" />
                        </div>
                        <div>
                            <h3 class="font-sans text-title-lg text-primary">
                                Personal information
                            </h3>
                            <p class="text-body-sm text-on-surface-variant">
                                Name, email, and profile photo
                            </p>
                        </div>
                    </div>

                    <form class="space-y-md" @submit.prevent="submitProfile">
                        <div class="flex flex-col gap-md sm:flex-row sm:items-center">
                            <div
                                class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-outline-variant bg-surface-container-high font-sans text-lg font-semibold text-primary"
                            >
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt=""
                                    class="size-full object-cover"
                                />
                                <span v-else>{{ initials }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="inline-flex h-10 items-center gap-2 rounded-full border border-outline-variant px-4 text-body-sm font-semibold text-on-surface transition-colors hover:bg-surface-container"
                                    @click="fileInput?.click()"
                                >
                                    <IconCamera class="size-4" stroke="1.5" />
                                    Change photo
                                </button>
                                <button
                                    v-if="avatarPreview"
                                    type="button"
                                    class="inline-flex h-10 items-center gap-2 rounded-full border border-error/40 px-4 text-body-sm font-semibold text-error transition-colors hover:bg-error-container"
                                    @click="clearAvatar"
                                >
                                    <IconTrash class="size-4" stroke="1.5" />
                                    Remove
                                </button>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onAvatarChange"
                                />
                            </div>
                        </div>
                        <AppInputError :message="profileForm.errors.avatar" />

                        <div>
                            <AppInputLabel for="profile-name" value="Full name" />
                            <AppInput
                                id="profile-name"
                                v-model="profileForm.name"
                                type="text"
                                class="mt-1"
                                required
                                autocomplete="name"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="profileForm.errors.name"
                            />
                        </div>

                        <div>
                            <AppInputLabel for="profile-email" value="Email" />
                            <AppInput
                                id="profile-email"
                                v-model="profileForm.email"
                                type="email"
                                class="mt-1"
                                required
                                autocomplete="username"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="profileForm.errors.email"
                            />
                        </div>

                        <div class="flex justify-end pt-sm">
                            <button
                                type="submit"
                                class="h-11 rounded-full bg-secondary px-8 font-bold text-white shadow-md transition-all hover:shadow-lg active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="profileForm.processing"
                            >
                                {{ profileForm.processing ? 'Saving…' : 'Save changes' }}
                            </button>
                        </div>
                    </form>
                </section>

                <section
                    class="rounded-xl border border-outline-variant bg-white p-lg shadow-sm"
                >
                    <div class="mb-lg flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-tertiary-fixed text-on-tertiary-fixed"
                        >
                            <IconLock class="size-5" stroke="1.5" />
                        </div>
                        <div>
                            <h3 class="font-sans text-title-lg text-primary">
                                Change password
                            </h3>
                            <p class="text-body-sm text-on-surface-variant">
                                Use a strong password you don’t reuse elsewhere
                            </p>
                        </div>
                    </div>

                    <form class="space-y-md" @submit.prevent="submitPassword">
                        <div>
                            <AppInputLabel
                                for="current-password"
                                value="Current password"
                            />
                            <AppInput
                                id="current-password"
                                v-model="passwordForm.current_password"
                                type="password"
                                class="mt-1"
                                required
                                autocomplete="current-password"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="passwordForm.errors.current_password"
                            />
                        </div>

                        <div>
                            <AppInputLabel for="new-password" value="New password" />
                            <AppInput
                                id="new-password"
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-1"
                                required
                                autocomplete="new-password"
                            />
                            <AppInputError
                                class="mt-1"
                                :message="passwordForm.errors.password"
                            />
                        </div>

                        <div>
                            <AppInputLabel
                                for="password-confirmation"
                                value="Confirm new password"
                            />
                            <AppInput
                                id="password-confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="mt-1"
                                required
                                autocomplete="new-password"
                            />
                        </div>

                        <div class="flex justify-end pt-sm">
                            <button
                                type="submit"
                                class="h-11 rounded-full border border-primary-container px-8 font-bold text-primary-container transition-colors hover:bg-primary-container hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="passwordForm.processing"
                            >
                                {{
                                    passwordForm.processing
                                        ? 'Updating…'
                                        : 'Update password'
                                }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
