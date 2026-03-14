<template>
    <DriverLayout>
        <div class="p-4 max-w-md mx-auto h-full flex flex-col">
            
            <div class="mb-6 mt-4">
                <h1 class="font-black text-2xl text-gray-900 uppercase italic tracking-tight">Tu <span class="text-blue-600">Perfil</span></h1>
                <p class="text-xs text-gray-500 mt-1">Gestión de identidad y credenciales</p>
            </div>

            <div v-if="status === 'active'" class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden relative">
                <div class="h-24 bg-blue-900 absolute top-0 w-full z-0"></div>
                <div class="p-6 relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-20 h-20 bg-white rounded-2xl shadow-lg border-4 border-white flex items-center justify-center font-black text-3xl text-gray-300">
                            {{ driver.profile?.first_name?.charAt(0) || 'D' }}
                        </div>
                        <span class="bg-green-100 text-green-700 text-[10px] font-black uppercase px-3 py-1 rounded-full border border-green-200 tracking-widest shadow-sm">
                            Cuenta Verificada
                        </span>
                    </div>
                    
                    <div>
                        <h2 class="text-xl font-black text-gray-900 uppercase">{{ driver.profile?.first_name }} {{ driver.profile?.last_name }}</h2>
                        <p class="text-sm font-mono font-bold text-gray-500 mt-1">{{ driver.phone }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ driver.email }}</p>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400">Sucursal Asignada</p>
                            <p class="text-sm font-black text-gray-800 line-clamp-1">{{ driver.branch?.name || 'No asignada' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400">Vehículo</p>
                            <p class="text-sm font-black text-gray-800 uppercase">{{ driver.profile?.license_plate || 'Sin Placa' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="status === 'pending' && hasDocs" class="bg-white rounded-2xl shadow-lg border border-amber-200 p-8 text-center mt-10">
                <div class="w-20 h-20 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">En Revisión</h3>
                <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                    Hemos recibido tus documentos. Un administrador verificará tu información para activarte en el radar.
                </p>
            </div>

            <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <h3 class="text-sm font-black text-blue-900 uppercase">Faltan Documentos</h3>
                    <p class="text-xs text-blue-700 mt-1">Sube las fotos para que podamos validar tu cuenta.</p>
                </div>

                <form @submit.prevent="submitDocs" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Carnet de Identidad (Anverso)</label>
                        <input type="file" @input="form.ci_front = $event.target.files[0]" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-gray-100 border border-gray-200 rounded-xl p-1">
                        <div v-if="form.errors.ci_front" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.ci_front }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Licencia de Conducir</label>
                        <input type="file" @input="form.license_photo = $event.target.files[0]" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-gray-100 border border-gray-200 rounded-xl p-1">
                        <div v-if="form.errors.license_photo" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.license_photo }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full py-4 rounded-xl bg-gray-900 text-white font-black uppercase tracking-widest text-sm shadow-lg disabled:opacity-50">
                        {{ form.processing ? 'Procesando...' : 'Enviar a Revisión' }}
                    </button>
                </form>
            </div>

        </div>
    </DriverLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({
    driver: Object,
    status: String,
    hasDocs: Boolean
});

const form = useForm({
    ci_front: null,
    license_photo: null,
});

const submitDocs = () => {
    form.post(route('driver.upload-docs'), {
        preserveScroll: true,
        forceFormData: true, // Crucial para subida de archivos
        onSuccess: () => {
            // El servidor recargará los props y 'hasDocs' será true
        }
    });
};
</script>