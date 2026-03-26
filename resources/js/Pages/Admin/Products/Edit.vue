<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

defineProps({ product: Object, brands: Array, categories: Array });
</script>

<template>
    <AdminLayout>
        <Head title="Reconfigurar Maestro" />
        <div class="max-w-5xl mx-auto py-10 px-4">
            <ProductForm :product="product.data" :brands="brands" :categories="categories" />
        </div>
    </AdminLayout>
</template>