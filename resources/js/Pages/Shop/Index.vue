<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CityMap from '@/Components/Shop/CityMap.vue';
import AisleRow from '@/Components/Shop/AisleRow.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({ zonesData: Object, products: Object });

// ESTADOS
const viewMode = ref('map'); 
const activeZone = ref(null);

// --- ACCIONES ---
const enterZone = (zone) => {
    // En Mobile, no hacemos zoom animado complejo, es mejor una transición rápida
    // para que se sienta "snappy" (rápido).
    activeZone.value = zone;
    viewMode.value = 'aisle';
    window.scrollTo({ top: 0, behavior: 'auto' });
};

const backToMap = () => {
    viewMode.value = 'map';
    activeZone.value = null;
};
</script>

<template>
    <ShopLayout>
        <Head title="Tienda" />

        <div class="min-h-[calc(100vh-64px)] w-full bg-gray-50 relative">
            
            <div v-show="viewMode === 'map'" class="w-full h-full pb-20">
                <CityMap :zones="zonesData" @select-zone="enterZone" />
            </div>

            <transition 
                enter-active-class="transform transition ease-in-out duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div v-if="viewMode === 'aisle'" 
                     class="fixed inset-0 top-[64px] z-30 w-full h-[calc(100vh-64px)] bg-gray-50 overflow-y-auto"
                >
                    <div class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b px-4 py-3 flex items-center gap-3 shadow-sm">
                        <button @click="backToMap" class="p-2 -ml-2 hover:bg-gray-100 rounded-full text-gray-600">
                            <ArrowLeft :size="22" />
                        </button>
                        <div class="flex-1">
                            <h1 class="text-lg font-black uppercase tracking-tight text-gray-900 leading-none">
                                {{ activeZone.name }}
                            </h1>
                            <p class="text-[10px] font-bold text-gray-400">Selecciona tus productos</p>
                        </div>
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: activeZone.color }"></div>
                    </div>

                    <div class="container mx-auto py-6 px-4 pb-32 space-y-8">
                        <AisleRow v-for="aisle in activeZone.aisles" 
                                  :key="aisle.id" 
                                  :title="aisle.name" 
                                  :products="aisle.products" 
                        />
                    </div>
                </div>
            </transition>

        </div>
    </ShopLayout>
</template>