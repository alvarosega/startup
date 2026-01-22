<script setup>
    import { useForm, Head } from '@inertiajs/vue3';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import BaseInput from '@/Components/Base/BaseInput.vue';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const updatePassword = () => {
        form.put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: () => { if (form.errors.password) form.reset('password', 'password_confirmation'); if (form.errors.current_password) form.reset('current_password'); },
        });
    };
    </script>
    
    <template>
        <Head title="Seguridad" />
        <ProfileLayout>
            <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50"><h1 class="font-black text-gray-800 tracking-tight">Cambiar Contraseña</h1></div>
                <div class="p-8">
                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <BaseInput v-model="form.current_password" type="password" label="Contraseña Actual" :error="form.errors.current_password" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput v-model="form.password" type="password" label="Nueva Contraseña" :error="form.errors.password" />
                            <BaseInput v-model="form.password_confirmation" type="password" label="Confirmar Nueva" />
                        </div>
                        <div class="flex justify-end pt-4"><BaseButton :isLoading="form.processing" class="bg-gray-900">Actualizar</BaseButton></div>
                    </form>
                </div>
            </div>
        </ProfileLayout>
    </template>