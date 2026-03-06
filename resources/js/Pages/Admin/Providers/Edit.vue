<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    FileText, DollarSign, Users, 
    ArrowRight, ArrowLeft, Save, 
    Building2, Hash, CalendarClock, CreditCard,
    Mail, Phone, MapPin, CheckCircle, AlertTriangle,
    Cpu, Terminal, Wifi, WifiOff, Zap, Globe,
    Fingerprint, Package, Clock, Target
} from 'lucide-vue-next';

const props = defineProps({ provider: Object });

// --- ESTADO ---
const currentStep = ref(1);
const steps = [
    { id: 1, title: 'IDENTIDAD', code: 'SEC_01', icon: FileText },
    { id: 2, title: 'NEGOCIO', code: 'SEC_02', icon: DollarSign },
    { id: 3, title: 'CONTACTO', code: 'SEC_03', icon: Users },
];

const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);

// --- CÓDIGO DE PROVEEDOR ---
const providerCode = computed(() => {
    return `PRV_${String(props.provider.id).padStart(4, '0')}`;
});

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
        if (!form.company_name) { 
            form.setError('company_name', '// RAZÓN SOCIAL REQUERIDA'); 
            valid = false; 
        }
        if (!form.tax_id) { 
            form.setError('tax_id', '// NIT REQUERIDO'); 
            valid = false; 
        }
        return valid;
    }
    return true;
};

const nextStep = () => {
    if (validateStep()) {
        if (currentStep.value < steps.length) {
            currentStep.value++;
        }
    } else {
        const card = document.getElementById('wizard-card');
        card?.classList.add('shake');
        setTimeout(() => card?.classList.remove('shake'), 400);
    }
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
            
            const card = document.getElementById('wizard-card');
            card?.classList.add('shake');
            setTimeout(() => card?.classList.remove('shake'), 400);
        }
    });
};
</script>

<template>
    <AdminLayout>
        
        <div class="max-w-4xl mx-auto pb-32 md:pb-12 px-4 md:px-0">
            
            <!-- Header sticky con progress bar -->
            <div class="sticky top-0 z-30 bg-background/95 backdrop-blur-md border-b border-primary/30 px-4 py-4 mb-6 transition-all duration-300 group/header">
                
                <!-- Efecto de escaneo -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent translate-x-[-100%] group-hover/header:translate-x-[100%] transition-transform duration-1000"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.providers.index')" 
                                  class="p-2 border border-border hover:border-primary hover:shadow-neon-primary transition-all relative group/back">
                                <ArrowLeft :size="20" class="group-hover/back:text-primary transition-colors" />
                                <!-- Esquinas decorativas -->
                                <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/back:opacity-100"></span>
                                <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/back:opacity-100"></span>
                            </Link>
                            
                            <div class="relative group/title">
                                <h1 class="text-xl font-display font-black tracking-widest text-primary uppercase glitch-text drop-shadow-[0_0_8px_hsl(var(--primary)/0.6)] leading-none"
                                    data-text="EDITAR PROVEEDOR">
                                    EDITAR PROVEEDOR
                                </h1>
                                <p class="text-[10px] font-mono text-muted-foreground mt-1 flex items-center gap-2">
                                    <Cpu :size="12" class="text-primary animate-pulse" />
                                    <span>{{ providerCode }} // {{ props.provider.company_name }}</span>
                                    <Terminal :size="12" class="text-primary animate-pulse" />
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <!-- Badge de estado -->
                            <div class="border px-3 py-1.5 flex items-center gap-2"
                                 :class="form.is_active ? 'border-cyan-500/30 bg-cyan-500/10' : 'border-destructive/30 bg-destructive/10'">
                                <component :is="form.is_active ? Wifi : WifiOff" 
                                           :size="12" 
                                           :class="form.is_active ? 'text-cyan-500' : 'text-destructive'" />
                                <span class="text-[10px] font-mono font-bold uppercase"
                                      :class="form.is_active ? 'text-cyan-500' : 'text-destructive'">
                                    {{ form.is_active ? 'ACTIVO' : 'INACTIVO' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-2">
                        <div class="relative h-1.5 w-full bg-border">
                            <div class="absolute top-0 left-0 h-full bg-primary shadow-neon-primary transition-all duration-500 ease-out" 
                                 :style="{ width: `${progressPercentage}%` }"></div>
                        </div>
                        <div class="flex justify-between px-1">
                            <span v-for="step in steps" :key="step.id"
                                  class="text-[8px] font-mono font-bold uppercase transition-colors duration-300"
                                  :class="currentStep >= step.id ? 'text-primary' : 'text-muted-foreground'">
                                {{ step.code }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" id="wizard-card">
                <!-- Main Card -->
                <div class="border border-border/50 bg-background shadow-2xl min-h-[450px] relative overflow-hidden group/card">
                    
                    <!-- Scanline superior -->
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-primary to-transparent translate-x-[-100%] group-hover/card:translate-x-[100%] transition-transform duration-1000"></div>
                    
                    <!-- Efecto de glow en esquinas -->
                    <div class="absolute top-0 left-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 w-20 h-20 bg-primary/5 blur-3xl"></div>
                    
                    <!-- Esquinas decorativas grandes -->
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-primary/30"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-primary/30"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-primary/30"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-primary/30"></div>

                    <div class="p-6 md:p-8 relative z-10">
                        
                        <!-- Step 1: Identidad -->
                        <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Razón Social -->
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <Terminal :size="12" /> // RAZÓN SOCIAL <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative group/input">
                                        <Building2 :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                        <input v-model="form.company_name" 
                                               type="text" 
                                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all uppercase"
                                               placeholder="CORPORACIÓN ACME S.A." />
                                        <!-- Esquinas decorativas en focus -->
                                        <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                        <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                        <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                        <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-focus-within/input:opacity-100"></span>
                                    </div>
                                    <p v-if="form.errors.company_name" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.company_name }}
                                    </p>
                                </div>

                                <!-- Nombre Comercial -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                        // NOMBRE COMERCIAL
                                    </label>
                                    <input v-model="form.commercial_name" 
                                           type="text" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="ACME CORP" />
                                </div>

                                <!-- NIT / RUC -->
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block flex items-center gap-1">
                                        <Fingerprint :size="12" /> // NIT / RUC <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative group/input">
                                        <Hash :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                        <input v-model="form.tax_id" 
                                               type="text" 
                                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="20123456789" />
                                    </div>
                                    <p v-if="form.errors.tax_id" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                        <AlertTriangle :size="10" /> {{ form.errors.tax_id }}
                                    </p>
                                </div>

                                <!-- Código ERP -->
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                        // CÓDIGO ERP
                                    </label>
                                    <input v-model="form.internal_code" 
                                           type="text" 
                                           class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="PROV-001" />
                                </div>
                            </div>

                            <!-- Toggle de estado -->
                            <div class="pt-6 border-t border-primary/30 mt-2">
                                <div @click="form.is_active = !form.is_active" 
                                     class="flex items-center justify-between p-4 border cursor-pointer select-none transition-all group/toggle"
                                     :class="form.is_active ? 'border-cyan-500/30 bg-cyan-500/5 shadow-neon-primary' : 'border-border/50 hover:border-primary/30'">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-bold text-sm uppercase flex items-center gap-2"
                                              :class="form.is_active ? 'text-cyan-500' : 'text-muted-foreground'">
                                            <component :is="form.is_active ? Wifi : WifiOff" :size="16" />
                                            {{ form.is_active ? '[ SYS_ONLINE ]' : '[ SYS_OFFLINE ]' }}
                                        </span>
                                        <span class="text-[8px] font-mono text-muted-foreground uppercase mt-1">
                                            HABILITAR PROVEEDOR PARA COMPRAS
                                        </span>
                                    </div>
                                    <div class="relative w-12 h-6 border border-border/50">
                                        <div class="absolute top-0.5 left-0.5 w-5 h-5 transition-all duration-300"
                                             :class="form.is_active ? 'translate-x-6 bg-cyan-500 shadow-neon-primary' : 'translate-x-0 bg-muted-foreground'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Negocio -->
                        <div v-else-if="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Lead Time -->
                                <div class="col-span-1 p-4 border border-primary/30 bg-primary/5 relative group/metric">
                                    <div class="flex flex-col items-center justify-center text-center gap-2">
                                        <CalendarClock :size="28" class="text-primary mb-1 icon-glow" />
                                        <label class="text-[8px] font-mono font-bold text-primary uppercase tracking-wide">TIEMPO ENTREGA</label>
                                        <div class="flex items-baseline gap-1">
                                            <input v-model="form.lead_time_days" 
                                                   type="number" 
                                                   class="w-16 text-center bg-transparent border-b-2 border-primary/30 font-mono font-black text-2xl text-foreground focus:outline-none focus:border-primary transition-colors p-0"
                                                   placeholder="0" />
                                            <span class="text-[10px] font-mono text-primary">días</span>
                                        </div>
                                    </div>
                                    <!-- Esquinas -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary/30"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary/30"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary/30"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary/30"></span>
                                </div>

                                <!-- Min Order -->
                                <div class="col-span-1 p-4 border border-emerald-500/30 bg-emerald-500/5 relative group/metric">
                                    <div class="flex flex-col items-center justify-center text-center gap-2">
                                        <DollarSign :size="28" class="text-emerald-500 mb-1 icon-glow" />
                                        <label class="text-[8px] font-mono font-bold text-emerald-500 uppercase tracking-wide">MÍNIMO COMPRA</label>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-xs font-mono text-emerald-500">$</span>
                                            <input v-model="form.min_order_value" 
                                                   type="number" 
                                                   class="w-20 text-center bg-transparent border-b-2 border-emerald-500/30 font-mono font-black text-2xl text-foreground focus:outline-none focus:border-emerald-500 transition-colors p-0"
                                                   placeholder="0" />
                                        </div>
                                    </div>
                                    <!-- Esquinas -->
                                    <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-emerald-500/30"></span>
                                    <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-emerald-500/30"></span>
                                    <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-emerald-500/30"></span>
                                    <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-emerald-500/30"></span>
                                </div>
                            </div>

                            <!-- Condiciones de Crédito -->
                            <div class="pt-4 border-t border-primary/30">
                                <h4 class="text-[10px] font-mono font-bold text-primary uppercase mb-4 flex items-center gap-2">
                                    <CreditCard :size="14" class="text-primary" /> // CONDICIONES DE CRÉDITO
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-mono text-primary uppercase tracking-wider">DÍAS CRÉDITO</label>
                                        <input v-model="form.credit_days" 
                                               type="number" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="30" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[9px] font-mono text-primary uppercase tracking-wider">LÍMITE (USD)</label>
                                        <input v-model="form.credit_limit" 
                                               type="number" step="0.01" 
                                               class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="1000.00" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Contacto -->
                        <div v-else class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                    // EMAIL PEDIDOS
                                </label>
                                <div class="relative group/input">
                                    <Mail :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                    <input v-model="form.email_orders" 
                                           type="email" 
                                           class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                           placeholder="pedidos@empresa.com" />
                                </div>
                                <p v-if="form.errors.email_orders" class="text-[10px] font-mono text-destructive mt-1 flex items-center gap-1">
                                    <AlertTriangle :size="10" /> {{ form.errors.email_orders }}
                                </p>
                            </div>

                            <!-- Contacto y Teléfono -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                        // CONTACTO
                                    </label>
                                    <div class="relative group/input">
                                        <Users :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                        <input v-model="form.contact_name" 
                                               type="text" 
                                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="JUAN PÉREZ" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                        // MÓVIL
                                    </label>
                                    <div class="relative group/input">
                                        <Phone :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                        <input v-model="form.phone" 
                                               type="tel" 
                                               class="w-full pl-10 pr-4 py-3 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all"
                                               placeholder="+591 77712345" />
                                    </div>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                    // DIRECCIÓN
                                </label>
                                <div class="relative group/input">
                                    <MapPin :size="16" class="absolute left-3 top-3 text-muted-foreground group-focus-within/input:text-primary transition-colors"/>
                                    <textarea v-model="form.address" 
                                              class="w-full pl-10 pr-4 pt-3 pb-2 bg-background border border-border/50 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                              rows="2"
                                              placeholder="DIRECCIÓN COMPLETA"></textarea>
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-primary uppercase tracking-widest mb-2 block">
                                    // NOTAS INTERNAS
                                </label>
                                <textarea v-model="form.notes" 
                                          class="w-full bg-background border border-border/50 px-4 py-3 font-mono text-sm focus:border-primary focus:shadow-neon-primary outline-none transition-all resize-none"
                                          rows="3"
                                          placeholder="INFORMACIÓN INTERNA RELEVANTE..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-6 border-t border-border/50 bg-background/80 backdrop-blur-sm flex justify-between items-center relative z-10">
                        <button type="button" @click="prevStep" 
                                :disabled="currentStep === 1"
                                class="px-6 py-2 border border-border text-[10px] font-mono font-bold uppercase hover:border-primary hover:text-primary transition-all disabled:opacity-30 disabled:pointer-events-none relative group/prev">
                            <span class="flex items-center gap-2">
                                <ArrowLeft :size="14" class="group-hover/prev:-translate-x-1 transition-transform" />
                                ANTERIOR
                            </span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary opacity-0 group-hover/prev:opacity-100"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary opacity-0 group-hover/prev:opacity-100"></span>
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/next overflow-hidden">
                            <span class="flex items-center gap-2 relative z-10">
                                SIGUIENTE <ArrowRight :size="14" class="group-hover/next:translate-x-1 transition-transform" />
                            </span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/next:translate-y-0 transition-transform duration-500"></span>
                            <span class="absolute top-0 left-0 w-1 h-1 border-t border-l border-primary-foreground/50"></span>
                            <span class="absolute top-0 right-0 w-1 h-1 border-t border-r border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 left-0 w-1 h-1 border-b border-l border-primary-foreground/50"></span>
                            <span class="absolute bottom-0 right-0 w-1 h-1 border-b border-r border-primary-foreground/50"></span>
                        </button>

                        <button v-else type="submit"
                                :disabled="form.processing"
                                class="px-8 py-2 bg-primary text-primary-foreground text-[10px] font-mono font-black uppercase shadow-neon-primary hover:bg-primary/90 transition-all relative group/submit overflow-hidden">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <Cpu :size="14" class="animate-spin" /> PROCESANDO...
                            </span>
                            <span v-else class="flex items-center gap-2 relative z-10">
                                <Save :size="14" /> GUARDAR CAMBIOS
                            </span>
                            <span class="absolute inset-0 bg-primary-foreground/10 translate-y-full group-hover/submit:translate-y-0 transition-transform duration-500"></span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Session ID -->
            <div class="mt-4 text-center">
                <p class="text-[8px] font-mono text-muted-foreground">
                    SESSION_ID // {{ providerCode }}_EDIT_{{ String(currentStep).padStart(2, '0') }} // {{ new Date().toISOString().slice(0,10) }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Animaciones */
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.shake {
    animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
}

/* Efecto glitch */
.glitch-text {
    position: relative;
    animation: glitch-skew 4s infinite linear alternate-reverse;
}

.glitch-text::before,
.glitch-text::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
}

.glitch-text::before {
    color: #0ff;
    z-index: -1;
    animation: glitch-anim-1 0.4s infinite linear alternate-reverse;
}

.glitch-text::after {
    color: #f0f;
    z-index: -2;
    animation: glitch-anim-2 0.4s infinite linear alternate-reverse;
}

@keyframes glitch-skew {
    0% { transform: skew(0deg); }
    20% { transform: skew(0deg); }
    21% { transform: skew(2deg); }
    22% { transform: skew(0deg); }
    80% { transform: skew(0deg); }
    81% { transform: skew(-2deg); }
    82% { transform: skew(0deg); }
    100% { transform: skew(0deg); }
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 30% 0); }
    20% { clip-path: inset(50% 0 10% 0); }
    40% { clip-path: inset(10% 0 60% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(30% 0 40% 0); }
    100% { clip-path: inset(40% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(60% 0 10% 0); }
    20% { clip-path: inset(20% 0 50% 0); }
    40% { clip-path: inset(70% 0 5% 0); }
    60% { clip-path: inset(10% 0 70% 0); }
    80% { clip-path: inset(40% 0 30% 0); }
    100% { clip-path: inset(30% 0 40% 0); }
}

/* Sombras neón */
.shadow-neon-primary {
    box-shadow: 0 0 20px hsl(var(--primary) / 0.3);
}

/* Efecto de brillo para íconos */
.icon-glow {
    filter: drop-shadow(0 0 4px currentColor);
    transition: filter 0.3s ease;
}
</style>