<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    branches: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    branch_id: '',
    status: 'pending',
    license_number: '',
    license_plate: '',
    vehicle_type: '',
});

const submit = () => {
    form.post(route('drivers.store'));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Inscribir Repartidor Corporativo
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Apertura obligatoria de ficha técnica logística y cuenta operativa
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl bg-card border border-border rounded-md shadow-flat p-6 md:p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sección 1: Credenciales y Cuenta -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-border pb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-base">person</span>
                            <span>1. Credenciales y Cuenta</span>
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    Nombre <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    required
                                    class="admin-input py-1.5 text-xs w-full"
                                    :class="{ 'border-rose-500': form.errors.first_name }"
                                    placeholder="Juan"
                                />
                                <span v-if="form.errors.first_name" class="text-[10px] text-rose-500 block font-medium">
                                    {{ form.errors.first_name }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    Apellido <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    required
                                    class="admin-input py-1.5 text-xs w-full"
                                    :class="{ 'border-rose-500': form.errors.last_name }"
                                    placeholder="Pérez"
                                />
                                <span v-if="form.errors.last_name" class="text-[10px] text-rose-500 block font-medium">
                                    {{ form.errors.last_name }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                Correo Electrónico <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="admin-input py-1.5 text-xs w-full"
                                :class="{ 'border-rose-500': form.errors.email }"
                                placeholder="repartidor@startup.test"
                            />
                            <span v-if="form.errors.email" class="text-[10px] text-rose-500 block font-medium">
                                {{ form.errors.email }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                Teléfono Móvil <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                placeholder="+591XXXXXXXX"
                                class="admin-input py-1.5 text-xs w-full font-mono"
                                :class="{ 'border-rose-500': form.errors.phone }"
                            />
                            <span v-if="form.errors.phone" class="text-[10px] text-rose-500 block font-medium">
                                {{ form.errors.phone }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                Base Operativa (Sucursal) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.branch_id"
                                required
                                class="admin-input py-1.5 text-xs w-full"
                                :class="{ 'border-rose-500': form.errors.branch_id }"
                            >
                                <option value="">Seleccione Sucursal Obligatoria</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.branch_id" class="text-[10px] text-rose-500 block font-medium">
                                {{ form.errors.branch_id }}
                            </span>
                        </div>
                    </div>

                    <!-- Sección 2: Logística y Vehículo -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-border pb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-base">two_wheeler</span>
                            <span>2. Datos de Logística y Vehículo</span>
                        </h3>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                Número de Licencia de Conducir
                            </label>
                            <input
                                v-model="form.license_number"
                                type="text"
                                class="admin-input py-1.5 text-xs w-full font-mono"
                                :class="{ 'border-rose-500': form.errors.license_number }"
                                placeholder="LP-8493021"
                            />
                            <span v-if="form.errors.license_number" class="text-[10px] text-rose-500 block font-medium">
                                {{ form.errors.license_number }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    Placa / Matrícula
                                </label>
                                <input
                                    v-model="form.license_plate"
                                    type="text"
                                    placeholder="4829-ABC"
                                    class="admin-input py-1.5 text-xs w-full uppercase font-mono"
                                    :class="{ 'border-rose-500': form.errors.license_plate }"
                                />
                                <span v-if="form.errors.license_plate" class="text-[10px] text-rose-500 block font-medium">
                                    {{ form.errors.license_plate }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    Tipo de Vehículo
                                </label>
                                <select
                                    v-model="form.vehicle_type"
                                    class="admin-input py-1.5 text-xs w-full"
                                    :class="{ 'border-rose-500': form.errors.vehicle_type }"
                                >
                                    <option value="">Seleccionar</option>
                                    <option value="motorcycle">Motocicleta</option>
                                    <option value="bicycle">Bicicleta</option>
                                    <option value="car">Automóvil</option>
                                    <option value="van">Furgón Logístico</option>
                                </select>
                                <span v-if="form.errors.vehicle_type" class="text-[10px] text-rose-500 block font-medium">
                                    {{ form.errors.vehicle_type }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                Estatus Inicial del Repartidor
                            </label>
                            <select
                                v-model="form.status"
                                class="admin-input py-1.5 text-xs w-full"
                                :class="{ 'border-rose-500': form.errors.status }"
                            >
                                <option value="pending">Pendiente (Requiere verificación de fotos posterior)</option>
                                <option value="approved">Aprobado para Operar Inmediatamente</option>
                            </select>
                            <span v-if="form.errors.status" class="text-[10px] text-rose-500 block font-medium">
                                {{ form.errors.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-border pt-5">
                    <Link
                        :href="route('drivers.index')"
                        class="px-3 py-2 bg-secondary text-secondary-foreground font-semibold rounded-md text-xs transition-colors hover:bg-neutral-200 dark:hover:bg-neutral-800 border border-border"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-4 disabled:opacity-50"
                    >
                        <span class="material-symbols-rounded text-base">save</span>
                        <span>{{ form.processing ? 'Registrando Repartidor...' : 'Inscribir Conductor' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
