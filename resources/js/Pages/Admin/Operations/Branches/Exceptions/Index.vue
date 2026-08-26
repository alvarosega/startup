<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    branch: {
        type: Object,
        required: true
    },
    exceptions: {
        type: Array,
        required: true
    }
});

const form = useForm({
    date: '',
    opening_time: '',
    closing_time: '',
    is_closed: true,
    reason: ''
});

const submit = () => {
    form.post(route('admin.operations.branches.schedule-exceptions.store', props.branch.id), {
        onSuccess: () => form.reset()
    });
};

const deleteException = (id) => {
    if (window.confirm('¿Desea remover esta excepción de horario?')) {
        router.delete(route('admin.operations.branches.schedule-exceptions.destroy', [props.branch.id, id]));
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Excepciones y Feriados — {{ branch.name }}
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Formulario de Alta -->
            <div class="bg-card border border-border p-5 rounded-md shadow-flat space-y-4">
                <div class="border-b border-border pb-2">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Programar Excepción</h3>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase">Fecha *</label>
                        <input v-model="form.date" type="date" required class="admin-input font-mono" :class="{ 'border-error': form.errors.date }" />
                        <p v-if="form.errors.date" class="text-error text-xs font-medium">{{ form.errors.date }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase">Motivo</label>
                        <input v-model="form.reason" type="text" placeholder="Feriado nacional, mantenimiento, etc." class="admin-input" :class="{ 'border-error': form.errors.reason }" />
                        <p v-if="form.errors.reason" class="text-error text-xs font-medium">{{ form.errors.reason }}</p>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input v-model="form.is_closed" type="checkbox" class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" />
                            <span class="text-xs font-bold text-foreground uppercase">Cierre Completo (Sin Atención)</span>
                        </label>
                    </div>

                    <div v-if="!form.is_closed" class="grid grid-cols-2 gap-3 pt-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Apertura</label>
                            <input v-model="form.opening_time" type="time" class="admin-input font-mono text-xs" :class="{ 'border-error': form.errors.opening_time }" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Cierre</label>
                            <input v-model="form.closing_time" type="time" class="admin-input font-mono text-xs" :class="{ 'border-error': form.errors.closing_time }" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing" class="admin-btn-primary w-full justify-center text-xs font-bold">
                            {{ form.processing ? 'Registrando...' : 'Registrar Excepción' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de Excepciones Futuras -->
            <div class="lg:col-span-2 bg-card border border-border rounded-md shadow-flat overflow-hidden">
                <div class="p-4 border-b border-border flex items-center justify-between">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wide">Excepciones Activas / Próximas</h3>
                    <Link :href="route('admin.operations.branches.index')" class="text-xs font-semibold text-muted-foreground hover:text-foreground">
                        ← Volver a Sucursales
                    </Link>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-table-th">Fecha</th>
                            <th class="admin-table-th">Motivo</th>
                            <th class="admin-table-th text-center">Régimen</th>
                            <th class="admin-table-th text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="exceptions.length === 0">
                            <td colspan="4" class="admin-table-td text-center text-muted-foreground py-6 font-normal">
                                No existen excepciones o feriados programados para esta sucursal.
                            </td>
                        </tr>
                        <tr v-for="exc in exceptions" :key="exc.id" class="admin-table-tr">
                            <td class="admin-table-td font-mono font-bold">{{ exc.date }}</td>
                            <td class="admin-table-td text-xs">{{ exc.reason || 'Sin motivo especificado' }}</td>
                            <td class="admin-table-td text-center">
                                <span v-if="exc.is_closed" class="badge-error">Cerrado</span>
                                <span v-else class="badge-info font-mono text-[11px]">
                                    {{ exc.opening_time?.substring(0, 5) }} - {{ exc.closing_time?.substring(0, 5) }}
                                </span>
                            </td>
                            <td class="admin-table-td text-right">
                                <button @click="deleteException(exc.id)" class="text-destructive hover:underline text-xs font-semibold">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>