<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BranchSubNavTabs from '@/Components/Admin/Operations/BranchSubNavTabs.vue';

const props = defineProps({
    branch: {
        type: Object,
        required: true
    },
    schedules: {
        type: Array,
        required: true
    }
});

const daysMap = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const form = useForm({
    schedules: props.schedules.map(item => ({
        day_of_week: item.day_of_week,
        opening_time: item.opening_time ? item.opening_time.substring(0, 5) : '08:00',
        closing_time: item.closing_time ? item.closing_time.substring(0, 5) : '22:00',
        is_closed: Boolean(item.is_closed)
    }))
});

/**
 * Helper ergonómico: Replica el horario del Lunes a todos los días de la semana hábil (Martes a Viernes).
 */
const copyMondayToWeekdays = () => {
    const monday = form.schedules.find(s => s.day_of_week === 1);
    if (!monday) return;

    form.schedules.forEach(s => {
        if (s.day_of_week >= 2 && s.day_of_week <= 5) {
            s.opening_time = monday.opening_time;
            s.closing_time = monday.closing_time;
            s.is_closed = monday.is_closed;
        }
    });
};

const submit = () => {
    form.put(route('admin.operations.branches.schedules.update', props.branch.id));
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
                        Horarios Semanales — {{ branch.name }}
                    </h1>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Definición de turnos de despacho y disponibilidad comercial para los 7 días
                    </p>
                </div>
            </div>
        </template>

        <!-- Barra Contextual de la Sucursal -->
        <BranchSubNavTabs :branch="branch" />

        <div class="max-w-4xl space-y-5">
            <!-- Barra de Acciones de Horario -->
            <div class="glass-panel p-3.5 md:p-4 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-primary text-[20px]">auto_fix_high</span>
                    <button 
                        type="button" 
                        @click="copyMondayToWeekdays"
                        class="text-xs font-semibold text-primary hover:underline cursor-pointer"
                    >
                        Replicar Lunes a toda la semana laboral (Mar - Vie)
                    </button>
                </div>

                <button 
                    type="button"
                    @click="submit" 
                    :disabled="form.processing"
                    class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-4 shadow-sm"
                >
                    <span class="material-symbols-rounded text-[18px]">save</span>
                    <span>{{ form.processing ? 'Guardando...' : 'Guardar Horarios' }}</span>
                </button>
            </div>

            <!-- Matriz de Días de la Semana -->
            <div class="glass-card p-5 md:p-6 rounded-2xl border border-white/60 dark:border-white/10 shadow-sm space-y-3.5">
                <div 
                    v-for="item in form.schedules" 
                    :key="item.day_of_week"
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3.5 rounded-xl border transition-all"
                    :class="item.is_closed 
                        ? 'bg-muted/30 border-border/40 opacity-60' 
                        : 'bg-card/60 backdrop-blur-md border-border/80 hover:border-primary/30 shadow-sm'"
                >
                    <div class="flex items-center gap-3 w-40 shrink-0">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs" :class="item.is_closed ? 'bg-muted text-muted-foreground' : 'bg-primary/10 text-primary'">
                            {{ daysMap[item.day_of_week].substring(0, 2).toUpperCase() }}
                        </div>
                        <span class="text-xs font-bold text-foreground">{{ daysMap[item.day_of_week] }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Apertura</label>
                            <input 
                                v-model="item.opening_time" 
                                type="time" 
                                :disabled="item.is_closed"
                                class="admin-input font-mono text-xs w-32 disabled:opacity-40" 
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Cierre</label>
                            <input 
                                v-model="item.closing_time" 
                                type="time" 
                                :disabled="item.is_closed"
                                class="admin-input font-mono text-xs w-32 disabled:opacity-40" 
                            />
                        </div>

                        <div class="pt-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input 
                                    v-model="item.is_closed" 
                                    type="checkbox" 
                                    class="rounded-md border-border/80 text-primary focus:ring-primary/20 bg-card/60 w-4 h-4" 
                                />
                                <span class="text-xs font-semibold text-foreground">No Laborable</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
