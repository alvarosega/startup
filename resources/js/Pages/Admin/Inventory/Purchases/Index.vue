<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import { ref } from 'vue';
    
    import { 
        ChevronDown, User, MapPin, 
        FileText, Package, Factory,
        CreditCard, AlertCircle, CheckCircle2 
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
    
    // Función segura para obtener iniciales
    const getUserInitials = (userObj) => {
        if (!userObj) return 'SY'; // System
        // Gracias al 'appends' en el modelo, user.name ya existe, pero por seguridad miramos el perfil
        if (userObj.profile && userObj.profile.first_name) {
            return userObj.profile.first_name.substring(0, 2).toUpperCase();
        }
        return (userObj.email || userObj.phone || '??').substring(0, 2).toUpperCase();
    };

    // Función segura para obtener el nombre
    const getUserName = (userObj) => {
        if (!userObj) return 'Sistema';
        // Prioridad: Nombre Completo > Email > Teléfono
        if (userObj.name && userObj.name !== 'Usuario') return userObj.name;
        return userObj.email || userObj.phone;
    };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-black text-skin-base tracking-tight">Ingresos y Compras</h1>
                <p class="text-skin-muted text-sm mt-1">Gestión de abastecimiento y cuentas por pagar.</p>
            </div>
            
            <Link :href="route('admin.purchases.create')" class="bg-skin-primary hover:brightness-110 text-skin-primary-text px-5 py-2.5 rounded-global font-bold shadow-sm transition flex items-center gap-2 active:scale-95">
                <span>+ Registrar Nueva Compra</span>
            </Link>
        </div>

        <div class="flex flex-col gap-4">
            
            <div v-for="p in purchases.data" :key="p.id" 
                 class="bg-skin-fill-card border border-skin-border rounded-global shadow-sm overflow-hidden transition-all duration-200 hover:border-skin-primary/30 group">
                
                <div @click="toggleRow(p.id)" class="p-4 cursor-pointer grid grid-cols-1 md:grid-cols-12 gap-4 items-center relative">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-skin-success"></div>

                    <div class="md:col-span-2 flex flex-col pr-2 md:border-r border-skin-border/50">
                        <span class="font-bold text-skin-base text-lg">{{ formatDate(p.purchase_date) }}</span>
                        <div class="flex items-center gap-1.5 text-xs text-skin-muted font-mono mt-1 bg-skin-fill w-fit px-1.5 py-0.5 rounded">
                            <FileText :size="10" /> #{{ p.document_number }}
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <span class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider flex items-center gap-1">
                            <Factory :size="10" /> Proveedor
                        </span>
                        <span class="font-bold text-skin-base text-sm truncate block mt-0.5" :title="p.provider?.commercial_name">
                            {{ p.provider ? p.provider.commercial_name : '---' }}
                        </span>
                    </div>

                    <div class="md:col-span-3">
                        <div v-if="!is_branch_admin">
                            <span class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider">Sucursal Destino</span>
                            <div class="flex items-center gap-1.5 text-sm text-skin-base truncate mt-0.5">
                                <MapPin :size="12" class="text-skin-primary" />
                                {{ p.branch ? p.branch.name : '---' }}
                            </div>
                        </div>
                        <div v-else>
                            <span class="block text-[10px] text-skin-muted uppercase font-bold tracking-wider">Condición</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span :class="p.payment_type === 'CREDIT' ? 'text-orange-500 border-orange-500/20 bg-orange-500/10' : 'text-green-500 border-green-500/20 bg-green-500/10'" 
                                      class="text-[10px] font-black px-2 py-0.5 rounded border uppercase">
                                    {{ p.payment_type === 'CREDIT' ? 'Crédito' : 'Contado' }}
                                </span>
                                <span v-if="p.payment_type === 'CREDIT'" class="text-[10px] text-skin-danger font-bold">
                                    Vence: {{ formatDate(p.payment_due_date) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-4 flex justify-between items-center pl-4 md:border-l border-skin-border/50">
                        <div class="text-right flex-1 mr-4">
                            <span class="block text-xl font-mono font-black text-skin-success tracking-tight">
                                {{ formatMoney(p.total_amount) }}
                            </span>
                            
                            <div class="flex justify-end items-center gap-3 mt-1">
                                <div class="flex items-center gap-1.5" :title="'Registrado por: ' + getUserName(p.user)">
                                    <div class="w-5 h-5 rounded-full bg-skin-muted text-skin-inverted text-[9px] flex items-center justify-center font-bold shadow-sm">
                                        {{ getUserInitials(p.user) }}
                                    </div>
                                    <span class="text-[10px] text-skin-muted truncate max-w-[100px]">
                                        {{ getUserName(p.user) }}
                                    </span>
                                </div>
                                
                                <span class="text-skin-border">|</span>
                                <span class="text-[10px] text-skin-muted font-bold">{{ p.inventory_lots_count }} Items</span>
                            </div>
                        </div>
                        
                        <div class="bg-skin-fill p-2 rounded-full text-skin-muted transition-all duration-300 group-hover:text-skin-primary"
                             :class="{'rotate-180 bg-skin-primary/10 text-skin-primary': expandedRows.includes(p.id)}">
                            <ChevronDown :size="20" />
                        </div>
                    </div>
                </div>

                <div v-if="expandedRows.includes(p.id)" class="bg-skin-fill/30 border-t border-skin-border p-5 animate-in slide-in-from-top-2 duration-200">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-xs border-b border-skin-border/50 pb-4">
                        <div class="flex items-start gap-2 text-skin-muted">
                            <FileText :size="14" class="mt-0.5 shrink-0" />
                            <span v-if="p.notes" class="italic">"{{ p.notes }}"</span>
                            <span v-else class="opacity-50">Sin notas adicionales.</span>
                        </div>
                        <div class="flex justify-end gap-4">
                            <div v-if="!is_branch_admin && p.payment_type === 'CREDIT'" class="flex items-center gap-2 bg-orange-500/5 px-2 py-1 rounded text-orange-600 border border-orange-500/10">
                                <CreditCard :size="14" /> 
                                <span class="font-bold">CRÉDITO</span>
                                <span>(Vence: {{ formatDate(p.payment_due_date) }})</span>
                            </div>
                            <div class="flex items-center gap-2 text-skin-success font-bold">
                                <CheckCircle2 :size="14" /> Ingreso Completado
                            </div>
                        </div>
                    </div>

                    <h4 class="text-[10px] font-black uppercase text-skin-muted tracking-widest mb-3">Productos Ingresados</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="lot in p.inventory_lots" :key="lot.id" 
                             class="bg-skin-fill-card border border-skin-border p-3 rounded flex justify-between items-start shadow-sm hover:border-skin-primary/40 transition">
                            <div class="overflow-hidden pr-2">
                                <p class="font-bold text-xs text-skin-base truncate" :title="lot.sku?.product?.name">
                                    {{ lot.sku?.product?.name }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] bg-skin-fill border border-skin-border px-1 rounded text-skin-muted">
                                        {{ lot.sku?.name }}
                                    </span>
                                </div>
                                <p class="text-[9px] text-skin-muted font-mono mt-1 opacity-70">
                                    Lote: {{ lot.lot_code }}
                                </p>
                            </div>
                            <div class="text-right shrink-0 border-l border-skin-border pl-3 ml-1">
                                <p class="font-mono text-lg font-bold text-skin-base leading-none">
                                    {{ lot.initial_quantity }}
                                </p>
                                <p class="text-[9px] text-skin-muted uppercase font-bold mt-0.5">Unid.</p>
                                <p class="text-[10px] text-skin-success font-mono mt-1">
                                    {{ formatMoney(lot.unit_cost) }} c/u
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div v-if="purchases.data.length === 0" class="p-16 text-center border-2 border-dashed border-skin-border rounded-global bg-skin-fill/20 text-skin-muted">
                <Package :size="48" class="mx-auto mb-4 opacity-20" />
                <h3 class="text-lg font-bold">No hay compras registradas</h3>
            </div>

        </div>

        <div class="mt-6 flex justify-center" v-if="purchases.links.length > 3">
            <div class="flex gap-1 bg-skin-fill-card p-1 rounded-global border border-skin-border shadow-sm">
                <template v-for="(link, k) in purchases.links" :key="k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" 
                          class="px-3 py-1.5 rounded text-xs font-medium transition-colors" 
                          :class="link.active ? 'bg-skin-primary text-skin-primary-text shadow-sm' : 'text-skin-muted hover:bg-skin-fill hover:text-skin-base'"/>
                    <span v-else v-html="link.label" class="px-3 py-1.5 rounded text-xs text-skin-muted/50"></span>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>