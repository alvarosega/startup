<script setup>
    import { computed } from 'vue';
    import { Link, usePage } from '@inertiajs/vue3';
    import ShopLayout from '@/Layouts/ShopLayout.vue'; 
    import { User, MapPin, ShieldCheck, FileText } from 'lucide-vue-next'; 
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const percentage = computed(() => user.value.completion_percentage || 0);
    
    // --- CORRECCIÓN AQUÍ ---
    // 1. "Datos Personales" ahora apunta al Index (Lectura).
    // 2. Eliminamos el item que apuntaba directo a Edit.
    const menuItems = [
        { name: 'Datos Personales', route: 'profile.index', icon: User },
        { name: 'Mis Direcciones', route: 'addresses.index', icon: MapPin },
        { name: 'Seguridad', route: 'profile.security', icon: ShieldCheck }, 
        { name: 'Verificación Legal', route: 'profile.verification', icon: FileText },
    ];
    </script>
    
    <template>
        <ShopLayout>
            <div class="min-h-[calc(100vh-64px)] bg-gray-50 text-gray-800 font-sans">
                
                <div class="bg-white border-b border-gray-200 sticky top-16 z-30 shadow-sm">
                    <div class="max-w-5xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <h2 class="text-sm font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                            <span class="text-blue-600">●</span> Centro de Mando
                        </h2>
                        
                        <div class="w-full sm:w-auto flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Integridad del Perfil</p>
                                <p class="text-xs font-black text-blue-600">{{ percentage }}% Completado</p>
                            </div>
                            <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 transition-all duration-1000 ease-out" 
                                     :style="{ width: `${percentage}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="max-w-5xl mx-auto px-4 py-8">
                    <div class="flex flex-col md:flex-row gap-8">
                        
                        <aside class="w-full md:w-64 flex-shrink-0">
                            <nav class="space-y-1">
                                <Link v-for="item in menuItems" :key="item.route" 
                                      :href="route(item.route)"
                                      class="group flex items-center justify-between px-4 py-3 text-sm font-bold rounded-xl transition-all border"
                                      :class="route().current(item.route) 
                                          ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
                                          : 'bg-white text-gray-600 border-gray-100 hover:border-blue-300 hover:text-blue-600'"
                                >
                                    <div class="flex items-center gap-3">
                                        <component :is="item.icon" :size="18" />
                                        {{ item.name }}
                                    </div>
                                </Link>
                            </nav>
                        </aside>
    
                        <main class="flex-1 min-w-0">
                            <slot />
                        </main>
    
                    </div>
                </div>
            </div>
        </ShopLayout>
    </template>