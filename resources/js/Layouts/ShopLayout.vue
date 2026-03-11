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
const activeOrder = computed(() => page.props.active_order || null); 
const showHamburgerMenu = ref(false);

const toggleHamburger = () => { showHamburgerMenu.value = !showHamburgerMenu.value; };

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

const telemetryStatusColor = computed(() => {
    if (!activeOrder.value) return 'bg-tech';
    const map = {
        'pending_payment': 'bg-amber-500 shadow-[0_0_8px_#f59e0b]',
        'under_review': 'bg-blue-400 shadow-[0_0_8px_#60a5fa]',
        'preparing': 'bg-cyan-400 animate-pulse shadow-[0_0_8px_#22d3ee]',
        'dispatched': 'bg-f1-red animate-bounce shadow-[0_0_8px_#ff0000]',
        'arrived': 'bg-telemetry-green shadow-[0_0_12px_#00ff00]',
        'completed': 'bg-telemetry-green opacity-50'
    };
    return map[activeOrder.value.status] || 'bg-f1-red';
});
</script>

<template>
    <div class="customer-theme text-foreground font-sans min-h-[100svh] relative w-full transition-colors duration-300">
        
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden bg-tech">
            
            <div class="absolute -top-[10%] -right-[10%] w-[70vw] md:w-[45vw] aspect-square rounded-full blur-[100px] md:blur-[140px] 
                        bg-fuchsia-200/30 dark:bg-[#FF007F]/10 transition-colors duration-1000">
            </div>

            <div class="absolute -bottom-[10%] -left-[10%] w-[70vw] md:w-[45vw] aspect-square rounded-full blur-[100px] md:blur-[140px] 
                        bg-cyan-200/30 dark:bg-[#00FFFF]/10 transition-colors duration-1000">
            </div>

            <div v-if="page.props.active_color" 
                 class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60vw] aspect-square rounded-full blur-[180px] opacity-10 mix-blend-screen"
                 :style="{ backgroundColor: page.props.active_color }">
            </div>

            <div class="absolute inset-0 tech-dot-pattern opacity-60 dark:opacity-40"></div>
        </div>

        <div class="relative z-10 flex flex-col min-h-[100svh]">
            
            <GlobalLoader />
            <Toast />

            <nav class="fixed top-0 left-0 right-0 h-[64px] flex items-center bg-background/40 backdrop-blur-xl border-b border-tech z-[60] transition-all duration-300">
                <div class="container mx-auto px-4 h-full flex items-center justify-between w-full">
                    <Link :href="route('customer.shop.index')" class="flex items-center group">
                        <div class="w-9 h-9 bg-f1-red text-white flex items-center justify-center rounded-lg shadow-neon-red">
                            <Flag :size="18" class="fill-current" />
                        </div>
                    </Link>

                    <div class="flex flex-col items-center justify-center cursor-pointer group" @click="router.visit(route('customer.profile.addresses'))">
                        <span class="text-[7px] font-mono font-black uppercase tracking-[0.2em] text-f1-red">[SECTOR]</span>
                        <div class="flex items-center gap-1 max-w-[150px]">
                            <MapPin :size="10" class="text-muted shrink-0" />
                            <span class="text-[10px] font-bold uppercase truncate text-primary">{{ location.label }}</span>
                        </div>
                    </div>

                    <button @click="toggleHamburger" class="w-10 h-10 rounded-full border border-tech flex items-center justify-center bg-surface/50 backdrop-blur-md overflow-hidden transition-all hover:border-f1-red">
                        <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover">
                        <User v-else :size="20" class="text-primary" />
                    </button>
                </div>
            </nav>

            <main class="w-full mx-auto transition-all pt-[64px] pb-[80px] flex flex-col flex-1" 
                :class="isIndexPage ? 'max-w-full px-0' : 'max-w-7xl px-4 py-6'">
                
                <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-8 flex-1">
                    <aside class="hidden md:block w-64 shrink-0 bg-surface/30 backdrop-blur-xl border border-tech rounded-[24px] p-2 h-fit shadow-sm">
                        <div class="p-4 border-b border-tech">
                            <h3 class="text-[9px] font-mono font-black uppercase text-f1-red mb-1">DATA PILOTO</h3>
                            <p class="font-sans font-bold uppercase truncate text-xs text-primary">{{ user.name }}</p>
                        </div>
                        <nav class="flex flex-col p-2 gap-1">
                            <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                                class="flex items-center gap-3 px-4 py-3 text-xs font-bold uppercase rounded-xl transition-all border border-transparent"
                                :class="route().current(item.route) ? 'bg-f1-red text-white shadow-neon-red' : 'text-primary hover:bg-white/5'">
                                <component :is="item.icon" :size="16" /> {{ item.name }}
                            </Link>
                        </nav>
                    </aside>
                    <div class="flex-1 bg-surface/30 backdrop-blur-xl border border-tech rounded-[24px] p-6 shadow-sm">
                        <slot />
                    </div>
                </div>
                
                <div v-else class="flex-1 w-full relative">
                    <slot />
                </div>

                <footer class="mt-12 w-full bg-surface/20 backdrop-blur-md border-t border-tech py-10 px-6">
                    <div class="container mx-auto text-center">
                        <span class="text-[9px] font-mono text-muted uppercase tracking-[0.3em]">&copy; 2026 CYBER-SYSTEM</span>
                    </div>
                </footer>
            </main>

            <nav class="fixed bottom-0 left-0 right-0 h-[80px] bg-background/40 backdrop-blur-xl border-t border-tech z-[70] flex justify-around items-center px-6 md:hidden">
                <Link :href="route('customer.shop.index')" class="flex flex-col items-center">
                    <Home :size="24" :class="isIndexPage ? 'text-f1-red' : 'text-muted'" />
                </Link>
                <Link :href="route('customer.cart.index')" class="relative">
                    <ShoppingBag :size="24" :class="isCartPage ? 'text-f1-red' : 'text-muted'" />
                    <span v-if="cartCount > 0" class="absolute -top-2 -right-3 min-w-[16px] h-[16px] bg-f1-red text-white text-[9px] font-mono rounded-full flex items-center justify-center">{{ cartCount }}</span>
                </Link>
                <Link :href="route('customer.orders.history')">
                    <ClipboardList :size="24" :class="isOrdersPage ? 'text-f1-red' : 'text-muted'" />
                </Link>
            </nav>

            <Transition name="fast-slide-right">
                <div v-if="showHamburgerMenu" class="fixed inset-0 z-[100] flex justify-end">
                    <div class="absolute inset-0 bg-background/20 backdrop-blur-sm" @click="showHamburgerMenu = false"></div>
                    <div class="relative w-80 h-full bg-background/80 backdrop-blur-2xl border-l border-tech p-6 flex flex-col shadow-2xl">
                        <button @click="showHamburgerMenu = false" class="self-end mb-8 p-2 text-muted hover:text-f1-red"><X :size="24" /></button>
                        <nav class="flex flex-col gap-2">
                            <template v-if="user">
                                <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)" @click="showHamburgerMenu = false"
                                    class="flex items-center gap-4 p-4 bg-surface/30 border border-tech rounded-2xl text-[10px] font-bold uppercase text-primary transition-all">
                                    <component :is="item.icon" :size="18" /> {{ item.name }}
                                </Link>
                                <button @click="logout" class="mt-4 p-4 bg-f1-red/10 border border-f1-red text-f1-red rounded-2xl font-bold uppercase">Cerrar Sesión</button>
                            </template>
                            <Link v-else :href="route('login')" class="mt-4 p-4 bg-telemetry-green/10 border border-telemetry-green text-telemetry-green rounded-2xl font-bold uppercase text-center">Acceso Piloto</Link>
                            <div class="mt-6 flex gap-4">
                                <ThemeToggler class="flex-1" />
                                <FullScreenToggler class="flex-1" />
                            </div>
                        </nav>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.fast-slide-right-enter-active, .fast-slide-right-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.fast-slide-right-enter-from, .fast-slide-right-leave-to { transform: translateX(100%); }

/* GRILLA MATEMÁTICA ESTÁTICA */
:global(.dark) .tech-dot-pattern {
    background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1.5px);
    background-size: 32px 32px;
}

.tech-dot-pattern {
    background-image: radial-gradient(rgba(0, 0, 0, 0.06) 1.2px, transparent 1.5px);
    background-size: 24px 24px;
}
</style>