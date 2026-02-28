<script setup>
import { Head } from '@inertiajs/vue3';
import DriverLayout from '@/Layouts/DriverLayout.vue';
import { Truck } from 'lucide-vue-next';

const props = defineProps({
    driver: Object,
    pendingOrders: { type: Array, default: () => [] }
});
</script>

<template>
    <Head title="Terminal Operativa" />
    <DriverLayout>
        <div class="py-4">
            <h2 class="text-lg font-black text-gray-800 mb-4 flex items-center gap-2">
                <Truck class="text-amber-500" /> Mi Ruta de Hoy
            </h2>
            
            <div v-if="pendingOrders.length === 0" class="p-8 text-center bg-white rounded-xl border border-border shadow-sm">
                <p class="text-gray-400 font-medium">No tienes entregas asignadas (aún).</p>
                <p class="text-xs text-gray-300 mt-1">Tu cuenta está activa y lista para operar.</p>
            </div>

            </div>
    </DriverLayout>
</template>