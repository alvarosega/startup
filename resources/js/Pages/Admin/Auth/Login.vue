<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ThemeToggler from '@/Components/Base/ThemeToggler.vue';

const form = useForm({
    email: '',
    password: '',
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

const submit = () => {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso Administrador" />

    <div class="min-h-screen bg-background flex items-center justify-center p-4 select-none relative">
        
        <div class="absolute top-4 right-4 border border-border bg-card rounded-md transition-colors duration-100 hover:bg-neutral-100 dark:hover:bg-neutral-800">
            <ThemeToggler class="p-1.5 text-foreground/80 hover:text-primary transition-colors duration-100" />
        </div>

        <div class="max-w-md w-full bg-card border border-border rounded-md shadow-flat p-6 md:p-8">
            
            <div class="text-center mb-2">
                <span class="text-3xl font-black italic tracking-wider text-primary select-none">DU</span>
            </div>

            <div class="text-center mb-6">
                <h1 class="text-xl font-bold text-foreground tracking-tight uppercase">
                    Control de Acceso
                </h1>
                <p class="text-xs text-muted-foreground mt-1.5">
                    Identificación obligatoria para personal operativo
                </p>
            </div>

            <div 
                v-if="hasErrors" 
                class="mb-4 p-3 bg-error/10 border border-error/20 rounded-md text-error text-xs flex flex-col gap-1.5"
            >
                <div v-for="(error, key) in form.errors" :key="key" class="flex items-center gap-2 font-medium">
                    <span class="material-symbols-rounded text-base shrink-0">error</span>
                    <span>{{ error }}</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Identificador corporativo (Email)
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        class="admin-input"
                        placeholder="operador@cybermarket.com"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Código de seguridad (Password)
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="admin-input"
                        placeholder="••••••••"
                    />
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="admin-btn-primary w-full inline-flex items-center justify-center gap-2 text-xs font-bold uppercase tracking-wider disabled:cursor-not-allowed"
                    >
                        <span class="material-symbols-rounded text-[18px] shrink-0">login</span>
                        <span>{{ form.processing ? 'Verificando terminal...' : 'Iniciar Sesión' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>