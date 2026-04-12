<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Truck, MapPin, Phone, ShieldCheck, ArrowLeft, Package, Clock, RefreshCw, Activity, Navigation } from 'lucide-vue-next';

const props = defineProps({ 
    tracking_data: Object, 
    order: Object 
});

const isSyncing = ref(false);

// UNWRAP SEGURO PARA DEFER
const safeOrder = computed(() => props.tracking_data?.order || props.order);
const isDataReady = computed(() => safeOrder.value !== undefined);

const statusSteps = [
    { key: 'preparing', label: 'Empaque', icon: Package },
    { key: 'ready_for_dispatch', label: 'Validado', icon: Clock },
    { key: 'dispatched', label: 'Tránsito', icon: Truck },
    { key: 'arrived', label: 'Destino', icon: MapPin },
];

const currentStepIndex = computed(() => {
    if (!isDataReady.value) return 0;
    return statusSteps.findIndex(s => s.key === safeOrder.value.status);
});

const syncRadar = () => {
    isSyncing.value = true;
    router.reload({ only: ['tracking_data', 'order'], onFinish: () => isSyncing.value = false });
};

// Variable de trayecto para CSS y SVG sincronizados
const motionPath = "M10 50 C 150 10, 350 90, 480 50";
</script>

<template>
    <ShopLayout :isProfileSection="true">
        <Head :title="isDataReady ? 'Radar Logístico #' + safeOrder.code : 'Cargando Radar...'" />

        <div class="max-w-2xl mx-auto min-h-[85vh] py-8 pb-32 px-4">
            
            <div class="flex justify-between items-center mb-10">
                <Link :href="route('customer.order.index')" 
                      class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-foreground/40 hover:text-primary transition-colors group">
                    <ArrowLeft :size="16" class="group-hover:-translate-x-1 transition-transform" /> Regresar al Historial
                </Link>

                <button v-if="isDataReady" @click="syncRadar" :disabled="isSyncing" 
                        class="flex items-center gap-2 px-4 py-2 bg-foreground/5 border border-border/10 rounded-full text-foreground/60 hover:text-primary hover:bg-primary/5 transition-all active:scale-95 disabled:opacity-30">
                    <span class="text-[10px] font-black uppercase tracking-widest">Sincronizar Radar</span>
                    <RefreshCw :size="14" :class="{ 'animate-spin': isSyncing }" />
                </button>
            </div>

            <div v-if="!isDataReady" class="space-y-6">
                <div class="product-card h-64 skeleton !rounded-3xl"></div>
                <div class="product-card h-48 skeleton !rounded-3xl"></div>
            </div>

            <template v-else>
                
                <div class="product-card glass-titanium !rounded-3xl border border-border/10 overflow-hidden relative shadow-f1-glow mb-8">
                    <div class="p-8 text-center">
                        <div class="relative flex justify-between mb-12 max-w-md mx-auto">
                            <div class="absolute top-5 left-0 w-full h-0.5 bg-foreground/10 z-0"></div>
                            <div class="absolute top-5 left-0 h-0.5 bg-primary transition-all duration-1000 z-0"
                                 :style="{ width: (currentStepIndex / (statusSteps.length - 1)) * 100 + '%' }">
                            </div>

                            <div v-for="(step, index) in statusSteps" :key="step.key" class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border-2"
                                     :class="[
                                         index <= currentStepIndex 
                                         ? 'bg-primary border-primary text-primary-foreground shadow-[0_0_15px_rgba(var(--primary-rgb),0.5)]' 
                                         : 'bg-background border-border/40 text-foreground/20'
                                     ]">
                                    <component :is="step.icon" :size="18" :stroke-width="index <= currentStepIndex ? 3 : 2" />
                                </div>
                                <span class="absolute -bottom-7 text-[10px] font-black uppercase tracking-widest whitespace-nowrap"
                                      :class="index <= currentStepIndex ? 'text-primary' : 'text-foreground/20'">
                                    {{ step.label }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-12 pt-4">
                            <h1 class="text-3xl font-black italic uppercase tracking-tighter text-foreground leading-none">
                                Protocolo de <span class="text-primary">Despacho</span>
                            </h1>
                            <p class="text-xs font-mono font-black text-foreground/40 uppercase tracking-[0.3em] mt-3 flex items-center justify-center gap-2">
                                <Activity :size="12" class="text-primary animate-pulse" />
                                Status: {{ safeOrder.status.replace('_', ' ') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="safeOrder.status === 'dispatched' || safeOrder.status === 'arrived'" 
                     class="product-card glass-manifest !rounded-3xl p-8 mb-8 relative overflow-hidden border-border/20 shadow-inner">
                    
                    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
                         style="background-image: radial-gradient(var(--primary) 1px, transparent 0); background-size: 24px 24px;"></div>
                    
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-foreground/40 mb-10 flex items-center gap-2">
                        <Navigation :size="14" class="text-primary" /> Trayectoria de Hardware
                    </h3>

                    <div class="relative w-full h-32 flex items-center justify-center">
                        <svg width="100%" height="100%" viewBox="0 0 500 100" fill="none" class="overflow-visible">
                            <path :d="motionPath" stroke="currentColor" stroke-width="2" class="text-foreground/5" />
                            <path :d="motionPath" stroke="var(--primary)" stroke-width="3" stroke-linecap="round" class="path-draw-animation opacity-30" />
                            
                            <circle cx="10" cy="50" r="4" fill="var(--primary)" />
                            <circle cx="480" cy="50" r="4" fill="var(--primary)" :class="{'animate-ping': safeOrder.status === 'dispatched'}" />
                        </svg>

                        <div v-if="safeOrder.status === 'dispatched'" 
                             class="truck-motion-object text-primary bg-background p-2 rounded-lg border border-primary/30 shadow-f1-glow z-20">
                            <Truck :size="20" stroke-width="3" />
                        </div>
                        
                        <div v-else class="absolute right-[10px] top-[40px] text-success bg-background p-2 rounded-lg border border-success/30 shadow-lg">
                            <MapPin :size="20" stroke-width="3" />
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary animate-pulse">
                            {{ safeOrder.status === 'dispatched' ? 'Hardware en movimiento: Sincronizando GPS...' : 'Unidad en Punto de Destino' }}
                        </p>
                    </div>
                </div>

                <div v-if="safeOrder.delivery_type === 'delivery'" 
                     class="product-card glass-manifest !rounded-3xl p-8 mb-8 text-center border-2 border-primary/20 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-primary animate-pulse"></div>
                    
                    <div class="flex items-center justify-center gap-2 mb-6">
                        <ShieldCheck class="text-primary" :size="18" />
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-foreground/60">Token de Validación Final</h3>
                    </div>

                    <div class="bg-background/60 inline-block px-12 py-6 rounded-2xl border border-border/40 shadow-inner group transition-all hover:border-primary/40">
                        <span class="text-6xl md:text-7xl font-mono font-black tracking-[0.25em] text-foreground drop-shadow-md group-hover:scale-110 transition-transform block">
                            {{ safeOrder.delivery_otp }}
                        </span>
                    </div>
                    
                    <div class="mt-8 p-4 bg-primary/5 rounded-xl border border-primary/10 max-w-sm mx-auto">
                        <p class="text-xs font-bold text-foreground/60 uppercase leading-relaxed tracking-tight">
                            Proporcione este código al conductor <span class="text-primary font-black">SOLO</span> al recibir su hardware físico.
                        </p>
                    </div>
                </div>

                <div class="product-card glass-manifest !rounded-3xl p-6 md:p-8">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-foreground/40 mb-6 flex items-center gap-3">
                        <div class="w-1 h-4 bg-primary rounded-full"></div>
                        Custodia de Activos
                    </h3>

                    <div v-if="safeOrder.driver" class="flex items-center gap-6 bg-foreground/5 border border-border/20 p-5 rounded-2xl animate-in fade-in duration-500">
                        <div class="w-16 h-16 bg-primary text-primary-foreground rounded-2xl flex items-center justify-center text-2xl font-black shadow-lg italic">
                            {{ safeOrder.driver.name.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">Unidad de Transporte</p>
                            <p class="text-lg font-black text-foreground uppercase truncate">{{ safeOrder.driver.name }}</p>
                        </div>
                        <a :href="'tel:' + safeOrder.driver.phone" 
                           class="w-14 h-14 bg-foreground text-background dark:bg-primary dark:text-primary-foreground rounded-2xl flex items-center justify-center hover:scale-105 active:scale-90 transition-all shadow-xl">
                            <Phone :size="24" stroke-width="2.5" />
                        </a>
                    </div>

                    <div v-else class="flex items-center gap-6 bg-foreground/5 border border-dashed border-border/40 p-6 rounded-2xl opacity-50">
                        <div class="w-16 h-16 bg-background rounded-2xl flex items-center justify-center text-foreground/20 animate-pulse border border-border/20">
                            <Package :size="32" stroke-width="1" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-black uppercase tracking-widest text-foreground/60">Buscando Operador...</p>
                            <p class="text-xs font-bold text-foreground/40 uppercase tracking-tight">El almacén está consolidando el hardware para despacho.</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </ShopLayout>
</template>

<style scoped>
.glass-titanium {
    background: linear-gradient(135deg, hsl(var(--card) / 0.8) 0%, hsl(var(--card) / 0.4) 100%);
    backdrop-filter: blur(40px) saturate(200%);
}
.glass-manifest {
    background-color: hsl(var(--card) / 0.5);
    backdrop-filter: blur(40px) saturate(200%);
    border-color: hsl(var(--border) / 0.4);
}
.dark .glass-manifest { background-color: hsl(var(--card) / 0.8); }
.shadow-f1-glow { box-shadow: 0 0 40px -10px hsl(var(--primary) / 0.2); }

/* --- INGENIERÍA DE ANIMACIÓN CSS (OFFSET PATH) --- */
.truck-motion-object {
    /* Definimos el trayecto (debe ser el mismo que el SVG path) */
    offset-path: path("M10 50 C 150 10, 350 90, 480 50");
    offset-rotate: auto 0deg; /* Rota automáticamente para seguir la curva */
    animation: moveTruck 8s linear infinite;
    position: absolute;
    left: 0;
    top: 0;
}

@keyframes moveTruck {
    0% { offset-distance: 0%; }
    100% { offset-distance: 100%; }
}

/* Animación de "dibujo" de la línea SVG */
.path-draw-animation {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: drawLine 4s ease-in-out infinite alternate;
}

@keyframes drawLine {
    to { stroke-dashoffset: 0; }
}
</style>