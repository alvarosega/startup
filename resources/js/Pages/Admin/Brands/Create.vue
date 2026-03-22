<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import BrandForm from './Partials/BrandForm.vue';

defineProps({
    options: Object // Ahora el controlador envía esto como un objeto único
});
</script>

<template>
    <AdminLayout>
        <Head title="Nueva Marca // Protocolo" />
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            <BrandForm :options="options" />
        </div>
    </AdminLayout>
</template>