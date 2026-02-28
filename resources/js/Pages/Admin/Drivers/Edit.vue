<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft, ShieldCheck, User, Truck, FileImage, ExternalLink, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({ 
    driver: Object 
});

// El DTO (UpdateDriverData) y el Request solo permiten estos campos exactos.
// El teléfono y el email son inmutables en esta vista por seguridad de identidad global.
const form = useForm({
    first_name: props.driver.details?.first_name || '',
    last_name: props.driver.details?.last_name || '',
    license_number: props.driver.details?.license_number || '',
    license_plate: props.driver.details?.license_plate || '',
    vehicle_type: props.driver.details?.vehicle_type || 'moto',
    is_identity_verified: props.driver.details?.verification_status === 'verified',
    is_active: !!props.driver.is_active
});

const submit = () => {
    form.put(route('admin.drivers.update', props.driver.id), {
        preserveScroll: true,
    });
};

// Resuelve la URL pública del storage local
const getImageUrl = (path) => path ? `/storage/${path}` : null;
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto py-8 px-4">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.drivers.index')" class="p-2 rounded-full border border-gray-200 hover:bg-gray-100 transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-900 flex items-center gap-3">
                            {{ form.first_name }} {{ form.last_name }}
                        </h1>
                        <p class="text-sm text-gray-500 font-mono mt-1">
                            UUID: {{ driver.id }} | Telf: {{ driver.phone }}
                        </p>
                    </div>
                </div>
                
                <button @click="submit" :disabled="form.processing" class="bg-black text-white px-6 py-3 rounded-lg flex items-center gap-2 hover:bg-gray-800 transition-colors shadow-md">
                    <Save :size="18" /> 
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar Cambios</span>
                </button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="space-y-6">
                    
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm" :class="form.is_identity_verified ? 'border-t-4 border-t-green-500' : 'border-t-4 border-t-amber-500'">
                        <h3 class="font-semibold flex items-center gap-2 mb-4">
                            <ShieldCheck :size="18" :class="form.is_identity_verified ? 'text-green-500' : 'text-amber-500'" /> 
                            Control de Acceso
                        </h3>
                        
                        <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                            Audita los documentos adjuntos a la derecha. Si la información es verídica, aprueba la identidad para habilitar al conductor en el sistema de despacho.
                        </p>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span class="block text-sm font-bold text-gray-800">Identidad Verificada</span>
                                    <span class="text-[10px] text-gray-500 uppercase">{{ form.is_identity_verified ? 'Aprobado' : 'Pendiente' }}</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_identity_verified" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                            </div>

                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <div>
                                    <span class="block text-sm font-bold text-gray-800">Cuenta Activa (Login)</span>
                                    <span class="text-[10px] text-gray-500 uppercase">{{ form.is_active ? 'Permitido' : 'Bloqueado' }}</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                        <h3 class="font-semibold flex items-center gap-2 mb-6 border-b border-gray-100 pb-3">
                            <User :size="18" class="text-gray-400" /> Datos del Perfil
                        </h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-600 uppercase">Nombres</label>
                                    <input v-model="form.first_name" type="text" class="w-full text-sm border-gray-300 rounded-md focus:ring-black focus:border-black" />
                                    <p v-if="form.errors.first_name" class="text-[10px] text-red-500">{{ form.errors.first_name }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-600 uppercase">Apellidos</label>
                                    <input v-model="form.last_name" type="text" class="w-full text-sm border-gray-300 rounded-md focus:ring-black focus:border-black" />
                                    <p v-if="form.errors.last_name" class="text-[10px] text-red-500">{{ form.errors.last_name }}</p>
                                </div>
                            </div>

                            <div class="space-y-1 mt-4">
                                <label class="text-xs font-bold text-gray-600 uppercase flex items-center gap-1"><Truck :size="12"/> Logística</label>
                                <select v-model="form.vehicle_type" class="w-full text-sm border-gray-300 rounded-md focus:ring-black focus:border-black">
                                    <option value="moto">Motocicleta</option>
                                    <option value="car">Automóvil</option>
                                    <option value="truck">Camión de Carga</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-600 uppercase">Licencia</label>
                                    <input v-model="form.license_number" type="text" class="w-full text-sm border-gray-300 font-mono rounded-md focus:ring-black focus:border-black" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-600 uppercase">Placa</label>
                                    <input v-model="form.license_plate" type="text" class="w-full text-sm border-gray-300 font-mono rounded-md focus:ring-black focus:border-black" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm h-full">
                        <h3 class="font-semibold flex items-center gap-2 mb-6 border-b border-gray-100 pb-3">
                            <FileImage :size="18" class="text-gray-400" /> Evidencia Documental (Solo Lectura)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase text-gray-600">Carnet de Identidad</span>
                                </div>
                                <div class="relative rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 aspect-video flex items-center justify-center overflow-hidden group">
                                    <img v-if="driver.details?.ci_front_path" :src="getImageUrl(driver.details.ci_front_path)" class="w-full h-full object-contain" />
                                    
                                    <div v-else class="text-gray-400 flex flex-col items-center">
                                        <AlertTriangle :size="24" class="mb-2 text-amber-400" />
                                        <span class="text-xs font-semibold">Ausente</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.ci_front_path" :href="getImageUrl(driver.details.ci_front_path)" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200">
                                        <span class="bg-white text-black px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2">
                                            <ExternalLink :size="14" /> Ampliar Original
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase text-gray-600">Licencia de Conducir</span>
                                    <span v-if="driver.details?.license_number" class="text-[10px] font-mono bg-gray-200 px-2 py-0.5 rounded">{{ driver.details.license_number }}</span>
                                </div>
                                <div class="relative rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 aspect-video flex items-center justify-center overflow-hidden group">
                                    <img v-if="driver.details?.license_photo_path" :src="getImageUrl(driver.details.license_photo_path)" class="w-full h-full object-contain" />
                                    
                                    <div v-else class="text-gray-400 flex flex-col items-center">
                                        <AlertTriangle :size="24" class="mb-2 text-amber-400" />
                                        <span class="text-xs font-semibold">Ausente</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.license_photo_path" :href="getImageUrl(driver.details.license_photo_path)" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200">
                                        <span class="bg-white text-black px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2">
                                            <ExternalLink :size="14" /> Ampliar Original
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-2 md:col-span-2 md:w-1/2 md:mx-auto">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase text-gray-600">Fotografía del Vehículo (Opcional)</span>
                                    <span v-if="driver.details?.license_plate" class="text-[10px] font-mono bg-gray-200 px-2 py-0.5 rounded">{{ driver.details.license_plate }}</span>
                                </div>
                                <div class="relative rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 aspect-video flex items-center justify-center overflow-hidden group">
                                    <img v-if="driver.details?.vehicle_photo_path" :src="getImageUrl(driver.details.vehicle_photo_path)" class="w-full h-full object-contain" />
                                    
                                    <div v-else class="text-gray-400 flex flex-col items-center">
                                        <Truck :size="24" class="mb-2 text-gray-300" />
                                        <span class="text-xs font-semibold text-gray-400">Sin Imagen</span>
                                    </div>
                                    
                                    <a v-if="driver.details?.vehicle_photo_path" :href="getImageUrl(driver.details.vehicle_photo_path)" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200">
                                        <span class="bg-white text-black px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2">
                                            <ExternalLink :size="14" /> Ampliar Original
                                        </span>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>