<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    FileText, DollarSign, Users, 
    ArrowRight, ArrowLeft, Save, 
    Building2, Hash, CalendarClock, CreditCard,
    Mail, Phone, MapPin, CheckCircle
} from 'lucide-vue-next';

const props = defineProps({ provider: Object });

// --- ESTADO ---
const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Identidad', icon: FileText },
    { id: 2, title: 'Negocio', icon: DollarSign },
    { id: 3, title: 'Contacto', icon: Users },
];

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// --- FORMULARIO ---
const form = useForm({
    company_name: props.provider.company_name,
    commercial_name: props.provider.commercial_name || '',
    tax_id: props.provider.tax_id,
    internal_code: props.provider.internal_code || '',
    is_active: !!props.provider.is_active,
    lead_time_days: props.provider.lead_time_days,
    min_order_value: props.provider.min_order_value,
    credit_days: props.provider.credit_days,
    credit_limit: props.provider.credit_limit,
    contact_name: props.provider.contact_name || '',
    email_orders: props.provider.email_orders || '',
    phone: props.provider.phone || '',
    address: props.provider.address || '',
    city: props.provider.city || '',
    notes: props.provider.notes || ''
});

// --- NAVEGACIÓN ---
const validateStep = () => {
    form.clearErrors();
    if (currentStep.value === 1) {
        let valid = true;
        if (!form.company_name) { form.setError('company_name', 'Requerido'); valid = false; }
        if (!form.tax_id) { form.setError('tax_id', 'Requerido'); valid = false; }
        return valid;
    }
    return true;
};

const nextStep = () => {
    if (validateStep() && currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    if (!validateStep()) return;
    
    form.put(route('admin.providers.update', props.provider.id), {
        preserveScroll: true,
        onError: (errors) => {
            const step1Fields = ['company_name', 'tax_id', 'commercial_name'];
            const step3Fields = ['email_orders', 'contact_name'];
            
            if (Object.keys(errors).some(k => step1Fields.includes(k))) currentStep.value = 1;
            else if (Object.keys(errors).some(k => step3Fields.includes(k))) currentStep.value = 3;
            else currentStep.value = 2;
        }
    });
};
</script>

<template>
    <AdminLayout>
        
        <div class="max-w-4xl mx-auto pb-32 md:pb-12">
            
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur border-b border-border px-4 py-4 mb-6 flex flex-col gap-4 shadow-sm transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.providers.index')" class="btn btn-ghost btn-sm btn-square -ml-2 text-muted-foreground hover:bg-muted">
                            <ArrowLeft :size="22" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-display font-black text-foreground leading-none tracking-tight">
                                Editar Proveedor
                            </h1>
                            <p class="text-xs font-mono text-primary mt-1">ID: #{{ provider.id }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span v-if="form.is_active" class="badge badge-success text-[10px] font-black tracking-widest uppercase px-2 py-1 shadow-sm shadow-success/20">
                            Activo
                        </span>
                        <span v-else class="badge badge-error text-[10px] font-black tracking-widest uppercase px-2 py-1 shadow-sm shadow-error/20">
                            Inactivo
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="relative h-1.5 w-full bg-muted/50 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-primary to-accent transition-all duration-500 ease-out shadow-[0_0_10px_rgba(0,240,255,0.5)]" 
                             :style="{ width: `${progressPercentage}%` }"></div>
                    </div>
                    <div class="flex justify-between px-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-300" :class="currentStep >= 1 ? 'text-primary' : 'text-muted-foreground'">Identidad</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-300" :class="currentStep >= 2 ? 'text-primary' : 'text-muted-foreground'">Negocio</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-300" :class="currentStep >= 3 ? 'text-primary' : 'text-muted-foreground'">Contacto</span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="px-4 md:px-0">
                <div class="card bg-card border border-border shadow-sm min-h-[450px] relative overflow-hidden flex flex-col">
                    
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                    <div class="p-6 md:p-8 flex-1 relative z-10">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-6 animate-in slide-in-from-right-4 fade-in duration-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2 space-y-2">
                                        <label class="form-label text-xs uppercase tracking-wider text-muted-foreground font-bold">Razón Social <span class="text-error">*</span></label>
                                        <div class="relative group">
                                            <Building2 :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                            <input v-model="form.company_name" type="text" class="form-input w-full pl-10 font-bold uppercase text-lg h-12" placeholder="Ej: CORPORACION ACME S.A." :class="{'border-error ring-error/20': form.errors.company_name}">
                                        </div>
                                        <p v-if="form.errors.company_name" class="form-error">{{ form.errors.company_name }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase tracking-wider text-muted-foreground font-bold">Nombre Comercial</label>
                                        <input v-model="form.commercial_name" type="text" class="form-input w-full h-11" placeholder="Ej: Acme Corp">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase tracking-wider text-muted-foreground font-bold">NIT / RUC <span class="text-error">*</span></label>
                                        <div class="relative group">
                                            <Hash :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                            <input v-model="form.tax_id" type="text" class="form-input w-full pl-10 font-mono text-base h-11" placeholder="20123456789" :class="{'border-error ring-error/20': form.errors.tax_id}">
                                        </div>
                                        <p v-if="form.errors.tax_id" class="form-error">{{ form.errors.tax_id }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase tracking-wider text-muted-foreground font-bold">Código ERP</label>
                                        <input v-model="form.internal_code" type="text" class="form-input w-full font-mono bg-muted/30 h-11" placeholder="PROV-001">
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-border mt-2">
                                    <div class="flex items-center justify-between p-4 rounded-xl border transition-all duration-300 cursor-pointer group" 
                                         :class="form.is_active ? 'border-primary/50 bg-primary/5' : 'border-border bg-muted/10'"
                                         @click="form.is_active = !form.is_active">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">Estado Operativo</span>
                                            <span class="text-xs text-muted-foreground">Habilitar proveedor para compras</span>
                                        </div>
                                        <div class="relative w-12 h-6 rounded-full transition-colors duration-300 border border-transparent"
                                             :class="form.is_active ? 'bg-primary' : 'bg-muted-foreground/30'">
                                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300"
                                                 :class="form.is_active ? 'translate-x-6' : 'translate-x-0'"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-6 animate-in slide-in-from-right-4 fade-in duration-300">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-1 p-4 rounded-2xl border border-border bg-gradient-to-b from-blue-50/50 to-transparent flex flex-col items-center justify-center text-center gap-2 shadow-sm">
                                        <CalendarClock :size="28" class="text-blue-600 mb-1"/>
                                        <label class="text-[10px] font-black text-blue-900/70 uppercase tracking-wide">Tiempo Entrega</label>
                                        <div class="flex items-baseline gap-1">
                                            <input v-model="form.lead_time_days" type="number" class="w-16 text-center bg-transparent border-b-2 border-blue-200 font-black text-2xl text-blue-900 focus:outline-none focus:border-blue-500 p-0 transition-colors" placeholder="0">
                                            <span class="text-xs font-bold text-blue-600">días</span>
                                        </div>
                                    </div>

                                    <div class="col-span-1 p-4 rounded-2xl border border-border bg-gradient-to-b from-emerald-50/50 to-transparent flex flex-col items-center justify-center text-center gap-2 shadow-sm">
                                        <DollarSign :size="28" class="text-emerald-600 mb-1"/>
                                        <label class="text-[10px] font-black text-emerald-900/70 uppercase tracking-wide">Mínimo Compra</label>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-xs font-bold text-emerald-600">$</span>
                                            <input v-model="form.min_order_value" type="number" class="w-20 text-center bg-transparent border-b-2 border-emerald-200 font-black text-2xl text-emerald-900 focus:outline-none focus:border-emerald-500 p-0 transition-colors" placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-border">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase mb-4 flex items-center gap-2">
                                        <CreditCard :size="16" class="text-primary"/> Condiciones de Crédito
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="form-label text-xs uppercase font-bold">Días Crédito</label>
                                            <input v-model="form.credit_days" type="number" class="form-input w-full h-11" placeholder="Ej: 30, 60">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="form-label text-xs uppercase font-bold">Límite ($)</label>
                                            <input v-model="form.credit_limit" type="number" step="0.01" class="form-input w-full h-11 font-mono" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6 animate-in slide-in-from-right-4 fade-in duration-300">
                                <div class="space-y-2">
                                    <label class="form-label text-xs uppercase tracking-wider text-muted-foreground font-bold">Email Pedidos</label>
                                    <div class="relative group">
                                        <Mail :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                        <input v-model="form.email_orders" type="email" class="form-input w-full pl-10 h-11" placeholder="pedidos@empresa.com" :class="{'border-error ring-error/20': form.errors.email_orders}">
                                    </div>
                                    <p v-if="form.errors.email_orders" class="form-error">{{ form.errors.email_orders }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase font-bold">Contacto</label>
                                        <div class="relative group">
                                            <Users :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                            <input v-model="form.contact_name" type="text" class="form-input w-full pl-10 h-11" placeholder="Juan Pérez">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="form-label text-xs uppercase font-bold">Móvil</label>
                                        <div class="relative group">
                                            <Phone :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                            <input v-model="form.phone" type="tel" class="form-input w-full pl-10 h-11" placeholder="+51 999...">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="form-label text-xs uppercase font-bold">Dirección</label>
                                    <div class="relative group">
                                        <MapPin :size="18" class="absolute left-3 top-3 text-muted-foreground group-focus-within:text-primary transition-colors"/>
                                        <textarea v-model="form.address" class="form-input w-full pl-10 pt-2.5 resize-none" rows="2" placeholder="Dirección completa"></textarea>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="form-label text-xs uppercase font-bold">Notas</label>
                                    <textarea v-model="form.notes" class="form-input w-full resize-none text-sm" rows="3" placeholder="Información interna relevante..."></textarea>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="p-6 bg-muted/20 border-t border-border flex justify-between items-center mt-auto relative z-10">
                        <button type="button" @click="prevStep" 
                                class="btn btn-outline h-12 px-6 rounded-xl border font-bold text-muted-foreground bg-background hover:bg-muted"
                                :class="{'opacity-0 pointer-events-none': currentStep === 1}">
                            <ArrowLeft :size="20" class="mr-2"/> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="btn btn-primary h-12 px-8 rounded-xl shadow-lg shadow-primary/20 text-base font-bold flex items-center gap-2">
                            Siguiente <ArrowRight :size="20"/>
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="btn btn-primary h-12 px-8 rounded-xl shadow-lg shadow-primary/20 text-base font-bold flex items-center gap-2 bg-navy text-white hover:bg-navy/90">
                            <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                            <span v-else class="flex items-center gap-2">
                                <Save :size="20"/> Guardar
                            </span>
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from { opacity: 0; transform: translateX(10px); }
.fade-leave-to { opacity: 0; transform: translateX(-10px); position: absolute; width: 100%; }
</style>