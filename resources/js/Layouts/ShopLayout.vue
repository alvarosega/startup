<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    MapPin, ShoppingBag, User, FileText, LogOut, Search,
    Zap, Home, ShieldCheck, X, Menu, Settings
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
const location = computed(() => page.props.location_context || { label: 'SIN SEÑAL DE TELEMETRÍA', type: 'branch' });
const cartCount = computed(() => page.props.cart_summary?.count || 0);

// Estados UI (Mecánicos)
const isSearchOpen = ref(false);
const showHamburgerMenu = ref(false);

const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) showHamburgerMenu.value = false;
};

const toggleHamburger = () => {
    showHamburgerMenu.value = !showHamburgerMenu.value;
    if (showHamburgerMenu.value) isSearchOpen.value = false;
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
</script>

<template>
    <GlobalLoader />
    <Toast />


        
        <nav class="absolute top-0 left-0 right-0 h-nav flex items-center bg-surface/95 backdrop-blur-md border-b border-tech z-[60]">
            <div class="container mx-auto px-4 h-full grid grid-cols-[auto_1fr_auto] gap-4 items-center w-full">
                
                <Link :href="route('customer.shop.index')" class="flex items-center group">
                    <div class="w-10 h-10 bg-primary text-background flex items-center justify-center clip-f1-br transition-transform duration-150 group-hover:scale-105">
                        <Zap :size="20" class="fill-current" />
                    </div>
                </Link>

                <div class="flex flex-col items-center justify-center min-w-0 px-2 cursor-pointer" @click="router.visit(route('customer.profile.addresses'))">
                    <span class="text-[8px] font-mono font-black uppercase tracking-[0.2em] text-f1-red mb-0.5">[ZONA ACTUAL]</span>
                    <div class="flex items-center gap-1.5 max-w-full">
                        <MapPin :size="12" class="text-muted shrink-0" />
                        <span class="text-xs font-bold uppercase truncate text-primary">{{ location.label }}</span>
                    </div>
                </div>

                <button @click="toggleHamburger" class="w-10 h-10 border border-tech flex items-center justify-center bg-background clip-f1-tl hover:border-f1-red transition-colors duration-150">
                    <Menu :size="20" class="text-primary" />
                </button>

            </div>
        </nav>

        <main class="w-full mx-auto transition-all pt-nav pb-nav md:pb-0 flex flex-col flex-1" 
            :class="isIndexPage ? 'max-w-full p-0 pb-nav' : 'max-w-7xl px-4 md:py-8'">
            
            <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-8 flex-1">
                <aside class="hidden md:block w-64 shrink-0 bg-surface border border-tech clip-f1-br p-0 h-fit shadow-tech">
                    <div class="p-4 border-b border-tech bg-background/50">
                        <h3 class="text-[10px] font-mono font-black uppercase tracking-widest text-f1-red mb-1">PILOTO ACTIVO</h3>
                        <p class="font-sans font-bold uppercase truncate text-sm text-primary">{{ user.name || user.email }}</p>
                    </div>
                    <nav class="flex flex-col p-2 gap-1">
                        <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold uppercase clip-f1-br transition-all duration-150 border border-transparent"
                            :class="route().current(item.route) ? 'bg-f1-red text-white border-f1-red' : 'text-primary hover:bg-background hover:border-tech'">
                            <component :is="item.icon" :size="18" /> {{ item.name }}
                        </Link>
                    </nav>
                </aside>
                <div class="flex-1 bg-surface/80 backdrop-blur-sm border border-tech clip-f1-br p-4">
                    <slot />
                </div>
            </div>
            
            <div v-else class="flex-1 w-full relative">
                <slot />
            </div>

        </main>

        <Transition name="fast-slide-right">
            <div v-if="showHamburgerMenu" class="fixed inset-0 top-nav bottom-nav md:bottom-0 z-[50] bg-surface border-l border-tech w-full md:w-80 ml-auto flex flex-col shadow-[-20px_0_50px_rgba(0,0,0,0.5)]">
                <div class="flex-1 overflow-y-auto p-4">
                    <nav v-if="user" class="flex flex-col gap-2">
                        <div class="p-4 mb-2 bg-background border border-tech clip-f1-br">
                            <p class="text-[10px] font-mono uppercase text-telemetry-green tracking-widest mb-1">TELEMETRÍA OK</p>
                            <p class="font-sans font-black text-lg uppercase truncate text-primary">{{ user.name || user.email }}</p>
                        </div>

                        <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                            @click="showHamburgerMenu = false"
                            class="flex items-center gap-4 p-4 bg-background border border-tech clip-f1-br text-sm font-bold uppercase text-primary hover:border-f1-red transition-colors duration-150">
                            <component :is="item.icon" :size="18" class="text-muted" /> {{ item.name }}
                        </Link>
                        
                        <div class="my-4 border-t border-tech"></div>
                        
                        <div class="mb-4">
                            <span class="text-[10px] font-mono text-muted uppercase block mb-3">Sistemas / HUD</span>
                            <div class="flex gap-4 p-4 bg-background border border-tech clip-f1-br">
                                <ThemeToggler />
                                <FullScreenToggler />
                            </div>
                        </div>

                        <button @click="logout" class="w-full flex items-center justify-center gap-3 p-4 bg-f1-red/10 border border-f1-red text-f1-red clip-f1-br font-bold uppercase hover:bg-f1-red hover:text-white transition-colors duration-150">
                            <LogOut :size="16" /> Desconectar Sistema
                        </button>
                    </nav>
                    <div v-else class="flex flex-col gap-4 mt-4">
                        <div class="flex gap-4 p-4 mb-4 bg-background border border-tech clip-f1-br">
                            <ThemeToggler />
                            <FullScreenToggler />
                        </div>
                        <Link :href="route('login')" class="w-full p-4 bg-f1-red text-white font-bold uppercase text-center clip-f1-br transition-colors hover:bg-f1-red-hover">Iniciar Stint (Login)</Link>
                        <Link :href="route('register')" class="w-full p-4 bg-background border border-tech text-primary font-bold uppercase text-center clip-f1-br transition-colors hover:border-primary">Nuevo Piloto (Unirse)</Link>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fast-fade">
            <div v-if="isSearchOpen" class="fixed inset-0 top-nav bottom-nav md:bottom-0 z-[50] bg-surface/95 backdrop-blur-md p-4">
                <div class="flex items-center w-full max-w-2xl mx-auto border-b-2 border-f1-red pb-2">
                    <Search :size="24" class="text-f1-red mr-3" />
                    <input autoFocus placeholder="INGRESE PARÁMETRO DE BÚSQUEDA..." class="w-full bg-transparent border-none outline-none text-lg font-mono font-bold text-primary placeholder-muted" />
                    <button @click="toggleSearch" class="text-muted hover:text-f1-red transition-colors duration-150"><X :size="24" /></button>
                </div>
            </div>
        </Transition>

        <nav class="fixed bottom-0 left-0 right-0 h-nav bg-surface border-t border-tech z-[70] flex justify-around items-center px-2 md:hidden">
            <Link :href="route('customer.shop.index')" class="flex flex-col items-center justify-center w-full h-full gap-1 group relative">
                <div class="absolute top-0 w-full h-[2px] bg-f1-red transition-transform duration-150 origin-center" :class="isIndexPage ? 'scale-x-100' : 'scale-x-0'"></div>
                <Home :size="20" :class="isIndexPage ? 'text-primary' : 'text-muted'" />
                <span class="text-[9px] font-mono uppercase" :class="isIndexPage ? 'text-primary font-bold' : 'text-muted'">Grid</span>
            </Link>

            <button @click="toggleSearch" class="flex flex-col items-center justify-center w-full h-full gap-1 group relative">
                <div class="absolute top-0 w-full h-[2px] bg-f1-red transition-transform duration-150 origin-center" :class="isSearchOpen ? 'scale-x-100' : 'scale-x-0'"></div>
                <Search :size="20" :class="isSearchOpen ? 'text-primary' : 'text-muted'" />
                <span class="text-[9px] font-mono uppercase" :class="isSearchOpen ? 'text-primary font-bold' : 'text-muted'">Escanear</span>
            </button>

            <Link :href="route('customer.cart.index')" class="flex flex-col items-center justify-center w-full h-full gap-1 group relative bg-background border-x border-tech">
                <div class="absolute top-0 w-full h-[2px] bg-f1-red transition-transform duration-150 origin-center" :class="route().current('customer.cart.index') ? 'scale-x-100' : 'scale-x-0'"></div>
                <div class="relative">
                    <ShoppingBag :size="20" :class="route().current('customer.cart.index') ? 'text-primary' : 'text-muted'" />
                    <span v-if="cartCount > 0" class="absolute -top-2 -right-3 min-w-[16px] h-[16px] bg-f1-red text-white text-[9px] font-mono font-black clip-f1-tl flex items-center justify-center shadow-lg px-1">
                        {{ cartCount }}
                    </span>
                </div>
                <span class="text-[9px] font-mono uppercase" :class="route().current('customer.cart.index') ? 'text-primary font-bold' : 'text-muted'">Box</span>
            </Link>

            <Link :href="route('customer.profile.index')" class="flex flex-col items-center justify-center w-full h-full gap-1 group relative">
                <div class="absolute top-0 w-full h-[2px] bg-f1-red transition-transform duration-150 origin-center" :class="route().current('customer.profile.index') ? 'scale-x-100' : 'scale-x-0'"></div>
                <User :size="20" :class="route().current('customer.profile.index') ? 'text-primary' : 'text-muted'" />
                <span class="text-[9px] font-mono uppercase" :class="route().current('customer.profile.index') ? 'text-primary font-bold' : 'text-muted'">Piloto</span>
            </Link>
        </nav>

        <Transition name="fast-fade">
            <div v-if="showHamburgerMenu || isSearchOpen" class="fixed inset-0 z-[40] bg-background/80 backdrop-blur-sm md:hidden" @click="showHamburgerMenu = false; isSearchOpen = false"></div>
        </Transition>
</template>

<style scoped>
/* Mecánica lineal. Sin rebotes. */
.fast-slide-right-enter-active,
.fast-slide-right-leave-active {
    transition: transform 0.15s linear, opacity 0.15s linear;
}
.fast-slide-right-enter-from,
.fast-slide-right-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.fast-fade-enter-active,
.fast-fade-leave-active {
    transition: opacity 0.1s linear;
}
.fast-fade-enter-from,
.fast-fade-leave-to {
    opacity: 0;
}
</style>