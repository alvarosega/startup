<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import ProviderForm from './Partials/ProviderForm.vue';
</script>

<template>
    <AdminLayout>
        <Head title="Nuevo Proveedor" />
        <div class="p-4 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.providers.index')" class="p-2 border border-border hover:bg-muted">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold uppercase">Registrar Nuevo Nodo // Proveedor</h1>
                    <p class="text-[10px] font-mono text-muted-foreground uppercase">Supply Chain Protocol Initialized</p>
                </div>
            </div>
            <ProviderForm :is-edit="false" />
        </div>
    </AdminLayout>
</template>