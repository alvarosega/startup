<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed } from 'vue';
    
    // Iconos
    import { 
        Building2, FileText, DollarSign, Users, 
        ArrowRight, ArrowLeft, CheckCircle, Save, AlertCircle
    } from 'lucide-vue-next';

    const currentStep = ref(1);
    
    // Configuración de los Pasos y sus campos asociados (para validación)
    const steps = [
        { 
            id: 1, 
            title: 'Identidad Fiscal', 
            icon: FileText, 
            fields: ['company_name', 'tax_id', 'commercial_name', 'internal_code'] 
        },
        { 
            id: 2, 
            title: 'Negocio', 
            icon: DollarSign, 
            fields: ['lead_time_days', 'min_order_value', 'credit_days', 'credit_limit'] 
        },
        { 
            id: 3, 
            title: 'Contacto', 
            icon: Users, 
            fields: ['email_orders', 'contact_name', 'phone', 'address', 'city'] 
        },
    ];

    const form = useForm({
        company_name: '',
        commercial_name: '',
        tax_id: '',
        internal_code: '',
        is_active: true,
        lead_time_days: 1,
        min_order_value: 0,
        credit_days: 0,
        credit_limit: 0,
        contact_name: '',
        email_orders: '',
        phone: '',
        address: '',
        city: '',
        notes: ''
    });

    // --- LÓGICA DE NAVEGACIÓN ---
    const nextStep = () => {
        // Validación simple cliente-side antes de avanzar
        if (currentStep.value === 1) {
            if (!form.company_name || !form.tax_id) {
                // Forzamos mostrar errores si están vacíos
                if(!form.company_name) form.setError('company_name', 'La Razón Social es obligatoria');
                if(!form.tax_id) form.setError('tax_id', 'El NIT es obligatorio');
                return; // No avanzamos
            }
        }
        if (currentStep.value < steps.length) currentStep.value++;
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    // --- ENVÍO ROBUSTO ---
    const submit = () => {
        form.post(route('admin.providers.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // El controlador hace redirect, no necesitamos hacer nada aquí
            },
            onError: (errors) => {
                // SI HAY ERROR, NO RECARGAMOS. BUSCAMOS DÓNDE ESTÁ EL ERROR.
                console.error("Errores de validación:", errors);
                
                // Buscamos el primer paso que tenga un campo con error
                const stepWithError = steps.find(step => 
                    step.fields.some(field => errors[field])
                );

                if (stepWithError) {
                    currentStep.value = stepWithError.id;
                    // Opcional: Mostrar una notificación toast aquí
                    alert(`Error en paso ${stepWithError.id}: Verifique los campos marcados en rojo.`);
                }
            }
        });
    };
    
    const progressPercentage = computed(() => {
        return ((currentStep.value - 1) / (steps.length - 1)) * 100;
    });
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">Nuevo Proveedor</h1>
                        <p class="text-skin-muted text-sm mt-1">Registro de socio comercial</p>
                    </div>
                    <Link :href="route('admin.providers.index')" class="text-sm font-bold text-skin-muted hover:text-skin-danger transition-colors">
                        Cancelar
                    </Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-skin-border -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-skin-primary -z-10 rounded-full transition-all duration-500 ease-out"
                         :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 bg-skin-fill-card"
                                 :class="[
                                    currentStep === step.id ? 'border-skin-primary text-skin-primary shadow-lg scale-110' : 
                                    currentStep > step.id ? 'border-skin-success bg-skin-success text-white' : 
                                    'border-skin-border text-skin-muted'
                                 ]">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-skin-fill px-1"
                                  :class="currentStep >= step.id ? 'text-skin-base' : 'text-skin-muted'">
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-xl overflow-hidden min-h-[450px] flex flex-col">
                
                <form class="flex-1 flex flex-col">
                    
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Razón Social *</label>
                                        <input v-model="form.company_name" type="text" 
                                               class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none uppercase font-bold"
                                               :class="{'border-skin-danger': form.errors.company_name}">
                                        <p v-if="form.errors.company_name" class="text-skin-danger text-xs mt-1 flex items-center gap-1">
                                            <AlertCircle :size="12"/> {{ form.errors.company_name }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Nombre Comercial</label>
                                        <input v-model="form.commercial_name" type="text" 
                                               class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">NIT / RUC *</label>
                                            <input v-model="form.tax_id" type="text" 
                                                   class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none font-mono"
                                                   :class="{'border-skin-danger': form.errors.tax_id}">
                                            <p v-if="form.errors.tax_id" class="text-skin-danger text-xs mt-1">{{ form.errors.tax_id }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Cód. ERP</label>
                                            <input v-model="form.internal_code" type="text" 
                                                   class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none">
                                            <p v-if="form.errors.internal_code" class="text-skin-danger text-xs mt-1">{{ form.errors.internal_code }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-skin-border/50">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input v-model="form.is_active" type="checkbox" class="accent-skin-primary w-5 h-5">
                                        <span class="text-sm font-bold text-skin-base">Proveedor Activo</span>
                                    </label>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-skin-fill/50 p-4 rounded-global border border-skin-border">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Tiempo Entrega (Días)</label>
                                        <input v-model="form.lead_time_days" type="number" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 text-center text-lg font-bold">
                                    </div>
                                    <div class="bg-skin-fill/50 p-4 rounded-global border border-skin-border">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Compra Mínima (Bs)</label>
                                        <input v-model="form.min_order_value" type="number" step="0.01" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 text-center text-lg font-bold">
                                    </div>
                                    <div class="col-span-2 grid grid-cols-2 gap-6 pt-4 border-t border-skin-border/50">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Días Crédito</label>
                                            <input v-model="form.credit_days" type="number" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Límite Crédito (Bs)</label>
                                            <input v-model="form.credit_limit" type="number" step="0.01" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Email Pedidos</label>
                                    <input v-model="form.email_orders" type="email" 
                                           class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary"
                                           :class="{'border-skin-danger': form.errors.email_orders}">
                                    <p v-if="form.errors.email_orders" class="text-skin-danger text-xs mt-1">{{ form.errors.email_orders }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Contacto</label>
                                        <input v-model="form.contact_name" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Teléfono</label>
                                        <input v-model="form.phone" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Ciudad</label>
                                        <input v-model="form.city" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Dirección</label>
                                        <input v-model="form.address" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3">
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center">
                        <button type="button" @click="prevStep" 
                                class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-skin-muted hover:text-skin-base disabled:opacity-0"
                                :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="flex items-center gap-2 px-6 py-2.5 bg-skin-fill-card border border-skin-border hover:border-skin-primary text-skin-base rounded-global text-sm font-bold shadow-sm active:scale-95 transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 px-8 py-2.5 bg-skin-primary hover:bg-skin-primary-hover text-skin-primary-text rounded-global text-sm font-bold shadow-lg shadow-skin-primary/30 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else class="flex items-center gap-2"><Save :size="18" /> Finalizar</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>