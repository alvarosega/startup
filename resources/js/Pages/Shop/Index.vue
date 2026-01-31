<script setup>
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import CityMap from '@/Components/Shop/CityMap.vue';

const props = defineProps({ 
    zonesData: Object, 
    products: Object
});

const enterZone = (zone) => {
    // Transición suave a la vista de pasillo
    router.visit(route('shop.zone', zone.slug), {
        method: 'get',
        preserveState: false,
        onStart: () => {
            // Aquí podrías disparar una animación de salida global si quisieras
        }
    });
};
</script>

<template>
    <ShopLayout>
        <Head title="Explorar Zonas" />

        <main class="w-full h-[100svh] overflow-hidden bg-background relative">
            
            <CityMap 
                :zones="zonesData" 
                @select-zone="enterZone" 
            />

        </main>
    </ShopLayout>
</template>