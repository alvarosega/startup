<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { computed, onMounted } from 'vue';
    import { Plus, Trash2, Calendar, DollarSign, FileText } from 'lucide-vue-next';
    
    const props = defineProps({
        branches: Array, // Si soy Branch Admin, este array trae solo 1 elemento
        providers: Array,
        skus: Array
    });
    
    const form = useForm({
        branch_id: '',
        provider_id: '',
        document_number: '',
        purchase_date: new Date().toISOString().split('T')[0],
        
        // --- NUEVOS CAMPOS ---
        payment_type: 'CASH', // CASH, CREDIT
        payment_due_date: '', // Requerido solo si es CREDIT
        // ---------------------

        notes: '',
        items: [ createEmptyItem() ]
    });

    // Autoseleccionar sucursal si solo hay una (Branch Admin)
    onMounted(() => {
        if (props.branches.length === 1) {
            form.branch_id = props.branches[0].id;
        }
    });
    
    function createEmptyItem() {
        return { 
            sku_id: '', 
            quantity_input: 1,      
            total_cost_input: 0,    
            factor: 1,
            sku_name: '',
            quantity: 1,            
            unit_cost: 0,           
            expiration_date: null 
        };
    }
    
    const addItem = () => form.items.push(createEmptyItem());
    
    const removeItem = (index) => {
        if (form.items.length > 1) form.items.splice(index, 1);
    };
    
    const onSkuChange = (item) => {
        const skuInfo = props.skus.find(s => s.id === item.sku_id);
        if (skuInfo) {
            item.factor = skuInfo.factor;
            item.sku_name = skuInfo.full_name;
        }
        calculateRow(item);
    };
    
    const calculateRow = (item) => {
        item.quantity = item.quantity_input || 0;
        if (item.quantity > 0 && item.total_cost_input > 0) {
            item.unit_cost = item.total_cost_input / item.quantity;
        } else {
            item.unit_cost = 0;
        }
    };
    
    const grandTotal = computed(() => {
        return form.items.reduce((acc, item) => acc + (item.total_cost_input || 0), 0);
    });
    
    const submit = () => {
        form.post(route('admin.purchases.store'), {
            preserveScroll: true,
            onError: (e) => console.error(e)
        });
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-5xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-black text-skin-base tracking-tight">Registrar Ingreso</h1>
                    <p class="text-skin-muted text-sm mt-1">Ingreso de mercadería y cuenta por pagar.</p>
                </div>
                <div class="text-right bg-skin-fill-card p-4 rounded-global border border-skin-border shadow-sm w-full md:w-auto">
                    <p class="text-[10px] text-skin-muted uppercase font-bold tracking-wider">Total a Pagar</p>
                    <p class="text-3xl font-mono font-bold text-skin-success">Bs {{ grandTotal.toFixed(2) }}</p>
                </div>
            </div>

            <div v-if="form.errors.error" class="mb-6 bg-skin-danger/10 border-l-4 border-skin-danger text-skin-danger p-4 font-bold text-sm rounded-r-global">
                {{ form.errors.error }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="bg-skin-fill-card p-6 rounded-global border border-skin-border shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div class="space-y-4">
                            <div v-if="branches.length > 1">
                                <label class="block text-skin-muted text-xs uppercase font-bold mb-2">Sucursal Destino</label>
                                <select v-model="form.branch_id" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2.5 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <p v-if="form.errors.branch_id" class="text-skin-danger text-[10px] mt-1 font-bold">Requerido</p>
                            </div>
                            
                            <div>
                                <label class="block text-skin-muted text-xs uppercase font-bold mb-2">Proveedor</label>
                                <select v-model="form.provider_id" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2.5 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                                </select>
                                <p v-if="form.errors.provider_id" class="text-skin-danger text-[10px] mt-1 font-bold">Requerido</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-skin-muted text-xs uppercase font-bold mb-2">Nro Factura / Doc</label>
                                <div class="relative">
                                    <FileText :size="16" class="absolute left-3 top-3 text-skin-muted pointer-events-none" />
                                    <input v-model="form.document_number" type="text" class="w-full pl-9 bg-skin-fill border border-skin-border text-skin-base rounded-global p-2.5 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none font-mono">
                                </div>
                                <p v-if="form.errors.document_number" class="text-skin-danger text-[10px] mt-1 font-bold">Requerido</p>
                            </div>
                            <div>
                                <label class="block text-skin-muted text-xs uppercase font-bold mb-2">Fecha Emisión</label>
                                <input v-model="form.purchase_date" type="date" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2.5 text-sm focus:ring-2 focus:ring-skin-primary/50 outline-none">
                            </div>
                        </div>

                        <div class="space-y-4 bg-skin-fill/30 p-4 rounded-global border border-skin-border/50">
                            <div>
                                <label class="block text-skin-muted text-xs uppercase font-bold mb-2">Condición de Pago</label>
                                <div class="flex gap-2">
                                    <button type="button" @click="form.payment_type = 'CASH'" 
                                            class="flex-1 py-2 text-xs font-bold rounded border transition-all"
                                            :class="form.payment_type === 'CASH' ? 'bg-green-500 text-white border-green-500 shadow-sm' : 'bg-skin-fill text-skin-muted border-skin-border'">
                                        CONTADO
                                    </button>
                                    <button type="button" @click="form.payment_type = 'CREDIT'" 
                                            class="flex-1 py-2 text-xs font-bold rounded border transition-all"
                                            :class="form.payment_type === 'CREDIT' ? 'bg-orange-500 text-white border-orange-500 shadow-sm' : 'bg-skin-fill text-skin-muted border-skin-border'">
                                        CRÉDITO
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="form.payment_type === 'CREDIT'">
                                <label class="block text-orange-500 text-xs uppercase font-bold mb-2">Fecha Vencimiento Pago</label>
                                <input v-model="form.payment_due_date" type="date" class="w-full bg-white border border-orange-300 text-skin-base rounded-global p-2.5 text-sm focus:ring-2 focus:ring-orange-500/50 outline-none shadow-inner">
                                <p v-if="form.errors.payment_due_date" class="text-skin-danger text-[10px] mt-1 font-bold">Requerido para créditos</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="bg-skin-fill-card rounded-global border border-skin-border overflow-hidden shadow-sm">
                    <div class="p-3 bg-skin-fill border-b border-skin-border flex justify-between items-center">
                        <h3 class="text-xs font-black text-skin-muted uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 bg-skin-primary rounded-full"></span> Detalle de Compra
                        </h3>
                        <button type="button" @click="addItem" class="text-skin-primary hover:text-skin-primary-hover text-xs font-bold flex items-center gap-1 transition">
                            <Plus :size="14" /> Agregar Línea
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[800px]">
                            <thead class="bg-skin-fill text-skin-muted text-[10px] uppercase font-bold">
                                <tr>
                                    <th class="px-4 py-2 w-10">#</th>
                                    <th class="px-4 py-2">Producto (SKU)</th>
                                    <th class="px-4 py-2 w-32 text-center">Cantidad</th>
                                    <th class="px-4 py-2 w-40 text-right">Costo Total</th>
                                    <th class="px-4 py-2 w-32 text-right">Costo Unit.</th>
                                    <th class="px-4 py-2 w-40">Vencimiento (Lote)</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-skin-border text-sm">
                                <tr v-for="(item, index) in form.items" :key="index" class="group hover:bg-skin-fill/50">
                                    <td class="px-4 py-2 text-center text-skin-muted text-xs">{{ index + 1 }}</td>
                                    
                                    <td class="px-4 py-2">
                                        <select v-model="item.sku_id" @change="onSkuChange(item)" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded p-1.5 text-sm focus:border-skin-primary outline-none">
                                            <option value="" disabled>Seleccionar...</option>
                                            <option v-for="sku in skus" :key="sku.id" :value="sku.id">
                                                {{ sku.full_name }} {{ sku.factor > 1 ? '(x'+sku.factor+')' : '(Unid)' }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors[`items.${index}.sku_id`]" class="text-skin-danger text-[9px] mt-1 font-bold">Selecciona un producto</p>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input v-model.number="item.quantity_input" @input="calculateRow(item)" type="number" min="1" class="w-full bg-skin-fill border border-skin-border text-skin-base font-bold rounded p-1.5 text-center focus:border-skin-primary outline-none">
                                        <div class="text-[9px] text-skin-muted text-center mt-0.5">
                                            {{ item.factor > 1 ? 'Packs' : 'Unidades' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-2">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1.5 text-skin-muted text-xs">Bs</span>
                                            <input v-model.number="item.total_cost_input" @input="calculateRow(item)" type="number" step="0.01" class="w-full bg-skin-fill border border-skin-border text-skin-base font-bold rounded p-1.5 text-right pl-6 focus:border-skin-primary outline-none">
                                        </div>
                                    </td>

                                    <td class="px-4 py-2 text-right">
                                        <div class="font-mono text-skin-muted">{{ item.unit_cost.toFixed(2) }}</div>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input v-model="item.expiration_date" type="date" class="w-full bg-skin-fill border border-skin-border text-skin-muted text-xs rounded p-1 focus:border-skin-primary outline-none">
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-skin-muted hover:text-skin-danger transition p-1 rounded hover:bg-skin-danger/10">
                                            <Trash2 :size="16" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-8 text-center text-skin-muted text-sm border-t border-skin-border bg-skin-fill/20" v-if="form.items.length === 0">
                        No hay productos en la lista. <button type="button" @click="addItem" class="text-skin-primary font-bold underline">Agregar uno</button>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-skin-border">
                    <Link :href="route('admin.purchases.index')" class="px-6 py-3 text-skin-muted font-bold hover:text-skin-base transition">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="bg-skin-primary hover:brightness-110 text-skin-primary-text font-bold py-3 px-8 rounded-global shadow-md transition disabled:opacity-50 flex items-center gap-2">
                        <span v-if="form.processing">Guardando...</span>
                        <span v-else>Confirmar Ingreso</span>
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>