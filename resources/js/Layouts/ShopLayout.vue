<script setup>
    import { Link, usePage, router } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    import ThemeToggler from '@/Components/Base/ThemeToggler.vue';
    import Toast from '@/Components/Base/Toast.vue';
    
    // Iconos Generales
    import { 
        MapPin, ChevronDown, ShoppingCart, Menu, X, 
        LogIn, UserPlus, Gift, Package, User, 
        ShieldCheck, FileText, LogOut 
    } from 'lucide-vue-next';

    // Modales Auth
    import LoginForm from '@/Components/Auth/LoginForm.vue';
    import RegisterForm from '@/Components/Auth/RegisterForm.vue';
    import RegisterDriverForm from '@/Components/Auth/RegisterDriverForm.vue';
    import ForgotPasswordForm from '@/Components/Auth/ForgotPasswordForm.vue';
    import ResetPasswordForm from '@/Components/Auth/ResetPasswordForm.vue';
    import ProfileWizardForm from '@/Components/Auth/ProfileWizardForm.vue';
    import { useAuthModal } from '@/Composables/useAuthModal'; 

    // PROPS PARA ACTIVAR EL MODO PERFIL
    const props = defineProps({
        isProfileSection: { type: Boolean, default: false } // <--- NUEVA PROP
    });

    const page = usePage();
    const user = computed(() => page.props.auth?.user || null);
    const addresses = computed(() => page.props.auth?.addresses || []);
    const cartCount = computed(() => page.props.cart_count || 0);
    const activeBranches = computed(() => page.props.active_branches || []);
    const percentage = computed(() => user.value?.completion_percentage || 0);

    // --- MENÚ LATERAL (CUSTOMER) ---
    const customerMenuItems = [
        { name: 'Datos Personales', route: 'profile.index', icon: User },
        { name: 'Mis Direcciones', route: 'addresses.index', icon: MapPin },
        { name: 'Seguridad', route: 'profile.security', icon: ShieldCheck }, 
        { name: 'Verificación Legal', route: 'profile.verification', icon: FileText },
    ];

    // --- ESTADO UI ---
    const showUserMenu = ref(false);
    const showAddressMenu = ref(false);
    const showMobileMenu = ref(false);
    const tempEmail = ref('');

    // --- COMPOSABLE AUTH ---
    const { 
        showLogin, showRegister, showRegisterDriver, 
        showForgotPassword, showResetPassword, showProfileWizard,
        openLogin, openRegister, openRegisterDriver,
        openForgotPassword, openResetPassword, closeModals 
    } = useAuthModal();

    const handleSwitchToReset = (email) => {
        tempEmail.value = email;
        openResetPassword();
    };

    // --- LÓGICA DIRECCIÓN ---
    const currentAddress = computed(() => {
        if (!user.value || addresses.value.length === 0) return null;
        return addresses.value.find(a => a.is_default) || addresses.value[0];
    });
    
    const switchAddress = (addressId) => {
        router.post(route('shop.setLocation'), { address_id: addressId }, {
            preserveScroll: true,
            onSuccess: () => showAddressMenu.value = false
        });
    };

    // --- HELPERS ---
    const getUserInitial = () => {
        if (!user.value) return '';
        const label = user.value.profile?.first_name || user.value.name || user.value.phone || 'U';
        return String(label).charAt(0).toUpperCase();
    };
    
    const getUserDisplayName = () => {
        if (!user.value) return '';
        return user.value.profile?.first_name || user.value.name || user.value.phone || 'Usuario';
    };
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans relative flex flex-col">
        
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-6">
                        <Link :href="route('shop.index')" class="shrink-0 flex items-center font-black text-2xl tracking-tighter italic">
                            <span class="text-gray-900">BOLIVIA</span><span class="text-blue-600">LOGISTICS</span>
                        </Link>
                        <div v-if="user" class="relative hidden md:block pl-6 border-l border-gray-200">
                            <button @click.stop="showAddressMenu = !showAddressMenu" class="flex items-center gap-2 hover:bg-gray-100 px-3 py-1.5 rounded-lg transition text-left">
                                <div class="bg-blue-50 text-blue-600 p-1.5 rounded-md"><MapPin :size="16" /></div>
                                <div class="leading-tight">
                                    <span class="block text-[10px] text-gray-400 font-bold uppercase">Enviar a</span>
                                    <span class="block text-xs font-bold text-gray-800 flex items-center gap-1">
                                        {{ currentAddress ? currentAddress.alias : 'Seleccionar' }} 
                                        <ChevronDown :size="12" class="text-gray-400"/>
                                    </span>
                                </div>
                            </button>
                            <div v-if="showAddressMenu" class="absolute top-full left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">
                                <div v-if="addresses.length > 0" class="max-h-64 overflow-y-auto">
                                    <button v-for="addr in addresses" :key="addr.id" @click="switchAddress(addr.id)" class="w-full text-left px-4 py-3 hover:bg-blue-50 transition border-b border-gray-100">
                                        <div class="font-bold text-sm">{{ addr.alias }} <span v-if="addr.is_default" class="text-[9px] bg-green-100 text-green-700 px-1 rounded">ACTIVA</span></div>
                                        <div class="text-xs text-gray-500 truncate">{{ addr.address }}</div>
                                    </button>
                                </div>
                                <Link :href="route('addresses.create')" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 text-xs font-bold">+ AÑADIR NUEVA</Link>
                                <div v-if="showAddressMenu" @click="showAddressMenu = false" class="fixed inset-0 z-[-1]"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <ThemeToggler class="hidden md:flex" />
                        <Link :href="route('cart.index')" class="relative p-2 text-gray-500 hover:text-blue-600 transition group">
                            <ShoppingCart class="group-hover:scale-110 transition" :size="24" />
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm">{{ cartCount }}</span>
                        </Link>
                        <div v-if="user" class="relative hidden md:block">
                            <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 group">
                                <div class="text-right hidden lg:block">
                                    <p class="text-xs font-bold text-gray-800">{{ getUserDisplayName() }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase">Mi Cuenta</p>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-md">{{ getUserInitial() }}</div>
                            </button>
                            <div v-if="showUserMenu" class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                                <div class="py-1">
                                    <Link v-for="item in customerMenuItems" :key="item.route" :href="route(item.route)" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                        <component :is="item.icon" :size="16" class="text-gray-400" /> {{ item.name }}
                                    </Link>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 font-bold"><LogOut :size="16" /> Cerrar Sesión</Link>
                                </div>
                                <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-[-1]"></div>
                            </div>
                        </div>
                        <div v-else class="hidden md:flex items-center gap-2">
                            <button @click="openLogin" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-blue-600">Ingresar</button>
                            <button @click="openRegister" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md">Regístrate</button>
                        </div>
                        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 text-gray-600"><Menu v-if="!showMobileMenu" :size="24" /><X v-else :size="24" /></button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex-1 relative z-0">
            
            <div v-if="isProfileSection" class="min-h-[calc(100vh-64px)]">
                <div class="bg-white border-b border-gray-200 sticky top-16 z-30 shadow-sm">
                    <div class="max-w-5xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <h2 class="text-sm font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                            <span class="text-blue-600">●</span> Centro de Mando
                        </h2>
                        <div class="w-full sm:w-auto flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Integridad</p>
                                <p class="text-xs font-black text-blue-600">{{ percentage }}%</p>
                            </div>
                            <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 transition-all duration-1000" :style="{ width: `${percentage}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="max-w-5xl mx-auto px-4 py-8">
                    <div class="flex flex-col md:flex-row gap-8">
                        <aside class="w-full md:w-64 flex-shrink-0">
                            <nav class="space-y-1">
                                <Link v-for="item in customerMenuItems" :key="item.route" 
                                      :href="route(item.route)"
                                      class="group flex items-center justify-between px-4 py-3 text-sm font-bold rounded-xl transition-all border"
                                      :class="route().current(item.route) ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-600 border-gray-100 hover:border-blue-300 hover:text-blue-600'">
                                    <div class="flex items-center gap-3">
                                        <component :is="item.icon" :size="18" /> {{ item.name }}
                                    </div>
                                </Link>
                            </nav>
                        </aside>
                        <main class="flex-1 min-w-0">
                            <slot />
                        </main>
                    </div>
                </div>
            </div>

            <div v-else>
                <slot />
            </div>

        </div>

        <footer class="bg-white border-t border-gray-200 py-8 mt-auto"><div class="container mx-auto px-4 text-center"><p class="text-sm text-gray-400">© {{ new Date().getFullYear() }} BoliviaLogistics.</p></div></footer>
        <Toast /> 
        <Teleport to="body">
            <div v-if="showLogin || showRegister || showRegisterDriver || showForgotPassword || showResetPassword || showProfileWizard" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModals"></div>
                <div class="relative bg-white w-full rounded-2xl shadow-2xl overflow-hidden z-50 border border-white/20" :class="[showRegister || showRegisterDriver ? 'max-w-lg h-[80vh]' : 'max-w-sm']">
                    <button @click="closeModals" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-20 p-2"><X :size="20" /></button>
                    <LoginForm v-if="showLogin" @close="closeModals" @switchToRegister="openRegister" @switchToForgot="openForgotPassword" />
                    <RegisterForm v-if="showRegister" :activeBranches="activeBranches" @close="closeModals" @switchToLogin="openLogin" @switchToDriver="openRegisterDriver" />
                    <RegisterDriverForm v-if="showRegisterDriver" @close="closeModals" @switchToLogin="openLogin" @switchToClient="openRegister" />
                    <ForgotPasswordForm v-if="showForgotPassword" @close="closeModals" @switchToLogin="openLogin" @switchToReset="handleSwitchToReset" />
                    <ResetPasswordForm v-if="showResetPassword" :email="tempEmail" @close="closeModals" @switchToLogin="openLogin" />
                    <ProfileWizardForm v-if="showProfileWizard" @close="closeModals" />
                </div>
            </div>
        </Teleport>
    </div>
</template>