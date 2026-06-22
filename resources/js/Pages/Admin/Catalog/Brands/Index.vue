<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, Head } from '@inertiajs/vue3';
import BrandDrawer from './Components/BrandDrawer.vue';

const props = defineProps({
    brands: Object,
    stats: Object,
    filters: Object,
    options: Object,
    can_manage: Boolean
});

const search = ref(props.filters?.search || '');
const selectedProvider = ref(props.filters?.provider_id || '');
const selectedCategory = ref(props.filters?.category_id || '');
const isDrawerOpen = ref(false);
const selectedBrand = ref(null);

const handleFilter = () => {
    router.get(route('admin.catalog.brands.index'), {
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
        router.delete(route('admin.catalog.brands.destroy', brand.id));
    }
};

const clearFilters = () => {
    search.value = '';
    selectedProvider.value = '';
    selectedCategory.value = '';
    handleFilter();
};
</script>

<template>
    <Head title="Catálogo - Marcas" />
    <AdminLayout>
        
        <template #header>
            <div class="flex items-center justify-between w-full select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Firmas y Marcas</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider mt-0.5 uppercase">Orquestación de marcas comerciales del supermercado</p>
                </div>
                <button v-if="can_manage" @click="openCreateDrawer" 
                        class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider">
                    REGISTRAR_MARCA
                </button>
            </div>
        </template>

        <div class="space-y-4">
            
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-2 flex flex-col md:flex-row gap-2 shadow-sm select-none">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-rounded text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2 text-lg">search</span>
                    <input v-model="search" @input="handleFilter" type="text" 
                           placeholder="Filtrar marca por denominación..." 
                           class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md pl-9 pr-3 py-1.5 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors placeholder:text-neutral-400/50" />
                </div>

                <select v-model="selectedProvider" @change="handleFilter" 
                        class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md text-xs font-bold uppercase py-1.5 px-3 md:w-56 w-full text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                    <option value="">Todos los Proveedores</option>
                    <option v-for="prov in options.providers" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
                </select>

                <select v-model="selectedCategory" @change="handleFilter" 
                        class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md text-xs font-bold uppercase py-1.5 px-3 md:w-56 w-full text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                    <option value="">Todas las Categorías</option>
                    <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <div class="w-full overflow-x-auto border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-900/50 select-none">
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-16 text-center">LOGO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 min-w-[220px]">MARCA / DENOMINACIÓN</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 min-w-[200px]">PROVEEDOR DUEÑO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 min-w-[180px]">PASILLO CATEGORÍA</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-28 text-center">ESTADO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-24 text-center">DESTACADO</th>
                            <th class="p-3 text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 w-28 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="brand in brands.data" :key="brand.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="p-3 text-center">
                                <div class="w-8 h-8 rounded-md border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 flex items-center justify-center overflow-hidden mx-auto select-none">
                                    <img v-if="brand.image_url" :src="brand.image_url" class="w-full h-full object-cover" />
                                    <span v-else class="text-[9px] text-neutral-400 dark:text-neutral-500 font-mono uppercase font-bold">N/A</span>
                                </div>
                            </td>
                            
                            <td class="p-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-sm border border-black/10 shrink-0 select-none block" 
                                          :style="{ backgroundColor: brand.bg_color || '#6366F1' }"></span>
                                    <span class="uppercase tracking-tight text-xs">{{ brand.name }}</span>
                                </div>
                            </td>
                            
                            <td class="p-3 text-neutral-500 dark:text-neutral-400 font-mono text-xs uppercase tracking-tight select-all">
                                {{ brand.provider_name }}
                            </td>
                            
                            <td class="p-3 text-neutral-500 dark:text-neutral-400 text-xs uppercase select-all">
                                {{ brand.category_name }}
                            </td>
                            
                            <td class="p-3 text-center select-none">
                                <span :class="brand.is_active 
                                    ? 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' 
                                    : 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                                    {{ brand.is_active ? 'ACTIVO' : 'OCULTO' }}
                                </span>
                            </td>
                            
                            <td class="p-3 text-center select-none">
                                <span class="material-symbols-rounded text-base align-middle"
                                      :class="brand.is_featured ? 'text-amber-500' : 'text-neutral-300 dark:text-neutral-700'"
                                      :style="{ fontVariationSettings: brand.is_featured ? `'FILL' 1` : `'FILL' 0` }">
                                    star
                                </span>
                            </td>
                            
                            <td class="p-3 text-center select-none">
                                <div class="flex items-center justify-center gap-1">
                                    <a v-if="brand.website" :href="brand.website" target="_blank" 
                                       class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                                       title="Visitar Portal">
                                        <span class="material-symbols-rounded text-base">language</span>
                                    </a>
                                    <button @click="openEditDrawer(brand)" 
                                            class="p-1.5 text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-50 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center" 
                                            title="Editar Parámetros">
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </button>
                                    <button @click="deleteBrand(brand)" 
                                            class="p-1.5 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center justify-center" 
                                            title="Neutralizar">
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="brands.data.length === 0">
                            <td colspan="7" class="p-16 text-center select-none bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
                                <span class="material-symbols-rounded text-4xl text-neutral-300 dark:text-neutral-700 block mb-2">search_off</span>
                                <h3 class="text-neutral-400 dark:text-neutral-500 font-mono text-xs font-bold uppercase tracking-wider">Ninguna firma comercial intersecta la matriz</h3>
                                <button @click="clearFilters" 
                                        class="mt-3 text-xs font-black text-neutral-900 dark:text-white hover:opacity-80 uppercase tracking-wider font-mono underline underline-offset-4">
                                    Reiniciar Protocolo
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BrandDrawer :show="isDrawerOpen" :brand="selectedBrand" :options="options" @close="isDrawerOpen = false" />
    </AdminLayout>
</template>