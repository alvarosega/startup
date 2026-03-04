<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapComponent from '@/Components/Base/MapComponent.vue';
import { 
    MapPin, Phone, Edit, Building, Plus, 
    Copy, ExternalLink, Search, Globe, 
    Zap, DollarSign, Target, ShieldCheck, ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    auth: Object // Para verificar permisos super_admin
});

const isLoading = ref(true);
const mapRef = ref(null);
const mapCenter = ref([-16.5000, -68.1500]);
const mapZoom = ref(12);

onMounted(() => {
    setTimeout(() => { isLoading.value = false; }, 600);
    // Posicionar mapa en la sucursal por defecto si existe
    const defaultBranch = props.branches?.find(b => b.is_default);
    if (defaultBranch) {
        focusOnMap(defaultBranch);
    }
});

const focusOnMap = (branch) => {
    if (branch.latitude && branch.longitude) {
        mapCenter.value = [parseFloat(branch.latitude), parseFloat(branch.longitude)];
        mapZoom.value = 16;
        // Scroll suave al mapa en móviles
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const copyId = (id) => {
    navigator.clipboard.writeText(id);
    // Trigger toast logic here
};

// Formateador de moneda para consistencia financiera
const fmt = (val) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(val);

</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="pt-0 pb-2 border-b border-border/50 flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_8px_hsl(var(--primary)/0.6)] leading-none">
                        Nodos Operativos
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-mono font-bold tracking-widest uppercase mt-1">
                        {{ branches?.length || 0 }} Unidades en Red
                    </p>
                </div>
                <Link :href="route('admin.branches.create')" class="btn btn-primary btn-sm gap-2 shadow-neon-primary font-black uppercase tracking-tighter">
                    <Plus :size="16" /> Inicializar Nodo
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-32 md:pb-12 relative mt-4">
            
            <div class="animate-in fade-in zoom-in duration-500">
                <div class="relative overflow-hidden border border-primary/50 shadow-[0_0_15px_hsl(var(--primary)/0.1)] group bg-background">
                    <div class="absolute top-0 left-0 z-[400] bg-background border-b border-r border-primary/50 px-3 py-1.5 flex items-center gap-2 pointer-events-none">
                        <Zap :size="14" class="text-primary icon-glow animate-pulse"/>
                        <span class="text-[10px] text-primary font-mono font-bold uppercase tracking-widest">Estado del Geofencing</span>
                    </div>
                    
                    <MapComponent 
                        ref="mapRef"
                        :markers="branches" 
                        :center="mapCenter" 
                        :zoom="mapZoom"
                        height="320px" 
                        class="w-full h-full opacity-90 group-hover:opacity-100 transition-opacity"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 font-mono">
                
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skel-${n}`" class="h-[400px] bg-background border border-primary/20 p-6 animate-pulse"></div>
                </template>

                <template v-else>
                    <div v-for="branch in branches" :key="branch.id" 
                        class="group relative bg-background border transition-all duration-300 flex flex-col"
                        :class="branch.is_default ? 'border-primary shadow-neon-primary z-10' : 'border-border hover:border-primary/50'">
                        
                        <div class="p-5 border-b border-border/50 relative overflow-hidden">
                            <div v-if="branch.is_default" class="absolute top-0 right-0 bg-primary text-background text-[8px] font-black px-2 py-0.5 uppercase tracking-tighter z-10">
                                Master Node
                            </div>
                            
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-black text-foreground uppercase tracking-tighter truncate w-3/4">
                                    {{ branch.name }}
                                </h3>
                                <div class="flex gap-2">
                                    <Link :href="route('admin.branches.edit', branch.id)" class="text-muted-foreground hover:text-primary transition-colors">
                                        <Edit :size="18" />
                                    </Link>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-primary">{{ branch.city }} // {{ branch.is_active ? 'ON-LINE' : 'OFF-LINE' }}</span>
                        </div>

                        <div class="p-5 flex-1 space-y-4 bg-scanlines text-[11px]">
                            <div class="grid grid-cols-2 gap-4 border-b border-border/30 pb-4">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block text-[9px] uppercase">Base Delivery</span>
                                    <span class="text-foreground font-bold">{{ fmt(branch.delivery_base_fee) }}</span>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-muted-foreground block text-[9px] uppercase">Surge Factor</span>
                                    <span :class="branch.surge_multiplier > 1 ? 'text-primary font-black' : 'text-foreground'">
                                        x{{ branch.surge_multiplier }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 border-b border-border/30 pb-4">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block text-[9px] uppercase">Pedido Mínimo</span>
                                    <span class="text-foreground font-bold">{{ fmt(branch.min_order_amount) }}</span>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-muted-foreground block text-[9px] uppercase">Service Fee</span>
                                    <span class="text-foreground font-bold">{{ branch.base_service_fee_percentage }}%</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 text-muted-foreground hover:text-foreground transition-colors cursor-default">
                                <MapPin :size="14" class="shrink-0 mt-0.5" />
                                <span class="uppercase text-[10px] leading-tight line-clamp-2">{{ branch.address }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 border-t border-border bg-muted/5 divide-x divide-border">
                            <button @click="focusOnMap(branch)" 
                                    class="flex items-center justify-center gap-2 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-all group/btn">
                                <Target :size="14" class="group-hover/btn:scale-125 transition-transform" /> Radar
                            </button>
                            <button @click="copyId(branch.id)"
                                    class="flex items-center justify-center gap-2 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-all">
                                <Copy :size="14" /> ID_HEX
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>