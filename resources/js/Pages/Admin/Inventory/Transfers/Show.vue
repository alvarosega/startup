<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
    
    // Iconos para indicar estado visualmente
    import { 
        Truck, CheckCircle2, AlertTriangle, 
        MapPin, User, Package, ArrowRight, ArrowLeft 
    } from 'lucide-vue-next';

    const props = defineProps({
        transfer: Object, 
        // La relación items.sku.product ya viene cargada desde el controlador
    });

    // Estado local para UI
    const isProcessing = ref(false);

    // Inicializamos el formulario con la lógica de recepción
    const form = useForm({
        items: props.transfer.items.map(item => ({
            id: item.id,
            // Datos visuales (no se envían, solo para mostrar)
            product_name: item.sku?.product?.name || 'Desconocido',
            sku_name: item.sku?.name || '---',
            sku_code: item.sku?.code,
            qty_sent: item.qty_sent,
            
            // Lógica de Recepción:
            // Si ya se completó, mostramos lo que se guardó en BD.
            // Si está en tránsito, pre-llenamos con lo enviado (asumiendo que todo llegó bien por defecto).
            qty_received: item.qty_received !== null ? item.qty_received : item.qty_sent
        }))
    });

    const submit = () => {
        // Validación visual antes de enviar
        const hasDifferences = form.items.some(i => i.qty_received < i.qty_sent);
        
        let msg = '¿Confirmar la recepción de la mercadería? El stock entrará a tu inventario.';
        if (hasDifferences) {
            msg = '⚠️ ATENCIÓN: Hay diferencias entre lo enviado y lo recibido.\n\nEl sistema generará una devolución automática a la sucursal de origen por los ítems faltantes.\n\n¿Estás seguro de continuar?';
        }

        if(confirm(msg)) {
            // Usamos transform para enviar solo lo necesario
            form.transform((data) => ({
                items: data.items.map(i => ({
                    id: i.id,
                    qty_received: i.qty_received
                }))
            })).post(route('admin.transfers.receive', props.transfer.id), {
                onStart: () => isProcessing.value = true,
                onFinish: () => isProcessing.value = false,
            });
        }
    };

    const formatDate = (date) => new Date(date).toLocaleString('es-BO', { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto pb-20">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.transfers.index')" class="text-skin-muted hover:text-skin-base transition">
                            <ArrowLeft :size="20" />
                        </Link>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">
                            Guía de Remisión <span class="font-mono text-skin-primary">#{{ transfer.code }}</span>
                        </h1>
                    </div>
                    <p class="text-skin-muted text-sm mt-1 ml-7">Detalle de movimiento de inventario entre sucursales.</p>
                </div>

                <div v-if="transfer.status === 'in_transit'" class="flex items-center gap-2 bg-yellow-500/10 text-yellow-500 px-4 py-2 rounded-global border border-yellow-500/20 animate-pulse">
                    <Truck :size="18" />
                    <span class="font-bold uppercase text-xs tracking-wider">En Tránsito - Pendiente Recepción</span>
                </div>
                <div v-else-if="transfer.status === 'completed'" class="flex items-center gap-2 bg-skin-success/10 text-skin-success px-4 py-2 rounded-global border border-skin-success/20">
                    <CheckCircle2 :size="18" />
                    <span class="font-bold uppercase text-xs tracking-wider">Recepción Completada</span>
                </div>
            </div>

            <div v-if="$page.props.errors.error" class="bg-skin-danger/10 text-skin-danger p-4 rounded-global mb-6 font-bold border border-skin-danger/20 flex items-center gap-2">
                <AlertTriangle :size="20" /> {{ $page.props.errors.error }}
            </div>

            <div class="bg-skin-fill-card p-6 rounded-global border border-skin-border shadow-sm grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-skin-danger via-skin-border to-skin-success opacity-50"></div>

                <div class="text-center md:text-left relative z-10">
                    <p class="text-[10px] text-skin-muted uppercase font-bold tracking-wider mb-1 flex items-center gap-1">
                        <MapPin :size="12" /> Origen
                    </p>
                    <p class="text-xl font-bold text-skin-base">{{ transfer.origin?.name }}</p>
                    <div class="text-xs text-skin-muted mt-2 flex items-center gap-1 md:justify-start justify-center">
                        <User :size="12" /> Enviado por: {{ transfer.sender?.email }}
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center opacity-50">
                    <div class="bg-skin-fill p-2 rounded-full border border-skin-border">
                        <ArrowRight :size="24" class="text-skin-muted" />
                    </div>
                    <div class="text-[10px] font-mono text-skin-muted mt-2">{{ formatDate(transfer.shipped_at) }}</div>
                </div>

                <div class="text-center md:text-right relative z-10">
                    <p class="text-[10px] text-skin-muted uppercase font-bold tracking-wider mb-1 flex items-center gap-1 justify-center md:justify-end">
                        Destino <MapPin :size="12" />
                    </p>
                    <p class="text-xl font-bold text-skin-base">{{ transfer.destination?.name }}</p>
                    <div v-if="transfer.received_at" class="text-xs text-skin-success mt-2 font-bold">
                        Recibido: {{ formatDate(transfer.received_at) }}
                    </div>
                </div>

                <div v-if="transfer.notes" class="md:col-span-3 mt-2 pt-4 border-t border-skin-border text-sm text-skin-muted italic text-center bg-skin-fill/30 rounded p-2">
                    Nota de envío: "{{ transfer.notes }}"
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-skin-fill-card rounded-global border border-skin-border overflow-hidden shadow-lg">
                <div class="p-4 bg-skin-fill border-b border-skin-border flex justify-between items-center">
                    <h3 class="text-xs font-black text-skin-muted uppercase tracking-widest flex items-center gap-2">
                        <Package :size="14" /> Conteo Físico
                    </h3>
                    <span class="text-[10px] text-skin-muted">Por favor verifique la mercadería antes de confirmar.</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-skin-base">
                        <thead class="bg-skin-fill/50 text-skin-muted text-[10px] uppercase font-bold">
                            <tr>
                                <th class="px-6 py-3">Producto</th>
                                <th class="px-6 py-3 text-center w-32">Enviado</th>
                                <th class="px-6 py-3 text-center w-40" :class="transfer.status === 'in_transit' ? 'bg-skin-primary/5 text-skin-primary' : ''">Recibido</th>
                                <th class="px-6 py-3 text-right w-48">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-skin-border">
                            <tr v-for="item in form.items" :key="item.id" class="hover:bg-skin-fill-hover transition group">
                                
                                <td class="px-6 py-4">
                                    <div class="font-bold text-skin-base">{{ item.product_name }}</div>
                                    <div class="text-xs text-skin-muted flex items-center gap-2 mt-1">
                                        <span class="bg-skin-fill border border-skin-border px-1.5 rounded">{{ item.sku_name }}</span>
                                        <span class="font-mono opacity-60">COD: {{ item.sku_code }}</span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span class="font-mono text-lg font-bold text-skin-muted">{{ item.qty_sent }}</span>
                                </td>

                                <td class="px-6 py-4 text-center" :class="transfer.status === 'in_transit' ? 'bg-skin-primary/5' : ''">
                                    
                                    <div v-if="transfer.status === 'in_transit'" class="relative">
                                        <input 
                                            v-model.number="item.qty_received" 
                                            type="number" 
                                            min="0" 
                                            :max="item.qty_sent" 
                                            class="w-24 bg-skin-fill border text-center font-bold text-lg rounded-global p-1.5 focus:ring-2 focus:ring-skin-primary/50 outline-none transition-all"
                                            :class="item.qty_received < item.qty_sent ? 'border-skin-danger text-skin-danger bg-skin-danger/5' : 'border-skin-border text-skin-base'"
                                        >
                                    </div>
                                    
                                    <div v-else class="font-mono text-lg font-bold" :class="item.qty_received < item.qty_sent ? 'text-skin-danger' : 'text-skin-success'">
                                        {{ item.qty_received }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div v-if="item.qty_sent - item.qty_received > 0" class="flex flex-col items-end">
                                        <span class="text-[10px] font-bold text-skin-danger uppercase bg-skin-danger/10 px-2 py-0.5 rounded border border-skin-danger/20 flex items-center gap-1">
                                            <AlertTriangle :size="10" /> Faltan {{ item.qty_sent - item.qty_received }}
                                        </span>
                                        <span class="text-[9px] text-skin-muted mt-1">Se devolverán a origen</span>
                                    </div>
                                    <div v-else class="flex justify-end items-center gap-1 text-skin-success font-bold text-xs">
                                        <CheckCircle2 :size="14" /> Conforme
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center" v-if="transfer.status === 'in_transit'">
                    <div class="text-xs text-skin-muted max-w-md">
                        * Al confirmar, el stock recibido se sumará a su inventario. Las diferencias se registrarán automáticamente como retorno a la sucursal de origen.
                    </div>

                    <button type="submit" 
                            :disabled="form.processing"
                            class="bg-skin-success hover:brightness-110 text-white font-bold py-3 px-8 rounded-global shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span v-if="form.processing">Procesando...</span>
                        <span v-else class="flex items-center gap-2"><CheckCircle2 :size="18"/> Confirmar Recepción</span>
                    </button>
                </div>
            </form>

        </div>
    </AdminLayout>
</template>