<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CategoryForm from '@/Pages/Admin/Catalog/Categories/Partials/CategoryForm.vue';

defineProps({
    parents: {
        type: Array,
        required: true
    }
});

// Inicialización limpia de llaves síncronas requeridas por StoreCategoryRequest
const form = useForm({
    name: '',
    slug: '',
    parent_id: '',
    external_code: '',
    tax_classification: '',
    requires_age_check: false,
    is_active: true,
    is_featured: false,
    bg_color: '#ffffff',
    image: null,
    icon: null,
    description: '',
    seo_title: '',
    seo_description: ''
});

const submit = () => {
    form.post(route('admin.catalog.categories.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Materializar Nodo de Catálogo
        </template>

        <CategoryForm 
            :form="form"
            :parents="parents"
            :is-edit="false"
            @submit="submit"
        />
    </AdminLayout>
</template>