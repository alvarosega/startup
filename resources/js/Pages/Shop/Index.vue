<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Search, ShoppingCart, Filter, AlertTriangle, Eye, Loader2, Package } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Modales
import ProductQuickView from '@/Components/Shop/ProductQuickView.vue';
import BundleModal from '@/Components/Shop/BundleModal.vue';

const props = defineProps({
    products: Object,
    filters: Object,
});

const page = usePage();
const shopContext = computed(() => page.props.shop_context || {});

// Estado para Modales
const showQuickView = ref(false);
const selectedProduct = ref(null);
const quickViewRef = ref(null);

const showBundleModal = ref(false);
const selectedBundleSlug = ref(null);

const search = ref(props.filters?.search || '');
const inStockOnly = ref(props.filters?.in_stock === 'true' || props.filters?.in_stock === true);
const processingId = ref(null);

const updateFilters = debounce(() => {
    router.get(route('shop.index'), { 
        search: search.value,
        in_stock: inStockOnly.value,
        category_id: props.filters?.category_id,
        type: props.filters?.type // Mantener el tipo en la URL
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 350);

watch([search, inStockOnly], updateFilters);

/**
 * LOGICA DE APERTURA DE MODALES
 */
const handleCardClick = (item) => {
    if (!item.is_available) return;

    if (item.type === 'bundle') {
        selectedBundleSlug.value = item.slug;
        showBundleModal.value = true;
    } else {
        selectedProduct.value = item;
        showQuickView.value = true;
        nextTick(() => {
            if (quickViewRef.value) quickViewRef.value.init();
        });
    }
};

/**
 * LOGICA DE CARRITO DIRECTO
 */
const addToCartQuick = (product) => {
    // Los bundles siempre abren el modal para confirmar contenido/stock
    if (product.type === 'bundle') {
        selectedBundleSlug.value = product.slug;
        showBundleModal.value = true;
        return;
    }

    if (processingId.value || product.variants.length !== 1) return;

    processingId.value = product.id;
    const variant = product.variants[0];

    router.post(route('cart.store'), {
        sku_id: variant.id,
        quantity: 1
    }, {
        preserveScroll: true,
        onFinish: () => processingId.value = null,
        onError: (errors) => console.error(errors)
    });
};
</script>

<template>
    <ShopLayout>
        <Head title="Catálogo" />

        <div v-if="shopContext.is_fallback" class="bg-amber-50 border-b border-amber-100 text-amber-800 px-4 py-3 text-xs text-center animate-in slide-in-from-top duration-300">
            <div class="flex justify-center items-center gap-2 max-w-4xl mx-auto">
                <AlertTriangle :size="16" />
                <span>
                    Estás viendo el catálogo general. Para ver precios y stock reales de tu zona, 
                    <Link :href="route('addresses.create')" class="underline font-bold hover:text-amber-950">
                        confirma tu ubicación
                    </Link>.
                </span>
            </div>
        </div>

        <div class="sticky top-[64px] z-30 bg-white/95 backdrop-blur border-b border-gray-100 py-3 px-4 shadow-sm transition-all">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-3 justify-between md:items-center">
                
                <div class="relative w-full md:w-96 group">
                    <Search class="absolute left-3 top-2.5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" :size="18" />
                    <input v-model="search" type="text" placeholder="Buscar..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all placeholder-gray-400">
                </div>
                
                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-4 py-1.5 rounded-full border border-gray-200 hover:border-indigo-300 hover:bg-white transition select-none group">
                    <input type="checkbox" v-model="inStockOnly" class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 group-hover:border-indigo-400">
                    <span class="text-xs font-bold text-gray-600 group-hover:text-indigo-700">En Stock</span>
                </label>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8 min-h-[60vh]">
            <div v-if="products?.data?.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                
                <div v-for="item in products.data" :key="item.id" 
                     class="group bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-xl hover:border-indigo-100 transition-all duration-300 flex flex-col h-full relative cursor-pointer"
                     @click="handleCardClick(item)">
                    
                    <div class="aspect-square bg-gray-50 relative p-4 flex items-center justify-center overflow-hidden">
                        
                        <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start pointer-events-none">
                            
                            <span v-if="item.type === 'bundle'" class="bg-indigo-600 text-white text-[9px] font-black px-2 py-1 rounded shadow-sm flex items-center gap-1 uppercase">
                                <Package :size="10" /> Pack
                            </span>

                            <span v-if="!item.is_available" class="bg-red-500/90 text-white text-[9px] font-black px-2 py-1 rounded backdrop-blur uppercase shadow-sm">No en zona</span>
                            <span v-else-if="!item.has_stock" class="bg-gray-800/90 text-white text-[9px] font-black px-2 py-1 rounded backdrop-blur uppercase shadow-sm">Agotado</span>
                        </div>
                        
                        <img :src="item.image_url" :alt="item.name" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500">
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider line-clamp-1">{{ item.brand }}</div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2 mb-3 flex-1 group-hover:text-indigo-600 transition-colors">
                            {{ item.name }}
                        </h3>
                        
                        <div class="mt-auto flex items-end justify-between border-t border-gray-50 pt-3" @click.stop>
                            <div>
                                <div class="text-[10px] text-gray-400 font-medium uppercase">Precio</div>
                                <div class="text-lg font-black text-gray-900 leading-none">
                                    <span v-if="item.is_available">Bs {{ item.price_display }}</span>
                                    <span v-else class="text-gray-300 text-sm font-bold">---</span>
                                </div>
                            </div>
                            
                            <div v-if="item.is_available">
                                <button v-if="item.has_stock" 
                                        @click="addToCartQuick(item)"
                                        :disabled="processingId === item.id"
                                        class="w-10 h-10 flex items-center justify-center rounded-full shadow-md transition-all text-white disabled:opacity-75 disabled:cursor-not-allowed bg-gray-900 hover:bg-indigo-600 active:scale-90">
                                    <Loader2 v-if="processingId === item.id" class="animate-spin" :size="18" />
                                    <ShoppingCart v-else :size="18" />
                                </button>
                                
                                <button v-else 
                                        @click="handleCardClick(item)"
                                        class="bg-gray-100 text-gray-600 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-200 hover:text-indigo-600 transition-all">
                                    <Eye :size="18" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="flex flex-col items-center justify-center py-20 animate-in fade-in zoom-in duration-500">
                <div class="bg-gray-50 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <Search :size="40" class="text-gray-300" />
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">No encontramos resultados</h3>
                <p class="text-gray-500 text-sm max-w-xs text-center">
                    Intenta con otro término o selecciona "Packs" en el menú.
                </p>
                <button @click="() => { search = ''; inStockOnly = false; }" class="mt-6 text-indigo-600 font-bold text-sm hover:underline">
                    Limpiar filtros
                </button>
            </div>

            <div v-if="products?.meta?.last_page > 1" class="mt-12 flex justify-center flex-wrap gap-2">
                <Link v-for="(link, k) in products.meta.links" :key="k" 
                      :href="link.url || '#'" 
                      v-html="link.label"
                      class="px-4 py-2 rounded-xl text-xs font-bold transition-all border shadow-sm"
                      :class="[
                          link.active 
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105' 
                            : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
                          !link.url ? 'opacity-50 cursor-not-allowed' : ''
                      ]" />
            </div>
        </div>

        <ProductQuickView 
            ref="quickViewRef"
            :show="showQuickView" 
            :product="selectedProduct" 
            @close="showQuickView = false" 
        />

        <BundleModal 
            :show="showBundleModal" 
            :bundleSlug="selectedBundleSlug" 
            @close="showBundleModal = false" 
        />

    </ShopLayout>
</template>