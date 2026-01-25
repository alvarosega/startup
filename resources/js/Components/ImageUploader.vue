<!-- resources/js/Components/ImageUploader.vue -->
<script setup>
    import { UploadCloud, Image } from 'lucide-vue-next';
    import { ref } from 'vue';
    
    const props = defineProps({
        modelValue: {
            type: [File, null],
            default: null
        },
        existingImage: {
            type: String,
            default: null
        },
        aspectRatio: {
            type: String,
            default: 'square'
        }
    });
    
    const emit = defineEmits(['update:modelValue']);
    
    const imagePreview = ref(null);
    const fileInput = ref(null);
    
    const aspectClasses = {
        square: 'aspect-square',
        video: 'aspect-video',
        product: 'aspect-product',
        banner: 'aspect-banner'
    };
    
    const handleClick = () => {
        fileInput.value.click();
    };
    
    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
            emit('update:modelValue', file);
            imagePreview.value = URL.createObjectURL(file);
        }
    };
    
    const clearImage = () => {
        emit('update:modelValue', null);
        imagePreview.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    };
    </script>
    
    <template>
        <div>
            <label class="block text-xs font-medium text-muted-foreground uppercase mb-2">
                Imagen de Portada
            </label>
            
            <div class="relative">
                <div class="relative w-full bg-muted border-2 border-dashed border-input rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all overflow-hidden group"
                     :class="aspectClasses[aspectRatio]"
                     @click="handleClick">
                    
                    <input ref="fileInput" 
                           type="file" 
                           @change="handleFileChange" 
                           accept="image/*" 
                           class="absolute inset-0 opacity-0 cursor-pointer z-20">
                    
                    <!-- Imagen existente o previsualización -->
                    <img v-if="existingImage && !imagePreview" 
                         :src="existingImage" 
                         class="absolute inset-0 w-full h-full object-cover z-10">
                    
                    <img v-if="imagePreview" 
                         :src="imagePreview" 
                         class="absolute inset-0 w-full h-full object-cover z-10">
                    
                    <!-- Estado vacío -->
                    <div v-if="!existingImage && !imagePreview" 
                         class="text-center p-4 z-0 group-hover:scale-110 transition-transform duration-300">
                        <UploadCloud :size="32" class="mx-auto text-muted-foreground mb-2" />
                        <p class="text-xs font-medium text-muted-foreground">Clic para subir</p>
                        <p class="text-[10px] text-muted-foreground/70">PNG, JPG, WEBP</p>
                    </div>
    
                    <!-- Estado con imagen (overlay para cambiar) -->
                    <div v-if="existingImage || imagePreview" 
                         class="absolute inset-0 bg-foreground/50 z-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <p class="text-background font-medium text-sm">Cambiar Imagen</p>
                    </div>
                </div>
                
                <!-- Botón para limpiar imagen -->
                <button v-if="existingImage || imagePreview" 
                        type="button"
                        @click.stop="clearImage"
                        class="absolute top-2 right-2 z-30 p-1.5 rounded-full bg-error text-error-foreground hover:bg-error/90 transition-colors">
                    <span class="text-xs">×</span>
                </button>
            </div>
            
            <p class="text-xs text-muted-foreground mt-2 text-center">
                Tamaño recomendado: 800x800px
            </p>
        </div>
    </template>