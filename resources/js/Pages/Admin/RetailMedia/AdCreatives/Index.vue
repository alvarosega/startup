<script setup>
import { computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Pencil, Trash2, Home, LayoutGrid, Package, 
    Layers, Smartphone, Monitor, MapPin, Sparkles, Box, 
    Image as ImageIcon, AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    items: Object,
    filters: Object,
    placements: Array, // <--- Faltaba
    branches: Array    // <--- Faltaba
});
const creativesList = computed(() => props.items.data || []);
const filterForm = computed(() => ({
    placement_code: props.filters.placement_code || '',
    branch_id: props.filters.branch_id || '',
    is_active: props.filters.is_active ?? '',
}));
const applyFilters = (key, value) => {
    router.get(route('admin.retail-media.ad-creatives.index'), {
        ...props.filters,
        [key]: value,
    }, { preserveState: true, preserveScroll: true });
};

const getPlacementMeta = (code) => {
    const c = (code || '').toUpperCase(); 
    if (c.includes('HOME')) return { label: 'Hero Home', color: 'bg-blue-600', text: 'text-blue-600', border: 'border-blue-500/20', icon: Home };
    if (c.includes('CATEGORY') || c.includes('CAT')) return { label: 'Pasillo Categoría', color: 'bg-purple-600', text: 'text-purple-600', border: 'border-purple-500/20', icon: LayoutGrid };
    if (c.includes('BUNDLE') || c.includes('PACK')) return { label: 'Promo de Packs', color: 'bg-orange-600', text: 'text-orange-600', border: 'border-orange-500/20', icon: Package };
    return { label: 'General', color: 'bg-slate-600', text: 'text-slate-600', border: 'border-slate-500/20', icon: Layers };
};

const stats = computed(() => {
    const list = creativesList.value;
    return {
        home: list.filter(i => i.placement?.code?.toUpperCase().includes('HOME')).length,
        category: list.filter(i => i.placement?.code?.toUpperCase().includes('CATEGORY')).length,
        bundle: list.filter(i => i.placement?.code?.toUpperCase().includes('BUNDLE')).length,
    }
});
/**
 * ACCIONES DE PERSISTENCIA
 */
const toggleStatus = (item) => {
    router.patch(route('admin.retail-media.ad-creatives.update', item.id), {
        is_active: !item.is_active
    }, { preserveScroll: true });
};

const deleteCreative = (id) => {
    if (confirm('¿Desea eliminar este activo publicitario de forma permanente?')) {
        router.delete(route('admin.retail-media.ad-creatives.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Retail Media - Inteligencia Visual" />

    <AdminLayout>
        <div class="p-8 max-w-[1600px] mx-auto pb-32">
            
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h1 class="text-4xl font-black uppercase italic tracking-tighter leading-none mb-2">Retail Media</h1>
                    <p class="text-xs font-mono text-primary uppercase tracking-[0.3em]">Control de Creativos y Anclajes Contextuales</p>
                </div>
                <Link :href="route('admin.retail-media.ad-creatives.create')" 
                      class="flex items-center gap-3 bg-primary text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20">
                    <Plus :size="18" stroke-width="3" />
                    Nuevo Despliegue
                </Link>
            </div>
            <div class="flex flex-wrap items-center gap-4 mb-6 bg-card border border-border p-4 rounded-2xl shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase text-muted-foreground">Ubicación:</span>
                    <select @change="applyFilters('placement_code', $event.target.value)" :value="filterForm.placement_code" 
                            class="bg-background border border-border rounded-lg px-3 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todos los Placements</option>
                        <option v-for="p in placements" :key="p.code" :value="p.code">{{ p.name }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 border-l border-border pl-4">
                    <span class="text-[10px] font-black uppercase text-muted-foreground">Sucursal:</span>
                    <select @change="applyFilters('branch_id', $event.target.value)" :value="filterForm.branch_id"
                            class="bg-background border border-border rounded-lg px-3 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Todas las Sucursales</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                
                <button @click="router.get(route('admin.retail-media.ad-creatives.index'))" 
                        class="ml-auto text-[10px] font-black uppercase text-primary hover:underline">
                    Limpiar Filtros
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div v-for="type in ['HOME', 'CATEGORY', 'BUNDLE']" :key="type" 
                     :class="['bg-card border-2 p-6 rounded-[2rem] flex items-center gap-5 shadow-sm transition-all', getPlacementMeta(type).border]">
                    <div :class="['w-14 h-14 text-white rounded-2xl flex items-center justify-center shadow-lg', getPlacementMeta(type).color]">
                        <component :is="getPlacementMeta(type).icon" :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest">{{ getPlacementMeta(type).label }}</p>
                        <p class="text-3xl font-black italic tracking-tighter">
                            {{ stats[type.toLowerCase()] }}
                            <span class="text-xs not-italic font-medium opacity-40">Activos</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-card border-2 border-border rounded-[2.5rem] overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-muted/30 border-b-2 border-border">
                        <tr>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Activo Visual / ID</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Ubicación y Anclaje</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">Target (Destino del Click)</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center">Estado</th>
                            <th class="px-6 py-5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-border">
                        <tr v-for="item in creativesList" :key="item.id" class="group hover:bg-muted/20 transition-all">
                            
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative w-32 h-16 bg-muted rounded-xl overflow-hidden border-2 border-border group-hover:border-primary/40 transition-colors">
                                        <img v-if="item.image_desktop_url" :src="item.image_desktop_url" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground/20 italic text-[8px] uppercase font-bold">Sin Imagen</div>
                                        <div class="absolute bottom-1 right-1 flex gap-1">
                                            <div class="p-1 bg-black/60 rounded-md text-white"><Monitor :size="8" /></div>
                                            <div class="p-1 bg-black/60 rounded-md text-white"><Smartphone :size="8" /></div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase leading-tight italic">{{ item.name }}</p>
                                        <p class="text-[9px] font-mono text-muted-foreground mt-1">{{ item.campaign.name }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-2">
                                    <div :class="['inline-flex items-center gap-2 px-3 py-1 rounded-full border text-[9px] font-black uppercase w-fit text-white shadow-sm', getPlacementMeta(item.placement.code).color]">
                                        <component :is="getPlacementMeta(item.placement.code).icon" :size="12" stroke-width="3" />
                                        {{ getPlacementMeta(item.placement.code).label }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-muted-foreground flex items-center gap-1"><MapPin :size="12" /> {{ item.branch.name }}</span>
                                        <span v-if="item.category" class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-md text-[9px] font-black uppercase border border-purple-200 animate-in fade-in">
                                            Pasillo: {{ item.category.name }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-muted rounded-2xl border border-border group-hover:border-primary/20 transition-colors">
                                        <template v-if="item.target.type === 'bundle'">
                                            <Sparkles v-if="item.target.bundle_subtype === 'template'" :size="18" class="text-orange-500 animate-pulse" />
                                            <Box v-else :size="18" class="text-orange-600" />
                                        </template>
                                        <Layers v-else :size="18" class="text-blue-500" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase italic">{{ item.target.name }}</span>
                                            <span v-if="item.target.type === 'bundle'" 
                                                  :class="item.target.bundle_subtype === 'template' ? 'text-orange-500 border-orange-200 bg-orange-50' : 'text-slate-500 border-slate-200 bg-slate-50'"
                                                  class="text-[8px] font-black border px-1.5 py-0.5 rounded uppercase">
                                                {{ item.target.bundle_subtype === 'template' ? 'Editable' : 'Fijo' }}
                                            </span>
                                        </div>
                                        <p class="text-[9px] font-bold text-muted-foreground mt-1 uppercase tracking-widest">{{ item.action_type }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-6 text-center">
                                <button @click="toggleStatus(item)"
                                        :class="item.is_active ? 'bg-green-500 text-white shadow-green-500/20' : 'bg-slate-200 text-slate-500'" 
                                        class="px-5 py-1.5 rounded-full text-[9px] font-black uppercase shadow-lg transition-all active:scale-90">
                                    {{ item.is_active ? 'Online' : 'Offline' }}
                                </button>
                            </td>

                            <td class="px-6 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="route('admin.retail-media.ad-creatives.edit', item.id)" 
                                          class="p-2.5 bg-background border border-border rounded-xl text-muted-foreground hover:text-primary hover:border-primary transition-all">
                                        <Pencil :size="18"/>
                                    </Link>
                                    <button @click="deleteCreative(item.id)"
                                            class="p-2.5 bg-background border border-border rounded-xl text-muted-foreground hover:text-destructive hover:border-destructive transition-all">
                                        <Trash2 :size="18"/>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="creativesList.length === 0" class="p-20 text-center">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground/30">
                        <AlertCircle :size="32" />
                    </div>
                    <h3 class="text-lg font-black uppercase italic italic">Silo Retail Media Vacío</h3>
                    <p class="text-xs text-muted-foreground mt-1 uppercase tracking-widest">No se detectaron creativos desplegados para los filtros actuales.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Optimizaciones de Renderizado */
tr { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
tr:hover { transform: translateX(4px); }
</style>