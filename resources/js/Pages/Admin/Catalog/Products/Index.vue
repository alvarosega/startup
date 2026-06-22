<script setup>
import { ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductRow from './Components/ProductRow.vue';
import PriceManagerModal from './Components/PriceManagerModal.vue';

const props = defineProps({
    products: Object,
    filters: Object,
    options: Object,
    can_manage: Boolean
});

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const selectedBrand = ref(props.filters?.brand || '');
const selectedStatus = ref(props.filters?.status || '');

const isPriceModalOpen = ref(false);
const activeSku = ref(null);

const handleFilter = () => {
    router.get(route('admin.catalog.products.index'), {
        search: search.value,
        category: selectedCategory.value,
        brand: selectedBrand.value,
        status: selectedStatus.value
    }, { preserveState: true, replace: true });
};

const openPriceModal = (sku) => {
    activeSku.value = sku;
    isPriceModalOpen.value = true;
};

const destroyProduct = (id, name) => {
    if (confirm(`¿Proceder con la remoción atómica del maestro y SKUs de: ${name}?`)) {
        router.delete(route('admin.catalog.products.destroy', id));
    }
};

const destroySku = (id) => {
    if (confirm('¿Remover esta variante física del catálogo permanente?')) {
        router.delete(route('admin.catalog.skus.destroy', id));
    }
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    selectedBrand.value = '';
    selectedStatus.value = '';
    handleFilter();
};
</script>

<template>
    <Head title="Catálogo - Maestros de Producto" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Maestros de Producto</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider mt-0.5 uppercase">Estructuras base del inventario comercializable</p>
                </div>
                <Link v-if="can_manage" :href="route('admin.catalog.products.create')" 
                      class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 border border-transparent rounded-md transition-colors text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2">
                    <span class="material-symbols-rounded text-sm">add_box</span>
                    <span>MATERIALIZAR_PRODUCTO</span>
                </Link>
            </div>
        </template>

        <div class="space-y-4">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-md p-2 flex flex-col lg:flex-row gap-2 shadow-sm select-none">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-rounded text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2 text-lg">search</span>
                    <input v-model="search" @input="handleFilter" type="text" 
                           placeholder="Buscar por designación, código interno o barra..." 
                           class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md pl-9 pr-3 py-1.5 text-xs font-mono uppercase text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors placeholder:text-neutral-400/50" />
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 shrink-0">
                    <select v-model="selectedCategory" @change="handleFilter" class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md text-xs font-bold uppercase py-1.5 px-3 min-w-[150px] text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                        <option value="">Todos los Pasillos</option>
                        <option v-for="cat in options.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    
                    <select v-model="selectedBrand" @change="handleFilter" class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md text-xs font-bold uppercase py-1.5 px-3 min-w-[150px] text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                        <option value="">Todas las Marcas</option>
                        <option v-for="br in options.brands" :key="br.id" :value="br.id">{{ br.name }}</option>
                    </select>
                    
                    <select v-model="selectedStatus" @change="handleFilter" class="bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md text-xs font-bold uppercase py-1.5 px-3 min-w-[150px] text-neutral-900 dark:text-neutral-50 focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 outline-none transition-colors">
                        <option value="">Matriz de Integridad</option>
                        <option value="complete">COMPLETOS (VALIDADO)</option>
                        <option value="incomplete">INCOMPLETOS (MIS_VARIANTES)</option>
                    </select>
                </div>
            </div>

            <div class="w-full overflow-x-auto border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50/70 dark:bg-neutral-900/50 select-none text-[10px] font-mono font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            <th class="p-3 w-12 text-center">VINCULO</th>
                            <th class="p-3 min-w-[240px]">PRODUCTO MAESTRO CORE</th>
                            <th class="p-3 w-36 text-center">INTEGRIDAD</th>
                            <th class="p-3 min-w-[150px]">LÍNEA / MARCA</th>
                            <th class="p-3 min-w-[150px]">PASILLO ALMACÉN</th>
                            <th class="p-3 w-28 text-center">DESGLOSE</th>
                            <th class="p-3 w-24 text-center">ESTADO</th>
                            <th class="p-3 w-24 text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <ProductRow 
                            v-for="prod in products.data" 
                            :key="prod.id" 
                            :product="prod" 
                            :can_manage="can_manage"
                            @delete-product="destroyProduct"
                            @delete-sku="destroySku"
                            @manage-prices="openPriceModal"
                        />
                        <tr v-if="products.data.length === 0">
                            <td colspan="8" class="p-16 text-center select-none bg-white dark:bg-neutral-900">
                                <span class="material-symbols-rounded text-4xl text-neutral-300 dark:text-neutral-700 block mb-2">search_off</span>
                                <h3 class="text-neutral-400 dark:text-neutral-500 font-mono text-xs font-bold uppercase tracking-wider">Ningún registro maestro intersecta los parámetros</h3>
                                <button @click="clearFilters" class="mt-3 text-xs font-black text-neutral-900 dark:text-white hover:opacity-80 uppercase tracking-wider font-mono underline underline-offset-4">Reiniciar Protocolo</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <PriceManagerModal 
            :show="isPriceModalOpen" 
            :sku="activeSku" 
            :branches="options.branches" 
            @close="isPriceModalOpen = false" 
        />
    </AdminLayout>
</template>