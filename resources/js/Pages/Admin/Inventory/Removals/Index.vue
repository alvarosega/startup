<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, usePage, router } from '@inertiajs/vue3';
    import { computed } from 'vue';

    const props = defineProps({ removals: Object });
    const page = usePage();

    // Verificamos si es Super Admin para mostrar controles de auditoría
    const isSuperAdmin = computed(() => page.props.auth.user.roles.includes('super_admin'));

    const reasons = {
        expiration: '📅 Vencimiento',
        damage: '💔 Daño / Rotura',
        theft: '🕵️ Robo / Faltante',
        internal_use: '🍷 Consumo Interno',
        admin_error: '📝 Error Administrativo'
    };

    const approve = (id) => {
        if(confirm('¿APROBAR BAJA? Esta acción es irreversible. El stock se descontará definitivamente.')) {
            router.post(route('admin.removals.approve', id));
        }
    };

    const reject = (id) => {
        if(confirm('¿RECHAZAR BAJA? El stock reservado será liberado y volverá a estar disponible.')) {
            router.post(route('admin.removals.reject', id));
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Bajas y Mermas</h1>
                <p class="text-gray-400 text-sm">Control de pérdidas y salidas sin venta.</p>
            </div>
            <Link :href="route('admin.removals.create')" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded font-bold shadow transition">
                + Solicitar Baja
            </Link>
        </div>

        <div class="bg-gray-800 rounded shadow overflow-hidden">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-900 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Código / Fecha</th>
                        <th class="px-6 py-3">Sucursal</th>
                        <th class="px-6 py-3">Motivo</th>
                        <th class="px-6 py-3">Solicitado Por</th>
                        <th class="px-6 py-3 text-center">Estado</th>
                        <th class="px-6 py-3 text-right" v-if="isSuperAdmin">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr v-for="r in removals.data" :key="r.id" class="hover:bg-gray-750">
                        <td class="px-6 py-4">
                            <div class="font-mono text-red-300 font-bold">{{ r.code }}</div>
                            <div class="text-xs text-gray-500">{{ new Date(r.created_at).toLocaleDateString() }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-white">{{ r.branch?.name }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs border border-gray-600">
                                {{ reasons[r.reason] || r.reason }}
                            </span>
                            <div v-if="r.notes" class="text-xs text-gray-500 italic mt-1 max-w-xs truncate">
                                "{{ r.notes }}"
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ r.requester?.email }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="r.status === 'pending'" class="bg-yellow-900/50 text-yellow-400 px-2 py-1 rounded text-xs font-bold border border-yellow-700 animate-pulse">
                                ⏳ Pendiente
                            </span>
                            <span v-else-if="r.status === 'approved'" class="bg-red-900/50 text-red-400 px-2 py-1 rounded text-xs font-bold border border-red-700">
                                📉 Aprobado
                            </span>
                            <span v-else class="bg-green-900/50 text-green-400 px-2 py-1 rounded text-xs font-bold border border-green-700">
                                ↩ Rechazado
                            </span>
                            
                            <div v-if="r.approved_by" class="text-[9px] text-gray-500 mt-1 uppercase">
                                Por: {{ r.approver?.first_name }}
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 text-right space-x-2" v-if="isSuperAdmin">
                            <template v-if="r.status === 'pending'">
                                <button @click="approve(r.id)" class="bg-green-600 hover:bg-green-500 text-white px-3 py-1 rounded text-xs font-bold shadow transition">
                                    ✔ Aprobar
                                </button>
                                <button @click="reject(r.id)" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-xs font-bold shadow transition">
                                    ✖ Rechazar
                                </button>
                            </template>
                            <span v-else class="text-gray-600 text-xs italic">
                                Cerrado
                            </span>
                        </td>
                    </tr>
                    <tr v-if="removals.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No hay solicitudes de baja registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <div class="px-6 py-4 bg-gray-850 border-t border-gray-700 flex justify-center" v-if="removals.links.length > 3">
                <div class="flex gap-1">
                    <template v-for="(link, k) in removals.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded text-xs" 
                              :class="link.active ? 'bg-red-600 text-white' : 'bg-gray-700 text-gray-400'"/>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>