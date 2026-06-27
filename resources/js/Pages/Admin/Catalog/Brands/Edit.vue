<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BrandForm from '@/Pages/Admin/Catalog/Brands/Partials/BrandForm.vue';

const props = defineProps({
    brand: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        required: true
    }
});

// Hidratación directa de datos planos limpios provistos por GetBrandForEditAction
const form = useForm({
    name: props.brand.name,
    slug: props.brand.slug,
    parent_id: props.brand.parent_id || '',
    provider_id: props.brand.provider_id,
    category_id: props.brand.category_id,
    website: props.brand.website || '',
    image: null, // Mantenido en nulo para evitar re-envíos ciegos del path string original
    is_active: Boolean(props.brand.is_active),
    is_featured: Boolean(props.brand.is_featured),
    description: props.brand.description || '',
    bg_color: props.brand.bg_color || '#ffffff'
});

const submit = () => {
    // Sincronización multipart/form-data a través de PUT soportada por el middleware global del servidor
    form.put(route('admin.catalog.brands.update', { brand: props.brand.id }));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Sincronizar Atributos de Marca
        </template>

        <BrandForm
            :form="form"
            :options="options"
            :is-edit="true"
            @submit="submit"
        />
    </AdminLayout>
</template>