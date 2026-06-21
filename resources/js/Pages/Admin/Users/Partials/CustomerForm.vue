<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLocationWorkflow from '@/Components/Admin/Maps/AdminLocationWorkflow.vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const props = defineProps({
    user: { type: Object, default: null },
    branches: Array
});

const isEditing = computed(() => !!props.user);

const form = useForm({
    id: props.user?.id || null,
    first_name: props.user?.first_name || '',
    last_name: props.user?.last_name || '',
    phone: props.user?.phone || '',
    email: props.user?.email || '',
    password: '',
    country_code: props.user?.country_code || 'BO',
    alias: props.user?.alias || '',
    address: props.user?.address || '',
    details: props.user?.reference || '',
    latitude: props.user?.latitude ? parseFloat(props.user.latitude) : -16.5000,
    longitude: props.user?.longitude ? parseFloat(props.user.longitude) : -68.1500,
    branch_id: props.user?.branch_id || null,
    avatar_source: props.user?.avatar_source || 'avatar_1.png',
    is_active: props.user ? !!props.user.is_active : true,
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.users.update', props.user.id), { preserveScroll: true });
    } else {
        form.post(route('admin.users.store'), { preserveScroll: true });
    }
};
const onPhoneInput = (phone, obj) => {
    if (obj?.country?.iso2) {
        form.country_code = obj.country.iso2.toUpperCase();
    }
};
</script>

<template>
    <div class="w-full max-w-7xl mx-auto">
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <div class="lg:col-span-6 space-y-6 bg-card border border-border rounded-md p-6 shadow-flat">
                <div class="flex items-center gap-2 pb-3 border-b border-border/60 select-none">
                    <span class="material-symbols-rounded text-primary text-lg">badge</span>
                    <h2 class="text-xs font-mono font-black text-foreground tracking-wider uppercase">
                        {{ isEditing ? 'Modificación Registro Socio' : 'Apertura Cuenta Operativa' }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nombres</label>
                        <input v-model="form.first_name" type="text" class="admin-input uppercase font-mono" :disabled="isEditing" required />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Apellidos</label>
                        <input v-model="form.last_name" type="text" class="admin-input uppercase font-mono" :disabled="isEditing" required />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Email corporativo</label>
                    <input v-model="form.email" type="email" class="admin-input font-mono" :disabled="isEditing" required />
                </div>

                <div class="space-y-1.5 select-none">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Terminal Móvil</label>
                    <vue-tel-input 
                        v-model="form.phone" 
                        @on-input="onPhoneInput" 
                        mode="international" 
                        class="admin-tel-input" 
                        :disabled="isEditing"
                        :dropdown-options="{ disabled: isEditing }"
                    />
                    <p v-if="form.errors.phone" class="text-xs text-error font-semibold mt-1 uppercase font-mono">{{ form.errors.phone }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Clave de Acceso</label>
                    <input v-model="form.password" type="password" class="admin-input" :required="!isEditing" placeholder="••••••••" />
                </div>

                <div v-if="isEditing" class="pt-2 border-t border-border/40 flex items-center justify-between select-none">
                    <span class="text-xs font-semibold text-foreground">Estado de Sincronización</span>
                    <select v-model="form.is_active" class="admin-input w-36 text-xs font-bold uppercase py-1.5">
                        <option :value="true">ONLINE</option>
                        <option :value="false">LOCKED</option>
                    </select>
                </div>
            </div>

            <div class="lg:col-span-6 space-y-6">
                <div class="bg-card border border-border rounded-md p-4 shadow-flat">
                    <div class="grid grid-cols-5 gap-3 items-center">
                        <div class="w-14 h-14 border border-border p-1 bg-neutral-50 dark:bg-neutral-800 rounded-md mx-auto shrink-0 select-none">
                            <img :src="`/assets/avatars/${form.avatar_source}`" class="w-full h-full object-cover rounded-sm" />
                        </div>
                        <div class="col-span-4 grid grid-cols-4 gap-1.5 select-none">
                            <button v-for="i in 8" :key="i" type="button" @click="form.avatar_source = `avatar_${i}.png`"
                                    class="aspect-square border rounded-md p-0.5 bg-neutral-50 dark:bg-neutral-800/40 transition-colors"
                                    :class="form.avatar_source === `avatar_${i}.png` ? 'border-primary bg-primary/5' : 'border-border hover:border-neutral-400'">
                                <img :src="`/assets/avatars/avatar_${i}.png`" class="w-full h-full object-contain" />
                            </button>
                        </div>
                    </div>
                </div>

                <AdminLocationWorkflow :form="form" :activeBranches="branches" />

                <button type="submit" :disabled="form.processing" class="admin-btn-primary w-full py-3 inline-flex items-center justify-center gap-2 font-bold uppercase tracking-wider text-xs select-none">
                    <span class="material-symbols-rounded text-lg">{{ isEditing ? 'save' : 'cloud_upload' }}</span>
                    <span>{{ form.processing ? 'Procesando...' : (isEditing ? 'Guardar Cambios' : 'Confirmar Registro') }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
<style scoped>
:deep(.admin-tel-input) {
    @apply bg-card border border-input rounded-md font-mono transition-shadow duration-75 !important;
    height: 34px; /* Alineación perfecta con el padding de .admin-input */
}
:deep(.vti__input) {
    @apply bg-transparent text-foreground text-sm font-medium focus:outline-none !important;
}
:deep(.vti__dropdown) {
    @apply border-r border-border/60 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-l-md px-2 !important;
}
:deep(.vti__dropdown-list) {
    @apply bg-card border border-border rounded-md shadow-flat max-h-48 overflow-y-auto z-50 text-foreground !important;
}
:deep(.vti__dropdown-item) {
    @apply text-xs font-mono py-1.5 px-3 hover:bg-neutral-100 dark:hover:bg-neutral-800 !important;
}
:deep(.admin-tel-input:focus-within) {
    @apply ring-1 ring-ring border-ring !important;
}

</style>