<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import { 
    Plus, Trash2, Calendar, FileText, Factory, MapPin, 
    Save, ArrowLeft, CreditCard, Package, ShieldAlert, Zap
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    providers: Array,
    skus: Array
});

const form = useForm({
    branch_id: '',
    provider_id: '',
    document_number: '', // Ahora es automático
    purchase_date: new Date().toISOString().split('T')[0],
    payment_type: 'CASH',
    is_emergency: false, // Switch global de tipo de documento
    notes: '',
    total_amount: 0,
    items: []
});

// Generador de ID corto aleatorio para evitar colisiones en sesión
const shortId = () => Math.random().toString(36).substring(2, 6).toUpperCase();

// LOGICA: Generación de Documento y Lotes
const updateAutomatedFields = () => {
    const datePart = new Date().toISOString().slice(2, 10).replace(/-/g, '');
    const prefixDoc = form.is_emergency ? 'EMG' : 'CMP';
    
    // 1. Actualizar Nro Documento de cabecera
    form.document_number = `${prefixDoc}-${datePart}-${shortId()}`;

    // 2. Sincronizar todos los items al tipo de la cabecera por defecto
    form.items.forEach(item => {
        item.is_safety_stock = form.is_emergency;
        const prefixLot = item.is_safety_stock ? 'RELOT' : 'LOT';
        item.lot_code = `${prefixLot}-${datePart}-${shortId()}`;
    });
};

function createEmptyItem() {
    return { 
        sku_id: '', 
        quantity_input: 1,      
        total_cost_input: 0,    
        quantity: 1,            
        unit_cost: 0,           
        expiration_date: null,
        is_safety_stock: form.is_emergency,
        lot_code: '' 
    };
}

onMounted(() => {
    if (props.branches.length === 1) form.branch_id = props.branches[0].id;
    addItem();
    updateAutomatedFields();
});

// Vigilar el switch de emergencia para regenerar todo
watch(() => form.is_emergency, updateAutomatedFields);

const addItem = () => {
    const item = createEmptyItem();
    form.items.push(item);
    updateAutomatedFields(); // Asigna código al nuevo item
};

const calculateRow = (item) => {
    item.quantity = item.quantity_input || 0;
    item.unit_cost = item.quantity > 0 ? (item.total_cost_input / item.quantity) : 0;
};

const grandTotal = computed(() => form.items.reduce((acc, item) => acc + (item.total_cost_input || 0), 0));
watch(grandTotal, (newTotal) => form.total_amount = newTotal);

const submit = () => {
    form.transform((data) => ({
        ...data,
        items: data.items.map(item => ({
            sku_id: item.sku_id,
            quantity: item.quantity,
            unit_cost: item.unit_cost,
            is_safety_stock: item.is_safety_stock,
            lot_code: item.lot_code,
            expiration_date: item.expiration_date
        }))
    })).post(route('admin.purchases.store'));
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto pb-24 px-4">
            
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b border-border py-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <Link :href="route('admin.purchases.index')" class="btn btn-ghost btn-sm btn-square text-muted-foreground"><ArrowLeft :size="20" /></Link>
                    <div>
                        <h1 class="text-xl font-black text-foreground tracking-tighter">Nueva Operación</h1>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">
                                {{ form.document_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex bg-muted/50 p-1 rounded-xl border border-border w-full md:w-auto">
                    <button type="button" @click="form.is_emergency = false" 
                        class="flex-1 md:flex-none px-6 py-2 text-[10px] font-black rounded-lg transition-all flex items-center justify-center gap-2"
                        :class="!form.is_emergency ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground'">
                        <Package :size="14"/> COMPRA ORDINARIA
                    </button>
                    <button type="button" @click="form.is_emergency = true" 
                        class="flex-1 md:flex-none px-6 py-2 text-[10px] font-black rounded-lg transition-all flex items-center justify-center gap-2"
                        :class="form.is_emergency ? 'bg-background text-orange-600 shadow-sm' : 'text-muted-foreground'">
                        <Zap :size="14"/> STOCK EMERGENCIA
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <div v-if="Object.keys(form.errors).length > 0" class="bg-red-500/10 border border-red-500/20 p-4 rounded-xl flex items-start gap-3">
                    <AlertCircle :size="20" class="text-red-500 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <h4 class="text-sm font-black text-red-500 uppercase tracking-widest">Error de Validación</h4>
                        <ul class="text-xs text-red-400 font-medium list-disc pl-4">
                            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="card p-4 space-y-1" :class="{'border-red-500 bg-red-500/5': form.errors.branch_id}">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground">Sucursal de Destino</label>
                        <select v-model="form.branch_id" class="form-input w-full h-11 text-sm bg-muted/20">
                            <option value="" disabled>Seleccione sede...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="text-[9px] text-red-500 font-bold mt-1">{{ form.errors.branch_id }}</p>
                    </div>
                    
                    <div class="card p-4 space-y-1" :class="{'border-red-500 bg-red-500/5': form.errors.provider_id}">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground">Proveedor Emisor</label>
                        <select v-model="form.provider_id" class="form-input w-full h-11 text-sm bg-muted/20">
                            <option value="" disabled>Seleccione proveedor...</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                        </select>
                        <p v-if="form.errors.provider_id" class="text-[9px] text-red-500 font-bold mt-1">{{ form.errors.provider_id }}</p>
                    </div>
                    
                    <div class="card p-4 space-y-1" :class="{'border-red-500 bg-red-500/5': form.errors.purchase_date}">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground">Fecha de Operación</label>
                        <input v-model="form.purchase_date" type="date" class="form-input w-full h-11 text-sm bg-muted/20" />
                        <p v-if="form.errors.purchase_date" class="text-[9px] text-red-500 font-bold mt-1">{{ form.errors.purchase_date }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xs font-black uppercase text-muted-foreground tracking-widest px-1">Artículos en Partida</h3>
                    
                    <div v-for="(item, index) in form.items" :key="index" 
                        class="card overflow-hidden border-l-4 transition-all hover:shadow-md"
                        :class="item.is_safety_stock ? 'border-l-orange-500' : 'border-l-primary'">
                        
                        <div class="p-4 grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
                            <div class="lg:col-span-4 space-y-1">
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground" :class="{'text-red-500': form.errors[`items.${index}.sku_id`]}">SKU / Producto</label>
                                    <span class="text-[9px] font-mono font-bold text-muted-foreground/60">{{ item.lot_code }}</span>
                                </div>
                                <select v-model="item.sku_id" class="form-input w-full h-10 text-xs font-medium" :class="{'border-red-500': form.errors[`items.${index}.sku_id`]}">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.full_name }}</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-3 lg:col-span-5 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground text-center block" :class="{'text-red-500': form.errors[`items.${index}.quantity`]}">Cant.</label>
                                    <input v-model.number="item.quantity_input" @input="calculateRow(item)" type="number" class="form-input w-full h-10 text-center font-bold" :class="{'border-red-500': form.errors[`items.${index}.quantity`]}" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground text-center block">Subtotal (Bs)</label>
                                    <input v-model.number="item.total_cost_input" @input="calculateRow(item)" type="number" step="0.01" class="form-input w-full h-10 text-center font-black text-emerald-600 bg-emerald-50/10 border-emerald-200" />
                                </div>
                                <div class="hidden lg:block space-y-1 opacity-60">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground text-center block">Costo Unit.</label>
                                    <div class="h-10 flex items-center justify-center text-xs font-mono border border-dashed rounded-lg">
                                        {{ item.unit_cost.toFixed(2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-3 flex items-center gap-3">
                                <div class="flex-1 space-y-1">
                                    <label class="text-[9px] font-black uppercase text-muted-foreground block">Vencimiento</label>
                                    <input v-model="item.expiration_date" type="date" class="form-input w-full h-10 text-xs" />
                                </div>
                                <button type="button" @click="form.items.splice(index, 1)" v-if="form.items.length > 1" class="btn btn-ghost text-red-400 hover:text-red-600 self-end h-10">
                                    <Trash2 :size="18" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="addItem" class="w-full py-4 border-2 border-dashed border-border rounded-xl flex items-center justify-center gap-2 text-muted-foreground hover:text-primary hover:bg-primary/5 transition-all font-black text-xs uppercase tracking-widest">
                        <Plus :size="18" /> Añadir Línea de Producto
                    </button>
                </div>

                <div class="pt-8 border-t border-border flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="w-full md:max-w-md">
                        <label class="text-[10px] font-bold uppercase text-muted-foreground ml-1">Observaciones de Auditoría</label>
                        <textarea v-model="form.notes" class="form-input w-full h-20 text-sm mt-1 resize-none" placeholder="Opcional..."></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div class="px-6 py-2 bg-emerald-600/10 rounded-xl border border-emerald-600/20 text-right order-last sm:order-none" :class="{'border-red-500 bg-red-500/10': form.errors.total_amount}">
                            <span class="text-[9px] font-black text-emerald-600 uppercase block" :class="{'text-red-500': form.errors.total_amount}">Total Final</span>
                            <span class="text-xl font-mono font-black text-emerald-700" :class="{'text-red-600': form.errors.total_amount}">{{ grandTotal.toFixed(2) }}</span>
                        </div>
                        <button type="submit" :disabled="form.processing" class="btn btn-primary h-14 px-12 font-black uppercase tracking-widest shadow-lg shadow-primary/20 flex items-center justify-center gap-3">
                            <Save :size="20"/> 
                            {{ form.processing ? 'PROCESANDO...' : 'REGISTRAR INGRESO' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>