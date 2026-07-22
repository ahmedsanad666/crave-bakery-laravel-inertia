<script setup>
import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/vue';
import { IconChevronDown } from '@tabler/icons-vue';
import { computed } from 'vue';

const model = defineModel({
    type: [String, Number, null],
    default: null,
});

const props = defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select…',
    },
    hasError: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['md', 'sm'].includes(value),
    },
    /** Option value key (default: id) */
    valueKey: {
        type: String,
        default: 'id',
    },
    /** Option label key (default: name) */
    labelKey: {
        type: String,
        default: 'name',
    },
});

const selectedOption = computed(() =>
    props.options.find(
        (option) => option[props.valueKey] === model.value,
    ),
);

const displayLabel = computed(() => {
    if (!selectedOption.value) {
        return props.placeholder;
    }
    return selectedOption.value[props.labelKey];
});
</script>

<template>
    <Listbox v-model="model" :disabled="disabled" as="div" class="relative">
        <ListboxButton
            v-slot="{ open }"
            class="dropdown-trigger"
            :class="{
                'dropdown-trigger-open': open,
                'dropdown-trigger-error': hasError,
                'dropdown-trigger-sm': size === 'sm',
                'cursor-not-allowed opacity-60': disabled,
            }"
        >
            <span
                class="block truncate pr-8"
                :class="
                    selectedOption
                        ? 'text-on-surface'
                        : 'text-text-muted'
                "
            >
                {{ displayLabel }}
            </span>
            <IconChevronDown
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 transition-transform"
                :class="[
                    open ? 'rotate-180 text-accent' : 'text-outline',
                    size === 'sm' ? 'size-4' : 'size-5',
                ]"
                stroke-width="1.5"
            />
        </ListboxButton>

        <ListboxOptions class="dropdown-panel">
            <ListboxOption
                v-for="option in options"
                :key="option[valueKey]"
                v-slot="{ active, selected }"
                :value="option[valueKey]"
                as="template"
            >
                <li
                    class="dropdown-option"
                    :class="{
                        'dropdown-option-active': active,
                        'dropdown-option-selected': selected,
                    }"
                >
                    {{ option[labelKey] }}
                </li>
            </ListboxOption>
            <li
                v-if="options.length === 0"
                class="px-4 py-2.5 text-sm text-text-muted"
            >
                No options available
            </li>
        </ListboxOptions>
    </Listbox>
</template>
