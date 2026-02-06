<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { ArrowLeft, Plus, Minus, PackageX, X, ShoppingCart, ChevronRight, ChevronLeft, Zap, Layers } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    groupedCategories: { type: Array, default: () => [] },
    targetCategory: [String, Number, null]
});

// --- LÓGICA DE AUTO-SCROLL INICIAL ---
onMounted(() => {
    if (props.targetCategory) {
        nextTick(() => {
            const targetId = props.targetCategory;
            let element = document.getElementById(`subcategory-${targetId}`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                element.classList.add('flash-highlight');
                setTimeout(() => element.classList.remove('flash-highlight'), 2000);
            }
        });
    }
    // Inicializar scroll infinito en las filas
    initInfiniteScrollRows();
});

// --- LOGICA DE SCROLL INFINITO PARA FILAS (PRODUCT CARDS) ---
// Triplicamos los productos para crear el buffer [Copia Izq] [Original] [Copia Der]
// CAMBIO: Multiplicador x4 para asegurar buffer suficiente a ambos lados
const getInfiniteProducts = (products) => {
    if (!products || products.length === 0) return [];
    // Clonamos 4 veces: [Buffer Izq] [Zona Visible] [Zona Visible] [Buffer Der]
    return Array(4).fill(products).flat().map((p, i) => ({ ...p, uniqueKey: `${p.id}-${i}` }));
};
const initInfiniteScrollRows = () => {
    const containers = document.querySelectorAll('.infinite-scroll-row');
    
    containers.forEach(container => {
        // Esperamos a que se renderice para medir
        nextTick(() => {
            if (container.children.length === 0) return;

            // Medimos el primer hijo (tarjeta) y el gap del contenedor (16px = gap-4)
            const itemWidth = container.children[0].offsetWidth;
            const gap = 16; 
            const totalItemWidth = itemWidth + gap;
            
            // Calculamos el ancho de UN set original (total items / 4 copias)
            const totalItems = container.children.length;
            const originalSetCount = totalItems / 4;
            const singleSetWidth = originalSetCount * totalItemWidth;

            // Posición Inicial: Al inicio del Set 2 (Centro-Izquierda)
            container.scrollLeft = singleSetWidth;

            let isResetting = false;

            container.addEventListener('scroll', () => {
                if (isResetting) return;

                // Si pasamos el final del Set 2 (hacia la derecha) -> saltamos al Set 1
                if (container.scrollLeft >= singleSetWidth * 2) {
                    isResetting = true;
                    container.scrollLeft -= singleSetWidth;
                    setTimeout(() => isResetting = false, 0); // Micro-task para liberar
                }
                // Si pasamos el inicio del Set 1 (hacia la izquierda) -> saltamos al Set 2
                else if (container.scrollLeft <= 0) {
                    isResetting = true;
                    container.scrollLeft += singleSetWidth;
                    setTimeout(() => isResetting = false, 0);
                }
            });
        });
    });
};

// --- LOGICA DEL MODAL ---
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

// Visible cards para el efecto 3D del modal
const visibleCards = computed(() => {
    const variants = selectedProduct.value?.variants || [];
    const total = variants.length;
    if (total === 0) return [];
    if (total === 1) return [{ ...variants[0], position: 'center' }];
    
    // Lógica circular (Infinita)
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

        <div class="min-h-[calc(100vh-64px)] bg-background font-sans text-foreground pb-32 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 right-0 h-[60vh] opacity-20 pointer-events-none z-0 mix-blend-screen"
                 :style="{ background: `radial-gradient(circle at 50% -20%, ${zone?.hex_color || 'var(--primary)'}, transparent 70%)` }">
            </div>
            <div class="absolute inset-0 bg-grid opacity-[0.05] pointer-events-none z-0"></div>

            <header class="sticky top-0 z-40 bg-background/80 backdrop-blur-xl border-b border-border/10 px-4 py-3 shadow-lg transition-colors duration-300">
                <div class="container mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="goBack" class="w-10 h-10 flex items-center justify-center rounded-full bg-card/50 border border-border/10 hover:bg-card transition active:scale-90 group backdrop-blur-md">
                            <ArrowLeft class="w-5 h-5 text-foreground group-hover:-translate-x-0.5 transition-transform" stroke-width="2.5" />
                        </button>
                        <div class="flex flex-col">
                            <h1 class="font-display font-black text-xl leading-[0.9] tracking-tight text-foreground uppercase italic drop-shadow-sm">
                                {{ zone?.name }}
                            </h1>
                            <span class="text-[9px] text-primary font-bold tracking-[0.2em] uppercase mt-0.5 flex items-center gap-1">
                                <Layers :size="10" /> {{ totalProductsCount }} ITEMS
                            </span>
                        </div>
                    </div>
                    <div class="hidden sm:block relative">
                        <div class="absolute inset-0 rounded-full blur-md opacity-50" :style="{ backgroundColor: zone?.hex_color }"></div>
                        <div class="relative w-6 h-6 rounded-full border-2 border-white/20 shadow-inner"
                             :style="{ backgroundColor: zone?.hex_color || '#333' }">
                        </div>
                    </div>
                </div>
            </header>

            <div class="container mx-auto py-6 space-y-10 relative z-10">
                <div v-for="parent in groupedCategories" :key="parent.id" class="space-y-4">
                    
                    <div class="px-4 sticky top-[68px] z-30 py-3 bg-background/95 backdrop-blur-md border-y border-border/10 shadow-sm">
                        <h2 class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-foreground via-foreground/80 to-transparent uppercase tracking-tighter italic">
                            {{ parent.name }}
                        </h2>
                    </div>

                    <div v-for="(subcategory, index) in parent.subcategories" 
                         :key="subcategory.id" 
                         :id="`subcategory-${subcategory.id}`" 
                         class="transition-all duration-700 rounded-3xl border border-transparent scroll-mt-32"
                         :class="{'bg-primary/5 border-primary/20 shadow-[0_0_30px_rgba(0,0,0,0.05)]': targetCategory == subcategory.id}">
                        
                        <div class="px-5 mb-3 flex items-center gap-3 pt-2">
                            <div class="h-1.5 w-1.5 rounded-full shadow-[0_0_8px_currentColor]"
                                 :style="{ backgroundColor: zone?.hex_color || 'var(--primary)' }"></div>
                            <h3 class="text-sm font-bold text-foreground tracking-widest uppercase">
                                {{ subcategory.name }}
                            </h3>
                        </div>

                        <div class="infinite-scroll-row flex overflow-x-auto gap-4 px-4 pb-8 scrollbar-hide snap-x snap-mandatory">
                            
                            <div v-for="product in getInfiniteProducts(subcategory.products)" :key="product.uniqueKey" 
                                 @click="openProductModal(product)"
                                 class="shrink-0 w-[160px] md:w-[170px] flex flex-col rounded-2xl overflow-hidden border transition-all duration-300 group cursor-pointer active:scale-95 relative
                                        backdrop-blur-md border-border/50 hover:border-primary/50
                                        bg-gradient-to-b from-card via-card to-muted/50 dark:bg-none dark:bg-[#0B1221]/60
                                        shadow-md hover:shadow-xl">
                                
                                <div class="aspect-[4/5] p-4 relative flex items-center justify-center overflow-hidden">
                                    <img :src="product.image_url" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 drop-shadow-xl filter dark:brightness-90 dark:group-hover:brightness-110" loading="lazy">
                                    
                                    <div v-if="!product.has_stock" class="absolute inset-0 bg-background/60 backdrop-blur-[2px] flex flex-col items-center justify-center z-10 border-2 border-red-500/20 m-1 rounded-xl">
                                        <PackageX class="w-8 h-8 text-red-500 mb-2" />
                                        <span class="text-red-500 font-black text-[10px] uppercase tracking-widest">Agotado</span>
                                    </div>
                                </div>

                                <div class="p-3 flex flex-col flex-1 relative border-t border-border/10">
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1 truncate">
                                        {{ product.brand || 'Original' }}
                                    </p>
                                    
                                    <h3 class="text-sm font-black text-foreground mb-4 leading-snug line-clamp-2 group-hover:text-primary transition-colors uppercase tracking-tight">
                                        {{ product.name }}
                                    </h3>
                                    
                                    <div class="mt-auto">
                                        <div class="w-full py-2 bg-primary/10 hover:bg-primary text-primary hover:text-black font-bold text-[10px] uppercase tracking-widest rounded-lg transition-all duration-300 flex items-center justify-center gap-2 border border-primary/20 group-hover:border-primary group-hover:shadow-[0_0_15px_rgba(0,240,255,0.4)]">
                                            <span>Ver Opciones</span>
                                            <ChevronRight class="w-3 h-3" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-background/80 backdrop-blur-xl animate-in fade-in duration-300" role="dialog">
            
            <div class="relative w-full max-w-sm h-[650px] flex flex-col items-center justify-center perspective-1000">
                
                <div class="absolute top-0 z-50 flex gap-1.5 p-2 bg-card/80 backdrop-blur rounded-full border border-border/20 shadow-lg mb-4">
                    <div v-for="(v, idx) in selectedProduct?.variants" :key="idx" 
                         class="h-1.5 rounded-full transition-all duration-300"
                         :class="currentIndex === idx ? 'w-6 bg-primary shadow-[0_0_8px_currentColor]' : 'w-1.5 bg-muted-foreground/30'">
                    </div>
                </div>

                <button @click="closeModal" class="absolute -top-10 right-0 p-3 text-foreground hover:text-primary bg-card/50 rounded-full transition-all z-50 border border-border/10 active:scale-90 shadow-sm">
                    <X class="w-6 h-6" />
                </button>

                <button v-if="(selectedProduct?.variants.length || 0) > 1" @click="prevCard" class="absolute left-[-15px] sm:left-[-60px] z-40 p-3 bg-card/50 hover:bg-primary hover:text-black text-foreground rounded-full backdrop-blur transition-all active:scale-90 border border-border/10 shadow-lg top-1/2 -translate-y-1/2">
                    <ChevronLeft class="w-6 h-6" />
                </button>
                <button v-if="(selectedProduct?.variants.length || 0) > 1" @click="nextCard" class="absolute right-[-15px] sm:right-[-60px] z-40 p-3 bg-card/50 hover:bg-primary hover:text-black text-foreground rounded-full backdrop-blur transition-all active:scale-90 border border-border/10 shadow-lg top-1/2 -translate-y-1/2">
                    <ChevronRight class="w-6 h-6" />
                </button>
                
                <div class="relative w-full h-full flex items-center justify-center transform-style-3d mt-8">
                    <div v-for="card in visibleCards" :key="card.id + card.key"
                         class="absolute transition-all duration-500 ease-out-back w-[280px] sm:w-[320px]"
                         :class="{
                             'z-30 scale-100 opacity-100 translate-x-0': card.position === 'center',
                             'z-10 scale-90 opacity-40 -translate-x-[110%] blur-[1px] rotate-y-12': card.position === 'left',
                             'z-10 scale-90 opacity-40 translate-x-[110%] blur-[1px] -rotate-y-12': card.position === 'right'
                         }">
                        
                        <div class="bg-card dark:bg-[#0B1221] rounded-[32px] p-1.5 shadow-2xl relative overflow-hidden flex flex-col h-full max-h-[520px] transition-all duration-300 border border-border/10"
                             :class="[
                                card.has_stock ? 'ring-1 ring-border/20' : 'ring-1 ring-red-500/30 grayscale opacity-80',
                                card.position === 'center' ? 'shadow-[0_20px_50px_-10px_rgba(0,0,0,0.5)]' : ''
                             ]">
                            
                            <div class="flex justify-between items-center mb-3 px-4 pt-3">
                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">{{ selectedProduct?.brand }}</span>
                                <div v-if="!card.has_stock" class="flex items-center gap-1.5 text-red-500 font-black text-[10px] bg-red-500/10 px-2 py-1 rounded"><PackageX class="w-3 h-3"/> AGOTADO</div>
                                <div v-else class="flex items-center gap-1.5 text-primary font-black text-[10px] bg-primary/10 px-2 py-1 rounded shadow-[0_0_10px_rgba(0,240,255,0.2)]"><Zap class="w-3 h-3 fill-primary"/> EN STOCK</div>
                            </div>

                            <div class="bg-gradient-to-br from-muted/50 to-transparent rounded-[24px] h-[240px] flex items-center justify-center p-6 relative overflow-hidden group mb-4 border border-border/5">
                                <img :src="card.image_url" class="w-full h-full object-contain drop-shadow-2xl z-10 transition-transform duration-500 hover:scale-110 filter dark:brightness-95">
                            </div>

                            <div class="flex-1 flex flex-col text-center px-4 pb-4">
                                <h3 class="text-xl font-black text-foreground leading-none mb-1 line-clamp-2 tracking-tight">{{ card.name }}</h3>
                                <p class="text-[10px] text-muted-foreground font-mono mb-5">{{ card.code }}</p>

                                <div class="bg-muted/30 rounded-xl p-3 border border-border/10 mb-auto flex flex-col items-center justify-center">
                                    <span class="text-xs text-muted-foreground uppercase font-bold tracking-widest mb-1">Precio Unitario</span>
                                    <span class="text-3xl font-black text-primary tracking-tighter drop-shadow-sm">Bs {{ parseFloat(card.price).toFixed(2) }}</span>
                                </div>

                                <div v-if="card.has_stock" class="mt-5 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <button @click="decreaseQty" class="w-12 h-12 rounded-xl bg-card hover:bg-muted text-foreground flex items-center justify-center border border-border/20 active:scale-95 transition-colors shadow-sm"><Minus/></button>
                                        <div class="flex-1 h-12 bg-muted/50 rounded-xl flex items-center justify-center border border-border/10">
                                            <span class="text-xl font-black text-foreground font-mono">{{ quantity }}</span>
                                        </div>
                                        <button @click="increaseQty" class="w-12 h-12 rounded-xl bg-card hover:bg-muted text-foreground flex items-center justify-center border border-border/20 active:scale-95 transition-colors shadow-sm"><Plus/></button>
                                    </div>
                                    
                                    <button @click="addToCart" class="w-full py-4 bg-primary hover:bg-primary/90 text-black font-black uppercase tracking-widest text-sm rounded-xl shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2 hover:shadow-primary/30">
                                        <ShoppingCart class="w-5 h-5" /> Agregar al Pedido
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
    0% { border-color: transparent; box-shadow: none; }
    50% { border-color: var(--primary); box-shadow: 0 0 20px var(--primary); }
    100% { border-color: transparent; box-shadow: none; }
}
.flash-highlight {
    animation: flashHighlight 1.5s ease-out;
}
</style>