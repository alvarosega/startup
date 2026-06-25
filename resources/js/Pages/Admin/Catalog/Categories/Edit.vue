<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CategoryForm from '@/Pages/Admin/Catalog/Categories/Partials/CategoryForm.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true
    },
    parents: {
        type: Array,
        required: true
    }
});

// Hidratación del formulario interceptado por el middleware global PUT multipart del servidor
const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    parent_id: props.category.parent_id || '',
    external_code: props.category.external_code || '',
    tax_classification: props.category.tax_classification || '',
    requires_age_check: Boolean(props.category.requires_age_check),
    is_active: Boolean(props.category.is_active),
    is_featured: Boolean(props.category.is_featured),
    bg_color: props.category.bg_color || '#ffffff',
    image: null,
    icon: null,
    description: props.category.description || '',
    seo_title: props.category.seo_title || '',
    seo_description: props.category.seo_description || ''
});

const submit = () => {
    form.put(route('admin.catalog.categories.update', { category: props.category.id }));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Sincronizar Atributos de Nodo
        </template>

        <CategoryForm 
            :form="form"
            :parents="parents"
            :is-edit="true"
            @submit="submit"
        />
    </AdminLayout>
</template>