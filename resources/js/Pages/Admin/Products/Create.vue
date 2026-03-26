<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

defineProps({ brands: Array, categories: Array, idempKey: String });
</script>

<template>
    <AdminLayout>
        <Head title="Registrar Activo Maestro" />
        <div class="max-w-5xl mx-auto py-10 px-4">
            <ProductForm :brands="brands" :categories="categories" :idemp-key="idempKey" />
        </div>
    </AdminLayout>
</template>