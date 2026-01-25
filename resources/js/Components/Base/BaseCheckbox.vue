<script setup>
    import { computed } from 'vue';
    
    const props = defineProps({
        modelValue: [Boolean, String, Number],
        label: String,
        error: String,
        disabled: Boolean,
        id: {
            type: String,
            default: () => `checkbox-${Math.random().toString(36).substr(2, 9)}`
        },
        trueValue: {
            type: [Boolean, String, Number],
            default: true
        },
        falseValue: {
            type: [Boolean, String, Number],
            default: false
        }
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    const isChecked = computed({
        get() {
            return props.modelValue === props.trueValue;
        },
        set(checked) {
            emit('update:modelValue', checked ? props.trueValue : props.falseValue);
        }
    });
    
    const handleChange = (event) => {
        isChecked.value = event.target.checked;
    };
    </script>
    
    <template>
        <div class="form-group">
            <label :for="id" class="flex items-center gap-3 cursor-pointer select-none group">
                <input 
                    :id="id"
                    type="checkbox"
                    :checked="isChecked"
                    :disabled="disabled"
                    @change="handleChange"
                    class="sr-only peer"
                />
                <span class="w-5 h-5 rounded-md border-2 border-input bg-background flex items-center justify-center transition-all duration-fast ease-smooth group-hover:border-primary group-hover:bg-primary/5 peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background peer-checked:bg-primary peer-checked:border-primary peer-checked:text-primary-foreground peer-disabled:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:bg-muted peer-disabled:border-muted">
                    <svg v-if="isChecked" class="w-3 h-3 text-current" viewBox="0 0 12 10" fill="none">
                        <path d="M10 2L4.5 7.5L2 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span v-if="label || $slots.default" class="text-sm text-foreground font-medium select-none peer-disabled:opacity-50 peer-disabled:cursor-not-allowed">
                    <slot>
                        {{ label }}
                    </slot>
                </span>
            </label>
            <p v-if="error" class="form-error">
                {{ error }}
            </p>
        </div>
    </template>