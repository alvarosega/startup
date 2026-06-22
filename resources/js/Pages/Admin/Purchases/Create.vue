<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, Trash, Plus, AlertOctagon } from 'lucide-vue-next';

const props = defineProps({
    providers: Array,
    branches: Array,
    skus: Array
});

const form = useForm({
    branch_id: props.branches?.[0]?.id || '',
    provider_id: props.providers?.[0]?.id || '',
    document_number: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    payment_type: 'CASH',
    lot_code: '',
    expiration_date: '',
    items: [
        { sku_id: '', quantity: 0.000 }
    ]
});

const addRow = () => {
    form.items.push({ sku_id: '', quantity: 0.000 });
};

const removeRow = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submitPurchase = () => {
    form.post(route('admin.purchases.process'), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="Registrar Ingreso de Lote" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4 select-none">
                <Link :href="route('admin.purchases.index')" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Ingresar Camión // Lote</h1>
                    <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-0.5 uppercase">Inyección transaccional masiva de stock físico primario</p>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            <div v-if="form.hasErrors" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 p-4 rounded-md">
                <div class="flex items-start gap-3">
                    <AlertOctagon class="text-rose-600 dark:text-rose-400 mt-0.5" :size="18" />
                    <div class="flex-1 font-mono text-[10px] text-rose-700 dark:text-rose-400 uppercase">
                        <h3 class="font-black">Fallo Crítico de Validación en Ingesta</h3>
                        <ul class="mt-1 space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">// {{ key.toUpperCase() }}: {{ error }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitPurchase" class="grid grid-cols-1 lg:grid-cols-12 gap-5 font-mono text-xs">
                <div class="lg:col-span-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md space-y-4 h-fit shadow-xs">
                    <h3 class="text-[10px] font-black uppercase text-neutral-400 border-b border-neutral-200 dark:border-neutral-800 pb-2 tracking-wider">// LOTE_GLOBAL_METADATA</h3>
                    
                    <div class="space-y-1">
                        <label class="font-bold text-neutral-400">Nodo Destino (Almacén) *</label>
                        <select v-model="form.branch_id" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name.toUpperCase() }}</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-neutral-400">Socio Comercial (Proveedor) *</label>
                        <select v-model="form.provider_id" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                            <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.company_name.toUpperCase() }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Nro Factura / Remisión *</label>
                            <input v-model="form.document_number" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 outline-none" placeholder="FAC-12345" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Fecha Carga *</label>
                            <input v-model="form.purchase_date" type="date" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-neutral-400">Condición de Pago *</label>
                        <div class="grid grid-cols-2 gap-2 text-center font-black">
                            <button type="button" @click="form.payment_type = 'CASH'" :class="form.payment_type === 'CASH' ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-950' : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 text-neutral-400'" class="border py-1.5 rounded transition-all">EFECTIVO (CASH)</button>
                            <button type="button" @click="form.payment_type = 'CREDIT'" :class="form.payment_type === 'CREDIT' ? 'border-amber-500 bg-amber-500 text-white' : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 text-neutral-400'" class="border py-1.5 rounded transition-all">CRÉDITO</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-3">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Código LOTE ÚNICO *</label>
                            <input v-model="form.lot_code" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-amber-500/40 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none" placeholder="LOTE_2026_M1" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Expiración Lote</label>
                            <input v-model="form.expiration_date" type="date" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md shadow-xs flex flex-col justify-between min-h-[460px]">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center border-b border-neutral-200 dark:border-neutral-800 pb-2 select-none">
                            <h3 class="text-[10px] font-black uppercase text-neutral-400 tracking-wider">// DETALLE_ITEMS_DESGLOSE</h3>
                            <button type="button" @click="addRow" class="text-neutral-900 hover:opacity-80 dark:text-white border border-neutral-200 dark:border-neutral-800 px-2 py-1 text-[10px] font-bold uppercase rounded flex items-center gap-1 bg-neutral-50 dark:bg-neutral-950"><Plus :size="12"/> Inyectar Fila</button>
                        </div>

                        <div class="max-h-[340px] overflow-y-auto pr-1 space-y-2">
                            <div v-for="(item, index) in form.items" :key="index" class="flex items-center gap-2 border-b border-neutral-100 dark:border-neutral-800/40 pb-2">
                                <div class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-400 font-bold rounded text-[10px] w-8 text-center select-none">{{ String(index + 1).padStart(2, '0') }}</div>
                                
                                <div class="flex-1">
                                    <select v-model="item.sku_id" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-xs text-neutral-900 dark:text-neutral-50 outline-none uppercase font-bold">
                                        <option value="" disabled>Seleccione presentación SKU...</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.name.toUpperCase() }} [{{ sku.code }}]</option>
                                    </select>
                                </div>

                                <div class="w-36">
                                    <input v-model.number="item.quantity" type="number" step="0.001" min="0.001" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-3 py-1.5 text-right text-xs font-bold text-neutral-900 dark:text-neutral-50 outline-none" placeholder="0.000" />
                                </div>

                                <button type="button" @click="removeRow(index)" :disabled="form.items.length === 1" class="p-1.5 border border-neutral-200 dark:border-neutral-800 text-neutral-300 hover:text-rose-600 dark:hover:text-rose-400 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all disabled:opacity-20 flex items-center justify-center shrink-0">
                                    <Trash :size="14" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-neutral-200 dark:border-neutral-800 pt-4 flex justify-end select-none">
                        <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-12 py-2.5 bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 font-sans font-bold text-xs uppercase tracking-wider rounded-md border border-transparent transition-colors flex items-center justify-center gap-2">
                            <Save v-if="!form.processing" :size="16" />
                            <span v-else class="w-4 h-4 border-2 border-current border-t-transparent animate-spin rounded-full"></span>
                            COMPROMETER_ENTRADA_ALMACÉN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>