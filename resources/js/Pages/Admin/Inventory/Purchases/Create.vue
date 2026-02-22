<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import { 
    Plus, Trash2, Calendar, DollarSign, FileText, 
    Factory, MapPin, Save, ArrowLeft, CreditCard,
    Package, AlertCircle, Calculator
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    providers: Array,
    skus: Array
});

const form = useForm({
    branch_id: '',
    provider_id: '',
    document_number: '',
    purchase_date: new Date().toISOString().split('T')[0],
    payment_type: 'CASH', // CASH, CREDIT
    payment_due_date: '',
    notes: '',
    items: [ createEmptyItem() ]
});

// Autoseleccionar sucursal si solo hay una
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
// ... dentro de tu script setup ...

// Sincroniza el total_amount del formulario con el grandTotal computado
watch(grandTotal, (newTotal) => {
    form.total_amount = newTotal;
}, { immediate: true });

const submit = () => {
    // Transformamos los datos para cumplir con el DTO del Backend (Zero-Trust)
    form.transform((data) => ({
        ...data,
        total_amount: grandTotal.value, // Aseguramos el total final
        items: data.items.map(item => ({
            sku_id: item.sku_id,
            quantity: item.quantity, // Enviamos el valor procesado, no el input
            unit_cost: item.unit_cost,
            expiration_date: item.expiration_date,
            lot_code: item.lot_code || null
        }))
    })).post(route('admin.purchases.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Manejo de éxito
        }
    });
};
</script>

<template>
    <AdminLayout>
        
        <div class="max-w-5xl mx-auto pb-40 md:pb-12">
            
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b border-border px-4 py-4 mb-6 flex flex-col gap-4 shadow-sm transition-all duration-300">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.purchases.index')" class="btn btn-ghost btn-sm btn-square -ml-2 text-muted-foreground hover:bg-muted">
                            <ArrowLeft :size="22" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-display font-black text-foreground leading-none tracking-tight">
                                Registrar Ingreso
                            </h1>
                            <p class="text-xs text-muted-foreground mt-1">Nueva Compra</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">Total</span>
                        <div class="flex items-center text-emerald-500">
                            <span class="text-xs font-bold mr-1">Bs</span>
                            <span class="text-xl font-mono font-black tracking-tight">{{ grandTotal.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="px-4 md:px-0 space-y-6">
                
                <div v-if="form.errors.error" class="bg-error/10 border border-error/20 text-error p-4 rounded-xl flex items-center gap-3 animate-in slide-in-from-top-2">
                    <AlertCircle :size="20"/>
                    <span class="font-bold text-sm">{{ form.errors.error }}</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="card bg-card border border-border shadow-sm p-5 space-y-4">
                        <div class="flex items-center gap-2 mb-2 border-b border-border pb-2">
                            <Factory :size="16" class="text-primary"/>
                            <h3 class="text-xs font-black uppercase text-muted-foreground tracking-wider">Logística</h3>
                        </div>

                        <div class="space-y-3">
                            <div v-if="branches.length > 1" class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground">Sucursal Destino</label>
                                <div class="relative group">
                                    <MapPin :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                    <select v-model="form.branch_id" class="form-input w-full pl-9 h-10 text-sm bg-muted/10 cursor-pointer" :class="{'border-error': form.errors.branch_id}">
                                        <option value="" disabled>Seleccionar...</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.branch_id" class="form-error">{{ form.errors.branch_id }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground">Proveedor</label>
                                <div class="relative group">
                                    <Factory :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                    <select v-model="form.provider_id" class="form-input w-full pl-9 h-10 text-sm bg-muted/10 cursor-pointer" :class="{'border-error': form.errors.provider_id}">
                                        <option value="" disabled>Seleccionar...</option>
                                        <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.provider_id" class="form-error">{{ form.errors.provider_id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-card border border-border shadow-sm p-5 space-y-4">
                        <div class="flex items-center gap-2 mb-2 border-b border-border pb-2">
                            <FileText :size="16" class="text-blue-500"/>
                            <h3 class="text-xs font-black uppercase text-muted-foreground tracking-wider">Documentación</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground">Nro. Factura / Recibo</label>
                                <div class="relative group">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-mono font-bold text-xs">#</span>
                                    <input v-model="form.document_number" type="text" class="form-input w-full pl-8 h-10 text-sm font-mono" :class="{'border-error': form.errors.document_number}" placeholder="000-000">
                                </div>
                                <p v-if="form.errors.document_number" class="form-error">{{ form.errors.document_number }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase text-muted-foreground">Fecha Emisión</label>
                                <div class="relative group">
                                    <Calendar :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                    <input v-model="form.purchase_date" type="date" class="form-input w-full pl-9 h-10 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-card border border-border shadow-sm p-5 space-y-4">
                        <div class="flex items-center gap-2 mb-2 border-b border-border pb-2">
                            <CreditCard :size="16" class="text-emerald-500"/>
                            <h3 class="text-xs font-black uppercase text-muted-foreground tracking-wider">Pago</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="flex p-1 bg-muted/30 rounded-lg border border-border">
                                <button type="button" @click="form.payment_type = 'CASH'" 
                                        class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2"
                                        :class="form.payment_type === 'CASH' ? 'bg-background text-emerald-600 shadow-sm border border-emerald-200' : 'text-muted-foreground hover:text-foreground'">
                                    <DollarSign :size="12"/> Contado
                                </button>
                                <button type="button" @click="form.payment_type = 'CREDIT'" 
                                        class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2"
                                        :class="form.payment_type === 'CREDIT' ? 'bg-background text-orange-600 shadow-sm border border-orange-200' : 'text-muted-foreground hover:text-foreground'">
                                    <Calendar :size="12"/> Crédito
                                </button>
                            </div>

                            <div v-if="form.payment_type === 'CREDIT'" class="space-y-1 animate-in slide-in-from-top-2">
                                <label class="text-[10px] font-bold uppercase text-orange-600">Vencimiento del Pago</label>
                                <input v-model="form.payment_due_date" type="date" class="form-input w-full h-10 text-sm border-orange-200 focus:border-orange-500 focus:ring-orange-500/20">
                                <p v-if="form.errors.payment_due_date" class="form-error">{{ form.errors.payment_due_date }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card bg-card border border-border shadow-sm overflow-hidden flex flex-col">
                    
                    <div class="p-4 border-b border-border bg-muted/20 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <Package :size="18" class="text-primary"/>
                            <h3 class="font-bold text-foreground text-sm uppercase tracking-wide">Productos</h3>
                        </div>
                        <span class="badge badge-outline text-[10px]">{{ form.items.length }} Líneas</span>
                    </div>

                    <div class="p-5 flex-1 space-y-4">
                        
                        <div v-for="(item, index) in form.items" :key="index" 
                             class="flex flex-col md:flex-row gap-4 p-4 rounded-xl border border-border bg-background hover:border-primary/30 transition-colors shadow-sm relative group">
                            
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-muted group-hover:bg-primary transition-colors"></div>
                            <span class="absolute top-2 left-2 text-[10px] font-mono text-muted-foreground/50 font-bold">#{{ index + 1 }}</span>

                            <div class="w-full md:flex-1 space-y-1">
                                <label class="text-[10px] text-muted-foreground font-bold uppercase ml-1">Producto / SKU</label>
                                <select v-model="item.sku_id" @change="onSkuChange(item)" class="form-input w-full h-10 text-xs bg-muted/10 cursor-pointer">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="sku in skus" :key="sku.id" :value="sku.id">
                                        {{ sku.full_name }} {{ sku.factor > 1 ? '(x'+sku.factor+')' : '' }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid grid-cols-3 gap-3 w-full md:w-auto">
                                
                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground font-bold uppercase text-center block">Cant.</label>
                                    <input v-model.number="item.quantity_input" @input="calculateRow(item)" type="number" min="1" class="form-input w-full h-10 text-center font-bold text-sm">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground font-bold uppercase text-center block">Costo Total</label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">Bs</span>
                                        <input v-model.number="item.total_cost_input" @input="calculateRow(item)" type="number" step="0.01" class="form-input w-full pl-6 h-10 text-right font-bold text-sm bg-emerald-50/50 border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground font-bold uppercase text-center block opacity-70">Unitario</label>
                                    <div class="relative">
                                        <div class="form-input w-full h-10 flex items-center justify-end px-3 bg-muted/30 text-xs font-mono text-muted-foreground border-transparent">
                                            {{ item.unit_cost.toFixed(2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-32 space-y-1">
                                <label class="text-[10px] text-muted-foreground font-bold uppercase block">Vencimiento</label>
                                <input v-model="item.expiration_date" type="date" class="form-input w-full h-10 text-xs">
                            </div>

                            <div class="flex items-end justify-end md:justify-center">
                                <button type="button" @click="removeItem(index)" 
                                        class="btn btn-square btn-ghost h-10 w-10 text-muted-foreground hover:text-error hover:bg-error/10 transition-colors"
                                        :disabled="form.items.length === 1"
                                        :class="{'opacity-50 cursor-not-allowed': form.items.length === 1}">
                                    <Trash2 :size="18"/>
                                </button>
                            </div>

                        </div>

                        <button type="button" @click="addItem" class="w-full py-3 border-2 border-dashed border-border rounded-xl flex items-center justify-center gap-2 text-muted-foreground hover:text-primary hover:border-primary/50 hover:bg-primary/5 transition-all group">
                            <Plus :size="18" class="group-hover:scale-110 transition-transform"/>
                            <span class="text-sm font-bold">Agregar otro producto</span>
                        </button>

                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold uppercase text-muted-foreground">Notas Adicionales</label>
                    <textarea v-model="form.notes" class="form-input w-full resize-none text-sm h-20" placeholder="Observaciones sobre la recepción o factura..."></textarea>
                </div>

                <div class="pt-6 border-t border-border flex flex-col md:flex-row justify-end gap-3">
                    <Link :href="route('admin.purchases.index')" class="btn btn-outline h-12 px-6 font-bold text-muted-foreground w-full md:w-auto order-2 md:order-1">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary h-12 px-8 font-black uppercase tracking-widest shadow-lg shadow-primary/20 w-full md:w-auto order-1 md:order-2 flex items-center justify-center gap-2">
                        <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                        <span v-else class="flex items-center gap-2">
                            <Save :size="18"/> Confirmar Ingreso
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>