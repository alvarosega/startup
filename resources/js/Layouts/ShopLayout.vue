<script setup>
    import { Link, usePage, router } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    import Toast from '@/Components/Base/Toast.vue';
    import LoginForm from '@/Components/Auth/LoginForm.vue';
    import RegisterForm from '@/Components/Auth/RegisterForm.vue';
    import { useAuthModal } from '@/Composables/useAuthModal'; 
    import { 
        MapPin, ChevronDown, ShoppingCart, 
        User, Menu, X, LogIn, UserPlus, LayoutDashboard 
    } from 'lucide-vue-next';
    
    const page = usePage();
    // Obtenemos el usuario de forma segura
    const user = computed(() => page.props.auth?.user || null);
    const addresses = computed(() => page.props.auth?.addresses || []);
    const cartCount = computed(() => page.props.cart_count || 0);
    const activeBranches = computed(() => page.props.active_branches || []);
    
    // --- ESTADO DEL UI ---
    const showUserMenu = ref(false);
    const showAddressMenu = ref(false);
    const showMobileMenu = ref(false);
    
    const { showLogin, showRegister, openLogin, openRegister, closeModals } = useAuthModal();
    
    // --- LÓGICA SEGURA ---
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
    
    // --- CORRECCIÓN CRÍTICA DEL ERROR CHARAT ---
    // Esta función evita que la pantalla se ponga blanca si el usuario no tiene nombre
    const getUserInitial = () => {
        if (!user.value) return '';
        // Prioridad: Nombre -> Alias -> Teléfono -> 'U'
        const label = user.value.name || user.value.alias || user.value.phone || 'U';
        return String(label).charAt(0).toUpperCase();
    };
    
    // Helper para mostrar nombre o teléfono
    const getUserDisplayName = () => {
        if (!user.value) return '';
        return user.value.name || user.value.phone || 'Usuario';
    };
    
    // Verificar si tiene permiso de admin (ajusta según cómo mandes tus roles/permisos)
    const isAdminOrStaff = computed(() => {
        // Si usas Spatie y compartes los roles en el handleInertiaRequests:
        const roles = user.value?.roles || [];
        // Si roles es un array de objetos (Spatie standard) o strings, ajusta aquí:
        return roles.some(r => ['super_admin', 'branch_admin', 'logistics_manager'].includes(r.name || r));
    });
    </script>
    
    <template>
        <div class="min-h-screen bg-gray-50 text-gray-800 font-sans relative flex flex-col">
            
            <nav class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex items-center gap-6">
                            <Link :href="route('shop.index')" class="shrink-0 flex items-center font-black text-2xl tracking-tighter italic">
                                <span class="text-blue-600">BOLIVIA</span>LOGISTICS
                            </Link>
    
                            <div v-if="user" class="relative hidden md:block">
                                <button @click.stop="showAddressMenu = !showAddressMenu; showUserMenu = false" class="flex items-center gap-2 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition text-left border border-transparent hover:border-gray-200">
                                    <div class="bg-blue-50 p-1.5 rounded-full text-blue-600"><MapPin :size="16" /></div>
                                    <div class="leading-tight">
                                        <span class="block text-[10px] text-gray-400 font-bold uppercase">Enviar a</span>
                                        <span class="block text-xs font-black text-gray-800 flex items-center gap-1">
                                            {{ currentAddress ? currentAddress.alias : 'Seleccionar' }} <ChevronDown :size="12" class="text-gray-400"/>
                                        </span>
                                    </div>
                                </button>
                                <div v-if="showAddressMenu" class="absolute top-full left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">
                                    <div class="p-3 bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-500 uppercase tracking-wider">Mis Direcciones</div>
                                    <div v-if="addresses.length > 0" class="max-h-64 overflow-y-auto">
                                        <button v-for="addr in addresses" :key="addr.id" @click="switchAddress(addr.id)" class="w-full text-left px-4 py-3 hover:bg-blue-50 transition border-b border-gray-50">
                                            <div class="font-bold text-sm">{{ addr.alias }} <span v-if="addr.is_default" class="text-[9px] bg-green-100 text-green-700 px-1 rounded">ACTIVA</span></div>
                                            <div class="text-xs text-gray-500 truncate">{{ addr.address }}</div>
                                        </button>
                                    </div>
                                    <Link :href="route('addresses.create')" class="block w-full text-center bg-blue-600 text-white py-3 text-xs font-bold">+ AÑADIR NUEVA</Link>
                                </div>
                                <div v-if="showAddressMenu" @click="showAddressMenu = false" class="fixed inset-0 z-[-1]"></div>
                            </div>
                        </div>
    
                        <div class="flex items-center gap-4">
                            <Link :href="route('cart.index')" class="relative p-2 text-gray-500 hover:text-blue-600 transition group mr-2">
                                <ShoppingCart class="group-hover:scale-110 transition" :size="24" />
                                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-600 rounded-full border-2 border-white shadow-sm animate-bounce-short">{{ cartCount }}</span>
                            </Link>
    
                            <div v-if="user" class="relative hidden md:block">
                                <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 focus:outline-none group">
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-700">{{ getUserDisplayName() }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase">Mi Cuenta</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border-2 border-transparent group-hover:border-blue-200 transition">
                                        {{ getUserInitial() }}
                                    </div>
                                </button>
    
                                <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-2 border border-gray-100 z-50">
                                    
                                    <div v-if="isAdminOrStaff" class="px-4 py-2 mb-2 border-b border-gray-100">
                                        <a :href="route('admin.dashboard')" class="flex items-center gap-2 text-xs font-black text-blue-700 hover:underline">
                                            <LayoutDashboard :size="14"/> IR AL PANEL ADMIN
                                        </a>
                                    </div>
    
                                    <Link :href="route('profile.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Perfil de Cliente</Link>
                                    <Link :href="route('orders.history')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Mis Compras</Link>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cerrar Sesión</Link>
                                </div>
                                <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-40"></div>
                            </div>
    
                            <div v-else class="hidden md:flex items-center gap-2">
                                <button @click="openLogin" class="flex items-center gap-1 px-4 py-2 text-sm font-bold text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition">
                                    <LogIn :size="16" /> Ingresar
                                </button>
                                <button @click="openRegister" class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full text-xs font-black uppercase tracking-wider shadow-lg hover:shadow-blue-600/30 transition transform active:scale-95">
                                    <UserPlus :size="16" /> Regístrate
                                </button>
                            </div>
    
                            <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 text-gray-500">
                                <Menu v-if="!showMobileMenu" :size="24" />
                                <X v-else :size="24" />
                            </button>
                        </div>
                    </div>
                </div>
    
                <div v-if="showMobileMenu" class="md:hidden bg-white border-t border-gray-100 p-4 space-y-4 shadow-lg absolute w-full z-40">
                    <div v-if="!user" class="grid grid-cols-2 gap-4">
                        <button @click="openLogin" class="text-center py-2 border rounded-lg font-bold text-gray-700">Ingresar</button>
                        <button @click="openRegister" class="text-center py-2 bg-blue-600 text-white rounded-lg font-bold">Regístrate</button>
                    </div>
                    <div v-else>
                        <div v-if="isAdminOrStaff" class="mb-2 pb-2 border-b border-gray-100">
                            <a :href="route('admin.dashboard')" class="block py-2 text-blue-700 font-black text-xs">IR AL PANEL ADMIN</a>
                        </div>
                        <Link :href="route('profile.index')" class="block py-2 font-medium">Mi Perfil</Link>
                        <Link :href="route('logout')" method="post" as="button" class="block py-2 text-red-600 font-bold">Salir</Link>
                    </div>
                </div>
            </nav>
    
            <main class="flex-1 relative z-0"><slot /></main>
            
            <footer class="bg-gray-900 text-white py-12 mt-auto relative z-0">
                <div class="max-w-7xl mx-auto px-4 text-center md:text-left"><p class="text-sm text-gray-500">© 2026 BoliviaLogistics.</p></div>
            </footer>
    
            <Toast /> 
    
            <Teleport to="body">
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showLogin || showRegister" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModals"></div>
                        <div v-if="showLogin" class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden z-10">
                            <button @click="closeModals" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-20"><X :size="20" /></button>
                            <LoginForm @close="closeModals" @switchToRegister="openRegister" />
                        </div>
                        <div v-if="showRegister" class="relative bg-white w-full max-w-lg h-[650px] rounded-2xl shadow-2xl overflow-hidden z-10">
                            <button @click="closeModals" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-20"><X :size="20" /></button>
                            <RegisterForm :activeBranches="activeBranches" @close="closeModals" @switchToLogin="openLogin" />
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </template>