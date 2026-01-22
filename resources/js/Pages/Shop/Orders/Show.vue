<script setup>
    import { ref, computed } from 'vue';
    import { Head, useForm, router } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { 
        CheckCircle2, Clock, Upload, XCircle, 
        Truck, Package, AlertCircle, FileText 
    } from 'lucide-vue-next';
    
    const props = defineProps({
        order: Object,
        proofUrl: String
    });
    
    // Formulario para subir la foto
    const form = useForm({
        proof: null
    });
    
    const fileInput = ref(null);
    const previewUrl = ref(null);
    
    const handleFile = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.proof = file;
            previewUrl.value = URL.createObjectURL(file);
        }
    };
    
    const uploadProof = () => {
        form.post(route('checkout.upload', props.order.id), {
            preserveScroll: true,
            onSuccess: () => { previewUrl.value = null; }
        });
    };
    
    // Mapa de estados para UI
    const statuses = {
        pending_proof: { label: 'Esperando Pago', color: 'bg-yellow-100 text-yellow-700', icon: Clock },
        review:        { label: 'Verificando',    color: 'bg-blue-100 text-blue-700',     icon: FileText },
        confirmed:     { label: 'Preparando',     color: 'bg-indigo-100 text-indigo-700', icon: Package },
        dispatched:    { label: 'En Camino',      color: 'bg-purple-100 text-purple-700', icon: Truck },
        completed:     { label: 'Entregado',      color: 'bg-green-100 text-green-700',   icon: CheckCircle2 },
        cancelled:     { label: 'Cancelado',      color: 'bg-red-100 text-red-700',       icon: XCircle },
    };
    
    const currentStatus = computed(() => statuses[props.order.status]);
    </script>
    
    <template>
        <ShopLayout>
            <Head :title="`Orden #${order.code}`" />
    
            <div class="max-w-4xl mx-auto py-10 px-4">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                            Orden #{{ order.code }}
                        </h1>
                        <p class="text-sm text-gray-500">Realizada el {{ new Date(order.created_at).toLocaleDateString() }}</p>
                    </div>
                    <div :class="`px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm ${currentStatus.color}`">
                        <component :is="currentStatus.icon" :size="18" />
                        {{ currentStatus.label }}
                    </div>
                </div>
    
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-100 font-bold text-gray-700 text-sm">Productos</div>
                            <div class="divide-y divide-gray-100">
                                <div v-for="item in order.items" :key="item.id" class="p-4 flex gap-4">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800 text-sm">{{ item.sku.product?.name }}</p>
                                        <p class="text-xs text-gray-500">{{ item.sku.name }} x {{ item.quantity }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800 text-sm">Bs {{ item.subtotal }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                                <span class="font-bold text-gray-700">Total a Pagar</span>
                                <span class="text-xl font-black text-blue-600">Bs {{ order.total_amount }}</span>
                            </div>
                        </div>
    
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                            <h3 class="font-bold text-gray-800 mb-2">Datos de Entrega</h3>
                            <p class="text-sm text-gray-600"><strong>Dirección:</strong> {{ order.delivery_data.address }}</p>
                            <p class="text-sm text-gray-600"><strong>Referencia:</strong> {{ order.delivery_data.details || 'Sin referencia' }}</p>
                        </div>
                    </div>
    
                    <div class="space-y-6">
                        
                        <div v-if="order.status === 'pending_proof'" class="bg-white rounded-xl border border-blue-100 shadow-lg p-6 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>
                            
                            <h3 class="font-black text-gray-800 mb-4 text-center">¡Último paso!</h3>
                            
                            <div class="bg-gray-100 rounded-lg p-4 mb-4 flex justify-center">
                                <img src="/assets/qr_placeholder.png" class="w-48 h-48 object-contain mix-blend-multiply" alt="QR Pago">
                            </div>
    
                            <p class="text-xs text-gray-500 text-center mb-6">
                                Escanea el QR, realiza el pago por <strong>Bs {{ order.total_amount }}</strong> y sube la captura aquí.
                            </p>
    
                            <form @submit.prevent="uploadProof">
                                <div class="mb-4">
                                    <label class="block w-full border-2 border-dashed border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition relative">
                                        <input type="file" ref="fileInput" @change="handleFile" class="hidden" accept="image/*">
                                        
                                        <div v-if="previewUrl" class="relative">
                                            <img :src="previewUrl" class="h-32 mx-auto rounded-lg shadow-sm object-contain">
                                            <button type="button" @click.prevent="previewUrl = null; form.proof = null" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1"><XCircle :size="16"/></button>
                                        </div>
                                        
                                        <div v-else class="text-gray-400">
                                            <Upload class="mx-auto mb-2" />
                                            <span class="text-xs font-bold uppercase">Subir Comprobante</span>
                                        </div>
                                    </label>
                                    <p v-if="form.errors.proof" class="text-red-500 text-[10px] mt-1">{{ form.errors.proof }}</p>
                                </div>
    
                                <BaseButton class="w-full" :isLoading="form.processing" :disabled="!form.proof">
                                    Enviar Comprobante
                                </BaseButton>
                            </form>
                        </div>
    
                        <div v-else-if="order.status === 'review'" class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                            <Clock class="w-12 h-12 text-blue-500 mx-auto mb-2 animate-pulse" />
                            <h3 class="font-bold text-blue-800">Verificando Pago</h3>
                            <p class="text-xs text-blue-600 mt-1">Estamos revisando tu comprobante. Te notificaremos pronto.</p>
                            <div v-if="proofUrl" class="mt-4">
                                <p class="text-[10px] font-bold uppercase text-blue-400 mb-1">Tu comprobante:</p>
                                <a :href="proofUrl" target="_blank" class="block w-20 h-20 mx-auto rounded border border-blue-200 overflow-hidden">
                                    <img :src="proofUrl" class="w-full h-full object-cover">
                                </a>
                            </div>
                        </div>
    
                        <div v-if="order.status === 'cancelled'" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                            <AlertCircle class="w-12 h-12 text-red-500 mx-auto mb-2" />
                            <h3 class="font-bold text-red-800">Orden Cancelada</h3>
                            <p class="text-xs text-red-600 mt-1">{{ order.rejection_reason || 'El tiempo de reserva expiró.' }}</p>
                        </div>
    
                    </div>
                </div>
            </div>
        </ShopLayout>
    </template>