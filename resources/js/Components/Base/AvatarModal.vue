<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, UploadCloud, Save, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close']);

// 1. EL FORMULARIO DEBE LLAMARSE 'avatar' PARA COINCIDIR CON EL BACKEND
const form = useForm({
    avatar: null,
});

const previewUrl = ref(null);
const fileInput = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Guardamos el archivo en el form
    form.avatar = file;

    // Creamos preview local
    previewUrl.value = URL.createObjectURL(file);
};

const submit = () => {
    if (!form.avatar) return;

    // 2. USAMOS LA RUTA CORRECTA
    form.post(route('profile.avatar.update'), {
        forceFormData: true, // Importante para subir archivos
        preserveScroll: true,
        onSuccess: () => {
            close();
            // Opcional: Recargar la página si Inertia no actualiza el prop user automáticamente
            // router.reload(); 
        },
        onFinish: () => {
            // Limpiar preview si falla o termina
            if (form.hasErrors) previewUrl.value = null; 
        }
    });
};

const close = () => {
    form.reset();
    previewUrl.value = null;
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="close"></div>

        <div class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800">Cambiar Foto de Perfil</h3>
                <button @click="close" class="text-gray-400 hover:text-red-500 transition">
                    <X :size="20" />
                </button>
            </div>

            <div class="p-6 flex flex-col items-center">
                
                <div class="relative w-40 h-40 rounded-full bg-gray-100 border-4 border-white shadow-lg overflow-hidden mb-6 group cursor-pointer"
                     @click="fileInput.click()">
                    
                    <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover">
                    
                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                        <UploadCloud :size="32" class="mb-2"/>
                        <span class="text-[10px] font-bold uppercase">Subir Foto</span>
                    </div>

                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="text-white text-xs font-bold">Cambiar</span>
                    </div>
                </div>

                <input 
                    ref="fileInput"
                    type="file" 
                    accept="image/jpeg, image/png, image/jpg" 
                    class="hidden" 
                    @change="handleFileChange"
                >

                <div v-if="form.errors.avatar" class="mb-4 flex items-center gap-2 text-red-600 bg-red-50 px-3 py-2 rounded-lg text-xs font-bold w-full">
                    <AlertCircle :size="16" />
                    {{ form.errors.avatar }}
                </div>

                <div class="w-full flex gap-3">
                    <button @click="close" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition">
                        Cancelar
                    </button>
                    <button 
                        @click="submit" 
                        :disabled="!form.avatar || form.processing"
                        class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 active:scale-95 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Save v-if="!form.processing" :size="18" />
                        <span v-else class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                        {{ form.processing ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>