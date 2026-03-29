<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Pencil, Trash2, Home, LayoutGrid, Package, 
    Layers, Smartphone, Monitor, MapPin, Sparkles, Box, 
    Image as ImageIcon, AlertCircle, Search
} from 'lucide-vue-next';

const props = defineProps({
    items: Object,
    filters: Object,
    placements: Array,
    branches: Array
});

const creativesList = computed(() => props.items?.data || []);

const filterForm = computed(() => ({
    placement_code: props.filters?.placement_code || '',
    branch_id: props.filters?.branch_id || '',
    is_active: props.filters?.is_active ?? '',
}));

const applyFilters = (key, value) => {
    router.get(route('admin.retail-media.ad-creatives.index'), {
        ...props.filters,
        [key]: value,
    }, { preserveState: true, preserveScroll: true });
};

/**
 * CONFIGURADOR VISUAL DINÁMICO (ESCALABLE)
 * Mapea el CÓDIGO de la tabla a un estilo visual.
 * Si el código no existe en el mapa, devuelve un estilo genérico.
 */
const getVisualConfig = (code) => {
    const configs = {
        'BRAND_HERO':    { color: 'bg-blue-600',    icon: Home },
        'CATEGORY_HERO': { color: 'bg-purple-600',  icon: LayoutGrid },
        'BUNDLE_HERO':   { color: 'bg-orange-600',  icon: Package },
        'SEARCH_TOP':    { color: 'bg-emerald-600', icon: Search }
    };

    return configs[code?.toUpperCase()] || { color: 'bg-slate-600', icon: Layers };
};

const toggleStatus = (item) => {
    router.patch(route('admin.retail-media.ad-creatives.update', item.id), {
        is_active: !item.is_active
    }, { preserveScroll: true });
};

const deleteCreative = (id) => {
    if (confirm('¿Desea eliminar este activo publicitario?')) {
        router.delete(route('admin.retail-media.ad-creatives.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Retail Media - Gestión de Creativos" />

    <AdminLayout>
        <div class="p-8 max-w-[1600px] mx-auto pb-32">
            
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h1 class="text-4xl font-black uppercase italic tracking-tighter leading-none mb-2">Retail Media</h1>
                    <p class="text-xs font-mono text-primary uppercase tracking-[0.3em]">Gestión de Inventario Visual por Placement</p>
                </div>
                <Link :href="route('admin.retail-media.ad-creatives.create')" 
                      class="flex items-center gap-3 bg-primary text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20">
                    <Plus :size="18" stroke-width="3" />
                    Nuevo Despliegue
                </Link>
            </div>

            <div class="flex flex-wrap items-center gap-4 mb-10 bg-card border border-border p-4 rounded-2xl shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase text-muted-foreground">Ubicación:</span>
                    <select @change="applyFilters('placement_code', $event.target.value)" :value="filterForm.placement_code" 
                            class="bg-background border border-border rounded-lg px-3 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todos los Espacios</option>
                        <option v-for="p in placements" :key="p.code" :value="p.code">{{ p.name }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 border-l border-border pl-4">
                    <span class="text-[10px] font-black uppercase text-muted-foreground">Sucursal:</span>
                    <select @change="applyFilters('branch_id', $event.target.value)" :value="filterForm.branch_id"
                            class="bg-background border border-border rounded-lg px-3 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todas las Sedes</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                
                <button @click="router.get(route('admin.retail-media.ad-creatives.index'))" 
                        class="ml-auto text-[10px] font-black uppercase text-primary hover:underline">
                    Resetear Filtros
                </button>
            </div>

            <div class="bg-card border-2 border-border rounded-[2.5rem] overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-muted/30 border-b-2 border-border">
                        <tr>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Activo Visual / Campaña</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Ubicación (Placement)</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Anclaje y Sede</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Target</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center">Estado</th>
                            <th class="px-6 py-5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-border">
                        <tr v-for="item in creativesList" :key="item.id" class="group hover:bg-muted/20 transition-all">
                            
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative w-32 h-16 bg-muted rounded-xl overflow-hidden border-2 border-border">
                                        <img v-if="item.image_desktop_url" :src="item.image_desktop_url" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-[8px] uppercase font-bold opacity-20">No Image</div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase italic leading-none mb-1">{{ item.name }}</p>
                                        <p class="text-[9px] font-mono text-muted-foreground">{{ item.campaign?.name || 'Campaña Interna' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6">
                                <div :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[9px] font-black uppercase text-white shadow-sm', getVisualConfig(item.placement?.code).color]">
                                    <component :is="getVisualConfig(item.placement?.code).icon" :size="12" stroke-width="3" />
                                    {{ item.placement?.name || 'Sin Definir' }}
                                </div>
                            </td>

                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-muted-foreground">
                                        <MapPin :size="12" /> {{ item.branch?.name || 'Sede Global' }}
                                    </div>
                                    <div v-if="item.category" class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-md text-[9px] font-black uppercase border border-purple-200 w-fit">
                                        Pasillo: {{ item.category?.name }}
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-muted rounded-xl border border-border">
                                        <component :is="item.target?.type === 'bundle' ? Package : Layers" :size="16" class="text-muted-foreground" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase leading-tight">{{ item.target?.name || 'N/A' }}</p>
                                        <p class="text-[8px] font-bold text-primary/60 uppercase tracking-tighter mt-0.5">{{ item.action_type }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6 text-center">
                                <button @click="toggleStatus(item)"
                                        :class="item.is_active ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-500'" 
                                        class="px-5 py-1.5 rounded-full text-[9px] font-black uppercase transition-all active:scale-90 shadow-sm">
                                    {{ item.is_active ? 'Activo' : 'Pausado' }}
                                </button>
                            </td>

                            <td class="px-6 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="route('admin.retail-media.ad-creatives.edit', item.id)" class="p-2 bg-background border border-border rounded-lg hover:border-primary transition-colors">
                                        <Pencil :size="16" class="text-muted-foreground" />
                                    </Link>
                                    <button @click="deleteCreative(item.id)" class="p-2 bg-background border border-border rounded-lg hover:border-destructive transition-colors">
                                        <Trash2 :size="16" class="text-muted-foreground" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>