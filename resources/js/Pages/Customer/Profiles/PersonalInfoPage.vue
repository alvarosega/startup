<script setup>
import { computed, ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import AvatarModal from '@/Components/Base/AvatarModal.vue';
import { 
    Edit2, X, Save, ShieldCheck, Camera, Mail, Phone, 
    Calendar, Smile, ChevronDown, Award, Star, Info
} from 'lucide-vue-next';

const props = defineProps({ user: Object });

// Reactividad de Datos
const profile = computed(() => props.user.profile || {});
const showEditModal = ref(false);
const showAvatarModal = ref(false);

// Lógica de Identidad
const isVerified = computed(() => Boolean(props.user.email_verified_at));
const trustScore = computed(() => props.user.trust_score || 50);

const avatarUrl = computed(() => {
    const u = props.user;
    const p = profile.value;
    if (p.avatar_type === 'storage') return `/storage/${p.avatar_source}`;
    return `/assets/avatars/${p.avatar_source || 'avatar_1.svg'}`;
});

const formatDate = (dateString) => {
    if (!dateString) return 'No registrado';
    return new Date(dateString).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Formulario de Edición
const form = useForm({
    first_name: profile.value.first_name || '', 
    last_name: profile.value.last_name || '', 
    birth_date: profile.value.birth_date || '', 
    gender: profile.value.gender || '', 
    email: props.user.email 
});

const submitEdit = () => {
    form.patch(route('customer.profile.update'), {
        preserveScroll: true,
        onSuccess: () => showEditModal.value = false
    });
};
</script>

<template>
    <Head title="Mi Perfil - Electric Luxury" />
    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-20 px-4">
            
            <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-foreground tracking-tighter uppercase italic text-navy">Mi Cuenta</h1>
                    <p class="text-sm text-muted-foreground uppercase tracking-widest font-bold">Gestión de identidad y reputación</p>
                </div>
                
                <div class="flex items-center gap-3 bg-primary/10 border border-primary/20 px-4 py-2 rounded-2xl">
                    <div class="p-2 bg-primary rounded-xl text-primary-foreground shadow-lg shadow-primary/30">
                        <Award :size="18" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase text-primary tracking-widest leading-none mb-1">Puntos de Confianza</p>
                        <p class="text-lg font-black text-foreground leading-none">{{ trustScore }}<span class="text-xs text-primary/60">/100</span></p>
                    </div>
                </div>
            </div>

            <ProfileNav />

            <div class="bg-card rounded-[2.5rem] border border-border shadow-2xl shadow-navy/5 overflow-hidden p-8 relative">
                
                <div class="flex flex-col items-center text-center">
                    <div class="relative group">
                        <div class="w-36 h-36 rounded-full p-1.5 bg-gradient-to-tr from-primary to-secondary shadow-2xl cursor-pointer active:scale-95 transition-all" 
                             @click="showAvatarModal = true">
                            <img :src="avatarUrl" class="w-full h-full rounded-full object-cover border-4 border-card">
                            <div class="absolute bottom-1 right-1 bg-navy text-white p-2.5 rounded-full border-4 border-card shadow-lg group-hover:scale-110 transition-transform">
                                <Camera :size="16"/>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-3xl font-black text-foreground tracking-tighter">{{ profile.first_name || 'Nuevo' }} {{ profile.last_name || 'Usuario' }}</h3>
                        <div class="flex justify-center gap-2 mt-3">
                            <span v-if="isVerified" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-success/10 text-success text-[10px] font-black uppercase tracking-widest border border-success/20">
                                <ShieldCheck :size="14" /> Cuenta Verificada
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-warning/10 text-warning text-[10px] font-black uppercase tracking-widest border border-warning/20">
                                <Info :size="14" /> Correo sin Verificar
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mt-12">
                    <div class="p-5 bg-muted/20 rounded-3xl border border-border/50 transition-colors hover:bg-muted/40">
                        <Mail class="text-primary mb-2" :size="18" />
                        <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest mb-0.5">Email Principal</p>
                        <p class="font-bold text-foreground truncate">{{ user.email }}</p>
                    </div>
                    <div class="p-5 bg-muted/20 rounded-3xl border border-border/50 transition-colors hover:bg-muted/40">
                        <Phone class="text-primary mb-2" :size="18" />
                        <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest mb-0.5">Teléfono (ID)</p>
                        <p class="font-bold text-foreground">{{ user.phone }}</p>
                    </div>
                    <div class="p-5 bg-muted/20 rounded-3xl border border-border/50 transition-colors hover:bg-muted/40 md:col-span-2 lg:col-span-1">
                        <Calendar class="text-primary mb-2" :size="18" />
                        <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest mb-0.5">Fecha de Nacimiento</p>
                        <p class="font-bold text-foreground">{{ formatDate(profile.birth_date) }}</p>
                    </div>
                </div>

                <div class="mt-10 flex justify-center">
                    <button @click="showEditModal = true" class="group bg-navy text-white px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-navy/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                        <Edit2 :size="16" class="group-hover:rotate-12 transition-transform" /> Editar Perfil
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-4">
            <div class="absolute inset-0 bg-navy/90 backdrop-blur-md transition-opacity" @click="showEditModal = false"></div>
            
            <div class="relative bg-card w-full max-w-lg rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl overflow-hidden animate-in slide-in-from-bottom duration-300 border border-white/10">
                <div class="p-6 border-b border-border flex justify-between items-center bg-muted/20">
                    <h3 class="font-black text-navy uppercase tracking-tighter">Actualizar Datos</h3>
                    <button @click="showEditModal = false" class="p-2 hover:bg-muted rounded-full transition"><X :size="20"/></button>
                </div>

                <form @submit.prevent="submitEdit" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput v-model="form.first_name" label="Nombres" :error="form.errors.first_name" placeholder="Ej: Juan" />
                        <BaseInput v-model="form.last_name" label="Apellidos" :error="form.errors.last_name" placeholder="Ej: Perez" />
                    </div>

                    <BaseInput v-model="form.email" label="Correo Electrónico" :error="form.errors.email" type="email" />

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput v-model="form.birth_date" label="Fecha Nacimiento" type="date" :error="form.errors.birth_date" />
                        
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-muted-foreground ml-1">Género</label>
                            <div class="relative">
                                <select v-model="form.gender" class="w-full bg-muted/30 border-border rounded-2xl p-4 font-bold appearance-none focus:ring-primary">
                                    <option value="">Prefiero no decir</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground" :size="16" />
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" 
                                class="w-full bg-primary text-primary-foreground py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-primary/30 transition-all active:scale-95 disabled:opacity-50">
                            <span v-if="form.processing" class="loading loading-spinner"></span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <Save :size="18"/> Guardar Cambios
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <AvatarModal :show="showAvatarModal" @close="showAvatarModal = false" />
    </ShopLayout> 
</template>