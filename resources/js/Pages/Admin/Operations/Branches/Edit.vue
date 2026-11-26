<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchForm from '@/Pages/Admin/Operations/Branches/Partials/BranchForm.vue';
import BranchSubNavTabs from '@/Components/Admin/Operations/BranchSubNavTabs.vue';

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
    max_active_orders: props.branch.max_active_orders !== null ? Number(props.branch.max_active_orders) : null, 
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
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.operations.branches.index')"
                    class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                    title="Volver a Sucursales"
                >
                    <span class="material-symbols-rounded text-[20px]">arrow_back</span>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                            {{ branch.name }}
                        </h1>
                        <span v-if="branch.is_default" class="badge-info text-[9px] py-0 px-1.5">Principal</span>
                        <span :class="branch.is_active ? 'badge-success text-[9px] py-0 px-1.5' : 'badge-error text-[9px] py-0 px-1.5'">
                            {{ branch.is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Jurisdicción: {{ branch.city }} — Configuración de geocerca y parámetros
                    </p>
                </div>
            </div>
        </template>

        <!-- Barra de Navegación Contextual de la Sucursal -->
        <BranchSubNavTabs :branch="branch" />

        <BranchForm 
            :form="form" 
            :is-edit="true" 
            @submit="submit" 
        />
    </AdminLayout>
</template>
