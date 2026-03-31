<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import ProviderForm from './Partials/ProviderForm.vue';

const props = defineProps({ provider: Object });
</script>

<template>
    <AdminLayout>
        <Head title="Editar Proveedor" />
        <div class="p-4 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.providers.index')" class="p-2 border border-border hover:bg-muted">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold uppercase">Actualizar Nodo // {{ provider.data?.company_name || provider.company_name }}</h1>
                    <p class="text-[10px] font-mono text-muted-foreground uppercase">ID_REF: {{ provider.data?.id || provider.id }}</p>
                </div>
            </div>
            <ProviderForm :provider="provider.data || provider" :is-edit="true" />
        </div>
    </AdminLayout>
</template>