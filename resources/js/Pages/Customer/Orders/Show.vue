<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Clock, UploadCloud, AlertCircle, CheckCircle2, 
    FileText, Package, Truck, Receipt, ArrowLeft, ChevronRight, Zap
} from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    payment_context: Object,
    delivery_otp: String
});

// --- MOTOR DEL TEMPORIZADOR ---
const timeRemaining = ref(Number(props.payment_context.seconds_remaining));
const isExpired = ref(props.payment_context.is_expired);
let timerInterval = null;

const formattedTime = computed(() => {
    if (timeRemaining.value <= 0) return "00:00";
    const m = Math.floor(timeRemaining.value / 60).toString().padStart(2, '0');
    const s = Math.floor(timeRemaining.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

onMounted(() => {
    // Sincronizado con estado 'pending'
    if (props.order.status === 'pending' && !isExpired.value) {
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
    form.clearErrors(); // Limpiamos errores previos al intentar de nuevo
    const file = e.target.files[0];
    if (!file) return;
    
    // Validación de peso en Frontend antes de gastar ancho de banda
    if (file.size > 5 * 1024 * 1024) {
        form.setError('proof', 'El archivo supera el límite de 5MB permitido.');
        return;
    }
    
    form.proof = file;
};

const submitProof = () => {
    form.post(route('customer.orders.upload-proof', props.order.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        // Si hay errores, forzamos a que el input se resetee para forzar un nuevo intento
        onError: () => {
            form.proof = null;
            if(fileInput.value) fileInput.value.value = null;
        }
    });
};
</script>

<template>
    <ShopLayout>
        <Head :title="'Orden ' + order.code" />
        <div class="container mx-auto px-4 py-8 max-w-6xl mb-20">
            
            <div class="mb-8 flex items-center justify-between">
                <Link :href="route('customer.index')" class="flex items-center gap-2 text-foreground/40 hover:text-primary transition-colors font-black uppercase text-[10px] tracking-widest">
                    <ArrowLeft :size="14" /> Volver a Tienda
                </Link>
                <div class="px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[10px] font-mono text-foreground/50 uppercase tracking-tighter">
                    Sistema Operativo / ID: {{ order.id.substring(0,8) }}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-8 shadow-2xl relative overflow-hidden">
                        <div class="flex justify-between items-start mb-10 relative z-10">
                            <div>
                                <h1 class="text-4xl font-black uppercase italic tracking-tighter text-foreground leading-none">
                                    Orden <span class="text-primary">{{ order.code }}</span>
                                </h1>
                                <p class="text-[10px] text-foreground/40 font-black mt-2 uppercase tracking-[0.3em]">
                                    Logística: {{ order.delivery_type === 'pickup' ? 'Retiro Local' : 'Despacho Directo' }}
                                </p>
                            </div>
                            <div class="px-4 py-2 rounded-xl border font-black uppercase text-[10px] tracking-widest shadow-lg"
                                :class="{
                                    'bg-amber-500/10 text-amber-500 border-amber-500/20 animate-pulse': order.status === 'pending',
                                    'bg-blue-500/10 text-blue-500 border-blue-500/20': order.status === 'payment_pending', // RECTIFICADO
                                    'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': order.status === 'confirmed',
                                    'bg-f1-red/10 text-f1-red border-f1-red/20': isExpired || order.status === 'expired'
                                }">
                                {{ order.status.replace('_', ' ') }}
                            </div>
                        </div>

                        <div v-if="isExpired || order.status === 'expired'" class="bg-f1-red/10 border border-f1-red/20 rounded-3xl p-8 text-center animate-in fade-in zoom-in duration-500">
                            <AlertCircle class="text-f1-red mx-auto mb-4" :size="48" />
                            <h3 class="font-black text-f1-red text-xl uppercase tracking-tighter italic">Reserva Caducada</h3>
                            <p class="text-xs text-foreground/60 mt-2 max-w-xs mx-auto font-bold uppercase">El stock ha sido liberado para otros usuarios. Debes generar un nuevo carrito.</p>
                            <Link :href="route('customer.index')" class="mt-6 inline-flex bg-f1-red text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:opacity-90 transition-all">Reintentar Compra</Link>
                        </div>

                        <div v-else-if="order.status === 'pending'" class="space-y-10">
                            <div class="flex flex-col items-center text-center">
                                <div class="bg-black/40 text-primary text-5xl font-black py-6 px-12 rounded-[40px] font-mono tracking-[0.2em] mb-4 shadow-2xl border border-primary/20 italic">
                                    {{ formattedTime }}
                                </div>
                                <div class="flex items-center gap-2 text-foreground/40">
                                    <Clock :size="14" />
                                    <p class="text-[10px] font-black uppercase tracking-widest">Cronómetro de Seguridad Activo</p>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 items-center bg-black/30 p-10 rounded-[40px] border border-white/5 shadow-inner">
                                <div class="w-48 h-48 bg-white p-3 rounded-[32px] shrink-0 shadow-2xl group transition-transform hover:scale-105 duration-500">
                                    <img :src="payment_context.qr_image" class="w-full h-full object-contain" alt="QR de Pago">
                                </div>
                                
                                <div class="flex-1 space-y-6 w-full text-center md:text-left">
                                    <div>
                                        <p class="text-[9px] font-black text-foreground/30 uppercase mb-2 tracking-[0.2em]">Entidad Recaudadora</p>
                                        <p class="text-sm font-black text-foreground">{{ payment_context.bank_name }}</p>
                                    </div>

                                    <div class="relative group">
                                        <div v-if="form.errors.proof || $page.props.errors.error" class="mb-4 space-y-2">
                                            <div v-if="form.errors.proof" class="p-3 bg-f1-red/10 border border-f1-red/20 rounded-xl flex items-start gap-2">
                                                <AlertCircle class="text-f1-red shrink-0 mt-0.5" :size="14" />
                                                <span class="text-f1-red text-[10px] font-black uppercase text-left">{{ form.errors.proof }}</span>
                                            </div>
                                            <div v-if="$page.props.errors.error" class="p-3 bg-f1-red/10 border border-f1-red/20 rounded-xl flex items-start gap-2">
                                                <AlertCircle class="text-f1-red shrink-0 mt-0.5" :size="14" />
                                                <span class="text-f1-red text-[10px] font-black uppercase text-left">{{ $page.props.errors.error }}</span>
                                            </div>
                                        </div>

                                        <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/*,.pdf">
                                        
                                        <button v-if="!form.proof" @click="$refs.fileInput.click()" 
                                                class="w-full py-6 border-2 border-dashed border-primary/30 rounded-[24px] flex flex-col items-center gap-2 hover:border-primary hover:bg-primary/5 transition-all text-primary bg-black/20 group-hover:shadow-lg shadow-primary/10">
                                            <UploadCloud :size="28" />
                                            <span class="text-[10px] font-black uppercase tracking-widest">Inyectar Comprobante</span>
                                        </button>
                                        
                                        <div v-else class="space-y-3 animate-in slide-in-from-bottom-2">
                                            <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-2xl flex items-center gap-4">
                                                <FileText class="text-emerald-500" :size="20" />
                                                <span class="text-[10px] font-black text-foreground/70 truncate flex-1 uppercase tracking-tighter">{{ form.proof.name }}</span>
                                                <button @click="form.proof = null" class="text-f1-red font-black text-xs">×</button>
                                            </div>
                                            <button @click="submitProof" :disabled="form.processing" 
                                                    class="w-full h-14 bg-primary text-white font-black rounded-2xl uppercase text-[11px] tracking-[0.2em] shadow-xl shadow-primary/30 active:scale-95 transition-all flex items-center justify-center gap-2">
                                                <Zap v-if="!form.processing" :size="16" fill="white" />
                                                {{ form.processing ? 'Sincronizando...' : 'Confirmar Transacción' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="order.status === 'arrived' && delivery_otp" class="bg-primary rounded-[32px] p-12 text-center text-white shadow-[0_20px_50px_rgba(var(--primary-rgb),0.4)] animate-in zoom-in-95 duration-500 relative">
                            <div class="absolute top-4 right-6 opacity-20"><Zap :size="100" /></div>
                            <Truck :size="64" class="mx-auto mb-6" />
                            <h3 class="text-3xl font-black uppercase italic tracking-tighter">Unidad en Destino</h3>
                            <p class="text-sm font-bold opacity-80 mb-10 uppercase tracking-[0.2em]">Dicta el código de validación</p>
                            <div class="bg-white text-black py-8 px-16 rounded-[40px] inline-block shadow-2xl border-4 border-black/10 transition-transform hover:scale-105">
                                <span class="text-7xl font-black font-mono tracking-[0.3em] ml-4 drop-shadow-sm">{{ delivery_otp }}</span>
                            </div>
                        </div>

                        <div v-else class="py-12 text-center space-y-6">
                            <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto border border-white/5 shadow-inner">
                                <Receipt v-if="order.status === 'payment_pending'" :size="40" class="text-blue-500 animate-pulse" />
                                <Package v-if="order.status === 'preparing'" :size="40" class="text-cyan-500" />
                                <Truck v-if="order.status === 'dispatched'" :size="40" class="text-primary animate-bounce" />
                                <CheckCircle2 v-if="order.status === 'completed'" :size="40" class="text-emerald-500" />
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-black uppercase text-foreground italic tracking-tighter">
                                    <template v-if="order.status === 'payment_pending'">Validando Pago</template>
                                    <template v-else-if="order.status === 'preparing'">Preparando Carga</template>
                                    <template v-else-if="order.status === 'dispatched'">En Tránsito</template>
                                    <template v-else-if="order.status === 'completed'">Entregado</template>
                                </h3>
                                <p class="text-[10px] text-foreground/40 max-w-xs mx-auto leading-relaxed font-black uppercase tracking-widest">
                                    <template v-if="order.status === 'under_review'">Tu comprobante está en cola de procesamiento financiero.</template>
                                    <template v-else-if="order.status === 'preparing'">Personal de almacén está consolidando los activos.</template>
                                    <template v-else-if="order.status === 'dispatched'">El repartidor ha abandonado la sucursal.</template>
                                    <template v-else-if="order.status === 'completed'">Operación finalizada satisfactoriamente.</template>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-surface/40 backdrop-blur-xl rounded-[32px] border border-white/10 p-8 shadow-xl">
                        <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-foreground/30 mb-8 flex items-center gap-2 italic">
                            <Receipt :size="14" class="text-primary" /> Manifiesto de Artículos
                        </h2>

                        <div class="space-y-6 mb-12 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="item in order.items" :key="item.name" class="flex items-center gap-4 group">
                                <div class="w-16 h-16 bg-white/5 rounded-2xl border border-white/5 p-2 shrink-0 shadow-inner group-hover:border-primary/30 transition-colors">
                                    <img :src="item.image" class="w-full h-full object-contain filter drop-shadow-hardware" :alt="item.name">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[11px] font-black uppercase text-foreground/80 truncate leading-tight tracking-tighter">{{ item.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-mono font-black text-foreground/20">QTY: {{ item.quantity }}</span>
                                        <span class="text-[9px] font-mono font-black text-primary/60 italic">@ Bs {{ Number(item.price).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-mono font-black text-foreground tracking-tighter">Bs {{ Number(item.subtotal).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-black/40 rounded-3xl p-8 border border-white/5 space-y-4">
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-foreground/30">
                                <span>Subtotal</span>
                                <span class="text-foreground/60 font-mono italic">Bs {{ Number(order.items_subtotal).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-foreground/30">
                                <span>Logística de Envío</span>
                                <span class="text-foreground/60 font-mono italic">Bs {{ Number(order.delivery_fee).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-foreground/30">
                                <span>Fee Operativo</span>
                                <span class="text-foreground/60 font-mono italic">Bs {{ Number(order.service_fee).toFixed(2) }}</span>
                            </div>
                            <div class="h-px bg-white/5 my-4"></div>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-black uppercase tracking-tighter text-primary italic">Importe Total</span>
                                <span class="text-4xl font-black text-primary tracking-tighter font-mono italic drop-shadow-[0_0_10px_rgba(var(--primary-rgb),0.3)]">
                                    <span class="text-sm mr-1 not-italic">Bs</span>{{ Number(order.total_amount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.delivery_type === 'delivery'" class="bg-primary/5 rounded-[32px] border border-primary/20 p-8 shadow-inner">
                        <div class="flex items-center gap-3 mb-4 text-primary">
                            <Truck :size="18" />
                            <h3 class="text-[10px] font-black uppercase tracking-[0.2em]">Punto de Recepción</h3>
                        </div>
                        <p class="text-xs font-bold text-foreground/80 leading-relaxed uppercase tracking-tighter">{{ order.delivery_data?.address_snapshot || 'Ubicación de Perfil Sincronizada' }}</p>
                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--primary-rgb), 0.2); border-radius: 10px; }
.drop-shadow-hardware { filter: drop-shadow(0 4px 4px rgba(0,0,0,0.5)); }
</style>