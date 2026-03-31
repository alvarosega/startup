<script setup>
import { useForm } from '@inertiajs/vue3';
import { Save, AlertOctagon, Info, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    provider: { type: Object, default: null },
    isEdit: { type: Boolean, default: false }
});

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
    version: props.provider?.version || 0, // CRÍTICO para Optimistic Locking
});

const submit = () => {
    if (props.isEdit) {
        // REGLA 2: Uso de PUT para actualizaciones según protocolo REST
        form.put(route('admin.providers.update', props.provider.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.providers.store'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="space-y-6">
        <div v-if="form.hasErrors" class="bg-destructive/10 border border-destructive p-4 animate-none">
            <div class="flex items-start gap-3">
                <AlertOctagon class="text-destructive mt-0.5" :size="20" />
                <div class="flex-1">
                    <h3 class="text-xs font-black text-destructive uppercase tracking-tighter">Violación de Protocolo Detectada</h3>
                    <ul class="mt-2 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field" class="text-[10px] font-mono text-destructive uppercase">
                            // ERROR_IN_{{ field.toUpperCase() }}: {{ error }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <form @submit.prevent="submit" class="space-y-8">
            <section class="bg-card border border-border p-5 space-y-4 relative">
                <div class="absolute -top-3 left-4 bg-background px-2 text-[10px] font-mono font-bold text-primary">SEC_01 // IDENTITY</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Razón Social *</label>
                        <input v-model="form.company_name" type="text" :class="{'border-destructive': form.errors.company_name}" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary font-bold" />
                        <p v-if="form.errors.company_name" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.company_name }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">NIT / Tax ID *</label>
                        <input v-model="form.tax_id" type="text" :class="{'border-destructive': form.errors.tax_id}" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary font-mono" />
                        <p v-if="form.errors.tax_id" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.tax_id }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Código Interno (ERP)</label>
                        <input v-model="form.internal_code" type="text" :class="{'border-destructive': form.errors.internal_code}" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary font-mono" />
                        <p v-if="form.errors.internal_code" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.internal_code }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-card border border-border p-5 space-y-4 relative">
                <div class="absolute -top-3 left-4 bg-background px-2 text-[10px] font-mono font-bold text-primary">SEC_02 // LOGISTICS_&_FINANCE</div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Lead Time (Días)</label>
                        <input v-model="form.lead_time_days" type="number" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                        <p v-if="form.errors.lead_time_days" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.lead_time_days }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Min. Order ($)</label>
                        <input v-model="form.min_order_value" type="number" step="0.01" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary font-mono" />
                        <p v-if="form.errors.min_order_value" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.min_order_value }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Crédito (Días)</label>
                        <input v-model="form.credit_days" type="number" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                        <p v-if="form.errors.credit_days" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.credit_days }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Límite ($)</label>
                        <input v-model="form.credit_limit" type="number" step="0.01" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary font-mono" />
                        <p v-if="form.errors.credit_limit" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.credit_limit }}</p>
                    </div>
                </div>
                
                <div v-if="form.errors.version" class="p-3 bg-destructive/10 border border-destructive text-destructive text-[10px] font-mono flex items-center gap-2 uppercase">
                    <Info :size="14" /> {{ form.errors.version }}
                </div>
            </section>

            <section class="bg-card border border-border p-5 space-y-4 relative">
                <div class="absolute -top-3 left-4 bg-background px-2 text-[10px] font-mono font-bold text-primary">SEC_03 // CONTACT_INFO</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Persona de Contacto</label>
                        <input v-model="form.contact_name" type="text" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Email de Pedidos</label>
                        <input v-model="form.email_orders" type="email" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                        <p v-if="form.errors.email_orders" class="text-[9px] text-destructive font-mono uppercase italic">{{ form.errors.email_orders }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Teléfono</label>
                        <input v-model="form.phone" type="text" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Ciudad</label>
                        <input v-model="form.city" type="text" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-mono font-bold uppercase text-muted-foreground">Dirección</label>
                        <textarea v-model="form.address" rows="2" class="w-full bg-background border border-border p-2 text-sm outline-none focus:border-primary resize-none"></textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-muted/30 p-6 border border-border">
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary" />
                        <span class="text-xs font-bold font-mono uppercase group-hover:text-primary transition-colors">Estado Operativo del Nodo</span>
                    </label>
                    <p class="text-[9px] text-muted-foreground font-mono uppercase italic">ID: {{ form.id || 'NEW_SEQUENCE' }} // VERSION: {{ form.version }}</p>
                </div>

                <button type="submit" 
                    :disabled="form.processing"
                    class="w-full md:w-auto px-10 py-3 bg-primary text-primary-foreground font-bold text-xs uppercase flex items-center justify-center gap-3 hover:opacity-90 disabled:opacity-50 transition-all">
                    <Save v-if="!form.processing" :size="16" />
                    <span v-else class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground animate-spin rounded-full"></span>
                    {{ isEdit ? 'Sincronizar Cambios' : 'Registrar Proveedor' }}
                </button>
            </div>
        </form>
    </div>
</template>