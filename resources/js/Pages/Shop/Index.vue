<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BundleModal from '@/Components/Shop/BundleModal.vue';
import { Zap, Package, ChevronRight, LayoutGrid, Hand } from 'lucide-vue-next';

const props = defineProps({ 
    zonesData: Object, 
    products: Object,
    bundlesData: Array
});

// --- 1. GESTIÓN DE DATOS ---
const sourceZones = computed(() => {
    let zones = props.zonesData ? Object.values(props.zonesData) : [];
    if (props.bundlesData && props.bundlesData.length > 0) {
        const bundleZone = {
            id: 'virtual-bundles',
            name: 'Packs & Ofertas',
            slug: 'packs-ofertas',
            color: '#F59E0B', // Amber for Offers
            aisles: props.bundlesData 
        };
        return [bundleZone, ...zones];
    }
    return zones;
});

// --- 2. MODALES Y NAVEGACIÓN ---
const showBundleModal = ref(false);
const activeBundleSlug = ref(null);
const isEntering = ref(false);
const hintingCardKey = ref(null); // Estado para la animación de "Pista"
const enteringCardKey = ref(null);

const openBundle = (slug) => {
    activeBundleSlug.value = slug;
    showBundleModal.value = true;
};

const enterZone = (zone, categoryId = null, cardKey = null) => {
    if (!zone || isEntering.value || zone.id === 'virtual-bundles') return;
    
    // Activar efecto visual de marco seleccionado
    if (cardKey !== null) enteringCardKey.value = cardKey;

    isEntering.value = true;
    const params = { zone: zone.slug };
    if (categoryId) params.category = categoryId;

    router.visit(route('shop.zone', params), {
        onFinish: () => isEntering.value = false
    });
};

// --- 3. INTERACCIÓN (CLICS Y PISTAS) ---

// A. Clic en un Item (Producto/Pack) - Acción Directa
const handleItemClick = (item, zoneData, cardKey) => {
    // CORRECCIÓN: Validamos que 'item' exista antes de leer sus propiedades
    if (!item || !isClick.value) return; 

    if (item.type === 'bundle') openBundle(item.slug);
    else enterZone(zoneData, item.id, cardKey);
};

// B. Clic en el Fondo/Marco (Feedback Educativo)
const handleBackgroundInteraction = (card) => {
    if (!isClick.value) return; // Si arrastró, ignorar

    // Disparar animación de "Pista" (Hint)
    hintingCardKey.value = card.key;
    
    // Quitar la pista después de 600ms
    setTimeout(() => {
        hintingCardKey.value = null;
    }, 600);

    // Si NO es un bundle, y el clic fue en el Footer/Header explícito, entramos.
    // Pero si es en el medio (scroller area vacía), solo mostramos el hint.
    // (Esta lógica se refina en el template con @click.stop en áreas específicas)
};

// C. Clic en el botón "Ir" (Pasamos cardKey)
const handleFooterClick = (card) => {
    if (card.data.id !== 'virtual-bundles') {
        enterZone(card.data, null, card.key); // <--- Pasamos el Key
    }
};

// --- 4. MOTOR DE SCROLL INFINITO (Core Restaurado) ---
const CARD_HEIGHT = 480; 
const PEEK_HEIGHT = 110; 
const VISIBLE_COUNT = 4; 
const ITEM_WIDTH = 150; 
const ITEM_GAP = 16;    
const TOTAL_ITEM_WIDTH = ITEM_WIDTH + ITEM_GAP;
const AUTO_SCROLL_SPEED = 0.5; 

// Estado Reactivo del Motor
const virtualIndex = ref(1000); 
const isInteracting = ref(false); 
const startX = ref(0);
const startY = ref(0);
const currentDragX = ref(0); 
const currentDragY = ref(0); 
const gestureAxis = ref(null); 
const isClick = ref(true); 

let horizOffset = 0; 
let animationFrameId = null;

const getZoneData = (vIndex) => {
    if (sourceZones.value.length === 0) return null;
    const realIndex = vIndex % sourceZones.value.length;
    return sourceZones.value[realIndex < 0 ? realIndex + sourceZones.value.length : realIndex];
};

const renderedCards = computed(() => {
    const cards = [];
    for (let i = -1; i < VISIBLE_COUNT; i++) {
        cards.push({
            offset: i, 
            data: getZoneData(virtualIndex.value + i),
            key: virtualIndex.value + i
        });
    }
    return cards;
});

// Asegúrate que esta función mantenga el multiplicador alto (12 o más)
const getLoopedAisles = (aisles) => {
    if (!aisles || aisles.length === 0) return [];
    // CORRECCIÓN: Añadimos .filter(Boolean) para eliminar cualquier 'undefined' o 'null' fantasma
    return Array(12).fill(aisles).flat().filter(Boolean);
};
const updateScrollerTransform = () => {
    // CORRECCIÓN: Buscamos dinámicamente el scroller de la carta activa (offset 0)
    const activeCardScroller = document.getElementById('scroller-0');
    
    if (activeCardScroller) {
        activeCardScroller.style.transform = `translate3d(${horizOffset}px, 0, 0)`;
    }
};

const startAutoScroll = () => {
    cancelAnimationFrame(animationFrameId);
    
    const currentZone = getZoneData(virtualIndex.value);
    // Validación de seguridad: si no hay datos, no hacemos nada
    if (!currentZone || !currentZone.aisles || currentZone.aisles.length === 0) return;

    // Cálculo preciso del ancho de UN set original de productos
    const singleSetWidth = currentZone.aisles.length * TOTAL_ITEM_WIDTH;
    
    // Punto de anclaje: El centro matemático del array clonado (x12). 
    // Nos situamos al inicio del set #6.
    const idealCenter = -(singleSetWidth * 6);

    // Si es la primera carga o reseteo, forzamos la posición al centro
    if (horizOffset === 0 || Math.abs(horizOffset) < 100) {
        horizOffset = idealCenter;
    }

    const animate = () => {
        // Pausar si hay modal abierto o cambiando de zona
        if (isEntering.value || showBundleModal.value) return;

        // Movimiento automático solo si el usuario NO está tocando
        if (!isInteracting.value) {
            horizOffset -= AUTO_SCROLL_SPEED;
        }

        // --- CORRECCIÓN MATEMÁTICA DEL LOOP INFINITO ---
        // Lógica: Si nos alejamos más de "un set" del centro, reseteamos.
        
        // 1. Si scrolleamos mucho a la izquierda (avanzamos)
        if (horizOffset < idealCenter - singleSetWidth) {
            horizOffset += singleSetWidth; // Salto invisible atrás
        }
        // 2. Si scrolleamos mucho a la derecha (retrocedemos)
        else if (horizOffset > idealCenter + singleSetWidth) {
            horizOffset -= singleSetWidth; // Salto invisible adelante
        }

        updateScrollerTransform();
        animationFrameId = requestAnimationFrame(animate);
    };
    
    animationFrameId = requestAnimationFrame(animate);
};

// --- 5. GESTOS TÁCTILES (Touch System) ---
const handleTouchStart = (e) => {
    if (isEntering.value || showBundleModal.value) return;
    isInteracting.value = true;
    isClick.value = true;
    gestureAxis.value = null;
    startX.value = e.touches[0].clientX;
    startY.value = e.touches[0].clientY;
    currentDragX.value = 0;
    currentDragY.value = 0;
};

const handleTouchMove = (e) => {
    if (!isInteracting.value || isEntering.value) return;
    const deltaX = e.touches[0].clientX - startX.value;
    const deltaY = e.touches[0].clientY - startY.value;

    // Si mueve más de 8px, ya no es un clic
    if (Math.abs(deltaX) > 8 || Math.abs(deltaY) > 8) isClick.value = false;

    // Decidir eje del gesto
    if (!gestureAxis.value) {
        if (Math.abs(deltaX) > Math.abs(deltaY)) gestureAxis.value = 'x';
        else if (Math.abs(deltaY) > Math.abs(deltaX)) gestureAxis.value = 'y';
    }

    // Mover Scroll Horizontal
    if (gestureAxis.value === 'x') {
        horizOffset += (deltaX - currentDragX.value);
        currentDragX.value = deltaX;
        updateScrollerTransform();
    } 
    // Mover Carta Vertical (Previsualización)
    else if (gestureAxis.value === 'y') {
        currentDragY.value = deltaY;
    }
};

const handleTouchEnd = () => {
    isInteracting.value = false; // Esto reactiva el 'if (!isInteracting)' dentro del loop
    
    // Si fue un Swipe Vertical (Cambio de carta)
    if (!isClick.value && gestureAxis.value === 'y') {
        if (currentDragY.value < -80) { // Arriba
            virtualIndex.value++;
            // IMPORTANTE: Resetear horizOffset a 0 forzará el recálculo del centro en startAutoScroll
            horizOffset = 0; 
            nextTick(() => startAutoScroll());
        } 
        else if (currentDragY.value > 80) { // Abajo
            virtualIndex.value--;
            horizOffset = 0;
            nextTick(() => startAutoScroll());
        }
    }
    
    currentDragY.value = 0;
    currentDragX.value = 0;
    gestureAxis.value = null;
};

// --- 6. ESTILOS 3D (Visual Engine) ---
const getCardStyle = (offset) => {
    let drag = 0;
    if (gestureAxis.value === 'y') drag = currentDragY.value;
    
    let yPos = 0; let zPos = 0; let scale = 1; let opacity = 1; 
    let zIndex = 30 - Math.abs(offset); 

    if (isEntering.value && offset === 0) {
        return {
            transform: `translate3d(0, 0, 0) scale(1.05)`,
            opacity: 0, zIndex: 30, transition: 'all 0.3s ease-out'
        };
    }

    if (offset === -1) { // Carta saliente
        scale = 1.1; zPos = 200; yPos = -80 + drag; opacity = drag > 0 ? (drag / 300) : 0;
        if (drag > 0) { scale = 1.1 - (drag / 1000); zPos = 200 - drag; }
        if (drag <= 0) return { display: 'none' }; 
    }
    else if (offset === 0) { // Carta ACTIVA
        yPos = drag * 0.4; 
        if (drag < 0) { // Elasticidad al tirar hacia arriba
            const progress = Math.min(Math.abs(drag) / 300, 1);
            scale = 1 + (progress * 0.1); opacity = 1 - progress; zPos = progress * 100;        
        }
    }
    else { // Stack inferior
        yPos = (offset * (PEEK_HEIGHT - 10)) + (drag * 0.2);
        zPos = -offset * 120; 
        scale = 1 - (offset * 0.05); 
        opacity = 1 - (offset * 0.1); 
    }

    return {
        transform: `translate3d(0, ${yPos}px, ${zPos}px) scale(${scale})`,
        opacity: Math.max(0, opacity),
        zIndex: zIndex,
        transition: isInteracting.value ? 'none' : 'all 0.5s cubic-bezier(0.2, 0.9, 0.1, 1)'
    };
};

onMounted(() => startAutoScroll());
onUnmounted(() => cancelAnimationFrame(animationFrameId));
</script>

<template>
    <ShopLayout>
        <Head title="Explorar Tienda" />
        
        <BundleModal 
            :show="showBundleModal" 
            :bundle-slug="activeBundleSlug" 
            @close="showBundleModal = false"
        />

        <div class="w-full h-[calc(100svh-4rem)] bg-background overflow-hidden relative flex flex-col items-center pt-4 touch-none select-none"
             @touchstart="handleTouchStart" 
             @touchmove="handleTouchMove" 
             @touchend="handleTouchEnd">

            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 bg-grid opacity-[0.05]"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-background via-transparent to-background/90"></div>
                <div class="absolute top-[-20%] left-1/2 -translate-x-1/2 w-[150%] h-[60%] rounded-full blur-[120px] opacity-30 transition-colors duration-700"
                     :style="{ backgroundColor: getZoneData(virtualIndex)?.color || 'var(--primary)' }">
                </div>
            </div>
            
            <div class="z-10 mb-2 flex flex-col items-center pointer-events-none">
                <div class="flex items-center gap-2 mb-2 text-primary/70">
                    <LayoutGrid :size="10" />
                    <h2 class="text-[9px] font-black tracking-[0.3em] uppercase">SYSTEM NAV</h2>
                </div>
                <div class="flex gap-1.5 shadow-sm p-1 rounded-full bg-black/40 backdrop-blur-md border border-white/5">
                    <div v-for="(_, idx) in sourceZones" :key="idx"
                         class="h-1 rounded-full transition-all duration-300 shadow-[0_0_8px_currentColor]"
                         :class="(virtualIndex % sourceZones.length) === idx ? 'w-6 bg-primary' : 'w-1.5 bg-white/20'">
                    </div>
                </div>
            </div>

            <div class="relative w-full max-w-[380px] h-full perspective-container">
                
                <div v-for="card in renderedCards" :key="card.key"
                     class="absolute left-0 right-0 w-full px-4 will-change-transform origin-center"
                     :style="{ 
                         height: `${CARD_HEIGHT}px`,
                         ...getCardStyle(card.offset) 
                     }">
                    
                    <div class="relative w-full h-full rounded-[32px] overflow-hidden flex flex-col transition-all duration-200 border backdrop-blur-xl bg-[#0B1221]/80"
                        :class="[
                            // 1. ESTADO DE ACTIVACIÓN (Click o Pista)
                            (enteringCardKey === card.key || hintingCardKey === card.key)
                                ? 'border-primary ring-2 ring-inset ring-primary shadow-[0_0_40px_rgba(0,240,255,0.4)] z-50'
                                
                                // 2. ESTADO NORMAL
                                : (card.offset === 0 
                                    ? 'border-white/10 ring-1 ring-inset ring-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)]' 
                                    : 'border-white/5 opacity-80 grayscale')
                        ]"
                        @click.stop="handleBackgroundInteraction(card)">
                        <div class="px-6 pt-6 pb-2 relative z-20 flex justify-between items-start">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5"
                                      :class="card.data.id === 'virtual-bundles' ? 'text-amber-400 drop-shadow-sm' : 'text-primary drop-shadow-sm'">
                                      <Zap :size="10" class="fill-current" />
                                      {{ card.data.id === 'virtual-bundles' ? 'FLASH OFFERS' : 'ZONE DATA' }}
                                </span>
                                <span class="text-xs text-muted-foreground font-medium mt-0.5">
                                    {{ card.data.aisles?.length || 0 }} Categorías
                                </span>
                            </div>
                            <span class="text-5xl font-display font-black text-white/5 tracking-tighter leading-none italic">
                                0{{ (card.key % sourceZones.length) + 1 }}
                            </span>
                        </div>
                        
                        <div class="flex-1 relative overflow-hidden mt-1 bg-gradient-to-b from-transparent to-black/30">
                            <div class="absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-[#0B1221] to-transparent z-10 pointer-events-none"></div>
                            <div class="absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-[#0B1221] to-transparent z-10 pointer-events-none"></div>

                            <div :id="'scroller-' + card.offset" 
                                class="absolute top-0 left-0 h-full flex items-center px-4 will-change-transform"
                                :class="{ 'pointer-events-none': card.offset !== 0 }">
                                                            
                                <div v-for="(aisle, idx) in getLoopedAisles(card.data.aisles)" :key="idx"
                                     class="shrink-0 relative active:scale-95 transition-transform group px-2"
                                     :style="{ width: `${TOTAL_ITEM_WIDTH}px` }"
                                     @click.stop="handleItemClick(aisle, card.data, card.key)">
                                    
                                    <div class="w-full bg-[#151e32] rounded-2xl overflow-hidden border transition-all duration-300 shadow-lg relative group-hover:scale-[1.02]"
                                         :class="[
                                            // Bordes base
                                            aisle.type === 'bundle' 
                                                ? 'border-amber-500/30' 
                                                : 'border-white/10',
                                            // Animación de Pista (Hint)
                                            hintingCardKey === card.key 
                                                ? (aisle.type === 'bundle' ? 'ring-2 ring-amber-500 animate-pulse' : 'ring-2 ring-primary animate-pulse') 
                                                : ''
                                         ]">
                                        
                                        <div class="w-full h-[140px] relative overflow-hidden bg-black/40">
                                            <img v-if="aisle.image_url" :src="aisle.image_url" class="w-full h-full object-cover opacity-90" loading="lazy">
                                            <div v-else class="w-full h-full flex items-center justify-center text-white/20"><Package :size="24" /></div>
                                            
                                            <div v-if="aisle.type === 'bundle'" class="absolute top-2 right-2 bg-amber-500 text-black text-[9px] font-black px-2 py-0.5 rounded shadow-sm flex items-center gap-1">
                                                <Zap :size="8" class="fill-black" /> PACK
                                            </div>
                                        </div>
                                        
                                        <div class="p-3 h-[70px] flex flex-col justify-between relative">
                                            <h3 class="text-xs font-bold text-gray-200 leading-tight line-clamp-2">
                                                {{ aisle.name }}
                                            </h3>
                                            
                                            <div class="flex items-center justify-between mt-1">
                                                <span v-if="aisle.type === 'bundle'" class="text-[10px] text-amber-400 font-mono font-bold tracking-tight">
                                                    {{ aisle.price_display }}
                                                </span>
                                                <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center border border-white/5 transition-colors"
                                                     :class="hintingCardKey === card.key ? 'bg-white text-black' : ''">
                                                    <component :is="aisle.type === 'bundle' ? Hand : ChevronRight" :size="14" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="relative shrink-0 flex items-center px-6 transition-all duration-300 overflow-hidden border-t border-white/5"
                             :style="{ 
                                 height: `${PEEK_HEIGHT}px`,
                                 background: `linear-gradient(to right, ${card.data.color}20, #0B1221)`
                             }">
                            
                            <img v-if="card.data.aisles?.[0]?.image_url" :src="card.data.aisles[0].image_url" 
                                 class="absolute inset-0 w-full h-full object-cover opacity-10 blur-md pointer-events-none grayscale mix-blend-overlay">

                            <div class="relative z-10 w-full flex justify-between items-center pointer-events-none pb-2">
                                <div>
                                    <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-0.5">
                                        {{ card.data.id === 'virtual-bundles' ? 'Oportunidad' : 'Explorar' }}
                                    </p>
                                    <h2 class="text-3xl font-display font-black text-white uppercase italic leading-[0.85] tracking-tighter w-[180px] break-words drop-shadow-lg">
                                        {{ card.data.name }}
                                    </h2>
                                </div>

                                <div v-if="card.data.id !== 'virtual-bundles'" 
                                    class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 shadow-xl border border-white/10 pointer-events-auto cursor-pointer active:scale-95"
                                    :class="card.offset === 0 ? 'bg-white text-black hover:scale-110' : 'bg-white/5 text-white/30'"
                                    @click.stop="handleFooterClick(card)"> <ChevronRight v-if="card.offset === 0" :size="24" stroke-width="3" />
                                    <span v-else class="text-[9px] font-black uppercase -rotate-90">Ir</span>
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
.perspective-container { perspective: 1000px; transform-style: preserve-3d; }
.touch-none { touch-action: none; }
</style>