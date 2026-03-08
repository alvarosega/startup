<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapComponent from '@/Components/Base/MapComponent.vue';
import { 
    MapPin, Edit, Building, Plus, 
    Copy, Target, Wifi, WifiOff, 
    DollarSign, Activity, HardDrive, Cpu,
    AlertTriangle, CheckCircle2, GitBranch,
    Server, Clock
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
const selectedBranchType = ref('all'); // 'all', 'active', 'inactive'

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
    // Aquí iría la lógica de toast
};

// Filtrado de sucursales
const filteredBranches = computed(() => {
    if (selectedBranchType.value === 'all') return props.branches;
    if (selectedBranchType.value === 'active') {
        return props.branches.filter(b => b.is_active);
    }
    return props.branches.filter(b => !b.is_active);
});

// Estadísticas
const stats = computed(() => {
    const total = props.branches?.length || 0;
    const active = props.branches?.filter(b => b.is_active).length || 0;
    const inactive = total - active;
    const surgeZones = props.branches?.filter(b => b.surge_multiplier > 1).length || 0;
    
    return { total, active, inactive, surgeZones };
});

const fmt = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

// Estado de red para cada nodo
const getNetworkStatus = (branch) => {
    if (!branch.is_active) return { icon: WifiOff, class: 'text-destructive', label: 'Inactiva' };
    if (branch.surge_multiplier > 1.5) return { icon: Activity, class: 'text-warning', label: 'Alerta surge' };
    return { icon: Wifi, class: 'text-success', label: 'Activa' };
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Sucursales
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión de nodos operativos y cobertura
                    </p>
                </div>
                
                <Link :href="route('admin.branches.create')" 
                      class="bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all inline-flex items-center gap-2">
                    <Plus :size="18" />
                    Nueva sucursal
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-32 md:pb-12">
            
            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 rounded-lg">
                            <Server :size="20" class="text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-success/10 rounded-lg">
                            <Wifi :size="20" class="text-success" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Activas</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.active }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-destructive/10 rounded-lg">
                            <WifiOff :size="20" class="text-destructive" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Inactivas</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.inactive }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-warning/10 rounded-lg">
                            <Activity :size="20" class="text-warning" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Zonas surge</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.surgeZones }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex items-center gap-2 border-b border-border pb-2">
                <button @click="selectedBranchType = 'all'"
                        class="px-3 py-1 text-sm font-medium transition-colors relative"
                        :class="selectedBranchType === 'all' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                    Todas ({{ stats.total }})
                </button>
                <button @click="selectedBranchType = 'active'"
                        class="px-3 py-1 text-sm font-medium transition-colors relative"
                        :class="selectedBranchType === 'active' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                    Activas ({{ stats.active }})
                </button>
                <button @click="selectedBranchType = 'inactive'"
                        class="px-3 py-1 text-sm font-medium transition-colors relative"
                        :class="selectedBranchType === 'inactive' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                    Inactivas ({{ stats.inactive }})
                </button>
                <div class="flex-1"></div>
                <span class="text-xs text-muted-foreground flex items-center gap-1">
                    <Clock :size="14" />
                    {{ new Date().toLocaleTimeString() }}
                </span>
            </div>

            <!-- Mapa -->
            <div class="border border-border rounded-xl overflow-hidden shadow-sm">
                <MapComponent 
                    ref="mapRef"
                    :markers="filteredBranches" 
                    :center="mapCenter" 
                    :zoom="mapZoom"
                    height="320px" 
                    class="w-full h-full"
                />
            </div>

            <!-- Grid de Sucursales -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                
                <!-- Skeletons -->
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skel-${n}`" 
                          class="h-[380px] bg-card border border-border rounded-xl animate-pulse"></div>
                </template>

                <template v-else>
                    <div v-for="branch in filteredBranches" :key="branch.id" 
                         @mouseenter="hoveredBranch = branch.id"
                         @mouseleave="hoveredBranch = null"
                         class="bg-card border border-border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col"
                         :class="{ 'opacity-70': !branch.is_active }">
                        
                        <!-- Badges -->
                        <div class="px-5 pt-4 flex justify-end gap-2">
                            <div v-if="branch.is_default" 
                                 class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full flex items-center gap-1">
                                <GitBranch :size="12" />
                                Principal
                            </div>
                            <div v-if="branch.surge_multiplier > 1"
                                 class="bg-warning/10 text-warning text-xs px-2 py-1 rounded-full flex items-center gap-1">
                                <Activity :size="12" />
                                Surge x{{ branch.surge_multiplier }}
                            </div>
                        </div>

                        <!-- Header -->
                        <div class="px-5 pb-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-foreground truncate max-w-[200px]">
                                        {{ branch.name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <component :is="getNetworkStatus(branch).icon" 
                                                   :size="14" 
                                                   :class="getNetworkStatus(branch).class" />
                                        <span class="text-sm" :class="getNetworkStatus(branch).class">
                                            {{ getNetworkStatus(branch).label }}
                                        </span>
                                    </div>
                                </div>
                                <Link :href="route('admin.branches.edit', branch.id)" 
                                      class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-md transition-colors">
                                    <Edit :size="16" />
                                </Link>
                            </div>
                            <div class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                <MapPin :size="12" class="text-primary/60" />
                                {{ branch.city }} · ID: {{ String(branch.id).slice(-4).toUpperCase() }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-5 py-3 flex-1 space-y-3 bg-muted/5">
                            <!-- Métricas principales -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-muted-foreground block">Base delivery</span>
                                    <span class="text-base font-semibold text-foreground">{{ fmt(branch.delivery_base_fee) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-muted-foreground block">Surge</span>
                                    <span class="text-base font-semibold" :class="branch.surge_multiplier > 1 ? 'text-warning' : 'text-foreground'">
                                        x{{ branch.surge_multiplier }}
                                    </span>
                                </div>
                            </div>

                            <!-- Métricas secundarias -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-muted-foreground block">Pedido mínimo</span>
                                    <span class="text-sm font-medium">{{ fmt(branch.min_order_amount) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-muted-foreground block">Service fee</span>
                                    <span class="text-sm font-medium">{{ branch.base_service_fee_percentage }}%</span>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="flex items-start gap-2 text-sm text-muted-foreground">
                                <MapPin :size="16" class="shrink-0 mt-0.5" />
                                <span class="line-clamp-2">{{ branch.address }}</span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="grid grid-cols-2 border-t border-border">
                            <button @click="focusOnMap(branch)" 
                                    class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Target :size="16" />
                                Enfocar
                            </button>
                            <button @click="copyId(branch.id)"
                                    class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                                <Copy :size="16" />
                                Copiar ID
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Mensaje sin resultados -->
            <div v-if="!isLoading && filteredBranches.length === 0" 
                 class="bg-card border border-border rounded-xl p-12 text-center">
                <Building :size="48" class="mx-auto text-muted-foreground mb-4" />
                <p class="text-lg font-medium text-foreground mb-2">No hay sucursales</p>
                <p class="text-sm text-muted-foreground">
                    {{ selectedBranchType === 'active' ? 'No hay sucursales activas.' : selectedBranchType === 'inactive' ? 'No hay sucursales inactivas.' : 'Crea tu primera sucursal.' }}
                </p>
                <button v-if="selectedBranchType !== 'all'" @click="selectedBranchType = 'all'" 
                        class="mt-4 text-sm text-primary hover:text-primary/80 transition-colors">
                    Ver todas
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Sin estilos personalizados - todo usa clases de Tailwind */
</style>