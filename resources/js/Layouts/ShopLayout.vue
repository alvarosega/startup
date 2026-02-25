<script setup>

import { computed, ref, watch } from 'vue';

import { Link, usePage, router } from '@inertiajs/vue3';

import {

    MapPin, ShoppingBag, User, FileText, LogOut, Search,

    ChevronRight, Zap, Facebook, Instagram, Phone,

    ShieldCheck, X, Settings

} from 'lucide-vue-next';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import FullScreenToggler from '@/Components/Base/FullScreenToggler.vue'; 
import GlobalLoader from '@/Components/Base/GlobalLoader.vue';
import Toast from '@/Components/Base/Toast.vue';

const showAccessibilityMenu = ref(false)

const props = defineProps({

    isProfileSection: { type: Boolean, default: false }

});



const page = usePage();

const user = computed(() => page.props.auth?.user);

const location = computed(() => page.props.location_context || { label: 'Cargando...', type: 'branch' });

const shop = computed(() => page.props.shop_context || {});

const cartCount = computed(() => page.props.cart_summary?.count || 0);



// UI States

const isSearchOpen = ref(false);

const showAccountMenu = ref(false);



const toggleSearch = () => {

    isSearchOpen.value = !isSearchOpen.value;

    if (isSearchOpen.value) showAccountMenu.value = false;

};



const menuItems = [

    { name: 'Mi Perfil', route: 'customer.profile.index', icon: User },

    { name: 'Mis Direcciones', route: 'customer.profile.addresses', icon: MapPin },

    { name: 'Pedidos', route: 'customer.orders.history', icon: FileText },

    { name: 'Seguridad', route: 'customer.profile.security', icon: ShieldCheck },

];



const logout = () => {

    showAccountMenu.value = false;

    router.post(route('logout'));

};



const isIndexPage = computed(() => route().current('customer.shop.index'));

</script>



<template>

    <GlobalLoader />

    <Toast />



    <div class="bg-background text-foreground font-sans flex flex-col selection:bg-primary selection:text-primary-foreground transition-colors duration-300 relative"
        :class="isIndexPage ? 'h-[100svh] overflow-hidden' : 'min-h-[100svh]'">

        <nav class="absolute top-0 left-0 right-0 h-[10svh] flex items-center bg-background/40 backdrop-blur-xl border-b border-white/5"
            style="z-index: 9999; transform: translateZ(9999px); transform-style: preserve-3d;">
            <div class="container mx-auto px-4 h-full relative flex items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-2 shrink-0">
                    <Link :href="route('customer.shop.index')" class="group">
                        <div class="w-9 h-9 bg-foreground text-background rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                            <Zap :size="20" class="fill-current" />
                        </div>
                    </Link>
                </div>

                <div v-if="!isSearchOpen" class="flex-1 max-w-md">
                    <Link :href="route('customer.profile.addresses')" class="w-full h-10 bg-muted/40 border border-white/5 rounded-full px-4 flex items-center justify-center gap-2 hover:bg-muted/60 transition-colors">
                        <MapPin :size="14" class="text-primary" />
                        <span class="text-[11px] font-black uppercase truncate">{{ location.label }}</span>
                    </Link>
                </div>
                <div v-else class="flex-1 max-w-2xl animate-in slide-in-from-right-4">
                    <div class="relative flex items-center">
                        <input autoFocus placeholder="Buscar..." class="w-full bg-muted border-primary/30 rounded-xl py-2 pl-4 pr-10 text-sm focus:ring-4 focus:ring-primary/10">
                        <button @click="toggleSearch" class="absolute right-3 text-error"><X :size="18" /></button>
                    </div>
                </div>

                <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                    <button v-if="!isSearchOpen" @click="toggleSearch" class="p-2 hover:bg-muted rounded-full"><Search :size="20" /></button>

                    <Link :href="route('customer.cart.index')" class="relative p-2 hover:bg-muted rounded-full">
                        <ShoppingBag :size="20" />
                        <span v-if="$page.props.cart_summary?.count > 0" 
                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-background shadow-lg z-[110]">
                            {{ $page.props.cart_summary.count }}
                        </span>
                    </Link>

                    <button @click="showAccessibilityMenu = !showAccessibilityMenu" class="p-2 hover:bg-muted rounded-full transition-colors" :class="{'text-primary': showAccessibilityMenu}">
                        <Settings :size="20" />
                    </button>

                    <button @click="showAccountMenu = !showAccountMenu" class="w-9 h-9 rounded-xl overflow-hidden border-2" :class="showAccountMenu ? 'border-primary' : 'border-white/10'">
                        <img v-if="user" :src="`/assets/avatars/${user.avatar}`" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full bg-muted flex items-center justify-center"><User :size="18" /></div>
                    </button>

                    <Transition name="menu-pop">
                        <div v-if="showAccountMenu" class="absolute right-4 top-[11svh] w-64 bg-card/95 backdrop-blur-2xl border border-white/10 rounded-[24px] shadow-2xl overflow-hidden z-[70]">
                            <div v-if="user" class="p-5 bg-primary/5 border-b border-white/5">
                                <p class="text-[10px] font-black uppercase text-primary tracking-widest">Socio Activo</p>
                                <p class="font-bold truncate text-sm">{{ user.name }}</p>
                            </div>
                            <nav class="p-2">
                                <Link v-for="item in (user ? menuItems : [])" :key="item.route" :href="route(item.route)"
                                    @click="showAccountMenu = false"
                                    class="flex items-center gap-3 px-4 py-3 text-xs font-bold hover:bg-primary/10 rounded-xl transition-all">
                                    <component :is="item.icon" :size="16" /> {{ item.name }}
                                </Link>
                                <div v-if="user" class="h-px bg-white/5 my-2"></div>
                                <button v-if="user" @click="logout" class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-error hover:bg-error/10 rounded-xl transition-all">
                                    <LogOut :size="16" /> Cerrar Sesión
                                </button>
                                <div v-else class="p-2 grid grid-cols-2 gap-2">
                                    <Link :href="route('login')" class="btn btn-primary btn-xs">Login</Link>
                                    <Link :href="route('register')" class="btn btn-outline btn-xs">Unirse</Link>
                                </div>
                            </nav>
                        </div>
                    </Transition>
                </div>
            </div>


        </nav>
        <main class="w-full mx-auto transition-all pt-[10svh] flex flex-col" 
            :style="{ minHeight: '95svh' }"
            :class="isIndexPage ? 'max-w-full p-0 h-[95svh] overflow-hidden' : 'max-w-7xl px-4 md:py-8'">
            
            <div class="flex-1"> <div v-if="isProfileSection && user" class="flex flex-col md:flex-row gap-8">
                    <aside class="hidden md:block w-64 shrink-0">...</aside>
                    <div class="flex-1"><slot /></div>
                </div>
                <slot v-else />
            </div>
        </main>
        <footer class="h-[5svh] border-t border-white/5 bg-muted/20 flex items-center shrink-0">
            <div class="container mx-auto px-4 flex flex-row justify-between items-center gap-2">
                <div class="font-display font-black italic text-xs">BOLIVIA<span class="text-primary">L.</span></div>
                <div class="flex gap-4 text-muted-foreground scale-75">
                    <Facebook :size="16" class="hover:text-primary cursor-pointer transition-colors" />
                    <Instagram :size="16" class="hover:text-primary cursor-pointer transition-colors" />
                    <Phone :size="16" class="hover:text-primary cursor-pointer transition-colors" />
                </div>
                <div class="text-[8px] font-black uppercase tracking-widest opacity-40">©2026</div>
            </div>
        </footer>

        <div v-if="showAccountMenu || showAccessibilityMenu" class="fixed inset-0 z-[55]" @click="showAccountMenu = false; showAccessibilityMenu = false"></div>
    </div>
    <Transition name="slide-up">
        <div v-if="showAccessibilityMenu" class="fixed bottom-12 left-1/2 -translate-x-1/2 z-[100] accessibility-card">
            <ul class="flex items-center gap-8 px-8 py-4">
                <li class="flex flex-col items-center gap-2">
                    <span class="text-[8px] font-black uppercase opacity-40">Pantalla</span>
                    <FullScreenToggler />
                </li>
                <li class="flex flex-col items-center gap-2">
                    <span class="text-[8px] font-black uppercase opacity-40">Ambiente</span>
                    <ThemeToggler />
                </li>
                <li class="ml-2">
                    <button @click="showAccessibilityMenu = false" class="svg-close-btn">
                        <X :size="18" />
                    </button>
                </li>
            </ul>
        </div>
    </Transition>
</template>



<style scoped>

.menu-pop-enter-active { animation: pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }

.menu-pop-leave-active { animation: pop 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) reverse; }

@keyframes pop { from { opacity: 0; transform: scale(0.9) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
/* Animación de entrada inferior */
.slide-up-enter-active, .slide-up-leave-active { 
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
}
.slide-up-enter-from, .slide-up-leave-to { 
    opacity: 0; 
    transform: translate(-50%, 100px); 
}

.accessibility-card {
    background: rgba(var(--background-rgb), 0.8);
    backdrop-filter: blur(25px);
    border-radius: 30px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
}
.accessibility-card ul {
    list-style: none;
    display: flex;
    align-items: center;
}

.svg-close-btn {
    width: 35px; height: 35px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255, 0, 0, 0.1); color: #ff4444; transition: 0.3s;
}
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translate(-50%, 100px); }
/* Ajuste para que el nav no corte los iconos en móviles */
nav .container {
    display: flex;
    align-items: center;
}

/* El carrito debe tener un tamaño mínimo de impacto para clics */
.relative.p-2.hover\:bg-muted {
    min-width: 40px;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.matrix-col::first-letter {
    color: #fff;
    text-shadow: 0 0 10px #fff, 0 0 20px var(--matrix-color), 0 0 40px var(--matrix-color);
    filter: brightness(2); /* Brillo extremo en la punta */
}
</style>