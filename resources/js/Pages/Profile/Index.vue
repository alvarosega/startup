<script setup>
    import { computed, ref } from 'vue';
    import { Head, usePage, Link, router } from '@inertiajs/vue3';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import AvatarModal from '@/Components/Base/AvatarModal.vue';
    import { User, MapPin, Shield, AlertCircle, Camera, CheckCircle, Edit2, Mail, Phone, Calendar, Smile } from 'lucide-vue-next';
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const profile = computed(() => user.value.profile || {});
    const addressesCount = computed(() => user.value.addresses_count || 0);
    const isVerified = computed(() => profile.value.is_identity_verified);
    
    // NUEVA LÓGICA: ¿Está incompleto? (Si no tiene nombre, consideramos que sí)
    const isProfileIncomplete = computed(() => !profile.value.first_name);

    const showAvatarModal = ref(false);
    
    const resendVerification = () => {
        router.post(route('verification.send'), {}, { preserveScroll: true });
    };
    
    const formatDate = (date) => {
        if (!date) return 'No registrado';
        return new Date(date).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
    };
</script>
    
<template>
    <Head title="Mi Perfil" />

    <ProfileLayout>
        
        <div v-if="!user.email_verified_at" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full"><AlertCircle :size="20"/></div>
                <div class="text-xs text-yellow-800">
                    <strong>Verifica tu correo.</strong> Necesario para facturación y seguridad.
                </div>
            </div>
            <button @click="resendVerification" class="text-xs font-bold bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg hover:bg-yellow-200 transition">Reenviar</button>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row items-center gap-6 relative">
                <div class="relative group shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gray-100 border-4 border-white shadow-lg overflow-hidden">
                        <img :src="user.avatar_url" class="w-full h-full object-cover" />
                    </div>
                    <button @click="showAvatarModal = true" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-md hover:bg-blue-500 transition transform hover:scale-110 z-20">
                        <Camera :size="14" />
                    </button>
                </div>
                
                <div class="text-center sm:text-left flex-1">
                    <h2 class="text-2xl font-black text-gray-900">
                        {{ isProfileIncomplete ? '¡Bienvenido!' : `${profile.first_name} ${profile.last_name}` }}
                    </h2>

                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-2">Cliente</p>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                        <span v-if="user.email_verified_at" class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold uppercase rounded border border-blue-100 flex items-center gap-1"><CheckCircle :size="10" /> Email OK</span>
                        <span v-if="isVerified" class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] font-bold uppercase rounded border border-green-100 flex items-center gap-1"><Shield :size="10" /> Verificado</span>
                        <span v-else class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase rounded border border-gray-200 flex items-center gap-1"><AlertCircle :size="10" /> Sin Verificar</span>
                    </div>
                </div>

                <Link :href="route('profile.edit')" 
                    class="px-5 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2 shadow-lg"
                    :class="isProfileIncomplete ? 'bg-blue-600 hover:bg-blue-500 text-white ring-4 ring-blue-50' : 'bg-gray-900 hover:bg-gray-800 text-white'"
                >
                    <Edit2 :size="14" /> 
                    {{ isProfileIncomplete ? 'Completar Perfil' : 'Editar Datos' }}
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-sm">Información Personal</h3>
                </div>
                <div v-if="isProfileIncomplete" class="p-8 text-center">
                    <div class="inline-flex bg-blue-50 p-3 rounded-full mb-3 text-blue-600">
                        <User :size="24" />
                    </div>
                    <h4 class="font-bold text-gray-800">Tu perfil está vacío</h4>
                    <p class="text-sm text-gray-500 mb-4">Completa tu información para agilizar tus pedidos.</p>
                </div>

                <div v-else class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><User :size="18" /></div>
                        <div><p class="text-[10px] font-bold text-gray-400 uppercase">Nombre Completo</p><p class="text-sm font-medium text-gray-900">{{ profile.first_name }} {{ profile.last_name }}</p></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg"><Mail :size="18" /></div>
                        <div><p class="text-[10px] font-bold text-gray-400 uppercase">Email</p><p class="text-sm font-medium text-gray-900">{{ user.email }}</p></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-green-50 text-green-600 rounded-lg"><Phone :size="18" /></div>
                        <div><p class="text-[10px] font-bold text-gray-400 uppercase">Teléfono</p><p class="text-sm font-medium text-gray-900">{{ user.phone }}</p></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-orange-50 text-orange-600 rounded-lg"><Calendar :size="18" /></div>
                        <div><p class="text-[10px] font-bold text-gray-400 uppercase">Nacimiento</p><p class="text-sm font-medium text-gray-900">{{ formatDate(profile.birth_date) }}</p></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-pink-50 text-pink-600 rounded-lg"><Smile :size="18" /></div>
                        <div><p class="text-[10px] font-bold text-gray-400 uppercase">Género</p><p class="text-sm font-medium text-gray-900 capitalize">{{ profile.gender || 'No especificado' }}</p></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link :href="route('addresses.index')" class="bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-400 transition group flex justify-between items-center">
                    <div><h3 class="font-bold text-gray-800 text-sm">Mis Direcciones</h3><p class="text-xs text-gray-500">{{ addressesCount }} guardadas</p></div>
                    <MapPin class="text-gray-300 group-hover:text-blue-500 transition" :size="24" />
                </Link>
                <Link :href="route('profile.security')" class="bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-400 transition group flex justify-between items-center">
                    <div><h3 class="font-bold text-gray-800 text-sm">Seguridad</h3><p class="text-xs text-gray-500">Contraseña</p></div>
                    <Shield class="text-gray-300 group-hover:text-blue-500 transition" :size="24" />
                </Link>
            </div>
        </div>

        <AvatarModal :show="showAvatarModal" @close="showAvatarModal = false" />
    </ProfileLayout>
</template>