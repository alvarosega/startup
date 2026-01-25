<!-- resources/js/Components/CategoryTypeSelector.vue -->
<script setup>
    import { Folder, CornerDownRight, CheckCircle } from 'lucide-vue-next';
    
    const props = defineProps({
        modelValue: {
            type: String,
            default: 'parent'
        }
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    const types = [
        {
            id: 'parent',
            label: 'Categoría Padre',
            description: 'Raíz principal (Ej: Licores)',
            icon: Folder
        },
        {
            id: 'child',
            label: 'Subcategoría',
            description: 'Depende de otra (Ej: Whisky)',
            icon: CornerDownRight
        }
    ];
    
    const selectType = (typeId) => {
        emit('update:modelValue', typeId);
    };
    </script>
    
    <template>
        <div>
            <label class="block text-xs font-medium text-muted-foreground uppercase mb-4 text-center">
                Selecciona el Nivel Jerárquico
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="type in types" 
                     :key="type.id"
                     @click="selectType(type.id)"
                     class="cursor-pointer card border-2 p-6 text-center transition-all duration-200 group relative overflow-hidden hover:shadow-md"
                     :class="modelValue === type.id ? 'border-primary bg-primary/5' : 'border-input hover:border-muted-foreground'">
                    
                    <div class="mb-3 transform group-hover:scale-110 transition-transform duration-300">
                        <component :is="type.icon" :size="40" 
                                   :class="modelValue === type.id ? 'text-primary' : 'text-muted-foreground'" 
                                   class="mx-auto" />
                    </div>
                    
                    <span class="font-bold text-lg block mb-1"
                          :class="modelValue === type.id ? 'text-primary' : 'text-foreground'">
                        {{ type.label }}
                    </span>
                    
                    <p class="text-xs text-muted-foreground">
                        {{ type.description }}
                    </p>
                    
                    <div v-if="modelValue === type.id" class="absolute top-3 right-3 text-primary">
                        <CheckCircle :size="20" />
                    </div>
                </div>
            </div>
        </div>
    </template>