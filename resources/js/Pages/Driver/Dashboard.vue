<script setup>
    import { useForm } from '@inertiajs/vue3';
    import DriverLayout from '@/Layouts/DriverLayout.vue';
    import { ShieldAlert, Truck, UploadCloud, FileImage, CheckCircle2 } from 'lucide-vue-next';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    const props = defineProps({
        auth_status: Object, // { has_documents, is_verified }
        pendingOrders: Array 
    });
    
    // Formulario para documentos
    const form = useForm({
        ci_front: null,
        license_photo: null,
        vehicle_photo: null
    });
    
    const submitDocs = () => {
        form.post(route('driver.upload-docs'), {
            forceFormData: true,
        });
    };
    
    // Helper para preview de archivos (nombre)
    const handleFile = (field, e) => {
        form[field] = e.target.files[0];
    };
    </script>
    
    <template>
        <DriverLayout>
            
            <div v-if="!auth_status.has_documents" class="flex flex-col h-full py-4">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-black text-gray-800">Casi listo...</h2>
                    <p class="text-gray-500 text-sm px-6">Para activar tu cuenta, necesitamos fotos de tus documentos legales.</p>
                </div>
    
                <form @submit.prevent="submitDocs" class="space-y-6 px-2">
                    
                    <div class="bg-white p-4 rounded-xl border border-dashed border-blue-300 text-center relative hover:bg-blue-50 transition">
                        <input type="file" @change="handleFile('ci_front', $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" />
                        <div class="flex flex-col items-center gap-2">
                            <FileImage v-if="!form.ci_front" class="text-blue-400" :size="32"/>
                            <CheckCircle2 v-else class="text-green-500" :size="32"/>
                            
                            <span class="font-bold text-gray-700 text-sm">
                                {{ form.ci_front ? 'Carnet Seleccionado' : 'Subir Carnet (Anverso)' }}
                            </span>
                            <span class="text-xs text-gray-400" v-if="!form.ci_front">Toca aquí para tomar foto</span>
                        </div>
                    </div>
    
                    <div class="bg-white p-4 rounded-xl border border-dashed border-blue-300 text-center relative hover:bg-blue-50 transition">
                        <input type="file" @change="handleFile('license_photo', $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" />
                        <div class="flex flex-col items-center gap-2">
                            <div class="p-2 bg-blue-100 rounded-full text-blue-600"><Truck :size="20"/></div>
                            
                            <span class="font-bold text-gray-700 text-sm">
                                {{ form.license_photo ? 'Licencia Seleccionada' : 'Subir Licencia de Conducir' }}
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
                        <BaseButton type="submit" :isLoading="form.processing" class="w-full shadow-xl bg-blue-600 hover:bg-blue-700 text-white py-4">
                            <UploadCloud class="mr-2" :size="20"/> Enviar Documentos
                        </BaseButton>
                    </div>
                </form>
            </div>
    
            <div v-else-if="!auth_status.is_verified" class="flex flex-col items-center justify-center h-[70vh] text-center space-y-6 animate-in fade-in">
                <div class="w-24 h-24 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center animate-pulse">
                    <ShieldAlert :size="48" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800">Verificando Identidad</h2>
                    <p class="text-gray-500 mt-2 px-6 text-sm">
                        Hemos recibido tus documentos. El equipo administrativo los está revisando.
                        <br><br>
                        <strong>Esto suele tomar menos de 24 horas.</strong>
                    </p>
                </div>
            </div>
    
            <div v-else>
                <h2 class="text-lg font-black text-gray-800 mb-4 flex items-center gap-2">
                    <Truck class="text-blue-600" /> Mi Ruta de Hoy
                </h2>
    
                <div v-if="pendingOrders.length === 0" class="p-8 text-center bg-white rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-gray-400 font-medium">No tienes entregas asignadas (aún).</p>
                    <p class="text-xs text-gray-300 mt-1">Tu cuenta está activa y lista.</p>
                </div>
            </div>
    
        </DriverLayout>
    </template>