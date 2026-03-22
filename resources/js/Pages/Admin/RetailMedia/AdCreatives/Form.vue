<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { 
    Save, ArrowLeft, Image as ImageIcon, Smartphone, 
    Monitor, CheckCircle2, Search, Loader2, PackageCheck,
    Layers, Tag, MapPin, LayoutGrid, X
} from 'lucide-vue-next';

const props = defineProps({
    campaigns: Array,
    placements: Array,
    branches: Array,
    categories: Array, // Prop inyectada por el controlador
    creative: { type: Object, default: null }
});

const isEdit = !!props.creative;
const creativeData = computed(() => props.creative?.data || props.creative);

// --- ESTADO DEL BUSCADOR ---
const targets = ref([]);
const isSearching = ref(false);
const searchQuery = ref(creativeData.value?.target?.name || '');
let searchTimeout = null;

// --- FORMULARIO UNIFICADO ---
const form = useForm({
    id: creativeData.value?.id || null,
    campaign_id: creativeData.value?.campaign?.id || '',
    placement_id: creativeData.value?.placement_id || '', // Asumiendo ID directo desde BD
    branch_id: creativeData.value?.branch?.id || '', // Anclaje Único
    category_id: creativeData.value?.category?.id || '', // Nuevo Anclaje Opcional
    target_type: creativeData.value?.target?.type || 'sku', // 'sku' o 'bundle'
    target_id: creativeData.value?.target?.id || '',
    name: creativeData.value?.name || '',
    action_type: creativeData.value?.action_type || 'ADD_TO_CART',
    sort_order: creativeData.value?.sort_order || 0,
    is_active: isEdit ? !!creativeData.value.is_active : true,
    image_mobile: null,
    image_desktop: null,
});

const handleSearch = () => {
    if (searchQuery.value.length < 2) {
        targets.value = [];
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            // Reutilizamos el buscador de SKUs o Bundles según el tipo
            const endpoint = form.target_type === 'sku' 
                ? route('admin.retail-media.ad-creatives.search-skus')
                : route('admin.bundles.index'); // O un buscador específico de bundles si lo tienes

            const { data } = await axios.get(endpoint, {
                params: { q: searchQuery.value }
            });
            targets.value = data;
        } catch (e) {
            console.error("Fallo en telemetría de búsqueda:", e);
        } finally {
            isSearching.value = false;
        }
    }, 300);
};

const selectTarget = (target) => {
    form.target_id = target.id;
    searchQuery.value = target.name;
    targets.value = [];
};

const submit = () => {
    const url = isEdit 
        ? route('admin.retail-media.ad-creatives.update', creativeData.value.id) 
        : route('admin.retail-media.ad-creatives.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="isEdit ? 'Editar Banner' : 'Nuevo Banner - Retail Media'" />

    <AdminLayout>
        <div class="p-6 max-w-6xl mx-auto pb-24">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.retail-media.ad-creatives.index')" class="p-2 hover:bg-muted rounded-full text-muted-foreground">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">{{ isEdit ? 'Actualizar Creativo' : 'Nuevo Creativo' }}</h1>
                        <p class="text-xs font-mono text-primary uppercase tracking-tighter">Silo: Retail Media // Sucursal: {{ form.branch_id || 'PENDIENTE' }}</p>
                    </div>
                </div>
                
                <button @click="submit" :disabled="form.processing"
                        class="bg-primary text-primary-foreground px-8 py-2 rounded-lg font-bold hover:opacity-90 disabled:opacity-50 transition-all shadow-lg flex items-center gap-2">
                    <Save :size="18" />
                    {{ isEdit ? 'SINCRONIZAR' : 'DESPLEGAR' }}
                </button>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest border-b border-border pb-3">
                            <Tag :size="14" /> Datos de Identidad
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Nombre del Creativo</label>
                                <input v-model="form.name" type="text" class="w-full bg-background border border-border rounded-lg px-4 py-2.5 outline-none focus:ring-2 focus:ring-primary/20 font-medium" placeholder="Ej: BANNER_VERANO_2026" />
                                <p v-if="form.errors.name" class="text-destructive text-[10px] mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Campaña Madre</label>
                                <select v-model="form.campaign_id" class="w-full bg-background border border-border rounded-lg px-3 py-2.5 outline-none">
                                    <option value="" disabled>Seleccionar Campaña</option>
                                    <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Ubicación (Placement)</label>
                                <select v-model="form.placement_id" class="w-full bg-background border border-border rounded-lg px-3 py-2.5 outline-none">
                                    <option value="" disabled>Seleccionar Espacio</option>
                                    <option v-for="p in placements" :key="p.id" :value="p.id">{{ p.name }} [{{ p.code }}]</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-border pb-3">
                            <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest">
                                <PackageCheck :size="14" /> Destino del Click (Target)
                            </div>
                            <div class="flex bg-muted rounded-md p-1">
                                <button type="button" @click="form.target_type = 'sku'; form.target_id = ''; searchQuery = ''"
                                        :class="form.target_type === 'sku' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground'"
                                        class="px-3 py-1 text-[10px] font-bold rounded transition-all">SKU</button>
                                <button type="button" @click="form.target_type = 'bundle'; form.target_id = ''; searchQuery = ''"
                                        :class="form.target_type === 'bundle' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground'"
                                        class="px-3 py-1 text-[10px] font-bold rounded transition-all">BUNDLE</button>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="relative">
                                <Search class="absolute left-3 top-3 text-muted-foreground" :size="18" />
                                <input v-model="searchQuery" @input="handleSearch" type="text" 
                                       :placeholder="`Buscar ${form.target_type.toUpperCase()}...`"
                                       class="w-full bg-background border border-border rounded-lg pl-10 pr-10 py-3 outline-none focus:ring-2 focus:ring-primary/20 font-bold" />
                                <Loader2 v-if="isSearching" class="absolute right-3 top-3 animate-spin text-primary" :size="20" />
                                <button v-if="form.target_id" @click="form.target_id = ''; searchQuery = ''" type="button" class="absolute right-3 top-3 text-muted-foreground hover:text-destructive">
                                    <X :size="20" />
                                </button>
                            </div>

                            <div v-if="targets.length > 0" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                                <div v-for="t in targets" :key="t.id" @click="selectTarget(t)"
                                     class="p-4 hover:bg-muted cursor-pointer flex items-center justify-between border-b border-border last:border-0 transition-colors">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black">{{ t.name }}</span>
                                        <span class="text-[10px] font-mono text-primary uppercase">{{ t.code || t.id.substring(0,8) }}</span>
                                    </div>
                                    <div class="bg-primary/10 text-primary p-2 rounded-lg"><CheckCircle2 :size="16" /></div>
                                </div>
                            </div>
                            
                            <p v-if="form.errors.target_id" class="text-destructive text-[10px] mt-2 font-bold uppercase italic">// Error: {{ form.errors.target_id }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Acción al Click</label>
                                <select v-model="form.action_type" class="w-full bg-background border border-border rounded-lg px-3 py-2.5 outline-none font-bold">
                                    <option value="ADD_TO_CART">🛒 AÑADIR AL CARRITO</option>
                                    <option value="NAVIGATE">🔗 NAVEGAR AL PRODUCTO</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Prioridad (Sort)</label>
                                <input v-model="form.sort_order" type="number" class="w-full bg-background border border-border rounded-lg px-3 py-2 outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest border-b border-border pb-3">
                            <ImageIcon :size="14" /> Activos Gráficos
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-[10px] font-bold uppercase text-muted-foreground"><Smartphone :size="14" /> Resolución Móvil (1:1)</label>
                                <div class="border-2 border-dashed border-border rounded-xl p-6 text-center hover:bg-muted/50 transition-all relative group h-40 flex flex-col justify-center items-center">
                                    <input type="file" @input="form.image_mobile = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_mobile" class="space-y-1">
                                        <ImageIcon class="mx-auto text-muted-foreground/40" :size="32" />
                                        <p class="text-[10px] text-muted-foreground font-mono">1080x1080 WEBP</p>
                                    </div>
                                    <div v-else class="text-primary text-[10px] font-bold animate-pulse"><CheckCircle2 class="mx-auto" :size="24" /> {{ form.image_mobile.name }}</div>
                                </div>
                                <p v-if="creativeData?.image_mobile_url && !form.image_mobile" class="text-[9px] text-center text-primary underline">Ver imagen actual</p>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-2 text-[10px] font-bold uppercase text-muted-foreground"><Monitor :size="14" /> Resolución Desktop (Wide)</label>
                                <div class="border-2 border-dashed border-border rounded-xl p-6 text-center hover:bg-muted/50 transition-all relative h-40 flex flex-col justify-center items-center">
                                    <input type="file" @input="form.image_desktop = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_desktop" class="space-y-1">
                                        <ImageIcon class="mx-auto text-muted-foreground/40" :size="32" />
                                        <p class="text-[10px] text-muted-foreground font-mono">1920x600 WEBP</p>
                                    </div>
                                    <div v-else class="text-primary text-[10px] font-bold animate-pulse"><CheckCircle2 class="mx-auto" :size="24" /> {{ form.image_desktop.name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest">
                            <MapPin :size="14" /> Segmentación Geográfica
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Sucursal Destino</label>
                            <select v-model="form.branch_id" class="w-full bg-background border border-border rounded-lg px-3 py-2.5 outline-none font-bold">
                                <option value="" disabled>Seleccionar Sucursal</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <p v-if="form.errors.branch_id" class="text-destructive text-[10px] mt-1 italic font-bold">// {{ form.errors.branch_id }}</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest">
                            <LayoutGrid :size="14" /> Anclaje Contextual
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-muted-foreground mb-1">Categoría (Opcional)</label>
                            <select v-model="form.category_id" class="w-full bg-background border border-border rounded-lg px-3 py-2.5 outline-none">
                                <option value="">Global (Home / Todo el sitio)</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p class="text-[9px] text-muted-foreground mt-2 italic">Si selecciona una categoría, el banner solo aparecerá dentro de esa sección.</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between p-3 bg-muted/30 rounded-lg border border-border">
                            <span class="text-xs font-bold uppercase">Estado de Publicación</span>
                            <button type="button" @click="form.is_active = !form.is_active"
                                    :class="form.is_active ? 'bg-primary' : 'bg-muted-foreground/30'"
                                    class="w-12 h-6 rounded-full relative transition-all duration-200 shadow-inner">
                                <div :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                     class="absolute top-1 w-4 h-4 bg-white rounded-full transition-transform shadow-sm"></div>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-neon { box-shadow: 0 0 15px hsl(var(--primary) / 0.2); }
.fade-enter-active, .fade-leave-active { transition: all 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>