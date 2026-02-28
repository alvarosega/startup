<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'; 
import BaseInput from '@/Components/Base/BaseInput.vue'; 
import { LogIn, Lock, Smartphone, ArrowRight, Truck } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const form = useForm({
    phone: '',
    password: '',
    remember: false,
});

// REGLA ESTRICTA: Solo Bolivia
const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    onlyCountries: ['BO'], 
    dropdownOptions: { showSearchBox: false, showFlags: true }, 
    inputOptions: { placeholder: '77712345', autofocus: true } 
};

const onInput = (phone, obj) => { 
    // Garantiza que el frontend entregue el formato internacional (+591...)
    if (obj?.number) {
        form.phone = obj.number; 
    }
};

const submit = () => {
    form.post(route('driver.login'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Terminal de Mando - Driver Access" />
    <ShopLayout> 
        <div class="flex-1 flex items-center justify-center relative overflow-hidden p-4 py-12">
            <div class="w-full max-w-md relative z-10 animate-in fade-in zoom-in-95 duration-700">
                <div class="bg-card backdrop-blur-xl border border-border rounded-[2.5rem] shadow-2xl overflow-hidden">
                    <div class="bg-amber-500 px-6 py-2.5 flex items-center justify-center gap-2">
                        <Truck :size="16" class="text-white fill-current" />
                        <span class="text-[10px] font-black text-white uppercase tracking-[0.3em]">Acceso Operativo</span>
                    </div>

                    <div class="p-8 md:p-10">
                        <div class="text-center mb-10">
                            <h2 class="text-4xl font-display font-black text-foreground tracking-tighter italic uppercase leading-none">
                                Bienvenido <span class="text-amber-500">Driver</span>
                            </h2>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-muted-foreground tracking-[0.2em] ml-1 flex items-center gap-2">
                                    <Smartphone :size="14" class="text-amber-500" /> Enlace Móvil (BO)
                                </label>
                                <vue-tel-input 
                                    v-model="form.phone" 
                                    @on-input="onInput"
                                    v-bind="telOptions"
                                    class="driver-tel-input"
                                    :class="{ '!border-error': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="text-[10px] text-error font-black mt-1 ml-1 uppercase">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-1 ml-1">
                                    <label class="text-[10px] font-black uppercase text-muted-foreground tracking-[0.2em] flex items-center gap-2">
                                        <Lock :size="14" class="text-amber-500" /> Clave
                                    </label>
                                    <Link :href="route('driver.password.request')" class="text-[10px] font-black text-amber-600 hover:text-amber-500 uppercase">Recuperar</Link>
                                </div>
                                <BaseInput v-model="form.password" type="password" placeholder="••••••••" class="h-[56px] font-mono" :error="form.errors.password" />
                            </div>

                            <BaseCheckbox v-model="form.remember" label="Mantener sesión iniciada" />

                            <button type="submit" :disabled="form.processing" class="btn w-full h-16 shadow-lg shadow-primary/20 group bg-amber-500 hover:bg-amber-600 text-white border-none rounded-2xl">
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                <span v-else class="flex items-center justify-center gap-3 font-black uppercase tracking-widest text-base italic">
                                    Iniciar Turno <ArrowRight :size="20" class="group-hover:translate-x-2 transition-transform" />
                                </span>
                            </button>
                            
                            <div class="mt-10 pt-8 border-t border-border/50">
                                <p class="text-sm text-muted-foreground text-center font-medium">
                                    ¿Aún no eres parte de la flota? 
                                    <Link :href="route('driver.register')" class="font-black text-amber-600 hover:underline underline-offset-4 ml-1">
                                        Postúlate aquí
                                    </Link>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.driver-tel-input { 
    @apply w-full rounded-2xl border-border bg-muted/50 text-foreground text-base h-[56px] border transition-all duration-300 font-mono;
}
.driver-tel-input:focus-within { @apply border-amber-500 ring-0 bg-background; }
:deep(.vti__dropdown) { @apply bg-transparent px-3 !important; border-radius: 1.25rem 0 0 1.25rem !important; }
:deep(.vti__input) { @apply bg-transparent text-foreground !important; }
</style>