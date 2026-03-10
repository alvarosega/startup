<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserFilters from '@/Components/Admin/Users/UserFilters.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import { 
    UserPlus, Search, 
    MessageCircle, SlidersHorizontal,
    Cpu, Terminal, MoreVertical, 
    Edit, Trash2, Radar, MapPin, 
    Mail, Phone, Lock, CheckCircle2 
} from 'lucide-vue-next';

const props = defineProps({
    users: Object, // Trae la data paginada de Customers
    branches: Array,
    filters: Object
});

const params = ref({
    search: props.filters?.search || '',
    branch_id: props.filters?.branch_id || '',
});

const showMobileFilters = ref(false);
const activeMenuUserId = ref(null);

/**
 * AGRUPACIÓN POR SUCURSAL (Silo Exclusivo de Clientes)
 */
const groupedUsers = computed(() => {
    // Protección Unwrapping Vue 3 (Evita crasheo si data es nula temporalmente)
    const list = props.users?.data || [];
    if (list.length === 0) return {};

    return list.reduce((acc, user) => {
        // En este silo, la única división física es la sucursal asignada por GPS
        const groupName = user.branch ? user.branch.toUpperCase().replace(/\s+/g, '_') : 'ZONA_LOGÍSTICA_NO_ASIGNADA';

        if (!acc[groupName]) acc[groupName] = [];
        acc[groupName].push(user);
        return acc;
    }, {});
});

const getWhatsappLink = (phone) => {
    if (!phone) return '#';
    const cleanPhone = phone.replace(/\D/g, '');
    return `https://wa.me/${cleanPhone}`; 
};

// Control de Menú Contextual
const toggleMenu = (userId) => {
    activeMenuUserId.value = activeMenuUserId.value === userId ? null : userId;
};

const closeMenu = () => {
    activeMenuUserId.value = null;
};

// Acción Zero-Trust
const deleteUser = (user) => {
    if (confirm(`⚠️ ALERTA DE SEGURIDAD: ¿CONFIRMAR ELIMINACIÓN DEL CLIENTE? // ${user.name}`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: () => closeMenu()
        });
    }
};

// Motor de Búsqueda Reactivo
watch(params, debounce((val) => {
    router.get(route('admin.users.index'), val, { 
        preserveState: true, 
        replace: true, 
        preserveScroll: true 
    });
}, 300), { deep: true });

const clearFilters = () => {
    params.value = { search: '', branch_id: '' };
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between pt-1 pb-1">
                <div class="relative group/header">
                    <h1 class="text-2xl font-display font-black tracking-tight text-foreground leading-none glitch-text" 
                        data-text="SILO DE CLIENTES">
                        SILO DE CLIENTES
                    </h1>
                    <p class="text-[10px] text-primary font-mono mt-1 uppercase tracking-[0.3em] flex items-center gap-2">
                        <Cpu :size="12" class="animate-pulse" />
                        RED LOGÍSTICA // SISTEMA ACTIVO
                        <Terminal :size="12" class="animate-pulse" />
                    </p>
                    <div class="absolute -bottom-2 left-0 w-0 h-[1px] bg-primary group-hover/header:w-full transition-all duration-700"></div>
                </div>
                
                <Link :href="route('admin.users.create')" 
                      class="hidden md:flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-mono text-xs font-bold border border-primary/50 relative overflow-hidden group/btn">
                    <UserPlus :size="16" class="relative z-10" /> 
                    <span class="relative z-10">NUEVO_CLIENTE</span>
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-500"></span>
                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                </Link>
            </div>
        </template>

        <div class="space-y-5 pb-32 md:pb-12 relative">
            
            <div class="sticky top-0 z-30 -mx-4 px-4 py-2 bg-background/80 backdrop-blur-xl border-b border-primary/20 transition-all">
                <div class="flex gap-2">
                    <div class="relative flex-1 group/search">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/search:text-primary transition-colors" :size="16" />
                        <input 
                            v-model="params.search" 
                            type="text" 
                            placeholder="> BUSCAR CLIENTE // NOMBRE, EMAIL, TEL..." 
                            class="w-full pl-9 pr-4 py-2.5 bg-card/50 border border-border font-mono text-sm focus:border-primary focus:shadow-neon-primary transition-all outline-none"
                        >
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 w-1 h-4 bg-primary animate-pulse"></div>
                    </div>
                    <button @click="showMobileFilters = true" 
                            class="flex items-center justify-center w-10 h-10 bg-card border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/filter">
                        <SlidersHorizontal :size="18" class="group-hover/filter:text-primary transition-colors" />
                        <span v-if="params.branch_id" class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/filter:opacity-100 transition-opacity"></span>
                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/filter:opacity-100 transition-opacity"></span>
                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/filter:opacity-100 transition-opacity"></span>
                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/filter:opacity-100 transition-opacity"></span>
                    </button>
                </div>
            </div>

            <div v-if="users.data && users.data.length > 0" class="space-y-10 animate-in fade-in slide-in-from-bottom-2 duration-500">
                
                <div v-for="(groupUsers, groupName) in groupedUsers" :key="groupName" class="space-y-4">
                    
                    <div class="flex items-center gap-3 px-1 group/sector">
                        <div class="p-1.5 bg-primary/10 text-primary border border-primary/30">
                            <Radar :size="14" />
                        </div>
                        <h3 class="text-[11px] font-mono font-bold text-foreground uppercase tracking-[0.2em] relative">
                            [{{ groupName }}]
                            <span class="absolute -bottom-1 left-0 w-0 h-[1px] bg-primary group-hover/sector:w-full transition-all duration-500"></span>
                        </h3>
                        <div class="h-px bg-gradient-to-r from-primary/30 to-transparent flex-1"></div>
                        <span class="text-[10px] font-mono text-primary bg-primary/10 px-2 py-0.5 border border-primary/30">
                            {{ String(groupUsers.length).padStart(2, '0') }}_UNIDADES
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="user in groupUsers" :key="user.id" 
                            class="group/card relative bg-card transition-all duration-500 p-4 flex items-center gap-4"
                            :class="activeMenuUserId === user.id ? 'z-50 overflow-visible border-primary/80 shadow-neon-primary' : 'z-10 overflow-hidden border-border hover:border-primary/50 border'">
                            
                            <div class="relative w-10 h-10 shrink-0 overflow-hidden border border-primary/30 bg-background group/avatar z-10">
                                <img v-if="user.avatar?.type === 'image'" :src="user.avatar.source" class="w-full h-full object-cover grayscale group-hover/avatar:grayscale-0 transition-all" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-primary/5 text-primary">
                                    <img :src="`/assets/avatars/${user.avatar?.source || 'avatar_1.svg'}`" class="w-6 h-6 opacity-70 group-hover/avatar:opacity-100 transition-opacity" />
                                </div>
                                <div class="absolute top-0 right-0 w-1 h-1 bg-primary"></div>
                            </div>

                            <div class="absolute inset-0 pointer-events-none opacity-0 group-hover/card:opacity-100 transition-opacity duration-500 z-0">
                                <div class="absolute top-0 left-0 w-full h-[1px] bg-primary/50 animate-scanline"></div>
                            </div>

                            <div class="flex-1 min-w-0 relative z-20">
                                <div class="flex justify-between items-start mb-0.5">
                                    <h4 class="font-mono font-bold text-foreground text-sm truncate pr-4 group-hover/card:text-primary transition-colors">
                                        {{ user.name }}
                                    </h4>
                                    
                                    <div class="relative">
                                        <button type="button" @click.stop="toggleMenu(user.id)" 
                                                class="p-1.5 text-muted-foreground hover:bg-primary/10 hover:text-primary transition-all border border-transparent hover:border-primary/30 relative group/menu">
                                            <MoreVertical :size="16" />
                                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/menu:opacity-100"></span>
                                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/menu:opacity-100"></span>
                                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/menu:opacity-100"></span>
                                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/menu:opacity-100"></span>
                                        </button>
                                        
                                        <div v-if="activeMenuUserId === user.id" 
                                            class="absolute right-0 top-8 z-[9999] w-40 bg-card border border-primary/30 shadow-neon-primary animate-in fade-in zoom-in-95 origin-top-right flex flex-col">
                                            
                                            <Link :href="route('admin.users.edit', user.id)" 
                                                class="flex items-center gap-2 w-full px-4 py-3 text-xs font-mono text-foreground hover:bg-primary/10 hover:text-primary transition-colors relative group/edit cursor-pointer">
                                                <Edit :size="13" class="pointer-events-none" /> 
                                                <span class="pointer-events-none">EDITAR</span>
                                                <span class="absolute left-0 w-0 h-full bg-primary/10 group-hover/edit:w-full transition-all duration-300 -z-10"></span>
                                            </Link>
                                            
                                            <div class="h-px bg-border mx-2"></div>
                                            
                                            <button type="button" @click.stop="deleteUser(user)" 
                                                    class="flex items-center gap-2 w-full px-4 py-3 text-xs font-mono text-destructive hover:bg-destructive/10 transition-colors relative group/delete cursor-pointer text-left">
                                                <Trash2 :size="13" class="pointer-events-none" /> 
                                                <span class="pointer-events-none">ELIMINAR</span>
                                                <span class="absolute left-0 w-0 h-full bg-destructive/10 group-hover/delete:w-full transition-all duration-300 -z-10"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-0.5 mb-3">
                                    <p class="text-[10px] font-mono text-muted-foreground truncate flex items-center gap-1.5">
                                        <Mail :size="10" class="text-primary/50" /> {{ user.email }}
                                    </p>
                                    <p v-if="user.phone" class="text-[10px] font-mono text-primary truncate flex items-center gap-1.5">
                                        <Phone :size="10" class="animate-pulse" /> 
                                        <span class="tracking-widest">{{ user.phone }}</span>
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-mono font-bold uppercase tracking-wider border border-cyan-500/50 text-cyan-500 bg-cyan-500/10">
                                        CLIENTE
                                    </span>
                                    <span v-if="!user.is_active" class="text-[8px] font-mono text-destructive uppercase tracking-wider border border-destructive/30 px-1.5 bg-destructive/5 flex items-center gap-1">
                                        <Lock :size="8" /> LOCKED
                                    </span>
                                    <span v-else class="text-[8px] font-mono text-emerald-500 uppercase tracking-wider flex items-center gap-1">
                                        <CheckCircle2 :size="8" /> ONLINE
                                    </span>
                                </div>
                            </div>

                            <div v-if="user.phone" class="shrink-0 pl-3 border-l border-border/50 relative z-20">
                                <a :href="getWhatsappLink(user.phone)" target="_blank"
                                class="w-9 h-9 bg-emerald-500/10 text-emerald-500 border border-emerald-500/30 flex items-center justify-center transition-all hover:bg-emerald-500 hover:text-white hover:shadow-[0_0_20px_rgba(16,185,129,0.5)] relative group/whatsapp"
                                title="CONTACTAR // WHATSAPP">
                                    <MessageCircle :size="18" stroke-width="2.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-24 text-center border border-dashed border-border bg-card/30">
                <div class="w-20 h-20 border-2 border-border flex items-center justify-center mb-4 relative">
                    <Search :size="40" class="text-muted-foreground" />
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="w-full h-[2px] bg-primary/30 animate-scan-slow"></div>
                    </div>
                </div>
                <h3 class="text-lg font-mono font-bold text-foreground glitch-text" data-text="SIN_REGISTROS">SIN_REGISTROS</h3>
                <p class="text-xs font-mono text-muted-foreground max-w-[200px] mx-auto mt-1">
                    NO HAY CLIENTES QUE COINCIDAN CON LOS FILTROS ACTUALES.
                </p>
                <button @click="clearFilters" class="mt-6 text-xs font-mono text-primary hover:text-primary/80 transition-colors relative group/clear">
                    LIMPIAR FILTROS //
                    <span class="absolute -bottom-1 left-0 w-0 h-[1px] bg-primary group-hover/clear:w-full transition-all duration-300"></span>
                </button>
            </div>
        </div>

        <Link :href="route('admin.users.create')" 
              class="md:hidden fixed bottom-[100px] right-6 z-40 w-14 h-14 bg-primary text-primary-foreground flex items-center justify-center border border-primary-foreground/50 shadow-neon-primary active:scale-90 transition-transform relative group/mobile">
            <UserPlus :size="28" stroke-width="2.5" />
            <span class="absolute inset-0 border border-primary animate-ping opacity-0 group-hover/mobile:opacity-100"></span>
            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground"></span>
            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground"></span>
            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground"></span>
            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground"></span>
        </Link>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] md:hidden flex items-end">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity animate-in fade-in" @click="showMobileFilters = false"></div>
            
            <div class="relative w-full bg-card border-t border-primary/30 p-8 pb-12 max-h-[90vh] overflow-y-auto animate-in slide-in-from-bottom-full duration-500">
                <div class="w-16 h-1 bg-primary/30 mx-auto mb-8"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-mono font-bold text-foreground uppercase tracking-tighter flex items-center gap-2">
                        <Terminal :size="18" class="text-primary" />
                        FILTRAR RED
                    </h2>
                    <button @click="clearFilters" class="text-[10px] font-mono text-primary bg-primary/10 px-3 py-1.5 border border-primary/30 uppercase">
                        REINICIAR
                    </button>
                </div>

                <UserFilters v-model="params" :branches="branches" layout="vertical" />

                <button @click="showMobileFilters = false" 
                        class="w-full bg-primary text-primary-foreground py-4 font-mono text-sm uppercase tracking-widest mt-10 shadow-neon-primary active:scale-95 transition-transform relative group/apply">
                    VER RESULTADOS
                    <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/apply:translate-y-0 transition-transform duration-500"></span>
                </button>
            </div>
        </div>

        <div v-if="activeMenuUserId" @click="closeMenu" class="fixed inset-0 z-[40] bg-transparent"></div>

    </AdminLayout>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes scanline {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scanline {
    animation: scanline 8s linear infinite;
}

@keyframes scan-slow {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

.animate-scan-slow {
    animation: scan-slow 12s linear infinite;
}

/* Efecto glitch mejorado */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0%, 20%, 22%, 80%, 82%, 100% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    81% { transform: skew(-2deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}
.shadow-neon-cyan {
    box-shadow: 0 0 20px #00ffff80;
}

input:focus {
    box-shadow: 0 0 0 2px hsl(var(--primary) / 0.2), 0 0 20px hsl(var(--primary) / 0.3);
}
</style>