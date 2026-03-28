<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3'; 
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProfileNav from './Partials/ProfileNav.vue'; // IMPORTACIÓN DEL NAV
import { 
    Lock, Calendar, ShieldCheck, 
    Edit3, X, Check, Camera
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    availableAvatars: Array,
});

// ... [Mantén aquí tu lógica de infoForm, avatarForm, isInfoModalOpen e isAvatarModalOpen] ...

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
                            class="absolute -bottom-2 -right-2 bg-zinc-900 text-white p-3 rounded-2xl shadow-xl hover:scale-110 transition-all">
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

            </div>

        </ShopLayout>
</template>