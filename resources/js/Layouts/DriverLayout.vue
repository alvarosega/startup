<script setup>
    import { Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import { Map, List, User, LogOut } from 'lucide-vue-next';
    import Toast from '@/Components/Base/Toast.vue';
    
    const page = usePage();
    const user = computed(() => page.props.auth.user);
    </script>
    
    <template>
        <div class="min-h-screen bg-gray-50 font-sans pb-20"> <header class="bg-blue-600 text-white p-4 shadow-md sticky top-0 z-30">
                <div class="flex justify-between items-center">
                    <h1 class="font-black italic tracking-tighter text-xl">
                        BL<span class="text-blue-200">DRIVER</span>
                    </h1>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold bg-blue-700 px-2 py-1 rounded">
                            {{ user.profile?.is_identity_verified ? '🟢 Activo' : '🔴 Sin Verificar' }}
                        </span>
                    </div>
                </div>
            </header>
    
            <main class="p-4">
                <slot />
            </main>
    
            <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 h-16 flex justify-around items-center z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                
                <Link :href="route('driver.dashboard')" 
                      class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-600 transition"
                      :class="{ 'text-blue-600': $page.url.startsWith('/driver/dashboard') }">
                    <Map :size="24" />
                    <span class="text-[10px] font-bold mt-1">Ruta</span>
                </Link>
    
                <Link :href="route('driver.history')" 
                      class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-600 transition"
                      :class="{ 'text-blue-600': $page.url.startsWith('/driver/history') }">
                    <List :size="24" />
                    <span class="text-[10px] font-bold mt-1">Historial</span>
                </Link>
    
                <Link :href="route('profile.index')" 
                      class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-600 transition"
                      :class="{ 'text-blue-600': $page.url.startsWith('/profile') }">
                    <User :size="24" />
                    <span class="text-[10px] font-bold mt-1">Perfil</span>
                </Link>
                
                <Link :href="route('logout')" method="post" as="button" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-red-500 transition">
                    <LogOut :size="24" />
                    <span class="text-[10px] font-bold mt-1">Salir</span>
                </Link>
            </nav>
    
            <Toast />
        </div>
    </template>