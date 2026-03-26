<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { 
    Plus, Trash2, Calendar, Factory, MapPin, 
    Save, ArrowLeft, Package, Zap, AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    branches: Array,
    providers: Array,
    skus: Array
});

// PROTOCOLO DE IDENTIDAD: La llave debe ser inmutable por intento
const generateKey = () => crypto.randomUUID();

const form = useForm({
    idempotency_key: generateKey(),
    branch_id: '',
    provider_id: '',
    purchase_date: new Date().toISOString().split('T')[0],
    payment_type: 'CASH',
    is_emergency: false, 
    notes: '',
    total_amount: 0,
    items: []
});

function createEmptyItem() {
    return { sku_id: '', quantity: 1, total_cost: 0, expiration_date: null };
}

onMounted(() => {
    if (props.branches.length === 1) form.branch_id = props.branches[0].id;
    if (form.items.length === 0) addItem();
});

const addItem = () => form.items.push(createEmptyItem());
const removeItem = (index) => form.items.length > 1 && form.items.splice(index, 1);

const grandTotal = computed(() => form.items.reduce((acc, item) => acc + (Number(item.total_cost) || 0), 0));

const submit = () => {
    form.transform((data) => ({
        ...data,
        total_amount: grandTotal.value.toFixed(2),
        items: data.items.map(item => ({
            sku_id: item.sku_id,
            quantity: parseInt(item.quantity),
            unit_cost: (Number(item.total_cost) / Number(item.quantity)).toFixed(4),
            expiration_date: item.expiration_date
        }))
    })).post(route('admin.purchases.store'), {
        // LEY: El header es la única fuente de verdad para la idempotencia
        headers: { 'X-Idempotency-Key': form.idempotency_key },
        onSuccess: () => {
            form.reset();
            form.idempotency_key = generateKey(); // REGENERACIÓN OBLIGATORIA
        },
        onError: () => {
            // Ante error (422, 500), la llave actual podría estar corrupta o usada. 
            // Se regenera para el reintento.
            form.idempotency_key = generateKey();
        }
    });
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto pb-24 px-4">
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b py-4 mb-6 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.purchases.index')" class="p-2 hover:bg-muted rounded-lg transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black uppercase tracking-tighter">Registrar Ingreso</h1>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                            ID LÓGICO: <span class="font-mono text-primary">{{ form.idempotency_key.split('-')[0] }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex bg-muted p-1 rounded-xl border">
                    <button type="button" @click="form.is_emergency = false" 
                        class="px-4 py-2 text-[10px] font-black rounded-lg transition-all flex items-center gap-2"
                        :class="!form.is_emergency ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground'">
                        <Package :size="14"/> ORDINARIO
                    </button>
                    <button type="button" @click="form.is_emergency = true" 
                        class="px-4 py-2 text-[10px] font-black rounded-lg transition-all flex items-center gap-2"
                        :class="form.is_emergency ? 'bg-background text-orange-600 shadow-sm' : 'text-muted-foreground'">
                        <Zap :size="14"/> EMERGENCIA
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-if="form.hasErrors" class="bg-red-500/10 border border-red-500/20 p-4 rounded-xl flex gap-3">
                    <AlertCircle class="text-red-500 shrink-0" :size="20" />
                    <ul class="text-xs text-red-600 font-bold list-disc pl-4">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card p-4 bg-card border rounded-xl">
                        <label class="text-[10px] font-black uppercase text-muted-foreground mb-2 block">Sucursal Destino</label>
                        <select v-model="form.branch_id" class="w-full bg-muted/20 border-none rounded-lg text-sm font-bold h-10 focus:ring-2 focus:ring-primary/20">
                            <option value="" disabled>Seleccionar sede...</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div class="card p-4 bg-card border rounded-xl">
                        <label class="text-[10px] font-black uppercase text-muted-foreground mb-2 block">Proveedor</label>
                        <select v-model="form.provider_id" class="w-full bg-muted/20 border-none rounded-lg text-sm font-bold h-10 focus:ring-2 focus:ring-primary/20">
                            <option value="" disabled>Seleccionar emisor...</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                        </select>
                    </div>
                    <div class="card p-4 bg-card border rounded-xl">
                        <label class="text-[10px] font-black uppercase text-muted-foreground mb-2 block">Fecha Factura</label>
                        <input v-model="form.purchase_date" type="date" class="w-full bg-muted/20 border-none rounded-lg text-sm font-bold h-10 focus:ring-2 focus:ring-primary/20" />
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-for="(item, index) in form.items" :key="index" 
                        class="card p-4 grid grid-cols-1 lg:grid-cols-12 gap-4 items-end border-l-4 rounded-xl bg-card border"
                        :class="form.is_emergency ? 'border-l-orange-500' : 'border-l-primary'">
                        
                        <div class="lg:col-span-5">
                            <label class="text-[9px] font-black uppercase text-muted-foreground mb-1 block">Producto (SKU)</label>
                            <select v-model="item.sku_id" class="w-full bg-muted/30 border-none rounded-lg h-10 px-3 text-xs font-bold focus:ring-2">
                                <option value="" disabled>Seleccionar artículo...</option>
                                <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.full_name }}</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="text-[9px] font-black uppercase text-muted-foreground mb-1 block text-center">Cantidad</label>
                            <input v-model.number="item.quantity" type="number" class="w-full h-10 bg-muted/30 border-none rounded-lg text-center font-black text-sm" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="text-[9px] font-black uppercase text-muted-foreground mb-1 block text-center">Costo Total (Bs)</label>
                            <input v-model.number="item.total_cost" type="number" step="0.01" class="w-full h-10 bg-emerald-500/5 text-emerald-700 border-none rounded-lg text-center font-black text-sm" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="text-[9px] font-black uppercase text-muted-foreground mb-1 block text-center">Expiración</label>
                            <input v-model="item.expiration_date" type="date" class="w-full h-10 bg-muted/30 border-none rounded-lg text-center font-bold text-[10px]" />
                        </div>
                        <div class="lg:col-span-1 flex justify-center">
                            <button type="button" @click="removeItem(index)" class="p-2 text-red-400 hover:text-red-600 transition-colors">
                                <Trash2 :size="18" />
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="addItem" class="w-full py-3 border-2 border-dashed rounded-xl flex items-center justify-center gap-2 text-muted-foreground hover:text-primary hover:border-primary transition-all font-black text-[10px] uppercase">
                        <Plus :size="16" /> Añadir Artículo
                    </button>
                </div>

                <div class="pt-6 border-t flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="w-full md:max-w-md">
                        <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Notas de Auditoría</label>
                        <textarea v-model="form.notes" class="w-full h-20 bg-muted/30 rounded-xl p-3 text-xs border-none focus:ring-0 mt-1 resize-none" placeholder="Opcional..."></textarea>
                    </div>

                    <div class="flex items-center gap-6 w-full md:w-auto">
                        <div class="text-right">
                            <span class="text-[9px] font-black text-muted-foreground uppercase block">Monto Final</span>
                            <span class="text-2xl font-black text-foreground">{{ grandTotal.toFixed(2) }} <small class="text-xs">Bs</small></span>
                        </div>
                        <button type="submit" :disabled="form.processing" class="bg-primary text-white h-14 px-10 rounded-2xl font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 flex items-center gap-3 shadow-xl shadow-primary/20">
                            <Save :size="20"/> {{ form.processing ? 'PROCESANDO...' : 'REGISTRAR' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>