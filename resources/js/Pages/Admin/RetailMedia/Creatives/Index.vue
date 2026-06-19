<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit2, Trash2, X, Search, Layers, Save, AlertCircle, Image as ImageIcon, Filter } from 'lucide-vue-next';

const props = defineProps({
    creatives: { type: Array, required: true },
    campaigns: { type: Array, required: true },
    placements: { type: Array, required: true },
    branches: { type: Array, required: true },
    categories: { type: Array, required: true },
    bundles: { type: Array, required: true },
    brands: { type: Array, required: true }
});

// Estados de carga e interfaz
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentCreativeId = ref(null);
const currentMediaTab = ref('desktop'); // Pestaña interna del cargador: 'desktop' | 'mobile'

// Selectores dinámicos del formulario modal
const selectedAnchorType = ref('global'); // global, sku, category, bundle, brand
const selectedTargetType = ref('sku');    // sku, category, bundle

// Estados de la barra de filtros rápidos
const filterBranchId = ref('ALL');
const filterPlacementId = ref('ALL');

// Formularios reactivos
const form = useForm({
    _method: 'POST',
    campaign_id: '',
    placement_id: '',
    branch_id: '',
    sku_id: '',
    category_id: '',
    bundle_id: '',
    brand_id: '',
    target_id: '',
    target_type: 'sku',
    name: '',
    image_mobile: null,
    image_desktop: null,
    action_type: 'NAVIGATE',
    sort_order: 0,
    is_active: true
});

// Previsualizaciones locales de imágenes
const previewDesktop = ref(null);
const previewMobile = ref(null);

// Filtrado rápido reactivo por lado del cliente (Ultra-velocidad)
const filteredCreatives = computed(() => {
    return props.creatives.filter(creative => {
        const matchesBranch = filterBranchId.value === 'ALL' || creative.branch_name === props.branches.find(b => b.id === filterBranchId.value)?.name;
        const matchesPlacement = filterPlacementId.value === 'ALL' || creative.placement_code === props.placements.find(p => p.id === filterPlacementId.value)?.code;
        return matchesBranch && matchesPlacement;
    });
});

const handleImageFile = (event, dimension) => {
    const file = event.target.files[0];
    if (!file) return;

    if (dimension === 'desktop') {
        form.image_desktop = file;
        previewDesktop.value = URL.createObjectURL(file);
    } else {
        form.image_mobile = file;
        previewMobile.value = URL.createObjectURL(file);
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    currentCreativeId.value = null;
    selectedAnchorType.value = 'global';
    selectedTargetType.value = 'sku';
    currentMediaTab.value = 'desktop';
    previewDesktop.value = null;
    previewMobile.value = null;
    form.reset();
    form.clearErrors();
    form._method = 'POST';
    isModalOpen.value = true;
};

const openEditModal = (creative) => {
    isEditing.value = false; // Se inicializa para resetear y estructurar la inyección multipart
    isEditing.value = true;
    currentCreativeId.value = creative.id;
    currentMediaTab.value = 'desktop';
    form.clearErrors();

    form.campaign_id = creative.campaign_id;
    form.placement_id = props.placements.find(p => p.code === creative.placement_code)?.id || '';
    form.branch_id = props.branches.find(b => b.name === creative.branch_name)?.id || '';
    form.name = creative.name;
    form.action_type = creative.action_type;
    form.sort_order = creative.sort_order;
    form.is_active = creative.is_active;
    form.target_type = creative.target_type;
    form.target_id = creative.target_id;
    selectedTargetType.value = creative.target_type;

    // Detectar anclaje original
    if (creative.anchor === 'Global') selectedAnchorType.value = 'global';
    else if (props.categories.some(c => c.name === creative.anchor)) { selectedAnchorType.value = 'category'; form.category_id = props.categories.find(c => c.name === creative.anchor)?.id; }
    else if (props.bundles.some(b => b.name === creative.anchor)) { selectedAnchorType.value = 'bundle'; form.bundle_id = props.bundles.find(b => b.name === creative.anchor)?.id; }
    else if (props.brands.some(b => b.name === creative.anchor)) { selectedAnchorType.value = 'brand'; form.brand_id = props.brands.find(b => b.name === creative.anchor)?.id; }
    else { selectedAnchorType.value = 'sku'; form.sku_id = creative.sku_id || ''; }

    previewDesktop.value = creative.image_desktop;
    previewMobile.value = creative.image_mobile;
    form._method = 'PUT';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    // Forzar limpieza de llaves inactivas según exclusión mutua elegida antes del envío
    if (selectedAnchorType.value !== 'sku') form.sku_id = '';
    if (selectedAnchorType.value !== 'category') form.category_id = '';
    if (selectedAnchorType.value !== 'bundle') form.bundle_id = '';
    if (selectedAnchorType.value !== 'brand') form.brand_id = '';
    form.target_type = selectedTargetType.value;

    if (isEditing.value) {
        form.post(route('retail-media.ad-creatives.update', currentCreativeId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('retail-media.ad-creatives.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    }
};

const deleteCreative = (id) => {
    if (confirm('¿Remover físicamente este banner del sistema?')) {
        router.delete(route('retail-media.ad-creatives.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Piezas Creativas - Retail Media" />
        <template #header>Catálogo de Banners Publicitarios</template>

        <div class="space-y-6">
            <div class="bg-card border p-4 rounded-xl shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1 min-w-[300px]">
                    <div class="flex items-center gap-1.5 text-xs font-bold uppercase text-muted-foreground"><Filter :size="14"/> Filtrar por:</div>
                    <select v-model="filterBranchId" class="border rounded-lg p-2 bg-background text-xs font-semibold max-w-[200px]">
                        <option value="ALL">Todas las Sucursales</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                    <select v-model="filterPlacementId" class="border rounded-lg p-2 bg-background text-xs font-semibold max-w-[200px]">
                        <option value="ALL">Todos los Placements</option>
                        <option v-for="placement in placements" :key="placement.id" :value="placement.id">[{{ placement.code }}] {{ placement.name }}</option>
                    </select>
                </div>
                <button @click="openCreateModal" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">
                    <Plus :size="16" /> Inyectar Creativo
                </button>
            </div>

            <div class="border rounded-xl bg-card overflow-x-auto shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                            <th class="p-4">Identificador Banner</th>
                            <th class="p-4">Placement / Sucursal</th>
                            <th class="p-4">Anclaje Contextual</th>
                            <th class="p-4">Destino (Target)</th>
                            <th class="p-4">Orden</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        <tr v-for="creative in filteredCreatives" :key="creative.id" class="hover:bg-muted/30 transition-colors">
                            <td class="p-4">
                                <div class="font-medium text-foreground">{{ creative.name }}</div>
                            </td>
                            <td class="p-4">
                                <span class="font-mono text-xs font-bold text-primary block">[{{ creative.placement_code }}]</span>
                                <span class="text-xs text-muted-foreground block">{{ creative.branch_name }}</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-muted border text-foreground">
                                    {{ creative.anchor }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="text-xs font-bold block uppercase text-foreground/70">{{ creative.target_type }}</span>
                                <span class="text-[11px] text-muted-foreground block truncate max-w-[180px]">{{ creative.target_display }}</span>
                            </td>
                            <td class="p-4 font-mono text-xs">{{ creative.sort_order }}</td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(creative)" class="p-2 border rounded-lg hover:bg-muted text-muted-foreground transition-colors"><Edit2 :size="14" /></button>
                                    <button @click="deleteCreative(creative.id)" class="p-2 border rounded-lg hover:bg-destructive/10 text-destructive transition-colors"><Trash2 :size="14" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCreatives.length === 0">
                            <td colspan="6" class="p-8 text-center text-muted-foreground text-xs uppercase tracking-widest">Ningún banner publicitario coincide con los filtros aplicados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="bg-card border rounded-2xl w-full max-w-3xl shadow-2xl animate-in fade-in zoom-in-95 duration-150 flex flex-col my-auto max-h-[90vh]">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h2 class="text-sm font-black uppercase tracking-wider">Estructura Pieza Publicitaria (Retail Media)</h2>
                        <button @click="closeModal" class="p-1.5 hover:bg-muted rounded-lg text-muted-foreground"><X :size="16" /></button>
                    </div>
                    
                    <form @submit.prevent="submitForm" class="p-6 space-y-6 overflow-y-auto flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Nombre Creativo</label>
                                <input type="text" v-model="form.name" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" placeholder="Ej: Campaña Banner Superior Vino" required />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Campaña Madre</label>
                                <select v-model="form.campaign_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                    <option v-for="c in campaigns" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Placement Asignado</label>
                                <select v-model="form.placement_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                    <option v-for="p in placements" :key="p.id" :value="p.id">[{{ p.code }}] {{ p.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Sucursal Cobertura</label>
                                <select v-model="form.branch_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Orden (Sort Order)</label>
                                <input type="number" v-model="form.sort_order" class="w-full border rounded-lg p-2 bg-background text-xs font-mono font-bold" min="0" required />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Acción de Clic</label>
                                <select v-model="form.action_type" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold">
                                    <option value="NAVIGATE">Navegar a Pantalla</option>
                                    <option value="ADD_TO_CART">Inyección de Ítem Directo</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-border" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3 p-4 border rounded-xl bg-muted/20">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-black uppercase text-primary tracking-widest">1. Selección Punto de Anclaje</label>
                                    <select v-model="selectedAnchorType" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold mt-1">
                                        <option value="global">Carga Global (Sin restricciones internas)</option>
                                        <option value="sku">Anclaje por SKU Específico</option>
                                        <option value="category">Anclaje dentro de una Categoría</option>
                                        <option value="bundle">Anclaje dentro de un Combo</option>
                                        <option value="brand">Anclaje por Filtro de Marca</option>
                                    </select>
                                </div>

                                <div v-if="selectedAnchorType === 'sku'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">UUID del SKU Objetivo</label>
                                    <input type="text" v-model="form.sku_id" class="w-full border rounded-lg p-2 bg-background text-xs font-mono" placeholder="Ingrese el UUID exacto del SKU" required />
                                </div>
                                <div v-if="selectedAnchorType === 'category'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Seleccione Categoría</label>
                                    <select v-model="form.category_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div v-if="selectedAnchorType === 'bundle'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Seleccione Combo</label>
                                    <select v-model="form.bundle_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                        <option v-for="b in bundles" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </div>
                                <div v-if="selectedAnchorType === 'brand'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Seleccione Marca</label>
                                    <select v-model="form.brand_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                        <option v-for="br in brands" :key="br.id" :value="br.id">{{ br.name }}</option>
                                    </select>
                                </div>
                                <div v-if="form.errors.sku_id" class="text-xs text-destructive font-semibold flex items-center gap-1"><AlertCircle :size="12"/> {{ form.errors.sku_id }}</div>
                            </div>

                            <div class="space-y-3 p-4 border rounded-xl bg-muted/20">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-black uppercase text-primary tracking-widest">2. Redirección Polimórfica (Target)</label>
                                    <select v-model="selectedTargetType" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold mt-1">
                                        <option value="sku">Destino: Ficha de SKU / Producto</option>
                                        <option value="category">Destino: Muro de Categoría</option>
                                        <option value="bundle">Destino: Vista de Combo Comercial</option>
                                    </select>
                                </div>

                                <div v-if="selectedTargetType === 'sku'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">UUID del SKU Destino</label>
                                    <input type="text" v-model="form.target_id" class="w-full border rounded-lg p-2 bg-background text-xs font-mono" placeholder="UUID del artículo destino" required />
                                </div>
                                <div v-if="selectedTargetType === 'category'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Categoría de Destino</label>
                                    <select v-model="form.target_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>
                                <div v-if="selectedTargetType === 'bundle'" class="flex flex-col gap-1 animate-in fade-in duration-100">
                                    <label class="text-xs font-bold uppercase text-muted-foreground">Combo de Destino</label>
                                    <select v-model="form.target_id" class="w-full border rounded-lg p-2 bg-background text-xs font-semibold" required>
                                        <option v-for="b in bundles" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </div>
                                <div v-if="form.errors.target_id" class="text-xs text-destructive font-semibold flex items-center gap-1"><AlertCircle :size="12"/> {{ form.errors.target_id }}</div>
                            </div>
                        </div>

                        <hr class="border-border" />

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-muted-foreground">Piezas Gráficas Adjuntas</label>
                            <div class="border rounded-xl overflow-hidden bg-background">
                                <div class="flex border-b bg-muted/30">
                                    <button type="button" @click="currentMediaTab = 'desktop'" :class="currentMediaTab === 'desktop' ? 'bg-background border-r font-bold text-primary' : 'text-muted-foreground hover:bg-muted/50'" class="px-4 py-2 text-xs uppercase tracking-wider transition-all focus:outline-none">
                                        Resolución Escritorio (Desktop)
                                    </button>
                                    <button type="button" @click="currentMediaTab = 'mobile'" :class="currentMediaTab === 'mobile' ? 'bg-background border-l border-r font-bold text-primary' : 'text-muted-foreground hover:bg-muted/50'" class="px-4 py-2 text-xs uppercase tracking-wider transition-all focus:outline-none">
                                        Resolución Móvil (Mobile)
                                    </button>
                                </div>

                                <div class="p-6 min-h-[160px] flex flex-col items-center justify-center relative bg-muted/5">
                                    <div v-show="currentMediaTab === 'desktop'" class="w-full flex flex-col items-center animate-in fade-in duration-100">
                                        <div v-if="previewDesktop" class="w-full max-h-[140px] border rounded-lg overflow-hidden relative mb-3">
                                            <img :src="previewDesktop" class="w-full h-full object-contain max-h-[140px]" />
                                        </div>
                                        <ImageIcon v-else :size="24" class="text-muted-foreground/30 mb-2" />
                                        <label class="text-[11px] bg-primary text-primary-foreground px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider cursor-pointer">
                                            Cargar Imagen Desktop
                                            <input type="file" @change="handleImageFile($event, 'desktop')" class="hidden" accept="image/*" />
                                        </label>
                                        <span class="text-[9px] text-muted-foreground mt-2 uppercase">Sugerido: Paisaje / Max 4MB</span>
                                    </div>

                                    <div v-show="currentMediaTab === 'mobile'" class="w-full flex flex-col items-center animate-in fade-in duration-100">
                                        <div v-if="previewMobile" class="w-28 h-36 border rounded-lg overflow-hidden relative mb-3">
                                            <img :src="previewMobile" class="w-full h-full object-cover" />
                                        </div>
                                        <ImageIcon v-else :size="24" class="text-muted-foreground/30 mb-2" />
                                        <label class="text-[11px] bg-primary text-primary-foreground px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider cursor-pointer">
                                            Cargar Imagen Mobile
                                            <input type="file" @change="handleImageFile($event, 'mobile')" class="hidden" accept="image/*" />
                                        </label>
                                        <span class="text-[9px] text-muted-foreground mt-2 uppercase">Sugerido: Vertical / Max 2MB</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.image_mobile" class="text-xs text-destructive font-semibold flex items-center gap-1"><AlertCircle :size="12"/> {{ form.errors.image_mobile }}</div>
                        </div>
                    </form>

                    <div class="p-4 border-t bg-muted/30 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" @click="closeModal" class="border rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wider bg-background text-foreground hover:bg-muted">Cancelar</button>
                        <button type="button" @click="submitForm" :disabled="form.processing" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider disabled:opacity-40">
                            <Save :size="14" /> {{ form.processing ? 'Sincronizando...' : 'Inyectar Pieza' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.font-mono { font-family: 'JetBrains Mono', monospace; }
</style>