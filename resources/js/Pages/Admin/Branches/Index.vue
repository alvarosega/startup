<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapComponent from '@/Components/Base/MapComponent.vue';
import { 
    MapPin, Phone, Edit, Building, Plus, 
    Copy, ExternalLink, Search, Globe, MoreVertical
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array
});

const isLoading = ref(true);

onMounted(() => {
    setTimeout(() => { isLoading.value = false; }, 600);
});

// Calcular centro del mapa
const mapCenter = ref([-16.5000, -68.1500]);
if (props.branches.length > 0 && props.branches[0].latitude) {
    mapCenter.value = [parseFloat(props.branches[0].latitude), parseFloat(props.branches[0].longitude)];
}

const copyId = (id) => {
    navigator.clipboard.writeText(id);
    // Idealmente mostrar un toast aquí
};
</script>

<template>
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center justify-between pt-1 pb-1">
                <div>
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none">
                        Sucursales
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-medium mt-0.5">
                        {{ branches.length }} puntos operativos
                    </p>
                </div>
                <Link :href="route('admin.branches.create')" class="hidden md:flex btn btn-primary btn-sm gap-2 shadow-lg shadow-primary/20">
                    <Plus :size="16" /> <span>Nueva Sucursal</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-32 md:pb-12 relative">
            
            <div class="animate-in fade-in zoom-in duration-500 delay-75">
                <div class="relative rounded-2xl overflow-hidden border border-border shadow-sm group">
                    <div class="absolute top-3 left-3 z-[400] bg-background/90 backdrop-blur px-3 py-1.5 rounded-lg border border-border/50 shadow-sm flex items-center gap-2 pointer-events-none">
                        <Globe :size="14" class="text-primary"/>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Mapa Global</span>
                    </div>
                    <MapComponent 
                        :markers="branches" 
                        :center="mapCenter" 
                        height="280px" 
                        :zoom="12"
                        class="w-full h-full"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                
                <template v-if="isLoading">
                    <div v-for="n in 3" :key="`skel-${n}`" class="bg-card border border-border rounded-2xl p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-muted animate-pulse"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 w-1/2 bg-muted rounded animate-pulse"></div>
                                <div class="h-3 w-1/3 bg-muted rounded animate-pulse"></div>
                            </div>
                        </div>
                        <div class="h-16 bg-muted/30 rounded-xl animate-pulse"></div>
                    </div>
                </template>

                <template v-else>
                    <div v-for="branch in branches" :key="branch.id" 
                         class="group relative bg-card border border-border rounded-2xl overflow-hidden transition-all hover:border-primary/40 hover:shadow-md">
                        
                        <div class="p-5 relative z-10">
                            
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex gap-3 items-center">
                                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0">
                                        <Building :size="18" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-black text-foreground truncate leading-tight">
                                            {{ branch.name }}
                                        </h3>
                                        <p class="text-[10px] font-medium text-muted-foreground mt-0.5 truncate">
                                            {{ branch.city }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div :class="`px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border ${branch.is_active ? 'bg-success/10 text-success border-success/20' : 'bg-destructive/10 text-destructive border-destructive/20'}`">
                                    {{ branch.is_active ? 'Activo' : 'Inactivo' }}
                                </div>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-start gap-2.5 text-xs text-muted-foreground">
                                    <MapPin :size="14" class="mt-0.5 shrink-0" />
                                    <span class="line-clamp-2 leading-relaxed">{{ branch.address || 'Sin dirección registrada' }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-xs text-muted-foreground">
                                    <Phone :size="14" class="shrink-0" />
                                    <span class="font-mono">{{ branch.phone || '---' }}</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-border/50 flex items-center justify-between gap-2">
                                
                                <button @click="copyId(branch.id)" 
                                        class="flex items-center gap-1.5 text-[10px] font-mono text-muted-foreground hover:text-primary transition-colors px-2 py-1 rounded hover:bg-muted"
                                        title="Copiar ID">
                                    <Copy :size="10" /> ID
                                </button>

                                <div class="flex items-center gap-1">
                                    <a v-if="branch.latitude" 
                                       :href="`https://www.google.com/maps/search/?api=1&query=${branch.latitude},${branch.longitude}`" 
                                       target="_blank"
                                       class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors"
                                       title="Ver en Google Maps">
                                        <ExternalLink :size="16" />
                                    </a>

                                    <Link :href="route('admin.branches.edit', branch.id)" 
                                          class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors"
                                          title="Editar Sucursal">
                                        <Edit :size="16" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-if="!isLoading && branches.length === 0" class="col-span-full py-12 text-center border-2 border-dashed border-border rounded-3xl bg-muted/5">
                    <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Search :size="24" class="text-muted-foreground/50" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Sin Sucursales</h3>
                    <p class="text-xs text-muted-foreground mb-4">Comienza registrando tu primer punto de venta.</p>
                </div>

            </div>
        </div>

        <Link :href="route('admin.branches.create')" 
              class="md:hidden fixed bottom-[100px] right-4 z-40 w-14 h-14 bg-primary text-primary-foreground rounded-2xl shadow-[0_8px_30px_rgba(var(--primary),0.4)] flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 border border-white/20 ring-1 ring-black/5">
            <Plus :size="26" stroke-width="3" />
        </Link>

    </AdminLayout>
</template>