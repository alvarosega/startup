<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link } from '@inertiajs/vue3';
    
    defineProps({
        incoming_transfers: Number,
        pending_removals: Number,
        expiring_products: Number
    });
    </script>
    
    <template>
        <AdminLayout>
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Centro de Operaciones Logísticas</h1>
                <p class="text-gray-400">Bienvenido, {{ $page.props.auth.user.first_name }}. Estado de tu sucursal hoy.</p>
            </div>
    
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <Link :href="route('admin.transfers.index')" 
                      class="p-6 rounded-xl border transition transform hover:scale-105"
                      :class="incoming_transfers > 0 ? 'bg-yellow-900/40 border-yellow-500' : 'bg-gray-800 border-gray-700'">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-sm uppercase font-bold">Camiones en Camino</p>
                            <h3 class="text-4xl font-bold text-white mt-2">{{ incoming_transfers }}</h3>
                        </div>
                        <span class="text-3xl">🚚</span>
                    </div>
                    <p v-if="incoming_transfers > 0" class="text-yellow-400 text-xs mt-3 font-bold animate-pulse">
                        ⚠️ Requiere recepción
                    </p>
                    <p v-else class="text-gray-500 text-xs mt-3">Todo recibido.</p>
                </Link>
    
                <div class="p-6 rounded-xl border bg-gray-800 border-gray-700">
                     <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-sm uppercase font-bold">Riesgo Vencimiento (30d)</p>
                            <h3 class="text-4xl font-bold text-white mt-2">{{ expiring_products }}</h3>
                        </div>
                        <span class="text-3xl">⏰</span>
                    </div>
                    <p class="text-gray-500 text-xs mt-3">Lotes críticos.</p>
                </div>
    
                <Link :href="route('admin.removals.index')" class="p-6 rounded-xl border bg-gray-800 border-gray-700 hover:bg-gray-750 transition">
                     <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-sm uppercase font-bold">Bajas Solicitadas</p>
                            <h3 class="text-4xl font-bold text-white mt-2">{{ pending_removals }}</h3>
                        </div>
                        <span class="text-3xl">🗑️</span>
                    </div>
                     <p class="text-gray-500 text-xs mt-3">Esperando aprobación.</p>
                </Link>
            </div>
    
            <h2 class="text-xl font-bold text-white mb-4">Acciones Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <Link :href="route('admin.inventory.create')" class="group bg-blue-900/20 border border-blue-800 hover:bg-blue-800/40 p-6 rounded-xl flex flex-col items-center justify-center text-center transition">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg group-hover:scale-110 transition">📥</div>
                    <span class="text-white font-bold">Registrar Compra</span>
                    <span class="text-blue-200 text-xs mt-1">Ingreso de Proveedor</span>
                </Link>
    
                <Link :href="route('admin.transfers.create')" class="group bg-purple-900/20 border border-purple-800 hover:bg-purple-800/40 p-6 rounded-xl flex flex-col items-center justify-center text-center transition">
                    <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg group-hover:scale-110 transition">📦</div>
                    <span class="text-white font-bold">Enviar a Sucursal</span>
                    <span class="text-purple-200 text-xs mt-1">Salida de Transferencia</span>
                </Link>
    
                <Link :href="route('admin.inventory.index')" class="group bg-gray-800 border border-gray-700 hover:bg-gray-700 p-6 rounded-xl flex flex-col items-center justify-center text-center transition">
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg group-hover:scale-110 transition">🔍</div>
                    <span class="text-white font-bold">Consultar Kardex</span>
                    <span class="text-gray-300 text-xs mt-1">Ver Stock Actual</span>
                </Link>
    
                 <Link :href="route('admin.removals.create')" class="group bg-red-900/20 border border-red-800 hover:bg-red-800/40 p-6 rounded-xl flex flex-col items-center justify-center text-center transition">
                    <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg group-hover:scale-110 transition">💔</div>
                    <span class="text-white font-bold">Reportar Merma</span>
                    <span class="text-red-200 text-xs mt-1">Roturas o Vencimientos</span>
                </Link>
            </div>
        </AdminLayout>
    </template>