<!-- resources/js/Components/SearchInput.vue -->
<script setup>
    import { Search } from 'lucide-vue-next';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    
    const props = defineProps({
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Buscar...'
        }
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    const searchValue = ref(props.modelValue);
    
    watch(searchValue, debounce((val) => {
        emit('update:modelValue', val);
    }, 300));
    </script>
    
    <template>
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search :size="16" class="text-muted-foreground" />
            </div>
            <input 
                v-model="searchValue" 
                type="text" 
                :placeholder="placeholder" 
                class="pl-10 w-full bg-card border border-input text-foreground text-sm rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent placeholder-muted-foreground/50 transition-all shadow-sm"
            >
        </div>
    </template>