<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    MapPin, ShoppingBag, User, FileText, LogOut, Flag,
    Home, ShieldCheck, X, Menu, Settings, ClipboardList,
    Facebook, Instagram, Twitter, CreditCard, Info, HelpCircle
} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue'; 
import GlobalLoader from '@/Components/Base/GlobalLoader.vue';
import Toast from '@/Components/Base/Toast.vue';

const props = defineProps({
    isProfileSection: { type: Boolean, default: false }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const location = computed(() => page.props.location_context || { label: 'SIN SEÑAL', type: 'branch' });
const cartCount = computed(() => page.props.cart_summary?.count || 0);

// Simulación de orden activa para la telemetría (esto debe venir de props/page)
const activeOrder = computed(() => page.props.active_order || null); 

const showHamburgerMenu = ref(false);

const toggleHamburger = () => {
    showHamburgerMenu.value = !showHamburgerMenu.value;
};

const menuItems = [
    { name: 'MI PERFIL', route: 'customer.profile.index', icon: User },
    { name: 'DIRECCIONES', route: 'customer.profile.addresses', icon: MapPin },
    { name: 'PEDIDOS', route: 'customer.orders.history', icon: FileText },
    { name: 'SEGURIDAD', route: 'customer.profile.security', icon: ShieldCheck },
];

const logout = () => {
    showHamburgerMenu.value = false;
    router.post(route('logout'));
};

const isIndexPage = computed(() => route().current('customer.shop.index'));
const isCartPage = computed(() => route().current('customer.cart.index'));
const isOrdersPage = computed(() => route().current('customer.orders.*'));

// Lógica de color de telemetría según estado
const telemetryStatusColor = computed(() => {
    if (!activeOrder.value) return 'bg-tech';
    const status = activeOrder.value.status;
    const map = {
        'pending_payment': 'bg-amber-500 shadow-[0_0_8px_#f59e0b]',
        'under_review': 'bg-blue-400 shadow-[0_0_8px_#60a5fa]',
        'preparing': 'bg-cyan-400 animate-pulse shadow-[0_0_8px_#22d3ee]',
        'dispatched': 'bg-f1-red animate-bounce shadow-[0_0_8px_#ff0000]',
        'arrived': 'bg-telemetry-green shadow-[0_0_12px_#00ff00]',
        'completed': 'bg-telemetry-green opacity-50'
    };
    return map[status] || 'bg-f1-red';
});
</script>

<template>
    <div class="customer-theme bg-f1-pattern text-foreground font-sans flex flex-col min-h-[100svh] relative w-full transition-colors duration-300">
        <GlobalLoader />
        <Toast />

        <nav class="fixed top-0 left-0 right-0 h-[64px] flex items-center bg-background/60 backdrop-blur-2xl border-b border-tech z-[60] transition-all duration-300">
            <div class="container mx-auto px-4 h-full flex items-center justify-between w-full">
                
                <Link :href="route('customer.shop.index')" class="flex items-center group">
                    <div class="w-9 h-9 bg-f1-red text-white flex items-center justify-center rounded-lg transition-all duration-300 group-hover:scale-105 shadow-neon-red">
                        <Flag :size="18" class="fill-current" />
                    </div>
                </Link>

                <div class="flex flex-col items-center justify-center min-w-0 px-2 cursor-pointer group" @click="router.visit(route('customer.profile.addresses'))">
                    <span class="text-[7px] font-mono font-black uppercase tracking-[0.2em] text-f1-red group-hover:text-f1-red-hover transition-colors">
                        [SECTOR]
                    </span>
                    <div class="flex items-center gap-1 max-w-[150px]">
                        <MapPin :size="10" class="text-muted shrink-0" />
                        <span class="text-[10px] font-bold uppercase truncate text-primary">{{ location.label }}</span>
                    </div>
                </div>

                <button @click="toggleHamburger" class="w-10 h-10 rounded-full border border-tech flex items-center justify-center bg-surface overflow-hidden transition-all duration-300 hover:border-f1-red group">
                    <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover">
                    <User v-else :size="20" class="text-primary group-hover:text-f1-red" />
                </button>
            </div>
        </nav>

        <main class="w-full mx-auto transition-all pt-[64px] pb-[80px] flex flex-col flex-1" 
            :class="isIndexPage ? 'max-w-full px-0' : 'max-w-7xl px-4 py-6'">
            
            <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-8 flex-1">
                <aside class="hidden md:block w-64 shrink-0 bg-surface border border-tech rounded-[20px] p-0 h-fit shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-tech bg-surface/50">
                        <h3 class="text-[9px] font-mono font-black uppercase tracking-widest text-f1-red mb-1">DATA PILOTO</h3>
                        <p class="font-sans font-bold uppercase truncate text-xs text-primary">{{ user.name || user.email }}</p>
                    </div>
                    <nav class="flex flex-col gap-2">
                        
                        <template v-if="user">
                            <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)" @click="showHamburgerMenu = false"
                                class="flex items-center gap-4 p-4 bg-surface/50 border border-tech rounded-2xl text-xs font-bold uppercase text-primary hover-neon-red transition-all">
                                <component :is="item.icon" :size="18" class="text-muted" /> {{ item.name }}
                            </Link>
                            <div class="my-6 border-t border-tech"></div>
                        </template>

                        <div class="flex gap-4 p-4 bg-surface/50 border border-tech rounded-2xl">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>

                        <button v-if="user" @click="logout" class="mt-4 w-full flex items-center justify-center gap-3 p-4 bg-f1-red/10 border border-f1-red text-f1-red rounded-2xl font-bold uppercase hover:bg-f1-red hover:text-white transition-all">
                            <LogOut :size="16" /> Cerrar Sesión
                        </button>
                        
                        <Link v-else :href="route('login')" class="mt-4 w-full flex items-center justify-center gap-3 p-4 bg-telemetry-green/10 border border-telemetry-green text-telemetry-green rounded-2xl font-bold uppercase hover:bg-telemetry-green hover:text-white transition-all">
                            <User :size="16" /> Iniciar Sesión
                        </Link>

                    </nav>
                </aside>
                <div class="flex-1 bg-surface border border-tech rounded-[24px] p-6 shadow-sm">
                    <slot />
                </div>
            </div>
            
            <div v-else class="flex-1 w-full relative">
                <slot />
            </div>

            <footer class="mt-12 w-full bg-surface/30 border-t border-tech py-10 px-6">
                <div class="container mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
                    <div>
                        <h4 class="text-[10px] font-mono font-black uppercase text-f1-red mb-4 tracking-tighter">Navegación</h4>
                        <ul class="space-y-2 text-xs font-bold text-muted uppercase">
                            <li><Link :href="route('customer.shop.index')" class="hover:text-primary transition-colors">Catálogo</Link></li>
                            <li><Link :href="route('customer.cart.index')" class="hover:text-primary transition-colors">Mi Carrito</Link></li>
                            <li><Link :href="route('customer.orders.history')" class="hover:text-primary transition-colors">Mis Pedidos</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-mono font-black uppercase text-f1-red mb-4 tracking-tighter">Soporte</h4>
                        <ul class="space-y-2 text-xs font-bold text-muted uppercase">
                            <li class="flex items-center gap-2"><HelpCircle :size="14" /> Centro de Ayuda</li>
                            <li class="flex items-center gap-2"><Info :size="14" /> Términos y Condiciones</li>
                        </ul>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <h4 class="text-[10px] font-mono font-black uppercase text-f1-red mb-4 tracking-tighter">Pagos Seguro</h4>
                        <div class="flex gap-4 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                            <CreditCard :size="24" />
                            <div class="w-8 h-5 bg-muted rounded"></div>
                            <div class="w-8 h-5 bg-muted rounded"></div>
                        </div>
                    </div>
                    <div class="col-span-2 md:col-span-1 flex flex-col items-end md:items-start">
                         <h4 class="text-[10px] font-mono font-black uppercase text-f1-red mb-4 tracking-tighter">Comunidad</h4>
                         <div class="flex gap-4">
                            <Facebook :size="18" class="text-muted hover:text-primary cursor-pointer" />
                            <Instagram :size="18" class="text-muted hover:text-primary cursor-pointer" />
                            <Twitter :size="18" class="text-muted hover:text-primary cursor-pointer" />
                         </div>
                    </div>
                </div>
                <div class="pt-6 border-t border-tech text-center">
                    <span class="text-[9px] font-mono text-muted uppercase tracking-[0.3em]">&copy; 2026 CYBER-SYSTEM // V 3.0.4 - RELEASE</span>
                </div>
            </footer>
        </main>

        <div v-if="activeOrder" class="fixed bottom-[80px] left-0 right-0 h-[2px] z-[65] pointer-events-none">
            <div class="h-full transition-all duration-1000 ease-in-out" :class="telemetryStatusColor" style="width: 100%;"></div>
            <div class="absolute -top-6 right-4 px-2 py-0.5 cyber-glass border border-tech rounded text-[8px] font-mono font-black text-primary uppercase">
                Estado: {{ activeOrder.status_label }}
            </div>
        </div>

        <nav class="fixed bottom-0 left-0 right-0 h-[80px] bg-background/80 backdrop-blur-2xl border-t border-tech z-[70] flex justify-around items-center px-6 md:hidden">
            <Link :href="route('customer.shop.index')" class="flex flex-col items-center justify-center h-full relative group">
                <div class="absolute -top-1 w-8 h-[2px] bg-f1-red transition-all duration-300" :class="isIndexPage ? 'opacity-100 shadow-neon-red' : 'opacity-0'"></div>
                <Home :size="24" :class="isIndexPage ? 'text-primary' : 'text-muted'" />
            </Link>
            
            <Link :href="route('customer.cart.index')" class="flex flex-col items-center justify-center h-full relative group">
                <div class="absolute -top-1 w-8 h-[2px] bg-f1-red transition-all duration-300" :class="isCartPage ? 'opacity-100 shadow-neon-red' : 'opacity-0'"></div>
                <div class="relative">
                    <ShoppingBag :size="24" :class="isCartPage ? 'text-primary' : 'text-muted'" />
                    <span v-if="cartCount > 0" class="absolute -top-2 -right-3 min-w-[16px] h-[16px] bg-f1-red text-white text-[9px] font-mono font-black rounded-full flex items-center justify-center shadow-neon-red px-1">
                        {{ cartCount }}
                    </span>
                </div>
            </Link>
            
            <Link :href="route('customer.orders.history')" class="flex flex-col items-center justify-center h-full relative group">
                <div class="absolute -top-1 w-8 h-[2px] bg-f1-red transition-all duration-300" :class="isOrdersPage ? 'opacity-100 shadow-neon-red' : 'opacity-0'"></div>
                <ClipboardList :size="24" :class="isOrdersPage ? 'text-primary' : 'text-muted'" />
            </Link>
        </nav>

        <Transition name="fast-slide-right">
            <div v-if="showHamburgerMenu" class="fixed inset-0 top-0 z-[100] flex justify-end">
                <div class="absolute inset-0 bg-background/60 backdrop-blur-md" @click="showHamburgerMenu = false"></div>
                <div class="relative w-80 h-full cyber-glass border-l border-tech p-6 flex flex-col shadow-2xl">
                    <button @click="showHamburgerMenu = false" class="self-end mb-8 p-2 text-muted hover:text-f1-red"><X :size="24" /></button>
                    
                    <div v-if="user" class="mb-10">
                        <div class="w-20 h-20 rounded-2xl bg-surface border border-tech mb-4 overflow-hidden shadow-neon-red">
                            <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover">
                            <User v-else :size="40" class="w-full h-full p-4 text-muted" />
                        </div>
                        <h3 class="text-xl font-black uppercase text-primary leading-tight">{{ user.name }}</h3>
                        <p class="text-[10px] font-mono text-muted uppercase tracking-widest">{{ user.email }}</p>
                    </div>

                    <nav class="flex flex-col gap-2">
                        
                        <template v-if="user">
                            <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)" @click="showHamburgerMenu = false"
                                class="flex items-center gap-4 p-4 bg-surface/50 border border-tech rounded-2xl text-xs font-bold uppercase text-primary hover-neon-red transition-all">
                                <component :is="item.icon" :size="18" class="text-muted" /> {{ item.name }}
                            </Link>
                            <div class="my-6 border-t border-tech"></div>
                        </template>

                        <div class="flex gap-4 p-4 bg-surface/50 border border-tech rounded-2xl">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>

                        <button v-if="user" @click="logout" class="mt-4 w-full flex items-center justify-center gap-3 p-4 bg-f1-red/10 border border-f1-red text-f1-red rounded-2xl font-bold uppercase hover:bg-f1-red hover:text-white transition-all">
                            <LogOut :size="16" /> Cerrar Sesión
                        </button>
                        
                        <Link v-else :href="route('login')" class="mt-4 w-full flex items-center justify-center gap-3 p-4 bg-telemetry-green/10 border border-telemetry-green text-telemetry-green rounded-2xl font-bold uppercase hover:bg-telemetry-green hover:text-white transition-all">
                            <User :size="16" /> Iniciar Sesión
                        </Link>

                    </nav>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fast-slide-right-enter-active, .fast-slide-right-leave-active { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease; }
.fast-slide-right-enter-from, .fast-slide-right-leave-to { transform: translateX(100%); opacity: 0; }
</style>