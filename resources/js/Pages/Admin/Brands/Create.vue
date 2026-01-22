<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useForm, Link } from '@inertiajs/vue3';
    import { ref, computed } from 'vue';
    
    // Iconos
    import { 
        Tag, Factory, CheckCircle, ArrowRight, ArrowLeft, Save, 
        Globe, Search, UploadCloud, ShieldCheck
    } from 'lucide-vue-next';

    const props = defineProps({
        providers: Array,
        categories: Array
    });

    const currentStep = ref(1);
    const categorySearch = ref(''); // Buscador local para el Paso 3

    const steps = [
        { id: 1, title: 'Identidad', icon: Tag, fields: ['name', 'image', 'tier'] },
        { id: 2, title: 'Logística', icon: Factory, fields: ['provider_id', 'origin_country_code'] },
        { id: 3, title: 'Segmentación', icon: Globe, fields: ['categories'] }, // El más importante
    ];

    const form = useForm({
        name: '',
        provider_id: '',
        manufacturer: '',
        origin_country_code: '',
        tier: 'Standard',
        website: '',
        image: null,
        is_active: true,
        is_featured: false,
        categories: [] // IDs
    });

    // --- FILTRADO DE CATEGORÍAS (Paso 3) ---
    const filteredCategories = computed(() => {
        if (!categorySearch.value) return props.categories;
        return props.categories.filter(c => c.name.toLowerCase().includes(categorySearch.value.toLowerCase()));
    });

    const toggleCategory = (id) => {
        const index = form.categories.indexOf(id);
        if (index === -1) form.categories.push(id);
        else form.categories.splice(index, 1);
    };

    // --- NAVEGACIÓN ---
    const nextStep = () => {
        if (currentStep.value === 1) {
            if (!form.name) { form.setError('name', 'Nombre obligatorio'); return; }
        }
        if (currentStep.value === 2) {
            if (!form.provider_id) { form.setError('provider_id', 'Debes elegir un proveedor'); return; }
        }
        if (currentStep.value < steps.length) currentStep.value++;
    };

    const prevStep = () => {
        if (currentStep.value > 1) currentStep.value--;
    };

    const submit = () => {
        form.post(route('admin.brands.store'), {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => {
                // Auto-navegar si hay error
                const stepWithError = steps.find(step => step.fields.some(f => errors[f]));
                if (stepWithError) currentStep.value = stepWithError.id;
            }
        });
    };

    // --- UTILIDADES ---
    const progressPercentage = computed(() => ((currentStep.value - 1) / (steps.length - 1)) * 100);
    
    const imagePreview = ref(null);
    const handleImage = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.image = file;
            imagePreview.value = URL.createObjectURL(file);
        }
    };

    const tiers = [
        { id: 'Economy', label: 'Económica', color: 'bg-gray-100 text-gray-600' },
        { id: 'Standard', label: 'Estándar', color: 'bg-blue-50 text-blue-600' },
        { id: 'Premium', label: 'Premium', color: 'bg-amber-50 text-amber-600' },
        { id: 'Luxury', label: 'Lujo', color: 'bg-purple-50 text-purple-600' },
    ];
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto py-6">
            
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-skin-base tracking-tight">Nueva Marca</h1>
                        <p class="text-skin-muted text-sm mt-1">Registra una marca en el portafolio</p>
                    </div>
                    <Link :href="route('admin.brands.index')" class="text-sm font-bold text-skin-muted hover:text-skin-danger transition-colors">Cancelar</Link>
                </div>

                <div class="relative px-4">
                    <div class="absolute top-5 left-0 w-full h-1 bg-skin-border -z-10 rounded-full"></div>
                    <div class="absolute top-5 left-0 h-1 bg-skin-primary -z-10 rounded-full transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>

                    <div class="flex justify-between">
                        <div v-for="step in steps" :key="step.id" 
                             class="flex flex-col items-center gap-2 cursor-pointer group"
                             @click="currentStep >= step.id ? currentStep = step.id : null">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all bg-skin-fill-card"
                                 :class="[currentStep === step.id ? 'border-skin-primary text-skin-primary scale-110 shadow-lg' : currentStep > step.id ? 'border-skin-success bg-skin-success text-white' : 'border-skin-border text-skin-muted']">
                                <CheckCircle v-if="currentStep > step.id" :size="20" />
                                <component v-else :is="step.icon" :size="18" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-skin-fill px-1" :class="currentStep >= step.id ? 'text-skin-base' : 'text-skin-muted'">{{ step.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-xl overflow-hidden min-h-[500px] flex flex-col">
                <form class="flex-1 flex flex-col">
                    <div class="p-8 flex-1">
                        <Transition name="fade" mode="out-in">
                            
                            <div v-if="currentStep === 1" key="1" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Logotipo</label>
                                        <div class="relative w-full aspect-video bg-skin-fill border-2 border-dashed border-skin-border rounded-global flex flex-col items-center justify-center cursor-pointer hover:border-skin-primary hover:bg-skin-primary/5 transition group overflow-hidden">
                                            <input type="file" @change="handleImage" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                                            <img v-if="imagePreview" :src="imagePreview" class="absolute inset-0 w-full h-full object-contain p-4 z-10">
                                            
                                            <div class="text-center p-4 z-0 group-hover:scale-110 transition-transform duration-300" :class="imagePreview ? 'opacity-0' : ''">
                                                <UploadCloud :size="32" class="mx-auto text-skin-muted mb-2" />
                                                <p class="text-xs text-skin-muted font-bold">Subir Logo</p>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.image" class="text-skin-danger text-xs mt-1">{{ form.errors.image }}</p>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Nombre Comercial *</label>
                                            <input v-model="form.name" type="text" class="w-full bg-skin-fill border border-skin-border text-skin-base rounded-global p-3 text-lg font-bold focus:ring-2 focus:ring-skin-primary outline-none" placeholder="Ej: Coca-Cola">
                                            <p v-if="form.errors.name" class="text-skin-danger text-xs mt-1">{{ form.errors.name }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Posicionamiento (Tier)</label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div v-for="tier in tiers" :key="tier.id" 
                                                     @click="form.tier = tier.id"
                                                     class="cursor-pointer border rounded-global p-2 text-center text-xs font-bold transition-all"
                                                     :class="form.tier === tier.id ? 'border-skin-primary ring-1 ring-skin-primary ' + tier.color : 'border-skin-border text-skin-muted hover:border-skin-primary'">
                                                    {{ tier.label }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="currentStep === 2" key="2" class="space-y-6">
                                <div class="bg-skin-fill/30 p-6 rounded-global border border-skin-border">
                                    <h3 class="text-sm font-bold text-skin-base mb-4 flex items-center gap-2">
                                        <Factory :size="18" class="text-skin-primary" /> Cadena de Suministro
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Distribuidor Oficial *</label>
                                            <select v-model="form.provider_id" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary">
                                                <option value="" disabled>-- Selecciona --</option>
                                                <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.commercial_name }}</option>
                                            </select>
                                            <p v-if="form.errors.provider_id" class="text-skin-danger text-xs mt-1">{{ form.errors.provider_id }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">País de Origen (ISO)</label>
                                            <input v-model="form.origin_country_code" type="text" maxlength="2" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 uppercase font-mono text-center focus:border-skin-primary outline-none" placeholder="BO">
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-skin-muted uppercase mb-2">Fabricante (Opcional)</label>
                                            <input v-model="form.manufacturer" type="text" class="w-full bg-skin-fill-card border border-skin-border text-skin-base rounded-global p-3 outline-none focus:border-skin-primary" placeholder="Ej: The Coca-Cola Company">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 p-3 border border-skin-border rounded-global w-full cursor-pointer hover:border-skin-primary transition">
                                        <input v-model="form.is_active" type="checkbox" class="w-5 h-5 accent-skin-primary">
                                        <span class="text-sm font-bold text-skin-base">Marca Activa</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 border border-skin-border rounded-global w-full cursor-pointer hover:border-skin-primary transition">
                                        <input v-model="form.is_featured" type="checkbox" class="w-5 h-5 accent-skin-primary">
                                        <span class="text-sm font-bold text-skin-base">Destacada</span>
                                    </label>
                                </div>
                            </div>

                            <div v-else key="3" class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-skin-base">Asignar Categorías</h3>
                                        <p class="text-xs text-skin-muted">Selecciona dónde aparecerán los productos de esta marca.</p>
                                    </div>
                                    <div class="relative w-64">
                                        <Search :size="14" class="absolute left-3 top-3 text-skin-muted" />
                                        <input v-model="categorySearch" type="text" placeholder="Filtrar categorías..." class="w-full bg-skin-fill border border-skin-border rounded-global pl-9 p-2 text-sm focus:border-skin-primary outline-none">
                                    </div>
                                </div>

                                <div class="bg-skin-fill/20 p-4 rounded-global border border-skin-border h-80 overflow-y-auto custom-scrollbar">
                                    <div v-if="filteredCategories.length > 0" class="flex flex-wrap gap-2">
                                        <button type="button" 
                                                v-for="cat in filteredCategories" :key="cat.id"
                                                @click="toggleCategory(cat.id)"
                                                class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all duration-200 select-none flex items-center gap-2"
                                                :class="form.categories.includes(cat.id) 
                                                    ? 'bg-skin-primary text-skin-primary-text border-skin-primary shadow-md scale-105' 
                                                    : 'bg-skin-fill-card text-skin-base border-skin-border hover:border-skin-muted'">
                                            {{ cat.name }}
                                            <CheckCircle v-if="form.categories.includes(cat.id)" :size="12" />
                                        </button>
                                    </div>
                                    <div v-else class="h-full flex flex-col items-center justify-center text-skin-muted">
                                        <Search :size="32" class="mb-2 opacity-50" />
                                        <p class="text-sm">No se encontraron categorías</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-xs text-skin-muted px-2">
                                    <span>{{ form.categories.length }} categorías seleccionadas</span>
                                    <span v-if="form.categories.length === 0" class="text-skin-danger font-bold flex items-center gap-1">
                                        <ShieldCheck :size="12" /> Se recomienda asignar al menos una
                                    </span>
                                </div>
                            </div>

                        </Transition>
                    </div>

                    <div class="px-8 py-4 bg-skin-fill/50 border-t border-skin-border flex justify-between items-center">
                        <button type="button" @click="prevStep" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-skin-muted hover:text-skin-base disabled:opacity-0" :disabled="currentStep === 1">
                            <ArrowLeft :size="16" /> Atrás
                        </button>

                        <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="flex items-center gap-2 px-6 py-2.5 bg-skin-fill-card border border-skin-border hover:border-skin-primary text-skin-base rounded-global text-sm font-bold shadow-sm active:scale-95 transition-all">
                            Siguiente <ArrowRight :size="16" />
                        </button>

                        <button v-else type="button" @click="submit" :disabled="form.processing" class="flex items-center gap-2 px-8 py-2.5 bg-skin-primary hover:bg-skin-primary-hover text-skin-primary-text rounded-global text-sm font-bold shadow-lg shadow-skin-primary/30 active:scale-95 transition-all disabled:opacity-50">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else class="flex items-center gap-2"><Save :size="18" /> Guardar Marca</span>
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