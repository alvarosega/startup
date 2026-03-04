<template>
    <DriverLayout>
        <div class="p-4 flex flex-col items-center justify-center h-full text-center">
            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4 text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Historial Vacío</h2>
            <p class="text-sm text-gray-500 mt-2">Aquí aparecerán tus viajes completados y ganancias.</p>
        </div>
    </DriverLayout>
</template>

<script setup>
import DriverLayout from '@/Layouts/DriverLayout.vue';
</script>