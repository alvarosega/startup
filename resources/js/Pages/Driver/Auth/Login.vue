<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';
import { Lock, Smartphone, ArrowRight, Truck } from 'lucide-vue-next';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const form = useForm({
    phone: '',
    password: '',
    remember: false,
});

const telOptions = { 
    mode: 'international', 
    defaultCountry: 'BO', 
    onlyCountries: ['BO'], 
    dropdownOptions: { showSearchBox: false, showFlags: true }, 
    inputOptions: { placeholder: '77712345', autofocus: true } 
};

const onInput = (phone, obj) => { 
    if (obj?.number) {
        form.phone = obj.number; 
    } else if (phone) {
        form.phone = phone.startsWith('+') ? phone : `+591${phone}`;
    }
};

const submit = () => {
    form.post(route('driver.login.store'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Terminal de Mando - Driver Access" />
    <ShopLayout> 
        <div class="flex-1 flex items-center justify-center relative overflow-hidden p-4 py-12">
            <div class="absolute inset-0 z-0 pointer-events-none">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,hsl(var(--primary)/0.03)_0%,transparent_70%)]" />
                <div class="absolute inset-0 bg-dots opacity-20 [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_90%)]" />
            </div>

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
                                    :class="{ '!border-red-500': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="text-[10px] text-red-500 font-black mt-1 ml-1 uppercase">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center mb-1 ml-1">
                                    <label class="text-[10px] font-black uppercase text-muted-foreground tracking-[0.2em] flex items-center gap-2">
                                        <Lock :size="14" class="text-amber-500" /> Clave
                                    </label>
                                    <Link :href="route('driver.password.request')" class="text-[10px] font-black text-amber-600 hover:text-amber-500 uppercase">Recuperar</Link>
                                </div>
                                <input 
                                    v-model="form.password" 
                                    type="password" 
                                    placeholder="••••••••" 
                                    class="w-full h-14 bg-transparent border border-border rounded-2xl px-4 text-sm font-mono font-bold text-foreground focus:border-amber-500 transition-all outline-none"
                                    :class="{ '!border-red-500': form.errors.password }"
                                />
                                <p v-if="form.errors.password" class="text-[10px] text-red-500 font-black mt-1 ml-1 uppercase">{{ form.errors.password }}</p>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer group select-none ml-1">
                                <input 
                                    type="checkbox" 
                                    v-model="form.remember"
                                    class="w-5 h-5 rounded-lg border-2 border-border bg-transparent text-amber-500 focus:ring-0 focus:ring-offset-0 transition-colors cursor-pointer accent-amber-500"
                                />
                                <span class="text-xs font-bold text-muted-foreground group-hover:text-foreground transition-colors">
                                    Mantener sesión iniciada
                                </span>
                            </label>

                            <button type="submit" :disabled="form.processing" class="w-full h-16 shadow-lg shadow-amber-500/20 group bg-amber-500 hover:bg-amber-600 text-white border-none rounded-2xl transition-all active:scale-95 flex items-center justify-center disabled:opacity-50">
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
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
:deep(.driver-tel-input) {
    height: 56px !important;
    background: transparent !important;
    border: 1px solid hsl(var(--border)) !important;
    border-radius: 1rem !important;
    transition: all 0.3s ease;
}
:deep(.driver-tel-input:focus-within) {
    border-color: hsl(var(--amber-500)) !important;
}
:deep(.driver-tel-input .vti__input) {
    background: transparent !important;
    color: currentColor !important;
    font-weight: 700 !important;
}
:deep(.driver-tel-input .vti__dropdown-list) {
    background-color: #15151e !important;
    border: 1px solid #32323b !important;
    color: #ffffff !important;
}
</style>