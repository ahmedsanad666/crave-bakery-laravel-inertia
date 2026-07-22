<script setup>
import { computed } from 'vue';

const props = defineProps({
    schema: {
        type: Object,
        default: () => ({}),
    },
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const scopes = computed(() => Object.entries(props.schema ?? {}));

const isChecked = (scope, action) =>
    Boolean(props.modelValue?.[scope]?.[action]);

const toggle = (scope, action) => {
    if (props.disabled) {
        return;
    }

    const next = structuredClone(props.modelValue ?? {});
    if (!next[scope]) {
        next[scope] = {};
    }
    next[scope][action] = !isChecked(scope, action);
    emit('update:modelValue', next);
};

const actionLabel = (action) =>
    action
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-outline-variant/20">
                    <th class="py-3 pr-4 font-sans text-label-caps uppercase text-on-surface-variant">
                        Module
                    </th>
                    <th class="py-3 font-sans text-label-caps uppercase text-on-surface-variant">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                <tr
                    v-for="[scope, definition] in scopes"
                    :key="scope"
                    class="transition-colors hover:bg-surface-container-low"
                >
                    <td class="py-4 pr-4 align-top font-sans text-body-sm font-semibold text-primary">
                        {{ definition.label ?? scope }}
                    </td>
                    <td class="py-4">
                        <div class="flex flex-wrap gap-x-5 gap-y-3">
                            <label
                                v-for="(label, action) in definition.actions ?? {}"
                                :key="`${scope}-${action}`"
                                class="inline-flex items-center gap-2"
                                :class="
                                    disabled
                                        ? 'cursor-not-allowed opacity-60'
                                        : 'cursor-pointer'
                                "
                            >
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="isChecked(scope, action)"
                                    :disabled="disabled"
                                    class="relative h-5 w-9 shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-secondary/40"
                                    :class="
                                        isChecked(scope, action)
                                            ? 'bg-secondary'
                                            : 'bg-outline-variant'
                                    "
                                    @click="toggle(scope, action)"
                                >
                                    <span
                                        class="absolute top-0.5 size-4 rounded-full bg-white shadow transition-transform"
                                        :class="
                                            isChecked(scope, action)
                                                ? 'left-4'
                                                : 'left-0.5'
                                        "
                                    />
                                </button>
                                <span class="text-body-sm text-on-surface-variant">
                                    {{ label || actionLabel(action) }}
                                </span>
                            </label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
