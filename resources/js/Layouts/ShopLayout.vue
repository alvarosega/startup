<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Gauge,
    Tag,
    ShieldCheck,
    MapPin,
    AlertTriangle,
    Search,
    User
} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue';

const page = usePage();
const isSearchActive = ref(false);
const searchQuery = ref('');
const isSidebarExpanded = ref(false);

// Sincronización con el middleware unificado
const user = computed(() => page.props.auth?.customer);
const location = computed(() => page.props.location_context || { label: 'LOCALIZANDO...', type: 'branch' });
const isOutOfCoverage = computed(() => page.props.shop_context?.is_out_of_coverage ?? false);

const locationText = computed(() => {
    if (!user.value) return 'INVITADO'; 
    if (isOutOfCoverage.value) return 'FUERA DE COBERTURA'; 
    return location.value.label; 
});

const locationSubtext = computed(() => {
    if (!user.value) return 'Catálogo Base';
    if (isOutOfCoverage.value) return 'Cambiar Ubicación';
    return 'Entregar en';
});

const userFullName = computed(() => {
    const profile = user.value?.profile;
    if (!profile) return 'Invitado';
    return `${profile.first_name} ${profile.last_name}`.toUpperCase();
});

const userInitials = computed(() => {
    const profile = user.value?.profile;
    if (!profile?.first_name) return '??';
    return `${profile.first_name[0] ?? ''}${profile.last_name[0] ?? ''}`.toUpperCase();
});

const navigation = [
    { name: 'Inicio',       icon: Gauge,       route: 'customer.index' },
    { name: 'Promociones',  icon: Tag,         route: 'customer.index' },
    { name: 'Direcciones',  icon: MapPin,      route: 'customer.profile.addresses.index' }, 
    { name: 'Seguridad',    icon: ShieldCheck, route: 'customer.profile.security' },
];

const filteredNavigation = computed(() => {
    return navigation.filter(item => ['customer.index'].includes(item.route) || !!user.value);
});

const logout = () => router.post(route('customer.logout'));

const toggleSearch = () => {
    isSearchActive.value = !isSearchActive.value;
    if (isSearchActive.value) {
        setTimeout(() => document.getElementById('global-search')?.focus(), 75);
    }
};
</script>

<template>
    <div class="du-cyber-canvas flex min-h-[100svh] text-foreground font-sans transition-colors duration-150 overflow-x-hidden selection:bg-primary/30">
        
        <!-- ESCRITORIO: ASIDE CON BOTÓN DE ENLACE DIRECTO AL PERFIL -->
        <aside 
            @mouseenter="isSidebarExpanded = true"
            @mouseleave="isSidebarExpanded = false"
            class="hidden lg:flex fixed left-0 top-0 h-full z-[100] flex-col bg-card border-r border-border transition-all duration-150 ease-f1"
            :class="isSidebarExpanded ? 'w-64' : 'w-[76px]'"
        >
            <div class="h-16 flex items-center px-6 shrink-0 border-b border-border">
                <div class="w-9 h-9 bg-primary flex items-center justify-center shrink-0">
                    <span class="text-primary-foreground font-black text-sm tracking-tighter">DU</span>
                </div>
                <div class="ml-4 flex flex-col transition-opacity duration-150 ease-f1" 
                     :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    <span class="font-black text-lg leading-none tracking-tighter uppercase">De</span>
                    <span class="text-primary-aaa dark:text-primary font-bold text-xs tracking-[0.3em] uppercase">Una</span>
                </div>
            </div>

            <!-- Navegación del Panel Lateral -->
            <nav class="flex-1 px-3 space-y-1.5 mt-4 overflow-y-auto no-scrollbar">
                <Link v-for="item in filteredNavigation" :key="item.name" :href="route().has(item.route) ? route(item.route) : '#'" 
                    class="flex items-center h-11 transition-all duration-150 ease-f1 group relative focus:ring-1 focus:ring-primary focus:outline-none"
                    :class="route().current(item.route) 
                        ? 'bg-neutral-100 dark:bg-neutral-800 text-primary-aaa dark:text-primary' 
                        : 'text-foreground hover:bg-neutral-100 dark:hover:bg-neutral-800'">
                    
                    <div class="w-[52px] flex justify-center shrink-0">
                        <component :is="item.icon" :size="20" />
                    </div>
                    
                    <span class="font-bold text-xs uppercase tracking-[0.15em] whitespace-nowrap transition-all duration-150 ease-f1"
                          :class="isSidebarExpanded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4'">
                        {{ item.name }}
                    </span>

                    <div v-if="route().current(item.route)" 
                         class="absolute left-0 w-1 h-full bg-primary animate-in fade-in slide-in-from-left-1 duration-150">
                    </div>
                </Link>
            </nav>

            <!-- RECTIFICACIÓN: El contenedor inferior completo muta a un enlace dinámico hacia el módulo de perfil -->
            <div class="p-3 border-t border-border bg-card">
                <Link 
                    :href="user ? route('customer.profile.index') : route('customer.login')"
                    class="flex items-center w-full h-12 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors duration-150 overflow-hidden group cursor-pointer px-1 rounded-md"
                >
                    <div class="w-[52px] flex justify-center shrink-0">
                        <div class="w-8 h-8 border border-border overflow-hidden bg-background flex items-center justify-center rounded-none select-none">
                            <img v-if="user?.profile?.avatar_source" :src="`/assets/avatars/${user.profile.avatar_source}`" class="w-full h-full object-cover" />
                            <span v-else class="text-xs font-black text-foreground">{{ userInitials }}</span>
                        </div>
                    </div>
                    <div class="ml-2 flex flex-col items-start transition-opacity duration-150 ease-f1" :class="isSidebarExpanded ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                        <span class="text-xs font-extrabold truncate w-32 uppercase tracking-tighter text-foreground">{{ userFullName }}</span>
                        
                        <div v-if="!user" class="flex gap-2 items-center leading-none mt-0.5">
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest">Entrar</span>
                        </div>
                        
                        <!-- El botón intercepta la propagación para evitar disparar el enlace del contenedor padre -->
                        <button v-else @click.stop.prevent="logout" class="text-[10px] font-black text-primary-aaa dark:text-primary hover:underline uppercase tracking-widest mt-0.5">
                            Cerrar Sesión
                        </button>
                    </div>
                </Link>
            </div>
        </aside>

        <!-- CUERPO DE TRABAJO -->
        <main class="flex-1 flex flex-col w-full pt-16 lg:pt-20 transition-all duration-150 ease-f1 relative z-10" 
              :class="isSidebarExpanded ? 'lg:pl-64' : 'lg:pl-[76px]'">
            
            <!-- HEADER DE ESCRITORIO / MÓVIL -->
            <header class="fixed top-0 left-0 lg:left-[76px] right-0 z-[60] w-full lg:w-auto transition-all duration-150 ease-f1">
                <div class="h-16 flex items-center justify-between px-4 lg:px-8 max-w-7xl mx-auto w-full bg-background border-b transition-colors duration-200 border-border">
                
                    <div v-if="!isSearchActive" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-2 duration-150">
                        <button @click="user ? router.visit(route('customer.profile.addresses.index')) : router.visit(route('customer.register'))" class="flex items-center gap-3 px-2 py-1.5 bg-transparent transition-colors duration-150 group focus:outline-none">
                            <div class="w-8 h-8 bg-transparent flex items-center justify-center">
                                <AlertTriangle v-if="isOutOfCoverage && user" :size="16" :stroke-width="1.5" class="text-red-500 animate-pulse" />
                                <MapPin v-else :size="16" :stroke-width="1.5" class="text-primary" />
                            </div>
                            <div class="flex flex-col items-start leading-none">
                                <span class="text-[9px] font-black uppercase tracking-widest" :class="isOutOfCoverage && user ? 'text-red-400' : 'text-neutral-500'">{{ locationSubtext }}</span>
                                <span class="text-xs font-bold text-foreground truncate max-w-[150px] uppercase tracking-wider transition-colors"
                                      :class="isOutOfCoverage && user ? 'text-red-500 font-black' : ''">
                                    {{ locationText }}
                                </span>
                            </div>
                        </button>
                    </div>

                    <div class="flex-1 flex justify-end items-center gap-2">
                        <div v-if="isSearchActive" class="w-full flex items-center gap-2 animate-in slide-in-from-right-2 duration-150">
                            <div class="flex-1 relative">
                                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-primary" :size="16" :stroke-width="1.5" />
                                <input 
                                    id="global-search" 
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="¿QUÉ BUSCAS HOY?"
                                    class="w-full h-10 bg-transparent border-b border-border border-t-0 border-x-0 pl-11 pr-4 text-xs font-bold tracking-widest focus:ring-0 focus:border-primary text-foreground placeholder:text-neutral-500 uppercase transition-colors duration-150"
                                />
                            </div>
                            <button @click="toggleSearch" class="p-2.5 bg-transparent text-foreground active:scale-95 transition-transform duration-75"><X :size="18" :stroke-width="1.5" /></button>
                        </div>

                        <div v-else class="flex items-center gap-2">
                            <button @click="toggleSearch" class="p-2.5 bg-transparent text-foreground active:scale-95 transition-all duration-75">
                                <Search :size="20" :stroke-width="1.5" />
                            </button>
                            
                            <div class="hidden sm:flex items-center gap-2 px-2 bg-transparent">
                                <ThemeToggler />
                                <FullScreenToggler />
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Renderizado de Vistas -->
            <section class="flex-1 w-full mx-auto pb-8 pt-4" :class="route().current('customer.index') ? 'max-w-full' : 'max-w-7xl px-4 lg:px-8'">
                <slot />
            </section>

            <footer class="w-full border-t border-border py-12 px-6 lg:px-8 bg-card mt-auto relative z-10 select-none">
                <div class="max-w-7xl mx-auto space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-foreground">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 bg-primary flex items-center justify-center text-primary-foreground text-[8px] font-black leading-none">DU</div>
                            <span class="tracking-[0.3em]">De Una Digital</span>
                        </div>
                        <div class="flex gap-8 opacity-90">
                            <a href="#" class="hover:text-primary transition-colors duration-75">Términos</a>
                            <a href="#" class="hover:text-primary transition-colors duration-75">Privacidad</a>
                            <a href="#" class="hover:text-primary transition-colors duration-75">Soporte</a>
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <!-- MÓVIL: MENÚ INFERIOR CON ICONO DE PERFIL FIJO EN EL EXTREMO DERECHO -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 h-[64px] bg-card border-t border-border z-[100] flex justify-around items-center px-1 pb-safe select-none">
            <button @click="toggleSearch" class="flex flex-col items-center justify-center w-16 h-full text-foreground active:scale-95 duration-75">
                <Search :size="20" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Buscar</span>
            </button>

            <Link :href="route('customer.index')" class="flex flex-col items-center justify-center w-16 h-full border-b-4 transition-colors duration-75" :class="route().current('customer.index') ? 'border-primary text-primary' : 'border-transparent text-foreground'">
                <Gauge :size="22" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Inicio</span>
            </Link>

            <!-- RECTIFICACIÓN: Enlace atómico en la parte derecha para acceso inmediato a la suite de perfiles -->
            <Link 
                :href="user ? route('customer.profile.index') : route('customer.login')" 
                class="flex flex-col items-center justify-center w-16 h-full border-b-4 transition-colors duration-75"
                :class="route().current('customer.profile.*') ? 'border-primary text-primary' : 'border-transparent text-foreground'"
            >
                <!-- Renderiza el avatar real si está autenticado; de lo contrario expone el nodo genérico de login -->
                <div v-if="user?.profile?.avatar_source" class="w-5 h-5 rounded-full border border-border overflow-hidden bg-background flex items-center justify-center select-none">
                    <img :src="`/assets/avatars/${user.profile.avatar_source}`" class="w-full h-full object-cover" />
                </div>
                <component v-else :is="User" :size="20" :stroke-width="2.5" />
                <span class="text-[9px] font-black uppercase tracking-tight mt-0.5">Perfil</span>
            </Link>
        </nav>
    </div>
</template>

<style scoped>
:deep(svg) { stroke-width: 2 !important; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.pb-safe { padding-bottom: env(safe-area-inset-bottom); }

.du-cyber-canvas {
    position: relative;
    background-color: hsl(var(--background));
}

.du-cyber-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image: 
        linear-gradient(90deg, hsl(var(--border) / 0.5) 1px, transparent 1px),
        linear-gradient(180deg, hsl(var(--border) / 0.5) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 60%);
    -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 60%);
}
</style>