<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Shield, Lock, Save, X, ChevronDown, KeyRound } from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';

const isOpen = ref(false);
const showModal = ref(false);
const form = useForm({ current_password: '', password: '', password_confirmation: '' });

const submit = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); }
    });
};
</script>

<template>
    <section class="bg-card rounded-3xl shadow-sm border border-border overflow-hidden transition-all duration-300">
        
        <button @click="isOpen = !isOpen" class="w-full relative overflow-hidden group text-left transition-colors hover:bg-muted/20">
            <div class="relative p-6 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-violet-500/10 text-violet-500 flex items-center justify-center border border-violet-500/20">
                        <Shield :size="20" stroke-width="2.5" />
                    </div>
                    <div>
                        <h2 class="font-black text-foreground text-lg tracking-tight leading-none">Seguridad</h2>
                        <p class="text-xs text-muted-foreground font-medium mt-1">Protección de cuenta</p>
                    </div>
                </div>
                
                <div class="w-8 h-8 rounded-full bg-muted/50 flex items-center justify-center text-muted-foreground transition-all duration-300 border border-transparent group-hover:border-border" 
                     :class="{'rotate-180 bg-primary/10 text-primary border-primary/20': isOpen}">
                    <ChevronDown :size="18"/>
                </div>
            </div>
        </button>

        <div v-show="isOpen" class="animate-in slide-in-from-top-2 duration-300 ease-out origin-top">
            <div class="p-6 bg-card border-t border-border/50">
                
                <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50 transition-colors hover:bg-muted/50">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-background rounded-xl text-muted-foreground shadow-sm border border-border">
                            <KeyRound :size="20" />
                        </div>
                        <div>
                            <h4 class="font-bold text-foreground text-sm">Contraseña</h4>
                            <p class="text-xs text-muted-foreground">Último cambio hace 3 meses</p>
                        </div>
                    </div>
                    <button @click="showModal = true" class="text-xs font-bold bg-background hover:bg-muted text-foreground px-4 py-2.5 rounded-xl shadow-sm border border-border transition active:scale-95">
                        Cambiar
                    </button>
                </div>

                <div class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-blue-500/5 border border-blue-500/10">
                    <Shield :size="16" class="text-blue-500 mt-0.5 shrink-0" />
                    <p class="text-[11px] text-muted-foreground leading-snug">
                        Recomendamos usar una contraseña fuerte con caracteres especiales para mayor seguridad.
                    </p>
                </div>

            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-md transition-opacity" @click="showModal = false"></div>
            
            <div class="relative bg-card w-full md:max-w-md md:rounded-2xl rounded-t-[32px] shadow-2xl flex flex-col overflow-hidden animate-slide-up border border-border">
                
                <div class="md:hidden w-full flex justify-center pt-3 pb-1 bg-card" @click="showModal = false">
                    <div class="w-12 h-1.5 bg-muted rounded-full"></div>
                </div>

                <div class="px-6 py-4 flex justify-between items-center border-b border-border bg-card">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-violet-500/10 text-violet-500 rounded-lg">
                            <Lock :size="18" />
                        </div>
                        <h3 class="font-black text-foreground text-base uppercase tracking-wider">Nueva Contraseña</h3>
                    </div>
                    <button @click="showModal = false" class="p-2 bg-muted/50 hover:bg-muted rounded-full transition text-muted-foreground hover:text-foreground">
                        <X :size="20"/>
                    </button>
                </div>

                <div class="p-6 bg-card">
                    <form @submit.prevent="submit" class="space-y-5">
                        
                        <div class="bg-muted/20 p-4 rounded-xl border border-border/50 space-y-4">
                            <BaseInput 
                                v-model="form.current_password" 
                                type="password" 
                                label="Contraseña Actual" 
                                :error="form.errors.current_password"
                                placeholder="••••••••"
                            />
                        </div>

                        <div class="space-y-4">
                            <BaseInput 
                                v-model="form.password" 
                                type="password" 
                                label="Nueva Contraseña" 
                                :error="form.errors.password"
                                placeholder="Mínimo 8 caracteres"
                            />
                            <BaseInput 
                                v-model="form.password_confirmation" 
                                type="password" 
                                label="Confirmar Contraseña"
                                placeholder="Repite la nueva contraseña"
                            />
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="form.processing" 
                                    class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-4 rounded-xl font-bold text-sm flex justify-center items-center gap-2 shadow-lg shadow-primary/20 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                <Save :size="18" /> Actualizar Seguridad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>