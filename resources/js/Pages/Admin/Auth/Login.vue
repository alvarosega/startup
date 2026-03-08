<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso Administrador" />

    <div class="min-h-screen bg-background flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-card border border-border rounded-xl shadow-lg p-8">
            <!-- Logo / Título -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-foreground tracking-tight">
                    Panel de Administración
                </h1>
                <p class="text-sm text-muted-foreground mt-2">
                    Ingresa tus credenciales para continuar
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-muted-foreground">
                        Correo electrónico
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full bg-background border border-border rounded-lg px-4 py-2 text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        placeholder="admin@ejemplo.com"
                    />
                    <p v-if="form.errors.email" class="text-sm text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Contraseña -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-muted-foreground">
                        Contraseña
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full bg-background border border-border rounded-lg px-4 py-2 text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        placeholder="••••••••"
                    />
                    <p v-if="form.errors.password" class="text-sm text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Recordar sesión -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-border bg-background text-primary focus:ring-primary/20 transition"
                        />
                        <span class="text-sm text-muted-foreground">Recordarme</span>
                    </label>
                    <!-- Opcional: enlace para recuperar contraseña -->
                    <a href="#" class="text-sm text-primary hover:text-primary/80 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Botón de ingreso -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-primary text-primary-foreground py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                >
                    <LogIn :size="18" />
                    <span>{{ form.processing ? 'Verificando...' : 'Ingresar' }}</span>
                </button>
            </form>
        </div>
    </div>
</template>