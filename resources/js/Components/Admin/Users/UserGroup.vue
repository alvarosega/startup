//okok
<script setup>
import { ref } from 'vue';
import { 
    ChevronDown, Mail, Phone, MapPin, 
    MoreHorizontal, Edit, Trash2, User 
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    data: Object
});

defineEmits(['delete-user']);

const isOpen = ref(true);
</script>

<template>
    <div class="card overflow-hidden border-border/40 shadow-sm transition-all duration-300" :class="!isOpen ? 'bg-muted/10' : 'bg-card'">
        <button @click="isOpen = !isOpen" class="w-full px-6 py-4 flex items-center justify-between hover:bg-muted/20 transition-colors">
            <div class="flex items-center gap-4">
                <div :class="`w-10 h-10 rounded-xl bg-${data.variant}/10 text-${data.variant} flex items-center justify-center shadow-inner`">
                    <component :is="data.icon" :size="20" />
                </div>
                <div class="text-left">
                    <h3 class="font-display font-bold text-base tracking-tight text-foreground">{{ title }}</h3>
                    <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">
                        {{ data.users.length }} Miembro{{ data.users.length !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
            <ChevronDown :size="20" class="text-muted-foreground transition-transform duration-300" :class="{ 'rotate-180': isOpen }" />
        </button>

        <Transition name="expand">
            <div v-show="isOpen" class="border-t border-border/30 overflow-hidden">
                <div class="divide-y divide-border/20">
                    <div v-for="user in data.users" :key="user.id" class="p-4 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-muted/5 transition-colors group">
                        
                        <div class="flex items-center gap-4">
                            <div class="avatar avatar-md ring-2 ring-background shadow-md bg-muted text-muted-foreground font-bold">
                                {{ user.first_name?.[0]?.toUpperCase() || 'U' }}
                            </div>
                            <div>
                                <h4 class="font-bold text-foreground">
                                    {{ user.first_name || 'Usuario' }} {{ user.last_name || '' }}
                                </h4>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                    <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Mail :size="12" /> {{ user.email }}
                                    </span>
                                    <span v-if="user.phone" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Phone :size="12" /> {{ user.phone }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-6">
                            <div class="hidden lg:flex flex-col items-end">
                                <span class="badge badge-outline text-[10px]">{{ user.role_name || 'Sin Rol' }}</span>
                                <span class="text-[10px] text-muted-foreground mt-1">{{ user.branch || 'Acceso Global' }}</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.users.edit', user.id)" class="btn btn-ghost btn-sm btn-circle hover:bg-primary/10 hover:text-primary transition-all">
                                    <Edit :size="16" />
                                </Link>
                                <button @click="$emit('delete-user', user.id)" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-all">
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Transición de expansión suave */
.expand-enter-active, .expand-leave-active { 
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
    max-height: 2000px; 
    opacity: 1; 
}
.expand-enter-from, .expand-leave-to { 
    max-height: 0; 
    opacity: 0; 
    transform: translateY(-10px);
}
</style>