<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    ChevronDown, User, MapPin, 
    FileText, Package, Factory,
    CreditCard, AlertCircle, CheckCircle2,
    Calendar, DollarSign, Plus
} from 'lucide-vue-next';

const props = defineProps({ 
    purchases: Object,
    is_branch_admin: Boolean 
});

const expandedRows = ref([]);

const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' });
const formatMoney = (amount) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount);

// Helpers de usuario seguros
const getUserInitials = (userObj) => {
    if (!userObj) return 'SY';
    if (userObj.profile && userObj.profile.first_name) {
        return userObj.profile.first_name.substring(0, 2).toUpperCase();
    }
    return (userObj.email || userObj.phone || '??').substring(0, 2).toUpperCase();
};

const getUserName = (userObj) => {
    if (!userObj) return 'Sistema';
    if (userObj.name && userObj.name !== 'Usuario') return userObj.name;
    return userObj.email || userObj.phone;
};
</script>

<template>
    <AdminLayout>
        
        <div class="pb-40 md:pb-12 min-h-screen flex flex-col">

            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b border-border px-4 py-4 mb-6 flex flex-col gap-4 shadow-sm transition-all duration-300">
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-display font-black text-foreground tracking-tighter flex items-center gap-2">
                            Ingresos
                            <span class="text-xs font-bold text-muted-foreground bg-muted px-2 py-0.5 rounded-full border border-border">
                                {{ purchases.total }}
                            </span>
                        </h1>
                        <p class="text-xs text-muted-foreground font-medium mt-0.5">Gestión de abastecimiento y costos.</p>
                    </div>
                </div>
            </div>

            <div class="px-4 md:px-0 max-w-7xl mx-auto w-full space-y-4">
                
                <div v-for="p in purchases.data" :key="p.id" 
                     class="card group bg-card border border-border hover:border-primary/40 transition-all duration-300 overflow-hidden relative">
                    
                    <div @click="toggleRow(p.id)" class="p-4 cursor-pointer relative z-10 bg-gradient-to-r from-transparent to-muted/5 hover:to-primary/5 transition-colors">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300"
                             :class="p.payment_type === 'CREDIT' ? 'bg-orange-500' : 'bg-emerald-500'"></div>

                        <div class="flex justify-between items-start gap-4">
                            
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-xs text-muted-foreground bg-muted/30 px-1.5 py-0.5 rounded border border-border">
                                        #{{ p.document_number }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider" 
                                          :class="p.payment_type === 'CREDIT' ? 'text-orange-500' : 'text-emerald-500'">
                                        {{ p.payment_type === 'CREDIT' ? 'Crédito' : 'Contado' }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-foreground text-base leading-tight">
                                    {{ p.provider ? p.provider.commercial_name : 'Proveedor Desconocido' }}
                                </h3>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
                                    <Calendar :size="12"/> {{ formatDate(p.purchase_date) }}
                                    <span v-if="!is_branch_admin" class="flex items-center gap-1 ml-2 text-primary">
                                        <MapPin :size="12"/> {{ p.branch ? p.branch.name : '---' }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right flex flex-col items-end justify-between h-full gap-2">
                                <div class="font-mono font-black text-lg text-foreground tracking-tight">
                                    {{ formatMoney(p.total_amount) }}
                                </div>
                                <div class="bg-muted/30 p-1.5 rounded-full text-muted-foreground transition-transform duration-300"
                                     :class="{'rotate-180 text-primary bg-primary/10': expandedRows.includes(p.id)}">
                                    <ChevronDown :size="16" stroke-width="2.5"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="expandedRows.includes(p.id)" class="border-t border-border/50 bg-muted/10 animate-in slide-in-from-top-2 fade-in duration-200">
                        <div class="p-4 space-y-4">
                            
                            <div class="flex flex-wrap gap-3 text-xs border-b border-border/50 pb-3">
                                <div class="flex items-center gap-2 bg-background px-2 py-1 rounded border border-border shadow-sm">
                                    <div class="w-4 h-4 rounded-full bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-[8px] text-white font-bold">
                                        {{ getUserInitials(p.user) }}
                                    </div>
                                    <span class="text-muted-foreground">{{ getUserName(p.user) }}</span>
                                </div>
                                
                                <div v-if="p.payment_type === 'CREDIT'" class="flex items-center gap-2 bg-orange-500/10 px-2 py-1 rounded border border-orange-500/20 text-orange-600 font-bold">
                                    <AlertCircle :size="12"/> Vence: {{ formatDate(p.payment_due_date) }}
                                </div>

                                <div v-if="p.notes" class="w-full mt-1 text-muted-foreground italic flex items-start gap-1">
                                    <FileText :size="12" class="mt-0.5 shrink-0"/> "{{ p.notes }}"
                                </div>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-2 flex items-center gap-1">
                                    <Package :size="12"/> Items Ingresados
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div v-for="lot in p.inventory_lots" :key="lot.id" 
                                         class="bg-background border border-border p-2.5 rounded-lg shadow-sm flex justify-between items-center group/item hover:border-primary/30 transition-colors">
                                        <div class="overflow-hidden">
                                            <p class="font-bold text-xs text-foreground truncate max-w-[150px]" :title="lot.sku?.product?.name">
                                                {{ lot.sku?.product?.name }}
                                            </p>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-[9px] bg-muted px-1 rounded text-muted-foreground font-mono">{{ lot.sku?.code }}</span>
                                                <span class="text-[9px] text-muted-foreground">Lote: {{ lot.lot_code }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right pl-2 border-l border-border ml-2">
                                            <span class="block font-mono font-bold text-sm text-foreground">{{ lot.initial_quantity }} <span class="text-[9px] uppercase font-normal text-muted-foreground">Und</span></span>
                                            <span class="block text-[10px] text-emerald-600 font-medium">{{ formatMoney(lot.unit_cost) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div v-if="purchases.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center opacity-70">
                    <div class="w-20 h-20 bg-muted/20 rounded-full flex items-center justify-center mb-4 border border-dashed border-border">
                        <Package :size="32" class="text-muted-foreground/50" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Sin Compras</h3>
                    <p class="text-sm text-muted-foreground mt-1">No se han registrado ingresos aún.</p>
                </div>

            </div>

            <div v-if="purchases.links && purchases.links.length > 3" class="mt-8 flex justify-center px-4">
                <div class="flex gap-1 overflow-x-auto max-w-full pb-2 no-scrollbar mask-gradient-x">
                    <template v-for="(link, k) in purchases.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                              class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-xs font-bold border transition-all active:scale-95"
                              :class="link.active 
                                ? 'bg-primary text-primary-foreground border-primary shadow-md' 
                                : 'bg-card text-muted-foreground border-border hover:border-primary/50 hover:text-foreground'" />
                        <span v-else v-html="link.label" 
                              class="min-w-[36px] h-9 flex items-center justify-center rounded-lg text-xs text-muted-foreground/30 border border-transparent" />
                    </template>
                </div>
            </div>

            <Teleport to="body">
                <Link :href="route('admin.purchases.create')" 
                      class="fixed bottom-24 right-4 md:right-8 z-[9999] group predictive-aura">
                    <div class="w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-[0_8px_30px_rgba(0,240,255,0.4)] flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-active:scale-95 border-2 border-white/10 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <Plus :size="28" stroke-width="3" class="group-hover:rotate-90 transition-transform duration-300"/>
                    </div>
                    <span class="sr-only">Registrar Compra</span>
                </Link>
            </Teleport>

        </div>
    </AdminLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>