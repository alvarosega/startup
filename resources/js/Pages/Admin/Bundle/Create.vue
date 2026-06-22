<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, Trash, Plus, AlertOctagon } from 'lucide-vue-next';

const props = defineProps({
    skus: Array,
    bundle: Object // Nullable (Solo inyectado en modo edición)
});

const form = useForm({
    name: props.bundle?.name || '',
    image: null,
    type: props.bundle?.type || 'OFFER',
    starts_at: props.bundle?.starts_at ? props.bundle.starts_at.slice(0, 16) : '',
    ends_at: props.bundle?.ends_at ? props.bundle.ends_at.slice(0, 16) : '',
    is_active: props.bundle ? Boolean(props.bundle.is_active) : true,
    items: props.bundle?.items || [{ sku_id: '', quantity: 1.000 }]
});

const addRow = () => form.items.push({ sku_id: '', quantity: 1.000 });
const removeRow = (index) => form.items.length > 1 && form.items.splice(index, 1);

const submitForm = () => {
    if (props.bundle) {
        // Mock method spoofing para soportar subida de archivos vía PUT/POST en Laravel
        form.transform((data) => ({ ...data, _method: 'PUT' }))
            .post(route('admin.bundles.update', props.bundle.id), { preserveScroll: true });
    } else {
        form.post(route('admin.bundles.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="bundle ? 'Editar Combo Promocional' : 'Configurar Nuevo Combo'" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4 select-none font-sans">
                <Link :href="route('admin.bundles.index')" class="p-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-md text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors">
                    <ArrowLeft :size="18" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">
                        {{ bundle ? 'Modificar Estructura' : 'Estructurar Combo Promocional' }}
                    </h1>
                    <p class="text-[10px] font-mono text-neutral-400 dark:text-neutral-500 mt-0.5 uppercase">Asignación paramétrica de SKUs e inyección de horizontes temporales</p>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6 font-mono text-xs">
            <div v-if="form.hasErrors" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 p-4 rounded-md">
                <div class="flex items-start gap-3">
                    <AlertOctagon class="text-rose-600 dark:text-rose-400 mt-0.5" :size="18" />
                    <div class="flex-1 text-[10px] text-rose-700 dark:text-rose-400 uppercase">
                        <h3 class="font-black">Rechazo de Registro: Inconsistencia en Datos</h3>
                        <ul class="mt-1 space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">// {{ key.toUpperCase() }}: {{ error }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <div class="lg:col-span-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md space-y-4 h-fit shadow-xs">
                    <h3 class="text-[10px] font-black uppercase text-neutral-400 border-b border-neutral-200 dark:border-neutral-800 pb-2 tracking-wider">// MAESTRO_METADATA</h3>
                    
                    <div class="space-y-1">
                        <label class="font-bold text-neutral-400">Designación del Combo *</label>
                        <input v-model="form.name" type="text" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold uppercase text-neutral-900 dark:text-neutral-50 outline-none" placeholder="EJ. COMBO FIN DE SEMANA GIN" />
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-neutral-400">Fotografía Referencial</label>
                        <input type="file" accept="image/jpeg,image/png,image/webp" @input="form.image = $event.target.files[0]" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1 text-neutral-500 text-[11px]" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Tipo Combo</label>
                            <select v-model="form.type" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold text-neutral-900 dark:text-neutral-50 outline-none">
                                <option value="OFFER">PROMOCIÓN / PACK</option>
                                <option value="TEMPLATE">PLANTILLA BASE</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Estado Celda</label>
                            <select v-model="form.is_active" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 font-bold text-neutral-900 dark:text-neutral-50 outline-none">
                                <option :value="true">ACTIVO (SINC)</option>
                                <option :value="false">DESACTIVADO</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-neutral-200 dark:border-neutral-800 pt-3">
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Apertura Visible</label>
                            <input v-model="form.starts_at" type="datetime-local" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-neutral-400">Cierre/Expiración</label>
                            <input v-model="form.ends_at" type="datetime-local" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-neutral-900 dark:text-neutral-50 outline-none" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-4 rounded-md shadow-xs flex flex-col justify-between min-h-[400px]">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center border-b border-neutral-200 dark:border-neutral-800 pb-2 select-none">
                            <h3 class="text-[10px] font-black uppercase text-neutral-400 tracking-wider">// COMPONENTES_SELECCIÓN_MATRIZ</h3>
                            <button type="button" @click="addRow" class="text-neutral-900 hover:opacity-80 dark:text-white border border-neutral-200 dark:border-neutral-800 px-2 py-1 text-[10px] font-bold uppercase rounded flex items-center gap-1 bg-neutral-50 dark:bg-neutral-950"><Plus :size="12"/> Añadir SKU</button>
                        </div>

                        <div class="max-h-[290px] overflow-y-auto pr-1 space-y-2">
                            <div v-for="(item, index) in form.items" :key="index" class="flex items-center gap-2 border-b border-neutral-100 dark:border-neutral-800/40 pb-2">
                                <div class="px-2 py-1 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 text-neutral-400 font-bold rounded text-[10px] w-8 text-center select-none">{{ String(index + 1).padStart(2, '0') }}</div>
                                
                                <div class="flex-1">
                                    <select v-model="item.sku_id" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-2 py-1.5 text-xs text-neutral-900 dark:text-neutral-50 outline-none font-bold uppercase">
                                        <option value="" disabled>Seleccione SKU físico individual...</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.name }} [{{ sku.code }}]</option>
                                    </select>
                                </div>

                                <div class="w-32">
                                    <input v-model.number="item.quantity" type="number" step="0.001" min="0.001" required class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded px-3 py-1.5 text-right text-xs font-bold text-neutral-900 dark:text-neutral-50 outline-none" placeholder="1.000" />
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
                            COMPROMETER_ESTRUCTURA_PROMO
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>