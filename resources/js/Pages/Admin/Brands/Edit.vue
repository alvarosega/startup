<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import BrandForm from './Partials/BrandForm.vue';

defineProps({
    brand: Object,
    options: Object
});
</script>

<template>
    <AdminLayout>
        <Head title="Editar Marca // Protocolo" />
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            <BrandForm :brand="brand" :options="options" />
        </div>
    </AdminLayout>
</template>