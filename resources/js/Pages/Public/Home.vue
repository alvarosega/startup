<script setup>
    import { Head, Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    
    const props = defineProps({
        products: { type: Array, default: () => [] },
        promotions: { type: Array, default: () => [] }
    });
    
    // Usamos usePage para acceder a las props globales de forma segura
    const page = usePage();
    const auth = computed(() => page.props.auth || { user: null });
    const user = computed(() => auth.value.user);
    
    // Verificación segura de roles
    const isAdmin = computed(() => user.value?.roles?.includes('admin') || user.value?.roles?.includes('super_admin'));
    </script>
    
    <template>
        <Head title="Inicio - Bolivia Logistics" />
    
        <div class="min-h-screen bg-gray-50 text-gray-900 font-sans">
            <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
                <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
                    <Link href="/" class="text-2xl font-black text-blue-900 italic">BL</Link>
    
                    <nav class="flex items-center space-x-4">
                        <template v-if="!user">
                            <Link :href="route('login')" class="text-sm font-semibold text-gray-600">Ingresar</Link>
                            <Link :href="route('register')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold">Registro</Link>
                        </template>
    
                        <template v-else>
                            <div class="flex items-center space-x-4">
                                <span v-if="isAdmin" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold uppercase">Admin</span>
                                
                                <Link :href="route('profile.edit')" class="relative">
                                    <div class="w-10 h-10 rounded-full bg-blue-900 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                                        {{ user.first_name ? user.first_name[0].toUpperCase() : '?' }}
                                    </div>
                                    <span v-if="user.profile_incomplete" class="absolute -top-1 -right-1 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border-2 border-white"></span>
                                    </span>
                                </Link>
    
                                <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                </Link>
                            </div>
                        </template>
                    </nav>
                </div>
            </header>
    
            <main class="max-w-7xl mx-auto px-4 py-8">
                <h2 class="text-2xl font-bold mb-6">Catálogo de Bebidas</h2>
                <div v-if="products && products.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="product in products" :key="product.id" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                        <div class="h-32 bg-gray-50 rounded-lg mb-4 flex items-center justify-center text-gray-300">Sin Imagen</div>
                        <h3 class="font-bold text-gray-800">{{ product.name }}</h3>
                        <p class="text-blue-600 font-bold">Bs. {{ product.price }}</p>
                    </div>
                </div>
                <div v-else class="text-center py-20 text-gray-400">
                    <p>No hay productos disponibles en este momento.</p>
                </div>
            </main>
        </div>
    </template>