<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { 
    Save, ArrowLeft, Image as ImageIcon, Smartphone, 
    Monitor, CheckCircle2, Search, Loader2, Package,
    Layers, Tag, MapPin, LayoutGrid, MousePointerClick, Building2
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

// --- BUSCADOR DE TARGET (POLIMORFISMO DINÁMICO) ---
const targets = ref([]);
const isSearching = ref(false);
const searchQuery = ref(creativeData.value?.target?.name || '');
let searchTimeout = null;

const form = useForm({
    id: creativeData.value?.id || null,
    campaign_id: creativeData.value?.campaign?.id || '',
    placement_id: creativeData.value?.placement?.id || '',
    branch_id: creativeData.value?.branch?.id || '', 
    brand_id: creativeData.value?.brand?.id || '', // NUEVO: Silo de Marca propietaria
    category_id: creativeData.value?.category?.id || '', 
    target_type: creativeData.value?.target?.type || 'sku', 
    target_id: creativeData.value?.target?.id || '',
    name: creativeData.value?.name || '',
    action_type: creativeData.value?.action_type || 'NAVIGATE',
    sort_order: creativeData.value?.sort_order || 0,
    is_active: isEdit ? !!creativeData.value.is_active : true,
    image_mobile: null,
    image_desktop: null,
});

/**
 * MOTOR DE BÚSQUEDA AJAX (SCALABLE)
 * Dicta el endpoint basado en el target_type seleccionado.
 */
const handleSearch = () => {
    if (searchQuery.value.length < 2) return;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearching.value = true;
        try {
            const endpoints = {
                'sku': route('admin.retail-media.ad-creatives.search-skus'),
                'bundle': route('admin.retail-media.ad-creatives.search-bundles'),
                'brand': route('admin.retail-media.ad-creatives.search-brands') // NUEVO
            };

            const { data } = await axios.get(endpoints[form.target_type], { 
                params: { q: searchQuery.value } 
            });
            targets.value = data;
        } catch (e) { 
            console.error("Critical: Target search failed", e); 
        } finally { 
            isSearching.value = false; 
        }
    }, 300);
};

const selectTarget = (t) => {
    form.target_id = t.id;
    searchQuery.value = t.name;
    targets.value = [];
    
    // Si el target es una marca, autocompletamos el brand_id de la metadata
    if (form.target_type === 'brand') {
        form.brand_id = t.id;
    }
};

const submit = () => {
    const url = isEdit 
        ? route('admin.retail-media.ad-creatives.update', creativeData.value.id) 
        : route('admin.retail-media.ad-creatives.store');

    // Regla 3: Sanitización y fuerza de FormData para carga de binarios
    form.post(url, { 
        forceFormData: true, 
        preserveScroll: true,
        onSuccess: () => {
            // Notificación de éxito implícita por redirección
        }
    });
};
</script>

<template>
    <Head :title="isEdit ? 'Sincronizar Activo' : 'Desplegar Banner'" />
    <AdminLayout>
        <div class="p-8 max-w-7xl mx-auto pb-32">
            
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-5">
                    <Link :href="route('admin.retail-media.ad-creatives.index')" class="p-3 bg-card border border-border rounded-2xl hover:bg-muted transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-black uppercase italic tracking-tighter">{{ isEdit ? 'Editar Creativo' : 'Nuevo Despliegue' }}</h1>
                        <p class="text-[10px] font-mono text-primary uppercase tracking-widest">Silo Admin // Retail Media System</p>
                    </div>
                </div>
                <button @click="submit" :disabled="form.processing" 
                        class="bg-primary text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-3 hover:scale-105 active:scale-95 disabled:opacity-50 transition-all shadow-xl shadow-primary/20">
                    <Loader2 v-if="form.processing" class="animate-spin" :size="18" />
                    <Save v-else :size="18" stroke-width="3" />
                    {{ isEdit ? 'Sincronizar' : 'Publicar' }}
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2 bg-primary/10 text-primary rounded-xl"><Tag :size="18"/></div>
                            <h3 class="text-sm font-black uppercase tracking-widest">Configuración Maestra</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Nombre del Activo (Interno)</label>
                                <input v-model="form.name" type="text" class="w-full bg-background border-2 border-border rounded-2xl px-5 py-4 outline-none focus:border-primary transition-colors font-bold" placeholder="Ej: BRAND_HERO_NESTLE_SOPOCACHI" />
                                <div v-if="form.errors.name" class="text-destructive text-[10px] font-bold mt-2 uppercase italic">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Campaña de Retail</label>
                                <select v-model="form.campaign_id" class="w-full bg-background border-2 border-border rounded-2xl px-4 py-4 outline-none focus:border-primary font-bold appearance-none">
                                    <option value="">Seleccionar Campaña...</option>
                                    <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Espacio Publicitario (Placement)</label>
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
                                <button v-for="type in ['sku', 'bundle', 'brand']" :key="type"
                                        type="button" @click="form.target_type = type; form.target_id = ''; searchQuery = ''"
                                        :class="form.target_type === type ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground'"
                                        class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all border-none outline-none capitalize">
                                    {{ type }}
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <Search class="absolute left-5 top-5 text-muted-foreground" :size="20" />
                            <input v-model="searchQuery" @input="handleSearch" type="text" 
                                   :placeholder="`Buscar ${form.target_type.toUpperCase()} por nombre...`"
                                   class="w-full bg-background border-2 border-border rounded-2xl pl-14 pr-14 py-5 outline-none focus:border-primary font-bold text-lg" />
                            <Loader2 v-if="isSearching" class="absolute right-5 top-5 animate-spin text-primary" :size="24" />
                            
                            <div v-if="targets.length > 0" class="absolute z-50 w-full mt-2 bg-card border-2 border-primary/20 rounded-2xl shadow-2xl max-h-64 overflow-y-auto backdrop-blur-xl">
                                <div v-for="t in targets" :key="t.id" @click="selectTarget(t)"
                                     class="p-5 hover:bg-primary/5 cursor-pointer flex items-center justify-between border-b border-border last:border-0 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-muted rounded-lg flex items-center justify-center text-primary">
                                            <Building2 v-if="form.target_type === 'brand'" :size="20"/>
                                            <Package v-else :size="20"/>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black uppercase italic">{{ t.name }}</span>
                                            <span class="text-[9px] font-mono text-muted-foreground tracking-widest">{{ t.code }}</span>
                                        </div>
                                    </div>
                                    <CheckCircle2 class="text-primary/20" :size="20" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Comportamiento</label>
                                <select v-model="form.action_type" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                    <option value="ADD_TO_CART">🛒 COMPRA DIRECTA (CART)</option>
                                    <option value="NAVIGATE">🔗 VER DETALLES (VIEW)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2">Orden de Visualización</label>
                                <input v-model="form.sort_order" type="number" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-[2.5rem] p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-2 bg-primary/10 text-primary rounded-xl"><ImageIcon :size="18"/></div>
                            <h3 class="text-sm font-black uppercase tracking-widest">Creatividades Visuales</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase text-muted-foreground"><Smartphone :size="14" /> Móvil (1:1 WebP)</label>
                                <div class="border-2 border-dashed border-border rounded-3xl p-8 text-center hover:border-primary transition-all relative aspect-square flex flex-col justify-center items-center bg-muted/20">
                                    <input type="file" @input="form.image_mobile = $event.target.files[0]" accept="image/webp,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_mobile" class="space-y-2">
                                        <ImageIcon class="mx-auto text-muted-foreground/20" :size="48" />
                                        <p class="text-[10px] font-black text-muted-foreground uppercase">Arrastrar Asset Móvil</p>
                                    </div>
                                    <div v-else class="text-primary font-black text-xs uppercase animate-in zoom-in-95"><CheckCircle2 class="mx-auto mb-2" :size="32" /> {{ form.image_mobile.name }}</div>
                                </div>
                                <div v-if="form.errors.image_mobile" class="text-destructive text-[9px] font-bold uppercase">{{ form.errors.image_mobile }}</div>
                            </div>
                            <div class="space-y-4">
                                <label class="flex items-center gap-2 text-[10px] font-black uppercase text-muted-foreground"><Monitor :size="14" /> Desktop (Wide WebP)</label>
                                <div class="border-2 border-dashed border-border rounded-3xl p-8 text-center hover:border-primary transition-all relative aspect-video flex flex-col justify-center items-center bg-muted/20">
                                    <input type="file" @input="form.image_desktop = $event.target.files[0]" accept="image/webp,image/png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <div v-if="!form.image_desktop" class="space-y-2">
                                        <ImageIcon class="mx-auto text-muted-foreground/20" :size="48" />
                                        <p class="text-[10px] font-black text-muted-foreground uppercase">Arrastrar Banner Desktop</p>
                                    </div>
                                    <div v-else class="text-primary font-black text-xs uppercase animate-in zoom-in-95"><CheckCircle2 class="mx-auto mb-2" :size="32" /> {{ form.image_desktop.name }}</div>
                                </div>
                                <div v-if="form.errors.image_desktop" class="text-destructive text-[9px] font-bold uppercase">{{ form.errors.image_desktop }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-[2rem] p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-2 text-primary font-black text-[10px] uppercase tracking-[0.2em] border-b border-border pb-4">
                            <MapPin :size="16" /> Control de Alcance
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2 italic">Sucursal Destino</label>
                            <select v-model="form.branch_id" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                <option value="">Todas las Sucursales</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-muted-foreground mb-2 italic">Anclaje en Pasillo</label>
                            <select v-model="form.category_id" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3 outline-none font-bold">
                                <option value="">Global (Sin Anclaje)</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <div class="p-4 bg-muted/50 rounded-2xl border-l-4 border-primary">
                            <p class="text-[9px] text-muted-foreground font-medium leading-relaxed italic">
                                El banner se inyectará dinámicamente según la sucursal del cliente y el pasillo de navegación detectado.
                            </p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-[2rem] p-6 shadow-sm">
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border">
                            <span class="text-[10px] font-black uppercase tracking-widest">Estado Activo</span>
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