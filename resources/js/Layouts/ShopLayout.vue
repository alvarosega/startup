<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import Toast from '@/Components/Base/Toast.vue';
import { 
    MapPin, ShoppingCart, Menu, X, LogIn, User, 
    FileText, LogOut, Store, Home, Search, ChevronRight,
    ChevronDown, Zap
} from 'lucide-vue-next';

// Componentes Auth
import LoginForm from '@/Components/Auth/LoginForm.vue';
import RegisterForm from '@/Components/Auth/RegisterForm.vue';
import RegisterDriverForm from '@/Components/Auth/RegisterDriverForm.vue';
import ForgotPasswordForm from '@/Components/Auth/ForgotPasswordForm.vue';
import ResetPasswordForm from '@/Components/Auth/ResetPasswordForm.vue';
import ProfileWizardForm from '@/Components/Auth/ProfileWizardForm.vue';
import { useAuthModal } from '@/Composables/useAuthModal'; 

const props = defineProps({
    isProfileSection: { type: Boolean, default: false }
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const cartCount = computed(() => page.props.cart_count || 0);
const shopContext = computed(() => page.props.shop_context || {}); 

// UI States
const isScrolled = ref(false);
const showUserDropdown = ref(false); 
const showMobileDrawer = ref(false);
const showMobileSearch = ref(false); // NUEVO: Estado para búsqueda móvil

const handleScroll = () => { isScrolled.value = window.scrollY > 10; };
onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

// Cerrar búsqueda móvil al navegar
watch(() => page.url, () => {
    showMobileSearch.value = false;
    showMobileDrawer.value = false;
});

const { 
    showLogin, showRegister, showRegisterDriver, 
    showForgotPassword, showResetPassword, showProfileWizard,
    openLogin, openRegister, openRegisterDriver,
    openForgotPassword, openResetPassword, closeModals 
} = useAuthModal();

const customerMenuItems = [
    { name: 'Mi Cuenta', route: 'profile.index', icon: User },
    { name: 'Mis Direcciones', route: 'addresses.index', icon: MapPin },
    { name: 'Mis Pedidos', route: 'orders.history', icon: FileText },
];

const getUserInitial = () => user.value ? (user.value.name || 'U').charAt(0).toUpperCase() : '';

const logout = () => {
    router.post(route('logout'));
    showUserDropdown.value = false;
    showMobileDrawer.value = false;
};

const isIndexPage = computed(() => route().current('shop.index'));
</script>

<template>
    <div class="min-h-[100svh] bg-background text-foreground font-sans flex flex-col selection:bg-primary selection:text-primary-foreground overflow-x-hidden transition-colors duration-300">
        
        <div class="bg-primary text-primary-foreground px-4 py-1.5 text-[10px] md:text-xs font-bold uppercase tracking-wide relative z-[60] shadow-sm overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] animate-shimmer"></div>
            <div class="container mx-auto flex justify-between items-center relative">
                <div class="flex items-center gap-2 truncate max-w-[70%]">
                    <Store :size="12" class="shrink-0" />
                    <span class="truncate">
                        {{ shopContext.branch_name }}
                        <span v-if="shopContext.is_fallback" class="ml-1 bg-navy/20 backdrop-blur-sm border border-navy/10 px-1.5 rounded-[2px] text-[9px] font-black">MATRIZ</span>
                    </span>
                </div>
                <Link :href="route('addresses.create')" class="flex items-center gap-1 hover:underline underline-offset-2 decoration-2 transition-all opacity-90 hover:opacity-100">
                    <MapPin :size="12" /> 
                    <span class="hidden sm:inline font-black">CAMBIAR</span>
                    <span class="sm:hidden font-black">UBICACIÓN</span>
                </Link>
            </div>
        </div>

        <header 
            class="sticky top-0 z-50 transition-all duration-300 w-full"
            :class="isScrolled ? 'glass shadow-lg border-b border-white/5' : 'bg-background/80 backdrop-blur-md border-b border-transparent'"
        >
            <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-2">
                
                <div class="flex items-center gap-3">
                    <button @click="showMobileDrawer = true" class="md:hidden p-2 -ml-2 text-foreground active:scale-95 transition-transform">
                        <Menu :size="24" stroke-width="2.5" />
                    </button>
                    
                    <Link :href="route('shop.index')" class="flex items-center gap-2 group shrink-0 select-none">
                        <div class="w-8 h-8 md:w-9 md:h-9 bg-foreground text-background rounded-lg flex items-center justify-center shadow-lg group-hover:bg-primary group-hover:text-primary-foreground transition-colors duration-300">
                            <Zap :size="18" class="fill-current" />
                        </div>
                        <div class="font-display font-black text-xl md:text-2xl tracking-tighter leading-none italic">
                            BOLIVIA<span class="text-gradient-primary drop-shadow-sm">LOGISTICS</span>
                        </div>
                    </Link>
                </div>

                <div class="flex-1 max-w-md hidden md:block px-4">
                    <div class="relative group">
                        <input disabled placeholder="Buscar productos..." 
                               class="w-full bg-muted/50 border border-transparent rounded-full py-2 pl-10 pr-4 text-sm focus:bg-background focus:border-primary/50 focus:ring-4 focus:ring-primary/10 transition-all duration-300 shadow-inner">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground group-hover:text-primary transition-colors" :size="16" />
                    </div>
                </div>

                <div class="flex items-center gap-1 md:gap-3">
                    <button @click="showMobileSearch = !showMobileSearch" class="md:hidden btn btn-ghost btn-circle btn-sm text-foreground">
                        <Search v-if="!showMobileSearch" :size="20" />
                        <X v-else :size="20" class="text-error" />
                    </button>

                    <ThemeToggler />
                    
                    <Link :href="route('cart.index')" class="relative btn btn-ghost btn-circle hover:bg-primary/5 transition-all group">
                        <ShoppingCart :size="22" class="group-hover:text-primary transition-colors" />
                        <span v-if="cartCount > 0" 
                              class="absolute top-0 right-0 w-4 h-4 bg-primary text-primary-foreground rounded-full text-[10px] font-black flex items-center justify-center ring-2 ring-background animate-pulse-subtle">
                            {{ cartCount }}
                        </span>
                    </Link>

                    <div class="hidden md:block pl-3 border-l border-border/40 relative">
                        <div v-if="user" class="relative">
                            <button @click="showUserDropdown = !showUserDropdown" class="flex items-center gap-2 hover:bg-muted/50 rounded-full pl-2 pr-1 py-1 transition-all border border-transparent hover:border-border/50">
                                <div class="text-right leading-none">
                                    <p class="text-xs font-black truncate max-w-[100px]">{{ user.name }}</p>
                                </div>
                                <div class="avatar avatar-sm bg-gradient-to-br from-primary to-navy text-primary-foreground font-black ring-2 ring-background">
                                    {{ getUserInitial() }}
                                </div>
                            </button>
                            <div v-if="showUserDropdown" class="absolute right-0 top-full mt-2 w-48 bg-card/90 backdrop-blur-xl rounded-xl shadow-xl border border-white/10 overflow-hidden animate-in fade-in slide-in-from-top-2 z-50">
                                <div class="p-1">
                                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-error hover:bg-error/10 rounded-lg font-bold transition-colors">
                                        <LogOut :size="16" /> Cerrar Sesión
                                    </button>
                                </div>
                            </div>
                            <div v-if="showUserDropdown" class="fixed inset-0 z-40" @click="showUserDropdown = false"></div>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <button @click="openLogin" class="text-sm font-bold hover:text-primary transition-colors px-2">Ingresar</button>
                            <button @click="openRegister" class="btn btn-primary btn-sm rounded-full px-4 shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">Registro</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="showMobileSearch" class="md:hidden absolute top-full left-0 w-full bg-background/95 backdrop-blur-xl border-b border-border/50 p-3 shadow-lg z-40 animate-slide-down origin-top">
                <div class="relative">
                    <input autoFocus placeholder="¿Qué buscas hoy?" class="w-full bg-muted/50 border-transparent rounded-xl py-3 pl-10 pr-4 text-base focus:ring-2 focus:ring-primary focus:bg-background transition-all">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 text-primary" :size="18" />
                </div>
            </div>
        </header>

        <main class="flex-1 w-full mx-auto transition-all"
              :class="isIndexPage ? 'max-w-full p-0' : 'max-w-7xl p-4 md:py-8'">
            
            <div v-if="isProfileSection" class="flex flex-col md:flex-row gap-6 lg:gap-8">
                <aside class="w-full md:w-64 flex-shrink-0 hidden md:block">
                    <div class="sticky top-24">
                        <div class="bg-card border border-border/50 rounded-2xl p-4 shadow-sm">
                             <nav class="space-y-1">
                                <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)"
                                      class="flex items-center justify-between px-4 py-3 text-sm font-bold rounded-xl transition-all group border-l-2 border-transparent"
                                      :class="route().current(item.route) ? 'bg-primary/5 text-primary border-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground hover:pl-5'">
                                    <div class="flex items-center gap-3"><component :is="item.icon" :size="18" /> {{ item.name }}</div>
                                    <ChevronRight v-if="route().current(item.route)" :size="14" class="text-primary" />
                                </Link>
                            </nav>
                        </div>
                    </div>
                </aside>
                
                <div class="flex-1 min-w-0 animate-in fade-in duration-500 slide-in-from-bottom-2">
                    <slot />
                </div>
            </div>

            <div v-else class="animate-in fade-in duration-500 w-full">
                <slot />
            </div>

        </main>

        <footer v-if="!isIndexPage" class="bg-muted/30 border-t border-border/50 py-8 mt-auto">
            <div class="container mx-auto px-4 text-center">
                <p class="text-xs text-muted-foreground font-medium uppercase tracking-widest">
                    Electric Luxury &copy; {{ new Date().getFullYear() }} BoliviaLogistics
                </p>
            </div>
        </footer>

        <Transition name="slide-drawer">
            <div v-if="showMobileDrawer" class="fixed inset-0 z-[70] md:hidden">
                <div class="absolute inset-0 bg-navy/60 backdrop-blur-sm transition-opacity" @click="showMobileDrawer = false"></div>

                <aside class="absolute inset-y-0 left-0 w-[85%] max-w-xs bg-background/95 backdrop-blur-xl shadow-2xl border-r border-white/10 flex flex-col h-full">
                    
                    <div class="p-6 flex items-center justify-between border-b border-white/5 bg-gradient-to-r from-primary/5 to-transparent">
                        <span class="font-display font-black italic text-2xl tracking-tighter">
                            BOLIVIA<span class="text-primary">L.</span>
                        </span>
                        
                        <div class="flex items-center gap-4">
                            <ThemeToggler /> <button @click="showMobileDrawer = false" class="p-2 -mr-2 text-muted-foreground hover:text-error transition-colors">
                                <X :size="24" stroke-width="2.5" />
                            </button>
                        </div>
                    </div>
                    <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                        <Link :href="route('shop.index')" 
                              class="flex items-center gap-4 px-4 py-4 text-sm font-bold rounded-xl transition-all border-l-4 border-transparent"
                              :class="route().current('shop.index') ? 'bg-primary/5 text-primary border-primary' : 'text-foreground hover:bg-muted'"
                              @click="showMobileDrawer = false">
                            <Home :size="22" /> Inicio
                        </Link>

                        <template v-if="user">
                            <div class="my-6 border-t border-dashed border-border/40 relative">
                                <span class="absolute top-[-10px] left-4 bg-background px-2 text-[10px] font-black uppercase text-muted-foreground tracking-widest">Tu Espacio</span>
                            </div>
                            
                            <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)"
                                  class="flex items-center gap-4 px-4 py-3.5 text-sm font-bold rounded-xl transition-all border-l-4 border-transparent"
                                  :class="route().current(item.route) ? 'bg-primary/5 text-primary border-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
                                  @click="showMobileDrawer = false">
                                <component :is="item.icon" :size="20" /> {{ item.name }}
                            </Link>
                        </template>
                    </nav>

                    <div class="p-5 border-t border-white/10 bg-muted/20">
                        <div v-if="user">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-primary to-secondary p-[2px]">
                                    <div class="w-full h-full rounded-full bg-background flex items-center justify-center font-black text-lg">
                                        {{ getUserInitial() }}
                                    </div>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-black truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <button @click="logout" class="w-full btn btn-outline border-error/30 text-error hover:bg-error hover:text-white justify-center gap-2 font-bold">
                                <LogOut :size="18"/> Cerrar Sesión
                            </button>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-3">
                            <button @click="openLogin(); showMobileDrawer = false" class="btn btn-outline w-full font-bold">Ingresar</button>
                            <button @click="openRegister(); showMobileDrawer = false" class="btn btn-primary w-full shadow-lg font-bold">Registro</button>
                        </div>
                    </div>
                </aside>
            </div>
        </Transition>

        <Toast />
        
        <Teleport to="body">
             <Transition name="modal-bounce">
                <div v-if="showLogin || showRegister || showRegisterDriver || showForgotPassword || showResetPassword || showProfileWizard" 
                     class="fixed inset-0 z-[100] flex items-end md:items-center justify-center sm:p-4">
                     <div class="absolute inset-0 bg-navy/80 backdrop-blur-sm" @click="closeModals"></div>
                     
                     <div class="relative bg-background w-full md:w-auto md:min-w-[420px] md:rounded-3xl rounded-t-[2rem] shadow-2xl z-50 border border-white/10 flex flex-col max-h-[90vh] overflow-hidden modal-shadow-glow">
                        <div class="overflow-y-auto scrollbar-hide p-6 md:p-8">
                             <LoginForm v-if="showLogin" @close="closeModals" @switchToRegister="openRegister" @switchToForgot="openForgotPassword" />
                             <RegisterForm v-if="showRegister" :activeBranches="page.props.active_branches || []" @close="closeModals" @switchToLogin="openLogin" @switchToDriver="openRegisterDriver" />
                             <RegisterDriverForm v-if="showRegisterDriver" @close="closeModals" @switchToRegister="openRegister"/>
                             <ForgotPasswordForm v-if="showForgotPassword" @close="closeModals" @switchToLogin="openLogin" />
                        </div>
                     </div>
                </div>
             </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
/* Transiciones Personalizadas */
.slide-drawer-enter-active, .slide-drawer-leave-active { transition: opacity 0.3s ease; }
.slide-drawer-enter-from, .slide-drawer-leave-to { opacity: 0; }
.slide-drawer-enter-active aside { animation: slideInLeft 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-drawer-leave-active aside { animation: slideOutLeft 0.3s cubic-bezier(0.16, 1, 0.3, 1); }

@keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } }
@keyframes slideOutLeft { from { transform: translateX(0); } to { transform: translateX(-100%); } }

.modal-bounce-enter-active { animation: modalUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-bounce-leave-active { animation: modalDown 0.3s ease-in; }

@keyframes modalUp { 
    from { opacity: 0; transform: translateY(100%) scale(0.95); } 
    to { opacity: 1; transform: translateY(0) scale(1); } 
}
@keyframes modalDown { 
    to { opacity: 0; transform: translateY(100%); } 
}

/* Efecto Glow Superior para el Modal Móvil */
.modal-shadow-glow {
    box-shadow: 0 -10px 40px -10px rgba(0, 240, 255, 0.15), 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Scrollbar oculto pero funcional */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>