<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Función para logout
import { router } from '@inertiajs/vue3';
const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">Boasdfasdflivia Logistics</h1>
            <button @click="logout" class="text-red-500 hover:text-red-700 font-semibold">Cerrar Sesión</button>
        </nav>

        <div class="max-w-7xl mx-auto py-10 px-4">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">¡Bienvenido, {{ user.email }}!</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <p class="text-sm text-gray-500">Nivel Actual</p>
                        <p class="text-xl font-bold" :style="{ color: user.current_level?.color_hex }">
                            {{ user.current_level?.name || 'Sin Rango' }}
                        </p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <p class="text-sm text-gray-500">Confianza (Trust Score)</p>
                        <p class="text-xl font-bold text-green-700">{{ user.trust_score }} / 100</p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <p class="text-sm text-gray-500">Rol Activo</p>
                        <p class="text-xl font-bold text-purple-700 uppercase">
                            {{ user.roles[0] || 'Cliente' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>