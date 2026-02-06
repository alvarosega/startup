<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
    User, Edit2, X, Save, ShieldCheck, Camera, ChevronDown, 
    Mail, Phone, Calendar, Smile
} from 'lucide-vue-next';
import BaseInput from '@/Components/Base/BaseInput.vue';
import AvatarModal from '@/Components/Base/AvatarModal.vue';

const props = defineProps({ user: Object });

const isOpen = ref(true); 
const profile = computed(() => props.user.profile || {});
const isVerified = computed(() => Boolean(profile.value.is_identity_verified));
const showEditModal = ref(false);
const showAvatarModal = ref(false);

const avatarUrl = computed(() => {
    const u = props.user;
    if (!u) return '/assets/avatars/avatar_1.svg';
    return u.avatar_type === 'storage' ? `/storage/${u.avatar_source}` : `/assets/avatars/${u.avatar_source || 'avatar_1.svg'}`;
});

const displayName = computed(() => {
    const first = profile.value.first_name || '';
    const last = profile.value.last_name || '';
    return (first + ' ' + last).trim() || 'Usuario Sin Nombre';
});

const formatDate = (dateString) => {
    if (!dateString) return 'No registrado';
    const cleanDate = String(dateString).split('T')[0];
    const parts = cleanDate.split('-');
    if (parts.length !== 3) return dateString;
    const date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
};

const form = useForm({
    first_name: '', last_name: '', birth_date: '', gender: '', email: ''
});

const openEdit = () => {
    form.first_name = profile.value.first_name;
    form.last_name = profile.value.last_name;
    if (profile.value.birth_date) {
        form.birth_date = String(profile.value.birth_date).split('T')[0].split(' ')[0];
    } else {
        form.birth_date = '';
    }
    form.gender = profile.value.gender;
    form.email = props.user.email;
    showEditModal.value = true;
};

const submitEdit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => showEditModal.value = false
    });
};
</script>

<template>
    <section class="bg-card rounded-3xl shadow-sm border border-border overflow-hidden transition-all duration-300">
        
        <button @click="isOpen = !isOpen" class="w-full relative overflow-hidden group text-left transition-colors hover:bg-muted/20">
            <div class="relative p-6 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                        <User :size="20" stroke-width="2.5" />
                    </div>
                    <div>
                        <h2 class="font-black text-foreground text-lg tracking-tight leading-none">Personal</h2>
                        <p class="text-xs text-muted-foreground font-medium mt-1">Identidad y Contacto</p>
                    </div>
                </div>
                
                <div class="w-8 h-8 rounded-full bg-muted/50 flex items-center justify-center text-muted-foreground transition-all duration-300 border border-transparent group-hover:border-border" 
                     :class="{'rotate-180 bg-primary/10 text-primary border-primary/20': isOpen}">
                    <ChevronDown :size="18"/>
                </div>
            </div>
        </button>
        
        <div v-show="isOpen" class="animate-in slide-in-from-top-2 duration-300 ease-out origin-top">
            
            <div class="px-6 pb-8 pt-2 text-center relative border-t border-border/50">
                <div class="relative inline-block mt-4">
                    <div class="w-24 h-24 rounded-full p-1 bg-card border-2 border-border shadow-lg cursor-pointer active:scale-95 transition-transform group" 
                         @click="showAvatarModal = true">
                        <img :src="avatarUrl" class="w-full h-full rounded-full object-cover">
                        <div class="absolute bottom-0 right-0 bg-foreground text-background p-1.5 rounded-full border-2 border-card shadow-sm group-hover:scale-110 transition-transform">
                            <Camera :size="12"/>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="text-xl font-black text-foreground tracking-tight">{{ displayName }}</h3>
                    <div class="flex justify-center mt-2">
                        <span v-if="isVerified" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-success/10 text-success text-[10px] font-black uppercase tracking-widest border border-success/20">
                            <ShieldCheck :size="12" /> Verificado
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-muted text-muted-foreground text-[10px] font-black uppercase tracking-widest border border-border">
                            Sin Verificar
                        </span>
                    </div>
                </div>

                <button @click="openEdit" class="absolute top-4 right-6 text-primary hover:bg-primary/10 p-2 rounded-xl transition active:scale-95 border border-transparent hover:border-primary/20">
                    <span class="sr-only">Editar</span>
                    <Edit2 :size="18"/>
                </button>
            </div>

            <div class="px-4 pb-6 space-y-3">
                <div class="flex items-center p-4 bg-muted/30 rounded-2xl border border-border/50">
                    <div class="w-10 h-10 rounded-full bg-card flex items-center justify-center text-muted-foreground shadow-sm shrink-0 border border-border/50">
                        <Mail :size="18"/>
                    </div>
                    <div class="ml-4 overflow-hidden">
                        <p class="text-[10px] text-muted-foreground/80 uppercase font-bold tracking-wider">Email</p>
                        <p class="font-bold text-foreground text-sm truncate">{{ user.email }}</p>
                    </div>
                </div>

                <div class="flex items-center p-4 bg-muted/30 rounded-2xl border border-border/50">
                    <div class="w-10 h-10 rounded-full bg-card flex items-center justify-center text-muted-foreground shadow-sm shrink-0 border border-border/50">
                        <Phone :size="18"/>
                    </div>
                    <div class="ml-4">
                        <p class="text-[10px] text-muted-foreground/80 uppercase font-bold tracking-wider">Teléfono</p>
                        <p class="font-bold text-foreground text-sm">{{ user.phone }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-4 bg-muted/30 rounded-2xl border border-border/50">
                        <div class="flex items-center gap-2 mb-2 text-muted-foreground">
                            <Calendar :size="16"/>
                            <span class="text-[10px] uppercase font-bold tracking-wider">Nacimiento</span>
                        </div>
                        <p class="font-bold text-foreground text-sm">{{ formatDate(profile.birth_date) }}</p>
                    </div>
                    <div class="p-4 bg-muted/30 rounded-2xl border border-border/50">
                        <div class="flex items-center gap-2 mb-2 text-muted-foreground">
                            <Smile :size="16"/>
                            <span class="text-[10px] uppercase font-bold tracking-wider">Género</span>
                        </div>
                        <p class="font-bold text-foreground text-sm capitalize">{{ profile.gender || '---' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
            <div class="absolute inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
            
            <div class="relative bg-card w-full md:max-w-lg md:rounded-2xl rounded-t-[32px] shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[90vh] border border-border">
                
                <div class="md:hidden w-full flex justify-center pt-3 pb-1 bg-card" @click="showEditModal = false">
                    <div class="w-12 h-1.5 bg-muted rounded-full"></div>
                </div>

                <div class="px-6 py-4 flex justify-between items-center border-b border-border bg-card">
                    <h3 class="font-black text-xl text-foreground tracking-tight">Editar Perfil</h3>
                    <button @click="showEditModal = false" class="p-2 bg-muted/50 hover:bg-muted rounded-full transition text-muted-foreground hover:text-foreground">
                        <X :size="20"/>
                    </button>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar bg-card">
                    
                    <div v-if="isVerified" class="bg-primary/10 p-4 rounded-2xl text-xs text-primary border border-primary/20 flex gap-3">
                        <ShieldCheck :size="18" class="shrink-0 mt-0.5"/>
                        <p class="leading-relaxed">Tu identidad está <span class="font-bold">verificada</span>. Algunos datos están bloqueados por seguridad.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.first_name" label="Nombres" :disabled="isVerified" :error="form.errors.first_name"/>
                            <BaseInput v-model="form.last_name" label="Apellidos" :disabled="isVerified" :error="form.errors.last_name"/>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5 ml-1">Teléfono (ID)</label>
                            <div class="flex items-center px-4 py-3 bg-muted/30 border border-border rounded-xl text-muted-foreground cursor-not-allowed">
                                <span class="text-sm font-medium">{{ user.phone }}</span>
                                <ShieldCheck :size="14" class="ml-auto opacity-50"/>
                            </div>
                        </div>

                        <BaseInput v-model="form.email" label="Correo Electrónico" :error="form.errors.email"/>

                        <div class="grid grid-cols-2 gap-4">
                            <BaseInput v-model="form.birth_date" type="date" label="Fecha Nacimiento" :disabled="isVerified" :error="form.errors.birth_date"/>
                            
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider ml-1">Género</label>
                                <div class="relative">
                                    <select v-model="form.gender" :disabled="isVerified" 
                                            class="w-full appearance-none bg-background border border-input rounded-xl px-4 py-3 text-sm font-bold text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary disabled:bg-muted disabled:text-muted-foreground transition-all">
                                        <option value="">Seleccionar...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="O">Otro</option>
                                    </select>
                                    <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" :size="16"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-border bg-muted/10 pb-safe">
                    <button @click="submitEdit" :disabled="form.processing" 
                            class="w-full bg-primary hover:bg-primary/90 text-primary-foreground py-4 rounded-xl font-bold text-sm flex justify-center items-center gap-2 shadow-xl shadow-primary/20 transition-all active:scale-[0.98]">
                        <Save :size="18"/> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>

        <AvatarModal :show="showAvatarModal" @close="showAvatarModal = false" />
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

.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 1rem);
}
</style>