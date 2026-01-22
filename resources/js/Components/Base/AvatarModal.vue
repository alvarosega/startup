<script setup>
    import { ref } from 'vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import BaseButton from '@/Components/Base/BaseButton.vue';
    import { Camera, Image as ImageIcon, Upload, X, Check } from 'lucide-vue-next';
    
    const props = defineProps({ show: Boolean });
    const emit = defineEmits(['close']);
    
    const activeTab = ref('gallery'); // 'gallery' | 'upload'
    const selectedIcon = ref(null);
    const customPreview = ref(null);
    
    const form = useForm({
        avatar_type: 'icon',
        avatar_source: '',
        avatar_file: null
    });
    
    const selectIcon = (iconName) => {
        selectedIcon.value = iconName;
        form.avatar_type = 'icon';
        form.avatar_source = iconName;
        form.avatar_file = null;
        customPreview.value = null;
    };
    
    const handleFileUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            form.avatar_type = 'custom';
            form.avatar_file = file;
            customPreview.value = URL.createObjectURL(file);
        }
    };
    
    const submit = () => {
        form.post(route('profile.avatar.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                form.reset();
                customPreview.value = null;
            }
        });
    };
    </script>
    
    <template>
        <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-all">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-800 text-lg">Cambiar Foto</h3>
                    <button @click="$emit('close')" class="p-1 rounded-full hover:bg-gray-200 transition"><X :size="20" class="text-gray-500"/></button>
                </div>
                <div class="flex border-b border-gray-100">
                    <button @click="activeTab = 'gallery'" class="flex-1 py-3 text-xs font-bold uppercase tracking-wider transition-colors flex items-center justify-center gap-2" :class="activeTab === 'gallery' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50' : 'text-gray-400 hover:text-gray-600'"><ImageIcon :size="16" /> Galería</button>
                    <button @click="activeTab = 'upload'" class="flex-1 py-3 text-xs font-bold uppercase tracking-wider transition-colors flex items-center justify-center gap-2" :class="activeTab === 'upload' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50' : 'text-gray-400 hover:text-gray-600'"><Upload :size="16" /> Subir Foto</button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div v-if="activeTab === 'gallery'">
                        <div class="grid grid-cols-3 gap-4">
                            <div v-for="i in 6" :key="i" @click="selectIcon(`avatar_${i}.svg`)" class="aspect-square rounded-full border-2 cursor-pointer flex items-center justify-center transition-all relative overflow-hidden" :class="selectedIcon === `avatar_${i}.svg` ? 'border-blue-500 bg-blue-50 scale-105' : 'border-gray-100 hover:border-gray-300'">
                                <img :src="`/assets/avatars/avatar_${i}.svg`" class="w-2/3 h-2/3" />
                                <div v-if="selectedIcon === `avatar_${i}.svg`" class="absolute inset-0 bg-blue-500/10 flex items-center justify-center"><div class="bg-blue-500 text-white rounded-full p-1"><Check :size="16" /></div></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="activeTab === 'upload'" class="text-center">
                        <div class="relative w-40 h-40 mx-auto mb-4 group cursor-pointer">
                            <div class="w-full h-full rounded-full border-4 border-dashed border-gray-300 flex flex-col items-center justify-center overflow-hidden hover:border-blue-500 transition">
                                <img v-if="customPreview" :src="customPreview" class="w-full h-full object-cover" />
                                <div v-else class="text-gray-400 flex flex-col items-center"><Camera :size="32" class="mb-2" /><span class="text-[10px] font-bold uppercase">Subir</span></div>
                            </div>
                            <input type="file" accept="image/*" @change="handleFileUpload" class="absolute inset-0 opacity-0 cursor-pointer" />
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <BaseButton @click="submit" :isLoading="form.processing" class="w-full" :disabled="!form.avatar_source && !form.avatar_file">Guardar Avatar</BaseButton>
                </div>
            </div>
        </div>
    </template>