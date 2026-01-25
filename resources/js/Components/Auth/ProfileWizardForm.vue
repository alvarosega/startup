<!-- resources/js/Components/Auth/ProfileWizardForm.vue -->
<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { User, Shield, Loader2 } from 'lucide-vue-next';

const emit = defineEmits(['close']);
const user = usePage().props.auth.user;

// Estado local del paso
const step = ref(1);

const formStep1 = useForm({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    birth_date: user?.birth_date || '',
});

const formStep2 = useForm({
    email: user?.email || '',
});

const progress = computed(() => (step.value === 1 ? 50 : 100));

const submitStep1 = () => {
    formStep1.post(route('profile.step1'), {
        onSuccess: () => step.value = 2,
        preserveScroll: true
    });
};

const submitStep2 = () => {
    formStep2.post(route('profile.step2'), {
        onSuccess: () => emit('close'),
        preserveScroll: true
    });
};
</script>

<template>
    <div class="flex flex-col h-full bg-card">
        <!-- Barra de progreso -->
        <div class="h-2 bg-muted w-full">
            <div class="h-full bg-primary transition-all duration-500 ease-smooth" 
                 :style="{ width: progress + '%' }"></div>
        </div>

        <div class="p-6 flex-1 overflow-y-auto">
            <div class="text-center mb-6">
                <!-- Avatar del paso -->
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mx-auto mb-3">
                    <User v-if="step === 1" :size="28" />
                    <Shield v-else :size="28" />
                </div>
                
                <h2 class="text-xl font-display font-semibold text-foreground">
                    {{ step === 1 ? 'Datos Personales' : 'Seguridad de la Cuenta' }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">Paso {{ step }} de 2</p>
            </div>

            <!-- Paso 1: Datos Personales -->
            <form v-if="step === 1" @submit.prevent="submitStep1" class="space-y-4 animate-in">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nombres</label>
                        <input v-model="formStep1.first_name" 
                               :class="{'border-error': formStep1.errors.first_name}">
                        <p v-if="formStep1.errors.first_name" class="form-error">
                            {{ formStep1.errors.first_name }}
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input v-model="formStep1.last_name" 
                               :class="{'border-error': formStep1.errors.last_name}">
                        <p v-if="formStep1.errors.last_name" class="form-error">
                            {{ formStep1.errors.last_name }}
                        </p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input v-model="formStep1.birth_date" 
                           type="date"
                           :class="{'border-error': formStep1.errors.birth_date}">
                    <p v-if="formStep1.errors.birth_date" class="form-error">
                        {{ formStep1.errors.birth_date }}
                    </p>
                </div>
                
                <div class="bg-muted/30 p-3 rounded-lg border border-input">
                    <p class="text-xs text-muted-foreground">
                        <span class="font-medium text-foreground">* Requerido</span> para verificar tu identidad legalmente.
                    </p>
                </div>

                <button type="submit" 
                        :disabled="formStep1.processing"
                        class="btn btn-primary w-full mt-4">
                    <Loader2 v-if="formStep1.processing" class="animate-spin mr-2" :size="18" />
                    <span>Continuar</span>
                </button>
            </form>

            <!-- Paso 2: Seguridad -->
            <form v-if="step === 2" @submit.prevent="submitStep2" class="space-y-4 animate-in">
                <div class="alert alert-info">
                    <strong>Importante:</strong> Verifica tu correo para asegurar la recuperación de tu cuenta.
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input v-model="formStep2.email" 
                           type="email"
                           :class="{'border-error': formStep2.errors.email}">
                    <p v-if="formStep2.errors.email" class="form-error">
                        {{ formStep2.errors.email }}
                    </p>
                </div>

                <button type="submit" 
                        :disabled="formStep2.processing"
                        class="btn btn-primary w-full mt-4">
                    <Loader2 v-if="formStep2.processing" class="animate-spin mr-2" :size="18" />
                    <span>Finalizar y Verificar</span>
                </button>
            </form>
        </div>
    </div>
</template>