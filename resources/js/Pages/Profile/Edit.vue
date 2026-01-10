<script setup>
    import { computed } from 'vue';
    import { useForm, usePage, Head } from '@inertiajs/vue3';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    
    // --- LÓGICA DE NIVELES (Cerraduras) ---
    
    // Nivel 1: Se bloquea si tiene Nombres, Apellidos y Fecha
    const isLevel1Locked = computed(() => {
        return !!(user.value.profile?.first_name && 
                  user.value.profile?.last_name && 
                  user.value.profile?.birth_date);
    });
    
    // Nivel 2: Se bloquea si tiene Email y Género
    const isLevel2Locked = computed(() => {
        return isLevel1Locked.value && 
               !!(user.value.email && user.value.profile?.gender);
    });
    
    const isVerified = computed(() => !!user.value.profile?.is_identity_verified);
    
    // --- FORMULARIO 1: DATOS (PATCH) ---
    const form = useForm({
        first_name: user.value.profile?.first_name || '',
        last_name: user.value.profile?.last_name || '',
        birth_date: user.value.profile?.birth_date || '',
        gender: user.value.profile?.gender || null,
        email: user.value.email || null,
        // Datos Driver
        license_number: user.value.profile?.license_number || '',
        license_plate: user.value.profile?.license_plate || '',
        vehicle_type: user.value.profile?.vehicle_type || '',
    });
    
    // --- FORMULARIO 2: ARCHIVOS (POST) ---
    const vForm = useForm({
        front_ci: null,
        back_ci: null,
        selfie: null,
    });
    
    const submitProfile = () => {
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => console.log("Perfil actualizado")
        });
    };
    
    const submitVerification = () => {
        // Es vital usar POST para subir archivos en Laravel
        vForm.post(route('profile.verify'), {
            forceFormData: true,
            onSuccess: () => alert("Documentos enviados a revisión"),
        });
    };
    const vStatus = computed(() => user.value.verification_status); // 'pending', 'approved', o null

    // El Nivel 3 está en modo "Lectura" si ya se envió o ya se aprobó
    const isVerificationProcessing = computed(() => vStatus.value === 'pending' || vStatus.value === 'approved');
    </script>
    
    <template>
        <Head title="Mi Perfil" />
    
        <div class="max-w-2xl mx-auto py-12 px-6">
            
            <div class="mb-12 bg-white p-6 rounded-[2rem] shadow-xl border border-blue-50 flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="relative w-24 h-24">
                        <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-gray-100" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-blue-600" 
                                stroke-width="3" 
                                :stroke-dasharray="`${user.completion_percentage}, 100`" 
                                stroke-linecap="round"
                                style="transition: stroke-dasharray 0.8s ease"
                            ></circle>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-black text-blue-900">{{ user.completion_percentage }}%</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black uppercase tracking-tighter text-blue-900 italic">Tu Identidad</h1>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Estado: {{ user.completion_percentage < 100 ? 'En Progreso' : 'Verificado' }}</p>
                    </div>
                </div>
                <div class="hidden sm:block text-4xl">🚀</div>
            </div>
    
            <div :class="['p-8 rounded-[2.5rem] mb-6 border-2 transition-all duration-500', isLevel1Locked ? 'bg-gray-50 border-gray-100 opacity-60' : 'bg-white border-blue-500 shadow-2xl scale-105']">
                <h3 class="font-black uppercase text-xs mb-6 tracking-widest" :class="isLevel1Locked ? 'text-gray-400' : 'text-blue-600'">
                    {{ isLevel1Locked ? '✅ Nivel 1 Completado' : '🔵 Nivel 1: Datos Personales' }}
                </h3>
                
                <div v-if="!isLevel1Locked" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput v-model="form.first_name" label="Nombres" :error="form.errors.first_name" />
                        <BaseInput v-model="form.last_name" label="Apellidos" :error="form.errors.last_name" />
                    </div>
                    <BaseInput v-model="form.birth_date" type="date" label="Fecha Nacimiento" :error="form.errors.birth_date" />
                    <BaseButton @click="submitProfile" :isLoading="form.processing" class="w-full">Guardar y Bloquear</BaseButton>
                </div>
                
                <div v-else class="text-xs font-bold text-gray-500 flex items-center space-x-2">
                    <span>{{ user.profile.first_name }} {{ user.profile.last_name }}</span>
                    <span class="text-gray-300">|</span>
                    <span>{{ user.profile.birth_date }}</span>
                </div>
            </div>
    
            <div v-if="isLevel1Locked" :class="['p-8 rounded-[2.5rem] mb-6 border-2 transition-all duration-500', isLevel2Locked ? 'bg-gray-50 border-gray-100 opacity-60' : 'bg-white border-blue-600 shadow-2xl scale-105']">
                <h3 class="font-black uppercase text-xs mb-6 tracking-widest" :class="isLevel2Locked ? 'text-gray-400' : 'text-blue-700'">
                    {{ isLevel2Locked ? '✅ Nivel 2 Completado' : '📧 Nivel 2: Comunicación' }}
                </h3>
                
                <div v-if="!isLevel2Locked" class="space-y-4">
                    <BaseInput v-model="form.email" label="Correo Electrónico" type="email" :error="form.errors.email" />
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black text-gray-400 mb-2 uppercase italic">Género</label>
                        <select v-model="form.gender" class="rounded-xl border-gray-300 text-sm p-3 focus:ring-blue-600">
                            <option :value="null">Seleccionar...</option>
                            <option value="masculino">Masculino</option>
                            <option value="femenino">Femenino</option>
                            <option value="pnd">Prefiero no decirlo</option>
                        </select>
                    </div>
                    <BaseButton @click="submitProfile" :isLoading="form.processing" class="w-full">Actualizar Nivel 2</BaseButton>
                </div>
                <div v-else class="text-xs font-bold text-gray-500 italic">
                    {{ user.email }} • {{ user.profile.gender }}
                </div>
            </div>
    
            <div v-if="isLevel2Locked" class="p-10 bg-gray-950 rounded-[3rem] border-4 border-yellow-500 shadow-2xl relative overflow-hidden">
                <h3 class="font-black uppercase text-xs text-yellow-500 mb-8 tracking-[0.2em]">🛡️ Nivel 3: Verificación Legal</h3>
    
                <div v-if="isVerificationProcessing" class="text-center py-10 space-y-4">
                    <div class="text-5xl mb-4">{{ vStatus === 'approved' ? '✅' : '⏳' }}</div>
                    <h4 class="text-white font-black uppercase text-lg">
                        {{ vStatus === 'approved' ? 'Identidad Verificada' : 'Documentos en Revisión' }}
                    </h4>
                    <p class="text-gray-500 text-xs max-w-xs mx-auto">
                        {{ vStatus === 'approved' 
                            ? 'Tu cuenta ha sido validada. Ya puedes operar sin restricciones.' 
                            : 'Estamos validando tus fotos. Recibirás una notificación en menos de 24h.' }}
                    </p>
                    
                    <BaseButton disabled class="opacity-50 cursor-not-allowed">
                        {{ vStatus === 'approved' ? 'Verificación Finalizada' : 'Trámite Pendiente' }}
                    </BaseButton>
                </div>
    
                <div v-else class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="flex flex-col items-center p-6 border-2 border-dashed border-gray-800 rounded-3xl hover:border-yellow-500 transition-all cursor-pointer relative">
                            <input type="file" @input="vForm.front_ci = $event.target.files[0]" class="absolute inset-0 opacity-0 cursor-pointer" />
                            <span class="text-3xl mb-2">{{ vForm.front_ci ? '📄' : '📤' }}</span>
                            <p class="text-[9px] font-black uppercase text-gray-500">Anverso CI</p>
                        </div>
                        </div>

                    <BaseButton @click="submitVerification" variant="warning" class="w-full" :isLoading="vForm.processing">
                        Subir Documentos para Auditoría
                    </BaseButton>
                </div>
            </div>
        </div>
    </template>