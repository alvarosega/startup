<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Map as MapIcon, Plus, Edit, Trash2, 
    Layers, Hash, Palette, Search, MapPin 
} from 'lucide-vue-next';

const props = defineProps({
    zones: Array // [{ id, name, hex_color, svg_id, description, categories_count }, ...]
});

// --- ESTADO ---
const search = ref('');

// --- LÓGICA COMPUTADA ---
// Filtrado en cliente (Las zonas suelen ser pocas, no requiere request al server)
const filteredZones = computed(() => {
    if (!search.value) return props.zones;
    const term = search.value.toLowerCase();
    return props.zones.filter(z => 
        z.name.toLowerCase().includes(term) || 
        z.svg_id.toLowerCase().includes(term)
    );
});

// Métricas rápidas
const stats = computed(() => {
    const total = props.zones.length;
    const totalCats = props.zones.reduce((acc, z) => acc + (z.categories_count || 0), 0);
    // Zona con más categorías
    const topZone = [...props.zones].sort((a,b) => b.categories_count - a.categories_count)[0];

    return [
        { label: 'Total Zonas', value: total, icon: MapIcon, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'Categorías Asignadas', value: totalCats, icon: Layers, color: 'text-info', bg: 'bg-info/10' },
        { label: 'Zona Principal', value: topZone ? topZone.name : 'N/A', icon: MapPin, color: 'text-success', bg: 'bg-success/10', isText: true },
    ];
});

// --- ACCIONES ---
const deleteZone = (zone) => {
    if (confirm(`¿Eliminar la zona "${zone.name}"? \n⚠️ Las categorías asociadas perderán su ubicación en el mapa.`)) {
        router.delete(route('admin.market-zones.destroy', zone.id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Zonas de Mercado" />
        
        <div class="pb-32 md:pb-10">

            <div class="mb-6 flex flex-col gap-4 px-4 md:px-0">
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-2xl font-display font-black text-foreground tracking-tight">Mapa Logístico</h1>
                        <p class="text-xs text-muted-foreground">Gestiona las regiones visuales de tu mercado.</p>
                    </div>
                </div>

                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar por nombre o ID SVG..." 
                        class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-sm shadow-sm"
                    >
                </div>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 pb-4 mb-2 -mx-4 px-4 md:mx-0 md:px-0 no-scrollbar touch-pan-x">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="snap-start shrink-0 w-[150px] card !p-3 flex flex-col justify-between h-24 border border-border/60 shadow-sm bg-card">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-black uppercase tracking-wider text-muted-foreground">{{ stat.label }}</span>
                        <div :class="`p-1.5 rounded-full ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="14" />
                        </div>
                    </div>
                    <span class="font-display font-black text-foreground tracking-tight" 
                          :class="stat.isText ? 'text-sm truncate leading-tight mt-1' : 'text-2xl'">
                        {{ stat.value }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 px-4 md:px-0">
                <div v-for="zone in filteredZones" :key="zone.id" 
                     class="card border border-border overflow-hidden hover:shadow-md transition-all duration-300 group animate-in zoom-in-95">
                    
                    <div class="h-2 w-full" :style="{ backgroundColor: zone.hex_color }"></div>

                    <div class="p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-sm border border-border/50 relative overflow-hidden">
                                <div class="absolute inset-0 opacity-20" :style="{ backgroundColor: zone.hex_color }"></div>
                                <MapIcon :size="24" :style="{ color: zone.hex_color }" />
                            </div>

                            <div class="flex gap-1">
                                <Link :href="route('admin.market-zones.edit', zone.id)" 
                                      class="btn btn-ghost btn-sm w-8 h-8 p-0 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground">
                                    <Edit :size="16" />
                                </Link>
                                <button @click="deleteZone(zone)" 
                                        class="btn btn-ghost btn-sm w-8 h-8 p-0 rounded-lg hover:bg-error/10 text-muted-foreground hover:text-error">
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>

                        <h3 class="text-lg font-black text-foreground leading-tight mb-1">{{ zone.name }}</h3>
                        <p class="text-xs text-muted-foreground line-clamp-2 h-8 mb-4">
                            {{ zone.description || 'Sin descripción definida para esta zona.' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2 border-t border-border/50 pt-3">
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-muted/50 border border-border/50">
                                <Hash :size="12" class="text-muted-foreground"/>
                                <span class="text-[10px] font-mono font-bold text-foreground">{{ zone.svg_id }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-muted/50 border border-border/50">
                                <Palette :size="12" class="text-muted-foreground"/>
                                <span class="text-[10px] font-mono font-bold text-foreground uppercase">{{ zone.hex_color }}</span>
                            </div>

                            <div class="ml-auto flex items-center gap-1.5 text-primary bg-primary/5 px-2 py-1 rounded-md border border-primary/10">
                                <Layers :size="12"/>
                                <span class="text-[10px] font-bold">{{ zone.categories_count }} Cats.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredZones.length === 0" class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mb-4">
                    <MapIcon :size="32" class="text-muted-foreground" />
                </div>
                <p class="text-sm font-medium">No se encontraron zonas</p>
                <p class="text-xs text-muted-foreground mt-1">
                    {{ search ? 'Intenta con otro término de búsqueda.' : 'Crea tu primera zona para comenzar.' }}
                </p>
            </div>

            <Link :href="route('admin.market-zones.create')" 
                  class="fixed bottom-24 md:bottom-8 right-4 md:right-8 z-[100] flex items-center justify-center w-14 h-14 rounded-2xl bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(var(--primary),0.4)] hover:scale-110 active:scale-95 transition-all duration-300 group border border-white/20">
                <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                <span class="sr-only">Nueva Zona</span>
            </Link>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Utilidad para ocultar scrollbar en carrusel móvil */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>