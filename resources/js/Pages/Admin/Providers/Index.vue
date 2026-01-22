<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';
    
    // Iconos
    import { 
        Search, Plus, MapPin, Phone, Mail, Clock, 
        DollarSign, CreditCard, Edit, Trash2, Building2 
    } from 'lucide-vue-next';

    // 1. Definir PROPS primero
    const props = defineProps({ 
        providers: Object, 
        filters: Object,
        can_manage: Boolean 
    });

    // 2. Definir variables reactivas
    const search = ref(props.filters.search || '');
    const filteredProviders = ref(props.providers.data); // Ojo: providers viene paginado (.data)

    // 3. Watcher para búsqueda (Usando el servidor, no local)
    // Es mejor filtrar en servidor con Inertia para paginación correcta
    watch(search, debounce((val) => {
        router.get(route('admin.providers.index'), { search: val }, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300));
    
    // Actualizar la lista local cuando cambian los props (por si acaso)
    watch(() => props.providers, (newVal) => {
        filteredProviders.value = newVal.data;
    }, { deep: true });

    const deleteItem = (provider) => {
        if(confirm(`¿Eliminar a ${provider.commercial_name || provider.company_name}?`)) {
            router.delete(route('admin.providers.destroy', provider.id));
        }
    };
</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-black text-skin-base tracking-tight">Proveedores</h1>
                <p class="text-skin-muted text-sm mt-1">Gestión de socios comerciales y cadena de suministro</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search :size="16" class="text-skin-muted" />
                    </div>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Buscar proveedor..." 
                        class="pl-10 w-full bg-skin-fill-card border border-skin-border text-skin-base text-sm rounded-global focus:ring-2 focus:ring-skin-primary focus:border-skin-primary placeholder-skin-muted/50 transition-all shadow-sm"
                    >
                </div>

                <Link v-if="can_manage" :href="route('admin.providers.create')" 
                      class="flex items-center justify-center gap-2 bg-skin-primary text-skin-primary-text px-4 py-2 rounded-global text-sm font-bold shadow-sm hover:brightness-110 transition-all active:scale-95 whitespace-nowrap">
                    <Plus :size="16" />
                    <span>Nuevo</span>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            
            <div v-for="prov in providers.data" :key="prov.id" 
                 class="group bg-skin-fill-card border border-skin-border rounded-global shadow-sm hover:shadow-md hover:border-skin-primary/30 transition-all duration-300 flex flex-col relative overflow-hidden">
                
                <div class="absolute top-0 left-0 w-1 h-full" 
                     :class="prov.is_active ? 'bg-skin-success' : 'bg-skin-danger'"></div>

                <div class="p-5 border-b border-skin-border/50">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded bg-skin-fill text-skin-primary shrink-0">
                                <Building2 :size="20" />
                            </div>
                            <div>
                                <h3 class="font-bold text-skin-base text-lg leading-tight">
                                    {{ prov.commercial_name || prov.company_name }}
                                </h3>
                                <p v-if="prov.commercial_name" class="text-xs text-skin-muted mt-0.5 font-medium">
                                    {{ prov.company_name }}
                                </p>
                                <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-skin-fill border border-skin-border text-skin-muted">
                                    NIT: {{ prov.tax_id }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4 flex-1">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-skin-fill/50 p-2 rounded border border-skin-border/50">
                            <div class="flex items-center gap-1.5 text-xs text-skin-muted mb-1">
                                <Clock :size="12" /> Tiempo Entrega
                            </div>
                            <div class="font-bold text-skin-base text-sm">{{ prov.lead_time_days }} días</div>
                        </div>
                        <div class="bg-skin-fill/50 p-2 rounded border border-skin-border/50">
                            <div class="flex items-center gap-1.5 text-xs text-skin-muted mb-1">
                                <DollarSign :size="12" /> Min. Compra
                            </div>
                            <div class="font-bold text-skin-base text-sm">Bs {{ prov.min_order_value }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <CreditCard :size="14" class="text-skin-muted" />
                        <span v-if="prov.credit_days > 0" class="text-skin-success font-bold">
                            Crédito: {{ prov.credit_days }} días (Bs {{ prov.credit_limit }})
                        </span>
                        <span v-else class="text-skin-danger font-bold">Pago al Contado</span>
                    </div>

                    <div class="pt-3 border-t border-skin-border/50 space-y-2">
                        <div v-if="prov.contact_name" class="text-xs font-bold text-skin-base">
                            {{ prov.contact_name }}
                        </div>
                        <div v-if="prov.email_orders" class="flex items-center gap-2 text-xs text-skin-muted truncate hover:text-skin-primary transition-colors cursor-pointer" title="Copiar correo">
                            <Mail :size="14" /> {{ prov.email_orders }}
                        </div>
                        <div v-if="prov.phone" class="flex items-center gap-2 text-xs text-skin-muted">
                            <Phone :size="14" /> {{ prov.phone }}
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-skin-fill/30 border-t border-skin-border flex justify-between items-center">
                    <div class="text-[10px] font-mono text-skin-muted">
                        ERP: {{ prov.internal_code || 'N/A' }}
                    </div>
                    
                    <div v-if="can_manage" class="flex items-center gap-2">
                        <Link :href="route('admin.providers.edit', prov.id)" 
                              class="p-1.5 rounded text-skin-muted hover:text-skin-primary hover:bg-skin-primary/10 transition-colors"
                              title="Editar">
                            <Edit :size="16" />
                        </Link>
                        <button @click="deleteItem(prov)" 
                                class="p-1.5 rounded text-skin-muted hover:text-skin-danger hover:bg-skin-danger/10 transition-colors"
                                title="Eliminar">
                            <Trash2 :size="16" />
                        </button>
                    </div>
                </div>

            </div>

            <div v-if="providers.data.length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-skin-muted border-2 border-dashed border-skin-border rounded-global">
                <Search :size="48" class="mb-4 opacity-20" />
                <p class="font-medium">No se encontraron proveedores</p>
                <p class="text-sm opacity-70">Intenta con otro término de búsqueda</p>
            </div>
            
        </div>
        
        </AdminLayout>
</template>