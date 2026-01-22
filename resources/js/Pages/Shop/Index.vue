<script setup>
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { Link, useForm } from '@inertiajs/vue3';
    
    // Recibimos los SKUs paginados desde el Controller
    const props = defineProps({ skus: Object });
    
    // Formulario para "Agregar al Carrito" (Lo usaremos pronto)
    const form = useForm({
        sku_id: null,
        quantity: 1
    });
    
    const addToCart = (sku) => {
        form.sku_id = sku.id;
        form.post(route('cart.add'), {
            preserveScroll: true,
            onSuccess: () => {
                // Opcional: Mostrar notificación toast
                alert('Producto agregado al carrito'); 
            }
        });
    };
    </script>
    
    <script>
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    export default { layout: ShopLayout }
    </script>
    
    <template>
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-2">
                        Catálogo de Productos
                    </h1>
                    <p class="text-lg text-gray-500">
                        Las mejores bebidas, directo a tu negocio o evento.
                    </p>
                </div>
    
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    
                    <div v-for="sku in skus.data" :key="sku.id" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition duration-300 overflow-hidden border border-gray-100 flex flex-col">
                        
                        <div class="h-48 bg-gray-100 relative group">
                            <img :src="sku.image" :alt="sku.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            
                            <span class="absolute top-3 left-3 bg-black/70 text-white text-xs font-bold px-2 py-1 rounded backdrop-blur-sm">
                                {{ sku.brand }}
                            </span>
                        </div>
    
                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-gray-800 leading-tight mb-1">
                                {{ sku.name }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2 flex-1">
                                {{ sku.description }}
                            </p>
    
                            <div class="mt-auto pt-4 border-t border-gray-100">
                                
                                <div v-if="!sku.is_guest" class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-400 uppercase font-bold">Precio</span>
                                        <span v-if="sku.price" class="text-xl font-black text-blue-600">
                                            Bs {{ sku.price.toFixed(2) }}
                                        </span>
                                        <span v-else class="text-sm text-red-500 font-bold">
                                            Sin Stock
                                        </span>
                                    </div>
                                    
                                    <button 
                                        @click="addToCart(sku)"
                                        :disabled="!sku.price || form.processing"
                                        class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg shadow-lg hover:shadow-blue-500/30 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </div>
    
                                <div v-else class="text-center">
                                    <p class="text-sm text-gray-400 mb-2 italic">Inicia sesión para ver precios</p>
                                    <Link :href="route('login')" class="block w-full py-2 px-4 border border-blue-600 text-blue-600 font-bold rounded hover:bg-blue-50 transition text-sm">
                                        Ingresar / Registrarse
                                    </Link>
                                </div>
    
                            </div>
                        </div>
                    </div>
    
                </div>
    
                <div class="mt-12 flex justify-center" v-if="skus.links.length > 3">
                    <div class="flex gap-1 bg-white p-2 rounded-lg shadow-sm">
                        <template v-for="(link, k) in skus.links" :key="k">
                            <Link v-if="link.url" 
                                :href="link.url" 
                                v-html="link.label"
                                class="px-4 py-2 border rounded text-sm font-medium transition"
                                :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-50 border-transparent'"
                            />
                            <span v-else 
                                v-html="link.label"
                                class="px-4 py-2 text-sm text-gray-400 border border-transparent"
                            ></span>
                        </template>
                    </div>
                </div>
    
            </div>
        </div>
    </template>
