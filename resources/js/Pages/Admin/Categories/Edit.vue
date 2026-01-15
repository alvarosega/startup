<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed, watch } from 'vue';
    
    // Iconos
    import { 
        FolderTree, Image as ImageIcon, Settings, 
        ArrowRight, ArrowLeft, Save, CheckCircle, 
        CornerDownRight, Folder, AlertCircle, UploadCloud
    } from 'lucide-vue-next';

    const props = defineProps({
        category: Object,
        parents: Array
    });

    const currentStep = ref(1);
    
    // Detectamos si es hijo o padre basado en la data existente
    const categoryType = ref(props.category.parent_id ? 'child' : 'parent');

    const steps = [
        { id: 1, title: 'Jerarquía', icon: FolderTree, fields: ['name', 'external_code', 'parent_id'] },
        { id: 2, title: 'Contenido', icon: ImageIcon, fields: ['image', 'description', 'seo_title'] },
        { id: 3, title: 'Ajustes', icon: Settings, fields: ['tax_classification', 'requires_age_check'] },
    ];

    const form = useForm({
        _method: 'PUT', // TRUCO: Necesario para enviar archivos en una actualización en Laravel/Inertia
        name: props.category.name,
        parent_id: props.category.parent_id || '',
        external_code: props.category.external_code || '',
        description: props.category.description || '',
        image: null, // Solo se llena si el usuario sube una NUEVA imagen
        
        seo_title: props.category.seo_title || '',
        seo_description: props.category.seo_description || '',
        
        tax_classification: props.category.tax_classification || '',
        requires_age_check: !!props.category.requires_age_check,
        is_active: !!props.category.is_active,
        is_featured: !!props.category.is_featured,
    });

    // Resetear parent_id si cambiamos a "Categoría Principal"
    watch(categoryType, (val) => {
        if (val === 'parent') form.parent_id = ''; 
    });

    // --- NAVEGACIÓN ---
    const nextStep = () => {
        if (currentStep.value === 1) {
            if (!form.name) {
                form.setError('name', 'El nombre es obligatorio');
                return;
            }
            if (categoryType.value === 'child' && !form.parent_id) {
                form.setError('parent_id', 'Debes seleccionar una categoría padre');
                return;
            }
        }
        if (currentStep.value < steps.length) currentStep.value++;
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    // --- ENVÍO ---
    const submit = () => {
        // Usamos POST apuntando a la ruta UPDATE, pero con _method: PUT en el payload
        // Esto permite que 'forceFormData: true' funcione correctamente para la imagen.
        form.post(route('admin.categories.update', props.category.id), {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => {
                const stepWithError = steps.find(step => step.fields.some(f => errors[f]));
                if (stepWithError) currentStep.value = stepWithError.id;
            }
        });
    };

    const progressPercentage = computed(() => {
        return ((currentStep.value - 1) / (steps.length - 1)) * 100;
    });

    // PREVISUALIZACIÓN DE IMAGEN
    const imagePreview = ref(null);
    const handleImageUpload = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.image = file;
            imagePreview.value = URL.createObjectURL(file);
        }
    };
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-primary/10 text-skin-primary border border-skin-primary/20">
                                ID: {{ category.id }}
                            </span>
                            <span v-if="!category.is_active" class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-danger/10 text-skin-danger border border-skin-danger/20">
                                INACTIVO
                            </span>
                        </div>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">Editar: {{ category.name }}</h1>
                    </div>
                    <Link :href="route('admin.categories.index')" class="text-sm font-bold text-skin-muted hover:text-skin-danger transition-colors">
                        Cancelar
                    </Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-skin-border -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-skin-primary -z-10 rounded-full transition-all duration-500 ease-out"
                         :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 bg-skin-fill-card"
                                 :class="[
                                    currentStep === step.id ? 'border-skin-primary text-skin-primary shadow-lg scale-110' : 
                                    currentStep > step.id ? 'border-skin-success bg-skin-success text-white' : 
                                    'border-skin-border text-skin-muted'
                                 ]">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-skin-fill px-1"
                                  :class="currentStep >= step.id ? 'text-skin-base' : 'text-skin-muted'">
                                {{ step.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                
                <form class="flex-1 flex flex-col">
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-8">
                                
                                <div>
                                    <label class="block text-xs font-bold text-skin-muted uppercase mb-4 text-center">Nivel Jerárquico</label>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div @click="categoryType = 'parent'" 
                                             class="cursor-pointer border-2 rounded-global p-6 text-center transition-all duration-200 group relative overflow-hidden"
                                             :class="categoryType === 'parent' ? 'border-skin-primary bg-skin-primary/5' : 'border-skin-border hover:border-skin-muted bg-skin-fill'">
                                            <div class="mb-3 transform group-hover:scale-110 transition-transform duration-300">
                                                <Folder :size="40" :class="categoryType === 'parent' ? 'text-skin-primary' : 'text-skin-muted'" class="mx-auto" />
                                            </div>
                                            <span class="font-bold text-lg" :class="categoryType === 'parent' ? 'text-skin-primary' : 'text-skin-base'">Categoría Padre</span>
                                            
                                            <div v-if="categoryType === 'parent'" class="absolute top-2 right-2 text-skin-primary">
                                                <CheckCircle :size="20" />
                                            </div>
                                        </div>

                                        <div @click="categoryType = 'child'" 
                                             class="cursor-pointer border-2 rounded-global p-6 text-center transition-all duration-200 group relative overflow-hidden"
                                             :class="categoryType === 'child' ? 'border-skin-primary bg-skin-primary/5' : 'border-skin-border hover:border-skin-muted bg-skin-fill'">
                                            <div class="mb-3 transform group-hover:scale-110 transition-transform duration-300">
                                                <CornerDownRight :size="40" :class="categoryType === 'child' ? 'text-skin-primary' : 'text-skin-muted'" class="mx-auto" />
                                            </div>
                                            <span class="font-bold text-lg" :class="categoryType === 'child' ? 'text-skin-primary' : 'text-skin-base'">Subcategoría</span>
                                            
                                            <div v-if="categoryType === 'child'" class="absolute top-2 right-2 text-skin-primary">
                                                <CheckCircle :size="20" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in">
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Nombre *</label>
                                        <input v-model="form.name" type="text" 
                                               class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none font-bold text-lg"
                                               :class="{'border-skin-danger': form.errors.name}">
                                        <p v-if="form.errors.name" class="text-skin-danger text-xs mt-1">{{ form.errors.name }}</p>
                                    </div>

                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Código ERP</label>
                                        <input v-model="form.external_code" type="text" 
                                               class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 focus:ring-2 focus:ring-skin-primary outline-none font-mono">
                                    </div>

                                    <div v-if="categoryType === 'child'" class="col-span-2 bg-skin-fill p-4 rounded-global border border-skin-border animate-in fade-in slide-in-from-top-2">
                                        <label class="block text-xs font-bold text-skin-primary uppercase mb-2">Mover a Categoría Padre:</label>
                                        <select v-model="form.parent_id" 
                                                class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary">
                                            <option value="" disabled>-- Selecciona --</option>
                                            <option v-for="cat in parents" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                        <p v-if="form.errors.parent_id" class="text-skin-danger text-xs mt-1">{{ form.errors.parent_id }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    
                                    <div class="lg:col-span-1">
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Imagen de Portada</label>
                                        <div class="relative w-full aspect-square bg-skin-fill border-2 border-dashed border-skin-border rounded-global flex flex-col items-center justify-center cursor-pointer hover:border-skin-primary hover:bg-skin-primary/5 transition-all overflow-hidden group">
                                            <input type="file" @change="handleImageUpload" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                                            
                                            <img v-if="imagePreview" :src="imagePreview" class="absolute inset-0 w-full h-full object-cover z-10">
                                            <img v-else-if="category.image_url" :src="category.image_url" class="absolute inset-0 w-full h-full object-cover z-10">
                                            
                                            <div class="text-center p-4 z-0 group-hover:scale-110 transition-transform duration-300" 
                                                 :class="(imagePreview || category.image_url) ? 'opacity-0 group-hover:opacity-100 relative z-30 bg-black/60 inset-0 absolute flex flex-col justify-center h-full' : ''">
                                                <UploadCloud :size="32" class="mx-auto text-skin-muted mb-2" :class="(imagePreview || category.image_url) ? 'text-white' : ''" />
                                                <p class="text-xs font-bold" :class="(imagePreview || category.image_url) ? 'text-white' : 'text-skin-muted'">
                                                    {{ (imagePreview || category.image_url) ? 'Cambiar Imagen' : 'Clic para subir' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="lg:col-span-2 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Descripción</label>
                                            <textarea v-model="form.description" rows="3" 
                                                      class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary resize-none"></textarea>
                                        </div>

                                        <div class="bg-skin-fill/30 p-4 rounded-global border border-skin-border">
                                            <h3 class="text-xs font-bold text-skin-success uppercase mb-3 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-skin-success"></span> SEO
                                            </h3>
                                            <div class="space-y-3">
                                                <input v-model="form.seo_title" type="text" placeholder="Meta Título" 
                                                       class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2 text-xs focus:border-skin-primary outline-none">
                                                <textarea v-model="form.seo_description" rows="2" placeholder="Meta Descripción" 
                                                          class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-2 text-xs focus:border-skin-primary outline-none resize-none"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6">
                                <div class="bg-skin-fill/30 p-6 rounded-global border border-skin-border">
                                    <h3 class="text-sm font-bold text-skin-base mb-4 flex items-center gap-2">
                                        <Settings :size="18" class="text-skin-primary" /> Configuración General
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <label class="flex items-center justify-between p-3 bg-skin-fill border border-skin-border rounded-global cursor-pointer hover:border-skin-primary transition-colors">
                                            <div>
                                                <span class="block text-sm font-bold text-skin-base">Categoría Activa</span>
                                            </div>
                                            <div class="relative">
                                                <input v-model="form.is_active" type="checkbox" class="peer sr-only">
                                                <div class="w-11 h-6 bg-skin-muted/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-skin-success"></div>
                                            </div>
                                        </label>

                                        <label class="flex items-center justify-between p-3 bg-skin-fill border border-skin-border rounded-global cursor-pointer hover:border-skin-primary transition-colors">
                                            <div>
                                                <span class="block text-sm font-bold text-skin-base">Destacada</span>
                                            </div>
                                            <div class="relative">
                                                <input v-model="form.is_featured" type="checkbox" class="peer sr-only">
                                                <div class="w-11 h-6 bg-skin-muted/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                                            </div>
                                        </label>

                                        <label class="flex items-center justify-between p-3 bg-skin-fill border border-skin-border rounded-global cursor-pointer hover:border-skin-danger transition-colors">
                                            <div>
                                                <span class="block text-sm font-bold text-skin-base flex items-center gap-2">
                                                    Restricción de Edad <AlertCircle :size="14" class="text-skin-danger"/>
                                                </span>
                                            </div>
                                            <div class="relative">
                                                <input v-model="form.requires_age_check" type="checkbox" class="peer sr-only">
                                                <div class="w-11 h-6 bg-skin-muted/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-skin-danger"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Clasificación Fiscal (ICE)</label>
                                    <input v-model="form.tax_classification" type="text" 
                                           class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary font-mono text-sm">
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center">
                        <button type="button" @click="prevStep" 
                                class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-skin-muted hover:text-skin-base disabled:opacity-0"
                                :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep"
                                class="flex items-center gap-2 px-6 py-2.5 bg-skin-fill-card border border-skin-border hover:border-skin-primary text-skin-base rounded-global text-sm font-bold shadow-sm active:scale-95 transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 px-8 py-2.5 bg-skin-primary hover:bg-skin-primary-hover text-skin-primary-text rounded-global text-sm font-bold shadow-lg shadow-skin-primary/30 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else class="flex items-center gap-2"><Save :size="18" /> Actualizar</span>
                        </button>
                    </div>

                </form>
            </div>
            
        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>