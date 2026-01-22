<script setup>
    import { computed, ref } from 'vue';
    import { Eye, EyeOff, AlertCircle } from 'lucide-vue-next';
    
    const props = defineProps({
        modelValue: [String, Number],
        label: String,
        type: { type: String, default: 'text' },
        placeholder: String,
        error: String,
        icon: Object, // Componente Lucide
        disabled: Boolean,
        autofocus: Boolean
    });
    
    const emit = defineEmits(['update:modelValue']);
    const showPassword = ref(false);
    
    const inputType = computed(() => {
        if (props.type === 'password') return showPassword.value ? 'text' : 'password';
        return props.type;
    });
    </script>
    
    <template>
        <div class="w-full">
            <label v-if="label" class="block text-xs font-bold text-content-light uppercase tracking-wider mb-2 ml-1">
                {{ label }}
            </label>
            
            <div class="relative group">
                <div v-if="icon" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-content-light transition-colors group-focus-within:text-primary pointer-events-none">
                    <component :is="icon" :size="18" />
                </div>
    
                <input 
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    :type="inputType"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :autofocus="autofocus"
                    class="w-full bg-base border border-line text-content rounded-xl py-3 px-4 outline-none transition-all duration-200 ease-out focus:border-primary focus:ring-4 focus:ring-primary/10 disabled:opacity-60 disabled:cursor-not-allowed placeholder:text-content-light/50 font-medium"
                    :class="[
                        icon ? 'pl-11' : '', 
                        error ? 'border-red-500 focus:border-red-500 focus:ring-red-500/10' : 'hover:border-content-light/30'
                    ]"
                >
    
                <button v-if="type === 'password'" 
                        type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-content-light hover:text-primary transition-colors p-1 rounded-md hover:bg-surface">
                    <EyeOff v-if="showPassword" :size="18" />
                    <Eye v-else :size="18" />
                </button>
            </div>
    
            <transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="-translate-y-1 opacity-0" enter-to-class="translate-y-0 opacity-100">
                <p v-if="error" class="flex items-center gap-1.5 mt-2 text-xs text-red-600 font-bold ml-1">
                    <AlertCircle :size="14" /> {{ error }}
                </p>
            </transition>
        </div>
    </template>