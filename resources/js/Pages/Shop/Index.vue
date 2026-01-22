<script setup>
    import { ref, computed } from 'vue';
    import { Head, router, useForm } from '@inertiajs/vue3'; // Importamos useForm
    import ShopLayout from '@/Layouts/ShopLayout.vue'; 
    import { MapPin, ShoppingCart, Search, Filter, Package, X, Plus, Minus, AlertCircle, Check } from 'lucide-vue-next';
    
    // Props recibidos del CatalogController
    const props = defineProps({
        products: Array,
        currentBranch: Object,
        branches: Array
    });
    
    const search = ref('');
    const selectedBranch = ref(props.currentBranch?.id || '');
    const selectedProduct = ref(null); // Para el Modal
    
    // --- FILTROS ---
    const filteredProducts = computed(() => {
        if (!search.value) return props.products;
        return props.products.filter(p => 
            p.name.toLowerCase().includes(search.value.toLowerCase()) || 
            p.brand?.name.toLowerCase().includes(search.value.toLowerCase())
        );
    });
    
    // --- LÓGICA DEL CARRITO (FORMULARIO) ---
    const cartForm = useForm({
        sku_id: null,
        branch_id: props.currentBranch?.id,
        quantity: 1
    });
    
    // Función para abrir el modal
    const openQuickView = (product) => {
        selectedProduct.value = product;
        // Pre-seleccionar el primer SKU disponible
        if (product.available_skus.length > 0) {
            cartForm.sku_id = product.available_skus[0].id;
            cartForm.branch_id = props.currentBranch.id; // Asegurar contexto
            cartForm.quantity = 1;
        }
    };
    
    const closeQuickView = () => {
        selectedProduct.value = null;
        cartForm.reset();
        cartForm.clearErrors();
    };
    
    const addToCart = () => {
        cartForm.post(route('cart.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeQuickView();
                // Aquí podrías mostrar un toast/notificación de éxito
            },
            onError: (errors) => {
                console.error(errors);
            }
        });
    };
    
    // --- HELPERS VISUALES ---
    const getSkuDisplay = (product) => {
        if (product.available_skus.length === 1) return product.available_skus[0].name;
        return `${product.available_skus.length} presentaciones`;
    };
    
    const getDisplayPrice = (product) => {
        if (!product.available_skus || product.available_skus.length === 0) return '0.00';
        const minPrice = Math.min(...product.available_skus.map(s => s.price));
        return minPrice.toFixed(2);
    };
    
    // Helper para obtener datos del SKU seleccionado en el modal
    const currentSku = computed(() => {
        if (!selectedProduct.value || !cartForm.sku_id) return null;
        return selectedProduct.value.available_skus.find(s => s.id === cartForm.sku_id);
    });
    </script>
    
    <template>
        <ShopLayout>
            <Head title="Catálogo" />
    
            <div class="bg-blue-900 text-white p-4 shadow-md sticky top-0 z-30">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2">
                        <MapPin class="text-yellow-400" />
                        <div>
                            <p class="text-[10px] uppercase font-bold opacity-80">Comprando en:</p>
                            <select v-model="selectedBranch" 
                                    @change="router.get(route('shop.index'), { branch_id: selectedBranch }, { preserveState: false })"
                                    class="bg-blue-800 border-none text-sm font-bold rounded cursor-pointer focus:ring-0">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
    
                    <div class="relative w-full md:w-96">
                        <input v-model="search" type="text" placeholder="¿Qué se te antoja hoy?" 
                               class="w-full pl-10 pr-4 py-2 rounded-full border-none text-gray-900 focus:ring-2 focus:ring-yellow-400 placeholder-gray-400 text-sm shadow-inner">
                        <Search class="absolute left-3 top-2.5 text-gray-400" :size="18" />
                    </div>
                </div>
            </div>
    
            <div class="max-w-7xl mx-auto py-8 px-4">
                
                <div v-if="filteredProducts.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    
                    <div v-for="product in filteredProducts" :key="product.id" 
                         @click="openQuickView(product)"
                         class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all duration-300 overflow-hidden group flex flex-col cursor-pointer">
                        
                        <div class="relative aspect-square bg-gray-50 p-4 flex items-center justify-center overflow-hidden">
                            <img v-if="product.available_skus[0]?.image" :src="product.available_skus[0].image" class="object-contain w-full h-full group-hover:scale-110 transition-transform duration-500">
                            <Package v-else :size="48" class="text-gray-200" />
                            
                            <span class="absolute top-2 right-2 bg-white/90 backdrop-blur text-[10px] font-bold px-2 py-1 rounded-full shadow-sm text-gray-500">
                                {{ product.brand?.name }}
                            </span>
                        </div>
    
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 line-clamp-2 leading-tight mb-1">{{ product.name }}</h3>
                                <p class="text-xs text-gray-500">{{ product.category?.name }}</p>
                            </div>
    
                            <div class="mt-4 pt-3 border-t border-gray-50 flex items-end justify-between">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold">{{ getSkuDisplay(product) }}</p>
                                    <p class="text-lg font-black text-blue-900">
                                        <span class="text-xs align-top opacity-60">Bs</span>{{ getDisplayPrice(product) }}
                                    </p>
                                </div>
                                <button class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 p-2 rounded-full shadow-lg shadow-yellow-400/30 transition transform active:scale-90">
                                    <Plus :size="20" />
                                </button>
                            </div>
                        </div>
                    </div>
    
                </div>
    
                <div v-else class="text-center py-20 opacity-50">
                    <Filter :size="64" class="mx-auto mb-4" />
                    <h3 class="text-xl font-bold">No encontramos productos</h3>
                    <p>Intenta con otro término o cambia de sucursal.</p>
                </div>
            </div>
    
            <div v-if="selectedProduct" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-0" @click.self="closeQuickView">
                
                <div class="bg-white w-full max-w-lg rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden animate-in slide-in-from-bottom-10 fade-in duration-300">
                    
                    <div class="relative bg-gray-50 p-6 border-b border-gray-100">
                        <button @click="closeQuickView" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition">
                            <X :size="24" />
                        </button>
                        <div class="flex gap-4">
                            <div class="w-20 h-20 bg-white rounded-lg border border-gray-200 flex items-center justify-center p-2">
                                 <img v-if="currentSku?.image" :src="currentSku.image" class="w-full h-full object-contain">
                                 <Package v-else :size="32" class="text-gray-300" />
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-800 leading-tight">{{ selectedProduct.name }}</h2>
                                <p class="text-sm text-gray-500">{{ selectedProduct.brand?.name }}</p>
                            </div>
                        </div>
                    </div>
    
                    <div class="p-6 space-y-6">
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Elige Presentación</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label v-for="sku in selectedProduct.available_skus" :key="sku.id" 
                                       class="border rounded-lg p-3 cursor-pointer transition relative overflow-hidden"
                                       :class="cartForm.sku_id === sku.id ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-blue-300'">
                                    
                                    <input type="radio" :value="sku.id" v-model="cartForm.sku_id" class="sr-only">
                                    
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-gray-700">{{ sku.name }}</span>
                                        <Check v-if="cartForm.sku_id === sku.id" :size="14" class="text-blue-600" />
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-xs text-gray-500">Stock: {{ sku.stock }}</span>
                                        <span class="font-bold text-blue-700">Bs {{ sku.price.toFixed(2) }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
    
                        <div v-if="currentSku">
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-xs font-bold text-gray-400 uppercase">Cantidad</label>
                                <span class="text-xs font-bold" :class="cartForm.quantity >= currentSku.stock ? 'text-red-500' : 'text-green-600'">
                                    {{ currentSku.stock }} disponibles
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-32">
                                    <button type="button" 
                                            class="p-3 hover:bg-gray-100 active:bg-gray-200 disabled:opacity-50 transition"
                                            @click="cartForm.quantity > 1 ? cartForm.quantity-- : null"
                                            :disabled="cartForm.quantity <= 1">
                                        <Minus :size="16" />
                                    </button>
                                    <input type="number" v-model="cartForm.quantity" 
                                           class="w-full text-center border-none focus:ring-0 font-bold text-gray-800 spin-hide" readonly>
                                    <button type="button" 
                                            class="p-3 hover:bg-gray-100 active:bg-gray-200 disabled:opacity-50 transition"
                                            @click="cartForm.quantity < currentSku.stock ? cartForm.quantity++ : null"
                                            :disabled="cartForm.quantity >= currentSku.stock">
                                        <Plus :size="16" />
                                    </button>
                                </div>
                                <div class="text-right flex-1">
                                    <p class="text-xs text-gray-400">Total Estimado</p>
                                    <p class="text-2xl font-black text-gray-800">Bs {{ (currentSku.price * cartForm.quantity).toFixed(2) }}</p>
                                </div>
                            </div>
                            
                            <p v-if="cartForm.errors.quantity" class="text-red-500 text-xs mt-2 flex items-center gap-1">
                                <AlertCircle :size="12" /> {{ cartForm.errors.quantity }}
                            </p>
                        </div>
    
                    </div>
    
                    <div class="p-6 border-t border-gray-100 bg-gray-50 flex gap-3">
                        <button @click="closeQuickView" type="button" class="flex-1 py-3 font-bold text-gray-500 hover:bg-gray-200 rounded-lg transition">
                            Cancelar
                        </button>
                        <button @click="addToCart" 
                                :disabled="cartForm.processing || !currentSku"
                                class="flex-[2] py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-600/30 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition transform active:scale-95">
                            <span v-if="cartForm.processing">Agregando...</span>
                            <span v-else class="flex items-center gap-2"><ShoppingCart :size="18" /> Agregar al Carrito</span>
                        </button>
                    </div>
    
                </div>
            </div>
    
        </ShopLayout>
    </template>
    
    <style scoped>
    /* Ocultar flechas de input number */
    .spin-hide::-webkit-outer-spin-button,
    .spin-hide::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    </style>