<!-- resources/js/Pages/Admin/Providers/Create.vue -->
<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import StepProgress from '@/Components/StepProgress.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue';
    import { ref, computed } from 'vue';
    import { 
        FileText, DollarSign, Users, 
        ArrowRight, ArrowLeft, Save, AlertCircle
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
    
    const handleStepClick = (stepId) => {
        if (currentStep.value >= stepId) {
            currentStep.value = stepId;
        }
    };
    </script>
    
    <template>
        <AdminLayout>
            <div class="max-w-4xl mx-auto py-6">
                
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-display font-semibold text-foreground tracking-tight">
                                Nuevo Proveedor
                            </h1>
                            <p class="text-muted-foreground text-sm mt-1">Registro de socio comercial</p>
                        </div>
                        <Link :href="route('admin.providers.index')" 
                              class="text-sm font-medium text-muted-foreground hover:text-error transition-colors">
                            Cancelar
                        </Link>
                    </div>
    
                    <!-- Componente StepProgress -->
                    <StepProgress 
                        :steps="steps" 
                        :current-step="currentStep" 
                        :progress-percentage="progressPercentage"
                        @step-click="handleStepClick"
                    />
                </div>
    
                <div class="card min-h-[450px] flex flex-col">
                    
                    <form class="flex-1 flex flex-col">
                        
                        <div class="p-6 md:p-8 flex-1">
                            <Transition name="fade" mode="out-in">
                                
                                <div v-if="currentStep === 1" key="1" class="space-y-6 animate-in">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="form-group md:col-span-2">
                                            <label class="form-label">Razón Social *</label>
                                            <input v-model="form.company_name" type="text" 
                                                   class="uppercase font-medium"
                                                   :class="{'border-error': form.errors.company_name}">
                                            <p v-if="form.errors.company_name" class="form-error">
                                                <AlertCircle class="inline mr-1" :size="12"/> 
                                                {{ form.errors.company_name }}
                                            </p>
                                        </div>
    
                                        <div class="form-group">
                                            <label class="form-label">Nombre Comercial</label>
                                            <input v-model="form.commercial_name" type="text">
                                        </div>
    
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="form-group">
                                                <label class="form-label">NIT / RUC *</label>
                                                <input v-model="form.tax_id" type="text" 
                                                       class="font-mono"
                                                       :class="{'border-error': form.errors.tax_id}">
                                                <p v-if="form.errors.tax_id" class="form-error">{{ form.errors.tax_id }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Cód. ERP</label>
                                                <input v-model="form.internal_code" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-border/50">
                                        <BaseCheckbox 
                                            v-model="form.is_active" 
                                            label="Proveedor Activo"
                                        />
                                    </div>
                                </div>
    
                                <div v-else-if="currentStep === 2" key="2" class="space-y-6 animate-in">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="card bg-muted/30 p-4">
                                            <label class="block text-xs font-medium text-muted-foreground uppercase mb-2">
                                                Tiempo Entrega (Días)
                                            </label>
                                            <input v-model="form.lead_time_days" type="number" 
                                                   class="text-center text-lg font-bold bg-card">
                                        </div>
                                        <div class="card bg-muted/30 p-4">
                                            <label class="block text-xs font-medium text-muted-foreground uppercase mb-2">
                                                Compra Mínima (Bs)
                                            </label>
                                            <input v-model="form.min_order_value" type="number" step="0.01" 
                                                   class="text-center text-lg font-bold bg-card">
                                        </div>
                                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-border/50">
                                            <div class="form-group">
                                                <label class="form-label">Días Crédito</label>
                                                <input v-model="form.credit_days" type="number">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Límite Crédito (Bs)</label>
                                                <input v-model="form.credit_limit" type="number" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <div v-else key="3" class="space-y-6 animate-in">
                                    <div class="form-group">
                                        <label class="form-label">Email Pedidos</label>
                                        <input v-model="form.email_orders" type="email"
                                               :class="{'border-error': form.errors.email_orders}">
                                        <p v-if="form.errors.email_orders" class="form-error">{{ form.errors.email_orders }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="form-group">
                                            <label class="form-label">Contacto</label>
                                            <input v-model="form.contact_name" type="text">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Teléfono</label>
                                            <input v-model="form.phone" type="text">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="form-group md:col-span-1">
                                            <label class="form-label">Ciudad</label>
                                            <input v-model="form.city" type="text">
                                        </div>
                                        <div class="form-group md:col-span-2">
                                            <label class="form-label">Dirección</label>
                                            <input v-model="form.address" type="text">
                                        </div>
                                    </div>
                                </div>
    
                            </Transition>
                        </div>
    
                        <div class="px-6 md:px-8 py-4 bg-muted/30 border-t border-border flex justify-between items-center">
                            <button type="button" @click="prevStep" 
                                    class="btn btn-ghost btn-sm"
                                    :disabled="currentStep === 1">
                                <ArrowLeft :size="16" /> Atrás
                            </button>
    
                            <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                    class="btn btn-outline">
                                Siguiente <ArrowRight :size="16" />
                            </button>
    
                            <button v-else type="button" @click="submit"
                                    :disabled="form.processing"
                                    class="btn btn-primary">
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <span class="spinner spinner-sm"></span> Guardando...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <Save :size="18" /> Finalizar
                                </span>
                            </button>
                        </div>
    
                    </form>
                </div>
            </div>
        </AdminLayout>
    </template>
    
    <style scoped>
    .fade-enter-active, .fade-leave-active { 
        transition: opacity 0.2s var(--ease-smooth); 
    }
    .fade-enter-from, .fade-leave-to { 
        opacity: 0; 
    }
    </style>