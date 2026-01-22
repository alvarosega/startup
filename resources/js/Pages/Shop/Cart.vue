<script setup>
    import { ref, computed } from 'vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { useAuthModal } from '@/Composables/useAuthModal'; // <--- IMPORTACIÓN DEL ESTADO GLOBAL
    import { Trash2, Plus, Minus, ArrowRight, AlertTriangle, ShoppingBag, Loader2, LogIn } from 'lucide-vue-next';
    
    // Props recibidos del Backend (CartController)
    const props = defineProps({
        cartId: Number,
        items: Array,
        total: Number,
        branch_id: Number
    });
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    
    // --- LÓGICA DE NAVEGACIÓN INTELIGENTE ---
    // Usamos el composable para abrir el modal sin recargar la página
    const { openLogin } = useAuthModal();
    
    const proceedToCheckout = () => {
        if (user.value) {
            // Opción A: Usuario Logueado -> Va a pagar
            router.visit(route('checkout.index'));
        } else {
            // Opción B: Invitado -> Abre modal de login (encima del carrito)
            openLogin();
        }
    };
    
    // --- LÓGICA DEL CARRITO (CRUD) ---
    const processingItem = ref(null);
    
    const updateQuantity = (item, newQty) => {
        // 1. Validaciones inmediatas
        if (newQty < 1) return;
        if (newQty > item.max_stock) {
            alert(`Solo hay ${item.max_stock} unidades disponibles.`);
            return;
        }
    
        // 2. Bloqueo de UI
        processingItem.value = item.id;
    
        // 3. Petición al servidor
        router.put(route('cart.update', item.id), { 
            quantity: newQty 
        }, {
            preserveScroll: true, // Mantiene la posición de la pantalla
            onFinish: () => processingItem.value = null,
            onError: (errors) => {
                if (errors.quantity) alert(errors.quantity);
                if (errors.error) alert(errors.error);
            }
        });
    };
    
    const removeItem = (id) => {
        if(!confirm('¿Quitar producto del carrito?')) return;
        router.delete(route('cart.destroy', id), { preserveScroll: true });
    };
    </script>
    
    <template>
        <ShopLayout>
            <Head title="Tu Carrito" />
    
            <div class="max-w-4xl mx-auto py-10 px-4">
                
                <h1 class="text-2xl font-black text-gray-800 mb-8 flex items-center gap-2">
                    <ShoppingBag class="text-blue-600" /> Tu Carrito de Compras
                </h1>
    
                <div v-if="items.length > 0" class="flex flex-col lg:flex-row gap-8">
                    
                    <div class="flex-1 space-y-4">
                        <div v-for="item in items" :key="item.id" 
                             class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex gap-4 items-center relative overflow-hidden transition hover:shadow-md">
                            
                            <div class="w-20 h-20 bg-gray-50 rounded-lg flex items-center justify-center shrink-0">
                                <img v-if="item.image" :src="item.image" class="w-full h-full object-contain p-1" alt="Producto">
                            </div>
    
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-sm">{{ item.product_name }}</h3>
                                        <p class="text-xs text-gray-500">{{ item.name }}</p> </div>
                                    <button @click="removeItem(item.id)" class="text-gray-300 hover:text-red-500 transition" title="Eliminar">
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
    
                                <div class="flex justify-between items-end mt-2">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center border border-gray-200 rounded-lg">
                                            <button @click="updateQuantity(item, item.quantity - 1)" 
                                                    class="p-1 hover:bg-gray-100 text-gray-500 disabled:opacity-50"
                                                    :disabled="item.quantity <= 1 || processingItem === item.id">
                                                <Minus :size="14" />
                                            </button>
                                            
                                            <span class="w-8 text-center text-sm font-bold text-gray-700">
                                                <Loader2 v-if="processingItem === item.id" class="animate-spin mx-auto" :size="14" />
                                                <span v-else>{{ item.quantity }}</span>
                                            </span>
                                            
                                            <button @click="updateQuantity(item, item.quantity + 1)" 
                                                    class="p-1 hover:bg-gray-100 text-gray-500 disabled:opacity-50"
                                                    :disabled="item.quantity >= item.max_stock || processingItem === item.id">
                                                <Plus :size="14" />
                                            </button>
                                        </div>
                                        <span v-if="item.quantity >= item.max_stock" class="text-[10px] text-orange-500 font-bold">Máx. alcanzado</span>
                                    </div>
    
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Unit: Bs {{ parseFloat(item.unit_price).toFixed(2) }}</p>
                                        <p class="text-lg font-black text-blue-900">Bs {{ parseFloat(item.subtotal).toFixed(2) }}</p>
                                    </div>
                                </div>
                            </div>
    
                            <div v-if="item.stock_warning" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
                                <div class="text-center">
                                    <AlertTriangle class="mx-auto text-red-500 mb-1" />
                                    <p class="text-xs font-bold text-red-600">Stock insuficiente</p>
                                    <p class="text-[10px] text-gray-500">Solo quedan {{ item.max_stock }} disponibles</p>
                                    <button @click="updateQuantity(item, item.max_stock)" class="mt-2 text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">
                                        Ajustar al máximo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="lg:w-80 shrink-0">
                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm sticky top-4">
                            <h3 class="font-bold text-gray-800 mb-4">Resumen</h3>
                            
                            <div class="flex justify-between mb-2 text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>Bs {{ total.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between mb-4 text-sm text-gray-600">
                                <span>Impuestos</span>
                                <span>Calculado al pagar</span>
                            </div>
                            
                            <div class="border-t border-gray-100 pt-4 flex justify-between items-center mb-6">
                                <span class="font-black text-lg text-gray-800">Total</span>
                                <span class="font-black text-xl text-blue-600">Bs {{ total.toFixed(2) }}</span>
                            </div>
    
                            <button 
                                @click="proceedToCheckout" 
                                class="w-full flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-3 rounded-lg shadow-lg shadow-yellow-400/30 transition transform active:scale-95"
                            >
                                <span v-if="user" class="flex items-center gap-2">
                                    Procesar Compra <ArrowRight :size="18" />
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <LogIn :size="18" /> Iniciar Sesión para Pagar
                                </span>
                            </button>
    
                            <div class="mt-4 text-center">
                                <Link :href="route('shop.index')" class="text-xs text-gray-500 hover:text-blue-600 underline">
                                    Seguir comprando
                                </Link>
                            </div>
    
                            <p v-if="!user" class="text-[10px] text-center text-gray-400 mt-2">
                                No perderás tu carrito al ingresar.
                            </p>
                        </div>
                    </div>
    
                </div>
    
                <div v-else class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-200">
                    <ShoppingBag class="mx-auto text-gray-300 mb-4" :size="64" />
                    <h2 class="text-xl font-bold text-gray-700">Tu carrito está vacío</h2>
                    <p class="text-gray-500 mb-6">Parece que aún no has elegido tus productos.</p>
                    <Link :href="route('shop.index')" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-full font-bold hover:bg-blue-700 transition">
                        Ir al Catálogo
                    </Link>
                </div>
    
            </div>
        </ShopLayout>
    </template>