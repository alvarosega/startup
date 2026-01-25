<script setup>
    import { Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';
    import Toast from '@/Components/Base/Toast.vue';
    import { Map, List, User, LogOut } from 'lucide-vue-next';

    const page = usePage();
    const user = computed(() => page.props.auth.user);
    const driverStatus = computed(() => page.props.driver?.status || user.value?.driver_profile?.status || 'pending');
    const isVerified = computed(() => driverStatus.value === 'verified');
    
    // Initials Helper
    const getUserInitial = () => {
        const name = user.value?.driver_profile?.first_name || user.value?.name || 'D';
        return name.charAt(0).toUpperCase();
    };

    const navItems = [
        { label: 'Ruta', route: 'driver.dashboard', icon: Map, activePrefix: '/driver/dashboard' },
        { label: 'Historial', route: 'driver.history', icon: List, activePrefix: '/driver/history' },
        // CAMBIO: Apunta al INDEX, no al EDIT
        { label: 'Perfil', route: 'driver.profile.index', icon: User, activePrefix: '/driver/profile' },
    ];
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-sans pb-24 text-gray-800"> 
        <header class="bg-gray-900 text-white p-4 shadow-md sticky top-0 z-30 flex justify-between items-center transition-all">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-black text-xs shadow-lg shadow-blue-500/50">BL</div>
                <h1 class="font-black italic tracking-tighter text-lg">DRIVER<span class="text-blue-400">APP</span></h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-[10px] font-bold uppercase tracking-wider shadow-sm"
                     :class="isVerified ? 'bg-green-500/10 border-green-500/50 text-green-400' : 'bg-yellow-500/10 border-yellow-500/50 text-yellow-400 animate-pulse'">
                    <span class="w-2 h-2 rounded-full" :class="isVerified ? 'bg-green-500' : 'bg-yellow-500'"></span>
                    {{ isVerified ? 'En Línea' : 'Verificando' }}
                </div>
                <Link :href="route('driver.profile.index')" class="w-8 h-8 rounded-full bg-gray-700 hover:bg-gray-600 flex items-center justify-center text-xs font-bold transition border border-gray-600">
                    {{ getUserInitial() }}
                </Link>
            </div>
        </header>
    
        <main class="p-4 relative z-0"><slot /></main>
    
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 h-20 flex justify-around items-center z-40 shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.1)] pb-safe">
            <Link v-for="item in navItems" :key="item.route" :href="route(item.route)" 
                  class="flex flex-col items-center justify-center w-full h-full transition-all duration-200 group active:scale-95"
                  :class="{ 'text-blue-600': $page.url.startsWith(item.activePrefix) }">
                <div class="relative p-1.5 rounded-xl transition-all duration-300" :class="$page.url.startsWith(item.activePrefix) ? 'bg-blue-50 -translate-y-1 shadow-sm' : 'text-gray-400 group-hover:text-gray-600'">
                    <component :is="item.icon" :size="24" :stroke-width="$page.url.startsWith(item.activePrefix) ? 2.5 : 2" />
                    <span v-if="$page.url.startsWith(item.activePrefix)" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-blue-600 rounded-full"></span>
                </div>
                <span class="text-[10px] font-bold mt-1 transition-colors" :class="$page.url.startsWith(item.activePrefix) ? 'text-blue-600' : 'text-gray-400'">{{ item.label }}</span>
            </Link>
            <Link :href="route('logout')" method="post" as="button" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-red-500 transition-all duration-200 group active:scale-95">
                <div class="p-1.5 rounded-xl group-hover:bg-red-50 transition"><LogOut :size="24" /></div>
                <span class="text-[10px] font-bold mt-1">Salir</span>
            </Link>
        </nav>
        <Toast />
    </div>
</template>
<style scoped>.pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }</style>