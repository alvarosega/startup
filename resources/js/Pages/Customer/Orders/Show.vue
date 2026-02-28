<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { 
    Clock, QrCode, UploadCloud, AlertCircle, CheckCircle2, 
    FileText, Package, Truck, Store, Receipt, Split, Check
} from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    payment_context: Object
});

// --- LÓGICA DEL TEMPORIZADOR (Solo para el Anticipo/Total) ---
const timeRemaining = ref(props.payment_context.seconds_remaining);
const isExpired = ref(props.payment_context.is_expired || props.order.status === 'expired');
let timerInterval = null;

const formattedTime = computed(() => {
    if (timeRemaining.value <= 0) return "00:00";
    const m = Math.floor(timeRemaining.value / 60).toString().padStart(2, '0');
    const s = (timeRemaining.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

onMounted(() => {
    // El temporizador solo corre si estamos esperando el PAGO INICIAL
    if (props.order.status === 'pending_payment' && props.order.advance_status === 'pending' && !isExpired.value) {
        timerInterval = setInterval(() => {
            if (timeRemaining.value > 0) {
                timeRemaining.value--;
            } else {
                clearInterval(timerInterval);
                isExpired.value = true;
                router.reload(); 
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// --- LÓGICA DE SUBIDA DE COMPROBANTES (Dual) ---
const fileInput = ref(null);
const filePreview = ref(null);

const form = useForm({
    proof: null,
    type: null // 'advance' o 'balance'
});

const handleFileUpload = (e, type) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
        alert("Solo se permiten imágenes (JPG/PNG) o PDF.");
        e.target.value = '';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert("El archivo es demasiado grande (Máximo 2MB).");
        e.target.value = '';
        return;
    }

    form.proof = file;
    form.type = type; // Inyectamos el tipo financieramente correcto
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => filePreview.value = e.target.result;
        reader.readAsDataURL(file);
    } else {
        filePreview.value = null; 
    }
};

const submitProof = () => {
    form.post(route('customer.orders.upload-proof', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            filePreview.value = null;
        }
    });
};

const cancelUpload = () => {
    if(fileInput.value) fileInput.value.value = '';
    form.proof = null;
    form.type = null;
    filePreview.value = null;
};
</script>

<template>
    <ShopLayout>
        <div class="container mx-auto px-4 py-8 pb-32 lg:pb-12 max-w-5xl">
            
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="font-display font-black text-3xl text-foreground uppercase tracking-tight">
                        Orden <span class="text-primary">{{ order.code }}</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1 font-medium flex items-center gap-2">
                        Realizada el: {{ new Date(order.created_at).toLocaleString() }}
                        <span v-if="order.payment_type === 'partial'" class="px-2 py-0.5 bg-warning/10 text-warning text-[10px] uppercase font-black rounded tracking-wider">
                            Pago Fraccionado
                        </span>
                        <span v-else class="px-2 py-0.5 bg-success/10 text-success text-[10px] uppercase font-black rounded tracking-wider">
                            Pago Total
                        </span>
                    </p>
                </div>
                
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold uppercase tracking-wider text-xs border"
                    :class="{
                        'bg-warning/10 text-warning border-warning/20': order.status === 'pending_payment',
                        'bg-primary/10 text-primary border-primary/20': order.status === 'preparing',
                        'bg-purple-500/10 text-purple-500 border-purple-500/20': order.status === 'dispatched',
                        'bg-success/10 text-success border-success/20': order.status === 'completed',
                        'bg-destructive/10 text-destructive border-destructive/20': isExpired || order.status === 'expired' || order.status === 'cancelled'
                    }">
                    <Clock v-if="order.status === 'pending_payment'" :size="16" />
                    <Package v-else-if="order.status === 'preparing'" :size="16" />
                    <Truck v-else-if="order.status === 'dispatched'" :size="16" />
                    <CheckCircle2 v-else-if="order.status === 'completed'" :size="16" />
                    <AlertCircle v-else :size="16" />
                    
                    <span>
                        <template v-if="order.status === 'pending_payment'">Pendiente de Pago</template>
                        <template v-else-if="order.status === 'preparing'">Preparando Pedido</template>
                        <template v-else-if="order.status === 'dispatched'">En Tránsito</template>
                        <template v-else-if="order.status === 'completed'">Completado</template>
                        <template v-else-if="isExpired || order.status === 'expired'">Expirada</template>
                        <template v-else>{{ order.status }}</template>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div v-if="isExpired || order.status === 'expired' || order.status === 'cancelled'" class="bg-destructive/5 border border-destructive/20 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <AlertCircle :size="32" class="text-destructive" />
                        </div>
                        <h3 class="font-black text-xl text-destructive mb-2">Orden Cancelada</h3>
                        <p class="text-sm text-destructive/80 mb-6">
                            Esta orden ha sido cancelada o el tiempo de reserva expiró. El stock fue liberado.
                        </p>
                        <Link :href="route('customer.shop.index')" class="btn btn-outline border-destructive/30 text-destructive hover:bg-destructive hover:text-white rounded-xl">
                            Volver a la Tienda
                        </Link>
                    </div>

                    <template v-else>
                        
                        <div v-if="order.advance_status === 'pending'" class="bg-card border-2 border-primary/30 rounded-3xl p-6 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 bg-primary text-primary-foreground text-xs font-black px-4 py-1 rounded-bl-xl uppercase tracking-wider">
                                Paso 1 Requerido
                            </div>
                            
                            <div class="text-center mb-6 pt-4">
                                <div class="bg-primary/10 text-primary text-3xl font-black py-4 px-8 rounded-2xl inline-block font-mono tracking-widest shadow-inner mb-4">
                                    {{ formattedTime }}
                                </div>
                                <h3 class="font-black text-xl text-foreground">
                                    {{ order.payment_type === 'total' ? 'Pago Total Requerido' : 'Anticipo Requerido' }}
                                </h3>
                                <p class="text-sm text-muted-foreground mt-2 max-w-md mx-auto">
                                    Transfiere <strong>Bs {{ parseFloat(order.advance_amount).toFixed(2) }}</strong> escaneando el QR para asegurar tu pedido antes de que el tiempo expire.
                                </p>
                            </div>

                            <div class="flex flex-col md:flex-row gap-6 items-center bg-muted/20 p-6 rounded-2xl border border-border">
                                <div class="bg-white p-3 rounded-2xl border-2 border-dashed border-muted shrink-0 relative">
                                    <QrCode :size="120" class="text-muted-foreground opacity-50" />
                                    <p class="absolute inset-0 flex items-center justify-center font-bold text-[10px] text-muted-foreground uppercase text-center px-2">QR QR Simple</p>
                                </div>
                                
                                <div class="flex-1 w-full space-y-4">
                                    <p class="text-[11px] font-mono bg-background border border-border p-3 rounded-xl text-muted-foreground break-all">
                                        {{ payment_context.bank_details }}
                                    </p>

                                    <input type="file" ref="fileInput" @change="(e) => handleFileUpload(e, 'advance')" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                                    
                                    <div v-if="!form.proof || form.type !== 'advance'" @click="$refs.fileInput.click()" class="border-2 border-dashed border-primary/50 hover:border-primary bg-primary/5 rounded-xl p-4 cursor-pointer transition-colors group text-center">
                                        <UploadCloud class="mx-auto text-primary mb-1 group-hover:scale-110 transition-transform" :size="24" />
                                        <span class="text-xs font-bold text-primary">Subir Comprobante (Paso 1)</span>
                                    </div>
                                    
                                    <div v-else class="border border-border rounded-xl p-2 bg-background flex flex-col gap-2">
                                        <div class="flex items-center gap-3 bg-muted/30 p-2 rounded-lg">
                                            <FileText class="text-primary" :size="20"/>
                                            <span class="text-xs font-bold truncate flex-1">{{ form.proof.name }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button @click="cancelUpload" class="btn btn-ghost btn-sm flex-1 text-xs">Cancelar</button>
                                            <button @click="submitProof" :disabled="form.processing" class="btn btn-primary btn-sm flex-1 text-xs shadow-md">
                                                {{ form.processing ? 'Enviando...' : 'Confirmar Pago' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="order.advance_status === 'under_review'" class="bg-card border border-border rounded-3xl p-6 flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center shrink-0 animate-pulse">
                                <Receipt :size="20" class="text-primary" />
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-foreground">Validando Pago 1 (Bs {{ parseFloat(order.advance_amount).toFixed(2) }})</h3>
                                <p class="text-xs text-muted-foreground">Un administrador está revisando tu comprobante. Por favor, espera.</p>
                            </div>
                        </div>

                        <div v-else-if="order.advance_status === 'approved'" class="bg-success/5 border border-success/20 rounded-3xl p-6 flex items-center gap-4">
                            <div class="w-12 h-12 bg-success/20 rounded-full flex items-center justify-center shrink-0">
                                <Check :size="20" class="text-success" />
                            </div>
                            <div>
                                <h3 class="font-black text-sm text-success">Pago 1 Aprobado (Bs {{ parseFloat(order.advance_amount).toFixed(2) }})</h3>
                                <p class="text-xs text-success/80">El pago inicial ha sido confirmado correctamente.</p>
                            </div>
                        </div>
                        
                        <template v-if="order.payment_type === 'partial' && order.advance_status === 'approved'">
                            
                            <div v-if="order.status === 'dispatched' && (!order.balance_status || order.balance_status === 'none' || order.balance_status === 'pending' || order.balance_status === 'rejected')" 
                                 class="bg-card border-2 border-warning/50 rounded-3xl p-6 shadow-lg shadow-warning/5 relative overflow-hidden animate-in slide-in-from-bottom-4">
                                <div class="absolute top-0 right-0 bg-warning text-warning-foreground text-[10px] font-black px-4 py-1 rounded-bl-xl uppercase tracking-wider animate-pulse">
                                    Paso 2 Urgente
                                </div>

                                <div class="mb-6 pt-4 flex gap-4">
                                    <div class="w-12 h-12 bg-warning/20 rounded-2xl flex items-center justify-center shrink-0 text-warning">
                                        <AlertCircle :size="24" />
                                    </div>
                                    <div>
                                        <h3 class="font-black text-lg text-foreground">Tu pedido está en camino</h3>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            Para evitar demoras en la puerta, transfiere el saldo de <strong class="text-foreground text-sm">Bs {{ parseFloat(order.balance_amount).toFixed(2) }}</strong> ahora mismo. 
                                            <span class="text-warning font-bold">El conductor no entregará el paquete hasta que se valide este comprobante.</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-6 items-center bg-muted/20 p-6 rounded-2xl border border-border">
                                    <div class="flex-1 w-full space-y-4">
                                        <input type="file" ref="fileInput" @change="(e) => handleFileUpload(e, 'balance')" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                                        
                                        <div v-if="!form.proof || form.type !== 'balance'" @click="$refs.fileInput.click()" class="border-2 border-dashed border-warning/50 hover:border-warning bg-warning/5 rounded-xl p-5 cursor-pointer transition-colors group text-center">
                                            <UploadCloud class="mx-auto text-warning mb-2 group-hover:scale-110 transition-transform" :size="24" />
                                            <span class="text-xs font-black text-warning uppercase">Subir Comprobante de Saldo</span>
                                        </div>
                                        
                                        <div v-else class="border border-border rounded-xl p-2 bg-background flex flex-col gap-2">
                                            <div class="flex items-center gap-3 bg-muted/30 p-2 rounded-lg">
                                                <FileText class="text-warning" :size="20"/>
                                                <span class="text-xs font-bold truncate flex-1">{{ form.proof.name }}</span>
                                            </div>
                                            <div class="flex gap-2">
                                                <button @click="cancelUpload" class="btn btn-ghost btn-sm flex-1 text-xs">Cancelar</button>
                                                <button @click="submitProof" :disabled="form.processing" class="btn bg-warning hover:bg-warning/90 text-warning-foreground btn-sm flex-1 text-xs shadow-md">
                                                    {{ form.processing ? 'Enviando...' : 'Confirmar Saldo' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="order.balance_status === 'under_review'" class="bg-card border border-border rounded-3xl p-6 flex items-center gap-4 mt-4">
                                <div class="w-12 h-12 bg-warning/10 rounded-full flex items-center justify-center shrink-0 animate-pulse">
                                    <Receipt :size="20" class="text-warning" />
                                </div>
                                <div>
                                    <h3 class="font-black text-sm text-foreground">Validando Saldo (Bs {{ parseFloat(order.balance_amount).toFixed(2) }})</h3>
                                    <p class="text-xs text-muted-foreground">Admin revisando el pago final. El conductor será notificado.</p>
                                </div>
                            </div>

                            <div v-else-if="order.balance_status === 'approved'" class="bg-success/5 border border-success/20 rounded-3xl p-6 flex items-center gap-4 mt-4">
                                <div class="w-12 h-12 bg-success/20 rounded-full flex items-center justify-center shrink-0">
                                    <Check :size="20" class="text-success" />
                                </div>
                                <div>
                                    <h3 class="font-black text-sm text-success">Saldo Aprobado (Bs {{ parseFloat(order.balance_amount).toFixed(2) }})</h3>
                                    <p class="text-xs text-success/80">Orden completamente pagada. Espera al conductor.</p>
                                </div>
                            </div>
                            
                            <div v-else-if="order.status === 'preparing'" class="bg-card border border-border rounded-3xl p-6 flex items-start gap-4 opacity-70">
                                <div class="w-10 h-10 bg-muted rounded-full flex items-center justify-center shrink-0">
                                    <Split :size="16" class="text-muted-foreground" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-foreground">Pago 2 Pendiente</h3>
                                    <p class="text-xs text-muted-foreground mt-1">Te pediremos el comprobante del saldo cuando tu pedido salga de la tienda.</p>
                                </div>
                            </div>

                        </template>
                    </template>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="bg-card border border-border rounded-3xl p-6 shadow-sm sticky top-28">
                        <h2 class="font-bold text-foreground text-xs uppercase tracking-widest mb-6 opacity-60 flex items-center gap-2">
                            <Package :size="16"/> Resumen
                        </h2>
                        
                        <div class="space-y-4 mb-6">
                            <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center text-[10px] font-black">
                                        x{{ item.quantity }}
                                    </div>
                                    <p class="font-bold text-xs leading-tight line-clamp-2">{{ item.sku?.name || 'Producto' }}</p>
                                </div>
                                <span class="font-mono font-bold text-xs">Bs {{ parseFloat(item.subtotal).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="bg-muted/30 rounded-2xl p-4 space-y-3 border border-border/50">
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>Subtotal</span>
                                <span class="font-mono">Bs {{ (order.total_amount - order.delivery_fee - order.service_fee).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>Envío</span>
                                <span class="font-mono">Bs {{ parseFloat(order.delivery_fee).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>Servicio</span>
                                <span class="font-mono">Bs {{ parseFloat(order.service_fee).toFixed(2) }}</span>
                            </div>
                            
                            <div class="h-px bg-border my-2"></div>
                            
                            <div class="flex justify-between items-end">
                                <span class="font-black text-foreground text-sm uppercase">Total Orden</span>
                                <span class="font-black text-xl text-primary tracking-tighter">Bs {{ parseFloat(order.total_amount).toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 border border-border rounded-2xl overflow-hidden text-xs">
                            <div class="p-3 flex justify-between items-center" :class="order.advance_status === 'approved' ? 'bg-success/10 text-success' : 'bg-background text-foreground'">
                                <span class="font-bold uppercase tracking-wider">Pago 1 ({{ order.payment_type === 'total' ? 'Total' : 'Anticipo' }})</span>
                                <span class="font-black">Bs {{ parseFloat(order.advance_amount).toFixed(2) }}</span>
                            </div>
                            <div v-if="order.payment_type === 'partial'" class="p-3 border-t border-border flex justify-between items-center" :class="order.balance_status === 'approved' ? 'bg-success/10 text-success' : 'bg-muted/30 text-muted-foreground'">
                                <span class="font-bold uppercase tracking-wider">Pago 2 (Saldo)</span>
                                <span class="font-black">Bs {{ parseFloat(order.balance_amount).toFixed(2) }}</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </ShopLayout>
</template>