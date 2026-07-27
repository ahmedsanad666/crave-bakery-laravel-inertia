<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const genderOptions = [
    { value: '', label: 'Select Gender' },
    { value: 'female', label: 'Female' },
    { value: 'male', label: 'Male' },
    { value: 'non_binary', label: 'Non-binary' },
    { value: 'prefer_not_to_say', label: 'Prefer not to say' },
];

const form = useForm({
    first_name: props.user.first_name ?? '',
    last_name: props.user.last_name ?? '',
    phone: props.user.phone ?? '',
    date_of_birth: props.user.date_of_birth ?? '',
    gender: props.user.gender ?? '',
});

const isVerified = computed(() => Boolean(props.user.email_verified_at));

const resetForm = () => {
    form.clearErrors();
    form.first_name = props.user.first_name ?? '';
    form.last_name = props.user.last_name ?? '';
    form.phone = props.user.phone ?? '';
    form.date_of_birth = props.user.date_of_birth ?? '';
    form.gender = props.user.gender ?? '';
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div
        class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-[0_2px_12px_rgba(0,0,0,0.07)]"
    >
        <div
            class="border-b border-outline-variant bg-surface-container-low px-6 py-4"
        >
            <h2 class="font-sans text-title-lg font-semibold text-primary">
                Personal Information
            </h2>
        </div>

        <div class="p-6">
            <form
                class="grid grid-cols-1 gap-6 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div class="space-y-1">
                    <label
                        for="first_name"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        First Name
                    </label>
                    <input
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        autocomplete="given-name"
                        required
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none transition-colors focus:border-primary"
                    />
                    <p
                        v-if="form.errors.first_name"
                        class="text-sm text-error"
                    >
                        {{ form.errors.first_name }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label
                        for="last_name"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Last Name
                    </label>
                    <input
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        autocomplete="family-name"
                        required
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none transition-colors focus:border-primary"
                    />
                    <p
                        v-if="form.errors.last_name"
                        class="text-sm text-error"
                    >
                        {{ form.errors.last_name }}
                    </p>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label
                        for="email"
                        class="flex items-center justify-between font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Email
                        <span
                            v-if="isVerified"
                            class="rounded bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-green-700"
                        >
                            Verified
                        </span>
                    </label>
                    <input
                        id="email"
                        type="email"
                        :value="user.email"
                        readonly
                        class="h-12 w-full cursor-not-allowed rounded-lg border border-outline-variant bg-surface-container-low px-4 text-on-surface-variant outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label
                        for="phone"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Phone Number
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        autocomplete="tel"
                        placeholder="+1 (555) 000-0000"
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none transition-colors focus:border-primary"
                    />
                    <p v-if="form.errors.phone" class="text-sm text-error">
                        {{ form.errors.phone }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label
                        for="date_of_birth"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Date of Birth
                    </label>
                    <input
                        id="date_of_birth"
                        v-model="form.date_of_birth"
                        type="date"
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none transition-colors focus:border-primary"
                    />
                    <p
                        v-if="form.errors.date_of_birth"
                        class="text-sm text-error"
                    >
                        {{ form.errors.date_of_birth }}
                    </p>
                </div>

                <div class="space-y-1 md:col-span-2 md:max-w-md">
                    <label
                        for="gender"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Gender
                    </label>
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none transition-colors focus:border-primary"
                    >
                        <option
                            v-for="option in genderOptions"
                            :key="option.value || 'empty'"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.gender" class="text-sm text-error">
                        {{ form.errors.gender }}
                    </p>
                </div>

                <div
                    class="flex flex-col gap-3 pt-4 sm:flex-row md:col-span-2"
                >
                    <button
                        type="submit"
                        class="min-h-12 rounded-full bg-accent px-8 py-3 font-sans text-body-lg font-semibold text-white transition-all hover:shadow-[0_4px_16px_rgba(0,0,0,0.1)] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </button>
                    <button
                        type="button"
                        class="min-h-12 rounded-full border border-primary px-8 py-3 font-sans text-body-lg font-semibold text-primary transition-all hover:bg-surface-variant/10"
                        :disabled="form.processing"
                        @click="resetForm"
                    >
                        Cancel
                    </button>
                    <p
                        v-if="form.recentlySuccessful"
                        class="self-center text-sm font-medium text-success"
                    >
                        Saved.
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>
