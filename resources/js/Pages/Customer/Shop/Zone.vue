<script setup>
import { ref, onMounted } from 'vue';
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

const scrollToBrand = (id) => {
    activeTab.value = id;
    const element = document.getElementById(`brand-section-${id}`);
    if (element) {
        const offset = 150; 
        const bodyRect = document.body.getBoundingClientRect().top;
        const elementRect = element.getBoundingClientRect().top;
        const elementPosition = elementRect - bodyRect;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    }
};
</script>

<template>
    <ShopLayout>
        <Head :title="zone?.name" />
        
        <div class="min-h-screen pb-32 relative bg-[#FFFFFF] dark:bg-[#050505]">
            <header class="sticky top-[64px] z-50 bg-[#FFFFFF]/80 dark:bg-[#050505]/80 backdrop-blur-xl border-b border-gray-100 dark:border-[#262626] px-4 py-3">
                <div class="flex items-center justify-center relative w-full h-8">
                    <h1 class="font-bold text-lg tracking-[-0.02em] text-gray-900 dark:text-white text-center truncate px-12">{{ zone?.name }}</h1>
                    <button @click="router.visit(route('customer.shop.index'))" class="absolute left-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-[#121217] border border-transparent dark:border-[#262626] active:scale-95 transition-transform">
                        <ArrowLeft class="w-4 text-gray-900 dark:text-white" stroke-width="2.5" />
                    </button>
                </div>
            </header>

            <div class="sticky top-[112px] z-40 w-full bg-[#FFFFFF]/95 dark:bg-[#050505]/95 backdrop-blur-xl border-b border-gray-100 dark:border-[#262626] pt-1 overflow-x-auto no-scrollbar scroll-smooth">
                <div class="flex px-4 gap-1">
                    <button v-for="brand in brandsNavigation" :key="brand.id"
                            :id="`tab-${brand.id}`"
                            @click="scrollToBrand(brand.id)"
                            class="px-3 py-2 text-sm font-semibold tracking-[-0.01em] transition-all whitespace-nowrap relative"
                            :class="activeTab === brand.id ? 'text-[#007AFF] dark:text-[#E10600]' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'">
                        {{ brand.name }}
                        <div v-if="activeTab === brand.id" class="absolute bottom-0 left-0 w-full h-[2px] bg-[#007AFF] dark:bg-[#E10600] dark:shadow-[0_0_8px_rgba(225,6,0,0.6)]"></div>
                    </button>
                </div>
            </div>

            <div class="container mx-auto py-0 px-0 space-y-0 relative z-0">
                <BrandSection 
                    v-for="brand in brandsNavigation" 
                    :key="brand.id" 
                    :brand="brand" 
                    @open-modal="p => selectedProductForModal = p"
                />
            </div>
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
</style>