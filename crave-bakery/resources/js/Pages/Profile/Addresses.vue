<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconMapPin, IconPlus } from '@tabler/icons-vue';
import AddressCard from '@/Components/Public/AddressCard.vue';
import AddressFormModal from '@/Components/Public/AddressFormModal.vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import EmptyState from '@/Components/Shared/EmptyState.vue';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';

defineProps({
    addresses: {
        type: Array,
        default: () => [],
    },
    user: {
        type: Object,
        default: null,
    },
});

const formOpen = ref(false);
const editingAddress = ref(null);
const deleteTarget = ref(null);
const deleting = ref(false);

const isDeleteOpen = computed(() => !!deleteTarget.value);

const openCreate = () => {
    editingAddress.value = null;
    formOpen.value = true;
};

const openEdit = (address) => {
    editingAddress.value = address;
    formOpen.value = true;
};

const closeForm = () => {
    formOpen.value = false;
    editingAddress.value = null;
};

const askDelete = (address) => {
    deleteTarget.value = address;
};

const closeDelete = () => {
    if (deleting.value) {
        return;
    }
    deleteTarget.value = null;
};

const confirmDelete = () => {
    if (!deleteTarget.value) {
        return;
    }

    deleting.value = true;
    router.delete(route('addresses.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
};
</script>

<template>
    <ProfileLayout>
        <Head title="My Addresses" />

        <section
            class="flex flex-col gap-md sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="space-y-1">
                <h1 class="font-serif text-headline-md text-primary">
                    My Addresses
                </h1>
                <p class="font-sans text-body-lg text-on-surface-variant">
                    Manage where we deliver your orders
                </p>
            </div>
            <AppButton
                type="button"
                class="inline-flex shrink-0 items-center justify-center gap-2"
                @click="openCreate"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                Add address
            </AppButton>
        </section>

        <EmptyState
            v-if="!addresses.length"
            title="No addresses yet"
            description="Add a delivery address so checkout is quicker next time."
        >
            <AppButton type="button" @click="openCreate">
                <span class="inline-flex items-center gap-2">
                    <IconMapPin :size="18" stroke-width="1.5" />
                    Add your first address
                </span>
            </AppButton>
        </EmptyState>

        <div
            v-else
            class="grid grid-cols-1 gap-lg md:grid-cols-2"
        >
            <AddressCard
                v-for="address in addresses"
                :key="address.id"
                :address="address"
                :deleting="deleting && deleteTarget?.id === address.id"
                @edit="openEdit"
                @delete="askDelete"
            />
        </div>

        <AddressFormModal
            :show="formOpen"
            :address="editingAddress"
            @close="closeForm"
        />

        <TransitionRoot appear :show="isDeleteOpen" as="template">
            <Dialog as="div" class="relative z-50" @close="closeDelete">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-primary/40 backdrop-blur-[4px]" />
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
                                class="w-full max-w-md rounded-xl bg-white p-lg shadow-[0_8px_40px_rgba(0,0,0,0.15)]"
                            >
                                <DialogTitle
                                    class="font-serif text-headline-sm text-primary"
                                >
                                    Delete address?
                                </DialogTitle>
                                <p
                                    class="mt-2 font-sans text-body-sm text-on-surface-variant"
                                >
                                    This will remove
                                    <span class="font-semibold text-primary">
                                        {{ deleteTarget?.label }}
                                    </span>
                                    permanently. You can add it again later if needed.
                                </p>
                                <div
                                    class="mt-lg flex flex-col-reverse gap-sm sm:flex-row sm:justify-end"
                                >
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        :disabled="deleting"
                                        @click="closeDelete"
                                    >
                                        Cancel
                                    </AppButton>
                                    <AppButton
                                        type="button"
                                        variant="danger"
                                        :loading="deleting"
                                        :disabled="deleting"
                                        @click="confirmDelete"
                                    >
                                        Delete address
                                    </AppButton>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </ProfileLayout>
</template>
