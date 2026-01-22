<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import { 
        Save, ArrowLeft, ShieldCheck, User, Truck, 
        FileImage, ExternalLink, AlertTriangle, CheckCircle2 
    } from 'lucide-vue-next';
    
    const props = defineProps({ driver: Object });
    
    // Accedemos a los datos de manera plana si vienen del Controller, 
    // o anidada si vienen del modelo directo. Asumimos estructura del Controller.
    const form = useForm({
        first_name: props.driver.first_name,
        last_name: props.driver.last_name,
        phone: props.driver.phone,
        license_number: props.driver.license_number,
        license_plate: props.driver.license_plate,
        vehicle_type: props.driver.vehicle_type,
        is_identity_verified: !!props.driver.is_identity_verified, // Forzamos booleano
        is_active: !!props.driver.is_active
    });
    
    const submit = () => form.put(route('admin.drivers.update', props.driver.id));
    
    // Helper para imagen (Asume que usas 'storage/' link simbólico)
    const getImageUrl = (path) => path ? `/storage/${path}` : null;
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-6xl mx-auto py-8 px-4">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <div class="flex items-center gap-4">
                        <Link :href="route('admin.drivers.index')" class="p-2 bg-white border border-gray-200 rounded-full hover:bg-gray-50 transition shadow-sm">
                            <ArrowLeft :size="20" class="text-gray-600"/>
                        </Link>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-black text-gray-800 tracking-tight">
                                    {{ form.first_name }} {{ form.last_name }}
                                </h1>
                                <span v-if="form.is_identity_verified" class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded flex items-center gap-1">
                                    <CheckCircle2 :size="12"/> VERIFICADO
                                </span>
                                <span v-else class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-1 rounded flex items-center gap-1 animate-pulse">
                                    <AlertTriangle :size="12"/> PENDIENTE
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 font-mono mt-1">ID: #{{ driver.id }} • {{ form.phone }}</p>
                        </div>
                    </div>
    
                    <button @click="submit" :disabled="form.processing" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-600/20 flex items-center gap-2 transition active:scale-95 disabled:opacity-50">
                        <Save :size="18"/> Guardar Decisión
                    </button>
                </div>
    
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="space-y-6">
                        
                        <div class="bg-white rounded-2xl shadow-sm border p-6 transition-all duration-300"
                             :class="form.is_identity_verified ? 'border-green-200 ring-4 ring-green-50' : 'border-orange-200 ring-4 ring-orange-50'">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <ShieldCheck :class="form.is_identity_verified ? 'text-green-600' : 'text-orange-500'" />
                                    Estado de Verificación
                                </h3>
                                
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_identity_verified" class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                <span v-if="!form.is_identity_verified">
                                    Revisa cuidadosamente los documentos a la derecha. Si coinciden con los datos, activa el interruptor para habilitar al conductor.
                                </span>
                                <span v-else>
                                    Este conductor tiene permiso para operar. Desactiva el interruptor si detectas irregularidades.
                                </span>
                            </p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_active" class="checkbox rounded text-blue-600 w-4 h-4" />
                                <span class="text-xs font-bold text-gray-600">Cuenta Activa (Login)</span>
                            </div>
                        </div>
    
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                            <div class="flex items-center gap-2 text-gray-400 border-b border-gray-100 pb-2 mb-2">
                                <User :size="16"/> <span class="text-xs font-bold uppercase">Datos Editables</span>
                            </div>
                            <BaseInput v-model="form.first_name" label="Nombre" class="text-sm" />
                            <BaseInput v-model="form.last_name" label="Apellido" class="text-sm" />
                            <BaseInput v-model="form.license_number" label="Licencia" class="text-sm" />
                            <BaseInput v-model="form.license_plate" label="Placa" class="text-sm" />
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Vehículo</label>
                                <select v-model="form.vehicle_type" class="w-full border-gray-300 rounded-lg text-sm p-2.5 bg-gray-50">
                                    <option value="moto">Moto</option>
                                    <option value="car">Auto</option>
                                    <option value="truck">Camión</option>
                                </select>
                            </div>
                        </div>
                    </div>
    
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <FileImage class="text-blue-600"/> Evidencia Documental
                            </h3>
    
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-500 uppercase">Carnet de Identidad</span>
                                    </div>
                                    <div class="relative group rounded-xl overflow-hidden border-2 border-gray-300 bg-gray-200 aspect-video flex items-center justify-center">
                                        <img v-if="props.driver.profile_docs?.ci_front_path" 
                                             :src="getImageUrl(props.driver.profile_docs.ci_front_path)" 
                                             class="w-full h-full object-contain bg-black/5" />
                                        <div v-else class="text-gray-400 flex flex-col items-center">
                                            <AlertTriangle :size="24" class="mb-1"/>
                                            <span class="text-xs">No subido</span>
                                        </div>
    
                                        <a v-if="props.driver.profile_docs?.ci_front_path" 
                                           :href="getImageUrl(props.driver.profile_docs.ci_front_path)" 
                                           target="_blank"
                                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition cursor-pointer">
                                            <div class="bg-white text-gray-900 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2">
                                                <ExternalLink :size="14"/> Ver Original
                                            </div>
                                        </a>
                                    </div>
                                </div>
    
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-500 uppercase">Licencia de Conducir</span>
                                        <span class="font-mono text-gray-800 bg-gray-200 px-1 rounded">{{ form.license_number }}</span>
                                    </div>
                                    <div class="relative group rounded-xl overflow-hidden border-2 border-gray-300 bg-gray-200 aspect-video flex items-center justify-center">
                                        <img v-if="props.driver.profile_docs?.license_photo_path" 
                                             :src="getImageUrl(props.driver.profile_docs.license_photo_path)" 
                                             class="w-full h-full object-contain bg-black/5" />
                                        <div v-else class="text-gray-400 flex flex-col items-center">
                                            <AlertTriangle :size="24" class="mb-1"/>
                                            <span class="text-xs">No subido</span>
                                        </div>
    
                                        <a v-if="props.driver.profile_docs?.license_photo_path" 
                                           :href="getImageUrl(props.driver.profile_docs.license_photo_path)" 
                                           target="_blank"
                                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition cursor-pointer">
                                            <div class="bg-white text-gray-900 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2">
                                                <ExternalLink :size="14"/> Ver Original
                                            </div>
                                        </a>
                                    </div>
                                </div>
    
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-500 uppercase">Foto Vehículo</span>
                                        <span class="font-mono text-gray-800 bg-gray-200 px-1 rounded">{{ form.license_plate }}</span>
                                    </div>
                                    <div class="relative group rounded-xl overflow-hidden border-2 border-gray-300 bg-gray-200 aspect-video flex items-center justify-center">
                                        <img v-if="props.driver.profile_docs?.vehicle_photo_path" 
                                             :src="getImageUrl(props.driver.profile_docs.vehicle_photo_path)" 
                                             class="w-full h-full object-contain bg-black/5" />
                                        <div v-else class="text-gray-400 flex flex-col items-center">
                                            <Truck :size="24" class="mb-1"/>
                                            <span class="text-xs">Opcional / No subido</span>
                                        </div>
    
                                        <a v-if="props.driver.profile_docs?.vehicle_photo_path" 
                                           :href="getImageUrl(props.driver.profile_docs.vehicle_photo_path)" 
                                           target="_blank"
                                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition cursor-pointer">
                                            <div class="bg-white text-gray-900 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2">
                                                <ExternalLink :size="14"/> Ver Original
                                            </div>
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