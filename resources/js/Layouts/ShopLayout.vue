<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    MapPin, ShoppingCart, User, Receipt, LogOut, Search,
    Home, ShieldCheck, X, Menu, Settings,
    CreditCard, Truck, Package, Flag, Bell, ChevronDown,
    Facebook, Instagram, Twitter, Youtube, Mail, Phone, Globe
} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue'; 
import GlobalLoader from '@/Components/Base/GlobalLoader.vue';
import Toast from '@/Components/Base/Toast.vue';

const props = defineProps({ isProfileSection: { type: Boolean, default: false } });
const page = usePage();
const user = computed(() => page.props.auth?.user);
const location = computed(() => page.props.location_context || { label: 'ENTREGA NO DEFINIDA', type: 'branch' });
const cartCount = computed(() => page.props.cart_summary?.count || 0);
const activeOrder = computed(() => page.props.active_order || null); 

// Lógica de Órdenes Activas
const activeOrderUrl = computed(() => {
    if (!activeOrder.value?.count || !activeOrder.value.latest?.id) return route('customer.orders.history');
    return activeOrder.value.count === 1 ? route('customer.orders.show', activeOrder.value.latest.id) : route('customer.orders.history');
});

const telemetryStatusColor = computed(() => {
    if (!activeOrder.value?.latest) return 'bg-muted';
    const map = {
        'pending_payment': 'bg-amber-500 shadow-[0_0_8px_#f59e0b]',
        'preparing': 'bg-cyan-400 animate-pulse',
        'dispatched': 'bg-primary animate-bounce',
        'arrived': 'bg-accent shadow-[0_0_12px_#00ff00]',
    };
    return map[activeOrder.value.latest.status] || 'bg-primary';
});

// Lógica del Sidebar
const showSidebar = ref(false);
const toggleSidebar = () => { showSidebar.value = !showSidebar.value; };

// Datos y Menús
const menuItems = [
    { name: 'Mi Perfil', route: 'customer.profile.index', icon: User },
    { name: 'Direcciones', route: 'customer.profile.addresses', icon: MapPin },
    { name: 'Mis Pedidos', route: 'customer.orders.history', icon: Receipt },
    { name: 'Seguridad', route: 'customer.profile.security', icon: ShieldCheck },
];

const customerStats = [
    { label: 'Nivel de Cliente', value: 'Platino', color: 'text-primary' },
    { label: 'Puntos Acumulados', value: '1,250 pts', color: 'text-foreground' },
    { label: 'Crédito Disponible', value: '$15.50', color: 'text-accent' },
];

const isIndexPage = computed(() => route().current('customer.shop.index'));
const isCartPage = computed(() => route().current('customer.cart.index'));
const isOrdersPage = computed(() => route().current('customer.orders.*'));

const logout = () => { router.post(route('logout')); };
</script>

<template>
    <div class="text-foreground font-sans min-h-[100svh] relative w-full transition-colors duration-500 bg-background flex flex-col">
        
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 hidden dark:block tech-dot-pattern"></div>
        </div>

        <div class="relative z-10 flex flex-col min-h-[100svh]">
            <GlobalLoader />
            <Toast />

            <nav class="fixed top-0 left-0 right-0 h-[64px] flex items-center bg-background/80 backdrop-blur-xl border-b border-border/50 dark:border-card-border z-[60]">
                <div class="container mx-auto px-4 flex items-center justify-between gap-4">
                    
                    <button @click="toggleSidebar" class="p-2 hover:bg-muted rounded-lg transition-colors text-foreground">
                        <Menu :size="24" />
                    </button>

                    <div class="flex items-center gap-2 flex-1 justify-center max-w-md">
                        <button @click="router.visit(route('customer.profile.addresses'))" class="flex flex-col items-center px-4 py-1 hover:bg-muted rounded-full transition-all group">
                            <div class="flex items-center gap-1">
                                <MapPin :size="12" class="text-primary" />
                                <span class="text-[9px] font-black text-primary uppercase tracking-tighter">Entrega en</span>
                            </div>
                            <span class="text-[11px] font-bold truncate max-w-[150px] uppercase text-foreground group-hover:text-primary">{{ location.label }}</span>
                        </button>
                        
                        <div class="h-6 w-px bg-border hidden sm:block"></div>
                        
                        <button class="p-2 hover:text-primary transition-colors hidden sm:block">
                            <Search :size="18" />
                        </button>
                    </div>

                    <Link :href="route('customer.shop.index')" class="flex items-center shrink-0">
                        <div class="w-10 h-10 bg-primary text-primary-foreground flex items-center justify-center rounded-lg shadow-lg dark:shadow-f1-glow transition-transform active:scale-90">
                            <Flag :size="20" class="fill-current" />
                        </div>
                    </Link>

                </div>
            </nav>

            <main class="w-full mx-auto pt-[64px] pb-[100px] flex-1 flex flex-col" :class="isIndexPage ? 'max-w-full px-0' : 'max-w-7xl px-4'">
                <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-6 flex-1">
                    <aside class="hidden md:block w-64 shrink-0 bg-card/80 backdrop-blur-md rounded-lg p-2 h-fit shadow-apple-soft dark:shadow-none border border-transparent dark:border-card-border">
                        <nav class="flex flex-col gap-1">
                            <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                                class="flex items-center gap-3 px-4 py-3 text-xs font-bold rounded-lg transition-all"
                                :class="route().current(item.route) ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'">
                                <component :is="item.icon" :size="16" /> {{ item.name }}
                            </Link>
                        </nav>
                    </aside>
                    <div class="flex-1 bg-card/80 backdrop-blur-md rounded-lg p-6 shadow-apple-soft dark:shadow-none border border-transparent dark:border-card-border">
                        <slot />
                    </div>
                </div>
                <div v-else class="w-full flex-1">
                    <slot />
                </div>

                <footer class="mt-auto w-full bg-card border-t border-border/50 dark:border-card-border pt-16 pb-24 md:pb-8 px-6">
                    <div class="container mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                            
                            <div class="flex flex-col gap-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary text-primary-foreground flex items-center justify-center rounded-lg shadow-lg dark:shadow-f1-glow">
                                        <Flag :size="20" class="fill-current" />
                                    </div>
                                    <span class="text-xl font-bold tracking-[-0.02em]">CYBER<span class="text-primary">MARKET</span></span>
                                </div>
                                <p class="text-sm text-muted-foreground leading-relaxed">
                                    La nueva generación de supermercados digitales. Velocidad de entrega técnica y calidad premium garantizada.
                                </p>
                                <div class="flex flex-col gap-3">
                                    <a href="#" class="flex items-center gap-3 text-xs font-semibold hover:text-primary transition-colors">
                                        <Mail :size="16" class="text-primary" /> soporte@cybermarket.io
                                    </a>
                                    <a href="#" class="flex items-center gap-3 text-xs font-semibold hover:text-primary transition-colors">
                                        <Phone :size="16" class="text-primary" /> +591 700 000 00
                                    </a>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Departamentos</h4>
                                <ul class="flex flex-col gap-4">
                                    <li v-for="cat in ['Frutas y Verduras', 'Lácteos y Huevos', 'Carnes y Aves', 'Panadería', 'Bebidas']" :key="cat">
                                        <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">{{ cat }}</a>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Centro de Control</h4>
                                <ul class="flex flex-col gap-4">
                                    <li v-for="link in ['Centro de Ayuda', 'Estado del Envío', 'Términos de Servicio', 'Privacidad', 'Trabaja con nosotros']" :key="link">
                                        <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">{{ link }}</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="flex flex-col gap-6">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-2">Suscripción Técnica</h4>
                                <div class="relative">
                                    <input type="email" placeholder="email@dominio.com" class="w-full bg-muted border-none rounded-lg px-4 py-3 text-xs focus:ring-2 ring-primary/20 transition-all outline-none" />
                                    <button class="absolute right-2 top-2 bottom-2 px-3 bg-primary text-primary-foreground rounded-md text-[10px] font-bold uppercase hover:bg-primary/90 transition-all">Unirse</button>
                                </div>
                                <div class="flex gap-4">
                                    <a v-for="icon in [Facebook, Instagram, Twitter, Youtube]" :key="icon" href="#" class="w-10 h-10 rounded-full bg-muted flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-all">
                                        <component :is="icon" :size="18" />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-border/50 dark:border-card-border flex flex-col md:flex-row justify-between items-center gap-6">
                            <div class="flex flex-col items-center md:items-start">
                                <span class="text-[10px] font-mono text-muted-foreground uppercase tracking-[0.3em]">&copy; 2026 CYBER-SYSTEM CORE v1.0.4</span>
                                <span class="text-[9px] text-muted-foreground/60 mt-1">Diseñado bajo estándares de alto rendimiento.</span>
                            </div>
                            
                            <div class="flex items-center gap-6 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all">
                                <CreditCard :size="24" />
                                <Globe :size="24" />
                                <div class="flex items-center gap-1 font-black text-lg italic">VISA</div>
                                <div class="flex items-center gap-1 font-black text-lg italic tracking-tighter">mastercard</div>
                            </div>
                        </div>
                    </div>
                </footer>
            </main>

            <nav class="fixed bottom-0 left-0 right-0 h-[68px] bg-background border-t border-border z-[70] flex justify-around items-center px-2 md:hidden">
                
                <Link :href="route('customer.orders.history')" class="flex-1 flex justify-center items-center">
                    <Receipt :size="24" 
                    :class="isOrdersPage ? 'text-primary' : 'text-muted-foreground'" 
                        :stroke-width="isOrdersPage ? 2.5 : 2" />
                </Link>

                <Link :href="route('customer.cart.index')" class="flex-1 flex justify-center items-center relative">
                    <ShoppingCart :size="24" 
                        :class="isCartPage ? 'text-[#007AFF] dark:text-[#E10600]' : 'text-gray-400 dark:text-[#525252]'" 
                        :stroke-width="isCartPage ? 2.5 : 2" />
                    
                        <div v-if="cartCount > 0" 
                            class="absolute -top-3 left-1/2 translate-x-1 w-6 h-6 bg-primary text-primary-foreground text-[11px] font-bold rounded-full flex items-center justify-center border-2 border-background shadow-sm dark:shadow-[0_0_15px_rgba(225,6,0,0.4)] transition-all">
                        {{ cartCount }}
                    </div>
                </Link>

                <Link :href="route('customer.shop.index')" class="flex-1 flex justify-center items-center relative -mt-6">
                    <div class="w-14 h-14 rounded-xl bg-card border border-card-border flex items-center justify-center shadow-sm dark:shadow-none">
                        <Home :size="28" 
                            :class="isIndexPage ? 'text-primary' : 'text-muted-foreground'" 
                            :stroke-width="2.5" />
                        <div v-if="activeOrder?.count > 0" 
                             class="absolute top-1 right-1 w-3 h-3 rounded-full border-2 border-[#FFFFFF] dark:border-[#121217]" 
                             :class="telemetryStatusColor"></div>
                    </div>
                </Link>

                <Link href="#" class="flex-1 flex justify-center items-center">
                    <Search :size="24" class="text-gray-400 dark:text-[#525252]" :stroke-width="2" />
                </Link>

                <Link :href="route('customer.profile.index')" class="flex-1 flex justify-center items-center">
                    <User :size="24" 
                        :class="route().current('customer.profile.*') ? 'text-[#007AFF] dark:text-[#E10600]' : 'text-gray-400 dark:text-[#525252]'" 
                        :stroke-width="route().current('customer.profile.*') ? 2.5 : 2" />
                </Link>

            </nav>
        </div>

        <Transition name="slide-left">
            <div v-if="showSidebar" class="fixed inset-0 z-[100] flex">
                <div class="absolute inset-0 bg-background/60 backdrop-blur-sm transition-opacity" @click="showSidebar = false"></div>
                
                <div class="relative w-[66vw] md:w-[350px] h-full bg-card/95 backdrop-blur-2xl border-r border-border shadow-2xl flex flex-col overflow-hidden">
                    
                    <div class="p-6 border-b border-border/50 dark:border-card-border flex justify-between items-center bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                <User :size="20" class="text-primary" />
                            </div>
                            <div v-if="user">
                                <p class="text-[10px] font-bold text-muted-foreground uppercase">Bienvenido</p>
                                <p class="font-bold text-sm truncate max-w-[140px]">{{ user.name }}</p>
                            </div>
                            <div v-else>
                                <p class="text-sm font-bold">Invitado</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="p-2 hover:bg-muted rounded-full transition-colors">
                            <X :size="20" />
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 gap-4 bg-primary/[0.02]">
                        <div v-for="stat in customerStats" :key="stat.label" class="flex justify-between items-center p-3 rounded-xl border border-border/50 dark:border-card-border bg-card shadow-sm">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ stat.label }}</span>
                            <span :class="['text-xs font-black', stat.color]">{{ stat.value }}</span>
                        </div>
                    </div>

                    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                        <template v-if="user">
                            <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)" 
                                @click="showSidebar = false"
                                class="flex items-center gap-4 px-4 py-4 text-xs font-bold uppercase rounded-xl transition-all border border-transparent hover:bg-muted hover:border-border/50">
                                <component :is="item.icon" :size="18" class="text-primary" />
                                {{ item.name }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="flex items-center gap-4 px-4 py-4 text-xs font-bold uppercase rounded-xl bg-primary text-primary-foreground shadow-md">
                                <User :size="18" /> Iniciar Sesión
                            </Link>
                        </template>
                    </nav>

                    <div class="p-4 border-t border-border/50 dark:border-card-border flex flex-col gap-3">
                        <div class="flex gap-2">
                            <ThemeToggler class="flex-1" />
                            <FullScreenToggler class="flex-1" />
                        </div>
                        <button v-if="user" @click="logout" class="w-full flex items-center justify-center gap-2 py-3 text-xs font-bold text-primary hover:bg-primary/10 rounded-xl transition-all">
                            <LogOut :size="16" /> CERRAR SESIÓN
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-left-enter-active, .slide-left-leave-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-left-enter-from, .slide-left-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}
</style>