<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchForm from '@/Pages/Admin/Operations/Branches/Partials/BranchForm.vue';

const props = defineProps({
    branch: {
        type: Object,
        required: true
    }
});

// Hidratación reactiva del formulario con la geometría mapeada desde el controlador
const form = useForm({
    name: props.branch.name,
    city: props.branch.city,
    phone: props.branch.phone || '',
    address: props.branch.address || '',
    latitude: Number(props.branch.latitude),
    longitude: Number(props.branch.longitude),
    coverage_polygon: props.branch.coverage_polygon || [],
    is_active: Boolean(props.branch.is_active),
    is_default: Boolean(props.branch.is_default),
    delivery_base_fee: Number(props.branch.delivery_base_fee),
    delivery_price_per_km: Number(props.branch.delivery_price_per_km),
    surge_multiplier: Number(props.branch.surge_multiplier),
    min_order_amount: Number(props.branch.min_order_amount),
    small_order_fee: Number(props.branch.small_order_fee),
    base_service_fee_percentage: Number(props.branch.base_service_fee_percentage)
});

/**
 * Ejecuta la actualización de los parámetros logísticos aplicando el interceptor de cierre de anillo GIS.
 */
const submit = () => {
    // Interceptor: Cierre automático topológico exigido por MySQL GIS [longitud, latitud]
    form.transform((data) => {
        let polygon = [...data.coverage_polygon];
        if (polygon.length >= 3) {
            const first = polygon[0];
            const last = polygon[polygon.length - 1];
            if (first[0] !== last[0] || first[1] !== last[1]) {
                polygon.push([first[0], first[1]]);
            }
        }
        return {
            ...data,
            coverage_polygon: polygon
        };
    });

    form.put(route('admin.operations.branches.update', props.branch.id));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Modificar Parámetros Logísticos
        </template>

        <BranchForm 
            :form="form" 
            :is-edit="true" 
            @submit="submit" 
        />
    </AdminLayout>
</template>