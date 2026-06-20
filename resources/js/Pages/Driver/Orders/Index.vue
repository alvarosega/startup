<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { MapPin, PackageCheck, Clock, CheckCircle2, Truck, Hash, ChevronRight } from 'lucide-vue-next';
import DriverLayout from '@/Layouts/DriverLayout.vue';

defineProps({ orders: Array });

const form = useForm({});

const takeOrder = (code) => {
    if (confirm('¿Confirmas tu compromiso para transportar esta carga? Una vez aceptada, el hardware queda bajo tu custodia.')) {
        form.post(route('driver.orders.take', code));
    }
};
</script>

<template>
    <DriverLayout>
        <Head title="Bolsa de Cargas Disponibles" />
        
        <div class="p-6 space-y-6 pb-32">
            
            <div class="flex items-center justify-between mb-8 border-b border-border/40 pb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary border border-primary/20 shadow-f1-glow">
                        <Truck :size="24" stroke-width="2.5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black uppercase italic tracking-tighter leading-none">Bolsa de <span class="text-primary">Cargas</span></h1>
                        <p class="text-[10px] font-mono font-black text-muted-foreground uppercase tracking-[0.3em] mt-1">Radar de Proximidad Activo</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black font-mono text-primary bg-primary/10 px-3 py-1 rounded-full border border-primary/20">
                        {{ orders.length }} DISPONIBLES
                    </span>
                </div>
            </div>
            
            <div v-if="orders.length > 0" class="grid gap-6">
                <div v-for="order in orders" :key="order.id" 
                    class="product-card glass-manifest p-6 flex flex-col gap-6 relative overflow-hidden group transition-all duration-500">
                    
                    <div class="absolute top-0 left-0 w-1.5 h-full transition-colors"
                         :class="order.status === 'ready_for_dispatch' ? 'bg-success' : 'bg-warning animate-pulse'">
                    </div>

                    <div class="flex justify-between items-start pl-2">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1 bg-foreground/5 px-2 py-1 rounded-md border border-border/40">
                                    <Hash :size="12" class="text-primary" />
                                    <span class="text-xs font-black font-mono tracking-tighter">{{ order.code }}</span>
                                </div>
                                
                                <span v-if="order.status === 'ready_for_dispatch'" 
                                      class="px-2 py-1 bg-success/10 text-success border border-success/20 text-[10px] font-black uppercase rounded-md tracking-widest">
                                    READY_TO_GO
                                </span>
                                <span v-else 
                                      class="px-2 py-1 bg-warning/10 text-warning border border-warning/20 text-[10px] font-black uppercase rounded-md tracking-widest flex items-center gap-1">
                                    <Clock :size="12"/> PACKING
                                </span>
                            </div>

                            <div>
                                <p class="text-base font-black uppercase tracking-tight text-foreground">{{ order.branch.name }}</p>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground font-bold mt-1">
                                    <MapPin :size="14" class="text-primary shrink-0" /> 
                                    <span class="truncate">{{ order.branch.address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right bg-foreground/5 p-3 rounded-2xl border border-border/40 shadow-inner">
                            <p class="text-[10px] uppercase font-black tracking-widest text-muted-foreground mb-1">Fee Despacho</p>
                            <p class="text-2xl font-black font-mono leading-none text-foreground tracking-tighter italic">
                                <span class="text-xs text-primary mr-1 not-italic font-sans">Bs</span>{{ order.total_amount }}
                            </p>
                        </div>
                    </div>

                    <button @click="takeOrder(order.code)" :disabled="form.processing"
                        class="w-full h-16 bg-foreground text-background dark:bg-primary dark:text-primary-foreground rounded-2xl font-black uppercase text-xs tracking-[0.2em] flex items-center justify-center gap-3 active:scale-[0.98] transition-all disabled:opacity-30 shadow-apple-soft group/btn">
                        <template v-if="form.processing">
                            <Clock class="animate-spin" :size="20" /> ASIGNANDO_HARDWARE...
                        </template>
                        <template v-else>
                            ACEPTAR CARGA <ChevronRight :size="18" stroke-width="3" class="group-hover/btn:translate-x-1 transition-transform" />
                        </template>
                    </button>
                </div>
            </div>
            
            <div v-else class="py-32 text-center space-y-6 product-card glass-manifest border-dashed border-2">
                <div class="w-24 h-24 bg-foreground/5 rounded-full flex items-center justify-center mx-auto border border-border/40">
                    <PackageCheck :size="48" stroke-width="1" class="text-foreground/20" />
                </div>
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tighter italic">Zona Despejada</h2>
                    <p class="text-xs font-bold text-muted-foreground uppercase tracking-[0.2em] mt-2">No hay hardware pendiente en tu sector operativo</p>
                </div>
            </div>
        </div>
    </DriverLayout>
</template>

<style scoped>
.glass-manifest {
    background-color: hsl(var(--card) / 0.5);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border-color: hsl(var(--border) / 0.4);
}

.dark .glass-manifest {
    background-color: hsl(var(--card) / 0.8);
}

/* Animación de entrada de los módulos */
.product-card {
    animation: slideUp 0.5s cubic-bezier(0.32, 0.72, 0, 1) backwards;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>