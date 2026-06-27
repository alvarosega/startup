<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BrandForm from '@/Pages/Admin/Catalog/Brands/Partials/BrandForm.vue';

defineProps({
    options: {
        type: Object,
        required: true
    }
});

// Inicialización limpia del formulario acoplada al contrato de StoreBrandRequest
const form = useForm({
    name: '',
    slug: '',
    parent_id: '',
    provider_id: '',
    category_id: '',
    website: '',
    image: null,
    is_active: true,
    is_featured: false,
    description: '',
    bg_color: '#ffffff'
});

const submit = () => {
    form.post(route('admin.catalog.brands.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Materializar Marca en Catálogo
        </template>

        <BrandForm
            :form="form"
            :options="options"
            :is-edit="false"
            @submit="submit"
        />
    </AdminLayout>
</template>