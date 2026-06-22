<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, AlertOctagon, HelpCircle, ShieldAlert } from 'lucide-vue-next';

const props = defineProps({
    prices: Array,
    branches: Array,
    skus: Array
});

const form = useForm({
    selected_skus: [], // Array de UUIDs de SKUs seleccionados para impacto masivo
    branch_id: props.branches?.[0]?.id || '',
    type: 'regular',
    list_price: 0.00,
    final_price: 0.00,
    min_quantity: 1,
    priority: 1,
    valid_from: new Date().toISOString().slice(0, 16), // datetime-local format friendly
    valid_to: ''
});

const submitMassPricing = () => {
    if (form.selected_skus.length === 0) {
        alert('ERROR_PROTOCOLO: Debe seleccionar mínimo un (1) SKU para propagación comercial.');
        return;
    }
    form.post(route('admin.prices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('selected_skus', 'list_price', 'final_price');
            alert('SINC_PRECIOS: Regla inyectada atómicamente en la matriz corporativa.');
        }
    });
};

const toggleSkuSelection = (id) => {
    const idx = form.selected_skus.indexOf(id);
    if (idx > -1) form.selected_skus.splice(idx, 1);
    else form.selected_skus.push(id);
};
</script>

<template>
    <Head title="Estrategias Comerciales - Precios Masivos" />
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center select-none">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Inyección Masiva de Márgenes</h1>
                    <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-0.5 uppercase">Propagación paralela de reglas de precio sobre el catálogo multizona</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6 font-mono text-xs">
            <div v-if="form.hasErrors" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 p-4 rounded-md">
                <div class="flex items-start gap-3">
                    <AlertOctagon class="text-rose-600 dark:text-rose-400 mt-0.5" :size="18" />
                    <div class="flex-1 text-[10px] text-rose-700 dark:text-rose-400 uppercase">
                        <h3 class="font-black">Rechazo de Regla por Violación de Reglas de Negocio</h3>
                        <ul class="mt-1 space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">// {{ key.toUpperCase() }}: {{ error }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitMassPricing" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <div class="lg:col-span-7 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md shadow-xs flex flex-col justify-between min-h-[480px]">
                    <div class="space-y-2 w-full">
                        <div class="flex justify-between items-center border-b border-neutral-200 dark:border-neutral-800 pb-2 select-none">
                            <h3 class="text-[10px] font-black text-neutral-400 uppercase tracking-wider">// UNIVERSO_SKU_SELECCIÓN ({{ form.selected_skus.length }})</h3>
                            <button type="button" @click="form.selected_skus = form.selected_skus.length === skus.length ? [] : skus.map(s => s.id)" class="text-[9px] font-black border border-neutral-200 dark:border-neutral-800 px-2 py-0.5 uppercase rounded bg-neutral-50 dark:bg-neutral-950">Alternar Universo</button>
                        </div>

                        <div class="max-h-[380px] overflow-y-auto pr-1 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <div v-for="sku in skus" :key="sku.id" @click="toggleSkuSelection(sku.id)" :class="form.selected_skus.includes(sku.id) ? 'border-neutral-900 bg-neutral-950/5 dark:border-white dark:bg-white/5' : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50/40 dark:bg-neutral-950/20'" class="border p-2.5 rounded cursor-pointer select-none transition-all flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-neutral-900 dark:text-neutral-100 uppercase truncate text-[11px] tracking-tight">{{ sku.name }}</div>
                                    <div class="text-[9px] text-neutral-400 mt-0.5 select-all">{{ sku.code }}</div>
                                </div>
                                <div class="w-3.5 h-3.5 border flex items-center justify-center text-[9px] font-black shrink-0" :class="form.selected_skus.includes(sku.id) ? 'border-neutral-950 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-950' : 'border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900'">
                                    <span v-if="form.selected_skus.includes(sku.id)">X</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md space-y-4 h-fit shadow-xs">
                    <h3 class="text-[10px] font-black uppercase text-neutral-900 dark:text-white border-b border-neutral-200 dark:border-neutral-800 pb-2 tracking-wider">// REGLA_PARÁMETROS_MATRIZ</h3>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Sucursal Destino *</label>
                            <select v-model="form.branch_id" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Estrategia Margen *</label>
                            <select v-model="form.type" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-xs font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none">
                                <option value="regular">REGULAR_BASE</option>
                                <option value="offer">OFERTA_LIQUIDA</option>
                                <option value="wholesale">MAYORISTA_DIST</option>
                                <option value="member">SOCIO_EXCLUSIVO</option>
                                <option value="liquidation">LIQUIDACIÓN</option>
                                <option value="staff">PERSONAL_INTERNO</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-3">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Precio Lista Ref. (Bs.) *</label>
                            <input v-model.number="form.list_price" type="number" step="0.01" min="0.01" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-right font-bold text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Precio Venta Final (Bs.) *</label>
                            <input v-model.number="form.final_price" type="number" step="0.01" min="0.01" required class="w-full bg-white dark:bg-neutral-900 border border-amber-500/50 rounded px-2 py-1 text-right font-bold text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Volumen Compra Mín.</label>
                            <input v-model.number="form.min_quantity" type="number" min="1" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-right text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Prioridad Ejecución</label>
                            <input v-model.number="form.priority" type="number" min="1" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-right text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-3">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Apertura Transmisión *</label>
                            <input v-model="form.valid_from" type="datetime-local" required class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Cierre Célula (Vigencia)</label>
                            <input v-model="form.valid_to" type="datetime-local" class="w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 py-2.5 font-sans font-bold uppercase text-xs tracking-wider border border-transparent rounded-md transition-colors flex items-center justify-center gap-2 shadow-sm select-none">
                        <Save v-if="!form.processing" :size="16" />
                        <span v-else class="w-4 h-4 border-2 border-current border-t-transparent animate-spin rounded-full"></span>
                        PROPAGAR_REGLA_MASIVA
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>