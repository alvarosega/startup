<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3'; 
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue';
import { 
    Lock, Calendar, ShieldCheck, 
    Edit3, X, Check, Camera
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    availableAvatars: Array,
});

// ESTADOS DE CONTROL
const isInfoModalOpen = ref(false);
const isAvatarModalOpen = ref(false);

// FORMULARIO DE INFORMACIÓN PERSONAL (PUT)
const infoForm = useForm({
    birth_date: props.user.profile?.birth_date || '',
    gender: props.user.profile?.gender || 'prefer_not_to_say',
});

// FORMULARIO DE AVATAR (POST)
const avatarForm = useForm({
    avatar_source: props.user.profile?.avatar_source
});

const updateInfo = () => {
    infoForm.put(route('customer.profile.update'), {
        onSuccess: () => isInfoModalOpen.value = false,
        preserveScroll: true
    });
};

const updateAvatar = (source) => {
    avatarForm.avatar_source = source;
    avatarForm.post(route('customer.profile.update-avatar'), {
        onSuccess: () => isAvatarModalOpen.value = false,
        preserveScroll: true
    });
};

const genderLabel = {
    male: 'Masculino',
    female: 'Femenino',
    other: 'Otro',
    prefer_not_to_say: 'No especificado'
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

            <div class="bg-white border border-zinc-200 rounded-[2.5rem] overflow-hidden shadow-sm mb-8">
                <div class="p-8 flex flex-col md:flex-row items-center gap-8">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden border-4 border-zinc-100 shadow-inner">
                            <img :src="user.profile.avatar_url" class="w-full h-full object-cover" />
                        </div>
                        <button @click="isAvatarModalOpen = true" 
                            class="absolute -bottom-2 -right-2 bg-zinc-900 text-white p-3 rounded-2xl shadow-xl hover:scale-110 transition-all active:scale-95">
                            <Camera :size="16" />
                        </button>
                    </div>

                    <div class="flex-1 text-center md:text-left space-y-1">
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <h2 class="text-2xl font-bold text-zinc-900">{{ user.profile.full_name }}</h2>
                            <ShieldCheck :size="18" class="text-blue-500" />
                        </div>
                        <p class="text-zinc-500 font-medium">{{ user.email }}</p>
                        <div class="inline-flex items-center px-3 py-1 bg-zinc-100 rounded-full text-[10px] font-black uppercase tracking-widest text-zinc-500">
                            ID_ACTIVO: {{ user.id.split('-')[0] }}
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
                            <p class="text-sm font-bold text-zinc-700 font-mono">{{ user.phone }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-400 shadow-sm">
                            <Calendar :size="18" />
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase text-zinc-400 leading-none mb-1 tracking-widest">Metadata Usuario</p>
                            <p class="text-sm font-bold text-zinc-700">
                                {{ user.profile.birth_date ? user.profile.birth_date : 'Sin registro' }} • {{ genderLabel[user.profile.gender] }}
                            </p>
                        </div>
                        <button @click="isInfoModalOpen = true" class="p-2.5 text-zinc-400 hover:text-zinc-900 hover:bg-white rounded-xl border border-transparent hover:border-zinc-200 transition-all shadow-sm">
                            <Edit3 :size="18" />
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="isInfoModalOpen" class="fixed inset-0 z-[600] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm" @click="isInfoModalOpen = false"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="px-8 py-6 border-b border-zinc-100 flex justify-between items-center">
                        <h3 class="font-bold text-zinc-900">Metadata de Usuario</h3>
                        <button @click="isInfoModalOpen = false" class="p-2 hover:bg-zinc-100 rounded-full transition text-zinc-400"><X :size="20" /></button>
                    </div>
                    <form @submit.prevent="updateInfo" class="p-8 space-y-6">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Fecha de Nacimiento</label>
                            <input type="date" v-model="infoForm.birth_date" class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-zinc-500 uppercase ml-1">Género</label>
                            <select v-model="infoForm.gender" class="w-full h-12 bg-zinc-50 border-zinc-200 rounded-2xl px-4 text-sm font-medium focus:ring-zinc-900 focus:border-zinc-900 transition-all">
                                <option value="male">Masculino</option>
                                <option value="female">Femenino</option>
                                <option value="other">Otro</option>
                                <option value="prefer_not_to_say">No especificar</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="infoForm.processing" class="w-full h-14 bg-zinc-900 text-white rounded-2xl font-bold uppercase text-xs tracking-[0.2em] active:scale-95 transition-all disabled:opacity-30">
                            <span v-if="infoForm.processing">Sincronizando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </form>
                </div>
            </div>

            <div v-if="isAvatarModalOpen" class="fixed inset-0 z-[600] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm" @click="isAvatarModalOpen = false"></div>
                <div class="relative bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="px-8 py-6 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
                        <h3 class="font-bold text-zinc-900">Identidad Visual</h3>
                        <button @click="isAvatarModalOpen = false" class="text-zinc-400 hover:text-zinc-900 transition-colors"><X :size="20" /></button>
                    </div>
                    <div class="p-8 grid grid-cols-4 gap-3 bg-white">
                        <button v-for="avatar in availableAvatars" :key="avatar.id"
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