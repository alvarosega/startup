<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import { 
    CheckCircle2, Clock, Upload, XCircle, 
    Truck, Package, AlertCircle, FileText, QrCode, Copy,
    ArrowLeft // <--- AÑADIR ESTE ICONO
} from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    proofUrl: String
});

const form = useForm({ proof: null });
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

// Mapa de estados semánticos
const statuses = {
    pending_proof: { 
        label: 'Esperando Pago', 
        color: 'bg-warning/10 text-warning border-warning/20', 
        icon: Clock,
        step: 1
    },
    review: { 
        label: 'Verificando', 
        color: 'bg-primary/10 text-primary border-primary/20', 
        icon: FileText,
        step: 2
    },
    confirmed: { 
        label: 'Preparando', 
        color: 'bg-blue-500/10 text-blue-500 border-blue-500/20', 
        icon: Package,
        step: 3
    },
    dispatched: { 
        label: 'En Camino', 
        color: 'bg-purple-500/10 text-purple-500 border-purple-500/20', 
        icon: Truck,
        step: 4
    },
    completed: { 
        label: 'Entregado', 
        color: 'bg-success/10 text-success border-success/20', 
        icon: CheckCircle2,
        step: 5
    },
    cancelled: { 
        label: 'Cancelado', 
        color: 'bg-destructive/10 text-destructive border-destructive/20', 
        icon: XCircle,
        step: 0
    },
};

const currentStatus = computed(() => statuses[props.order.status]);
const steps = ['pending_proof', 'review', 'confirmed', 'dispatched', 'completed'];
</script>

<template>
    <ShopLayout>
        <Head :title="`Orden #${order.code}`" />

        <div class="container mx-auto px-4 py-8 pb-32 lg:pb-12 min-h-full">
            <div class="mb-6">
                <Link :href="route('orders.history')" 
                    class="inline-flex items-center gap-2 text-sm font-bold text-muted-foreground hover:text-primary transition-colors group">
                    
                    <div class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center shadow-sm group-hover:border-primary/50 group-hover:text-primary transition-all">
                        <ArrowLeft :size="16" stroke-width="2.5" />
                    </div>
                    
                    <span>Volver a mis Pedidos</span>
                </Link>
            </div>
            <div class="mb-8 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2 border"
                     :class="currentStatus.color">
                    <component :is="currentStatus.icon" :size="14" />
                    {{ currentStatus.label }}
                </div>
                <h1 class="font-display font-black text-3xl text-foreground tracking-tight">
                    Orden #{{ order.code }}
                </h1>
                <p class="text-xs text-muted-foreground mt-1">
                    Creada el {{ new Date(order.created_at).toLocaleDateString() }} • {{ new Date(order.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                </p>
            </div>

            <div v-if="order.status !== 'cancelled'" class="mb-10 px-2 overflow-x-auto scrollbar-hide">
                <div class="flex items-center justify-between min-w-[300px] relative">
                    <div class="absolute left-0 top-1/2 w-full h-1 bg-border -z-10 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 h-1 bg-primary -z-10 rounded-full transition-all duration-1000"
                         :style="{ width: `${((currentStatus.step - 1) / (steps.length - 1)) * 100}%` }"></div>

                    <div v-for="(stepKey, index) in steps" :key="stepKey" class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 transition-all duration-500 bg-card"
                             :class="index + 1 <= currentStatus.step 
                                ? 'border-primary text-primary scale-110' 
                                : 'border-muted text-muted-foreground bg-muted/50'">
                            <CheckCircle2 v-if="index + 1 < currentStatus.step" :size="16" />
                            <span v-else class="text-xs font-bold">{{ index + 1 }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-2 min-w-[300px] text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                    <span>Pago</span>
                    <span>Revisión</span>
                    <span>Prep</span>
                    <span>Ruta</span>
                    <span>Fin</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 lg:order-2 space-y-6">
                    
                    <div v-if="order.status === 'pending_proof'" class="bg-card rounded-3xl border border-primary/20 shadow-xl shadow-primary/5 overflow-hidden relative group">
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-primary via-blue-500 to-purple-500"></div>
                        
                        <div class="p-6 text-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 text-primary animate-bounce-slow">
                                <QrCode :size="32" />
                            </div>
                            <h3 class="font-black text-foreground text-lg mb-1">Realizar Pago QR</h3>
                            <p class="text-xs text-muted-foreground mb-6 px-4">
                                Escanea el código y paga el monto exacto de <strong class="text-foreground">Bs {{ order.total_amount }}</strong>.
                            </p>

                            <div class="bg-white p-4 rounded-xl inline-block shadow-inner border border-gray-100 mb-6">
                                <img src="/assets/qr_placeholder.png" class="w-40 h-40 object-contain mix-blend-multiply" alt="QR Pago">
                            </div>

                            <form @submit.prevent="uploadProof">
                                <label class="block w-full border-2 border-dashed border-border hover:border-primary/50 hover:bg-primary/5 rounded-2xl p-4 cursor-pointer transition-all relative group/upload">
                                    <input type="file" ref="fileInput" @change="handleFile" class="hidden" accept="image/*">
                                    
                                    <div v-if="previewUrl" class="relative">
                                        <img :src="previewUrl" class="h-40 w-full object-contain rounded-lg shadow-sm bg-black/5">
                                        <button type="button" @click.prevent="previewUrl = null; form.proof = null" 
                                                class="absolute -top-3 -right-3 bg-destructive text-white rounded-full p-1.5 shadow-md hover:scale-110 transition">
                                            <XCircle :size="16"/>
                                        </button>
                                    </div>
                                    
                                    <div v-else class="py-4">
                                        <Upload class="mx-auto mb-2 text-muted-foreground group-hover/upload:text-primary transition-colors" :size="24"/>
                                        <span class="text-xs font-black uppercase text-foreground">Subir Comprobante</span>
                                        <p class="text-[10px] text-muted-foreground mt-1">JPG o PNG</p>
                                    </div>
                                </label>
                                <p v-if="form.errors.proof" class="text-destructive text-xs mt-2 font-bold">{{ form.errors.proof }}</p>

                                <BaseButton class="w-full mt-4" :isLoading="form.processing" :disabled="!form.proof">
                                    Enviar para Verificación
                                </BaseButton>
                            </form>
                        </div>
                    </div>

                    <div v-else-if="order.status === 'review'" class="bg-card border border-primary/20 rounded-3xl p-8 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-primary/5 animate-pulse"></div>
                        <div class="relative z-10">
                            <Clock class="w-16 h-16 text-primary mx-auto mb-4" />
                            <h3 class="font-black text-xl text-foreground">Verificando Pago</h3>
                            <p class="text-sm text-muted-foreground mt-2 mb-6">Nuestro equipo está validando tu comprobante manualmente.</p>
                            
                            <div v-if="proofUrl" class="inline-block relative group">
                                <p class="text-[10px] font-bold uppercase text-muted-foreground mb-2">Comprobante enviado:</p>
                                <a :href="proofUrl" target="_blank" class="block w-24 h-32 rounded-xl border border-border overflow-hidden shadow-sm hover:scale-105 transition">
                                    <img :src="proofUrl" class="w-full h-full object-cover">
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.status === 'cancelled'" class="bg-destructive/5 border border-destructive/20 rounded-3xl p-8 text-center">
                        <AlertCircle class="w-16 h-16 text-destructive mx-auto mb-4" />
                        <h3 class="font-black text-xl text-destructive">Orden Cancelada</h3>
                        <p class="text-sm text-muted-foreground mt-2 font-medium">
                            {{ order.rejection_reason || 'El tiempo de reserva expiró o el pago no fue válido.' }}
                        </p>
                    </div>

                </div>

                <div class="lg:col-span-2 lg:order-1 space-y-6">
                    
                    <div class="bg-card rounded-2xl border border-border p-6 shadow-sm">
                        <h3 class="font-bold text-foreground text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Truck :size="18" class="text-primary"/> Datos de Entrega
                        </h3>
                        <div class="bg-muted/30 rounded-xl p-4 space-y-3">
                            <div>
                                <span class="text-[10px] font-black text-muted-foreground uppercase">Dirección</span>
                                <p class="text-sm font-medium text-foreground">{{ order.delivery_data.address }}</p>
                            </div>
                            <div v-if="order.delivery_data.details">
                                <span class="text-[10px] font-black text-muted-foreground uppercase">Referencia</span>
                                <p class="text-sm text-muted-foreground italic">{{ order.delivery_data.details }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-card rounded-2xl border border-border overflow-hidden shadow-sm">
                        <div class="p-4 bg-muted/20 border-b border-border font-bold text-foreground text-sm flex items-center gap-2">
                            <Package :size="16" /> Resumen de Compra
                        </div>
                        <div class="divide-y divide-border/50">
                            <div v-for="item in order.items" :key="item.id" class="p-4 flex gap-4 hover:bg-muted/10 transition-colors">
                                <div class="w-12 h-12 bg-background rounded-lg border border-border flex items-center justify-center shrink-0">
                                    <Package :size="20" class="text-muted-foreground opacity-50"/>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-foreground text-sm line-clamp-1">{{ item.sku.product?.name }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ item.sku.name }} <span class="text-primary font-bold">x {{ item.quantity }}</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-foreground text-sm">Bs {{ item.subtotal }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-muted/10 border-t border-border flex justify-between items-center">
                            <span class="font-bold text-muted-foreground text-sm uppercase tracking-wider">Total Final</span>
                            <span class="text-2xl font-black text-foreground tracking-tighter">Bs {{ order.total_amount }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
@keyframes bounce-slow {
  0%, 100% { transform: translateY(-5%); }
  50% { transform: translateY(5%); }
}
.animate-bounce-slow {
  animation: bounce-slow 2s infinite;
}
</style>