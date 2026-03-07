<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Map as MapIcon, Plus, Edit, Trash2, 
    Hash, Palette, Search, MapPin, Tag,
    Cpu, Terminal
} from 'lucide-vue-next';

const props = defineProps({
    zones: Array // [{ id, name, hex_color, svg_id, description, brands_count, is_active }, ...]
});

// --- ESTADO ---
const search = ref('');

// --- LÓGICA COMPUTADA (Rendimiento Extremo) ---

// 1. Extraemos el array real de los datos envueltos por el Resource
const zonesList = computed(() => props.zones.data || props.zones);

// 2. Filtramos sobre el array real
const filteredZones = computed(() => {
    if (!search.value) return zonesList.value;
    const term = search.value.toLowerCase();
    return zonesList.value.filter(z => 
        z.name.toLowerCase().includes(term) || 
        (z.svg_id && z.svg_id.toLowerCase().includes(term)) // Prevención contra svg_id nulo
    );
});

// 3. Calculamos métricas sobre el array real
const stats = computed(() => {
    const list = zonesList.value;
    const total = list.length;
    const totalBrands = list.reduce((acc, z) => acc + (z.brands_count || 0), 0);
    // Ordenamos una copia del array para no mutar el original
    const topZone = [...list].sort((a,b) => (b.brands_count || 0) - (a.brands_count || 0))[0];

    return [
        { label: 'ZONAS_ACTIVAS', value: String(total).padStart(2, '0'), icon: MapIcon, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'MARCAS_VINCULADAS', value: String(totalBrands).padStart(2, '0'), icon: Tag, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
        { label: 'NODO_PRINCIPAL', value: topZone && topZone.brands_count > 0 ? topZone.name : 'N/A', icon: MapPin, color: 'text-warning', bg: 'bg-warning/10', isText: true },
    ];
});
// --- ACCIONES ---
const deleteZone = (zone) => {
    if (confirm(`¿CONFIRMAR ELIMINACIÓN DE NODO // "${zone.name.toUpperCase()}"?\n\n[!] ADVERTENCIA: Esta acción verificará que no existan Marcas asociadas a esta zona antes de proceder.`)) {
        router.delete(route('admin.market-zones.destroy', zone.id), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Zonas de Mercado" />
        
        <div class="max-w-7xl mx-auto pb-32 md:pb-10 px-4 md:px-0">

            <div class="mb-8 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 relative z-10">
                    <div>
                        <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" data-text="MAPA LOGÍSTICO">
                            MAPA LOGÍSTICO
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                            <Cpu :size="12" class="text-primary animate-pulse" />
                            GESTIÓN DE REGIONES Y DISPONIBILIDAD DE MARCAS
                            <Terminal :size="12" class="text-primary animate-pulse" />
                        </p>
                    </div>

                    <div class="relative group/search w-full md:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="> BUSCAR ZONA O ID..." 
                            class="w-full pl-10 pr-4 py-2.5 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase"
                        >
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" class="border border-border/50 p-4 relative group/stat">
                    <div class="flex items-center gap-4">
                        <div :class="`p-3 ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="20" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[8px] font-mono font-bold uppercase text-muted-foreground">{{ stat.label }}</p>
                            <p class="font-mono font-black text-foreground mt-1 truncate" :class="stat.isText ? 'text-lg' : 'text-2xl'">
                                {{ stat.value }}
                            </p>
                        </div>
                    </div>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div v-for="zone in filteredZones" :key="zone.id" 
                     class="border border-border/50 bg-background hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card flex flex-col">
                    
                    <div class="h-1 w-full" :style="{ backgroundColor: zone.hex_color }"></div>

                    <span class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary/30"></span>
                    <span class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary/30"></span>
                    <span class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-primary/30"></span>

                    <div class="p-5 flex-1 flex flex-col relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 border border-primary/20 bg-background flex items-center justify-center shrink-0 relative overflow-hidden group-hover/card:border-primary/50 transition-colors">
                                    <div class="absolute inset-0 opacity-10" :style="{ backgroundColor: zone.hex_color }"></div>
                                    <MapIcon :size="20" :style="{ color: zone.hex_color }" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-mono font-bold text-foreground leading-tight truncate group-hover/card:text-primary transition-colors">
                                        {{ zone.name }}
                                    </h3>
                                    <span class="text-[8px] font-mono" :class="zone.is_active ? 'text-cyan-500' : 'text-destructive'">
                                        [{{ zone.is_active ? 'OPERATIVA' : 'SUSPENDIDA' }}]
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] font-mono text-muted-foreground line-clamp-2 h-8 mb-4">
                            {{ zone.description || 'SIN DESCRIPCIÓN OPERATIVA DEFINIDA.' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2 mt-auto pt-4 border-t border-border/50">
                            <div class="flex items-center gap-1 px-2 py-1 bg-muted/30 border border-border/50">
                                <Hash :size="10" class="text-muted-foreground"/>
                                <span class="text-[8px] font-mono text-foreground">{{ zone.svg_id || 'NULL' }}</span>
                            </div>
                            <div class="flex items-center gap-1 px-2 py-1 bg-muted/30 border border-border/50">
                                <Palette :size="10" class="text-muted-foreground"/>
                                <span class="text-[8px] font-mono text-foreground uppercase">{{ zone.hex_color }}</span>
                            </div>
                            
                            <div class="ml-auto flex items-center gap-1 px-2 py-1 border"
                                 :style="`border-color: ${zone.hex_color}40; color: ${zone.hex_color}; background-color: ${zone.hex_color}10`">
                                <Tag :size="10"/>
                                <span class="text-[8px] font-mono font-bold">{{ zone.brands_count || 0 }} MARCAS</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 border-t border-border/50 divide-x divide-border/50 bg-muted/5 opacity-0 group-hover/card:opacity-100 transition-opacity duration-300">
                        <Link :href="route('admin.market-zones.edit', zone.id)" 
                              class="flex items-center justify-center gap-2 py-2.5 text-[8px] font-mono uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all">
                            <Edit :size="12" /> RECONFIGURAR
                        </Link>
                        <button @click="deleteZone(zone)" 
                                class="flex items-center justify-center gap-2 py-2.5 text-[8px] font-mono uppercase tracking-wider text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-all">
                            <Trash2 :size="12" /> ELIMINAR NODO
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="filteredZones.length === 0" class="border border-dashed border-primary/30 p-12 text-center mt-8">
                <div class="w-16 h-16 mx-auto mb-4 border-2 border-dashed border-primary/30 flex items-center justify-center bg-primary/5">
                    <MapIcon :size="24" class="text-primary/50" />
                </div>
                <h3 class="text-sm font-mono font-bold text-foreground">SIN RESULTADOS</h3>
                <p class="text-[10px] font-mono text-muted-foreground mt-2 max-w-xs mx-auto uppercase">
                    {{ search ? 'NO SE ENCONTRARON ZONAS QUE COINCIDAN CON LOS CRITERIOS DE BÚSQUEDA.' : 'INICIA LA CONFIGURACIÓN DEL MAPA LOGÍSTICO CREANDO EL PRIMER NODO.' }}
                </p>
            </div>

            <Teleport to="body">
                <Link :href="route('admin.market-zones.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/fab">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/fab:rotate-90 transition-transform duration-500 relative z-10" />
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/fab:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                </Link>
            </Teleport>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Glitch CSS mantenido de tu diseño original */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}
.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}
.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}
.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}
@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}
@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}
@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}
</style>