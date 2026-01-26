<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Search, ShoppingCart, Filter, AlertTriangle 
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    filters: Object,
    context: Object
});

// Inicialización segura de filtros
const search = ref(props.filters?.search || '');
const inStockOnly = ref(props.filters?.in_stock === 'true' || props.filters?.in_stock === true);

// Filtros Reactivos (Debounced)
const updateFilters = debounce(() => {
    router.get(route('shop.index'), { 
        search: search.value,
        in_stock: inStockOnly.value,
        category_id: props.filters?.category_id
    }, { 
        preserveState: true, 
        preserveScroll: true, 
        replace: true 
    });
}, 350);

watch([search, inStockOnly], updateFilters);

const addToCart = (product, variant) => {
    // Aquí conectarás con tu Store de Pinia
    console.log(`Añadiendo ${product.name} (Variante: ${variant.name})`);
    // cartStore.add(product, variant); 
};
</script>


<template>
    <ShopLayout>
        <Head title="Catálogo" />

        <div class="bg-indigo-900 text-white px-4 py-3 shadow-md">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <MapPin :size="20" class="text-indigo-300" />
                    <div>
                        <p class="text-xs text-indigo-300 uppercase font-bold tracking-wider">Comprando en:</p>
                        <h2 class="text-sm md:text-base font-bold text-white">
                            {{ context?.branch_name }} (ID: {{ context?.branch_id }})
                        </h2>
                    </div>
                </div>
                
                <Link :href="route('addresses.create')" class="text-xs bg-indigo-700 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg transition border border-indigo-500">
                    Cambiar Ubicación
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div v-if="products?.data?.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                
                <div v-for="product in products.data" :key="product.id" 
                     class="group bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-xl transition-all">
                    
                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-bold text-gray-900 text-sm mb-2">{{ product.name }}</h3>

                        <div class="mb-3 p-2 bg-gray-50 rounded border border-gray-100 text-[10px] text-gray-500 font-mono">
                            <p>Stock: {{ product.stock }} unid.</p>
                            <p>Fuente: Sucursal ID {{ product.stock_source_id }}</p>
                        </div>
                        
                        <div class="mt-auto flex items-end justify-between">
                            <div>
                                <div class="text-xs text-gray-400">Precio</div>
                                <div class="text-lg font-black text-gray-900">
                                    <span v-if="product.is_available">Bs {{ product.price_display }}</span>
                                    <span v-else class="text-gray-300 text-sm">---</span>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>

            </div>
            </div>
    </ShopLayout>
</template>