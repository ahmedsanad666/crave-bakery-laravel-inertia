<script setup>
import AppButton from '@/Components/Shared/AppButton.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppInputError from '@/Components/Shared/AppInputError.vue';
import AppInputLabel from '@/Components/Shared/AppInputLabel.vue';
import Sortable from 'sortablejs';
import {
    IconCirclePlus,
    IconDeviceFloppy,
    IconGripVertical,
    IconTrash,
} from '@tabler/icons-vue';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    editingName: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['save', 'discard']);

const typeOptions = [
    { value: 'text', label: 'Text' },
    { value: 'number', label: 'Number' },
    { value: 'color', label: 'Color' },
    { value: 'boolean', label: 'Boolean' },
];

const displayTypeOptions = [
    { value: 'pills', label: 'Label Chips (Pills)' },
    { value: 'dropdown', label: 'Select Menu' },
    { value: 'swatches', label: 'Swatches' },
    { value: 'checkboxes', label: 'Checkboxes' },
];

const isColorType = computed(() => props.form.type === 'color');

const title = computed(() =>
    props.isEditing ? 'Edit Attribute' : 'Create Attribute',
);
const subtitle = computed(() =>
    props.isEditing
        ? `Update the configuration for '${props.editingName || 'attribute'}'`
        : 'Define a new global attribute for your catalogue.',
);

const valuesListRef = ref(null);
let valuesSortable = null;

const reindexSortOrders = () => {
    props.form.values.forEach((row, index) => {
        row.sort_order = index + 1;
    });
};

const initValuesSortable = () => {
    if (!valuesListRef.value) {
        return;
    }

    valuesSortable?.destroy();

    valuesSortable = Sortable.create(valuesListRef.value, {
        handle: '.value-drag-handle',
        animation: 150,
        draggable: '.attribute-value-row',
        onEnd(evt) {
            const moved = props.form.values.splice(evt.oldIndex, 1)[0];
            props.form.values.splice(evt.newIndex, 0, moved);
            reindexSortOrders();
        },
    });
};

const addValue = () => {
    props.form.values.push({
        id: null,
        value: '',
        color_swatch: isColorType.value ? '#E8572A' : null,
        sort_order: props.form.values.length + 1,
    });
    nextTick(initValuesSortable);
};

const removeValue = (index) => {
    props.form.values.splice(index, 1);
    reindexSortOrders();
};

watch(
    () => props.form.values.length,
    () => nextTick(initValuesSortable),
);

watch(
    () => props.form.type,
    (type) => {
        if (type === 'color') {
            props.form.values.forEach((row) => {
                if (!row.color_swatch) {
                    row.color_swatch = '#E8572A';
                }
            });
        }
    },
);

onMounted(() => nextTick(initValuesSortable));
onBeforeUnmount(() => valuesSortable?.destroy());
</script>

<template>
    <section
        class="space-y-xl rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-lg shadow-card"
    >
        <div>
            <h3 class="mb-2 font-serif text-headline-sm text-primary">
                {{ title }}
            </h3>
            <p class="text-body-sm text-on-surface-variant">{{ subtitle }}</p>
        </div>

        <div class="space-y-md">
            <div>
                <AppInputLabel
                    for="attr_name"
                    value="Attribute Name"
                    class="!mb-2 !font-sans !text-label-caps !uppercase !text-primary"
                />
                <AppInput
                    id="attr_name"
                    v-model="form.name"
                    class="block w-full"
                    placeholder="e.g. Size"
                    :has-error="!!form.errors.name"
                />
                <AppInputError :message="form.errors.name" />
            </div>

            <div class="grid grid-cols-2 gap-md">
                <div>
                    <AppInputLabel
                        for="attr_type"
                        value="Type"
                        class="!mb-2 !font-sans !text-label-caps !uppercase !text-primary"
                    />
                    <select
                        id="attr_type"
                        v-model="form.type"
                        class="input-field"
                        :class="{ 'input-field-error': form.errors.type }"
                    >
                        <option
                            v-for="option in typeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <AppInputError :message="form.errors.type" />
                </div>

                <div>
                    <AppInputLabel
                        for="attr_display_type"
                        value="Display Type"
                        class="!mb-2 !font-sans !text-label-caps !uppercase !text-primary"
                    />
                    <select
                        id="attr_display_type"
                        v-model="form.display_type"
                        class="input-field"
                        :class="{
                            'input-field-error': form.errors.display_type,
                        }"
                    >
                        <option
                            v-for="option in displayTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <AppInputError :message="form.errors.display_type" />
                </div>
            </div>

            <div class="space-y-md pt-xl">
                <div
                    class="flex items-center justify-between border-b border-outline-variant pb-2"
                >
                    <span
                        class="font-sans text-label-caps uppercase text-primary"
                    >
                        Attribute Values
                    </span>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 font-sans text-label-caps uppercase text-secondary hover:underline"
                        @click="addValue"
                    >
                        <IconCirclePlus :size="16" stroke-width="1.5" />
                        Add Value
                    </button>
                </div>

                <AppInputError :message="form.errors.values" />

                <div ref="valuesListRef" class="space-y-sm">
                    <div
                        v-for="(row, index) in form.values"
                        :key="row.id ?? `new-${index}`"
                        class="attribute-value-row flex items-center gap-md rounded-lg border border-outline-variant/30 bg-surface p-2 transition-all hover:border-secondary"
                    >
                        <button
                            type="button"
                            class="value-drag-handle cursor-grab text-outline active:cursor-grabbing"
                            title="Drag to reorder"
                        >
                            <IconGripVertical :size="20" stroke-width="1.5" />
                        </button>

                        <input
                            v-if="isColorType"
                            v-model="row.color_swatch"
                            type="color"
                            class="h-9 w-9 shrink-0 cursor-pointer rounded border border-outline-variant bg-transparent p-0"
                            :title="row.color_swatch || 'Pick color'"
                        />

                        <input
                            v-model="row.value"
                            type="text"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-body-lg focus:ring-0"
                            placeholder="Value label"
                        />

                        <span
                            class="shrink-0 text-on-surface-variant opacity-40"
                        >
                            #{{ String(index + 1).padStart(3, '0') }}
                        </span>

                        <button
                            type="button"
                            class="rounded-md p-1 text-error transition-colors hover:bg-error-container"
                            title="Remove value"
                            @click="removeValue(index)"
                        >
                            <IconTrash :size="20" stroke-width="1.5" />
                        </button>
                    </div>

                    <p
                        v-if="form.values.length === 0"
                        class="py-4 text-center text-body-sm text-on-surface-variant"
                    >
                        No values yet. Click “Add Value” to create one.
                    </p>
                </div>

                <p
                    v-for="(message, key) in form.errors"
                    v-show="String(key).startsWith('values.')"
                    :key="key"
                    class="text-sm text-error"
                >
                    {{ message }}
                </p>
            </div>
        </div>

        <div class="pt-xl">
            <AppButton
                type="button"
                variant="primary"
                class="!h-12 w-full !rounded-full !bg-primary-container !font-sans !text-label-caps !uppercase !text-on-primary-container hover:!bg-primary hover:!text-white"
                :loading="form.processing"
                :disabled="form.processing"
                @click="emit('save')"
            >
                <span class="inline-flex items-center justify-center gap-2">
                    <IconDeviceFloppy :size="18" stroke-width="1.5" />
                    Save Attribute
                </span>
            </AppButton>
            <button
                type="button"
                class="mt-2 h-12 w-full font-sans text-label-caps uppercase text-on-surface-variant hover:underline"
                :disabled="form.processing"
                @click="emit('discard')"
            >
                Discard Changes
            </button>
        </div>
    </section>
</template>
