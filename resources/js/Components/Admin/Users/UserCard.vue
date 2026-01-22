<script setup>
    import { computed, ref } from 'vue';
    import { Link } from '@inertiajs/vue3';
    import { MoreVertical, Phone, Mail, Edit, Trash2, Shield } from 'lucide-vue-next';
    
    const props = defineProps({ user: Object });
    const showMenu = ref(false);
    
    // Helper para colores de roles (Píldoras)
    const roleStyle = computed(() => {
        const r = props.user.role?.toLowerCase() || '';
        if (r.includes('admin') || r.includes('gerente')) return 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800';
        if (r.includes('vendedor')) return 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800';
        if (r.includes('almacen')) return 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800';
        return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800';
    });
    
    const emit = defineEmits(['delete']);
    </script>
    
    <template>
        <div class="group relative bg-surface rounded-box border border-transparent hover:border-primary/20 shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300 ease-elastic overflow-visible">
            
            <div class="p-5 flex items-start justify-between">
                <div class="flex gap-4">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-lg shadow-inner">
                            {{ user.full_name ? user.full_name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 border-2 border-surface rounded-full"
                              :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    </div>
                    
                    <div>
                        <h3 class="font-display font-bold text-content text-lg leading-tight truncate max-w-[150px]" :title="user.full_name">
                            {{ user.full_name }}
                        </h3>
                        <div class="mt-1 inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border" :class="roleStyle">
                            {{ user.role }}
                        </div>
                    </div>
                </div>
    
                <div class="relative">
                    <button @click="showMenu = !showMenu" @blur="setTimeout(() => showMenu = false, 200)"
                            class="p-1.5 rounded-md text-content-light hover:bg-base hover:text-primary transition-colors">
                        <MoreVertical :size="18" />
                    </button>
                    
                    <transition enter-active-class="transition duration-100 ease-out" enter-from-class="scale-95 opacity-0" enter-to-class="scale-100 opacity-100">
                        <div v-if="showMenu" class="absolute right-0 top-8 w-32 bg-surface rounded-lg shadow-xl border border-base z-10 py-1 flex flex-col">
                            <Link :href="route('admin.users.edit', user.id)" class="px-4 py-2 text-sm text-content hover:bg-base hover:text-primary flex items-center gap-2">
                                <Edit :size="14" /> Editar
                            </Link>
                            <button @click="$emit('delete', user.id)" class="px-4 py-2 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 flex items-center gap-2 w-full text-left">
                                <Trash2 :size="14" /> Eliminar
                            </button>
                        </div>
                    </transition>
                </div>
            </div>
    
            <div class="px-5 pb-5 space-y-2">
                <div class="h-px bg-base w-full mb-3"></div>
                
                <div class="flex items-center gap-3 text-sm text-content-light truncate" title="Email">
                    <Mail :size="15" class="shrink-0 opacity-70" /> 
                    <span class="truncate">{{ user.email }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-content-light" title="Teléfono">
                    <Phone :size="15" class="shrink-0 opacity-70" /> 
                    <span>{{ user.phone || '---' }}</span>
                </div>
            </div>
        </div>
    </template>