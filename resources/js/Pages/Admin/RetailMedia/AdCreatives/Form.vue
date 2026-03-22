<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { 
    Save, ArrowLeft, Image as ImageIcon, Smartphone, 
    Monitor, CheckCircle2, Search, Loader2, PackageCheck 
} from 'lucide-vue-next';

const props = defineProps({
    campaigns: Array,
    placements: Array,
    branches: Array,
    // Eliminamos initial_skus para usar el buscador dinámico
    creative: { type: Object, default: null }
});

const isEdit = !!props.creative;

// --- ESTADO DEL BUSCADOR DINÁMICO ---
const skus = ref([]);
const isSearching = ref(false);
const searchQuery = ref(props.creative?.sku?.name || '');
let searchTimeout = null;

const form = useForm({
    id: props.creative?.id || null,
    campaign_id: props.creative?.campaign_id || '',
    placement_id: props.creative?.placement_id || '',
    sku_id: props.creative?.sku_id || '',
    name: props.creative?.name || '',
    sort_order: props.creative?.sort_order || 0,
    is_active: props.creative ? !!props.creative.is_active : true,
    branch_ids: props.creative?.branches?.map(b => b.id) || [],
    image_mobile: null,
    image_desktop: null,
});

// --- LÓGICA DE BÚSQUEDA (DEBOUNCE 300ms) ---
const handleSearch = () => {
    if (searchQuery.value.length < 2) {
        skus.value = [];
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            const { data } = await axios.get(route('admin.retail-media.ad-creatives.search-skus'), {
                params: { q: searchQuery.value }
            });
            skus.value = data;
        } catch (e) {
            console.error("Error en búsqueda de SKUs:", e);
        } finally {
            isSearching.value = false;
        }
    }, 300);
};

const selectSku = (sku) => {
    form.sku_id = sku.id;
    searchQuery.value = sku.name;
    skus.value = [];
};

const submit = () => {
    const url = isEdit 
        ? route('admin.retail-media.ad-creatives.update', props.creative.id) 
        : route('admin.retail-media.ad-creatives.store');

    form.post(url, {
        forceFormData: true,
        onSuccess: () => { /* Notificación de éxito opcional */ },
    });
};
</script>

<template>
    <Head :title="isEdit ? 'Editar Banner' : 'Nuevo Banner - Retail Media'" />

    <AdminLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.retail-media.ad-creatives.index')" 
                          class="p-2 hover:bg-muted rounded-full transition-colors text-muted-foreground">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">
                            {{ isEdit ? 'Editar Creativo' : 'Crear Nuevo Creativo' }}
                        </h1>
                        <p class="text-sm text-muted-foreground">Vincule un producto a un banner con segmentación geográfica.</p>
                    </div>
                </div>
                
                <button @click="submit" :disabled="form.processing"
                        class="flex items-center gap-2 bg-primary text-primary-foreground px-6 py-2 rounded-lg font-medium hover:opacity-90 disabled:opacity-50 transition-all shadow-sm">
                    <Save :size="18" />
                    {{ form.processing ? 'Procesando...' : 'Guardar Creativo' }}
                </button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-4">
                        <h3 class="font-bold border-b border-border pb-2 mb-4">Información del Anuncio</h3>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Nombre Interno</label>
                            <input v-model="form.name" type="text" placeholder="Ej: Promo Coca-Cola Verano"
                                   class="w-full bg-background border border-border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.name" class="text-destructive text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Campaña</label>
                                <select v-model="form.campaign_id" class="w-full bg-background border border-border rounded-lg px-3 py-2 outline-none">
                                    <option value="" disabled>Seleccione campaña</option>
                                    <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Espacio (Placement)</label>
                                <select v-model="form.placement_id" class="w-full bg-background border border-border rounded-lg px-3 py-2 outline-none">
                                    <option value="" disabled>Seleccione ubicación</option>
                                    <option v-for="p in placements" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                                </select>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium mb-1">Producto Vinculado (SKU)</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-2.5 text-muted-foreground" :size="18" />
                                <input 
                                    v-model="searchQuery" 
                                    @input="handleSearch"
                                    type="text" 
                                    placeholder="Buscar por nombre o código..."
                                    class="w-full bg-background border border-border rounded-lg pl-10 pr-10 py-2 outline-none focus:ring-2 focus:ring-primary/20"
                                />
                                <Loader2 v-if="isSearching" class="absolute right-3 top-2.5 animate-spin text-primary" :size="18" />
                            </div>

                            <div v-if="skus.length > 0" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <div v-for="sku in skus" :key="sku.id" @click="selectSku(sku)"
                                     class="p-3 hover:bg-muted cursor-pointer flex items-center justify-between border-b border-border last:border-0">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold">{{ sku.name }}</span>
                                        <span class="text-[10px] text-muted-foreground font-mono uppercase">{{ sku.code }}</span>
                                    </div>
                                    <PackageCheck :size="16" class="text-muted-foreground" />
                                </div>
                            </div>
                            
                            <div v-if="form.sku_id" class="mt-2 inline-flex items-center gap-2 text-[10px] font-bold text-primary bg-primary/10 px-2 py-1 rounded">
                                <CheckCircle2 :size="12" /> VINCULADO: {{ form.sku_id }}
                            </div>
                            <p v-if="form.errors.sku_id" class="text-destructive text-xs mt-1">{{ form.errors.sku_id }}</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                        <h3 class="font-bold border-b border-border pb-2 mb-4 flex items-center gap-2">
                            <ImageIcon :size="18" /> Piezas Gráficas
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-sm font-medium"><Smartphone :size="16" /> Versión Móvil</label>
                                <div class="border-2 border-dashed border-border rounded-xl p-8 text-center hover:bg-muted/50 transition-all relative group">
                                    <input type="file" @input="form.image_mobile = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_mobile" class="space-y-2">
                                        <p class="text-xs text-muted-foreground">Click para subir WebP/PNG</p>
                                        <p class="text-[10px] text-primary font-bold">1080 x 1080 px</p>
                                    </div>
                                    <div v-else class="text-primary text-xs font-bold flex flex-col items-center gap-2">
                                        <CheckCircle2 :size="24" /> {{ form.image_mobile.name }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-sm font-medium"><Monitor :size="16" /> Versión Escritorio</label>
                                <div class="border-2 border-dashed border-border rounded-xl p-8 text-center hover:bg-muted/50 transition-all relative group">
                                    <input type="file" @input="form.image_desktop = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_desktop" class="space-y-2">
                                        <p class="text-xs text-muted-foreground">Click para subir WebP/PNG</p>
                                        <p class="text-[10px] text-primary font-bold">1920 x 600 px</p>
                                    </div>
                                    <div v-else class="text-primary text-xs font-bold flex flex-col items-center gap-2">
                                        <CheckCircle2 :size="24" /> {{ form.image_desktop.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-4">
                        <h3 class="font-bold text-xs uppercase tracking-widest text-muted-foreground">Publicación</h3>
                        
                        <div class="flex items-center justify-between p-3 bg-muted/30 rounded-lg border border-border">
                            <span class="text-sm font-medium">¿Está Activo?</span>
                            <button type="button" @click="form.is_active = !form.is_active"
                                    :class="form.is_active ? 'bg-primary' : 'bg-muted-foreground/30'"
                                    class="w-11 h-6 rounded-full relative transition-all duration-200">
                                <div :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                     class="absolute top-1 w-4 h-4 bg-white rounded-full transition-transform shadow-sm"></div>
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Orden de Aparición</label>
                            <input v-model="form.sort_order" type="number" 
                                   class="w-full bg-background border border-border rounded-lg px-3 py-2 outline-none" />
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                        <h3 class="font-bold text-xs uppercase tracking-widest text-muted-foreground mb-4">Alcance Geográfico</h3>
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="b in branches" :key="b.id" @click="toggleBranch(b.id)"
                                 class="flex items-center gap-3 p-2 hover:bg-muted/50 rounded-lg cursor-pointer transition-colors border border-transparent"
                                 :class="form.branch_ids.includes(b.id) ? 'bg-primary/5 border-primary/20' : ''">
                                <input type="checkbox" :value="b.id" v-model="form.branch_ids" :id="b.id"
                                       class="rounded border-border text-primary focus:ring-primary/20" />
                                <label :for="b.id" class="text-sm cursor-pointer flex-1">{{ b.name }}</label>
                            </div>
                        </div>
                        <p v-if="form.errors.branch_ids" class="text-destructive text-xs mt-3 font-medium">{{ form.errors.branch_ids }}</p>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--border));
    border-radius: 10px;
}
</style>