<script setup>
import { useForm } from '@inertiajs/vue3';
import { Save, AlertOctagon, Info } from 'lucide-vue-next';

const props = defineProps({
    provider: { type: Object, default: null },
    isEdit: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const form = useForm({
    id: props.provider?.id || null,
    company_name: props.provider?.company_name || '',
    commercial_name: props.provider?.commercial_name || '',
    tax_id: props.provider?.tax_id || '',
    internal_code: props.provider?.internal_code || '',
    is_active: props.isEdit ? !!props.provider?.is_active : true,
    lead_time_days: props.provider?.lead_time_days || 1,
    min_order_value: props.provider?.min_order_value || 0,
    credit_days: props.provider?.credit_days || 0,
    credit_limit: props.provider?.credit_limit || 0,
    contact_name: props.provider?.contact_name || '',
    email_orders: props.provider?.email_orders || '',
    phone: props.provider?.phone || '',
    address: props.provider?.address || '',
    city: props.provider?.city || '',
    notes: props.provider?.notes || '',
    version: props.provider?.version || 0,
});

const submit = () => {
    if (props.isEdit) {
        form.put(route('admin.operations.providers.update', props.provider.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.operations.providers.store'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="space-y-6">
        <div v-if="form.hasErrors" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 p-4 rounded-md">
            <div class="flex items-start gap-3">
                <AlertOctagon class="text-rose-600 dark:text-rose-400 mt-0.5" :size="20" />
                <div class="flex-1">
                    <h3 class="text-xs font-black text-rose-700 dark:text-rose-400 uppercase tracking-tighter">Violación de Protocolo Detectada</h3>
                    <ul class="mt-2 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field" class="text-[10px] font-mono text-rose-600 dark:text-rose-400 uppercase">
                            // ERROR_IN_{ { field.toUpperCase() } }: {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <section class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-5 space-y-4 relative rounded-md shadow-sm">
                <div class="absolute -top-3 left-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 text-[10px] font-mono font-bold text-neutral-900 dark:text-neutral-50">SEC_01 // IDENTITY</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Razón Social *</label>
                        <input v-model="form.company_name" type="text" required :class="{'border-rose-500 focus:border-rose-500': form.errors.company_name}" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 font-bold uppercase transition-colors" />
                        <p v-if="form.errors.company_name" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.company_name }}</p>
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Nombre Comercial (Firma)</label>
                        <input v-model="form.commercial_name" type="text" :class="{'border-rose-500 focus:border-rose-500': form.errors.commercial_name}" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 font-bold uppercase transition-colors" />
                        <p v-if="form.errors.commercial_name" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.commercial_name }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">NIT / Tax ID *</label>
                        <input v-model="form.tax_id" type="text" required :class="{'border-rose-500 focus:border-rose-500': form.errors.tax_id}" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 font-mono transition-colors" />
                        <p v-if="form.errors.tax_id" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.tax_id }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Código Interno (ERP)</label>
                        <input v-model="form.internal_code" type="text" :class="{'border-rose-500 focus:border-rose-500': form.errors.internal_code}" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 font-mono transition-colors" />
                        <p v-if="form.errors.internal_code" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.internal_code }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-5 space-y-4 relative rounded-md shadow-sm">
                <div class="absolute -top-3 left-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 text-[10px] font-mono font-bold text-neutral-900 dark:text-neutral-50">SEC_02 // LOGISTICS_&_FINANCE</div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Lead Time (Días)</label>
                        <input v-model="form.lead_time_days" type="number" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                        <p v-if="form.errors.lead_time_days" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.lead_time_days }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Min. Order (Bs.)</label>
                        <input v-model="form.min_order_value" type="number" step="0.01" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                        <p v-if="form.errors.min_order_value" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.min_order_value }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Crédito (Días)</label>
                        <input v-model="form.credit_days" type="number" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                        <p v-if="form.errors.credit_days" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.credit_days }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Límite (Bs.)</label>
                        <input v-model="form.credit_limit" type="number" step="0.01" min="0" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs font-mono text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                        <p v-if="form.errors.credit_limit" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.credit_limit }}</p>
                    </div>
                </div>
                
                <div v-if="form.errors.version" class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 text-[10px] font-mono flex items-center gap-2 uppercase rounded">
                    <img src="" class="hidden" /><Info :size="14" /> {{ form.errors.version }}
                </div>
            </section>

            <section class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 p-5 space-y-4 relative rounded-md shadow-sm">
                <div class="absolute -top-3 left-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded px-2 text-[10px] font-mono font-bold text-neutral-900 dark:text-neutral-50">SEC_03 // CONTACT_INFO</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Persona de Contacto</label>
                        <input v-model="form.contact_name" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Email de Pedidos</label>
                        <input v-model="form.email_orders" type="email" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                        <p v-if="form.errors.email_orders" class="text-[9px] text-rose-600 dark:text-rose-400 font-mono uppercase italic">{{ form.errors.email_orders }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Teléfono</label>
                        <input v-model="form.phone" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Ciudad</label>
                        <input v-model="form.city" type="text" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 transition-colors" />
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-neutral-400 dark:text-neutral-500">Dirección</label>
                        <textarea v-model="form.address" rows="2" class="w-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 px-3 py-2 text-xs text-neutral-900 dark:text-neutral-50 rounded-md outline-none focus:border-neutral-400 dark:focus:border-neutral-700 focus:ring-0 resize-none transition-colors"></textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-neutral-50 dark:bg-neutral-950 p-6 border border-neutral-200 dark:border-neutral-800 rounded-md shadow-sm">
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer group select-none">
                        <input type="checkbox" v-model="form.is_active" class="w-4 h-4 border-neutral-300 dark:border-neutral-700 text-neutral-950 dark:text-white bg-white dark:bg-neutral-900 focus:ring-0" />
                        <span class="text-xs font-bold font-mono uppercase group-hover:text-neutral-900 dark:group-hover:text-white transition-colors">Estado Operativo del Nodo</span>
                    </label>
                    <p class="text-[9px] text-neutral-400 font-mono uppercase italic">ID: {{ form.id || 'NEW_SEQUENCE' }} // VERSION: {{ form.version }}</p>
                </div>

                <button type="submit" 
                        :disabled="form.processing"
                        class="w-full md:w-auto px-10 py-2.5 bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 font-bold text-xs uppercase flex items-center justify-center gap-3 rounded-md border border-transparent transition-colors disabled:opacity-50">
                    <Save v-if="!form.processing" :size="16" />
                    <span v-else class="w-4 h-4 border-2 border-current border-t-transparent animate-spin rounded-full"></span>
                    {{ isEdit ? 'Sincronizar Cambios' : 'Registrar Proveedor' }}
                </button>
            </div>
        </form>
    </div>
</template>