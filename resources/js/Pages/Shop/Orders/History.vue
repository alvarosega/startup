<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue';
    import { 
        Package, Calendar, ChevronRight, SearchX, 
        Clock, FileText, CheckCircle2, Truck, XCircle 
    } from 'lucide-vue-next';
    
    defineProps({
        orders: Object // Objeto paginado de Laravel
    });
    
    // Mapa de estados para las etiquetas (Badges)
    const statusMap = {
        pending_proof: { label: 'Pagar Ahora', classes: 'bg-yellow-100 text-yellow-700 border-yellow-200', icon: Clock },
        review:        { label: 'Verificando', classes: 'bg-blue-100 text-blue-700 border-blue-200',     icon: FileText },
        confirmed:     { label: 'Preparando',  classes: 'bg-indigo-100 text-indigo-700 border-indigo-200', icon: Package },
        dispatched:    { label: 'En Camino',   classes: 'bg-purple-100 text-purple-700 border-purple-200', icon: Truck },
        completed:     { label: 'Entregado',   classes: 'bg-green-100 text-green-700 border-green-200',   icon: CheckCircle2 },
        cancelled:     { label: 'Cancelado',   classes: 'bg-red-100 text-red-700 border-red-200',       icon: XCircle },
    };
    
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('es-BO', { 
            year: 'numeric', month: 'long', day: 'numeric' 
        });
    };
    </script>
    
    <template>
        <ShopLayout>
            <Head title="Mis Pedidos" />
    
            <div class="max-w-4xl mx-auto py-10 px-4 min-h-[60vh]">
                
                <div class="flex items-center gap-3 mb-8">
                    <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                        <Package :size="28" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-gray-800 leading-none">Mis Pedidos</h1>
                        <p class="text-sm text-gray-500 mt-1">Historial y seguimiento de compras.</p>
                    </div>
                </div>
    
                <div v-if="orders.data.length > 0" class="space-y-4">
                    
                    <div v-for="order in orders.data" :key="order.id" 
                         class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden">
                        
                        <Link :href="route('orders.show', order.code)" class="block p-6">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-black text-lg text-gray-800">#{{ order.code }}</span>
                                        
                                        <span :class="`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border flex items-center gap-1 ${statusMap[order.status].classes}`">
                                            <component :is="statusMap[order.status].icon" :size="12" />
                                            {{ statusMap[order.status].label }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-xs text-gray-500 font-medium">
                                        <span class="flex items-center gap-1">
                                            <Calendar :size="14"/> {{ formatDate(order.created_at) }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ order.items_count }} Productos</span>
                                    </div>
                                </div>
    
                                <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Total</p>
                                        <p class="text-xl font-black text-gray-800">Bs {{ order.total_amount }}</p>
                                    </div>
                                    
                                    <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <ChevronRight :size="20" />
                                    </div>
                                </div>
    
                            </div>
                        </Link>
    
                        <div v-if="order.status === 'pending_proof'" class="bg-yellow-50 px-6 py-2 border-t border-yellow-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-yellow-700 flex items-center gap-2">
                                ⚠️ Pendiente de Pago
                            </span>
                            <Link :href="route('orders.show', order.code)" class="text-xs font-black text-yellow-800 hover:underline">
                                Subir Comprobante →
                            </Link>
                        </div>
                    </div>
    
                    <div v-if="orders.links.length > 3" class="flex justify-center mt-8 gap-2">
                        <Link v-for="(link, k) in orders.links" :key="k" 
                              :href="link.url || '#'" 
                              v-html="link.label"
                              class="px-4 py-2 text-sm rounded-lg border transition-colors"
                              :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' + (!link.url ? ' opacity-50 pointer-events-none' : '')"
                        />
                    </div>
    
                </div>
    
                <div v-else class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <SearchX :size="32" class="text-gray-300" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">No tienes pedidos aún</h3>
                    <p class="text-gray-500 text-sm mb-6 max-w-xs mx-auto">Explora nuestro catálogo y encuentra los mejores productos para tu evento.</p>
                    
                    <Link :href="route('shop.index')" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                        Ir a Comprar
                    </Link>
                </div>
    
            </div>
        </ShopLayout>
    </template>