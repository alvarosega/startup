<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import { debounce } from 'lodash';
    import { Search, UserPlus, Truck, Bike, Car, CheckCircle2, AlertTriangle, ChevronRight } from 'lucide-vue-next';
    
    const props = defineProps({ 
        drivers: Object, 
        filters: Object,
        pending_count: Number
    });
    
    const search = ref(props.filters.search || '');
    const currentTab = ref(props.filters.status || 'all');
    
    // Filtrado
    watch([search, currentTab], debounce(() => {
        router.get(route('admin.drivers.index'), { 
            search: search.value,
            status: currentTab.value === 'all' ? null : currentTab.value
        }, { preserveState: true, replace: true });
    }, 300));
    
    // Helpers visuales
    const getVehicleIcon = (type) => {
        if (type === 'moto') return Bike;
        if (type === 'car') return Car;
        return Truck;
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-7xl mx-auto py-8 px-4">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-gray-800 tracking-tight">Flota de Conductores</h1>
                        <p class="text-gray-500 text-sm mt-1">Verificación de identidad y gestión de vehículos.</p>
                    </div>
                    <Link :href="route('admin.drivers.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-600/20 flex items-center gap-2 transition hover:-translate-y-0.5">
                        <UserPlus :size="18"/> Nuevo Conductor
                    </Link>
                </div>
    
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                    
                    <div class="bg-gray-100 p-1 rounded-xl flex gap-1">
                        <button @click="currentTab = 'all'" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
                                :class="currentTab === 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            Todos los Conductores
                        </button>
                        <button @click="currentTab = 'pending'" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2"
                                :class="currentTab === 'pending' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            Pendientes de Verificación
                            <span v-if="pending_count > 0" class="bg-orange-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ pending_count }}</span>
                        </button>
                    </div>
    
                    <div class="relative w-full md:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
                        <input v-model="search" type="text" placeholder="Buscar por nombre o placa..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition outline-none">
                    </div>
                </div>
    
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link v-for="driver in drivers.data" :key="driver.id" 
                          :href="route('admin.drivers.edit', driver.id)"
                          class="group bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 relative overflow-hidden">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1.5" :class="driver.is_verified ? 'bg-green-500' : 'bg-orange-400'"></div>
    
                        <div class="flex justify-between items-start mb-4 pl-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 border border-gray-100">
                                    <component :is="getVehicleIcon(driver.vehicle_type)" :size="20"/>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 leading-tight">{{ driver.full_name }}</h3>
                                    <p class="text-xs text-gray-400 font-mono tracking-wide">{{ driver.license_plate }}</p>
                                </div>
                            </div>
                            <div v-if="!driver.is_verified" class="bg-orange-50 text-orange-600 px-2 py-1 rounded text-[10px] font-bold border border-orange-100 flex items-center gap-1 animate-pulse">
                                <AlertTriangle :size="12"/> Revisar
                            </div>
                        </div>
    
                        <div class="pl-3 border-t border-gray-50 pt-3 flex justify-between items-center">
                            <div class="text-xs text-gray-500">
                                <span :class="driver.is_active ? 'text-green-600' : 'text-red-500'">● {{ driver.is_active ? 'Activo' : 'Inactivo' }}</span>
                                <span class="mx-2 text-gray-300">|</span>
                                {{ driver.phone }}
                            </div>
                            <ChevronRight :size="16" class="text-gray-300 group-hover:text-blue-500 transition-colors"/>
                        </div>
                    </Link>
                </div>
    
                <div v-if="drivers.data.length === 0" class="py-12 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <Truck :size="24" class="text-gray-300"/>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">No se encontraron conductores.</p>
                </div>
    
            </div>
        </AdminLayout>
    </template>