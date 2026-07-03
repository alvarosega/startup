<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3'; 
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue';
import { Lock, Calendar, ShieldCheck, Edit3, X, Check, Camera } from 'lucide-vue-next';

const props = defineProps({
    profile: { type: Object, required: true },
    availableAvatars: { type: Array, required: true }
});

const page = usePage();

// Enlace estático al nodo de autenticación correcto del middleware global
const customer = computed(() => page.props.auth.customer);

const isInfoModalOpen = ref(false);
const isAvatarModalOpen = ref(false);

// Inicialización de formularios basados en las propiedades exclusivas de la vista
const infoForm = useForm({
    first_name: props.profile?.first_name || '',
    last_name: props.profile?.last_name || '',
    birth_date: props.profile?.birth_date || '',
    gender: props.profile?.gender || 'MALE', 
});

const avatarForm = useForm({
    avatar_source: props.profile?.avatar_source || 'avatar_1.png'
});

const updateInfo = () => {
    infoForm.put(route('customer.profile.update'), {
        onSuccess: () => {
            isInfoModalOpen.value = false;
        },
        preserveScroll: true
    });
};

const updateAvatar = (source) => {
    avatarForm.avatar_source = source;
    avatarForm.post(route('customer.profile.update-avatar'), {
        onSuccess: () => {
            isAvatarModalOpen.value = false;
        },
        preserveScroll: true
    });
};

const genderLabel = {
    MALE: 'Masculino',
    FEMALE: 'Femenino',
    OTHER: 'Otro'
};
</script>

<template>
    <Head title="Mi Perfil" />

    <ShopLayout :is-profile-section="true">
        <div class="max-w-4xl mx-auto pb-24 px-4 py-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 tracking-tight uppercase italic">Mi Perfil</h1>
                <p class="text-zinc-500 text-sm mt-1">Gestión de identidad y parámetros de cuenta.</p>
            </div>

            <ProfileNav />

            <div v-if="customer && profile" class="bg-white border border-zinc-200 rounded-[2.5rem] overflow-hidden shadow-sm mb-8">
                <div class="p-8 flex flex-col md:flex-row items-center gap-8">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden border-4 border-zinc-100 shadow-inner select-none">
                            <img :src="profile.avatar_url" class="w-full h-full object-cover" />
                        </div>
                        <button type="button" @click="isAvatarModalOpen = true" 
                            class="absolute -bottom-2 -right-2 bg-zinc-900 text-white p-3 rounded-2xl shadow-xl hover:scale-110 transition-all active:scale-95">
                            <Camera :size="16" />
                        </button>
                    </div>

                    <div class="flex-1 text-center md:text-left space-y-1">
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <h2 class="text-2xl font-bold text-zinc-900">{{ profile.full_name }}</h2>
                            <ShieldCheck :size="18" class="text-blue-500" />
                        </div>
                        <p class="text-zinc-500 font-medium">{{ customer.email }}</p>
                        <div class="inline-flex items-center px-3 py-1 bg-zinc-100 rounded-full text-[10px] font-black uppercase tracking-widest text-zinc-500 font-mono">
                            ID_ACTIVO: {{ customer.id.split('-')[0] }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50/50 p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-400 shadow-sm">
                            <Lock :size="18" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-400 leading-none mb-1 tracking-widest">Contacto Base</p>
                            <p class="text-sm font-bold text-zinc-700 font-mono">{{ customer.phone }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-400 shadow-sm">
                            <Calendar :size="18" />
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase text-zinc-400 leading-none mb-1 tracking-widest">Metadata Usuario</p>
                            <p class="text-sm font-bold text-zinc-700">
                                {{ profile.birth_date ? profile.birth_date : 'Sin registro' }} • {{ genderLabel[profile.gender] || 'No especificado' }}
                            </p>
                        </div>
                        <button type="button" @click="isInfoModalOpen = true" class="p-2.5 text-zinc-400 hover:text-zinc-900 hover:bg-white rounded-xl border border-transparent hover:border-zinc-200 transition-all shadow-sm">
                            <Edit3 :size="18" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL: ACTUALIZACIÓN METADATA DE USUARIO -->
            <div v-if="isInfoModalOpen" class="fixed inset-0 z-[600] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm" @click="isInfoModalOpen = false"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="px-8 py-6 border-b border-zinc-100 flex justify-between items-center">
                        <h3 class="font-bold text-zinc-900 uppercase text-xs tracking-wider">Metadata de Usuario</h3>
                        <button type="button" @click="isInfoModalOpen = false" class="p-2 hover:bg-zinc-100 rounded-full transition text-zinc-400"><X :size="20" /></button>
                    </div>
                    <form @submit.prevent="updateInfo" class="p-8 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Nombre</label>
                                <input type="text" v-model="infoForm.first_name" required class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all" />
                                <p v-if="infoForm.errors.first_name" class="text-red-500 text-[10px] font-bold uppercase mt-0.5">{{ infoForm.errors.first_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Apellido</label>
                                <input type="text" v-model="infoForm.last_name" required class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all" />
                                <p v-if="infoForm.errors.last_name" class="text-red-500 text-[10px] font-bold uppercase mt-0.5">{{ infoForm.errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Fecha de Nacimiento *</label>
                            <input type="date" v-model="infoForm.birth_date" required class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-mono font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all" :class="{ 'border-red-500': infoForm.errors.birth_date }" />
                            <p v-if="infoForm.errors.birth_date" class="text-red-500 text-[10px] font-bold uppercase mt-0.5 ml-1">{{ infoForm.errors.birth_date }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Género *</label>
                            <select v-model="infoForm.gender" required class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all">
                                <option value="MALE">Masculino</option>
                                <option value="FEMALE">Femenino</option>
                                <option value="OTHER">Otro</option>
                            </select>
                            <p v-if="infoForm.errors.gender" class="text-red-500 text-[10px] font-bold uppercase mt-0.5">{{ infoForm.errors.gender }}</p>
                        </div>

                        <button type="submit" :disabled="infoForm.processing" class="w-full h-14 bg-zinc-900 text-white rounded-2xl font-bold uppercase text-xs tracking-[0.2em] active:scale-95 transition-all disabled:opacity-30 mt-2">
                            <span>{{ infoForm.processing ? 'Sincronizando...' : 'Guardar Cambios' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- MODAL: CAMBIO DE IDENTIDAD VISUAL (AVATAR) -->
            <div v-if="isAvatarModalOpen" class="fixed inset-0 z-[600] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm" @click="isAvatarModalOpen = false"></div>
                <div class="relative bg-white w-full max-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="px-8 py-6 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
                        <h3 class="font-bold text-zinc-900 uppercase text-xs tracking-wider">Identidad Visual</h3>
                        <button type="button" @click="isAvatarModalOpen = false" class="text-zinc-400 hover:text-zinc-900 transition-colors"><X :size="20" /></button>
                    </div>
                    <div class="p-8 grid grid-cols-4 gap-3 bg-white">
                        <button v-for="avatar in availableAvatars" :key="avatar.id"
                            type="button"
                            @click="updateAvatar(avatar.id)"
                            class="aspect-square rounded-2xl border-2 transition-all p-2 relative group"
                            :class="avatarForm.avatar_source === avatar.id ? 'border-zinc-900 bg-zinc-50' : 'border-transparent opacity-40 hover:opacity-100'">
                            <img :src="avatar.url" class="w-full h-full object-contain" />
                            <div v-if="avatarForm.avatar_source === avatar.id" class="absolute -top-1 -right-1 bg-zinc-900 text-white rounded-full p-0.5 shadow-lg">
                                <Check :size="10" />
                            </div>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>