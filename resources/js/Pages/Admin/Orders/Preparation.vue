<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue'; 
import { ArrowLeft, Box, CheckCircle, Package, AlertTriangle, UserX, Truck } from 'lucide-vue-next';

const props = defineProps({ order: Object });

const packedItems = ref(new Set());
const togglePack = (skuId) => packedItems.value.has(skuId) ? packedItems.value.delete(skuId) : packedItems.value.add(skuId);
const allPacked = computed(() => packedItems.value.size === props.order.items.length && props.order.items.length > 0);

const markAsReady = () => router.post(route('admin.orders.mark-as-ready', props.order.code));

// PROTOCOLO DE EXPULSIÓN
const isUnassigning = ref(false);
const unassignDriver = () => {
    if (confirm('¿Está seguro de remover a este conductor? La orden volverá a la bolsa general.')) {
        isUnassigning.value = true;
        router.post(route('admin.orders.unassign-driver', props.order.code), {}, {
            onFinish: () => isUnassigning.value = false
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="'Preparar ' + order.code" />
        <div class="p-6 max-w-4xl mx-auto">
            <Link :href="route('admin.orders.index')" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-muted-foreground hover:text-foreground mb-6">
                <ArrowLeft :size="16" /> Monitor Logístico
            </Link>

            <div v-if="order.driver" class="mb-6 bg-blue-500/10 border-2 border-blue-500/20 p-4 rounded-3xl flex justify-between items-center animate-in slide-in-from-top-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30 overflow-hidden">
                        <img v-if="order.driver.avatar" :src="`/assets/avatars/${order.driver.avatar}`" class="w-full h-full object-cover">
                        <Truck v-else class="text-blue-500" :size="20"/>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-blue-500/70 mb-0.5">Conductor en camino</p>
                        <p class="text-sm font-black uppercase text-blue-600">{{ order.driver.name }}</p>
                    </div>
                </div>
                <button @click="unassignDriver" :disabled="isUnassigning" class="w-10 h-10 rounded-xl bg-destructive/10 text-destructive flex items-center justify-center hover:bg-destructive hover:text-white transition-all">
                    <UserX v-if="!isUnassigning" :size="18" />
                    <span v-else class="w-4 h-4 border-2 border-destructive border-t-transparent rounded-full animate-spin"></span>
                </button>
            </div>

            <div class="bg-card border-2 border-border rounded-3xl p-8 shadow-xl">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-3xl font-black uppercase italic tracking-tighter text-foreground"><span class="text-primary">#</span>{{ order.code }}</h1>
                        <p class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.2em] mt-1">{{ order.customer.name }}</p>
                    </div>
                    <div class="px-4 py-2 rounded-xl bg-blue-500/10 text-blue-500 border-2 border-blue-500/20 font-black uppercase text-[10px] tracking-widest">
                        ZONA DE EMPAQUETADO
                    </div>
                </div>

                <div class="flex items-center gap-3 mb-6">
                    <Box class="text-primary" :size="24" />
                    <h2 class="text-xl font-black uppercase tracking-tighter italic text-foreground">Manifiesto de Almacén</h2>
                </div>

                <div class="space-y-4">
                    <div v-for="item in order.items" :key="item.sku_id" 
                         @click="togglePack(item.sku_id)"
                         class="flex items-center gap-4 p-4 rounded-2xl border-2 transition-all cursor-pointer"
                         :class="packedItems.has(item.sku_id) ? 'bg-primary/5 border-primary/30 opacity-60' : 'bg-muted border-border hover:border-primary/50'">
                        
                        <div class="w-16 h-16 bg-background rounded-xl p-2 border border-border shrink-0 flex items-center justify-center">
                            <img v-if="item.image" :src="item.image" class="max-w-full max-h-full object-contain">
                            <Package v-else class="text-muted-foreground" :size="24" />
                        </div>
                        
                        <div class="flex-1">
                            <h4 class="text-sm font-black uppercase tracking-tight text-foreground leading-none mb-2">{{ item.name }}</h4>
                            <span class="inline-block px-2 py-1 bg-foreground text-background text-[10px] font-mono font-black rounded uppercase">
                                EXTRAER: {{ item.quantity }} UNIDADES
                            </span>
                        </div>

                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                             :class="packedItems.has(item.sku_id) ? 'bg-primary border-primary text-primary-foreground' : 'border-muted-foreground/30'">
                            <CheckCircle v-if="packedItems.has(item.sku_id)" :size="16" stroke-width="4" />
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t-2 border-border">
                    <div v-if="!allPacked" class="flex items-center gap-2 text-[10px] font-black text-amber-500 uppercase tracking-widest justify-center mb-4">
                        <AlertTriangle :size="14" /> Marca todos los items para finalizar
                    </div>
                    <button @click="markAsReady" :disabled="!allPacked" 
                            class="w-full py-5 font-black uppercase text-sm rounded-2xl transition-all flex justify-center items-center gap-2"
                            :class="allPacked ? 'bg-blue-600 text-white shadow-xl shadow-blue-600/30 hover:bg-blue-700 active:scale-95' : 'bg-muted text-muted-foreground opacity-50 cursor-not-allowed'">
                        <Package :size="18" /> Finalizar Empaquetado y Generar PIN
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>