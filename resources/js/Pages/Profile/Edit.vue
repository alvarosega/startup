<script setup>
    import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
    import { Head, useForm, Link } from '@inertiajs/vue3';
    // CAMBIO 1: Importar ShopLayout
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import ConfirmPasswordModal from '@/Components/Base/ConfirmPasswordModal.vue';
    import { ArrowLeft, Lock, Save, AlertTriangle, ShieldCheck } from 'lucide-vue-next';
    
    // ... resto de tu lógica de script (form, props, computed) ...
    // (Mantén el código de script que te pasé en la respuesta anterior que arreglaba el género y el bloqueo)
    // Solo asegúrate de NO importar ProfileLayout
    const props = defineProps({
        user: Object, 
    });

    const user = computed(() => props.user || {});
    const profile = computed(() => user.value.profile || {});
    
    const isLocked = computed(() => Boolean(profile.value.is_identity_verified));
    
    const showConfirmModal = ref(false);
    
    const form = useForm({
        first_name: profile.value.first_name || '',
        last_name:  profile.value.last_name || '',
        birth_date: profile.value.birth_date || '',
        gender:     profile.value.gender || '', 
        email:      user.value.email || '',
    });
    
    let initialEmail = user.value.email;
    
    const confirmLeave = (e) => { if (form.isDirty) { e.preventDefault(); e.returnValue = ''; } };
    onMounted(() => window.addEventListener('beforeunload', confirmLeave));
    onBeforeUnmount(() => window.removeEventListener('beforeunload', confirmLeave));
    
    const handleSave = () => {
        if (form.email !== initialEmail) {
            showConfirmModal.value = true;
        } else {
            submitForm();
        }
    };
    
    const submitForm = () => {
        showConfirmModal.value = false;
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                initialEmail = form.email; 
                form.defaults({ 
                    first_name: form.first_name,
                    last_name: form.last_name,
                    birth_date: form.birth_date,
                    gender: form.gender,
                    email: form.email
                });
                form.reset(); 
            }
        });
    };
</script>
    
<template>
    <Head title="Editar Perfil" />

    <ShopLayout :is-profile-section="true">
        <div class="mb-6">
            <Link :href="route('profile.index')" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition">
                <ArrowLeft :size="16" /> Volver al Perfil
            </Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl mx-auto">
            
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h1 class="text-xl font-black text-gray-800 tracking-tight">Información Personal</h1>
                <div v-if="form.isDirty" class="text-[10px] font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full animate-pulse uppercase tracking-wider">
                    Cambios sin guardar
                </div>
            </div>

            <div class="p-8">
                
                <div v-if="isLocked" class="mb-8 p-4 bg-blue-50 text-blue-800 rounded-xl text-xs flex gap-3 items-start border border-blue-100 shadow-sm">
                    <div class="p-2 bg-white rounded-full text-blue-600 shrink-0">
                        <ShieldCheck :size="20" />
                    </div>
                    <div>
                        <p class="font-black text-sm mb-1">Identidad Verificada</p>
                        <p class="opacity-80 leading-relaxed">
                            Por seguridad, tu nombre, fecha de nacimiento y género están bloqueados porque ya validamos tu documentación. 
                            Si necesitas corregir un error legal, por favor contacta a soporte.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="handleSave" class="space-y-8">
                    
                    <div class="relative">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Identidad Legal</h3>
                        
                        <div v-if="isLocked" class="absolute inset-0 bg-white/50 z-10 cursor-not-allowed"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput v-model="form.first_name" label="Nombres" :disabled="isLocked" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" :disabled="isLocked" :error="form.errors.last_name" />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <BaseInput v-model="form.birth_date" type="date" label="Fecha de Nacimiento" :disabled="isLocked" :error="form.errors.birth_date" />
                            
                            <div class="flex flex-col">
                                <label class="text-[10px] font-black text-gray-400 mb-2 uppercase tracking-wide">Género</label>
                                <select v-model="form.gender" :disabled="isLocked" class="w-full rounded-xl border-gray-300 text-sm p-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500 transition shadow-sm appearance-none" :class="{'border-red-500 focus:ring-red-500/20': form.errors.gender}">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                                <p v-if="form.errors.gender" class="text-xs text-red-500 mt-1 font-bold">{{ form.errors.gender }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Datos de Acceso</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <BaseInput v-model="form.email" type="email" label="Correo Electrónico" :error="form.errors.email" />
                                <div class="mt-2 flex items-center gap-1.5 text-[10px] text-orange-600 bg-orange-50 w-fit px-2 py-1 rounded">
                                    <AlertTriangle :size="10"/>
                                    <span>Cambiarlo requerirá nueva verificación</span>
                                </div>
                            </div>
                            <div class="opacity-60">
                                <label class="text-[10px] font-black text-gray-400 mb-2 uppercase tracking-wide">Teléfono (ID)</label>
                                <div class="w-full rounded-xl bg-gray-100 border border-gray-200 text-sm p-3 text-gray-500 font-mono">
                                    {{ user.phone || 'No registrado' }}
                                </div>
                                <p class="text-[9px] text-gray-400 mt-1">No se puede cambiar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end gap-4 border-t border-gray-100">
                        <Link :href="route('profile.index')" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800 transition">Cancelar</Link>
                        <BaseButton :isLoading="form.processing" :disabled="!form.isDirty" class="px-8 shadow-xl shadow-blue-500/20 bg-gray-900 hover:bg-black text-white flex items-center gap-2">
                            <Save :size="18" /> Guardar Cambios
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmPasswordModal :show="showConfirmModal" @close="showConfirmModal = false" @confirmed="submitForm" />
    </ShopLayout>
</template>