<script setup>
    import { CheckCircle, Briefcase, ShieldAlert } from 'lucide-vue-next';
    
    defineProps({
        modelValue: [String, Number], // ID del rol seleccionado
        roles: Array,
        error: String
    });
    
    defineEmits(['update:modelValue']);
    
    const formatRoleName = (name) => {
        // Mapeo para visualización humana
        const map = { 
            'super_admin': 'Super Administrador', 
            'branch_admin': 'Gerente de Sucursal', 
            'sales': 'Vendedor',
            'warehouse': 'Almacenero'
        };
        return map[name] || name;
    };
    </script>
    
    <template>
        <div class="space-y-2">
            <label class="block text-xs font-bold text-content-light uppercase tracking-wider mb-2 ml-1">
                Rol del Sistema <span class="text-red-500">*</span>
            </label>
    
            <div v-if="roles.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="role in roles" :key="role.id" 
                     @click="$emit('update:modelValue', role.id)"
                     class="cursor-pointer relative border rounded-xl p-4 flex items-center gap-3 transition-all duration-200 ease-out hover:shadow-md group"
                     :class="modelValue === role.id 
                        ? 'bg-primary/5 border-primary ring-1 ring-primary shadow-sm' 
                        : 'bg-surface border-line hover:border-primary/40'">
                    
                    <div class="p-2.5 rounded-full transition-colors shrink-0" 
                         :class="modelValue === role.id ? 'bg-primary text-white' : 'bg-base text-content-light group-hover:bg-primary/10 group-hover:text-primary'">
                        <Briefcase :size="18" />
                    </div>
                    
                    <div class="overflow-hidden">
                        <span class="block text-sm font-bold text-content truncate transition-colors" 
                              :class="modelValue === role.id ? 'text-primary-focus' : ''">
                            {{ formatRoleName(role.name) }}
                        </span>
                        <span class="block text-[11px] text-content-light truncate font-medium">
                            Nivel de Acceso: {{ role.display_name }}
                        </span>
                    </div>
    
                    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="scale-0 opacity-0" enter-to-class="scale-100 opacity-100">
                        <CheckCircle v-if="modelValue === role.id" :size="20" class="absolute top-3 right-3 text-primary" stroke-width="2.5" />
                    </transition>
                </div>
            </div>
    
            <div v-else class="p-4 bg-orange-50 border border-orange-200 text-orange-800 rounded-xl flex items-center gap-3 text-sm font-medium">
                <ShieldAlert :size="20" class="shrink-0" />
                <span>No hay roles disponibles para asignar.</span>
            </div>
    
            <p v-if="error" class="text-xs text-red-600 font-bold mt-2 ml-1">{{ error }}</p>
        </div>
    </template>