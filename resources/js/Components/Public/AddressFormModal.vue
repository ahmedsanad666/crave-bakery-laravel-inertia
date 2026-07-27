<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconMapPin, IconX } from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import AppCheckbox from '@/Components/Shared/AppCheckbox.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    address: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.address?.id);

const labelOptions = [
    { id: 'Home', name: 'Home' },
    { id: 'Office', name: 'Office' },
    { id: 'Other', name: 'Other' },
];

const emptyForm = () => ({
    label: 'Home',
    first_name: '',
    last_name: '',
    phone: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'UK',
    is_default: false,
});

const form = useForm(emptyForm());

const fillFromAddress = (address) => {
    if (!address) {
        form.defaults(emptyForm());
        form.reset();
        form.clearErrors();
        return;
    }

    form.defaults({
        label: address.label || 'Home',
        first_name: address.first_name || '',
        last_name: address.last_name || '',
        phone: address.phone || '',
        address_line1: address.address_line1 || '',
        address_line2: address.address_line2 || '',
        city: address.city || '',
        state: address.state || '',
        postal_code: address.postal_code || '',
        country: address.country || 'UK',
        is_default: !!address.is_default,
    });
    form.reset();
    form.clearErrors();
};

watch(
    () => [props.show, props.address],
    ([show]) => {
        if (show) {
            fillFromAddress(props.address);
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
    if (isEditing.value) {
        form.patch(route('addresses.update', props.address.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
        return;
    }

    form.post(route('addresses.store'), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
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
                            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-[0_8px_40px_rgba(0,0,0,0.15)]"
                        >
                            <div
                                class="flex items-center justify-between border-b border-outline-variant/30 px-lg py-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-full bg-secondary/10 text-secondary"
                                    >
                                        <IconMapPin :size="22" stroke-width="1.5" />
                                    </div>
                                    <div>
                                        <DialogTitle
                                            class="font-serif text-headline-sm text-primary"
                                        >
                                            {{
                                                isEditing
                                                    ? 'Edit address'
                                                    : 'Add address'
                                            }}
                                        </DialogTitle>
                                        <p
                                            class="font-sans text-body-sm text-on-surface-variant"
                                        >
                                            Delivery details for your orders
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-full p-2 transition-colors hover:bg-surface-container"
                                    aria-label="Close"
                                    @click="close"
                                >
                                    <IconX
                                        class="text-on-surface-variant"
                                        :size="20"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </div>

                            <form
                                class="flex min-h-0 flex-1 flex-col"
                                @submit.prevent="submit"
                            >
                                <div
                                    class="flex-1 space-y-4 overflow-y-auto p-lg"
                                >
                                    <div>
                                        <AppInputLabel value="Label" />
                                        <AppSelect
                                            v-model="form.label"
                                            class="mt-1"
                                            :options="labelOptions"
                                            :has-error="!!form.errors.label"
                                        />
                                        <AppInputError :message="form.errors.label" />
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <AppInputLabel
                                                for="address-first-name"
                                                value="First name"
                                            />
                                            <AppInput
                                                id="address-first-name"
                                                v-model="form.first_name"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.first_name"
                                                required
                                                autocomplete="given-name"
                                            />
                                            <AppInputError
                                                :message="form.errors.first_name"
                                            />
                                        </div>
                                        <div>
                                            <AppInputLabel
                                                for="address-last-name"
                                                value="Last name"
                                            />
                                            <AppInput
                                                id="address-last-name"
                                                v-model="form.last_name"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.last_name"
                                                required
                                                autocomplete="family-name"
                                            />
                                            <AppInputError
                                                :message="form.errors.last_name"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <AppInputLabel
                                            for="address-phone"
                                            value="Phone"
                                        />
                                        <AppInput
                                            id="address-phone"
                                            v-model="form.phone"
                                            type="tel"
                                            class="mt-1 block w-full"
                                            :has-error="!!form.errors.phone"
                                            autocomplete="tel"
                                        />
                                        <AppInputError :message="form.errors.phone" />
                                    </div>

                                    <div>
                                        <AppInputLabel
                                            for="address-line1"
                                            value="Address line 1"
                                        />
                                        <AppInput
                                            id="address-line1"
                                            v-model="form.address_line1"
                                            class="mt-1 block w-full"
                                            :has-error="!!form.errors.address_line1"
                                            required
                                            autocomplete="address-line1"
                                        />
                                        <AppInputError
                                            :message="form.errors.address_line1"
                                        />
                                    </div>

                                    <div>
                                        <AppInputLabel
                                            for="address-line2"
                                            value="Address line 2"
                                        />
                                        <AppInput
                                            id="address-line2"
                                            v-model="form.address_line2"
                                            class="mt-1 block w-full"
                                            :has-error="!!form.errors.address_line2"
                                            autocomplete="address-line2"
                                        />
                                        <AppInputError
                                            :message="form.errors.address_line2"
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <AppInputLabel
                                                for="address-city"
                                                value="City"
                                            />
                                            <AppInput
                                                id="address-city"
                                                v-model="form.city"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.city"
                                                required
                                                autocomplete="address-level2"
                                            />
                                            <AppInputError
                                                :message="form.errors.city"
                                            />
                                        </div>
                                        <div>
                                            <AppInputLabel
                                                for="address-state"
                                                value="State / County"
                                            />
                                            <AppInput
                                                id="address-state"
                                                v-model="form.state"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.state"
                                                autocomplete="address-level1"
                                            />
                                            <AppInputError
                                                :message="form.errors.state"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <AppInputLabel
                                                for="address-postal"
                                                value="Postal code"
                                            />
                                            <AppInput
                                                id="address-postal"
                                                v-model="form.postal_code"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.postal_code"
                                                required
                                                autocomplete="postal-code"
                                            />
                                            <AppInputError
                                                :message="form.errors.postal_code"
                                            />
                                        </div>
                                        <div>
                                            <AppInputLabel
                                                for="address-country"
                                                value="Country"
                                            />
                                            <AppInput
                                                id="address-country"
                                                v-model="form.country"
                                                class="mt-1 block w-full"
                                                :has-error="!!form.errors.country"
                                                required
                                                autocomplete="country-name"
                                            />
                                            <AppInputError
                                                :message="form.errors.country"
                                            />
                                        </div>
                                    </div>

                                    <label
                                        class="flex items-center gap-2 font-sans text-body-sm text-on-surface"
                                    >
                                        <AppCheckbox
                                            v-model:checked="form.is_default"
                                        />
                                        Set as default address
                                    </label>
                                    <AppInputError
                                        :message="form.errors.is_default"
                                    />
                                </div>

                                <div
                                    class="flex flex-col-reverse gap-sm border-t border-outline-variant/30 bg-surface-container-low px-lg py-md sm:flex-row sm:justify-end"
                                >
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        class="w-full sm:w-auto"
                                        :disabled="form.processing"
                                        @click="close"
                                    >
                                        Cancel
                                    </AppButton>
                                    <AppButton
                                        type="submit"
                                        class="w-full sm:w-auto"
                                        :loading="form.processing"
                                        :disabled="form.processing"
                                    >
                                        {{
                                            isEditing
                                                ? 'Save changes'
                                                : 'Save address'
                                        }}
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
