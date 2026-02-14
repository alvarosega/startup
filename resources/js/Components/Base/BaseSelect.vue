<script setup>
import { ChevronDown, AlertCircle } from 'lucide-vue-next';

defineProps({
    modelValue: [String, Number, null],
    label: String,
    options: { type: Array, default: () => [] }, // Array de objetos {id, name}
    placeholder: { type: String, default: 'Seleccionar...' },
    error: String,
    icon: { 
        type: [Object, Function], 
        default: null 
    },
    required: Boolean,
    disabled: Boolean
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="form-label mb-2 ml-1 flex items-center gap-1">
            {{ label }} <span v-if="required" class="text-error">*</span>
        </label>
        
        <div class="relative group">
            <div v-if="icon" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none z-10">
                <component :is="icon" :size="18" />
            </div>

            <select 
                :value="modelValue"
                @change="$emit('update:modelValue', $event.target.value)"
                :disabled="disabled"
                class="input w-full appearance-none cursor-pointer bg-background"
                :class="[
                    icon ? 'pl-11' : '',
                    error ? 'border-error ring-error/10 focus:ring-error/20' : 'hover:border-primary/50'
                ]"
            >
                <option :value="null" disabled selected>{{ placeholder }}</option>
                <option v-for="opt in options" :key="opt.id" :value="opt.id">
                    {{ opt.name }}
                </option>
            </select>

            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none">
                <ChevronDown :size="16" />
            </div>
        </div>

        <Transition enter-active-class="animate-slide-down">
            <p v-if="error" class="form-error flex items-center gap-1.5 mt-2 ml-1">
                <AlertCircle :size="14" /> {{ error }}
            </p>
        </Transition>
    </div>
</template>