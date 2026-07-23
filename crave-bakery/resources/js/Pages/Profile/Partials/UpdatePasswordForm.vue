<script setup>
import { IconEye, IconEyeOff } from '@tabler/icons-vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const strength = computed(() => {
    const val = form.password ?? '';
    if (val.length === 0) {
        return { width: '0%', color: '#e5e2e1', label: '' };
    }
    if (val.length < 6) {
        return { width: '25%', color: '#ba1a1a', label: 'Weak' };
    }
    if (val.length < 10) {
        return { width: '60%', color: '#b02f00', label: 'Fair' };
    }
    return { width: '100%', color: '#16a34a', label: 'Strong' };
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
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
                Security
            </h2>
        </div>

        <div class="max-w-2xl p-6">
            <form class="space-y-6" @submit.prevent="updatePassword">
                <div class="space-y-1">
                    <label
                        for="current_password"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Current Password
                    </label>
                    <div class="relative">
                        <input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 pr-12 outline-none focus:border-primary"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            :aria-label="
                                showCurrent
                                    ? 'Hide current password'
                                    : 'Show current password'
                            "
                            @click="showCurrent = !showCurrent"
                        >
                            <IconEyeOff
                                v-if="showCurrent"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <IconEye
                                v-else
                                :size="20"
                                stroke-width="1.5"
                            />
                        </button>
                    </div>
                    <p
                        v-if="form.errors.current_password"
                        class="text-sm text-error"
                    >
                        {{ form.errors.current_password }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label
                        for="password"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        New Password
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNew ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Enter new password"
                            class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 pr-12 outline-none focus:border-primary"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            :aria-label="
                                showNew ? 'Hide new password' : 'Show new password'
                            "
                            @click="showNew = !showNew"
                        >
                            <IconEyeOff
                                v-if="showNew"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <IconEye v-else :size="20" stroke-width="1.5" />
                        </button>
                    </div>
                    <div
                        class="mt-2 h-1 w-full overflow-hidden rounded-full bg-surface-variant"
                    >
                        <div
                            class="h-full transition-all duration-300"
                            :style="{
                                width: strength.width,
                                backgroundColor: strength.color,
                            }"
                        />
                    </div>
                    <p
                        v-if="strength.label"
                        class="text-[10px] font-medium text-on-surface-variant"
                    >
                        Password strength: {{ strength.label }}
                    </p>
                    <p v-if="form.errors.password" class="text-sm text-error">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label
                        for="password_confirmation"
                        class="font-sans text-label-caps font-bold uppercase tracking-wider text-on-surface-variant"
                    >
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Re-enter new password"
                            class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 pr-12 outline-none focus:border-primary"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            :aria-label="
                                showConfirm
                                    ? 'Hide confirm password'
                                    : 'Show confirm password'
                            "
                            @click="showConfirm = !showConfirm"
                        >
                            <IconEyeOff
                                v-if="showConfirm"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <IconEye v-else :size="20" stroke-width="1.5" />
                        </button>
                    </div>
                    <p
                        v-if="form.errors.password_confirmation"
                        class="text-sm text-error"
                    >
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <button
                        type="submit"
                        class="min-h-12 rounded-full border border-primary px-8 py-3 font-sans text-body-lg font-semibold text-primary transition-all hover:bg-surface-variant/10 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? 'Updating…'
                                : 'Update Password'
                        }}
                    </button>
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-medium text-success"
                    >
                        Password updated.
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>
