<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BrandSection from '@/Components/Shop/BrandSection.vue';
import ProductConfigurationSheet from '@/Components/Shop/ProductConfigurationSheet.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    zone: Object,
    brandsNavigation: { type: Array, default: () => [] }
});

const selectedProductForModal = ref(null);
const activeTab = ref(props.brandsNavigation.length > 0 ? props.brandsNavigation[0].id : null);
const tabsRef = ref(null);

// --- MOTOR DE SCROLL SPY (REGLA: REACTIVIDAD ABSOLUTA) ---
let observer = null;

const initScrollSpy = () => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.1) {
                const id = entry.target.id.replace('brand-section-', '');
                activeTab.value = isNaN(id) ? id : Number(id);
                syncTabScroll(activeTab.value);
            }
        });
    }, { 
        rootMargin: '-160px 0px -70% 0px', // Ajuste preciso para detectar el título superior
        threshold: [0, 0.1, 0.2] 
    });

    document.querySelectorAll('.brand-section-wrapper').forEach(el => observer.observe(el));
};

const syncTabScroll = (id) => {
    const activeTabEl = document.getElementById(`tab-${id}`);
    if (activeTabEl && tabsRef.value) {
        tabsRef.value.scrollTo({
            left: activeTabEl.offsetLeft - (tabsRef.value.offsetWidth / 2) + (activeTabEl.offsetWidth / 2),
            behavior: 'smooth'
        });
    }
};

const scrollToBrand = (id) => {
    activeTab.value = id;
    const element = document.getElementById(`brand-section-${id}`);
    if (element) {
        const offset = 150; 
        const bodyRect = document.body.getBoundingClientRect().top;
        const elementRect = element.getBoundingClientRect().top;
        const offsetPosition = (elementRect - bodyRect) - offset;

        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    }
};

onMounted(() => {
    nextTick(() => initScrollSpy());
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <ShopLayout>
        <Head :title="zone?.name" />
        
        <div class="min-h-screen pb-32 relative bg-[#FFFFFF] dark:bg-[#050505]">
            
            <header class="sticky top-[64px] z-50 bg-[#FFFFFF]/80 dark:bg-[#050505]/80 backdrop-blur-xl border-b border-gray-100 dark:border-[#262626] px-4 py-3">
                <div class="flex items-center justify-between max-w-5xl mx-auto h-8">
                    <button @click="router.visit(route('customer.shop.index'))" 
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-[#121217] border border-transparent dark:border-[#262626] active:scale-95 transition-transform">
                        <ArrowLeft class="w-4 text-gray-900 dark:text-white" stroke-width="2.5" />
                    </button>

                    <div class="flex flex-col items-center">
                        <h1 class="font-black text-sm uppercase tracking-tighter text-gray-900 dark:text-white">
                            {{ zone?.name }}
                        </h1>
                        <div class="h-0.5 w-4 rounded-full" :style="{ backgroundColor: zone?.hex_color || '#E10600' }"></div>
                    </div>

                    <div class="w-8"></div> </div>
            </header>

            <nav ref="tabsRef" class="sticky top-[112px] z-40 w-full bg-[#FFFFFF]/95 dark:bg-[#050505]/95 backdrop-blur-xl border-b border-gray-100 dark:border-[#262626] py-1 overflow-x-auto no-scrollbar scroll-smooth">
                <div class="flex px-4 gap-1 min-w-max">
                    <button v-for="brand in brandsNavigation" :key="brand.id"
                            :id="`tab-${brand.id}`"
                            @click="scrollToBrand(brand.id)"
                            class="px-4 py-3 text-[10px] font-black uppercase tracking-widest transition-all relative"
                            :class="activeTab === brand.id ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'">
                        {{ brand.name }}
                        <div v-if="activeTab === brand.id" 
                             class="absolute bottom-0 left-0 w-full h-[3px] rounded-t-full transition-all duration-300 shadow-[0_-4px_10px_rgba(0,0,0,0.1)]"
                             :style="{ 
                                backgroundColor: zone?.hex_color || 'var(--primary)',
                                boxShadow: `0 -2px 10px ${zone?.hex_color}40` 
                             }">
                        </div>
                    </button>
                </div>
            </nav>

            <main class="container mx-auto space-y-2 relative z-0">
                <div v-for="brand in brandsNavigation" :key="brand.id" 
                     :id="`brand-section-${brand.id}`"
                     class="brand-section-wrapper transition-opacity duration-500">
                    
                    <BrandSection 
                        :brand="brand" 
                        @open-modal="p => selectedProductForModal = p"
                    />
                </div>
            </main>
        </div>

        <ProductConfigurationSheet 
            :product="selectedProductForModal" 
            @close="selectedProductForModal = null" 
        />
    </ShopLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Efecto de entrada para las secciones */
.brand-section-wrapper {
    content-visibility: auto;
    contain-intrinsic-size: 1px 500px;
}
</style>