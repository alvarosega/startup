<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    branches: {
        type: Array,
        required: true
    }
});

// Inicialización estricta del formulario acoplada a las reglas de StoreCustomerRequest
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    branch_id: '',
    is_active: true
});

/**
 * Procesa el envío del formulario mediante la API nativa useForm de Inertia.
 */
const submit = () => {
    form.post(route('customers.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customers.index')"
                    class="w-9 h-9 rounded-xl flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-foreground/[0.06] transition-colors border border-border/60"
                >
                    <span class="material-symbols-rounded text-[20px] block">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Alta de Cliente Corporativo
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Ficha de cliente con asignación de sucursal y contraseña provisional
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl glass-card border border-white/60 dark:border-white/10 rounded-2xl shadow-sm p-6 md:p-8">
            <div class="mb-6 border-b border-border/50 pb-3">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wide">
                    Datos de la Cuenta Operativa
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Complete los campos obligatorios. Al guardar, el backend inyectará una contraseña provisional de seguridad.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Nombres <span class="text-error">*</span>
                        </label>
                        <input 
                            v-model="form.first_name"
                            type="text"
                            required
                            class="admin-input"
                            :class="{ 'border-error focus:ring-error focus:border-error': form.errors.first_name }"
                            placeholder="John"
                        />
                        <div v-if="form.errors.first_name" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ form.errors.first_name }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Apellidos <span class="text-error">*</span>
                        </label>
                        <input 
                            v-model="form.last_name"
                            type="text"
                            required
                            class="admin-input"
                            :class="{ 'border-error focus:ring-error focus:border-error': form.errors.last_name }"
                            placeholder="Doe"
                        />
                        <div v-if="form.errors.last_name" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ form.errors.last_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Identificador Corporativo (Email) <span class="text-error">*</span>
                        </label>
                        <input 
                            v-model="form.email"
                            type="email"
                            required
                            class="admin-input"
                            :class="{ 'border-error focus:ring-error focus:border-error': form.errors.email }"
                            placeholder="j.doe@cybermarket.com"
                        />
                        <div v-if="form.errors.email" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ form.errors.email }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Teléfono de Enlace <span class="text-error">*</span>
                        </label>
                        <input 
                            v-model="form.phone"
                            type="text"
                            required
                            class="admin-input font-mono"
                            :class="{ 'border-error focus:ring-error focus:border-error': form.errors.phone }"
                            placeholder="+51999888777"
                        />
                        <div v-if="form.errors.phone" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                            <span class="material-symbols-rounded text-sm shrink-0">error</span>
                            <span>{{ form.errors.phone }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Sucursal de Operación (Opcional)
                    </label>
                    <select 
                        v-model="form.branch_id" 
                        class="admin-input"
                        :class="{ 'border-error focus:ring-error focus:border-error': form.errors.branch_id }"
                    >
                        <option value="">Sin sucursal asignada (Ubicación Global)</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                            {{ branch.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.branch_id" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                        <span class="material-symbols-rounded text-sm shrink-0">error</span>
                        <span>{{ form.errors.branch_id }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                        <input 
                            v-model="form.is_active"
                            type="checkbox" 
                            class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4 transition-colors"
                        />
                        <span class="text-xs font-bold text-foreground uppercase tracking-wide">
                            Habilitar cuenta inmediatamente al guardar
                        </span>
                    </label>
                    <div v-if="form.errors.is_active" class="text-error text-xs flex items-center gap-1 mt-1 font-medium">
                        <span class="material-symbols-rounded text-sm shrink-0">error</span>
                        <span>{{ form.errors.is_active }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-border/50 flex items-center justify-end gap-3">
                    <Link 
                        :href="route('customers.index')"
                        class="px-3.5 py-2 glass-panel border border-border/80 text-foreground font-semibold rounded-xl text-xs hover:bg-foreground/[0.06] transition-all"
                        :class="{ 'pointer-events-none opacity-50': form.processing }"
                    >
                        Cancelar
                    </Link>
                    
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-4 rounded-xl shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-base shrink-0">save</span>
                        <span>{{ form.processing ? 'Registrando Terminal...' : 'Guardar Cliente' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>