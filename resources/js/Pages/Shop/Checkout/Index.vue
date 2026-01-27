<script setup>
import { ref, computed, watch } from 'vue'; // Importar watch
import { useForm, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { MapPin, ShieldCheck, ArrowRight, AlertTriangle, Store, Truck } from 'lucide-vue-next';

// 1. CORRECCIÓN DE PROPS
const props = defineProps({
    cart: Object,
    addresses: Array,
    // Aceptamos String porque tus IDs son UUIDs
    default_address_id: [String, Number] 
});

// 2. VARIABLES DE ESTADO
const deliveryType = ref('pickup'); 

const form = useForm({
    delivery_type: 'pickup',
    address_id: null
});

// 3. COMPUTADAS
const validAddresses = computed(() => {
    return props.addresses.filter(addr => addr.branch_id === props.cart.branch_id);
});

// 4. INICIALIZACIÓN
if (props.default_address_id && validAddresses.value.find(a => a.id === props.default_address_id)) {
    form.address_id = props.default_address_id;
} else if (validAddresses.value.length > 0) {
    form.address_id = validAddresses.value[0].id;
}

// 5. WATCHER (Colocado DESPUÉS de declarar las variables para evitar ReferenceError)
watch(deliveryType, (newVal) => {
    form.delivery_type = newVal;
    
    if (newVal === 'delivery' && !form.address_id && validAddresses.value.length > 0) {
        form.address_id = validAddresses.value[0].id;
    }
    if (newVal === 'pickup') {
        form.address_id = null;
    }
});

const submit = () => {
    form.delivery_type = deliveryType.value;
    
    if (form.delivery_type === 'delivery' && !form.address_id) {
        return alert('Selecciona una dirección para el envío.');
    }
    
    form.post(route('checkout.store'));
};
</script>

<template>
    <ShopLayout>
        <div class="max-w-4xl mx-auto py-10 px-4">
            
            <h1 class="text-3xl font-black text-gray-800 mb-8">Confirmar Pedido</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <Truck class="text-blue-600" /> Método de Entrega
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer border rounded-xl p-4 flex flex-col items-center gap-2 transition-all"
                                   :class="deliveryType === 'pickup' ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 hover:border-blue-300'">
                                <input type="radio" v-model="deliveryType" value="pickup" class="hidden">
                                <Store :size="28" :class="deliveryType === 'pickup' ? 'text-blue-600' : 'text-gray-400'" />
                                <span class="font-bold text-sm text-gray-800">Recojo en Tienda</span>
                                <span class="text-[10px] text-green-600 bg-green-100 px-2 py-0.5 rounded-full font-bold">Gratis</span>
                            </label>

                            <label class="cursor-pointer border rounded-xl p-4 flex flex-col items-center gap-2 transition-all"
                                   :class="deliveryType === 'delivery' ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 hover:border-blue-300'">
                                <input type="radio" v-model="deliveryType" value="delivery" class="hidden">
                                <Truck :size="28" :class="deliveryType === 'delivery' ? 'text-blue-600' : 'text-gray-400'" />
                                <span class="font-bold text-sm text-gray-800">Delivery</span>
                                <span class="text-[10px] text-gray-500">A tu ubicación</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm animate-in fade-in slide-in-from-top-2">
                        
                        <div v-if="deliveryType === 'pickup'">
                            <h2 class="font-bold text-lg mb-2 flex items-center gap-2">
                                <MapPin class="text-blue-600" /> Punto de Recojo
                            </h2>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <p class="font-bold text-gray-800 text-sm">{{ cart.branch_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">Debes pasar a recoger tu pedido en esta sucursal dentro de las próximas 24 horas.</p>
                            </div>
                        </div>

                        <div v-else>
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="font-bold text-lg flex items-center gap-2">
                                    <MapPin class="text-blue-600" /> Dirección de Entrega
                                </h2>
                                <Link :href="route('addresses.create')" class="text-xs font-bold text-blue-600 hover:underline">+ Nueva</Link>
                            </div>

                            <div v-if="validAddresses.length === 0" class="text-center py-4 bg-yellow-50 rounded border border-yellow-200">
                                <p class="text-sm text-yellow-700 font-bold">Sin cobertura guardada.</p>
                                <p class="text-xs text-yellow-600 mt-1">No tienes direcciones registradas para la zona de <strong>{{ cart.branch_name }}</strong>.</p>
                            </div>

                            <div v-else class="space-y-3">
                                <label v-for="addr in validAddresses" :key="addr.id" 
                                    class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition-all hover:border-blue-300"
                                    :class="form.address_id === addr.id ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200'">
                                    <input type="radio" :value="addr.id" v-model="form.address_id" class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="block font-bold text-sm text-gray-800">{{ addr.alias }}</span>
                                        <span class="block text-xs text-gray-500">{{ addr.address }}</span>
                                        <span class="block text-[10px] text-gray-400 mt-1">{{ addr.details }}</span>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.address_id" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.address_id }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h2 class="font-bold text-lg mb-4">Productos ({{ cart.items.length }})</h2>
                        <div class="divide-y divide-gray-100">
                            <div v-for="item in cart.items" :key="item.name" class="py-3 flex justify-between">
                                <div>
                                    <p class="font-medium text-sm">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">Cant: {{ item.quantity }}</p>
                                </div>
                                <p class="font-bold text-sm">Bs {{ item.subtotal.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg sticky top-24">
                        <h3 class="font-black text-gray-800 text-lg mb-4">Resumen</h3>
                        
                        <div class="flex justify-between mb-2 text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-bold">Bs {{ cart.total.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between mb-4 text-sm">
                            <span class="text-gray-500">Envío</span>
                            <span class="font-bold text-green-600" v-if="deliveryType === 'pickup'">GRATIS</span>
                            <span class="font-bold text-blue-600" v-else>Por Calcular</span> 
                        </div>
                        
                        <div class="border-t border-dashed border-gray-300 my-4"></div>
                        
                        <div class="flex justify-between mb-6 text-xl">
                            <span class="font-black text-gray-800">Total</span>
                            <span class="font-black text-blue-600">Bs {{ cart.total.toFixed(2) }}</span>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg flex gap-2 items-start mb-6">
                            <ShieldCheck class="w-5 h-5 text-blue-600 shrink-0" />
                            <p class="text-xs text-blue-800">
                                Al confirmar, tu stock quedará <strong>reservado por 5 minutos</strong> para que realices el pago QR.
                            </p>
                        </div>

                        <button @click="submit" :disabled="form.processing" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-xl flex items-center justify-center gap-2 transition-transform active:scale-95 disabled:opacity-50">
                            <span v-if="form.processing">Procesando...</span>
                            <span v-else class="flex items-center gap-2">Confirmar y Pagar <ArrowRight :size="20"/></span>
                        </button>
                        
                        <div v-if="$page.props.errors.error" class="mt-4 p-3 bg-red-100 text-red-700 text-xs rounded border border-red-200 flex items-center gap-2">
                            <AlertTriangle :size="16"/> {{ $page.props.errors.error }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>