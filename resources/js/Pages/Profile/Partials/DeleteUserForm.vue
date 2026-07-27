<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div
        class="mb-8 overflow-hidden rounded-xl border border-error/20 bg-error-container/20"
    >
        <div
            class="border-b border-error/10 bg-error-container/30 px-6 py-4"
        >
            <h2 class="font-sans text-title-lg font-semibold text-error">
                Danger Zone
            </h2>
        </div>

        <div
            class="flex flex-col justify-between gap-6 p-6 md:flex-row md:items-center"
        >
            <div>
                <p class="font-sans text-body-lg font-bold text-primary">
                    Delete Account
                </p>
                <p class="font-sans text-body-sm text-on-surface-variant">
                    Once you delete your account, there is no going back. Please
                    be certain.
                </p>
            </div>
            <button
                type="button"
                class="min-h-12 shrink-0 rounded-full border border-error px-8 py-3 text-center font-sans text-body-lg font-semibold text-error transition-all hover:bg-error/10"
                @click="confirmUserDeletion"
            >
                Delete My Account
            </button>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="font-serif text-headline-sm text-primary">
                    Are you sure you want to delete your account?
                </h2>
                <p class="mt-2 font-sans text-body-sm text-on-surface-variant">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm.
                </p>

                <div class="mt-6">
                    <label
                        for="delete_password"
                        class="sr-only"
                    >
                        Password
                    </label>
                    <input
                        id="delete_password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Password"
                        class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 outline-none focus:border-primary sm:w-3/4"
                        @keyup.enter="deleteUser"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-2 text-sm text-error"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button
                        type="button"
                        class="min-h-11 rounded-full border border-outline-variant px-6 py-2 text-sm font-semibold text-primary hover:bg-surface-variant/10"
                        @click="closeModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="min-h-11 rounded-full bg-error px-6 py-2 text-sm font-semibold text-on-error disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{
                            form.processing
                                ? 'Deleting…'
                                : 'Delete Account'
                        }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
