<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    MapPin, ShoppingCart, User, Receipt, LogOut, Search,
    Home, ShieldCheck, X, Menu, Flag, Tag, Mail, Phone, Globe,
    Facebook, Instagram, Twitter, Youtube, CreditCard, Loader2
} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue'; 
import GlobalLoader from '@/Components/Base/GlobalLoader.vue';
import Toast from '@/Components/Base/Toast.vue';

const props = defineProps({ isProfileSection: { type: Boolean, default: false } });
const page = usePage();

// --- SISTEMA DE DATOS COMPARTIDOS ---
const user = computed(() => page.props.auth?.user);
const location = computed(() => page.props.location_context || { label: 'LOCALIZANDO...', type: 'branch' });
const cartCount = computed(() => page.props.cart?.total_items || 0);
const activeOrder = computed(() => page.props.active_order || null); 

// --- TELEMETRÍA DE ÓRDENES ---
const telemetryStatusColor = computed(() => {
    if (!activeOrder.value?.latest) return 'bg-muted';
    const statusMap = {
        'pending_payment': 'bg-amber-500 shadow-[0_0_8px_#f59e0b]',
        'preparing': 'bg-cyan-400 animate-pulse',
        'dispatched': 'bg-primary animate-bounce',
        'arrived': 'bg-accent shadow-[0_0_12px_#00ff00]',
    };
    return statusMap[activeOrder.value.latest.status] || 'bg-primary';
});

// --- ESTADO DE NAVEGACIÓN ---
const showSidebar = ref(false);
const toggleSidebar = () => { showSidebar.value = !showSidebar.value; };

const menuItems = [
    { name: 'Mi Perfil', route: 'customer.profile.index', icon: User },
    { name: 'Direcciones', route: 'customer.profile.addresses', icon: MapPin },
    { name: 'Mis Pedidos', route: 'customer.orders.history', icon: Receipt },
    { name: 'Seguridad', route: 'customer.profile.security', icon: ShieldCheck },
];

const logout = () => { router.post(route('logout')); };
</script>

<template>
    <div class="text-foreground font-sans min-h-[100svh] relative w-full transition-colors duration-500 bg-background flex flex-col">
        
        <GlobalLoader />
        <Toast />

        <nav class="fixed top-0 left-0 right-0 h-[64px] flex items-center bg-background/80 backdrop-blur-xl border-b border-border/50 z-[60]">
            <div class="container mx-auto px-4 flex items-center justify-between gap-4">
                
                <button @click="toggleSidebar" class="p-2 hover:bg-muted rounded-lg transition-colors">
                    <Menu :size="24" />
                </button>

                <div class="flex items-center gap-2 flex-1 justify-center max-w-md">
                    <button @click="router.visit(route('customer.profile.addresses'))" class="flex flex-col items-center px-4 py-1 hover:bg-muted rounded-full transition-all group">
                        <div class="flex items-center gap-1">
                            <MapPin :size="12" class="text-primary" />
                            <span class="text-[9px] font-black text-primary uppercase tracking-tighter">Entrega en</span>
                        </div>
                        <span class="text-[11px] font-bold truncate max-w-[150px] uppercase text-foreground group-hover:text-primary transition-colors">
                            {{ location.label }}
                        </span>
                    </button>
                </div>

                <div class="flex items-center gap-1 md:gap-3">
                    <Link :href="route('customer.cart.index')" class="relative p-2 hover:bg-muted rounded-xl transition-all active:scale-90 group">
                        <ShoppingCart :size="24" :class="route().current('customer.cart.*') ? 'text-primary' : 'text-foreground/70'" />
                        <div v-if="cartCount > 0" class="absolute top-1 right-1 w-5 h-5 bg-primary text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-background shadow-lg">
                            {{ cartCount }}
                        </div>
                    </Link>

                    <Link :href="route('customer.shop.index')" class="hidden md:flex items-center shrink-0">
                        <div class="w-10 h-10 bg-primary text-primary-foreground flex items-center justify-center rounded-lg shadow-lg">
                            <Flag :size="20" class="fill-current" />
                        </div>
                    </Link>
                </div>
            </div>
        </nav>

        <main class="w-full mx-auto pt-[64px] pb-[100px] flex-1 flex flex-col" :class="route().current('customer.shop.index') ? 'max-w-full px-0' : 'max-w-7xl px-4'">
            
            <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-6 flex-1 py-8">
                <aside class="hidden md:block w-64 shrink-0 bg-card/80 backdrop-blur-md rounded-2xl p-2 h-fit border border-border/50">
                    <nav class="flex flex-col gap-1">
                        <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                            class="flex items-center gap-3 px-4 py-3 text-xs font-bold rounded-xl transition-all"
                            :class="route().current(item.route) ? 'bg-primary text-primary-foreground shadow-md' : 'hover:bg-muted'">
                            <component :is="item.icon" :size="16" /> {{ item.name }}
                        </Link>
                    </nav>
                </aside>

                <div class="flex-1 bg-card/80 backdrop-blur-md rounded-2xl p-6 border border-border/50 shadow-sm">
                    <slot />
                </div>
            </div>

            <div v-else class="w-full flex-1">
                <slot />
            </div>

            <footer class="mt-auto w-full bg-card border-t border-border/50 pt-16 pb-24 md:pb-8 px-6">
                <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                    <div class="flex flex-col gap-6">
                        <span class="text-xl font-bold tracking-tighter">CYBER<span class="text-primary">MARKET</span></span>
                        <p class="text-sm text-muted-foreground">Retail de alta densidad para usuarios técnicos.</p>
                    </div>
                </div>
            </footer>
        </main>

        <nav class="fixed bottom-0 left-0 right-0 h-[68px] bg-background/95 backdrop-blur-xl border-t border-border/50 z-[70] flex justify-around items-center md:hidden">
            
            <Link :href="route('customer.orders.history')" class="flex-1 flex flex-col items-center gap-1">
                <Receipt :size="22" :class="route().current('customer.orders.*') ? 'text-primary' : 'text-muted-foreground'" />
                <span class="text-[8px] font-black uppercase tracking-tighter">Pedidos</span>
            </Link>

            <Link :href="route('customer.shop.index')" class="flex-1 flex flex-col items-center gap-1">
                <Tag :size="22" :class="route().current('customer.shop.promotions') ? 'text-primary' : 'text-muted-foreground'" />
                <span class="text-[8px] font-black uppercase tracking-tighter">Promos</span>
            </Link>

            <Link :href="route('customer.shop.index')" class="flex-1 flex justify-center items-center relative -mt-8">
                <div class="w-16 h-16 rounded-2xl bg-card border-4 border-background flex items-center justify-center shadow-2xl transition-transform active:scale-90">
                    <Home :size="28" :class="route().current('customer.shop.index') ? 'text-primary' : 'text-muted-foreground'" />
                    <div v-if="activeOrder?.count > 0" class="absolute top-3 right-3 w-3 h-3 rounded-full border-2 border-card" :class="telemetryStatusColor"></div>
                </div>
            </Link>

            <button class="flex-1 flex flex-col items-center gap-1">
                <Search :size="22" class="text-muted-foreground" />
                <span class="text-[8px] font-black uppercase tracking-tighter">Buscar</span>
            </button>

            <Link :href="route('customer.profile.index')" class="flex-1 flex flex-col items-center gap-1">
                <User :size="22" :class="route().current('customer.profile.*') ? 'text-primary' : 'text-muted-foreground'" />
                <span class="text-[8px] font-black uppercase tracking-tighter">Perfil</span>
            </Link>
        </nav>

        <Transition name="slide-left">
            <div v-if="showSidebar" class="fixed inset-0 z-[100] flex">
                <div class="absolute inset-0 bg-background/60 backdrop-blur-sm" @click="showSidebar = false"></div>
                <div class="relative w-[300px] h-full bg-card border-r border-border flex flex-col shadow-2xl">
                    <div class="p-6 border-b border-border flex justify-between items-center bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                <User :size="20" class="text-primary" />
                            </div>
                            <div v-if="user">
                                <p class="text-[10px] font-bold text-muted-foreground uppercase">Cliente</p>
                                <p class="font-bold text-sm truncate max-w-[140px]">{{ user.name }}</p>
                            </div>
                            <div v-else>
                                <p class="text-sm font-bold">Invitado</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="p-2 hover:bg-muted rounded-full transition-colors"><X :size="20" /></button>
                    </div>

                    <nav class="flex-1 px-4 py-6 space-y-2">
                        <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)" @click="showSidebar = false"
                            class="flex items-center gap-4 px-4 py-4 text-xs font-black uppercase rounded-xl hover:bg-primary/5 transition-colors group">
                            <component :is="item.icon" :size="20" class="text-muted-foreground group-hover:text-primary transition-colors" /> 
                            {{ item.name }}
                        </Link>
                    </nav>

                    <div class="p-6 border-t border-border flex flex-col gap-4">
                        <div class="flex gap-2">
                            <ThemeToggler class="flex-1" />
                            <FullScreenToggler class="flex-1" />
                        </div>
                        <button v-if="user" @click="logout" class="w-full flex items-center justify-center gap-2 py-4 text-xs font-black text-primary border border-primary/20 rounded-xl hover:bg-primary/10 transition-all">
                            <LogOut :size="16" /> CERRAR SESIÓN
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-left-enter-active, .slide-left-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>