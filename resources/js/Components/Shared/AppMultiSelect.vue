<script setup>
import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/vue';
import { IconChevronDown, IconX } from '@tabler/icons-vue';
import { computed } from 'vue';

const model = defineModel({
    type: Array,
    default: () => [],
});

const props = defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Add…',
    },
    hasError: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    labelKey: {
        type: String,
        default: 'name',
    },
});

const selectedOptions = computed(() =>
    props.options.filter((option) =>
        model.value.includes(option[props.valueKey]),
    ),
);

const availableOptions = computed(() =>
    props.options.filter(
        (option) => !model.value.includes(option[props.valueKey]),
    ),
);

const remove = (value) => {
    model.value = model.value.filter((id) => id !== value);
};

const onSelect = (value) => {
    if (value === null || value === undefined || value === '') {
        return;
    }
    if (!model.value.includes(value)) {
        model.value = [...model.value, value];
    }
};
</script>

<template>
    <Listbox
        :model-value="null"
        :disabled="disabled || availableOptions.length === 0"
        as="div"
        class="relative"
        @update:model-value="onSelect"
    >
        <ListboxButton
            v-slot="{ open }"
            class="dropdown-trigger min-h-12 h-auto py-2"
            :class="{
                'dropdown-trigger-open': open,
                'dropdown-trigger-error': hasError,
                'cursor-not-allowed opacity-60': disabled,
            }"
        >
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 pr-8">
                <span
                    v-for="option in selectedOptions"
                    :key="option[valueKey]"
                    class="dropdown-tag"
                    @click.stop
                >
                    {{ option[labelKey] }}
                    <button
                        type="button"
                        class="inline-flex rounded-full p-0.5 hover:bg-white/20"
                        :aria-label="`Remove ${option[labelKey]}`"
                        @click.stop="remove(option[valueKey])"
                    >
                        <IconX :size="12" stroke-width="2.5" />
                    </button>
                </span>
                <span class="text-sm text-text-muted">
                    {{
                        availableOptions.length === 0 && selectedOptions.length
                            ? 'All selected'
                            : placeholder
                    }}
                </span>
            </div>
            <IconChevronDown
                class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 transition-transform"
                :class="open ? 'rotate-180 text-accent' : 'text-outline'"
                stroke-width="1.5"
            />
        </ListboxButton>

        <ListboxOptions v-if="availableOptions.length" class="dropdown-panel">
            <ListboxOption
                v-for="option in availableOptions"
                :key="option[valueKey]"
                v-slot="{ active }"
                :value="option[valueKey]"
                as="template"
            >
                <li
                    class="dropdown-option"
                    :class="{ 'dropdown-option-active': active }"
                >
                    {{ option[labelKey] }}
                </li>
            </ListboxOption>
        </ListboxOptions>
    </Listbox>
</template>
