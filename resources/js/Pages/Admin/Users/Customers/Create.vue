<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    branches: Array
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    branch_id: '',
    is_active: true
});

const submit = () => {
    form.post(route('admin.users.customers.store'));
};
</script>

<template>
    <AdminLayout>
        <div class="p-6 max-w-2xl mx-auto space-y-6">
            <div class="border-b border-border pb-4">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Registrar Nuevo Cliente</h1>
                <p class="text-xs text-muted-foreground mt-0.5">Creación manual con clave provisional forzada por el sistema.</p>
            </div>

            <form @submit.prevent="submit" class="bg-card border border-border rounded-md p-5 space-y-4 shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Nombre</label>
                        <input v-model="form.first_name" type="text" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                        <span v-if="form.errors.first_name" class="text-[10px] text-destructive block font-medium">{{ form.errors.first_name }}</span>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Apellido</label>
                        <input v-model="form.last_name" type="text" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                        <span v-if="form.errors.last_name" class="text-[10px] text-destructive block font-medium">{{ form.errors.last_name }}</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Correo Electrónico</label>
                    <input v-model="form.email" type="email" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                    <span v-if="form.errors.email" class="text-[10px] text-destructive block font-medium">{{ form.errors.email }}</span>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Teléfono Móvil</label>
                    <input v-model="form.phone" type="text" placeholder="+591XXXXXXXX" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary" />
                    <span v-if="form.errors.phone" class="text-[10px] text-destructive block font-medium">{{ form.errors.phone }}</span>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Asignar Sucursal Operativa</label>
                    <select v-model="form.branch_id" class="w-full bg-background border border-border rounded-md px-3 py-2 text-xs focus:outline-none focus:border-primary">
                        <option value="">Acceso Global (Sin sucursal fija)</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                    <span v-if="form.errors.branch_id" class="text-[10px] text-destructive block font-medium">{{ form.errors.branch_id }}</span>
                </div>

                <div class="flex items-center justify-between border-t border-border pt-4">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-foreground">Estado Inicial de Cuenta</span>
                        <span class="text-[10px] text-muted-foreground">Determina si la cuenta entra en vigor de forma inmediata.</span>
                    </div>
                    <button type="button" @click="form.is_active = !form.is_active" :class="[form.is_active ? 'bg-primary' : 'bg-neutral-300 dark:bg-neutral-700']" class="w-11 h-6 flex items-center rounded-full p-1 transition-colors duration-200 focus:outline-none relative">
                        <div :class="[form.is_active ? 'translate-x-5' : 'translate-x-0']" class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform duration-200"></div>
                    </button>
                </div>

                <div class="flex justify-end gap-2 border-t border-border pt-4 mt-6">
                    <Link :href="route('admin.users.customers.index')" class="px-3 py-2 border border-border text-xs font-semibold rounded-md hover:bg-neutral-50 text-foreground transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-3 py-2 bg-primary text-white text-xs font-semibold rounded-md hover:bg-primary/90 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Procesando Escritura...' : 'Ejecutar Transacción' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>