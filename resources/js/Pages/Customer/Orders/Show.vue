<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Clock, QrCode, UploadCloud, AlertCircle, CheckCircle2, 
    FileText, Package, Truck, Receipt, ArrowLeft, ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    order: Object,           // Filtrado por GetCustomerOrderDetailAction
    payment_context: Object, // seconds_remaining, is_expired, qr_payload...
    delivery_otp: String     // PIN de 4 dígitos
});

// --- MOTOR DEL TEMPORIZADOR (Sync con Servidor) ---
const timeRemaining = ref(props.payment_context.seconds_remaining);
const isExpired = ref(props.payment_context.is_expired);
let timerInterval = null;

const formattedTime = computed(() => {
    // Forzamos a entero para evitar los "15 dígitos" (floats de JS)
    const totalSeconds = Math.floor(timeRemaining.value);
    if (totalSeconds <= 0) return "00:00";

    const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const s = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});
onMounted(() => {
    if (props.order.status === 'pending_payment' && !isExpired.value) {
        timerInterval = setInterval(() => {
            if (timeRemaining.value > 0) {
                timeRemaining.value--;
            } else {
                clearInterval(timerInterval);
                isExpired.value = true;
                router.reload({ preserveScroll: true }); 
            }
        }, 1000);
    }
});

onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });

// --- GESTIÓN DE COMPROBANTE ---
const fileInput = ref(null);
const form = useForm({ proof: null });

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) return alert("Máximo 5MB");
    form.proof = file;
};

const submitProof = () => {
    form.post(route('customer.orders.upload-proof', props.order.id), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 max-w-5xl mb-20">
            
            <div class="mb-8 flex items-center justify-between">
                <Link :href="route('customer.shop.index')" class="flex items-center gap-2 text-foreground/40 hover:text-primary transition-colors font-black uppercase text-[10px] tracking-widest">
                    <ArrowLeft :size="14" /> Volver a Tienda
                </Link>
                <div class="px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[10px] font-mono text-foreground/50">
                    ID: {{ order.id.substring(0,8) }}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-7 space-y-6">
                    
                    <div class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="text-3xl font-black uppercase italic tracking-tighter text-foreground">
                                    Orden <span class="text-primary">{{ order.code }}</span>
                                </h1>
                                <p class="text-xs text-foreground/40 font-bold mt-1 uppercase">{{ order.delivery_type === 'pickup' ? 'Retiro en Tienda' : 'Envío a Domicilio' }}</p>
                            </div>
                            <div class="px-4 py-2 rounded-2xl border font-black uppercase text-[10px] tracking-widest"
                                 :class="{
                                     'bg-warning/10 text-warning border-warning/20': order.status === 'pending_payment',
                                     'bg-primary/10 text-primary border-primary/20 animate-pulse': order.status === 'under_review',
                                     'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': order.status === 'completed',
                                     'bg-f1-red/10 text-f1-red border-f1-red/20': isExpired || order.status === 'expired'
                                 }">
                                {{ order.status.replace('_', ' ') }}
                            </div>
                        </div>

                        <div v-if="isExpired" class="bg-f1-red/10 border border-f1-red/20 rounded-2xl p-6 text-center">
                            <AlertCircle class="text-f1-red mx-auto mb-3" :size="32" />
                            <h3 class="font-black text-f1-red uppercase tracking-tight">Tiempo Agotado</h3>
                            <p class="text-xs text-foreground/60 mt-1">La reserva de stock ha expirado. Debes iniciar un nuevo pedido.</p>
                        </div>

                        <div v-else-if="order.status === 'pending_payment'" class="space-y-8">
                            <div class="flex flex-col items-center text-center">
                                <div class="bg-primary/10 text-primary text-4xl font-black py-4 px-10 rounded-3xl font-mono tracking-[0.2em] mb-4 shadow-inner border border-primary/20">
                                    {{ formattedTime }}
                                </div>
                                <p class="text-xs font-bold text-foreground/60 uppercase tracking-widest">Tiempo para pagar y asegurar stock</p>
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 items-center bg-black/20 p-8 rounded-[32px] border border-white/5">
                                <div class="w-40 h-40 bg-white p-4 rounded-3xl shrink-0 shadow-2xl relative group">
                                    <QrCode :size="144" class="text-black opacity-20" />
                                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                                        <span class="text-[10px] font-black text-black text-center uppercase leading-none mb-1">Escanear para pagar</span>
                                        <span class="text-[14px] font-mono font-black text-primary">Bs {{ order.total_amount.toFixed(2) }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex-1 space-y-4 w-full">
                                    <div class="bg-background/50 p-4 rounded-2xl border border-white/5">
                                        <p class="text-[9px] font-black text-foreground/30 uppercase mb-1 tracking-widest">Referencia de Pago</p>
                                        <p class="text-xs font-mono font-bold text-primary break-all">{{ payment_context.qr_payload }}</p>
                                    </div>

                                    <div class="relative">
                                        <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/*,.pdf">
                                        <button v-if="!form.proof" @click="$refs.fileInput.click()" 
                                                class="w-full py-4 border-2 border-dashed border-primary/30 rounded-2xl flex flex-col items-center gap-1 hover:border-primary hover:bg-primary/5 transition-all text-primary">
                                            <UploadCloud :size="24" />
                                            <span class="text-[10px] font-black uppercase">Subir Comprobante</span>
                                        </button>
                                        
                                        <div v-else class="flex flex-col gap-2">
                                            <div class="bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-2xl flex items-center gap-3">
                                                <FileText class="text-emerald-500" :size="18" />
                                                <span class="text-[10px] font-black text-foreground/70 truncate flex-1">{{ form.proof.name }}</span>
                                                <button @click="form.proof = null" class="text-f1-red font-black text-[10px] uppercase">X</button>
                                            </div>
                                            <button @click="submitProof" :disabled="form.processing" 
                                                    class="w-full h-12 bg-primary text-white font-black rounded-xl uppercase text-[10px] tracking-[0.2em] shadow-lg shadow-primary/20 active:scale-95 transition-all">
                                                {{ form.processing ? 'Enviando...' : 'Confirmar Envío' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="order.status === 'arrived' && delivery_otp" class="bg-primary rounded-[32px] p-10 text-center text-white shadow-2xl animate-in zoom-in duration-500">
                            <Truck :size="48" class="mx-auto mb-4" />
                            <h3 class="text-2xl font-black uppercase italic tracking-tighter">¡Tu pedido llegó!</h3>
                            <p class="text-sm font-bold opacity-80 mb-8 uppercase tracking-widest">Dicta este código al conductor</p>
                            <div class="bg-white text-black py-6 px-12 rounded-3xl inline-block shadow-2xl">
                                <span class="text-6xl font-black font-mono tracking-[0.3em] ml-4">{{ delivery_otp }}</span>
                            </div>
                        </div>

                        <div v-else class="py-10 text-center">
                            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 border border-white/5">
                                <Package v-if="order.status === 'preparing'" :size="32" class="text-primary" />
                                <Truck v-if="order.status === 'dispatched'" :size="32" class="text-primary" />
                                <CheckCircle2 v-if="order.status === 'completed'" :size="32" class="text-emerald-500" />
                            </div>
                            <h3 class="text-xl font-black uppercase text-foreground mb-2">
                                <template v-if="order.status === 'under_review'">Validando Pago...</template>
                                <template v-else-if="order.status === 'preparing'">Preparando tu Carga</template>
                                <template v-else-if="order.status === 'dispatched'">Pedido en Camino</template>
                                <template v-else-if="order.status === 'completed'">¡Pedido Entregado!</template>
                            </h3>
                            <p class="text-sm text-foreground/40 max-w-xs mx-auto leading-relaxed">
                                <template v-if="order.status === 'under_review'">Tu comprobante está en manos de nuestro equipo de finanzas.</template>
                                <template v-else-if="order.status === 'preparing'">La sucursal está alistando los productos con máxima prioridad.</template>
                                <template v-else-if="order.status === 'dispatched'">Nuestro repartidor se dirige a tu ubicación predeterminada.</template>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-8">
                        <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground/40 mb-8 flex items-center gap-2">
                            <Receipt :size="14" /> Detalle del Pedido
                        </h2>

                        <div class="space-y-6 mb-10">
                            <div v-for="item in order.items" :key="item.name" class="flex items-center gap-4 group">
                                <div class="w-14 h-14 bg-white/5 rounded-2xl border border-white/5 p-1 shrink-0">
                                    <img :src="item.image" class="w-full h-full object-contain">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-black uppercase text-foreground/80 truncate leading-tight">{{ item.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-mono font-bold text-foreground/30">x{{ item.quantity }}</span>
                                        <span class="text-[10px] font-mono font-black text-primary">Bs {{ item.price.toFixed(2) }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-mono font-black text-foreground">Bs {{ item.subtotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-black/20 rounded-3xl p-6 border border-white/5 space-y-4">
                            <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-foreground/40">
                                <span>Subtotal Productos</span>
                                <span class="text-foreground/60">Bs {{ order.items_subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-foreground/40">
                                <span>Costo de Envío</span>
                                <span class="text-foreground/60">Bs {{ order.delivery_fee.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest text-foreground/40">
                                <span>Fee de Gestión</span>
                                <span class="text-foreground/60">Bs {{ order.service_fee.toFixed(2) }}</span>
                            </div>
                            <div class="h-px bg-white/5 my-2"></div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black uppercase tracking-tighter text-primary">Total Final</span>
                                <span class="text-3xl font-black text-primary tracking-tighter font-mono italic">
                                    Bs {{ order.total_amount.toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.delivery_type === 'delivery'" class="bg-primary/5 rounded-[32px] border border-primary/20 p-8">
                        <div class="flex items-center gap-3 mb-4">
                            <MapPin class="text-primary" :size="20" />
                            <h3 class="text-xs font-black uppercase tracking-widest text-primary">Punto de Entrega</h3>
                        </div>
                        <p class="text-sm font-bold text-foreground/80 leading-relaxed">{{ order.delivery_data?.address_snapshot || 'Ubicación seleccionada' }}</p>
                        <p v-if="order.delivery_data?.reference" class="text-[10px] text-foreground/40 mt-2 italic">Ref: {{ order.delivery_data.reference }}</p>
                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.font-display { font-family: 'Inter', sans-serif; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>