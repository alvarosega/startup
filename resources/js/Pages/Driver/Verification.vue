<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import DriverLayout from '@/Layouts/DriverLayout.vue';
import { ShieldAlert, Truck, UploadCloud, FileImage, CheckCircle2 } from 'lucide-vue-next';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    driver: Object,
    status: String,
    hasDocs: Boolean
});

const form = useForm({
    ci_front: null,
    license_photo: null,
    vehicle_photo: null
});

const submitDocs = () => {
    form.post(route('driver.upload-docs'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};

const handleFile = (field, e) => {
    form[field] = e.target.files[0];
};
</script>
    
<template>
    <Head title="Verificación de Identidad" />
    <DriverLayout>
        <div v-if="!hasDocs" class="flex flex-col h-full py-4">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black text-gray-800">ddddd</h2>
                <p class="text-gray-500 text-sm px-6">Para activar tu cuenta, necesitamos fotos de tus documentos legales.</p>
            </div>

            <form @submit.prevent="submitDocs" class="space-y-6 px-2">
                <div class="bg-white p-4 rounded-xl border border-dashed border-amber-300 text-center relative hover:bg-amber-50 transition overflow-hidden">
                    <input type="file" @change="handleFile('ci_front', $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" />
                    <div class="flex flex-col items-center gap-2 relative z-0">
                        <FileImage v-if="!form.ci_front" class="text-amber-500" :size="32"/>
                        <CheckCircle2 v-else class="text-success" :size="32"/>
                        <span class="font-bold text-gray-700 text-sm">
                            {{ form.ci_front ? 'Archivo Seleccionado' : 'Subir Carnet (Obligatorio)' }}
                        </span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-dashed border-amber-300 text-center relative hover:bg-amber-50 transition">
                    <input type="file" @change="handleFile('license_photo', $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" />
                    <div class="flex flex-col items-center gap-2">
                        <div class="p-2 bg-amber-100 rounded-full text-amber-600"><Truck :size="20"/></div>
                        <span class="font-bold text-gray-700 text-sm">
                            {{ form.license_photo ? 'Licencia Seleccionada' : 'Subir Licencia de Conducir (Obligatorio)' }}
                        </span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-dashed border-gray-300 text-center relative hover:bg-gray-50 transition">
                    <input type="file" @change="handleFile('vehicle_photo', $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" />
                    <div class="flex flex-col items-center gap-2">
                        <span class="font-bold text-gray-600 text-sm">
                            {{ form.vehicle_photo ? 'Vehículo Seleccionado' : 'Foto del Vehículo (Opcional)' }}
                        </span>
                    </div>
                </div>

                <div class="pt-4">
                    <BaseButton type="submit" :isLoading="form.processing" class="w-full shadow-xl bg-amber-500 hover:bg-amber-600 text-white py-4 border-none rounded-2xl">
                        <UploadCloud class="mr-2" :size="20"/> Enviar Documentos
                    </BaseButton>
                </div>
            </form>
        </div>

        <div v-else class="flex flex-col items-center justify-center h-[70vh] text-center space-y-6">
            <div class="w-24 h-24 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center animate-pulse">
                <ShieldAlert :size="48" />
            </div>
            <div>
                <h2 class="text-2xl font-black text-gray-800">Verificando Identidad</h2>
                <p class="text-gray-500 mt-2 px-6 text-sm">Hemos recibido tus documentos. La terminal administrativa los está revisando de forma manual.</p>
            </div>
        </div>
    </DriverLayout>
</template>