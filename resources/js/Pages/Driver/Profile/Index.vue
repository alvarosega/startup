<script setup>
    import { Link } from '@inertiajs/vue3';
    import DriverLayout from '@/Layouts/DriverLayout.vue';
    
    // Importación de Iconos
    import { 
        User, 
        Truck, 
        FileText, 
        Edit, 
        ShieldCheck, 
        AlertTriangle,
        Car
    } from 'lucide-vue-next';

    // Props que vienen del DriverController::indexProfile
    const props = defineProps({
        driver: Object,       // Datos del Resource (license_plate, vehicle_type, URLs de fotos, etc.)
        user_profile: Object, // Datos humanos (first_name, last_name)
        email: String,
        phone: String
    });

    // Configuración visual de estados
    const statusColors = {
        verified: 'bg-green-100 text-green-700 border-green-200',
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        rejected: 'bg-red-100 text-red-700 border-red-200',
        suspended: 'bg-gray-100 text-gray-700 border-gray-200'
    };

    const statusLabels = {
        verified: 'Verificado',
        pending: 'En Revisión',
        rejected: 'Rechazado',
        suspended: 'Suspendido'
    };

    // Helper para íconos de vehículos
    const vehicleIcon = (type) => {
        if (type === 'car') return Car;
        if (type === 'truck') return Truck;
        return Truck; // Default (moto suele usar Truck o Bike si lo importas)
    };
</script>

<template>
    <DriverLayout>
        <div class="space-y-6 pt-2">
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-20 bg-gradient-to-r from-gray-900 to-gray-800"></div>
                
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full bg-white p-1 shadow-lg mb-3">
                        <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                            <User :size="40" class="text-gray-400" />
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-black text-gray-900 leading-tight capitalize">
                        {{ user_profile?.first_name || 'Conductor' }} {{ user_profile?.last_name || '' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">{{ email }}</p>
                    <p class="text-sm text-gray-500 font-mono">{{ phone }}</p>

                    <div class="mt-4 px-4 py-1.5 rounded-full border text-xs font-black uppercase tracking-wide inline-block" 
                         :class="statusColors[driver?.status] || statusColors.pending">
                        {{ statusLabels[driver?.status] || 'Pendiente' }}
                    </div>
                </div>
            </div>

            <div v-if="driver?.status === 'rejected'" class="bg-red-50 border border-red-200 rounded-xl p-4 flex gap-3 items-start animate-in fade-in">
                <AlertTriangle class="text-red-600 shrink-0 mt-0.5" :size="20"/>
                <div>
                    <h3 class="font-bold text-red-800 text-sm">Documentación Rechazada</h3>
                    <p class="text-xs text-red-700 mt-1 leading-relaxed">
                        {{ driver.rejection_reason || 'Tus documentos no cumplen con los requisitos.' }}
                    </p>
                    <Link :href="route('driver.dashboard')" class="text-xs font-bold text-red-900 underline mt-2 block hover:text-red-950">
                        Ir al Dashboard para corregir
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <Truck :size="16" class="text-blue-600"/> Vehículo & Licencia
                    </h3>
                    
                    <Link :href="route('driver.profile.edit')" class="text-blue-600 hover:bg-blue-50 p-2 rounded-full transition group">
                        <Edit :size="18" class="group-hover:scale-110 transition"/>
                    </Link>
                </div>
                
                <div class="p-6 grid grid-cols-2 gap-y-6 gap-x-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tipo</p>
                        <p class="text-sm font-bold text-gray-900 capitalize flex items-center gap-1">
                            <component :is="vehicleIcon(driver?.vehicle_type)" :size="14" class="text-gray-500"/>
                            {{ driver?.vehicle_type || '---' }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Placa</p>
                        <div class="px-2 py-1 bg-gray-100 rounded text-xs font-black font-mono border border-gray-300 inline-block text-gray-800 tracking-wider uppercase">
                            {{ driver?.license_plate || '---' }}
                        </div>
                    </div>
                    
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Licencia de Conducir</p>
                        <p class="text-sm font-medium text-gray-900 font-mono bg-gray-50 p-2 rounded-lg border border-gray-100 block">
                            {{ driver?.license_number || 'No registrada' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <FileText :size="16" class="text-orange-500"/> Documentos Digitales
                    </h3>
                </div>
                
                <div class="divide-y divide-gray-100">
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-200 shrink-0">
                                <img v-if="driver?.ci_front_url" :src="driver.ci_front_url" class="w-full h-full object-cover transition group-hover:scale-110"/>
                                <User v-else :size="20"/>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Carnet de Identidad</span>
                        </div>
                        
                        <span v-if="driver?.ci_front_url" class="text-green-600 bg-green-50 p-1.5 rounded-full">
                            <ShieldCheck :size="18"/>
                        </span>
                        <span v-else class="text-gray-400 text-[10px] font-bold uppercase bg-gray-100 px-2 py-1 rounded">
                            Falta
                        </span>
                    </div>

                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-200 shrink-0">
                                <img v-if="driver?.license_photo_url" :src="driver.license_photo_url" class="w-full h-full object-cover transition group-hover:scale-110"/>
                                <FileText v-else :size="20"/>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Foto Licencia</span>
                        </div>
                        
                        <span v-if="driver?.license_photo_url" class="text-green-600 bg-green-50 p-1.5 rounded-full">
                            <ShieldCheck :size="18"/>
                        </span>
                        <span v-else class="text-gray-400 text-[10px] font-bold uppercase bg-gray-100 px-2 py-1 rounded">
                            Falta
                        </span>
                    </div>
                    
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-200 shrink-0">
                                <img v-if="driver?.vehicle_photo_url" :src="driver.vehicle_photo_url" class="w-full h-full object-cover transition group-hover:scale-110"/>
                                <Truck v-else :size="20"/>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Foto Vehículo</span>
                        </div>
                        
                        <span v-if="driver?.vehicle_photo_url" class="text-green-600 bg-green-50 p-1.5 rounded-full">
                            <ShieldCheck :size="18"/>
                        </span>
                        <span v-else class="text-gray-300 text-[10px] font-bold uppercase bg-gray-50 px-2 py-1 rounded">
                            Opcional
                        </span>
                    </div>
                </div>
            </div>

            <div class="pb-6">
                <Link :href="route('logout')" 
                      method="post" 
                      as="button" 
                      class="w-full py-4 text-sm font-bold text-red-500 hover:bg-red-50 rounded-xl transition border border-transparent hover:border-red-100 flex justify-center items-center gap-2 shadow-sm bg-white">
                    Cerrar Sesión
                </Link>
            </div>

        </div>
    </DriverLayout>
</template>