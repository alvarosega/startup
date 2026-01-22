<script setup>
    import { ref } from 'vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { MapPin, ShieldCheck, ArrowRight, AlertTriangle } from 'lucide-vue-next';
    
    const props = defineProps({
        cart: Object,
        addresses: Array,
        default_address_id: Number
    });
    
    const form = useForm({
        address_id: props.default_address_id || (props.addresses.length > 0 ? props.addresses[0].id : null)
    });
    
    const submit = () => {
        if (!form.address_id) return alert('Selecciona una dirección de entrega');
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
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="font-bold text-lg flex items-center gap-2">
                                    <MapPin class="text-blue-600" /> Dirección de Entrega
                                </h2>
                                <Link :href="route('addresses.create')" class="text-xs font-bold text-blue-600 hover:underline">+ Nueva</Link>
                            </div>
    
                            <div v-if="addresses.length === 0" class="text-center py-4 bg-yellow-50 rounded border border-yellow-200">
                                <p class="text-sm text-yellow-700">No tienes direcciones registradas.</p>
                            </div>
    
                            <div v-else class="space-y-3">
                                <label v-for="addr in addresses" :key="addr.id" 
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
    
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h2 class="font-bold text-lg mb-4">Productos ({{ cart.items.length }})</h2>
                            <div class="divide-y divide-gray-100">
                                <div v-for="item in cart.items" :key="item.sku_id" class="py-3 flex justify-between">
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
                                <span class="font-bold text-green-600">GRATIS</span>
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