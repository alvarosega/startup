<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { useAuthModal } from '@/Composables/useAuthModal';
import { Trash2, Plus, Minus, ArrowRight, AlertTriangle, ShoppingBag, Loader2, XCircle, LogIn } from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    items: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user); // Acceso seguro al usuario

const { openLogin } = useAuthModal();
const processingItem = ref(null);

// COMPUTADA: Detectar si el carrito tiene errores bloqueantes
const hasStockErrors = computed(() => {
    return props.items.some(item => item.quantity > item.max_stock);
});

const proceedToCheckout = () => {
    if (!props.cart) return;
    
    // CASO 1: INVITADO -> Siempre abrir login
    // Permitimos avanzar aunque haya errores visuales, porque al loguearse,
    // el backend hará la fusión y "Auto-Sanación" del stock.
    if (!user.value) {
        openLogin();
        return;
    }

    // CASO 2: USUARIO -> Validar errores antes de ir al checkout
    if (props.cart.is_conflict || hasStockErrors.value) {
        alert('Por favor, corrige los problemas de stock antes de continuar.');
        return; 
    }

    router.visit(route('checkout.index'));
};

const updateQuantity = (item, newQty) => {
    // Permitir bajar a 0 es igual a eliminar, pero aquí restringimos min 1
    if (newQty < 1) return; 
    
    // Si intenta subir más del stock real
    if (newQty > item.max_stock) {
        alert(`Solo hay ${item.max_stock} unidades disponibles.`);
        return;
    }

    processingItem.value = item.id;
    router.put(route('cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        onFinish: () => processingItem.value = null,
    });
};

const removeItem = (id) => {
    if(confirm('¿Quitar producto del carrito?')) {
        router.delete(route('cart.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <ShopLayout>
        <Head title="Tu Carrito" />

        <div class="max-w-4xl mx-auto py-10 px-4">
            <h1 class="text-2xl font-black text-gray-800 mb-8 flex items-center gap-2">
                <ShoppingBag class="text-blue-600" /> Tu Carrito de Compras
            </h1>

            <div v-if="cart?.is_conflict" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <AlertTriangle class="text-red-500 shrink-0" />
                    <div>
                        <h3 class="font-bold text-red-800 text-sm">Conflicto de Sucursal</h3>
                        <p class="text-xs text-red-700 mt-1">
                            Estos productos pertenecen a otra sucursal. Por favor, vacía el carrito o regresa a la ubicación anterior.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="items.length > 0" class="flex flex-col lg:flex-row gap-8">
                
                <div class="flex-1 space-y-4">
                    <div v-for="item in items" :key="item.id" 
                         class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex gap-4 items-center relative overflow-hidden transition hover:shadow-md"
                         :class="{'border-red-300 bg-red-50': item.max_stock < item.quantity}">
                        
                        <div class="w-20 h-20 bg-gray-50 rounded-lg flex items-center justify-center shrink-0">
                            <img :src="item.image || '/images/placeholder.png'" class="w-full h-full object-contain p-1 mix-blend-multiply" alt="Producto">
                        </div>

                        <div class="flex-1 min-w-0 z-20"> 
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-sm line-clamp-1">{{ item.name }}</h3>
                                    <p class="text-xs text-gray-500">Unit: Bs {{ parseFloat(item.unit_price).toFixed(2) }}</p>
                                </div>
                                <button @click="removeItem(item.id)" class="text-gray-400 hover:text-red-500 transition p-1 bg-white rounded-full shadow-sm border border-gray-100">
                                    <Trash2 :size="16" />
                                </button>
                            </div>

                            <div class="flex justify-between items-end mt-3">
                                <div v-if="item.max_stock === 0" class="text-xs font-bold text-red-600 bg-white px-2 py-1 rounded border border-red-200 shadow-sm">
                                    AGOTADO
                                </div>

                                <div v-else class="flex items-center gap-2">
                                    <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50">
                                        <button @click="updateQuantity(item, item.quantity - 1)" 
                                                class="p-1.5 hover:bg-gray-200 text-gray-600 disabled:opacity-50"
                                                :disabled="item.quantity <= 1 || processingItem === item.id">
                                            <Minus :size="14" />
                                        </button>
                                        
                                        <span class="w-8 text-center text-sm font-bold text-gray-800">
                                            <Loader2 v-if="processingItem === item.id" class="animate-spin mx-auto" :size="14" />
                                            <span v-else>{{ item.quantity }}</span>
                                        </span>
                                        
                                        <button @click="updateQuantity(item, item.quantity + 1)" 
                                                class="p-1.5 hover:bg-gray-200 text-gray-600 disabled:opacity-50"
                                                :disabled="item.quantity >= item.max_stock || processingItem === item.id">
                                            <Plus :size="14" />
                                        </button>
                                    </div>
                                    <span v-if="item.quantity >= item.max_stock" class="text-[10px] text-orange-500 font-bold bg-orange-50 px-2 py-0.5 rounded-full">Max</span>
                                </div>

                                <p class="text-lg font-black text-blue-900">Bs {{ parseFloat(item.subtotal).toFixed(2) }}</p>
                            </div>
                        </div>

                        <div v-if="item.stock_error && item.max_stock > 0" class="absolute inset-0 bg-white/90 backdrop-blur-[1px] flex items-center justify-center z-10 rounded-xl">
                            <div class="text-center">
                                <p class="text-xs font-bold text-red-600 mb-2">{{ item.stock_error }}</p>
                                <div class="flex gap-2 justify-center">
                                    <button @click="updateQuantity(item, item.max_stock)" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-full hover:bg-blue-700 font-bold shadow-sm">
                                        Ajustar a {{ item.max_stock }}
                                    </button>
                                    <button @click="removeItem(item.id)" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-full hover:bg-red-200 font-bold flex items-center gap-1 shadow-sm">
                                        <XCircle :size="12"/> Quitar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:w-80 shrink-0">
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm sticky top-24">
                        <h3 class="font-bold text-gray-800 mb-4">Resumen</h3>
                        
                        <div class="flex justify-between mb-2 text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>Bs {{ cart?.total?.toFixed(2) || '0.00' }}</span>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-4 flex justify-between items-center mb-6">
                            <span class="font-black text-lg text-gray-800">Total</span>
                            <span class="font-black text-xl text-blue-600">Bs {{ cart?.total?.toFixed(2) || '0.00' }}</span>
                        </div>

                        <button @click="proceedToCheckout" 
                                :disabled="user && (cart?.is_conflict || hasStockErrors)"
                                class="w-full flex items-center justify-center gap-2 font-bold py-3.5 rounded-xl shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                :class="(user && hasStockErrors) ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-yellow-400 text-blue-900 hover:bg-yellow-500 shadow-yellow-400/30'">
                            
                            <span v-if="user && hasStockErrors" class="flex items-center gap-2">
                                <AlertTriangle :size="18" /> Revisar Stock
                            </span>
                            
                            <span v-else class="flex items-center gap-2">
                                <template v-if="!user">
                                    <LogIn :size="18" /> Iniciar Sesión para Pagar
                                </template>
                                <template v-else>
                                    Procesar Compra <ArrowRight :size="18" />
                                </template>
                            </span>
                        </button>

                        <p v-if="user && hasStockErrors" class="text-xs text-red-500 text-center mt-3 font-bold">
                            Algunos productos no tienen stock suficiente. <br> Elimínalos o ajusta la cantidad.
                        </p>
                        
                        <p v-if="!user" class="text-[10px] text-gray-400 text-center mt-2">
                            No te preocupes, tus productos se guardarán al ingresar.
                        </p>

                        <div class="mt-4 text-center">
                            <Link :href="route('shop.index')" class="text-xs text-gray-500 hover:text-blue-600 underline">
                                Seguir comprando
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
            
            <div v-else class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-200">
                <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <ShoppingBag class="text-gray-300" :size="32" />
                </div>
                <h2 class="text-xl font-bold text-gray-700">Tu carrito está vacío</h2>
                <Link :href="route('shop.index')" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-full font-bold hover:bg-blue-700 transition mt-4">
                    Ir al Catálogo
                </Link>
            </div>

        </div>
    </ShopLayout>
</template>