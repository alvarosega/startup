<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    const page = usePage();
    const user = computed(() => page.props.auth.user);
    
    // Props específicas que enviamos desde el controlador para este rol
    defineProps({ 
        pending_removals: Number, 
        my_branch: String 
    });
</script>

<template>
    <AdminLayout>
        <div class="mb-8 bg-gray-800/50 p-6 rounded-xl border border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    📦 Panel Operativo
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Usuario: <span class="text-blue-400 font-bold">{{ user.first_name }}</span> | 
                    Ubicación: <span class="text-green-400 font-bold border border-green-500/30 px-2 py-0.5 rounded bg-green-900/20">{{ my_branch }}</span>
                </p>
            </div>
            
            <div v-if="pending_removals > 0" class="bg-yellow-900/40 border border-yellow-600 text-yellow-200 px-4 py-3 rounded-lg flex items-center gap-3 animate-pulse">
                <span class="text-2xl">⏳</span>
                <div class="leading-tight">
                    <p class="font-bold text-sm">Solicitudes Pendientes</p>
                    <p class="text-xs">Tienes {{ pending_removals }} bajas esperando aprobación.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <Link :href="route('admin.purchases.create')" class="group relative bg-gray-800 p-8 rounded-xl border border-gray-700 hover:border-green-500 hover:bg-gray-750 transition-all shadow-lg overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition text-green-500 text-9xl">📥</div>
                <div class="relative z-10">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300 transform origin-left">📥</div>
                    <h3 class="text-xl font-bold text-white mb-2">Registrar Ingreso</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Recepción de mercadería de proveedores. <br>
                        <span class="text-green-500 text-xs">Genera stock nuevo.</span>
                    </p>
                </div>
            </Link>

            <Link :href="route('admin.transfers.create')" class="group relative bg-gray-800 p-8 rounded-xl border border-gray-700 hover:border-purple-500 hover:bg-gray-750 transition-all shadow-lg overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition text-purple-500 text-9xl">🚚</div>
                <div class="relative z-10">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300 transform origin-left">🚚</div>
                    <h3 class="text-xl font-bold text-white mb-2">Enviar a Sucursal</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Mover stock a otra tienda. <br>
                        <span class="text-purple-500 text-xs">Genera guía de remisión.</span>
                    </p>
                </div>
            </Link>

            <Link :href="route('admin.removals.create')" class="group relative bg-gray-800 p-8 rounded-xl border border-gray-700 hover:border-red-500 hover:bg-gray-750 transition-all shadow-lg overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition text-red-500 text-9xl">⚠️</div>
                <div class="relative z-10">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition duration-300 transform origin-left">⚠️</div>
                    <h3 class="text-xl font-bold text-white mb-2">Reportar Merma</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Solicitar baja por daño, robo o vencimiento. <br>
                        <span class="text-red-500 text-xs">Requiere aprobación.</span>
                    </p>
                </div>
            </Link>
        </div>

        <h3 class="text-gray-400 uppercase text-xs font-bold mb-4 tracking-wider">Consultas y Reportes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <Link :href="route('admin.inventory.index')" class="flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:border-blue-500 transition group">
                <div class="bg-blue-900/20 p-2 rounded text-blue-400 mr-4 group-hover:bg-blue-600 group-hover:text-white transition">📦</div>
                <div class="flex-1">
                    <h4 class="text-white font-bold">Ver Kardex / Stock Actual</h4>
                    <p class="text-xs text-gray-500">Consultar existencias en tiempo real.</p>
                </div>
                <div class="text-gray-500 group-hover:text-white">➜</div>
            </Link>

            <Link :href="route('admin.transfers.index')" class="flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:border-purple-500 transition group">
                <div class="bg-purple-900/20 p-2 rounded text-purple-400 mr-4 group-hover:bg-purple-600 group-hover:text-white transition">📋</div>
                <div class="flex-1">
                    <h4 class="text-white font-bold">Historial de Transferencias</h4>
                    <p class="text-xs text-gray-500">Ver envíos y recepciones pasadas.</p>
                </div>
                <div class="text-gray-500 group-hover:text-white">➜</div>
            </Link>
             <Link :href="route('admin.transformations.index')" class="flex items-center p-4 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:border-purple-500 transition group">
                <div class="bg-purple-900/20 p-2 rounded text-purple-400 mr-4 group-hover:bg-purple-600 group-hover:text-white transition">🔄</div>
                <div class="flex-1">
                    <h4 class="text-white font-bold">Transformaciones</h4>
                    <p class="text-xs text-gray-500">Desglose de Packs y Cajas.</p>
                </div>
                <div class="text-gray-500 group-hover:text-white">➜</div>
            </Link>
        </div>
    </AdminLayout>
</template>