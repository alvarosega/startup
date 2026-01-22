<script setup>
    import { MapPin } from 'lucide-vue-next';
    import UserCard from './UserCard.vue'; // Asegúrate de importar
    
    defineProps({
        groupName: String,
        users: Array
    });
    
    const emit = defineEmits(['delete-user']);
    </script>
    
    <template>
        <div class="mb-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex items-center gap-3 mb-5 pb-2 border-b border-surface">
                <div class="p-2 bg-surface rounded-lg text-primary shadow-sm">
                    <MapPin :size="20" />
                </div>
                <h2 class="text-xl font-display font-bold text-content">{{ groupName }}</h2>
                <span class="text-xs bg-surface text-content-light px-2 py-0.5 rounded-full border border-base font-medium">
                    {{ users.length }}
                </span>
            </div>
    
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <UserCard 
                    v-for="user in users" 
                    :key="user.id" 
                    :user="user"
                    @delete="(id) => emit('delete-user', id)"
                />
            </div>
        </div>
    </template>