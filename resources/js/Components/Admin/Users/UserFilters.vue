//okok 
<script setup>
import { Search, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    modelValue: Object,
    roles: Array,
    branches: Array,
    layout: { type: String, default: 'horizontal' }
});

const emit = defineEmits(['update:modelValue', 'clear']);

const updateFilter = (key, value) => {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
};
</script>

<template>
    <div :class="layout === 'horizontal' ? 'flex flex-col md:flex-row items-center gap-4 w-full' : 'flex flex-col gap-6'">
        <div class="relative flex-1 w-full">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
            <input 
                type="text" 
                placeholder="Buscar por nombre o correo..." 
                class="pl-10 !bg-muted/20 !border-transparent focus:!bg-background focus:!ring-primary/30"
                :value="modelValue.search"
                @input="updateFilter('search', $event.target.value)"
            />
        </div>

        <div :class="layout === 'horizontal' ? 'w-full md:w-60' : 'w-full'">
            <select :value="modelValue.role_id" @change="updateFilter('role_id', $event.target.value)" class="!bg-muted/20 !border-transparent">
                <option value="">Todos los Roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
        </div>

        <div :class="layout === 'horizontal' ? 'w-full md:w-60' : 'w-full'">
            <select :value="modelValue.branch_id" @change="updateFilter('branch_id', $event.target.value)" class="!bg-muted/20 !border-transparent">
                <option value="">Todas las Sedes</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
        </div>

        <button v-if="layout === 'horizontal'" @click="$emit('clear')" class="btn btn-ghost btn-sm px-4 text-muted-foreground hover:text-foreground">
            <RefreshCw :size="16" class="mr-2" /> 
            <span class="hidden lg:inline">Limpiar</span>
        </button>
    </div>
</template>