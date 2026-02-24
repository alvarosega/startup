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

// --- GESTIÓN DE IDENTIDAD (ZERO-TRUST) ---
const getGuestUuid = () => {
    let uuid = localStorage.getItem('guest_client_uuid');
    if (!uuid) {
        uuid = crypto.randomUUID();
        localStorage.setItem('guest_client_uuid', uuid);
    }
    return uuid;
};

// --- GESTIÓN DE DATOS ---
const sourceZones = computed(() => {
    let zones = props.zonesData ? Object.values(props.zonesData) : [];
    if (props.bundlesData?.length > 0) {
        const bundleZone = {
            id: 'virtual-bundles',
            name: 'Packs & Ofertas',
            slug: 'packs-ofertas',
            color: '#F59E0B',
            aisles: props.bundlesData 
        };
        return [bundleZone, ...zones];
    }
    return zones;
});

const showBundleModal = ref(false);
const activeBundleSlug = ref(null);
const isEntering = ref(false);
const hintingCardKey = ref(null);
const enteringCardKey = ref(null);

const openBundle = (slug) => {
    activeBundleSlug.value = slug;
    showBundleModal.value = true;
};

const enterZone = (zone, categoryId = null, cardKey = null) => {
    if (!zone || isEntering.value || zone.id === 'virtual-bundles') return;
    
    if (cardKey !== null) enteringCardKey.value = cardKey;
    isEntering.value = true;

    router.visit(route('customer.shop.zone', { zone: zone.slug }), {
        data: { category: categoryId },
        onFinish: () => isEntering.value = false
    });
};

const handleItemClick = (item, zoneData, cardKey) => {
    if (!item || !isClick.value) return; 
    item.type === 'bundle' ? openBundle(item.slug) : enterZone(zoneData, item.id, cardKey);
};

// --- MOTOR DE SCROLL INFINITO ---
const CARD_HEIGHT = 480; 
const PEEK_HEIGHT = 110; 
const VISIBLE_COUNT = 4; 
const ITEM_WIDTH = 150; 
const ITEM_GAP = 16;    
const TOTAL_ITEM_WIDTH = ITEM_WIDTH + ITEM_GAP;
const AUTO_SCROLL_SPEED = 0.5; 

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

const getLoopedAisles = (aisles) => {
    if (!aisles || aisles.length === 0) return [];
    return Array(12).fill(aisles).flat().filter(Boolean);
};

const updateScrollerTransform = () => {
    const activeCardScroller = document.getElementById('scroller-0');
    if (activeCardScroller) {
        activeCardScroller.style.transform = `translate3d(${horizOffset}px, 0, 0)`;
    }
};

const startAutoScroll = () => {
    cancelAnimationFrame(animationFrameId);
    const currentZone = getZoneData(virtualIndex.value);
    if (!currentZone?.aisles?.length) return;

    const singleSetWidth = currentZone.aisles.length * TOTAL_ITEM_WIDTH;
    const idealCenter = -(singleSetWidth * 6);

    if (horizOffset === 0 || Math.abs(horizOffset) < 100) horizOffset = idealCenter;

    const animate = () => {
        if (isEntering.value || showBundleModal.value) return;
        if (!isInteracting.value) horizOffset -= AUTO_SCROLL_SPEED;

        if (horizOffset < idealCenter - singleSetWidth) horizOffset += singleSetWidth;
        else if (horizOffset > idealCenter + singleSetWidth) horizOffset -= singleSetWidth;

        updateScrollerTransform();
        animationFrameId = requestAnimationFrame(animate);
    };
    animationFrameId = requestAnimationFrame(animate);
};

const handleTouchStart = (e) => {
    if (isEntering.value || showBundleModal.value) return;
    isInteracting.value = true;
    isClick.value = true;
    gestureAxis.value = null;
    startX.value = e.touches[0].clientX;
    startY.value = e.touches[0].clientY;
};

const handleTouchMove = (e) => {
    if (!isInteracting.value || isEntering.value) return;
    const deltaX = e.touches[0].clientX - startX.value;
    const deltaY = e.touches[0].clientY - startY.value;

    if (Math.abs(deltaX) > 8 || Math.abs(deltaY) > 8) isClick.value = false;

    if (!gestureAxis.value) {
        gestureAxis.value = Math.abs(deltaX) > Math.abs(deltaY) ? 'x' : 'y';
    }

    if (gestureAxis.value === 'x') {
        horizOffset += (deltaX - currentDragX.value);
        currentDragX.value = deltaX;
        updateScrollerTransform();
    } else {
        currentDragY.value = deltaY;
    }
};

const handleTouchEnd = () => {
    isInteracting.value = false;
    if (!isClick.value && gestureAxis.value === 'y') {
        if (currentDragY.value < -80) virtualIndex.value++;
        else if (currentDragY.value > 80) virtualIndex.value--;
        horizOffset = 0;
        nextTick(() => startAutoScroll());
    }
    currentDragY.value = 0;
    currentDragX.value = 0;
    gestureAxis.value = null;
};

const getCardStyle = (offset) => {
    let drag = gestureAxis.value === 'y' ? currentDragY.value : 0;
    let yPos = 0; let zPos = 0; let scale = 1; let opacity = 1; 
    let zIndex = 30 - Math.abs(offset); 

    if (isEntering.value && offset === 0) {
        return { transform: `translate3d(0, 0, 0) scale(1.05)`, opacity: 0, zIndex: 30 };
    }

    if (offset === -1) {
        scale = 1.1; zPos = 200; yPos = -80 + drag; opacity = drag > 0 ? (drag / 300) : 0;
        if (drag <= 0) return { display: 'none' }; 
    } else if (offset === 0) {
        yPos = drag * 0.4; 
    } else {
        yPos = (offset * (PEEK_HEIGHT - 10)) + (drag * 0.2);
        zPos = -offset * 120; scale = 1 - (offset * 0.05); opacity = 1 - (offset * 0.1); 
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
        <BundleModal :show="showBundleModal" :bundle-slug="activeBundleSlug" @close="showBundleModal = false" />

        <div class="w-full h-[calc(100svh-4rem)] bg-background overflow-hidden relative flex flex-col items-center pt-4 touch-none select-none"
             @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">

            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 bg-grid opacity-[0.05]"></div>
                <div class="absolute top-[-20%] left-1/2 -translate-x-1/2 w-[150%] h-[60%] rounded-full blur-[120px] opacity-30 transition-colors duration-700"
                     :style="{ backgroundColor: getZoneData(virtualIndex)?.color || 'var(--primary)' }"></div>
            </div>
            
            <div class="z-10 mb-2 flex flex-col items-center pointer-events-none">
                <div class="flex gap-1.5 shadow-sm p-1 rounded-full bg-black/40 backdrop-blur-md border border-white/5">
                    <div v-for="(_, idx) in sourceZones" :key="idx"
                         class="h-1 rounded-full transition-all duration-300"
                         :class="(virtualIndex % sourceZones.length) === idx ? 'w-6 bg-primary' : 'w-1.5 bg-white/20'"></div>
                </div>
            </div>

            <div class="relative w-full max-w-[380px] h-full perspective-container">
                <div v-for="card in renderedCards" :key="card.key"
                     class="absolute left-0 right-0 w-full px-4 will-change-transform origin-center"
                     :style="{ height: `${CARD_HEIGHT}px`, ...getCardStyle(card.offset) }">
                    
                    <div class="relative w-full h-full rounded-[32px] overflow-hidden flex flex-col border backdrop-blur-xl bg-[#0B1221]/80"
                        :class="[
                            (enteringCardKey === card.key || hintingCardKey === card.key)
                                ? 'border-primary ring-2 ring-primary shadow-[0_0_40px_rgba(0,240,255,0.4)] z-50'
                                : (card.offset === 0 ? 'border-white/10 shadow-2xl' : 'border-white/5 opacity-80 grayscale')
                        ]">
                        <div class="px-6 pt-6 pb-2 z-20 flex justify-between items-start">
                            <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 text-primary">
                                <Zap :size="10" class="fill-current" /> {{ card.data.name }}
                            </span>
                        </div>
                        
                        <div class="flex-1 relative overflow-hidden mt-1">
                            <div :id="'scroller-' + card.offset" class="absolute top-0 left-0 h-full flex items-center px-4 will-change-transform">
                                <div v-for="(aisle, idx) in getLoopedAisles(card.data.aisles)" :key="idx"
                                     class="shrink-0 px-2" :style="{ width: `${TOTAL_ITEM_WIDTH}px` }"
                                     @click.stop="handleItemClick(aisle, card.data, card.key)">
                                    <div class="w-full bg-[#151e32] rounded-2xl overflow-hidden border border-white/10 shadow-lg">
                                        <img :src="aisle.image_url" class="w-full h-32 object-cover">
                                        <div class="p-3 text-xs font-bold text-gray-200 line-clamp-2">{{ aisle.name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative shrink-0 flex items-center px-6 border-t border-white/5"
                             :style="{ height: `${PEEK_HEIGHT}px`, background: `linear-gradient(to right, ${card.data.color}20, #0B1221)` }">
                            <div class="w-full flex justify-between items-center">
                                <h2 class="text-2xl font-black text-white uppercase italic tracking-tighter">{{ card.data.name }}</h2>
                                <div v-if="card.offset === 0" @click.stop="enterZone(card.data, null, card.key)"
                                     class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center cursor-pointer">
                                    <ChevronRight :size="20" stroke-width="3" />
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