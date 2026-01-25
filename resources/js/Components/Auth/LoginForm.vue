<script setup>
    import { useForm } from '@inertiajs/vue3';
    import { VueTelInput } from 'vue-tel-input';
    import 'vue-tel-input/vue-tel-input.css';
    import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
    import { useAuthModal } from '@/Composables/useAuthModal';
    
    import { LogIn, Lock, Smartphone } from 'lucide-vue-next';
    
    const emit = defineEmits(['close', 'switchToRegister', 'switchToForgot']);
    const { closeModals } = useAuthModal();
    
    const form = useForm({
        phone: '',
        password: '',
        remember: false,
        device_name: 'Web Browser',
    });
    
    const onInput = (phone, phoneObject) => {
        if (phoneObject?.number) {
            form.phone = phoneObject.number;
        }
    };
    
    const submit = () => {
        form.clearErrors();
    
        form.post(route('login'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModals();
                emit('close');
            },
            onFinish: () => form.reset('password'),
        });
    };
    </script>
    
    <template>
        <div class="p-8">
            <!-- HEADER -->
            <div class="text-center mb-8">
                <div class="avatar avatar-lg bg-gradient-to-br from-primary to-secondary text-primary-foreground mx-auto mb-4 shadow-lg">
                    <LogIn :size="24" />
                </div>
                <h2 class="text-2xl font-display font-black text-foreground uppercase italic">
                    Bienvenido
                </h2>
                <p class="text-sm text-muted-foreground font-medium mt-1">
                    Ingresa para continuar comprando
                </p>
            </div>
    
            <!-- FORM -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- PHONE INPUT -->
                <div class="form-group">
                    <label class="form-label flex items-center gap-2">
                        <Smartphone :size="14" />
                        Celular
                    </label>
                    
                    <vue-tel-input 
                        v-model="form.phone" 
                        @on-input="onInput"
                        mode="international"
                        :preferredCountries="['BO']" 
                        :defaultCountry="'BO'"
                        :inputOptions="{ placeholder: '77712345' }"
                        class="custom-tel-input"
                        :class="{ 'form-input-error': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="form-error">
                        {{ form.errors.phone }}
                    </p>
                </div>
    
                <!-- PASSWORD INPUT -->
                <div class="form-group">
                    <div class="flex justify-between items-center mb-1">
                        <label class="form-label flex items-center gap-2">
                            <Lock :size="14" />
                            Contraseña
                        </label>
                        
                        <button type="button" 
                                @click="$emit('switchToForgot')" 
                                class="text-xs text-primary hover:underline bg-transparent border-0 p-0 cursor-pointer">
                            ¿Olvidaste tu contraseña?
                        </button>
                        
                    </div>
                    
                    <input 
                        v-model="form.password" 
                        type="password" 
                        class="form-input" 
                        placeholder="••••••••"
                    >
                    <p v-if="form.errors.password" class="form-error">
                        {{ form.errors.password }}
                    </p>
                </div>
    
                <!-- REMEMBER ME -->
                <div class="form-group">
                    <BaseCheckbox v-model="form.remember" label="Recordar mi sesión" />
                </div>
    
                <!-- SUBMIT BUTTON -->
                <button type="submit" 
                        :disabled="form.processing"
                        class="btn btn-primary btn-lg w-full shadow-lg hover-lift">
                    <span v-if="form.processing" class="spinner spinner-sm mr-2"></span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <LogIn :size="16" />
                        Ingresar de forma Segura
                    </span>
                </button>
            </form>
    
            <!-- REGISTER LINK -->
            <div class="mt-8 pt-6 border-t border-border text-center">
                <p class="text-sm text-muted-foreground mb-3">
                    ¿No tienes cuenta?
                </p>
                <button @click="$emit('switchToRegister')" 
                        class="btn btn-outline btn-md w-full">
                    Regístrate Gratis
                </button>
            </div>
        </div>
    </template>
    
    <style scoped>
    .custom-tel-input { 
        border: 1px solid hsl(var(--input));
        border-radius: var(--radius-lg);
        padding: 2px 0;
        background: hsl(var(--background));
        transition: all var(--duration-fast) var(--ease-smooth);
    }
    
    .custom-tel-input:focus-within {
        border-color: hsl(var(--ring));
        box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2);
    }
    
    .custom-tel-input.form-input-error {
        border-color: hsl(var(--error));
    }
    
    .custom-tel-input.form-input-error:focus-within {
        border-color: hsl(var(--error));
        box-shadow: 0 0 0 2px hsl(var(--error) / 0.2);
    }
    </style>