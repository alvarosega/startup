<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import Toast from '@/Components/Base/Toast.vue';
import { 
    MapPin, ShoppingCart, Menu, X, LogIn, User, 
    FileText, LogOut, Store, Package, Home, Search, ChevronRight,
    ChevronDown, Heart
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
const showUserDropdown = ref(false); // Dropdown Desktop
const showMobileDrawer = ref(false); // Drawer Móvil (Sidebar)

const handleScroll = () => { isScrolled.value = window.scrollY > 10; };
onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

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
</script>

<template>
    <div class="min-h-screen bg-background text-foreground font-sans flex flex-col selection:bg-primary/20 selection:text-primary">
        
        <div class="bg-primary text-primary-foreground px-4 py-2 text-[10px] md:text-xs font-medium relative z-50 shadow-md">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2 truncate max-w-[70%]">
                    <Store :size="14" class="text-primary-foreground/70 shrink-0" />
                    <span class="truncate font-semibold">
                        {{ shopContext.branch_name }}
                        <span v-if="shopContext.is_fallback" class="ml-1 bg-warning text-warning-foreground px-1.5 py-0.5 rounded text-[9px] font-black tracking-wider">MATRIZ</span>
                    </span>
                </div>
                <Link :href="route('addresses.create')" class="flex items-center gap-1 hover:text-white/80 transition-colors whitespace-nowrap font-bold">
                    <MapPin :size="12" /> <span class="hidden sm:inline">Cambiar</span> Ubicación
                </Link>
            </div>
        </div>

        <header 
            class="sticky top-0 z-40 transition-all duration-300 border-b border-transparent"
            :class="isScrolled ? 'glass border-border/40 shadow-sm' : 'bg-background border-border/20'"
        >
            <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-3">
                
                <div class="flex items-center gap-3 md:gap-4">
                    <button @click="showMobileDrawer = true" class="md:hidden p-2 -ml-2 text-foreground hover:bg-muted rounded-full transition-colors">
                        <Menu :size="24" />
                    </button>

                    <Link :href="route('shop.index')" class="flex items-center gap-2 group shrink-0">
                        <div class="w-9 h-9 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-primary-foreground shadow-sm group-hover:scale-105 transition-transform border border-white/10">
                            <span class="font-black italic text-sm">BL</span>
                        </div>
                        <div class="font-display font-black text-xl tracking-tighter italic leading-none hidden xs:block">
                            BOLIVIA<span class="text-gradient-primary">LOGISTICS</span>
                        </div>
                    </Link>
                </div>

                <div class="flex-1 max-w-md hidden md:block">
                    <div class="relative group cursor-pointer">
                        <input disabled placeholder="Buscar productos..." class="w-full bg-muted/40 border-transparent rounded-full py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all hover:bg-muted/60">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground group-hover:text-primary transition-colors" :size="18" />
                    </div>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <ThemeToggler />
                    
                    <Link :href="route('cart.index')" class="relative btn btn-ghost btn-circle hover:bg-primary/10 hover:text-primary transition-all">
                        <ShoppingCart :size="24" />
                        <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-primary-foreground rounded-full text-[10px] font-bold flex items-center justify-center animate-bounce-subtle border-2 border-background">
                            {{ cartCount }}
                        </span>
                    </Link>

                    <div class="hidden md:block pl-2 border-l border-border/50 relative">
                        <div v-if="user" class="relative">
                            <button @click="showUserDropdown = !showUserDropdown" class="flex items-center gap-3 hover:opacity-80 transition-opacity p-1 pr-2 rounded-full hover:bg-muted/50 focus:outline-none">
                                <div class="text-right leading-tight">
                                    <p class="text-xs font-bold">{{ user.name }}</p>
                                    <p class="text-[10px] text-muted-foreground">Mi Cuenta</p>
                                </div>
                                <div class="avatar avatar-sm bg-gradient-to-tr from-primary to-accent text-primary-foreground font-bold shadow-md">
                                    {{ getUserInitial() }}
                                </div>
                                <ChevronDown :size="14" class="text-muted-foreground transition-transform" :class="{'rotate-180': showUserDropdown}"/>
                            </button>

                            <div v-if="showUserDropdown" class="absolute right-0 top-full mt-2 w-56 bg-card rounded-xl shadow-xl border border-border/50 overflow-hidden animate-in fade-in zoom-in-95 z-50">
                                <div class="p-3 border-b border-border/50 bg-muted/20">
                                    <p class="text-xs text-muted-foreground">Conectado como</p>
                                    <p class="font-bold text-sm truncate">{{ user.email }}</p>
                                </div>
                                <div class="p-1">
                                    <Link v-for="item in customerMenuItems" :key="item.route" 
                                          :href="route(item.route)" 
                                          class="flex items-center gap-3 px-3 py-2 text-sm text-foreground hover:bg-primary/10 hover:text-primary rounded-lg transition-colors"
                                          @click="showUserDropdown = false">
                                        <component :is="item.icon" :size="16" /> {{ item.name }}
                                    </Link>
                                </div>
                                <div class="p-1 border-t border-border/50">
                                    <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-error hover:bg-error/10 rounded-lg transition-colors font-medium">
                                        <LogOut :size="16" /> Cerrar Sesión
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="showUserDropdown" class="fixed inset-0 z-40" @click="showUserDropdown = false"></div>
                        </div>

                        <div v-else class="flex items-center gap-2">
                            <button @click="openLogin" class="btn btn-ghost btn-sm font-bold text-foreground hover:text-primary">Ingresar</button>
                            <button @click="openRegister" class="btn btn-primary btn-sm shadow-lg shadow-primary/20">Registro</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full max-w-7xl mx-auto p-4 md:py-8">
            <div v-if="isProfileSection" class="flex flex-col md:flex-row gap-6 lg:gap-10">
                <aside class="w-full md:w-64 flex-shrink-0">
                    <div class="md:hidden overflow-x-auto pb-1 -mx-4 px-4 scrollbar-hide flex gap-2 sticky top-[70px] z-30 py-2 bg-background/95 backdrop-blur-sm">
                        <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)"
                              class="flex items-center gap-2 px-4 py-2 rounded-full border text-xs font-bold whitespace-nowrap transition-all"
                              :class="route().current(item.route) ? 'bg-primary text-primary-foreground shadow-md' : 'bg-card border-border text-muted-foreground'">
                            <component :is="item.icon" :size="14" /> {{ item.name }}
                        </Link>
                    </div>

                    <div class="hidden md:block sticky top-24">
                        <div class="card p-3 shadow-sm border-border/60">
                            <div class="px-3 py-4 flex items-center gap-3 border-b border-border/30 mb-2">
                                <div class="avatar avatar-md bg-gradient-to-tr from-primary to-accent text-primary-foreground shadow-md">{{ getUserInitial() }}</div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-sm truncate text-foreground">{{ user?.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user?.email }}</p>
                                </div>
                            </div>
                            <nav class="space-y-1">
                                <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)"
                                      class="flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-all group"
                                      :class="route().current(item.route) ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground'">
                                    <div class="flex items-center gap-3"><component :is="item.icon" :size="18" /> {{ item.name }}</div>
                                    <ChevronRight v-if="route().current(item.route)" :size="14" />
                                </Link>
                            </nav>
                        </div>
                    </div>
                </aside>
                <div class="flex-1 min-w-0 animate-in fade-in duration-500 slide-in-from-bottom-2"><slot /></div>
            </div>
            <div v-else class="animate-in fade-in duration-500"><slot /></div>
        </main>

        <footer class="bg-muted/20 border-t border-border py-8 mt-auto">
            <div class="container mx-auto px-4 text-center"><p class="text-sm text-muted-foreground">© {{ new Date().getFullYear() }} BoliviaLogistics System.</p></div>
        </footer>

        <Transition name="slide-right">
            <div v-if="showMobileDrawer" class="fixed inset-0 z-[60] md:hidden">
                <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" @click="showMobileDrawer = false"></div>
                
                <aside class="absolute inset-y-0 left-0 w-[85%] max-w-xs bg-card shadow-2xl border-r border-border p-5 flex flex-col animate-slide-in-left">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-primary-foreground font-black italic">BL</div>
                            <span class="font-display font-black text-lg">BOLIVIA<span class="text-primary">LOGISTICS</span></span>
                        </div>
                        <button @click="showMobileDrawer = false" class="btn btn-ghost btn-sm btn-circle"><X :size="20"/></button>
                    </div>

                    <div v-if="user" class="mb-6 p-4 rounded-2xl bg-muted/40 border border-border flex items-center gap-3">
                        <div class="avatar avatar-md bg-gradient-to-tr from-primary to-secondary text-primary-foreground font-bold shadow-sm">{{ getUserInitial() }}</div>
                        <div class="overflow-hidden">
                            <p class="font-bold text-sm truncate">{{ user.name }}</p>
                            <Link :href="route('profile.index')" class="text-xs text-primary font-medium hover:underline" @click="showMobileDrawer = false">Ver mi perfil</Link>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-3 mb-6">
                        <button @click="{openLogin(); showMobileDrawer=false;}" class="btn btn-outline w-full">Ingresar</button>
                        <button @click="{openRegister(); showMobileDrawer=false;}" class="btn btn-primary w-full">Registro</button>
                    </div>

                    <nav class="flex-1 space-y-1">
                        <p class="px-4 text-xs font-bold text-muted-foreground uppercase mb-2 tracking-wider">Navegación</p>
                        <Link :href="route('shop.index')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-foreground hover:bg-muted rounded-xl transition-colors" @click="showMobileDrawer = false">
                            <Home :size="20" class="text-muted-foreground"/> Inicio
                        </Link>
                        <Link :href="route('shop.index', { type: 'bundles' })" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-foreground hover:bg-muted rounded-xl transition-colors" @click="showMobileDrawer = false">
                            <Package :size="20" class="text-muted-foreground"/> Ofertas y Packs
                        </Link>
                        
                        <div v-if="user" class="pt-4 mt-4 border-t border-border">
                            <p class="px-4 text-xs font-bold text-muted-foreground uppercase mb-2 tracking-wider">Mi Cuenta</p>
                            <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)" 
                                  class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-foreground hover:bg-muted rounded-xl transition-colors"
                                  @click="showMobileDrawer = false">
                                <component :is="item.icon" :size="20" class="text-primary"/> {{ item.name }}
                            </Link>
                        </div>
                    </nav>

                    <div v-if="user" class="pt-4 border-t border-border mt-auto">
                        <button @click="logout" class="w-full btn btn-ghost text-error hover:bg-error/10 justify-start gap-3 px-4">
                            <LogOut :size="20"/> Cerrar Sesión
                        </button>
                    </div>
                </aside>
            </div>
        </Transition>

        <Toast />
        
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showLogin || showRegister || showRegisterDriver || showForgotPassword || showResetPassword || showProfileWizard" 
                     class="fixed inset-0 z-[100] flex items-end md:items-center justify-center sm:p-4">
                    <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity" @click="closeModals"></div>
                    <div class="relative bg-card w-full md:w-auto md:min-w-[400px] md:rounded-3xl rounded-t-[2rem] shadow-2xl overflow-hidden z-50 border border-border/50 flex flex-col max-h-[90vh] animate-slide-up-mobile md:animate-scale-in">
                        <div class="md:hidden w-full flex justify-center pt-3 pb-1" @click="closeModals"><div class="w-12 h-1.5 bg-muted rounded-full"></div></div>
                        <button @click="closeModals" class="hidden md:block absolute top-4 right-4 text-muted-foreground hover:text-foreground z-20 p-2 rounded-full hover:bg-muted transition-colors"><X :size="20" /></button>
                        <div class="overflow-y-auto scrollbar-thin p-6 md:p-8" :class="[showRegister || showRegisterDriver ? 'h-[80vh] md:h-auto md:max-h-[85vh] md:w-[500px]' : '']">
                            <LoginForm v-if="showLogin" @close="closeModals" @switchToRegister="openRegister" @switchToForgot="openForgotPassword" />
                            <RegisterForm v-if="showRegister" :activeBranches="page.props.active_branches || []" @close="closeModals" @switchToLogin="openLogin" @switchToDriver="openRegisterDriver" />
                            <RegisterDriverForm v-if="showRegisterDriver" @close="closeModals" @switchToLogin="openLogin" @switchToClient="openRegister" />
                            <ForgotPasswordForm 
                                v-if="showForgotPassword" 
                                @close="closeModals" 
                                @switchToLogin="openLogin" 
                                @switchToReset="handleSwitchToReset" 
                            />
                            <ResetPasswordForm 
                                v-if="showResetPassword" 
                                :email="tempEmail" 
                                @close="closeModals" 
                                @switchToLogin="openLogin" 
                                />
                            <ProfileWizardForm v-if="showProfileWizard" @close="closeModals" />
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-right-enter-active, .slide-right-leave-active { transition: opacity 0.3s ease; }
.slide-right-enter-from, .slide-right-leave-to { opacity: 0; }

@keyframes slideInLeft {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}
.animate-slide-in-left {
    animation: slideInLeft 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

@keyframes slideUpMobile { from { transform: translateY(100%); } to { transform: translateY(0); } }
.animate-slide-up-mobile { animation: slideUpMobile 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
</style>