<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
        closing_time: item.closing_time ? item.closing_time.substring(0, 5) : '23:00',
        is_closed: Boolean(item.is_closed)
    }))
});

const submit = () => {
    form.put(route('admin.operations.branches.schedules.update', props.branch.id));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            Horarios Operativos — {{ branch.name }}
        </template>

        <div class="space-y-4 max-w-4xl">
            <div class="flex items-center justify-between bg-card p-4 border border-border rounded-md shadow-flat">
                <Link 
                    :href="route('admin.operations.branches.index')"
                    class="px-3 py-1.5 bg-secondary text-secondary-foreground text-xs font-semibold rounded border border-border hover:bg-neutral-200 dark:hover:bg-neutral-800"
                >
                    ← Volver a Sucursales
                </Link>
                <button 
                    @click="submit" 
                    :disabled="form.processing"
                    class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold"
                >
                    <span class="material-symbols-rounded text-base">save</span>
                    <span>{{ form.processing ? 'Guardando...' : 'Guardar Horarios' }}</span>
                </button>
            </div>

            <div class="bg-card border border-border rounded-md shadow-flat p-5">
                <div class="space-y-4">
                    <div 
                        v-for="(item, index) in form.schedules" 
                        :key="item.day_of_week"
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3 rounded-md border border-border"
                        :class="{ 'bg-neutral-50 dark:bg-neutral-900/40 opacity-70': item.is_closed }"
                    >
                        <div class="w-32">
                            <span class="text-sm font-bold text-foreground">{{ daysMap[item.day_of_week] }}</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase">Apertura</label>
                                <input 
                                    v-model="item.opening_time" 
                                    type="time" 
                                    :disabled="item.is_closed"
                                    class="admin-input font-mono text-xs w-32" 
                                />
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase">Cierre</label>
                                <input 
                                    v-model="item.closing_time" 
                                    type="time" 
                                    :disabled="item.is_closed"
                                    class="admin-input font-mono text-xs w-32" 
                                />
                            </div>

                            <div class="pt-4">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input 
                                        v-model="item.is_closed" 
                                        type="checkbox" 
                                        class="rounded border-input text-primary focus:ring-ring bg-card w-4 h-4" 
                                    />
                                    <span class="text-xs font-semibold text-foreground">No Laborable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="form.hasErrors" class="mt-4 p-3 bg-destructive/10 border border-destructive/20 rounded text-error text-xs font-medium">
                    Revise los horarios ingresados. La hora de cierre debe ser posterior a la de apertura.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>