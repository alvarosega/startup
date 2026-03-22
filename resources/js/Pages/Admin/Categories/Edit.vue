<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import CategoryForm from './Partials/CategoryForm.vue';

defineProps({
    category: Object,
    parents: Array
});
</script>

<template>
    <AdminLayout>
        <Head title="Editar Nodo // Protocolo Catálogo" />
        <div class="max-w-4xl mx-auto pb-12 px-4 md:px-0">
            <CategoryForm :category="category" :parents="parents" />
        </div>
    </AdminLayout>
</template>