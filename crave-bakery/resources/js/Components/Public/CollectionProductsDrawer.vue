<script setup>
import { computed } from 'vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconFolderOff, IconX } from '@tabler/icons-vue';
import CollectionProductRow from '@/Components/Public/CollectionProductRow.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    collection: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'add-to-cart', 'remove']);

const products = computed(() => props.collection?.products ?? []);

const itemsLabel = computed(() => {
    const count = Number(
        props.collection?.products_count ?? products.value.length,
    );
    return `${count} item${count === 1 ? '' : 's'}`;
});

const close = () => emit('close');
</script>

<template>
    <TransitionRoot :show="show" as="template">
        <Dialog class="relative z-50" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-[#1A1A1A]/40" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10"
                    >
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-out duration-300"
                            enter-from="translate-x-full"
                            enter-to="translate-x-0"
                            leave="transform transition ease-in duration-200"
                            leave-from="translate-x-0"
                            leave-to="translate-x-full"
                        >
                            <DialogPanel
                                class="pointer-events-auto flex w-screen max-w-lg flex-col bg-white shadow-modal"
                            >
                                <div
                                    class="flex shrink-0 items-start justify-between gap-md border-b border-outline-variant p-lg"
                                >
                                    <div class="min-w-0">
                                        <DialogTitle
                                            class="font-serif text-headline-sm text-primary"
                                        >
                                            {{ collection?.name ?? 'Collection' }}
                                        </DialogTitle>
                                        <p
                                            class="mt-1 font-sans text-body-sm text-on-surface-variant"
                                        >
                                            {{ itemsLabel }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-full p-2 text-primary transition-colors hover:bg-surface-container"
                                        aria-label="Close collection"
                                        @click="close"
                                    >
                                        <IconX :size="20" stroke-width="1.5" />
                                    </button>
                                </div>

                                <div class="min-h-0 flex-1 overflow-y-auto">
                                    <div
                                        v-if="products.length"
                                        class="divide-y divide-outline-variant"
                                    >
                                        <CollectionProductRow
                                            v-for="product in products"
                                            :key="product.id"
                                            :product="product"
                                            :added-at="product.added_at"
                                            @add-to-cart="emit('add-to-cart', $event)"
                                            @remove="emit('remove', $event)"
                                        />
                                    </div>

                                    <div
                                        v-else
                                        class="flex flex-col items-center px-lg py-xxl text-center"
                                    >
                                        <div
                                            class="mb-md flex h-16 w-16 items-center justify-center rounded-full bg-surface-container text-outline"
                                        >
                                            <IconFolderOff
                                                :size="28"
                                                stroke-width="1.5"
                                            />
                                        </div>
                                        <p
                                            class="font-sans text-body-sm text-on-surface-variant"
                                        >
                                            This collection is empty. Add favourites
                                            to it from your saved items.
                                        </p>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
