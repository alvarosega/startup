<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import { 
        Truck, CheckCircle2, ArrowRight, ArrowLeft, 
        MapPin, FileText, User, Package, Calendar 
    } from 'lucide-vue-next';

    const props = defineProps({ 
        transfers: Object,
        user_branch_id: Number // Necesario para saber si es Entrante o Saliente
    });

    const formatDate = (date) => new Date(date).toLocaleDateString('es-BO', { 
        month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' 
    });

    // Helper para determinar si la transferencia es entrante (hacia mi sucursal)
    const isIncoming = (transfer) => transfer.destination_branch_id === props.user_branch_id;
</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-black text-skin-base tracking-tight">Transferencias</h1>
                <p class="text-skin-muted text-sm mt-1">Control de movimientos entre sucursales.</p>
            </div>
            <Link :href="route('admin.transfers.create')" class="bg-skin-primary hover:brightness-110 text-skin-primary-text px-5 py-2.5 rounded-global font-bold shadow-sm transition flex items-center gap-2 active:scale-95">
                <span>+ Nueva Guía</span>
            </Link>
        </div>

        <div class="flex flex-col gap-4">
            
            <div v-for="t in transfers.data" :key="t.id" 
                 class="bg-skin-fill-card border border-skin-border rounded-global shadow-sm overflow-hidden hover:border-skin-primary/30 transition group">
                
                <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center relative">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1" 
                         :class="t.status === 'in_transit' ? 'bg-yellow-500' : 'bg-skin-success'"></div>

                    <div class="md:col-span-2 flex flex-col pl-2">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-black text-skin-primary text-lg">{{ t.code }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-skin-muted mt-1">
                            <Calendar :size="12" /> {{ formatDate(t.created_at) }}
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <div class="flex flex-col">
                            <span v-if="isIncoming(t)" class="text-[10px] font-bold uppercase text-skin-success flex items-center gap-1 mb-1">
                                <ArrowLeft :size="12" /> Entrante (Recibes)
                            </span>
                            <span v-else class="text-[10px] font-bold uppercase text-skin-primary flex items-center gap-1 mb-1">
                                <ArrowRight :size="12" /> Saliente (Envías)
                            </span>

                            <div class="flex items-center gap-2 text-sm">
                                <span :class="!isIncoming(t) ? 'font-bold text-skin-base' : 'text-skin-muted'">
                                    {{ t.origin?.name }}
                                </span>
                                <ArrowRight :size="14" class="text-skin-border" />
                                <span :class="isIncoming(t) ? 'font-bold text-skin-base' : 'text-skin-muted'">
                                    {{ t.destination?.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span v-if="t.status === 'in_transit'" class="inline-flex items-center gap-1.5 bg-yellow-500/10 text-yellow-600 px-2 py-0.5 rounded text-[10px] font-bold border border-yellow-500/20 uppercase tracking-wide">
                                <Truck :size="10" /> En Tránsito
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 bg-skin-success/10 text-skin-success px-2 py-0.5 rounded text-[10px] font-bold border border-skin-success/20 uppercase tracking-wide">
                                <CheckCircle2 :size="10" /> Completado
                            </span>
                        </div>
                        <div class="text-xs text-skin-muted flex items-center gap-2">
                            <span class="flex items-center gap-1"><Package :size="12"/> {{ t.items_count }} Items</span>
                            <span class="text-skin-border">|</span>
                            <span class="flex items-center gap-1" :title="t.sender?.email"><User :size="12"/> {{ t.sender?.name || 'Usuario' }}</span>
                        </div>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <Link v-if="t.status === 'in_transit' && isIncoming(t)" 
                              :href="route('admin.transfers.show', t.id)" 
                              class="bg-skin-success hover:brightness-110 text-white px-4 py-2 rounded-global font-bold text-xs shadow-md transition flex items-center gap-2 animate-pulse">
                            <Package :size="16" /> Recibir Mercadería
                        </Link>

                        <Link v-else 
                              :href="route('admin.transfers.show', t.id)" 
                              class="text-skin-muted hover:text-skin-primary font-bold text-xs uppercase border border-skin-border hover:border-skin-primary px-4 py-2 rounded-global transition flex items-center gap-2 bg-skin-fill">
                            <FileText :size="14" /> Ver Detalle
                        </Link>
                    </div>

                </div>
            </div>

            <div v-if="transfers.data.length === 0" class="p-16 text-center border-2 border-dashed border-skin-border rounded-global bg-skin-fill/20 text-skin-muted">
                <Truck :size="48" class="mx-auto mb-4 opacity-20" />
                <h3 class="text-lg font-bold">Sin movimientos</h3>
                <p class="text-sm opacity-70">No hay transferencias registradas aún.</p>
            </div>

        </div>

        <div class="mt-6 flex justify-center" v-if="transfers.links.length > 3">
            <div class="flex gap-1 bg-skin-fill-card p-1 rounded-global border border-skin-border shadow-sm">
                <template v-for="(link, k) in transfers.links" :key="k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" 
                          class="px-3 py-1.5 rounded text-xs font-medium transition-colors" 
                          :class="link.active ? 'bg-skin-primary text-skin-primary-text shadow-sm' : 'text-skin-muted hover:bg-skin-fill hover:text-skin-base'"/>
                    <span v-else v-html="link.label" class="px-3 py-1.5 rounded text-xs text-skin-muted/50"></span>
                </template>
            </div>
        </div>

    </AdminLayout>
</template>