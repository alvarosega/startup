<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, Head } from '@inertiajs/vue3';
import { 
    Layers, Plus, Edit2, Trash2, Eye, EyeOff, Star, Search, 
    Layers3, CheckCircle2, AlertCircle, Globe, ExternalLink
} from 'lucide-vue-next';
import BrandDrawer from './Components/BrandDrawer.vue';

const props = defineProps({
    brands: Object,
    stats: Object,
    filters: Object,
    options: Object,
    can_manage: Boolean
});

const search = ref(props.filters.search || '');
const selectedProvider = ref(props.filters.provider_id || '');
const selectedCategory = ref(props.filters.category_id || '');
const isDrawerOpen = ref(false);
const selectedBrand = ref(null);

const handleFilter = () => {
    router.get(route('admin.brands.index'), {
        search: search.value,
        provider_id: selectedProvider.value,
        category_id: selectedCategory.value
    }, {
        preserveState: true,
        replace: true
    });
};

const openCreateDrawer = () => {
    selectedBrand.value = null;
    isDrawerOpen.value = true;
};

const openEditDrawer = (brand) => {
    selectedBrand.value = brand;
    isDrawerOpen.value = true;
};

const deleteBrand = (brand) => {
    if (confirm(`¿Confirmar la neutralización estricta de la marca: ${brand.name}?`)) {
        router.delete(route('admin.brands.destroy', brand.id));
    }
};
</script>

<template>
    <AdminLayout>
    <Head title="Catálogo - Marcas" />

    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-card p-4 rounded-xl border border-border shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                    <Layers :size="24" />
                </div>
                <div>
                    <h1 class="font-sans font-bold text-xl text-foreground">Firmas y Marcas</h1>
                    <p class="text-xs text-muted-foreground">Orquestación de marcas comerciales del supermercado</p>
                </div>
            </div>
            
            <button v-if="can_manage" @click="openCreateDrawer" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg shadow-sm hover:bg-primary/90 transition-colors">
                <Plus :size="16" />
                Registrar Marca
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-card p-4 rounded-xl border border-border shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Marcas Registradas</p>
                    <h3 class="text-2xl font-bold text-foreground mt-1 font-mono">{{ stats.total }}</h3>
                </div>
                <div class="p-3 bg-muted rounded-xl text-muted-foreground"><Layers3 :size="20" /></div>
            </div>
            <div class="bg-card p-4 rounded-xl border border-border shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Activas en Góndola</p>
                    <h3 class="text-2xl font-bold text-emerald-500 mt-1 font-mono">{{ stats.active }}</h3>
                </div>
                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500"><CheckCircle2 :size="20" /></div>
            </div>
            <div class="bg-card p-4 rounded-xl border border-border shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Destacadas (Home)</p>
                    <h3 class="text-2xl font-bold text-amber-500 mt-1 font-mono">{{ stats.featured }}</h3>
                </div>
                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500"><Star :size="20" /></div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-4 flex flex-col md:flex-row gap-3 shadow-sm">
            <div class="flex items-center gap-2 flex-1 bg-background border border-border rounded-lg px-3 py-1.5 focus-within:ring-1 focus-within:ring-primary">
                <Search :size="16" class="text-muted-foreground" />
                <input v-model="search" @input="handleFilter" type="text" placeholder="Filtrar marca por denominación..." class="w-full bg-transparent text-sm text-foreground outline-none border-none p-0 focus:ring-0" />
            </div>

            <select v-model="selectedProvider" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm text-foreground outline-none focus:ring-1 focus:ring-primary">
                <option value="">Todos los Proveedores</option>
                <option v-for="prov in options.providers" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
            </select>

            <select v-model="selectedCategory" @change="handleFilter" class="bg-background border border-border rounded-lg px-3 py-1.5 text-sm text-foreground outline-none focus:ring-1 focus:ring-primary">
                <option value="">Todas las Categorías</option>
                <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
        </div>

        <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground text-xs font-semibold uppercase border-b border-border">
                        <th class="p-4 w-16 text-center">Logo</th>
                        <th class="p-4">Marca / Denominación</th>
                        <th class="p-4">Proveedor Dueño</th>
                        <th class="p-4">Pasillo Categoría</th>
                        <th class="p-4 w-24 text-center">Estado</th>
                        <th class="p-4 w-24 text-center">Destacado</th>
                        <th class="p-4 w-32 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-border">
                    <tr v-for="brand in brands.data" :key="brand.id" class="hover:bg-muted/20 transition-colors">
                        <td class="p-4 text-center">
                            <img v-if="brand.image_url" :src="brand.image_url" class="w-9 h-9 object-cover rounded border border-border bg-background mx-auto" />
                            <div v-else class="w-9 h-9 bg-muted border border-border rounded flex items-center justify-center text-[9px] text-muted-foreground font-mono uppercase mx-auto">N/A</div>
                        </td>
                        <td class="p-4 font-medium text-foreground">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: brand.bg_color }"></span>
                                {{ brand.name }}
                            </div>
                        </td>
                        <td class="p-4 text-muted-foreground font-sans text-xs uppercase tracking-tight">{{ brand.provider_name }}</td>
                        <td class="p-4 text-muted-foreground text-xs">{{ brand.category_name }}</td>
                        <td class="p-4 text-center">
                            <span :class="brand.is_active ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full border">
                                <Eye v-if="brand.is_active" :size="12" class="mr-1" />
                                <EyeOff v-else :size="12" class="mr-1" />
                                {{ brand.is_active ? 'Activo' : 'Oculto' }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <Star :size="16" :class="brand.is_featured ? 'text-amber-500 fill-amber-500' : 'text-muted-foreground/30'" class="mx-auto" />
                        </td>
                        <td class="p-4 text-right space-x-1 whitespace-nowrap">
                            <a v-if="brand.website" :href="brand.website" target="_blank" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-md transition-colors" title="Visitar Sitio Web">
                                <ExternalLink :size="15" />
                            </a>
                            <button @click="openEditDrawer(brand)" class="inline-flex items-center p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors" title="Editar Marca">
                                <Edit2 :size="15" />
                            </button>
                            <button @click="deleteBrand(brand)" class="inline-flex items-center p-1.5 text-destructive hover:bg-destructive/10 rounded-md transition-colors" title="Neutralizar">
                                <Trash2 :size="15" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="brands.data.length === 0">
                        <td colspan="7" class="p-8 text-center text-muted-foreground">
                            Ninguna firma comercial intersecta los filtros seleccionados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <BrandDrawer :show="isDrawerOpen" :brand="selectedBrand" :options="options" @close="isDrawerOpen = false" />
</AdminLayout>
</template>