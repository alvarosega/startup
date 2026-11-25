<script setup>
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    operator: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    first_name: props.operator.first_name || '',
    last_name: props.operator.last_name || '',
    email: props.operator.email || '',
    phone: props.operator.phone || '',
    password: '',
    branch_id: props.operator.branch_id || '',
    is_active: props.operator.is_active,
    roles: [...(props.operator.roles || [])],
    permissions: [...(props.operator.permissions || [])],
});

const isGlobalSuperAdmin = computed(() => form.roles.includes('super_admin'));

watch(isGlobalSuperAdmin, (val) => {
    if (val) {
        form.branch_id = '';
    }
});

const toggleRole = (roleName) => {
    const idx = form.roles.indexOf(roleName);
    if (idx > -1) {
        if (form.roles.length > 1) {
            form.roles.splice(idx, 1);
        }
    } else {
        form.roles.push(roleName);
    }
};

const submit = () => {
    form.put(route('staff.update', props.operator.id));
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link
                    :href="route('staff.index')"
                    class="p-1 rounded text-muted-foreground hover:text-foreground hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                >
                    <span class="material-symbols-rounded text-[20px] block">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground uppercase">
                        Editar Operador
                    </h1>
                    <p class="text-xs text-muted-foreground">
                        Modificación de roles, sucursal y credenciales de {{ operator.first_name }} {{ operator.last_name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto">
            <form @submit.prevent="submit" class="space-y-5">
                
                <!-- Datos Personales -->
                <div class="bg-card border border-border rounded-md shadow-flat p-4 md:p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground border-b border-border pb-2 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm">badge</span>
                        <span>Identificación del Operador</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-foreground">Nombre(s) *</label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                required
                                class="admin-input text-xs w-full"
                                :class="{ 'border-destructive': form.errors.first_name }"
                            />
                            <span v-if="form.errors.first_name" class="text-destructive text-[11px] block">{{ form.errors.first_name }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-foreground">Apellido(s) *</label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                required
                                class="admin-input text-xs w-full"
                                :class="{ 'border-destructive': form.errors.last_name }"
                            />
                            <span v-if="form.errors.last_name" class="text-destructive text-[11px] block">{{ form.errors.last_name }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-foreground">Email Corporativo *</label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="admin-input text-xs w-full font-mono"
                                :class="{ 'border-destructive': form.errors.email }"
                            />
                            <span v-if="form.errors.email" class="text-destructive text-[11px] block">{{ form.errors.email }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-foreground">Teléfono / Celular</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="admin-input text-xs w-full font-mono"
                                :class="{ 'border-destructive': form.errors.phone }"
                            />
                            <span v-if="form.errors.phone" class="text-destructive text-[11px] block">{{ form.errors.phone }}</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-foreground">Actualizar Contraseña (Opcional)</label>
                        <input
                            v-model="form.password"
                            type="password"
                            minlength="8"
                            placeholder="Dejar en blanco para mantener la contraseña actual"
                            class="admin-input text-xs w-full font-mono"
                            :class="{ 'border-destructive': form.errors.password }"
                        />
                        <span v-if="form.errors.password" class="text-destructive text-[11px] block">{{ form.errors.password }}</span>
                    </div>
                </div>

                <!-- Roles -->
                <div class="bg-card border border-border rounded-md shadow-flat p-4 md:p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground border-b border-border pb-2 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm">security</span>
                        <span>Roles y Nivel de Acceso *</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div
                            v-for="r in options.roles"
                            :key="r.id"
                            @click="toggleRole(r.name)"
                            class="border rounded-md p-3 cursor-pointer select-none transition-all duration-75 flex items-start gap-2.5"
                            :class="[
                                form.roles.includes(r.name)
                                    ? 'border-primary bg-primary/5 text-foreground'
                                    : 'border-border hover:border-neutral-400 dark:hover:border-neutral-600 bg-background text-muted-foreground'
                            ]"
                        >
                            <input
                                type="checkbox"
                                :checked="form.roles.includes(r.name)"
                                class="rounded border-border text-primary focus:ring-primary mt-0.5"
                            />
                            <div>
                                <span class="text-xs font-bold block leading-tight">{{ r.label }}</span>
                                <span class="text-[10px] text-muted-foreground font-mono mt-0.5 block leading-tight">{{ r.name }}</span>
                            </div>
                        </div>
                    </div>
                    <span v-if="form.errors.roles" class="text-destructive text-[11px] block">{{ form.errors.roles }}</span>
                </div>

                <!-- Sucursal Dinámica -->
                <div class="bg-card border border-border rounded-md shadow-flat p-4 md:p-6 space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted-foreground border-b border-border pb-2 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-sm">storefront</span>
                        <span>Alcance Territorial y Sucursal</span>
                    </h2>

                    <div
                        v-if="isGlobalSuperAdmin"
                        class="p-3 bg-purple-500/10 border border-purple-500/20 rounded-md text-purple-600 dark:text-purple-400 text-xs flex items-center gap-2 font-medium"
                    >
                        <span class="material-symbols-rounded text-base shrink-0">public</span>
                        <span>Este usuario posee el rol <strong>SuperAdmin</strong>, por lo que tiene alcance global en todas las sucursales sin restricción territorial.</span>
                    </div>

                    <div v-else class="space-y-1">
                        <label class="text-xs font-semibold text-foreground">Sucursal Asignada *</label>
                        <select
                            v-model="form.branch_id"
                            required
                            class="admin-input text-xs w-full"
                            :class="{ 'border-destructive': form.errors.branch_id }"
                        >
                            <option value="" disabled>Seleccione la sucursal de operaciones</option>
                            <option v-for="b in options.branches" :key="b.id" :value="b.id">
                                {{ b.name }} ({{ b.city }})
                            </option>
                        </select>
                        <span v-if="form.errors.branch_id" class="text-destructive text-[11px] block">{{ form.errors.branch_id }}</span>
                    </div>

                    <!-- Estado Activo -->
                    <div class="pt-2 border-t border-border flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-foreground block">Estado de la Cuenta</span>
                            <span class="text-[11px] text-muted-foreground block">{{ form.is_active ? 'La cuenta está activa' : 'La cuenta está suspendida' }}</span>
                        </div>
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                        />
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link
                        :href="route('staff.index')"
                        class="px-4 py-2 border border-border rounded text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="admin-btn-primary inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider py-2 px-5 disabled:opacity-60"
                    >
                        <span class="material-symbols-rounded text-base">save</span>
                        <span>{{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
