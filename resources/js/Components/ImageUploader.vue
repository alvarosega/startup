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

// --- CORRECCIÓN 1: Eliminamos la función handleClick ---
// Ya no es necesaria porque el input cubre toda el área y captura el clic nativo.

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
    <div class="w-full">
        <div class="relative h-full w-full">
            <div class="relative w-full h-full bg-muted border-2 border-dashed border-input rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all overflow-hidden group"
                 :class="aspectClasses[aspectRatio]">
                
                <input ref="fileInput" 
                       type="file" 
                       @change="handleFileChange" 
                       accept="image/png, image/jpeg, image/webp" 
                       class="absolute inset-0 opacity-0 cursor-pointer z-40 w-full h-full">
                
                <img v-if="existingImage && !imagePreview" 
                     :src="existingImage" 
                     class="absolute inset-0 w-full h-full object-cover z-10">
                
                <img v-if="imagePreview" 
                     :src="imagePreview" 
                     class="absolute inset-0 w-full h-full object-cover z-10">
                
                <div v-if="!existingImage && !imagePreview" 
                     class="text-center p-4 z-0 group-hover:scale-110 transition-transform duration-300">
                    <UploadCloud :size="32" class="mx-auto text-muted-foreground mb-2" />
                    <p class="text-xs font-medium text-muted-foreground">Clic para subir</p>
                    <p class="text-[10px] text-muted-foreground/70">PNG, JPG, WEBP</p>
                </div>

                <div v-if="existingImage || imagePreview" 
                     class="absolute inset-0 bg-black/50 z-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                    <p class="text-white font-bold text-sm">Cambiar Imagen</p>
                </div>
            </div>
            
            <button v-if="existingImage || imagePreview" 
                    type="button"
                    @click.stop="clearImage"
                    class="absolute top-2 right-2 z-50 p-2 rounded-full bg-red-500 text-white hover:bg-red-600 shadow-sm transition-colors"
                    title="Eliminar imagen">
                <span class="text-xs font-bold">✕</span>
            </button>
        </div>
        
        <p class="text-[10px] text-muted-foreground mt-2 text-center">
            Máximo 2MB. Formatos recomendados: 800x800px.
        </p>
    </div>
</template>