<script setup>
    import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
    import { Head, usePage, useForm, Link, router } from '@inertiajs/vue3';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import ConfirmPasswordModal from '@/Components/Base/ConfirmPasswordModal.vue';
    import { ArrowLeft, Lock, Save } from 'lucide-vue-next';
    
    // --- CORRECCIÓN 1: Definir Props ---
    // Recibimos el 'user' que envía el controlador (el que tiene el perfil cargado)
    const props = defineProps({
        user: Object,
        status: String,
    });

    // --- CORRECCIÓN 2: Usar props.user en lugar de page.props.auth.user ---
    // props.user viene con ->load('profile')
    const user = computed(() => props.user); 
    
    // Ahora profile SÍ existirá
    const profile = computed(() => user.value.profile || {});
    const isLocked = computed(() => !!profile.value.is_identity_verified);
    
    const showConfirmModal = ref(false);
    
    // Inicialización del formulario (Ahora tendrá datos)
    const form = useForm({
        first_name: profile.value.first_name || '',
        last_name: profile.value.last_name || '',
        birth_date: profile.value.birth_date || '',
        gender: profile.value.gender || '',
        email: user.value.email || '',
    });
    
    let initialEmail = user.value.email;
    
    // Protección de cambios sin guardar
    const confirmLeave = (e) => { if (form.isDirty) { e.preventDefault(); e.returnValue = ''; } };
    onMounted(() => {
        window.addEventListener('beforeunload', confirmLeave);
        const removeGuard = router.on('start', (event) => {
            if (form.isDirty && !confirm('Tienes cambios sin guardar. ¿Salir?')) event.preventDefault();
        });
        onBeforeUnmount(() => removeGuard());
    });
    onBeforeUnmount(() => window.removeEventListener('beforeunload', confirmLeave));
    
    const handleSave = () => {
        if (isLocked.value) return;
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
        <Head title="Editar Datos" />
    
        <ProfileLayout>
            <div class="mb-6">
                <Link :href="route('profile.index')" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition">
                    <ArrowLeft :size="16" /> Volver (Cancelar)
                </Link>
            </div>
    
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h1 class="text-xl font-black text-gray-800 tracking-tight">Editar Información</h1>
                    <div v-if="form.isDirty" class="text-xs font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full animate-pulse">● Sin Guardar</div>
                </div>
    
                <div class="p-8">
                    <div v-if="isLocked" class="mb-6 p-4 bg-blue-50 text-blue-800 rounded-xl text-xs flex gap-3 items-center border border-blue-100">
                        <Lock :size="20" />
                        <p class="font-bold">Datos Protegidos: Tu identidad está verificada. Contacta a soporte para cambios.</p>
                    </div>
    
                    <form @submit.prevent="handleSave" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput v-model="form.first_name" label="Nombres" :disabled="isLocked" :error="form.errors.first_name" />
                            <BaseInput v-model="form.last_name" label="Apellidos" :disabled="isLocked" :error="form.errors.last_name" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput v-model="form.birth_date" type="date" label="Fecha Nacimiento" :disabled="isLocked" :error="form.errors.birth_date" />
                            <div class="flex flex-col">
                                <label class="text-[10px] font-black text-gray-400 mb-2 uppercase italic">Género</label>
                                <select v-model="form.gender" :disabled="isLocked" class="w-full rounded-xl border-gray-300 text-sm p-3 focus:ring-blue-600 disabled:bg-gray-100">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="pnd">Prefiero no decirlo</option>
                                </select>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-6">
                            <BaseInput v-model="form.email" type="email" label="Correo Electrónico" :error="form.errors.email" />
                            <p class="text-[10px] text-gray-400 mt-2">* Si cambias tu correo, te pediremos confirmar tu contraseña.</p>
                        </div>
                        <div class="pt-6 flex justify-end gap-4 mt-4" v-if="!isLocked || form.email !== initialEmail">
                            <Link :href="route('profile.index')" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-800">Cancelar</Link>
                            <BaseButton :isLoading="form.processing" :disabled="!form.isDirty" class="px-8 shadow-lg shadow-blue-500/30 flex items-center gap-2">
                                <Save :size="16" /> Guardar Cambios
                            </BaseButton>
                        </div>
                    </form>
                </div>
            </div>
    
            <ConfirmPasswordModal :show="showConfirmModal" @close="showConfirmModal = false" @confirmed="submitForm" />
        </ProfileLayout>
    </template>