<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { ShieldCheck, ArrowLeft, Clock, MapPin, Building } from 'lucide-vue-next';
import DriverLayout from '@/Layouts/DriverLayout.vue';

const props = defineProps({ order: Object });

const form = useForm({ pickup_otp: '' });

const submit = () => {
    form.post(route('driver.orders.verify-pickup', props.order.code));
};
</script>

<template>
    <DriverLayout>
        <Head :title="'Recogida #' + order.code" />
        <div class="p-6 max-w-md mx-auto min-h-screen flex flex-col justify-center pb-24">
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary/10 text-primary rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                    <Building v-if="order.status === 'preparing'" :size="40" />
                    <ShieldCheck v-else :size="40" />
                </div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter">
                    {{ order.status === 'preparing' ? 'En Tránsito' : 'Validar Carga' }}
                </h1>
                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mt-2">Orden #{{ order.code }}</p>
            </div>

            <div class="bg-muted/30 border border-border p-4 rounded-3xl flex items-center gap-4 mb-6">
                <div class="p-3 bg-background rounded-2xl"><MapPin :size="20" class="text-primary"/></div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Dirígete a Almacén</p>
                    <p class="text-sm font-black uppercase">{{ order.branch.name }}</p>
                    <p class="text-[10px] text-muted-foreground font-bold">{{ order.branch.address }}</p>
                </div>
            </div>

            <div v-if="order.status === 'preparing'" class="bg-amber-500/10 border-2 border-amber-500/20 rounded-[2.5rem] p-8 text-center animate-pulse">
                <Clock class="mx-auto text-amber-500 mb-4" :size="32" />
                <h3 class="text-sm font-black uppercase text-amber-600 tracking-widest mb-2">Almacén Empacando</h3>
                <p class="text-xs font-bold text-amber-600/70">Conduce hacia la sucursal. El código de seguridad se habilitará cuando la caja esté sellada.</p>
            </div>

            <div v-else class="bg-card border-2 border-primary/30 rounded-[2.5rem] p-8 shadow-xl shadow-primary/5">
                <div class="space-y-6">
                    <div class="text-center">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-4">Ingrese el PIN dictado por el Admin</label>
                        <input v-model="form.pickup_otp" type="number" maxlength="5" placeholder="00000"
                            class="w-full text-center text-5xl font-mono font-black tracking-[0.3em] bg-muted border-none rounded-3xl py-6 focus:ring-4 focus:ring-primary/20 transition-all">
                        <p v-if="form.errors.pickup_otp" class="text-[10px] text-destructive font-black uppercase mt-4">{{ form.errors.pickup_otp }}</p>
                    </div>

                    <button @click="submit" :disabled="form.pickup_otp.length < 5 || form.processing"
                        class="w-full h-16 bg-primary text-background rounded-2xl font-black uppercase text-sm tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-20">
                        {{ form.processing ? 'Verificando...' : 'Reclamar Pedido' }}
                    </button>
                </div>
            </div>

        </div>
    </DriverLayout>
</template>