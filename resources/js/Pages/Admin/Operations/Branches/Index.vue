<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminLocationWorkflow from '@/Components/Admin/Maps/AdminLocationWorkflow.vue';
import { 
    MapPin, Edit, Building, Plus, 
    Copy, Target, Wifi, WifiOff, 
    Activity, Server, Clock, GitBranch
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    auth: Object
});

const isLoading = ref(true);
const mapRef = ref(null);
const mapCenter = ref([-16.5000, -68.1500]);
const mapZoom = ref(12);
const hoveredBranch = ref(null);
const selectedBranchType = ref('all');

onMounted(() => {
    setTimeout(() => { isLoading.value = false; }, 600);
    const defaultBranch = props.branches?.find(b => b.is_default);
    if (defaultBranch) {
        focusOnMap(defaultBranch);
    }
});

const focusOnMap = (branch) => {
    if (branch.latitude && branch.longitude) {
        mapCenter.value = [parseFloat(branch.latitude), parseFloat(branch.longitude)];
        mapZoom.value = 16;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const copyId = (id) => {
    navigator.clipboard.writeText(id);
    alert(`ID copiado al portapapeles: ${id}`);
};

const filteredBranches = computed(() => {
    // Adaptación para mapear la clave 'polygon' requerida por Leaflet desde 'coverage_polygon'
    const mapped = (props.branches || []).map(b => ({
        ...b,
        polygon: b.coverage_polygon
    }));

    if (selectedBranchType.value === 'all') return mapped;
    if (selectedBranchType.value === 'active') {
        return mapped.filter(b => b.is_active);
    }
    return mapped.filter(b => !b.is_active);
});

const stats = computed(() => {
    const total = props.branches?.length || 0;
    const active = props.branches?.filter(b => b.is_active).length || 0;
    const inactive = total - active;
    const surgeZones = props.branches?.filter(b => b.surge_multiplier > 1).length || 0;
    
    return { total, active, inactive, surgeZones };
});

const fmt = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

const getNetworkStatus = (branch) => {
    if (!branch.is_active) return { icon: WifiOff, class: 'text-rose-600 dark:text-rose-400', label: 'Inactiva' };
    if (branch.surge_multiplier > 1.5) return { icon: Activity, class: 'text-amber-500', label: 'Alerta surge' };
    return { icon: Wifi, class: 'text-emerald-600 dark:text-emerald-400', label: 'Activa' };
};
</script>

<template>
    <Head title="Operaciones - Sucursales" />
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Sucursales</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider mt-0.5 uppercase">Gestión de nodos operativos y cobertura logística</p>
                </div>
                
                <Link :href="route('admin.operations.branches.create')" 
                      class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2">
                    <Plus :size="16" />
                    Nueva sucursal
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-32 md:pb-12">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 select-none">
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-neutral-100 dark:bg-neutral-950 text-neutral-900 dark:text-white border border-neutral-200 dark:border-neutral-800 rounded-md">
                            <Server :size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Total Nodos</p>
                            <p class="text-2xl font-black text-neutral-900 dark:text-neutral-50 font-mono">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900">
                            <Wifi :size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Online</p>
                            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ stats.active }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900">
                            <WifiOff :size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Offline</p>
                            <p class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ stats.inactive }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-500 border border-amber-100 dark:border-amber-900">
                            <Activity :size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Zonas Surge</p>
                            <p class="text-2xl font-black text-amber-500 font-mono">{{ stats.surgeZones }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 border-b border-neutral-200 dark:border-neutral-800 pb-2 select-none">
                <button @click="selectedBranchType = 'all'"
                        class="px-3 py-1 text-xs font-bold uppercase tracking-wider transition-colors relative"
                        :class="selectedBranchType === 'all' ? 'text-neutral-900 dark:text-white border-b-2 border-neutral-900 dark:border-white' : 'text-neutral-400 dark:text-neutral-500 hover:text-neutral-900 dark:hover:text-white'">
                    Todas ({{ stats.total }})
                </button>
                <button @click="selectedBranchType = 'active'"
                        class="px-3 py-1 text-xs font-bold uppercase tracking-wider transition-colors relative"
                        :class="selectedBranchType === 'active' ? 'text-neutral-900 dark:text-white border-b-2 border-neutral-900 dark:border-white' : 'text-neutral-400 dark:text-neutral-500 hover:text-neutral-900 dark:hover:text-white'">
                    Activas ({{ stats.active }})
                </button>
                <button @click="selectedBranchType = 'inactive'"
                        class="px-3 py-1 text-xs font-bold uppercase tracking-wider transition-colors relative"
                        :class="selectedBranchType === 'inactive' ? 'text-neutral-900 dark:text-white border-b-2 border-neutral-900 dark:border-white' : 'text-neutral-400 dark:text-neutral-500 hover:text-neutral-900 dark:hover:text-white'">
                    Inactivas ({{ stats.inactive }})
                </button>
                <div class="flex-1"></div>
                <span class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 flex items-center gap-1 uppercase font-bold">
                    <Clock :size="14" />
                    SINC_TIME: {{ new Date().toLocaleTimeString() }}
                </span>
            </div>

            <div class="border border-neutral-200 dark:border-neutral-800 rounded-md overflow-hidden shadow-sm h-[320px]">
                <AdminLocationWorkflow 
                    ref="mapRef"
                    :active-branches="filteredBranches"
                    :form="{ 
                        latitude: mapCenter[0], 
                        longitude: mapCenter[1],
                        address: 'UBICACIÓN_CENTRAL' 
                    }"
                    class="w-full h-full grayscale dark:opacity-80"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skel-${n}`" 
                          class="h-[340px] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md motions-safe:animate-pulse"></div>
                </template>

                <template v-else>
                    <div v-for="branch in filteredBranches" :key="branch.id" 
                         @mouseenter="hoveredBranch = branch.id"
                         @mouseleave="hoveredBranch = null"
                         class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col"
                         :class="{ 'opacity-60 border-neutral-200 dark:border-neutral-800': !branch.is_active }">
                        
                        <div class="px-5 pt-4 flex justify-end gap-2 select-none">
                            <div v-if="branch.is_default" 
                                 class="bg-neutral-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded border border-neutral-200 dark:border-neutral-700 flex items-center gap-1">
                                <GitBranch :size="12" />
                                Principal
                            </div>
                            <div v-if="branch.surge_multiplier > 1"
                                 class="bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded border border-amber-200 dark:border-amber-900 flex items-center gap-1">
                                <Activity :size="12" />
                                Surge x{{ branch.surge_multiplier }}
                            </div>
                        </div>

                        <div class="px-5 pb-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-sm font-black text-neutral-900 dark:text-neutral-50 truncate max-w-[220px] uppercase font-mono tracking-tight">
                                        {{ branch.name }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-1 select-none">
                                        <component :is="getNetworkStatus(branch).icon" 
                                                   :size="14" 
                                                   :class="getNetworkStatus(branch).class" />
                                        <span class="text-[11px] font-mono font-bold uppercase" :class="getNetworkStatus(branch).class">
                                            {{ getNetworkStatus(branch).label }}
                                        </span>
                                    </div>
                                </div>
                                
                                <Link :href="route('admin.operations.branches.edit', branch.id)" 
                                      class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 border border-neutral-200 dark:border-neutral-800 rounded-md bg-neutral-50 dark:bg-neutral-950 transition-colors">
                                    <Edit :size="14" />
                                </Link>
                            </div>
                            <div class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-1.5 flex items-center gap-1 select-none">
                                <MapPin :size="12" class="text-neutral-400" />
                                {{ branch.city.toUpperCase() }} · ID: {{ String(branch.id).slice(0,8).toUpperCase() }}
                            </div>
                        </div>

                        <div class="px-5 py-3 flex-1 space-y-3 bg-neutral-50/50 dark:bg-neutral-950/20 border-t border-b border-neutral-100 dark:border-neutral-800/40">
                            <div class="grid grid-cols-2 gap-3 font-mono">
                                <div>
                                    <span class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 block uppercase">Base Delivery</span>
                                    <span class="text-xs font-bold text-neutral-900 dark:text-neutral-50">Bs. {{ Number(branch.delivery_base_fee).toFixed(2) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 block uppercase">Precio x Km</span>
                                    <span class="text-xs font-bold text-neutral-900 dark:text-neutral-50">Bs. {{ Number(branch.delivery_price_per_km).toFixed(2) }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 font-mono">
                                <div>
                                    <span class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 block uppercase">Pedido Mínimo</span>
                                    <span class="text-xs font-bold text-neutral-900 dark:text-neutral-50">Bs. {{ Number(branch.min_order_amount).toFixed(2) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 block uppercase">Service Fee</span>
                                    <span class="text-xs font-bold text-neutral-900 dark:text-neutral-50">{{ branch.base_service_fee_percentage }}%</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-xs text-neutral-500 dark:text-neutral-400 border-t border-neutral-200/50 dark:border-neutral-800/50 pt-2 font-mono">
                                <MapPin :size="14" class="shrink-0 mt-0.5 text-neutral-400" />
                                <span class="line-clamp-2 uppercase text-[11px] leading-tight">{{ branch.address }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 border-t border-neutral-200 dark:border-neutral-800 font-mono select-none">
                            <button @click="focusOnMap(branch)" 
                                    class="flex items-center justify-center gap-1.5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-neutral-950 border-r border-neutral-200 dark:border-neutral-800 transition-colors">
                                <Target :size="14" />
                                Enfocar
                            </button>
                            <button @click="copyId(branch.id)"
                                    class="flex items-center justify-center gap-1.5 py-2.5 text-[11px] font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-neutral-950 transition-colors">
                                <Copy :size="14" />
                                Copiar ID
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div v-if="!isLoading && filteredBranches.length === 0"
                 class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-12 text-center select-none shadow-sm">
                <Building :size="40" class="mx-auto text-neutral-300 dark:text-neutral-700 mb-3" />
                <p class="text-xs font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Ningún nodo operativo intersecta los parámetros</p>
                <button v-if="selectedBranchType !== 'all'" @click="selectedBranchType = 'all'" 
                        class="mt-3 text-xs font-black text-neutral-900 dark:text-white font-mono uppercase underline underline-offset-4">
                    Restablecer Filtro
                </button>
            </div>
        </div>
    </AdminLayout>
</template>