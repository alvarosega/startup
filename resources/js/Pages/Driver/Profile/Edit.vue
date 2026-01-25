<script setup>
    import { useForm, Link } from '@inertiajs/vue3';
    import DriverLayout from '@/Layouts/DriverLayout.vue';
    import { ArrowLeft, Truck, Car, Save, AlertTriangle, FileText } from 'lucide-vue-next';

    const props = defineProps({
        driver: Object, 
    });

    const form = useForm({
        license_number: props.driver?.license_number || '',
        license_plate: props.driver?.license_plate || '',
        vehicle_type: props.driver?.vehicle_type || 'moto',
    });

    const submit = () => {
        form.patch(route('driver.profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                // Opcional: mostrar un toast de éxito
            }
        });
    };
</script>

<template>
    <DriverLayout>
        <div class="flex flex-col h-full py-2">
            
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('driver.dashboard')" class="p-2 hover:bg-gray-100 rounded-full transition text-gray-600">
                    <ArrowLeft :size="24" />
                </Link>
                <div>
                    <h1 class="text-xl font-black text-gray-800 tracking-tight">Mi Vehículo</h1>
                    <p class="text-xs text-gray-500">Datos operativos</p>
                </div>
            </div>

            <div v-if="driver?.status === 'verified'" class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-white rounded-full text-blue-600"><AlertTriangle :size="18"/></div>
                    <div>
                        <h3 class="text-sm font-bold text-blue-900">Perfil Verificado</h3>
                        <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                            Tu vehículo está activo. Cambiar la placa o licencia podría requerir una nueva validación manual por parte de administración.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Tipo de Vehículo</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.vehicle_type" value="moto" class="peer sr-only">
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-gray-100 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition group-hover:bg-gray-50 text-gray-400">
                                    <Truck :size="24" class="mb-1"/>
                                    <span class="text-xs font-bold">Moto</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.vehicle_type" value="car" class="peer sr-only">
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-gray-100 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition group-hover:bg-gray-50 text-gray-400">
                                    <Car :size="24" class="mb-1"/>
                                    <span class="text-xs font-bold">Auto</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.vehicle_type" value="truck" class="peer sr-only">
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-gray-100 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition group-hover:bg-gray-50 text-gray-400">
                                    <Truck :size="24" class="mb-1"/>
                                    <span class="text-xs font-bold">Camión</span>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.vehicle_type" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.vehicle_type }}</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Placa del Vehículo</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold text-xs">BO</span>
                                </div>
                                <input 
                                    v-model="form.license_plate" 
                                    type="text" 
                                    class="w-full pl-10 pr-3 py-3 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-black uppercase placeholder-gray-300"
                                    placeholder="1234-XXX"
                                >
                            </div>
                            <p v-if="form.errors.license_plate" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.license_plate }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nro. Licencia Conducir</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <FileText :size="16" class="text-gray-400" />
                                </div>
                                <input 
                                    v-model="form.license_number" 
                                    type="text" 
                                    class="w-full pl-10 pr-3 py-3 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-bold placeholder-gray-300"
                                    placeholder="Ej: 8456123 SC"
                                >
                            </div>
                            <p v-if="form.errors.license_number" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.license_number }}</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing || !form.isDirty"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-xl shadow-lg transition flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <Save :size="20"/> 
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </DriverLayout>
</template>