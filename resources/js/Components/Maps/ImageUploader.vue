<script setup>
    import { ref, watch } from 'vue';
    import { Upload, X, Image as ImageIcon } from 'lucide-vue-next';
    
    const props = defineProps({
        modelValue: [File, null], // El archivo seleccionado
        existingImage: String,    // URL de imagen existente (si la hay)
        aspectRatio: { type: String, default: 'square' }, // 'square', 'video', etc.
        label: { type: String, default: 'Subir Imagen' }
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    const preview = ref(props.existingImage);
    const fileInput = ref(null);
    
    // Sincronizar cambios externos (reset del formulario)
    watch(() => props.modelValue, (val) => {
        if (!val) {
            preview.value = props.existingImage;
            if (fileInput.value) fileInput.value.value = '';
        }
    });
    
    // Sincronizar cambios en existingImage
    watch(() => props.existingImage, (val) => {
        if (!props.modelValue) {
            preview.value = val;
        }
    });
    
    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
            // Crear preview local
            preview.value = URL.createObjectURL(file);
            emit('update:modelValue', file);
        }
    };
    
    const removeImage = () => {
        preview.value = null;
        emit('update:modelValue', null);
        if (fileInput.value) fileInput.value.value = '';
    };
    
    const triggerBrowse = () => {
        fileInput.value.click();
    };
    </script>
    
    <template>
        <div class="w-full">
            <input 
                type="file" 
                ref="fileInput" 
                class="hidden" 
                accept="image/*" 
                @change="handleFileChange" 
            />
    
            <div 
                @click="triggerBrowse"
                class="relative overflow-hidden bg-muted/30 border-2 border-dashed border-border hover:border-primary/50 transition-colors cursor-pointer group flex flex-col items-center justify-center text-center"
                :class="[
                    aspectRatio === 'square' ? 'aspect-square rounded-full w-32 h-32 mx-auto' : 'aspect-video rounded-xl w-full'
                ]"
            >
                <img 
                    v-if="preview" 
                    :src="preview" 
                    class="absolute inset-0 w-full h-full object-cover" 
                />
    
                <div v-if="!preview" class="p-4 text-muted-foreground group-hover:text-primary transition-colors">
                    <Upload :size="24" class="mx-auto mb-2" />
                    <slot name="label">
                        <span class="text-xs font-bold uppercase">{{ label }}</span>
                    </slot>
                </div>
    
                <div v-if="preview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-white text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <Upload :size="14" /> Cambiar
                    </span>
                </div>
            </div>
    
            <div v-if="preview && aspectRatio !== 'square'" class="mt-2 text-center">
                <button type="button" @click.stop="removeImage" class="text-xs text-error hover:underline flex items-center justify-center gap-1 mx-auto">
                    <X :size="12" /> Quitar imagen
                </button>
            </div>
        </div>
    </template>