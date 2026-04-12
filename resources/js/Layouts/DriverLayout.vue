<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { List, User, LogOut, Package, Navigation, Activity, ShieldCheck } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const driverStatus = computed(() => {
    return user.value?.status || page.props.driver?.status || 'pending';
});

const isVerified = computed(() => driverStatus.value === 'approved');

const getUserInitial = () => {
    const name = user.value?.name || page.props.driver?.name || 'D';
    return name.charAt(0).toUpperCase();
};

const navItems = [
    { label: 'Bolsa', route: 'driver.orders.index', icon: Package, activePattern: /^\/driver\/orders\/?$/, requiresAuth: true },
    { label: 'Mi Ruta', route: 'driver.dashboard', icon: Navigation, activePattern: /^\/driver\/orders\/[A-Z0-9-]+/, requiresAuth: true },
    { label: 'Historial', route: 'driver.history', icon: List, activePattern: /^\/driver\/history/, requiresAuth: true },
    { label: 'Perfil', route: 'driver.profile.index', icon: User, activePattern: /^\/driver\/profile/, requiresAuth: false },
];

const isRouteActive = (pattern) => pattern.test(page.url);

onMounted(() => {
    if (!isVerified.value && !page.url.startsWith('/driver/profile')) {
        router.visit(route('driver.profile.index'));
    }
});
</script>

<template>
    <div class="h-screen w-full bg-background flex flex-col font-sans text-foreground overflow-hidden selection:bg-primary/20"> 
        
        <header class="shrink-0 glass-titanium h-16 px-6 z-50 flex justify-between items-center border-b border-border/40 relative shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary rounded-xl flex items-center justify-center font-black text-sm text-primary-foreground shadow-f1-glow italic tracking-tighter">
                    EL
                </div>
                <div class="flex flex-col leading-none">
                    <h1 class="font-black italic tracking-tighter text-lg uppercase">
                        Driver<span class="text-primary">OS</span>
                    </h1>
                    <span class="text-[10px] font-mono font-black text-muted-foreground tracking-[0.2em]">SISTEMA_V4.0</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-black uppercase tracking-wider transition-all duration-700 shadow-sm"
                     :class="isVerified ? 'bg-success/10 border-success/30 text-success' : 'bg-warning/10 border-warning/30 text-warning animate-pulse'">
                    <span class="w-2 h-2 rounded-full shadow-[0_0_8px_currentColor]" :class="isVerified ? 'bg-success' : 'bg-warning'"></span>
                    <span class="hidden sm:inline">{{ isVerified ? 'Operativo' : 'En Revisión' }}</span>
                </div>
                
                <Link :href="route('driver.profile.index')" 
                      class="w-10 h-10 rounded-xl bg-secondary border border-border flex items-center justify-center text-xs font-black transition-all active:scale-90 hover:border-primary/50 group">
                    <span class="group-hover:text-primary transition-colors">{{ getUserInitial() }}</span>
                </Link>
            </div>
        </header>
    
        <main class="flex-1 relative w-full h-full overflow-y-auto z-0 bg-neutral-50 dark:bg-neutral-950">
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] dark:opacity-[0.07]" 
                 style="background-image: radial-gradient(var(--primary) 1px, transparent 0); background-size: 40px 40px;"></div>
            
            <div class="relative z-10 max-w-4xl mx-auto min-h-full">
                <slot />
            </div>
        </main>
    
        <nav class="shrink-0 glass-titanium border-t border-border/40 h-20 flex justify-around items-center z-50 px-2 pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
            
            <template v-for="item in navItems" :key="item.route">
                <Link v-if="isVerified || !item.requiresAuth" 
                      :href="route(item.route)" 
                      class="flex flex-col items-center justify-center w-full h-full transition-all duration-500 group relative"
                      :class="isRouteActive(item.activePattern) ? 'text-primary' : 'text-muted-foreground hover:text-foreground'">
                    
                    <div class="relative flex flex-col items-center transition-transform duration-500 ease-ios"
                         :class="{ '-translate-y-1.5': isRouteActive(item.activePattern) }">
                        <component :is="item.icon" 
                                   :size="22" 
                                   :stroke-width="isRouteActive(item.activePattern) ? 2.8 : 2.2" 
                                   class="group-active:scale-90 transition-transform" />
                        
                        <span class="text-[10px] font-black mt-1.5 uppercase tracking-tighter transition-opacity"
                              :class="isRouteActive(item.activePattern) ? 'opacity-100' : 'opacity-60 group-hover:opacity-100'">
                            {{ item.label }}
                        </span>
                    </div>

                    <div v-if="isRouteActive(item.activePattern)" 
                         class="absolute bottom-2 w-1 h-1 bg-primary rounded-full shadow-[0_0_12px_var(--primary)]"></div>
                </Link>
                
                <div v-else class="flex flex-col items-center justify-center w-full h-full text-muted-foreground/20 cursor-not-allowed">
                    <ShieldCheck :size="20" :stroke-width="1.5" />
                    <span class="text-[9px] font-black mt-1 uppercase tracking-tighter">Locked</span>
                </div>
            </template>

            <Link :href="route('driver.logout')" method="post" as="button" 
                  class="flex flex-col items-center justify-center w-full h-full text-muted-foreground/30 hover:text-error transition-all group active:scale-90">
                <LogOut :size="20" stroke-width="2.2" class="group-hover:rotate-12 transition-transform" />
                <span class="text-[9px] font-black mt-1 uppercase tracking-tighter">Eject</span>
            </Link>
            
        </nav>
    </div>
</template>

<style scoped>
.pb-safe { padding-bottom: env(safe-area-inset-bottom); }

/* Efecto de resplandor dinámico para elementos activos */
.text-primary svg {
    filter: drop-shadow(0 0 5px hsl(var(--primary) / 0.3));
}
</style>