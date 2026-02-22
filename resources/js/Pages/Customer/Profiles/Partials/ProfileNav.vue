<script setup>
import { Link } from '@inertiajs/vue3';
import { User, MapPin, Shield } from 'lucide-vue-next';
</script>

<template>
    <nav class="flex gap-2 p-1 bg-muted/50 rounded-2xl border border-border mb-8 max-w-fit mx-auto md:mx-0">
        <Link :href="route('customer.profile.index')" 
              :class="$page.component === 'Customer/Profiles/PersonalInfoPage' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:bg-background/50'"
              class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
            <User :size="14" /> <span class="hidden sm:inline">Perfil</span>
        </Link>
        <Link :href="route('customer.profile.addresses')" 
              :class="$page.component === 'Customer/Profiles/AddressesPage' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:bg-background/50'"
              class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
            <MapPin :size="14" /> <span class="hidden sm:inline">Direcciones</span>
        </Link>
        <Link :href="route('customer.profile.security')" 
              :class="$page.component === 'Customer/Profiles/SecurityPage' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:bg-background/50'"
              class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
            <Shield :size="14" /> <span class="hidden sm:inline">Seguridad</span>
        </Link>
    </nav>
</template>