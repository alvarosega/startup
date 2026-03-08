<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Map as MapIcon, Plus, Edit, Trash2, 
    Hash, Palette, Search, MapPin, Tag
} from 'lucide-vue-next';

const props = defineProps({
    zones: Array // [{ id, name, hex_color, svg_id, description, brands_count, is_active }, ...]
});

// --- ESTADO ---
const search = ref('');

// --- NORMALIZACIÓN Y FILTRADO ---
const zonesList = computed(() => props.zones.data || props.zones);

const filteredZones = computed(() => {
    if (!search.value) return zonesList.value;
    const term = search.value.toLowerCase();
    return zonesList.value.filter(z => 
        z.name.toLowerCase().includes(term) || 
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
        { label: 'Zonas activas', value: total, icon: MapIcon },
        { label: 'Marcas vinculadas', value: totalBrands, icon: Tag },
        { label: 'Principal', value: topZone && topZone.brands_count > 0 ? topZone.name : 'N/A', icon: MapPin, isText: true },
    ];
});

// --- ACCIONES ---
const deleteZone = (zone) => {
    if (confirm(`¿Eliminar la zona "${zone.name}"? Se verificará que no tenga marcas asociadas.`)) {
        router.delete(route('admin.market-zones.destroy', zone.id), { preserveScroll: true });
    }
};

const clearSearch = () => {
    search.value = '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Zonas de Mercado" />
        
        <div class="max-w-7xl mx-auto pb-32 md:pb-10 px-4 md:px-0">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">
                        Zonas de Mercado
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Gestión de regiones y disponibilidad de marcas
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Buscador -->
                    <div class="relative flex-1 md:w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar zona o ID..."
                            class="w-full pl-10 pr-10 py-2.5 bg-background border border-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        >
                        <button v-if="search" @click="clearSearch" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-primary transition-colors">
                            ✕
                        </button>
                    </div>
                    <!-- Botón nueva zona (desktop) -->
                    <Link :href="route('admin.market-zones.create')" 
                          class="hidden md:inline-flex bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all items-center gap-2">
                        <Plus :size="18" />
                        <span>Nueva zona</span>
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div v-for="(stat, index) in stats" :key="index" 
                     class="bg-card border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary/10 rounded-lg">
                            <component :is="stat.icon" :size="20" class="text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-bold text-foreground" :class="{ 'text-lg': stat.isText }">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de zonas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div v-for="zone in filteredZones" :key="zone.id" 
                     class="bg-card border border-border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col">
                    
                    <!-- Barra de color superior -->
                    <div class="h-1.5 w-full" :style="{ backgroundColor: zone.hex_color || '#3b82f6' }"></div>

                    <div class="p-5">
                        <!-- Cabecera con icono y nombre -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 bg-primary/5 rounded-lg flex items-center justify-center border border-border">
                                    <MapIcon :size="20" :style="{ color: zone.hex_color || 'var(--primary)' }" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-semibold text-foreground truncate">{{ zone.name }}</h3>
                                    <span class="text-xs" :class="zone.is_active ? 'text-success' : 'text-destructive'">
                                        {{ zone.is_active ? 'Activa' : 'Suspendida' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <p class="text-sm text-muted-foreground line-clamp-2 mb-4">
                            {{ zone.description || 'Sin descripción' }}
                        </p>

                        <!-- Etiquetas (código, color, marcas) -->
                        <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-border">
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-muted/30 rounded-md text-xs">
                                <Hash :size="12" class="text-muted-foreground" />
                                <span>{{ zone.svg_id || 'N/A' }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-muted/30 rounded-md text-xs">
                                <Palette :size="12" class="text-muted-foreground" />
                                <span class="uppercase">{{ zone.hex_color || '#000' }}</span>
                            </div>
                            <div class="ml-auto inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium"
                                 :style="{
                                     backgroundColor: zone.hex_color ? zone.hex_color + '20' : 'var(--primary-20)',
                                     color: zone.hex_color || 'var(--primary)'
                                 }">
                                <Tag :size="12" />
                                <span>{{ zone.brands_count || 0 }} marcas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer acciones -->
                    <div class="grid grid-cols-2 border-t border-border">
                        <Link :href="route('admin.market-zones.edit', zone.id)" 
                              class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors">
                            <Edit :size="14" /> Editar
                        </Link>
                        <button @click="deleteZone(zone)" 
                                class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-colors">
                            <Trash2 :size="14" /> Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estado vacío -->
            <div v-if="filteredZones.length === 0" 
                 class="bg-card border border-dashed border-border rounded-xl p-12 text-center mt-8">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <MapIcon :size="24" class="text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold text-foreground mb-2">No hay zonas</h3>
                <p class="text-sm text-muted-foreground max-w-md mx-auto">
                    {{ search ? 'No se encontraron zonas para tu búsqueda.' : 'Comienza creando la primera zona de mercado.' }}
                </p>
                <button v-if="search" @click="clearSearch" 
                        class="mt-4 text-sm text-primary hover:text-primary/80 transition-colors">
                    Limpiar búsqueda
                </button>
            </div>

            <!-- Botón flotante móvil -->
            <Teleport to="body">
                <Link :href="route('admin.market-zones.create')" 
                      class="md:hidden fixed bottom-6 right-6 z-50 w-14 h-14 bg-primary text-primary-foreground rounded-full shadow-lg flex items-center justify-center hover:bg-primary/90 active:scale-95 transition-all">
                    <Plus :size="24" />
                </Link>
            </Teleport>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Sin estilos personalizados - todo usa clases de Tailwind */
</style>