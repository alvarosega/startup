<script setup>
import { computed, ref } from 'vue';
import { Eye, EyeOff, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: { type: String, default: 'text' },
    placeholder: String,
    error: String,
    disabled: Boolean,
    autofocus: Boolean,
    required: Boolean, // <--- Faltaba esta prop
    // CAMBIO: Usamos 'null' para aceptar cualquier tipo de componente (Objeto o Función) y silenciar la alerta
    icon: { type: null, default: null }, 
});

const emit = defineEmits(['update:modelValue']);
const showPassword = ref(false);

const inputType = computed(() => {
    if (props.type === 'password') return showPassword.value ? 'text' : 'password';
    return props.type;
});
</script>

<template>
    <div class="form-group w-full">
        <label v-if="label" class="form-label mb-2 ml-1 flex items-center gap-1">
            {{ label }}
            <span v-if="required" class="text-error">*</span>
        </label>
        
        <div class="relative group">
            <div v-if="icon" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary pointer-events-none">
                <component :is="icon" :size="18" />
            </div>

            <input 
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                :type="inputType"
                :placeholder="placeholder"
                :disabled="disabled"
                :autofocus="autofocus"
                class="input w-full"
                :class="[
                    icon ? 'pl-11' : '', 
                    error ? 'border-error focus:ring-error/10' : ''
                ]"
            >

            <button v-if="type === 'password'" 
                    type="button" 
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors p-2 rounded-lg hover:bg-muted">
                <EyeOff v-if="showPassword" :size="18" />
                <Eye v-else :size="18" />
            </button>
        </div>

        <Transition 
            enter-active-class="animate-slide-down" 
            leave-active-class="animate-fade-out"
        >
            <p v-if="error" class="form-error flex items-center gap-1.5 mt-2 ml-1">
                <AlertCircle :size="14" /> {{ error }}
            </p>
        </Transition>
    </div>
</template>