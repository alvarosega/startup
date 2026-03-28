<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { User, MapPin, Shield } from 'lucide-vue-next';

const page = usePage();
</script>

<template>
    <nav class="flex flex-wrap gap-2 p-1.5 bg-zinc-100 rounded-2xl border border-zinc-200 mb-8 max-w-fit">
        
        <Link :href="route('customer.profile.index')" 
              :class="page.component === 'Customer/Profiles/PersonalInfoPage' ? 'bg-white text-zinc-900 shadow-sm border-zinc-200' : 'text-zinc-500 hover:bg-white/60 border-transparent'"
              class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all border">
            <User :size="16" /> <span class="hidden sm:inline">Perfil</span>
        </Link>

        <Link :href="route('customer.profile.addresses')" 
              :class="page.component === 'Customer/Profiles/AddressesPage' ? 'bg-white text-zinc-900 shadow-sm border-zinc-200' : 'text-zinc-500 hover:bg-white/60 border-transparent'"
              class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all border">
            <MapPin :size="16" /> <span class="hidden sm:inline">Direcciones</span>
        </Link>

        <Link :href="route('customer.profile.security')" 
              :class="page.component === 'Customer/Profiles/SecurityPage' ? 'bg-white text-zinc-900 shadow-sm border-zinc-200' : 'text-zinc-500 hover:bg-white/60 border-transparent'"
              class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all border">
            <Shield :size="16" /> <span class="hidden sm:inline">Seguridad</span>
        </Link>

    </nav>
</template>