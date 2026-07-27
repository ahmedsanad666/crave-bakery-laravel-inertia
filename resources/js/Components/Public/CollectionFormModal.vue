<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconLock, IconX } from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    description: '',
    privacy: 'private',
});

watch(
    () => props.show,
    (show) => {
        if (show) {
            form.reset();
            form.clearErrors();
            form.privacy = 'private';
        }
    },
);

const close = () => {
    if (form.processing) {
        return;
    }
    emit('close');
};

const submit = () => {
    form.post(route('collections.store'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};

const togglePrivacy = () => {
    form.privacy = form.privacy === 'private' ? 'public' : 'private';
};
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-tertiary/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto p-md">
                <div class="flex min-h-full items-center justify-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-200"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl"
                        >
                            <div
                                class="sticky top-0 z-10 flex items-center justify-between border-b border-outline-variant bg-white p-xl"
                            >
                                <DialogTitle
                                    class="font-serif text-headline-sm text-primary"
                                >
                                    Create New Collection
                                </DialogTitle>
                                <button
                                    type="button"
                                    class="text-on-surface-variant transition-colors hover:text-primary"
                                    aria-label="Close"
                                    @click="close"
                                >
                                    <IconX :size="22" stroke-width="1.5" />
                                </button>
                            </div>

                            <form class="space-y-xl p-xl" @submit.prevent="submit">
                                <div class="space-y-md">
                                    <div>
                                        <AppInputLabel for="collection-name" value="Collection Name" />
                                        <AppInput
                                            id="collection-name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="e.g. Birthday Party Ideas"
                                            required
                                        />
                                        <AppInputError class="mt-1" :message="form.errors.name" />
                                    </div>

                                    <div>
                                        <AppInputLabel
                                            for="collection-description"
                                            value="Description (Optional)"
                                        />
                                        <textarea
                                            id="collection-description"
                                            v-model="form.description"
                                            rows="4"
                                            class="mt-1 block w-full resize-none rounded-xl border border-outline-variant bg-white p-md font-sans text-body-sm text-on-surface outline-none transition focus:border-secondary focus:ring-1 focus:ring-secondary/20"
                                            placeholder="Tell us what this collection is for..."
                                        />
                                        <AppInputError
                                            class="mt-1"
                                            :message="form.errors.description"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between py-md">
                                    <div class="flex items-center gap-md">
                                        <IconLock
                                            class="text-outline"
                                            :size="22"
                                            stroke-width="1.5"
                                        />
                                        <div>
                                            <p class="font-sans text-label-caps text-on-surface">
                                                Private Collection
                                            </p>
                                            <p class="text-[10px] text-on-surface-variant">
                                                Only you can see this
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        role="switch"
                                        :aria-checked="form.privacy === 'private'"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors"
                                        :class="
                                            form.privacy === 'private'
                                                ? 'bg-secondary'
                                                : 'bg-surface-container-highest'
                                        "
                                        @click="togglePrivacy"
                                    >
                                        <span
                                            class="absolute top-[2px] h-5 w-5 rounded-full bg-white transition-all"
                                            :class="
                                                form.privacy === 'private'
                                                    ? 'left-[22px]'
                                                    : 'left-[2px]'
                                            "
                                        />
                                    </button>
                                </div>

                                <AppInputError :message="form.errors.privacy" />

                                <div class="flex gap-md border-t border-outline-variant pt-xl">
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        class="flex-1"
                                        :disabled="form.processing"
                                        @click="close"
                                    >
                                        Cancel
                                    </AppButton>
                                    <AppButton
                                        type="submit"
                                        class="flex-1"
                                        :disabled="form.processing"
                                    >
                                        Create Collection
                                    </AppButton>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
