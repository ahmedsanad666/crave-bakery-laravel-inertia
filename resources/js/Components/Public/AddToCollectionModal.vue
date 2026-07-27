<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconFolderPlus, IconX } from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
    collections: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'create-collection']);

const selectedCollectionId = ref(null);
const submitting = ref(false);

watch(
    () => props.show,
    (show) => {
        if (show) {
            selectedCollectionId.value = props.collections[0]?.id ?? null;
            submitting.value = false;
        }
    },
);

const hasCollections = computed(() => (props.collections?.length ?? 0) > 0);

const productName = computed(() => props.product?.name ?? 'this product');

const close = () => {
    if (submitting.value) {
        return;
    }
    emit('close');
};

const selectCollection = (collection) => {
    selectedCollectionId.value = collection.id;
};

const submit = () => {
    if (!props.product?.slug || !selectedCollectionId.value) {
        return;
    }

    submitting.value = true;

    router.post(
        route('collections.products.attach', {
            collection: selectedCollectionId.value,
            product: props.product.slug,
        }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => emit('close'),
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
};

const openCreate = () => {
    emit('create-collection');
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
                            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl"
                        >
                            <div
                                class="flex items-center justify-between border-b border-outline-variant p-xl"
                            >
                                <DialogTitle
                                    class="font-serif text-headline-sm text-primary"
                                >
                                    Add to Collection
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

                            <div class="space-y-md p-xl">
                                <p class="font-sans text-body-sm text-on-surface-variant">
                                    Choose a collection for
                                    <span class="font-semibold text-on-surface">
                                        {{ productName }}
                                    </span>
                                    .
                                </p>

                                <div
                                    v-if="hasCollections"
                                    class="max-h-64 space-y-sm overflow-y-auto"
                                    role="listbox"
                                    aria-label="Collections"
                                >
                                    <button
                                        v-for="collection in collections"
                                        :key="collection.id"
                                        type="button"
                                        role="option"
                                        :aria-selected="
                                            selectedCollectionId === collection.id
                                        "
                                        class="flex w-full items-center justify-between rounded-xl border px-md py-3 text-left transition-colors"
                                        :class="
                                            selectedCollectionId === collection.id
                                                ? 'border-secondary bg-secondary/5'
                                                : 'border-outline-variant hover:bg-surface-container'
                                        "
                                        @click="selectCollection(collection)"
                                    >
                                        <span class="font-sans text-body-sm font-semibold text-on-surface">
                                            {{ collection.name }}
                                        </span>
                                        <span class="font-sans text-body-sm text-on-surface-variant">
                                            {{ collection.products_count ?? 0 }}
                                            {{
                                                (collection.products_count ?? 0) === 1
                                                    ? 'item'
                                                    : 'items'
                                            }}
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-else
                                    class="rounded-xl border border-dashed border-outline-variant bg-surface-container-low px-md py-lg text-center"
                                >
                                    <IconFolderPlus
                                        class="mx-auto mb-sm text-outline"
                                        :size="32"
                                        stroke-width="1.5"
                                    />
                                    <p class="font-sans text-body-sm text-on-surface-variant">
                                        You don’t have any collections yet.
                                    </p>
                                    <button
                                        type="button"
                                        class="mt-md font-sans text-label-caps text-secondary hover:underline"
                                        @click="openCreate"
                                    >
                                        Create Collection
                                    </button>
                                </div>
                            </div>

                            <div
                                class="flex gap-md border-t border-outline-variant p-xl"
                            >
                                <AppButton
                                    type="button"
                                    variant="secondary"
                                    class="flex-1"
                                    :disabled="submitting"
                                    @click="close"
                                >
                                    Cancel
                                </AppButton>
                                <AppButton
                                    v-if="hasCollections"
                                    type="button"
                                    class="flex-1"
                                    :disabled="submitting || !selectedCollectionId"
                                    @click="submit"
                                >
                                    Add
                                </AppButton>
                                <AppButton
                                    v-else
                                    type="button"
                                    class="flex-1"
                                    @click="openCreate"
                                >
                                    Create Collection
                                </AppButton>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
