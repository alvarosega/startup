<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
import Toast from '@/Components/Base/Toast.vue';
import { 
    MapPin, ChevronDown, ShoppingCart, Menu, X, 
    LogIn, User, ShieldCheck, FileText, LogOut, Store, Package 
} from 'lucide-vue-next';

// Modales
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

// --- AUTH & UI STATE ---
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const tempEmail = ref('');

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

const handleSwitchToReset = (email) => {
    tempEmail.value = email;
    openResetPassword();
};

const getUserInitial = () => user.value ? (user.value.name || 'U').charAt(0).toUpperCase() : '';
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans relative flex flex-col">
        
        <div class="bg-indigo-900 text-white px-4 py-2 text-sm relative z-50">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <Store :size="16" class="text-indigo-300" />
                    <span class="text-indigo-200 hidden sm:inline">Estás comprando en:</span>
                    <span class="font-bold text-white flex items-center gap-2">
                        {{ shopContext.branch_name }}
                        <span v-if="shopContext.is_fallback" class="bg-amber-500 text-black text-[10px] px-1.5 rounded font-black">
                            MATRIZ
                        </span>
                    </span>
                </div>
                <Link :href="route('addresses.create')" class="text-xs bg-indigo-700 hover:bg-indigo-600 text-white px-3 py-1 rounded transition border border-indigo-500 flex items-center gap-1">
                    <MapPin :size="12" /> Cambiar Ubicación
                </Link>
            </div>
        </div>

        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    
                    <div class="flex items-center gap-6">
                        <Link :href="route('shop.index')" class="shrink-0 flex items-center font-black text-2xl tracking-tighter italic">
                            <span class="text-gray-900">BOLIVIA</span><span class="text-blue-600">LOGISTICS</span>
                        </Link>

                        <div class="hidden md:flex items-center gap-4 ml-4">
                            <Link :href="route('shop.index', { type: 'bundles' })" 
                                  class="text-sm font-bold text-gray-600 hover:text-blue-600 flex items-center gap-1 transition-colors"
                                  :class="route().params.type === 'bundles' ? 'text-blue-600' : ''">
                                <Package :size="18" /> Packs
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <ThemeToggler class="hidden md:flex" />
                        
                        <Link :href="route('cart.index')" class="relative p-2 text-gray-500 hover:text-blue-600 transition group">
                            <ShoppingCart class="group-hover:scale-110 transition" :size="24" />
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm">
                                {{ cartCount }}
                            </span>
                        </Link>

                        <div v-if="user" class="relative hidden md:block">
                            <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 group">
                                <div class="text-right hidden lg:block">
                                    <p class="text-xs font-bold text-gray-800">{{ user.name }}</p>
                                    <p class="text-xs text-gray-400 uppercase">Cliente</p>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-md">
                                    {{ getUserInitial() }}
                                </div>
                            </button>
                            <div v-if="showUserMenu" class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-100">
                                <div class="py-1">
                                    <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                        <component :is="item.icon" :size="16" class="text-gray-400" /> {{ item.name }}
                                    </Link>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 font-bold">
                                        <LogOut :size="16" /> Cerrar Sesión
                                    </Link>
                                </div>
                                <div class="fixed inset-0 z-[-1]" @click="showUserMenu = false"></div>
                            </div>
                        </div>

                        <div v-else class="hidden md:flex items-center gap-2">
                            <button @click="openLogin" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-blue-600">Ingresar</button>
                            <button @click="openRegister" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md">Regístrate</button>
                        </div>

                        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 text-gray-600">
                            <Menu v-if="!showMobileMenu" :size="24" />
                            <X v-else :size="24" />
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div v-if="showMobileMenu" class="md:hidden absolute top-16 left-0 w-full bg-white border-b border-gray-200 shadow-lg py-4 px-4 flex flex-col gap-4 z-40 animate-in slide-in-from-top-2">
            
            <Link :href="route('shop.index', { type: 'bundles' })" 
                  class="flex items-center gap-2 text-gray-600 font-bold p-2 rounded-lg hover:bg-gray-50"
                  @click="showMobileMenu = false">
                <Package :size="20" class="text-indigo-600"/> Ver Packs y Ofertas
            </Link>

            <div v-if="!user" class="flex flex-col gap-2">
                <button @click="{openLogin(); showMobileMenu=false;}" class="w-full py-2 text-sm font-bold text-center border rounded-lg">Ingresar</button>
                <button @click="{openRegister(); showMobileMenu=false;}" class="w-full py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-md">Regístrate</button>
            </div>
            
            <div v-else class="flex flex-col gap-2">
                 <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-3 px-2 py-2 text-sm text-red-500 font-bold">
                    <LogOut :size="16" /> Cerrar Sesión
                </Link>
            </div>
        </div>

        <div class="flex-1 relative z-0">
            <div v-if="isProfileSection" class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
                <aside class="w-full md:w-64 flex-shrink-0">
                    <nav class="space-y-1">
                        <Link v-for="item in customerMenuItems" :key="item.route" 
                              :href="route(item.route)"
                              class="flex items-center gap-3 px-4 py-3 text-sm font-bold rounded-xl transition-all border"
                              :class="route().current(item.route) ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-600 border-gray-100 hover:border-blue-300 hover:text-blue-600'">
                            <component :is="item.icon" :size="18" /> {{ item.name }}
                        </Link>
                    </nav>
                </aside>
                <main class="flex-1 min-w-0">
                    <slot />
                </main>
            </div>

            <div v-else>
                <slot />
            </div>
        </div>

        <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
            <div class="container mx-auto px-4 text-center">
                <p class="text-sm text-gray-400">© {{ new Date().getFullYear() }} BoliviaLogistics.</p>
            </div>
        </footer>

        <Toast /> 
        
        <Teleport to="body">
            <div v-if="showLogin || showRegister || showRegisterDriver || showForgotPassword || showResetPassword || showProfileWizard" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModals"></div>
                <div class="relative bg-white w-full rounded-2xl shadow-2xl overflow-hidden z-50 border border-white/20 transition-all duration-300" 
                     :class="[showRegister || showRegisterDriver ? 'max-w-lg h-[85vh]' : 'max-w-sm']">
                    <button @click="closeModals" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-20 p-2"><X :size="20" /></button>
                    
                    <div class="h-full overflow-y-auto scrollbar-thin">
                        <LoginForm v-if="showLogin" @close="closeModals" @switchToRegister="openRegister" @switchToForgot="openForgotPassword" />
                        <RegisterForm v-if="showRegister" :activeBranches="page.props.active_branches || []" @close="closeModals" @switchToLogin="openLogin" @switchToDriver="openRegisterDriver" />
                        <RegisterDriverForm v-if="showRegisterDriver" @close="closeModals" @switchToLogin="openLogin" @switchToClient="openRegister" />
                        <ForgotPasswordForm v-if="showForgotPassword" @close="closeModals" @switchToLogin="openLogin" @switchToReset="handleSwitchToReset" />
                        <ResetPasswordForm v-if="showResetPassword" :email="tempEmail" @close="closeModals" @switchToLogin="openLogin" />
                        <ProfileWizardForm v-if="showProfileWizard" @close="closeModals" />
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>