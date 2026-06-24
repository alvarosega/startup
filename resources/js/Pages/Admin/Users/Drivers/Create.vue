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
    status: 'pending',
    license_number: '',
    license_plate: '',
    vehicle_type: ''
});

const submit = () => {
    form.post(route('admin.users.drivers.store'));
};
</script>

<template>
    <AdminLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="border-b border-border pb-4">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Inscribir Repartidor Corporativo</h1>
                <p class="text-xs text-muted-foreground mt-0.5">Apertura obligatoria de ficha técnica y de cuenta operativa.</p>
            </div>

            <form @submit.prevent="submit" class="bg-card border border-border rounded-md p-6 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-border pb-1">1. Credenciales y Cuenta</h3>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Nombre</label>
                                <input v-model="form.first_name" type="text" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                                <span v-if="form.errors.first_name" class="text-[10px] text-destructive block">{{ form.errors.first_name }}</span>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Apellido</label>
                                <input v-model="form.last_name" type="text" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                                <span v-if="form.errors.last_name" class="text-[10px] text-destructive block">{{ form.errors.last_name }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Correo Electrónico</label>
                            <input v-model="form.email" type="email" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                            <span v-if="form.errors.email" class="text-[10px] text-destructive block">{{ form.errors.email }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Teléfono Móvil</label>
                            <input v-model="form.phone" type="text" placeholder="+591XXXXXXXX" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                            <span v-if="form.errors.phone" class="text-[10px] text-destructive block">{{ form.errors.phone }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Base Operativa (Sucursal)</label>
                            <select v-model="form.branch_id" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                                <option value="">Seleccione Sucursal Obligatoria</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                            <span v-if="form.errors.branch_id" class="text-[10px] text-destructive block">{{ form.errors.branch_id }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-border pb-1">2. Datos de Logística y Vehículo</h3>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Número de Licencia de Conducir</label>
                            <input v-model="form.license_number" type="text" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary" />
                            <span v-if="form.errors.license_number" class="text-[10px] text-destructive block">{{ form.errors.license_number }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Placa / Matrícula</label>
                                <input v-model="form.license_plate" type="text" placeholder="CH-XXXX" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary uppercase" />
                                <span v-if="form.errors.license_plate" class="text-[10px] text-destructive block">{{ form.errors.license_plate }}</span>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Tipo de Vehículo</label>
                                <select v-model="form.vehicle_type" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                                    <option value="">Seleccionar</option>
                                    <option value="motorcycle">Motocicleta</option>
                                    <option value="bicycle">Bicicleta</option>
                                    <option value="car">Automóvil</option>
                                    <option value="van">Furgón Logístico</option>
                                </select>
                                <span v-if="form.errors.vehicle_type" class="text-[10px] text-destructive block">{{ form.errors.vehicle_type }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Estatus Inicial del Repartidor</label>
                            <select v-model="form.status" class="w-full bg-background border border-border rounded-md px-3 py-1.5 text-xs focus:outline-none focus:border-primary">
                                <option value="pending">Pendiente (Requiere verificación de fotos posterior)</option>
                                <option value="approved">Aprobado para Operar Inmediatamente</option>
                            </select>
                            <span v-if="form.errors.status" class="text-[10px] text-destructive block">{{ form.errors.status }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border pt-4 mt-4">
                    <Link :href="route('admin.users.drivers.index')" class="px-3 py-2 border border-border text-xs font-semibold rounded-md hover:bg-neutral-50 text-foreground transition-colors">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-3 py-2 bg-primary text-white text-xs font-semibold rounded-md hover:bg-primary/90 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Registrando Historial...' : 'Inscribir Conductor' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>