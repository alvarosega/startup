<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import CustomerForm from './Partials/CustomerForm.vue';

defineProps({ branches: Array });
</script>

<template>
    <Head title="Nuevo Cliente" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3 select-none">
                <Link :route="route('admin.users.index')" 
                      class="p-1.5 border border-border bg-card hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-md text-foreground transition-colors duration-75 flex items-center justify-center">
                    <span class="material-symbols-rounded text-lg">arrow_back</span>
                </Link>
                <h1 class="text-xl md:text-2xl font-black italic uppercase tracking-tight text-foreground">Registrar Nuevo Cliente</h1>
            </div>
        </template>
        
        <div class="py-4">
            <CustomerForm :branches="branches" />
        </div>
    </AdminLayout>
</template>