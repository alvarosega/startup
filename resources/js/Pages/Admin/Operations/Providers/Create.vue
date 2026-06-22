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
            <div class="flex items-center gap-4 select-none">
                <Link :href="route('admin.operations.providers.index')" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-400 hover:text-neutral-900 dark:hover:text-white rounded-md transition-colors">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tight text-neutral-900 dark:text-white">Registrar Nuevo Nodo // Proveedor</h1>
                    <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 uppercase">Supply Chain Protocol Initialized</p>
                </div>
            </div>
            <ProviderForm :is-edit="false" />
        </div>
    </AdminLayout>
</template>