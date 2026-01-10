<script setup>
    import { Link, usePage } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const cartCount = computed(() => page.props.cart_count || 0); // Esto lo mandaremos desde el Middleware HandleInertiaRequests
    
    const showingNavigationDropdown = ref(false);
    </script>
    
    <template>
        <div class="min-h-screen bg-gray-50 text-gray-800">
            
            <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex">
                            <Link :href="route('shop.index')" class="shrink-0 flex items-center font-black text-2xl tracking-tighter italic">
                                <span class="text-blue-600">BOLIVIA</span>LOGISTICS
                            </Link>
                        </div>
    
                        <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                            
                            <Link :href="route('cart.index')" class="relative p-2 text-gray-500 hover:text-blue-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span v-if="cartCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                                    {{ cartCount }}
                                </span>
                            </Link>
    
                            <div v-if="user" class="relative ml-3 group">
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                    <div>{{ user.first_name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                                <div class="absolute right-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border border-gray-100">
                                    <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</Link>
                                    <Link :href="route('orders.history')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mis Pedidos</Link>
                                    <div class="border-t border-gray-100"></div>
                                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Cerrar Sesión</Link>
                                </div>
                            </div>
    
                            <template v-else>
                                <Link :href="route('login')" class="text-sm text-gray-700 font-bold hover:text-blue-600">Ingresar</Link>
                                <Link :href="route('register')" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded text-sm font-bold shadow transition">Registrarse</Link>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>
    
            <main>
                <slot />
            </main>
    
            <footer class="bg-gray-900 text-white py-12 mt-12">
                <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                    <div>
                        <h3 class="font-bold text-lg mb-4">BoliviaLogistics</h3>
                        <p class="text-gray-400">Tu socio estratégico en distribución de bebidas.</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-4">Enlaces</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white">Catálogo</a></li>
                            <li><a href="#" class="hover:text-white">Zona de Cobertura</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-4">Contacto</h3>
                        <p class="text-gray-400">Soporte 24/7</p>
                    </div>
                </div>
            </footer>
        </div>
    </template>