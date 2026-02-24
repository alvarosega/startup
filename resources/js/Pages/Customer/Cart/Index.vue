<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Trash2, Plus, Minus, ArrowRight, AlertTriangle, ShoppingBag, Loader2, PackageOpen, CreditCard } from 'lucide-vue-next';

const props = defineProps({
    cart: Object // Viene del CartResource: { id, items, total_price, total_items, ... }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const processingItem = ref(null);

// MODIFICACIÓN QUIRÚRGICA EN EL SCRIPT
const cartItems = computed(() => {
    if (!props.cart?.items) return [];
    // Soporte para ambos casos: array plano o envuelto en .data
    return Array.isArray(props.cart.items) ? props.cart.items : props.cart.items.data;
});

const hasStockErrors = computed(() => {
    return cartItems.value.some(item => item.quantity > item.max_stock);
});

// Aseguramos que la URL siempre tenga el guest_id si no hay usuario
onMounted(() => {
    const guestId = localStorage.getItem('guest_client_uuid');
    if (!user.value && guestId && !new URLSearchParams(window.location.search).has('guest_id')) {
        router.reload({ data: { guest_id: guestId } });
    }
});

const updateQuantity = (item, newQty) => {
    if (newQty < 1 || newQty > item.max_stock) return;
    processingItem.value = item.id;
    router.patch(route('customer.cart.update', item.id), { quantity: newQty }, {
        preserveScroll: true,
        onFinish: () => processingItem.value = null,
    });
};

const removeItem = (id) => {
    if(confirm('¿Quitar producto?')) {
        router.delete(route('customer.cart.remove', id), { preserveScroll: true });
    }
};

const proceedToCheckout = () => {
    if (!user.value) { router.visit(route('login')); return; }
    router.visit(route('customer.checkout.index'));
};
</script>

<template>
    <ShopLayout>
        <Head title="Tu Carrito" />

        <div class="container mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-black uppercase italic">Tu <span class="text-primary">Carga</span></h1>
                <p class="font-mono text-xs text-muted-foreground" v-if="cart">
                    {{ cart.total_items }} ITEMS / ID: {{ cart.id?.substring(0,8) || '---' }}
                </p>
            </div>

            <div v-if="cartItems.length > 0" class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="item in cartItems" :key="item.id" 
                         class="bg-card border rounded-2xl p-4 flex gap-4 transition-all"
                         :class="item.quantity > item.max_stock ? 'border-error/50 bg-error/5' : 'border-border'">
                        
                        <img :src="item.image" class="w-24 h-24 object-contain bg-white rounded-xl p-2">
                        
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-sm leading-tight">{{ item.name }}</h3>
                                <button @click="removeItem(item.id)" class="text-muted-foreground hover:text-error"><Trash2 :size="18"/></button>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center bg-muted rounded-lg p-1">
                                    <button @click="updateQuantity(item, item.quantity - 1)" :disabled="processingItem === item.id" class="p-1"><Minus :size="14"/></button>
                                    <span class="w-10 text-center font-black">
                                        <Loader2 v-if="processingItem === item.id" class="animate-spin inline" :size="14"/>
                                        <span v-else>{{ item.quantity }}</span>
                                    </span>
                                    <button @click="updateQuantity(item, item.quantity + 1)" :disabled="processingItem === item.id" class="p-1"><Plus :size="14"/></button>
                                </div>
                                <span class="font-black text-lg">Bs {{ item.subtotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-card border rounded-3xl p-6 sticky top-24">
                        <h2 class="font-black uppercase mb-6 flex items-center gap-2"><CreditCard :size="20" class="text-primary"/> Resumen</h2>
                        <div class="flex justify-between text-2xl font-black mb-8">
                            <span>TOTAL</span>
                            <span class="text-primary italic">Bs {{ cart.total_price.toFixed(2) }}</span>
                        </div>
                        <button @click="proceedToCheckout" :disabled="hasStockErrors" class="w-full py-4 bg-primary text-white font-black uppercase rounded-xl flex items-center justify-center gap-2">
                            {{ user ? 'Procesar Pago' : 'Iniciar Sesión' }} <ArrowRight :size="18"/>
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-card rounded-[40px] border border-dashed border-border">
                <PackageOpen :size="64" class="mx-auto text-muted-foreground mb-4" stroke-width="1"/>
                <h2 class="text-xl font-black uppercase mb-2">Carrito Vacío</h2>
                <Link :href="route('customer.shop.index')" class="text-primary font-bold hover:underline">Volver a la tienda</Link>
            </div>
        </div>
    </ShopLayout>
</template>