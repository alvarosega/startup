<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: { type: String, default: 'text' },
    error: String,
    placeholder: String,
    required: Boolean,
    autofocus: Boolean,
});

const emit = defineEmits(['update:modelValue']);
const showPassword = ref(false);

const inputType = computed(() => {
    return (props.type === 'password' && showPassword.value) ? 'text' : props.type;
});
</script>

<template>
    <div class="mb-4">
        <label v-if="label" class="block text-gray-700 text-sm font-bold mb-2">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input
                :value="modelValue"
                :type="inputType"
                :placeholder="placeholder"
                :required="required"
                :autofocus="autofocus"
                @input="$emit('update:modelValue', $event.target.value)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
                :class="error ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200'"
            />
            <button v-if="type === 'password'" type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500">
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
            </button>
        </div>
        <div v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</div>
    </div>
</template>
