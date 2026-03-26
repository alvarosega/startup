<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { 
    Save, ArrowLeft, Image as ImageIcon, Smartphone, 
    Monitor, CheckCircle2, Search, Loader2, Package,
    Layers, Tag, MapPin, LayoutGrid, X, MousePointerClick
} from 'lucide-vue-next';

const props = defineProps({
    campaigns: Array,
    placements: Array,
    branches: Array,
    categories: Array,
    creative: { type: Object, default: null }
});

const isEdit = !!props.creative;
const creativeData = computed(() => props.creative?.data || props.creative);

// --- BUSCADOR DE TARGET ---
const targets = ref([]);
const isSearching = ref(false);
const searchQuery = ref(creativeData.value?.target_name || '');
let searchTimeout = null;

const form = useForm({
    id: creativeData.value?.id || null,
    campaign_id: creativeData.value?.campaign_id || '',
    placement_id: creativeData.value?.placement_id || '',
    branch_id: creativeData.value?.branch_id || '', 
    category_id: creativeData.value?.category_id || '', 
    target_type: creativeData.value?.target_type || 'sku', 
    target_id: creativeData.value?.target_id || '',
    name: creativeData.value?.name || '',
    action_type: creativeData.value?.action_type || 'ADD_TO_CART',
    sort_order: creativeData.value?.sort_order || 0,
    is_active: isEdit ? !!creativeData.value.is_active : true,
    image_mobile: null,
    image_desktop: null,
});

const handleSearch = () => {
    if (searchQuery.value.length < 2) return;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            // Decidimos endpoint basado en target_type
            const url = form.target_type === 'sku' 
                ? route('admin.retail-media.ad-creatives.search-skus')
                : route('admin.retail-media.ad-creatives.search-bundles');

            const { data } = await axios.get(url, { params: { q: searchQuery.value } });
            targets.value = data;
        } catch (e) { console.error(e); } 
        finally { isSearching.value = false; }
    }, 300);
};

const selectTarget = (t) => {
    form.target_id = t.id;
    searchQuery.value = t.name;
    targets.value = [];
};

const submit = () => {
    const url = isEdit 
        ? route('admin.retail-media.ad-creatives.update', creativeData.value.id) 
        : route('admin.retail-media.ad-creatives.store');

    form.post(url, { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Editar Banner' : 'Nuevo Banner'" />
    <AdminLayout>
        <div class="p-8 max-w-7xl mx-auto pb-32">
            
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-5">
                    <Link :href="route('admin.retail-media.ad-creatives.index')" class="p-3 bg-card border border-border rounded-2xl hover:bg-muted transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-black uppercase italic tracking-tighter">{{ isEdit ? 'Editar Creativo' : 'Desplegar Banner' }}</h1>
                        <p class="text-[10px] font-mono text-primary uppercase tracking-widest">Silo: Retail Media // Intelligence System</p>
                    </div>
                </div>
                <button @click="submit" :disabled="form.processing" 
                        class="bg-primary text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-3 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/20">
                    <Save :size="18" stroke-width="3" />
                    {{ isEdit ? 'Sincronizar' : 'Publicar' }}
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2 bg-primary/10 text-primary rounded-xl"><Tag :size="18"/></div>
                            <h3 class="text-sm font-black uppercase tracking-widest">Configuración Base</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2 ml-1">Nombre Identificador</label>
                                <input v-model="form.name" type="text" class="w-full bg-background border-2 border-border rounded-2xl px-5 py-4 outline-none focus:border-primary transition-colors font-bold" placeholder="Ej: BANNER_HERO_VERANO_BOLIVIA" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2 ml-1">Campaña Vinculada</label>
                                <select v-model="form.campaign_id" class="w-full bg-background border-2 border-border rounded-2xl px-4 py-4 outline-none focus:border-primary font-bold appearance-none">
                                    <option value="">Seleccionar Campaña...</option>
                                    <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2 ml-1">Ubicación (Placement)</label>
                                <select v-model="form.placement_id" class="w-full bg-background border-2 border-border rounded-2xl px-4 py-4 outline-none focus:border-primary font-bold appearance-none">
                                    <option value="">Seleccionar Espacio...</option>
                                    <option v-for="p in placements" :key="p.id" :value="p.id">{{ p.name }} [{{ p.code }}]</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border-2 border-primary/20 rounded-[2.5rem] p-8 shadow-lg relative overflow-hidden">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-primary text-white rounded-xl"><MousePointerClick :size="18"/></div>
                                <h3 class="text-sm font-black uppercase tracking-widest">Destino del Click</h3>
                            </div>
                            <div class="flex bg-muted rounded-xl p-1.5 border border-border">
                                <button type="button" @click="form.target_type = 'sku'; form.target_id = ''; searchQuery = ''"
                                        :class="form.target_type === 'sku' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground'"
                                        class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all">Producto</button>
                                <button type="button" @click="form.target_type = 'bundle'; form.target_id = ''; searchQuery = ''"
                                        :class="form.target_type === 'bundle' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground'"
                                        class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all">Pack</button>
                            </div>
                        </div>

                        <div class="relative">
                            <Search class="absolute left-5 top-5 text-muted-foreground" :size="20" />
                            <input v-model="searchQuery" @input="handleSearch" type="text" 
                                   :placeholder="`Buscar ${form.target_type === 'sku' ? 'Producto' : 'Pack'} por nombre...`"
                                   class="w-full bg-background border-2 border-border rounded-2xl pl-14 pr-14 py-5 outline-none focus:border-primary font-bold text-lg" />
                            <Loader2 v-if="isSearching" class="absolute right-5 top-5 animate-spin text-primary" :size="24" />
                            
                            <div v-if="targets.length > 0" class="absolute z-50 w-full mt-2 bg-card border-2 border-primary/20 rounded-2xl shadow-2xl max-h-64 overflow-y-auto backdrop-blur-xl">
                                <div v-for="t in targets" :key="t.id" @click="selectTarget(t)"
                                     class="p-5 hover:bg-primary/5 cursor-pointer flex items-center justify-between border-b border-border last:border-0 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-muted rounded-lg flex items-center justify-center text-primary"><Package :size="20"/></div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black uppercase italic">{{ t.name }}</span>
                                            <span class="text-[9px] font-mono text-muted-foreground">{{ t.code }}</span>
                                        </div>
                                    </div>
                                    <CheckCircle2 class="text-primary/20" :size="20" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Acción Directa</label>
                                <select v-model="form.action_type" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                    <option value="ADD_TO_CART">🛒 AÑADIR AL CARRITO</option>
                                    <option value="NAVIGATE">🔗 VER DETALLES</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Prioridad de Aparición</label>
                                <input v-model="form.sort_order" type="number" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2 bg-primary/10 text-primary rounded-xl"><ImageIcon :size="18"/></div>
                            <h3 class="text-sm font-black uppercase tracking-widest">Creatividades Web</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase text-muted-foreground"><Smartphone :size="14" /> Móvil (1080x1080)</label>
                                <div class="border-2 border-dashed border-border rounded-3xl p-8 text-center hover:border-primary transition-all relative aspect-square flex flex-col justify-center items-center bg-muted/20">
                                    <input type="file" @input="form.image_mobile = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_mobile" class="space-y-2">
                                        <ImageIcon class="mx-auto text-muted-foreground/20" :size="48" />
                                        <p class="text-[10px] font-black text-muted-foreground uppercase">Soltar WEBP/PNG</p>
                                    </div>
                                    <div v-else class="text-primary font-black text-xs uppercase animate-pulse"><CheckCircle2 class="mx-auto mb-2" :size="32" /> {{ form.image_mobile.name }}</div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase text-muted-foreground"><Monitor :size="14" /> Desktop (1920x600)</label>
                                <div class="border-2 border-dashed border-border rounded-3xl p-8 text-center hover:border-primary transition-all relative aspect-video flex flex-col justify-center items-center bg-muted/20">
                                    <input type="file" @input="form.image_desktop = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_desktop" class="space-y-2">
                                        <ImageIcon class="mx-auto text-muted-foreground/20" :size="48" />
                                        <p class="text-[10px] font-black text-muted-foreground uppercase">Soltar Banner Wide</p>
                                    </div>
                                    <div v-else class="text-primary font-black text-xs uppercase animate-pulse"><CheckCircle2 class="mx-auto mb-2" :size="32" /> {{ form.image_desktop.name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-[2rem] p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 text-primary font-black text-[10px] uppercase tracking-[0.2em] border-b border-border pb-4">
                            <MapPin :size="16" /> Segmentación
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Sucursal</label>
                            <select v-model="form.branch_id" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                <option value="">Todas las Sucursales</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Anclaje en Categoría</label>
                            <select v-model="form.category_id" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                <option value="">Global (Solo Home)</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p class="text-[9px] text-muted-foreground mt-3 italic leading-relaxed">
                                Si eliges una categoría, el banner solo aparecerá cuando el usuario esté navegando en esa sección específica.
                            </p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-[2rem] p-6 shadow-sm">
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border">
                            <span class="text-[10px] font-black uppercase">Estado Vivo</span>
                            <button type="button" @click="form.is_active = !form.is_active"
                                    :class="form.is_active ? 'bg-primary' : 'bg-muted-foreground/30'"
                                    class="w-14 h-7 rounded-full relative transition-all duration-300">
                                <div :class="form.is_active ? 'translate-x-7' : 'translate-x-1'"
                                     class="absolute top-1 w-5 h-5 bg-white rounded-full transition-transform shadow-md"></div>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>