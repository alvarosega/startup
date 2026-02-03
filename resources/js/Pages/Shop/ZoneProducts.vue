<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Plus, Minus, PackageX, X, ShoppingCart, ChevronRight, ChevronLeft, Zap } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    groupedCategories: { type: Array, default: () => [] },
    targetCategory: [String, Number, null]
});

// --- LÓGICA DE AUTO-SCROLL BLINDADA ---
onMounted(() => {
    if (props.targetCategory) {
        nextTick(() => {
            // Intentamos encontrar el elemento específico de la subcategoría
            const targetId = props.targetCategory;
            console.log("Intentando scroll a categoría ID:", targetId);

            // Buscamos por el ID que generamos en el bucle: 'subcategory-{id}'
            let element = document.getElementById(`subcategory-${targetId}`);
            
            // Si no lo encontramos (quizás es un padre), buscamos por 'parent-{id}' (opcional si renderizas padres con ID)
            if (!element) {
                 // Fallback: buscar cualquier elemento que contenga ese ID en su dataset u otro atributo si fuera necesario
                 console.warn("Elemento no encontrado:", `subcategory-${targetId}`);
            }

            if (element) {
                console.log("Haciendo scroll a:", element);
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Efecto visual
                element.classList.add('flash-highlight');
                setTimeout(() => element.classList.remove('flash-highlight'), 2000);
            }
        });
    }
});

// ... (EL RESTO DE TU LÓGICA NO CAMBIA) ...
// Copia aquí todo lo demás: estados, computed, métodos del modal, etc.
const isModalOpen = ref(false);
const selectedProduct = ref(null);
const currentIndex = ref(0);
const quantity = ref(1);

const totalProductsCount = computed(() => {
    if (!props.groupedCategories || !Array.isArray(props.groupedCategories)) return 0;
    return props.groupedCategories.reduce((total, parent) => {
        const subs = parent.subcategories || [];
        const subTotal = subs.reduce((st, sub) => st + (sub.products?.length || 0), 0);
        return total + subTotal;
    }, 0);
});

const visibleCards = computed(() => {
    const variants = selectedProduct.value?.variants || [];
    const total = variants.length;
    if (total === 0) return [];
    if (total === 1) return [{ ...variants[0], position: 'center' }];
    const prevIndex = (currentIndex.value - 1 + total) % total;
    const nextIndex = (currentIndex.value + 1) % total;
    return [
        { ...variants[prevIndex], position: 'left', key: 'prev' },
        { ...variants[currentIndex.value], position: 'center', key: 'curr' },
        { ...variants[nextIndex], position: 'right', key: 'next' }
    ];
});

const activeSku = computed(() => {
    if (!selectedProduct.value?.variants) return null;
    return selectedProduct.value.variants[currentIndex.value];
});

const openProductModal = (product) => {
    selectedProduct.value = product;
    currentIndex.value = 0;
    quantity.value = 1;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => selectedProduct.value = null, 300);
};

const nextCard = () => {
    const total = selectedProduct.value?.variants.length || 0;
    currentIndex.value = (currentIndex.value + 1) % total;
    quantity.value = 1;
};

const prevCard = () => {
    const total = selectedProduct.value?.variants.length || 0;
    currentIndex.value = (currentIndex.value - 1 + total) % total;
    quantity.value = 1;
};

const increaseQty = () => quantity.value++;
const decreaseQty = () => { if (quantity.value > 1) quantity.value--; };

const addToCart = () => {
    if (!activeSku.value) return;
    router.post(route('cart.add'), {
        sku_id: activeSku.value.id,
        quantity: quantity.value
    }, {
        preserveScroll: true,
        only: ['cart_count', 'flash', 'shop_context'],
        onSuccess: () => closeModal()
    });
};

const goBack = () => router.visit(route('shop.index'));
</script>

<template>
    <ShopLayout>
        <Head :title="zone?.name || 'Zona'" />

        <div class="min-h-[calc(100vh-64px)] bg-slate-900 font-sans text-slate-100 pb-32">
            <div class="sticky top-0 z-30 bg-slate-900/95 backdrop-blur-md border-b border-white/10 px-4 py-3 shadow-lg">
                <div class="container mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="goBack" class="p-2 -ml-2 rounded-full hover:bg-white/10 transition active:scale-90 group">
                            <ArrowLeft class="w-6 h-6 text-white group-hover:-translate-x-1 transition-transform" />
                        </button>
                        <div>
                            <h1 class="font-display font-black text-xl leading-none tracking-tight text-white uppercase flex items-center gap-2">
                                {{ zone?.name }}
                            </h1>
                            <span class="text-[10px] text-emerald-400 font-bold tracking-widest uppercase">
                                {{ totalProductsCount }} Productos
                            </span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-8 h-8 rounded-full shadow-[0_0_15px_rgba(255,255,255,0.2)] border-2 border-white/20"
                         :style="{ backgroundColor: zone?.hex_color || '#333' }">
                    </div>
                </div>
            </div>

            <div class="container mx-auto py-6 space-y-12">
                <div v-for="parent in groupedCategories" :key="parent.id" class="space-y-6">
                    
                    <div class="px-4 sticky top-16 z-20 py-2 bg-slate-900/90 backdrop-blur-sm border-b border-white/5">
                        <h2 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 uppercase tracking-tight">
                            {{ parent.name }}
                        </h2>
                    </div>

                    <div v-for="(subcategory, index) in parent.subcategories" 
                         :key="subcategory.id" 
                         :id="`subcategory-${subcategory.id}`" 
                         class="transition-all duration-700 rounded-2xl"
                         :class="{'bg-white/5': targetCategory == subcategory.id}">
                        
                        <div class="px-4 mb-3 flex items-center gap-3 pt-2">
                            <div class="h-6 w-1 rounded-full shadow-[0_0_10px_currentColor]"
                                 :style="{ backgroundColor: zone?.hex_color || '#10b981' }"></div>
                            <h3 class="text-lg font-bold text-slate-200 tracking-wide">
                                {{ subcategory.name }}
                            </h3>
                        </div>

                        <div class="flex overflow-x-auto gap-4 px-4 pb-6 snap-x snap-mandatory scrollbar-hide scroll-smooth">
                            <div v-for="product in subcategory.products" :key="product.id" 
                                 @click="openProductModal(product)"
                                 class="snap-start shrink-0 w-[160px] md:w-[180px] flex flex-col bg-slate-800 rounded-2xl overflow-hidden border border-white/5 shadow-lg hover:shadow-xl hover:border-emerald-500/30 transition-all duration-300 group cursor-pointer active:scale-95 relative">
                                
                                <div class="aspect-[3/4] bg-white p-4 relative flex items-center justify-center overflow-hidden">
                                    <img :src="product.image_url" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 drop-shadow-sm" loading="lazy">
                                    <div v-if="!product.has_stock" class="absolute inset-0 bg-slate-900/70 backdrop-blur-[1px] flex flex-col items-center justify-center z-10">
                                        <span class="text-white font-black text-xs uppercase tracking-widest mb-1">Agotado</span>
                                        <div class="w-8 h-1 bg-red-500 rounded-full"></div>
                                    </div>
                                </div>

                                <div class="p-3 flex flex-col flex-1 bg-slate-800 relative border-t border-white/5">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1 truncate">
                                        {{ product.brand || 'Genérico' }}
                                    </p>
                                    <h3 class="text-xs font-bold text-slate-100 mb-2 leading-snug line-clamp-2 h-8">
                                        {{ product.name }}
                                    </h3>
                                    <div class="mt-auto flex items-center justify-between">
                                        <p class="text-emerald-400 font-black text-sm">Bs {{ product.price_display }}</p>
                                        <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center text-white/50 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                            <Plus class="w-3 h-3" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md animate-in fade-in duration-200" role="dialog">
            <div class="relative w-full max-w-sm h-[600px] flex items-center justify-center perspective-1000">
                <button @click="closeModal" class="absolute -top-12 right-0 p-2 text-white bg-white/10 rounded-full hover:bg-white/20 transition-colors z-50 border border-white/10"><X class="w-6 h-6" /></button>
                <button v-if="(selectedProduct?.variants.length || 0) > 1" @click="prevCard" class="absolute left-[-10px] sm:left-[-60px] z-40 p-3 bg-slate-800/80 hover:bg-emerald-500 text-white rounded-full backdrop-blur transition-all active:scale-90 border border-white/20"><ChevronLeft class="w-6 h-6" /></button>
                <button v-if="(selectedProduct?.variants.length || 0) > 1" @click="nextCard" class="absolute right-[-10px] sm:right-[-60px] z-40 p-3 bg-slate-800/80 hover:bg-emerald-500 text-white rounded-full backdrop-blur transition-all active:scale-90 border border-white/20"><ChevronRight class="w-6 h-6" /></button>
                
                <div class="relative w-full h-full flex items-center justify-center transform-style-3d">
                    <div v-for="card in visibleCards" :key="card.id + card.key"
                         class="absolute transition-all duration-500 ease-out-back w-[280px] sm:w-[320px]"
                         :class="{
                             'z-30 scale-100 opacity-100 translate-x-0': card.position === 'center',
                             'z-10 scale-90 opacity-40 -translate-x-[120%] blur-[2px]': card.position === 'left',
                             'z-10 scale-90 opacity-40 translate-x-[120%] blur-[2px]': card.position === 'right'
                         }">
                        
                        <div class="bg-slate-800 rounded-[24px] p-3 shadow-2xl border-4 border-slate-700 relative overflow-hidden flex flex-col h-full max-h-[500px]"
                             :class="card.has_stock ? 'border-emerald-500/50 shadow-emerald-900/50' : 'border-red-500/30 grayscale opacity-80'">
                            
                            <div class="flex justify-between items-center mb-2 px-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ selectedProduct?.brand }}</span>
                                <div v-if="!card.has_stock" class="flex items-center gap-1 text-red-500 font-bold text-[9px]"><PackageX class="w-3 h-3"/> AGOTADO</div>
                                <div v-else class="flex items-center gap-1 text-emerald-400 font-bold text-[9px]"><Zap class="w-3 h-3 fill-emerald-400"/> EN STOCK</div>
                            </div>

                            <div class="bg-white rounded-xl h-[220px] flex items-center justify-center p-4 shadow-inner relative overflow-hidden group mb-4">
                                <img :src="card.image_url" class="w-full h-full object-contain drop-shadow-xl z-10 transition-transform duration-500 hover:scale-110">
                            </div>

                            <div class="flex-1 flex flex-col text-center px-2">
                                <h3 class="text-lg font-black text-white leading-tight mb-1 line-clamp-2">{{ card.name }}</h3>
                                <p class="text-[10px] text-slate-500 mb-4">{{ card.code }}</p>

                                <div class="bg-slate-900/50 rounded-lg p-3 border border-white/5 mb-auto">
                                    <span class="text-3xl font-black text-emerald-400 tracking-tight">Bs {{ parseFloat(card.price).toFixed(2) }}</span>
                                </div>

                                <div v-if="card.has_stock" class="mt-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button @click="decreaseQty" class="w-12 h-12 rounded-xl bg-slate-700 hover:bg-slate-600 text-white flex items-center justify-center border border-white/10 active:scale-95"><Minus/></button>
                                        <div class="flex-1 h-12 bg-slate-950 rounded-xl flex items-center justify-center border border-white/10">
                                            <span class="text-xl font-bold text-white">{{ quantity }}</span>
                                        </div>
                                        <button @click="increaseQty" class="w-12 h-12 rounded-xl bg-slate-700 hover:bg-slate-600 text-white flex items-center justify-center border border-white/10 active:scale-95"><Plus/></button>
                                    </div>
                                    <button @click="addToCart" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-black uppercase tracking-widest text-sm rounded-xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                                        <ShoppingCart class="w-5 h-5" /> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </ShopLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.perspective-1000 { perspective: 1000px; }
.transform-style-3d { transform-style: preserve-3d; }
.ease-out-back { transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1); }

@keyframes flashHighlight {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(255, 255, 255, 0.15); border-color: rgba(255,255,255,0.2); }
}
.flash-highlight {
    animation: flashHighlight 1.5s ease-out;
}
</style>