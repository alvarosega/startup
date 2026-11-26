<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchSubNavTabs from '@/Components/Admin/Operations/BranchSubNavTabs.vue';

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
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.operations.branches.index')"
                    class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-foreground/[0.05] transition-all inline-flex items-center"
                    title="Volver a Sucursales"
                >
                    <span class="material-symbols-rounded text-[20px]">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Feriados y Excepciones — {{ branch.name }}
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Programación de días festivos, mantenimientos y variaciones puntuales de horario
                    </p>
                </div>
            </div>
        </template>

        <!-- Barra Contextual de la Sucursal -->
        <BranchSubNavTabs :branch="branch" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Formulario de Alta de Excepción -->
            <div class="glass-card p-5 md:p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm space-y-4">
                <div class="border-b border-border/50 pb-2.5">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-primary text-[18px]">add_circle</span>
                        <span>Programar Excepción</span>
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Fecha *</label>
                        <input 
                            v-model="form.date" 
                            type="date" 
                            required 
                            class="admin-input font-mono text-xs" 
                            :class="{ 'border-error': form.errors.date }" 
                        />
                        <p v-if="form.errors.date" class="text-error text-[11px] font-medium">{{ form.errors.date }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Motivo / Descripción</label>
                        <input 
                            v-model="form.reason" 
                            type="text" 
                            placeholder="Feriado nacional, inventario anual, etc." 
                            class="admin-input text-xs" 
                            :class="{ 'border-error': form.errors.reason }" 
                        />
                        <p v-if="form.errors.reason" class="text-error text-[11px] font-medium">{{ form.errors.reason }}</p>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                v-model="form.is_closed" 
                                type="checkbox" 
                                class="rounded-md border-border/80 text-primary focus:ring-primary/20 bg-card/60 w-4 h-4" 
                            />
                            <span class="text-xs font-bold text-foreground uppercase tracking-wide">Cierre Completo (Sin Atención)</span>
                        </label>
                    </div>

                    <div v-if="!form.is_closed" class="grid grid-cols-2 gap-3 pt-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Apertura</label>
                            <input 
                                v-model="form.opening_time" 
                                type="time" 
                                class="admin-input font-mono text-xs" 
                                :class="{ 'border-error': form.errors.opening_time }" 
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Cierre</label>
                            <input 
                                v-model="form.closing_time" 
                                type="time" 
                                class="admin-input font-mono text-xs" 
                                :class="{ 'border-error': form.errors.closing_time }" 
                            />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="admin-btn-primary w-full justify-center text-xs font-bold uppercase tracking-wider py-2.5 px-4 rounded-xl shadow-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">event_available</span>
                            <span>{{ form.processing ? 'Registrando...' : 'Registrar Excepción' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de Excepciones Futuras -->
            <div class="lg:col-span-2 glass-panel rounded-2xl border border-white/60 dark:border-white/10 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-border/50 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-rounded text-primary text-[18px]">calendar_month</span>
                        <span>Excepciones Activas & Próximas</span>
                    </h3>
                    <span class="text-[11px] text-muted-foreground font-mono">
                        {{ exceptions.length }} programadas
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="admin-table-th">Fecha</th>
                                <th class="admin-table-th">Motivo</th>
                                <th class="admin-table-th text-center">Horario Especial</th>
                                <th class="admin-table-th text-center">Disponibilidad</th>
                                <th class="admin-table-th text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="exceptions.length === 0">
                                <td colspan="5" class="admin-table-td text-center text-muted-foreground py-8 font-normal">
                                    No hay excepciones de horario programadas para fechas futuras.
                                </td>
                            </tr>
                            <tr v-for="ex in exceptions" :key="ex.id" class="admin-table-tr">
                                <td class="admin-table-td font-mono font-bold text-xs">{{ ex.date }}</td>
                                <td class="admin-table-td text-xs">{{ ex.reason || 'Sin motivo especificado' }}</td>
                                <td class="admin-table-td text-center font-mono text-xs">
                                    {{ ex.is_closed ? '—' : `${ex.opening_time?.substring(0, 5)} - ${ex.closing_time?.substring(0, 5)}` }}
                                </td>
                                <td class="admin-table-td text-center">
                                    <span :class="ex.is_closed ? 'badge-error' : 'badge-warning'">
                                        {{ ex.is_closed ? 'Cerrado Total' : 'Horario Especial' }}
                                    </span>
                                </td>
                                <td class="admin-table-td text-right">
                                    <button 
                                        type="button" 
                                        @click="deleteException(ex.id)" 
                                        class="p-1.5 rounded-xl text-destructive hover:bg-destructive/10 transition-all inline-flex items-center border border-destructive/20"
                                        title="Eliminar excepción"
                                    >
                                        <span class="material-symbols-rounded text-[17px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
