<script setup>
    import { computed, ref } from 'vue';
    import { Head, usePage, useForm } from '@inertiajs/vue3';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { Camera, CheckCircle, Clock, AlertTriangle, UploadCloud } from 'lucide-vue-next';
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const status = computed(() => user.value.verification_status); 
    const form = useForm({ front_ci: null, back_ci: null, selfie: null });
    const previews = ref({ front_ci: null, back_ci: null, selfie: null });
    
    const handleFile = (field, e) => {
        const file = e.target.files[0];
        if (file) { form[field] = file; previews.value[field] = URL.createObjectURL(file); }
    };
    const submit = () => form.post(route('profile.verify'), { forceFormData: true, preserveScroll: true });
    </script>
    
    <template>
        <Head title="Verificación" />
        <ProfileLayout>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl mx-auto">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50"><h1 class="font-black text-gray-800 tracking-tight">Verificación de Identidad</h1></div>
                <div class="p-8">
                    <div v-if="status === 'approved'" class="text-center py-8"><CheckCircle class="mx-auto text-green-500 mb-4" :size="64" /><h2 class="text-xl font-bold text-gray-800">¡Identidad Confirmada!</h2></div>
                    <div v-else-if="status === 'pending'" class="text-center py-8"><Clock class="mx-auto text-blue-500 mb-4 animate-pulse" :size="64" /><h2 class="text-xl font-bold text-gray-800">Revisando Documentos</h2></div>
                    <div v-else>
                        <div v-if="status === 'rejected'" class="bg-red-50 p-4 rounded-xl flex items-center gap-3 mb-6 border border-red-100 text-red-700"><AlertTriangle :size="20" /><span class="text-xs font-bold">Verificación rechazada. Intenta de nuevo.</span></div>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div v-for="(label, key) in {front_ci: 'Carnet (Frente)', back_ci: 'Carnet (Atrás)', selfie: 'Selfie'}" :key="key" 
                                     class="relative border-2 border-dashed border-gray-300 rounded-xl h-32 flex flex-col items-center justify-center hover:bg-blue-50 hover:border-blue-400 transition cursor-pointer overflow-hidden group">
                                    <input type="file" accept="image/*" @change="(e) => handleFile(key, e)" class="absolute inset-0 opacity-0 z-10 cursor-pointer" />
                                    <img v-if="previews[key]" :src="previews[key]" class="absolute inset-0 w-full h-full object-cover" />
                                    <div v-else class="text-center p-2"><component :is="key === 'selfie' ? Camera : UploadCloud" class="mx-auto text-gray-400 group-hover:text-blue-500 mb-1" :size="24" /><p class="text-[10px] font-black uppercase text-gray-400 group-hover:text-blue-600">{{ label }}</p></div>
                                </div>
                            </div>
                            <div class="flex justify-end"><BaseButton :isLoading="form.processing" :disabled="!form.front_ci || !form.back_ci || !form.selfie" class="px-6">Enviar</BaseButton></div>
                        </form>
                    </div>
                </div>
            </div>
        </ProfileLayout>
    </template>