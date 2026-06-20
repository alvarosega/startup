<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Map as MapIcon, Plus, Edit, Trash2, 
    Hash, Palette, Search, MapPin, Tag,
    Cpu, Terminal, Wifi, WifiOff, Star
} from 'lucide-vue-next';

const props = defineProps({
    zones: Array // [{ id, name, hex_color, svg_id, description, brands_count, is_active }, ...]
});

// --- ESTADO ---
const search = ref('');

// --- NORMALIZACIÓN Y FILTRADO (La Ley 2.0: Unwrapping) ---
const zonesList = computed(() => props.zones?.data || props.zones || []);

const filteredZones = computed(() => {
    if (!search.value) return zonesList.value;
    const term = search.value.toLowerCase();
    return zonesList.value.filter(z => 
        (z.name && z.name.toLowerCase().includes(term)) || 
        (z.svg_id && z.svg_id.toLowerCase().includes(term))
    );
});

// --- ESTADÍSTICAS ---
const stats = computed(() => {
    const list = zonesList.value;
    const total = list.length;
    const totalBrands = list.reduce((acc, z) => acc + (z.brands_count || 0), 0);
    const topZone = [...list].sort((a,b) => (b.brands_count || 0) - (a.brands_count || 0))[0];

    return [
        { label: 'ZONAS_ACTIVAS', value: String(total).padStart(2, '0'), icon: MapIcon, color: 'text-primary', bg: 'bg-primary/10' },
        { label: 'MARCAS_VINCULADAS', value: String(totalBrands).padStart(2, '0'), icon: Tag, color: 'text-cyan-500', bg: 'bg-cyan-500/10' },
        { label: 'ZONA_LÍDER', value: topZone && topZone.brands_count > 0 ? topZone.name.toUpperCase() : 'N/A', icon: MapPin, color: 'text-emerald-500', bg: 'bg-emerald-500/10', isText: true },
    ];
});

// --- ACCIONES ---
const deleteZone = (zone) => {
    if (confirm(`¿CONFIRMAR ELIMINACIÓN // ZONA: "${zone.name.toUpperCase()}"? // SE VERIFICARÁ QUE NO EXISTA VÍNCULO COMERCIAL.`)) {
        router.delete(route('admin.market-zones.destroy', zone.id), { preserveScroll: true });
    }
};

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Radar de Zonas" />
        
        <div class="max-w-7xl mx-auto pb-32 md:pb-10 px-4 md:px-0">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-primary/30 pb-6 relative group/header">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <h1 class="text-3xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_12px_hsl(var(--primary)/0.6)] leading-none" data-text="RADAR DE ZONAS">
                        RADAR DE ZONAS
                    </h1>
                    <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                        <Cpu :size="12" class="text-primary animate-pulse" />
                        GESTIÓN TERRITORIAL Y DISPONIBILIDAD COMERCIAL
                        <Terminal :size="12" class="text-primary animate-pulse" />
                    </p>
                </div>
                
                <div class="relative flex-1 md:w-80 group/search z-10 w-full md:w-auto">
                    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-primary/40 group-focus-within/search:text-primary transition-colors" />
                    <input v-model="search" type="text" placeholder="[ BUSCAR_ZONA_O_ID ]" 
                           class="w-full pl-9 pr-8 py-2.5 bg-background border border-border/50 font-mono text-[10px] focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase text-foreground">
                    <button v-if="search" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">✕</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" class="border border-border/50 p-4 relative group/stat bg-background/50">
                    <div class="flex items-center gap-4">
                        <div :class="`p-3 ${stat.bg} ${stat.color}`">
                            <component :is="stat.icon" :size="20" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[8px] font-mono font-bold uppercase text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-mono font-black text-foreground mt-1 truncate" :class="{ 'text-lg': stat.isText }">{{ stat.value }}</p>
                        </div>
                    </div>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div v-for="zone in filteredZones" :key="zone.id" 
                     class="border border-border/50 bg-background hover:border-primary/30 hover:shadow-neon-primary transition-all duration-500 relative group/card flex flex-col overflow-hidden">
                    
                    <div class="h-1 w-full" :style="{ backgroundColor: zone.hex_color || '#3b82f6' }"></div>

                    <div class="p-5 flex-1 flex flex-col relative z-10">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 border border-primary/20 bg-background flex items-center justify-center shrink-0 relative overflow-hidden">
                                    <MapIcon :size="20" :style="{ color: zone.hex_color || 'var(--primary)' }" class="relative z-10" />
                                    <div class="absolute inset-0 opacity-10" :style="{ backgroundColor: zone.hex_color || 'var(--primary)' }"></div>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-mono font-bold text-foreground truncate uppercase group-hover/card:text-primary transition-colors">{{ zone.name }}</h3>
                                    <span class="text-[8px] font-mono font-black mt-1 flex items-center gap-1" :class="zone.is_active ? 'text-primary' : 'text-destructive'">
                                        <component :is="zone.is_active ? Wifi : WifiOff" :size="10" />
                                        {{ zone.is_active ? 'ONLINE' : 'OFFLINE' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] font-mono text-muted-foreground line-clamp-2 mb-4 h-8 uppercase">
                            {{ zone.description || 'S/D' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2 mt-auto pt-4 border-t border-border/50">
                            <div class="flex items-center gap-1 px-2 py-1 bg-muted/30 border border-border/50">
                                <Hash :size="10" class="text-muted-foreground" />
                                <span class="text-[8px] font-mono text-foreground uppercase">{{ zone.svg_id || 'N/A' }}</span>
                            </div>
                            <div class="flex items-center gap-1 px-2 py-1 bg-muted/30 border border-border/50">
                                <Palette :size="10" class="text-muted-foreground" />
                                <span class="text-[8px] font-mono uppercase" :style="{ color: zone.hex_color || 'var(--primary)' }">{{ zone.hex_color || '#000' }}</span>
                            </div>
                            <div class="ml-auto flex items-center gap-1 px-2 py-1 border border-cyan-500/30 bg-cyan-500/10 text-cyan-500">
                                <Tag :size="10" />
                                <span class="text-[8px] font-mono font-black">{{ zone.brands_count || 0 }} BRANDS</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 border-t border-border/50 bg-background/50 backdrop-blur-sm relative z-10">
                        <Link :href="route('admin.market-zones.edit', zone.id)" 
                              class="flex items-center justify-center gap-2 py-3 text-[10px] font-mono font-bold text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors border-r border-border/50 uppercase">
                            <Edit :size="12" /> EDITAR
                        </Link>
                        <button @click="deleteZone(zone)" 
                                class="flex items-center justify-center gap-2 py-3 text-[10px] font-mono font-bold text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-colors uppercase">
                            <Trash2 :size="12" /> ELIMINAR
                        </button>
                    </div>

                    <div class="absolute inset-0 border-2 border-primary/30 pointer-events-none opacity-0 group-hover/card:opacity-100 transition-opacity"></div>
                </div>
            </div>

            <div v-if="filteredZones.length === 0" class="py-16 border border-dashed border-primary/30 text-center relative bg-primary/5 mt-8">
                <div class="w-16 h-16 border-2 border-dashed border-primary/30 flex items-center justify-center mx-auto mb-4">
                    <MapIcon :size="24" class="text-primary/50" />
                </div>
                <h3 class="text-sm font-mono font-bold text-foreground uppercase mb-1">RADAR VACÍO</h3>
                <p class="text-[10px] font-mono text-muted-foreground uppercase">
                    {{ search ? 'NO SE ENCONTRARON ZONAS CON LOS PARÁMETROS ACTUALES.' : 'INICIALIZA LA BASE DE DATOS CREANDO LA PRIMERA ZONA DE MERCADO.' }}
                </p>
            </div>

            <Teleport to="body">
                <Link :href="route('admin.market-zones.create')" 
                      class="fixed bottom-8 right-8 z-[9999] group/create">
                    <div class="w-14 h-14 bg-primary text-primary-foreground border border-primary-foreground/50 shadow-neon-primary flex items-center justify-center relative overflow-hidden">
                        <Plus :size="24" class="group-hover/create:rotate-90 transition-transform duration-500 relative z-10" />
                        <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/create:translate-y-0 transition-transform duration-500"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                    </div>
                </Link>
            </Teleport>
            
            <div class="mt-12 text-center opacity-30">
                <p class="text-[7px] font-mono text-muted-foreground uppercase tracking-[0.4em]">
                    ZONE_IDX_NODE // v2.0 // TS: {{ new Date().toISOString() }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon-primary { box-shadow: 0 0 15px hsl(var(--primary) / 0.3); }

.glitch-text { position: relative; animation: glitch-skew 4s infinite linear alternate-reverse; }
.glitch-text::before, .glitch-text::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
.glitch-text::before { color: #0ff; z-index: -1; animation: glitch-anim-1 0.4s infinite linear alternate-reverse; }
.glitch-text::after { color: #f0f; z-index: -2; animation: glitch-anim-2 0.4s infinite linear alternate-reverse; }

@keyframes glitch-skew { 0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); } 21% { transform: skew(2deg); } 81% { transform: skew(-2deg); } }
@keyframes glitch-anim-1 { 0% { clip-path: inset(20% 0 30% 0); } 100% { clip-path: inset(40% 0 20% 0); } }
@keyframes glitch-anim-2 { 0% { clip-path: inset(60% 0 10% 0); } 100% { clip-path: inset(30% 0 40% 0); } }
</style>