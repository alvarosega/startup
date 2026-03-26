<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SkuForm from './Partials/SkuForm.vue';

defineProps({ product: Object, idempKey: String });
</script>

<template>
    <AdminLayout>
        <Head :title="`Crear Variantes - ${product.name}`" />
        <div class="max-w-6xl mx-auto py-10 px-4">
            <SkuForm :product="product" :idemp-key="idempKey" />
        </div>
    </AdminLayout>
</template>