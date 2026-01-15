<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { Link, router, useForm } from '@inertiajs/vue3'; // Importamos useForm
    import { ref, computed, watch, onMounted } from 'vue';
    import debounce from 'lodash/debounce';
    
    import { 
        Search, Plus, Package, Image as ImageIcon, 
        Barcode, Box, Layers, Tag, Edit, Trash2, 
        ChevronRight, AlertTriangle, Cuboid, Scale, DollarSign, X
    } from 'lucide-vue-next';

    const props = defineProps({ 
        products: Object, 
        filters: Object 
    });

    const search = ref(props.filters.search || '');
    const selectedProductId = ref(null);
    const showSkuModal = ref(false); // Estado del Modal

    // --- FORMULARIO SKU (Para el Modal) ---
    const skuForm = useForm({
        product_id: '',
        name: '',
        code: '',
        conversion_factor: 1,
        weight: 0,
        price: 0
    });

    const activeProduct = computed(() => {
        return props.products.data.find(p => p.id === selectedProductId.value) || null;
    });

    const selectProduct = (id) => {
        selectedProductId.value = id;
    };

    onMounted(() => {
        if (props.products.data.length > 0 && !selectedProductId.value) {
            selectedProductId.value = props.products.data[0].id;
        }
    });

    watch(search, debounce((val) => {
        router.get(route('admin.products.index'), { search: val }, { 
            preserveState: true, replace: true, preserveScroll: true,
            onSuccess: () => {
                if (props.products.data.length > 0) selectedProductId.value = props.products.data[0].id;
            }
        });
    }, 300));

    // --- ACCIONES DE PRODUCTO ---
    const deleteProduct = (id) => {
        if(confirm('⚠ ALERTA: Eliminar este producto archivará TODOS sus SKUs. ¿Estás seguro?')) {
            router.delete(route('admin.products.destroy', id), {
                onSuccess: () => selectedProductId.value = null
            });
        }
    };

    const deleteSku = (skuId) => {
        if(confirm('¿Archivar este SKU?')) {
            router.delete(route('admin.skus.destroy', skuId));
        }
    };

    // --- LÓGICA DEL MODAL SKU ---
    const openSkuModal = () => {
        skuForm.reset();
        skuForm.product_id = activeProduct.value.id;
        // Pre-llenar nombre sugerido
        skuForm.name = activeProduct.value.name + ' - '; 
        showSkuModal.value = true;
    };

    const submitSku = () => {
        skuForm.post(route('admin.skus.store'), {
            onSuccess: () => {
                showSkuModal.value = false;
                skuForm.reset();
            },
            preserveScroll: true
        });
    };
</script>

<template>
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row h-[calc(100vh-6rem)] gap-6 overflow-hidden relative">
            
            <div class="w-full md:w-1/3 lg:w-1/4 flex flex-col bg-skin-fill-card border border-skin-border rounded-global shadow-sm overflow-hidden h-full">
                <div class="p-4 border-b border-skin-border bg-skin-fill/50 shrink-0">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-black text-lg text-skin-base tracking-tight">Productos</h2>
                        <Link :href="route('admin.products.create')" class="p-2 bg-skin-primary text-skin-primary-text rounded-global hover:brightness-110 transition shadow-sm" title="Crear Nuevo Producto Maestro">
                            <Plus :size="16" />
                        </Link>
                    </div>
                    <div class="relative group">
                        <Search :size="14" class="absolute left-3 top-2.5 text-skin-muted pointer-events-none" />
                        <input v-model="search" type="text" placeholder="Buscar..." 
                               class="pl-9 w-full bg-skin-fill border border-skin-border text-skin-base text-xs font-medium rounded-global py-2 focus:ring-2 focus:ring-skin-primary/50 outline-none transition-all">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
                    <div v-for="prod in products.data" :key="prod.id"
                         @click="selectProduct(prod.id)"
                         class="flex items-center gap-3 p-3 rounded-global cursor-pointer transition-all duration-200 border border-transparent group"
                         :class="selectedProductId === prod.id ? 'bg-skin-primary/10 border-skin-primary/20 shadow-sm' : 'hover:bg-skin-fill hover:border-skin-border'">
                        
                        <div class="w-10 h-10 rounded bg-white border border-skin-border overflow-hidden shrink-0 flex items-center justify-center p-0.5">
                            <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-contain">
                            <Package v-else :size="16" class="text-gray-300" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <h3 class="font-bold text-sm truncate" :class="selectedProductId === prod.id ? 'text-skin-primary' : 'text-skin-base'">{{ prod.name }}</h3>
                                <div v-if="!prod.is_active" class="w-2 h-2 rounded-full bg-skin-danger" title="Inactivo"></div>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-[10px] text-skin-muted truncate max-w-[100px]">{{ prod.brand?.name }}</p>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-skin-fill border border-skin-border text-skin-muted">{{ prod.skus?.length || 0 }} SKUs</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="products.links.length > 3" class="p-3 border-t border-skin-border bg-skin-fill/50 flex justify-center gap-1 shrink-0 overflow-x-auto">
                    <template v-for="(link, k) in products.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-2 py-1 rounded text-[10px] transition border" :class="link.active ? 'bg-skin-primary text-white border-skin-primary' : 'bg-skin-fill border-skin-border text-skin-muted hover:bg-white'"/>
                    </template>
                </div>
            </div>

            <div class="flex-1 flex flex-col h-full overflow-hidden bg-skin-fill rounded-global border border-transparent">
                <Transition name="fade" mode="out-in">
                    <div v-if="activeProduct" :key="activeProduct.id" class="flex flex-col h-full">
                        
                        <div class="bg-skin-fill-card border border-skin-border rounded-global shadow-sm p-6 mb-6 shrink-0 relative overflow-hidden">
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex gap-6">
                                    <div class="w-24 h-24 rounded-lg bg-white border-2 border-skin-border shadow-inner flex items-center justify-center p-2 shrink-0">
                                        <img v-if="activeProduct.image_url" :src="activeProduct.image_url" class="w-full h-full object-contain">
                                        <Package v-else :size="40" class="text-gray-200" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-fill border border-skin-border text-skin-muted flex items-center gap-1"><Tag :size="10" /> {{ activeProduct.brand?.name }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-skin-fill border border-skin-border text-skin-muted flex items-center gap-1"><Layers :size="10" /> {{ activeProduct.category?.name }}</span>
                                        </div>
                                        <h1 class="text-3xl font-black text-skin-base tracking-tight mb-2">{{ activeProduct.name }}</h1>
                                        <p class="text-sm text-skin-muted max-w-xl line-clamp-2">{{ activeProduct.description }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <Link :href="route('admin.products.edit', activeProduct.id)" class="flex items-center gap-2 px-4 py-2 bg-skin-primary text-skin-primary-text rounded-global text-xs font-bold shadow-sm hover:brightness-110 transition text-center justify-center"><Edit :size="14" /> Editar</Link>
                                    <button @click="deleteProduct(activeProduct.id)" class="flex items-center gap-2 px-4 py-2 bg-skin-fill border border-skin-border hover:border-skin-danger text-skin-muted hover:text-skin-danger rounded-global text-xs font-bold transition text-center justify-center"><Trash2 :size="14" /> Eliminar</button>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-10">
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                
                                <div @click="openSkuModal" 
                                     class="border-2 border-dashed border-skin-border rounded-global flex flex-col items-center justify-center text-skin-muted hover:text-skin-primary hover:border-skin-primary hover:bg-skin-primary/5 transition-all cursor-pointer min-h-[140px] group order-first">
                                    <div class="p-3 rounded-full bg-skin-fill group-hover:bg-skin-primary/10 transition mb-2">
                                        <Plus :size="24" />
                                    </div>
                                    <span class="text-xs font-bold">Añadir Presentación</span>
                                </div>

                                <div v-for="sku in activeProduct.skus" :key="sku.id" 
                                     class="group bg-skin-fill-card border border-skin-border rounded-global p-4 relative overflow-hidden transition-all hover:shadow-md hover:border-skin-primary/50">
                                    
                                    <div class="absolute -right-4 -bottom-4 text-skin-fill-hover opacity-50 transform rotate-12 group-hover:scale-110 transition-transform pointer-events-none">
                                        <Box v-if="parseFloat(sku.conversion_factor) > 1" :size="80" />
                                        <Cuboid v-else :size="80" />
                                    </div>

                                    <div class="relative z-10">
                                        <div class="flex justify-between items-start mb-3">
                                            <span v-if="parseFloat(sku.conversion_factor) > 1" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-500/10 text-blue-600 border border-blue-500/20 text-[10px] font-black uppercase shadow-sm"><Box :size="10" /> Pack x{{ parseFloat(sku.conversion_factor) }}</span>
                                            <span v-else class="inline-flex items-center gap-1 px-2 py-1 rounded bg-skin-fill text-skin-muted border border-skin-border text-[10px] font-bold uppercase"><Cuboid :size="10" /> Unidad Base</span>
                                            
                                            <button @click="deleteSku(sku.id)" class="p-1 text-skin-muted hover:text-skin-danger rounded hover:bg-skin-fill opacity-0 group-hover:opacity-100 transition"><Trash2 :size="14" /></button>
                                        </div>
                                        <h4 class="font-bold text-skin-base text-sm leading-tight mb-1 line-clamp-2 min-h-[1.25rem]">{{ sku.name }}</h4>
                                        <p class="font-mono text-xs text-skin-primary font-bold mb-4 bg-skin-primary/5 inline-block px-1 rounded">{{ sku.code }}</p>
                                        
                                        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-skin-border/50">
                                            <div><span class="block text-[9px] text-skin-muted uppercase font-bold">Peso</span><span class="text-xs font-mono text-skin-base">{{ sku.weight || '0' }} kg</span></div>
                                            <div><span class="block text-[9px] text-skin-muted uppercase font-bold text-right">Precio</span><span class="block text-xs font-mono text-skin-success text-right font-bold">{{ sku.prices?.[0]?.final_price || '---' }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="h-full flex flex-col items-center justify-center text-skin-muted opacity-50">
                        <Package :size="64" class="mb-4 text-skin-border" />
                        <p class="font-bold text-lg">Selecciona un producto</p>
                    </div>
                </Transition>
            </div>

            <div v-if="showSkuModal" class="absolute inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-skin-fill-card border border-skin-border rounded-lg shadow-2xl w-full max-w-md animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex justify-between items-center p-4 border-b border-skin-border">
                        <h3 class="font-bold text-lg text-skin-base">Nueva Presentación</h3>
                        <button @click="showSkuModal = false" class="text-skin-muted hover:text-skin-danger"><X :size="20" /></button>
                    </div>
                    
                    <form @submit.prevent="submitSku" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-skin-muted uppercase mb-1">Nombre Presentación</label>
                            <input v-model="skuForm.name" type="text" class="w-full bg-skin-fill border border-skin-border rounded p-2 text-sm focus:border-skin-primary outline-none">
                            <p v-if="skuForm.errors.name" class="text-skin-danger text-xs mt-1">{{ skuForm.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-skin-muted uppercase mb-1">Código EAN</label>
                                <input v-model="skuForm.code" type="text" class="w-full bg-skin-fill border border-skin-border rounded p-2 text-sm font-mono focus:border-skin-primary outline-none">
                                <p v-if="skuForm.errors.code" class="text-skin-danger text-xs mt-1">{{ skuForm.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-skin-muted uppercase mb-1">Unidades (Factor)</label>
                                <input v-model.number="skuForm.conversion_factor" type="number" min="1" class="w-full bg-skin-fill border border-skin-border rounded p-2 text-sm text-center focus:border-skin-primary outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-skin-muted uppercase mb-1">Peso (Kg)</label>
                                <input v-model.number="skuForm.weight" type="number" step="0.01" class="w-full bg-skin-fill border border-skin-border rounded p-2 text-sm text-center focus:border-skin-primary outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-skin-success uppercase mb-1">Precio Base (Bs)</label>
                                <input v-model.number="skuForm.price" type="number" step="0.01" class="w-full bg-skin-fill border border-skin-border rounded p-2 text-sm text-right font-bold text-skin-success focus:border-skin-success outline-none">
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="showSkuModal = false" class="px-4 py-2 text-sm text-skin-muted hover:text-skin-base">Cancelar</button>
                            <button type="submit" :disabled="skuForm.processing" class="px-6 py-2 bg-skin-primary text-skin-primary-text rounded text-sm font-bold shadow hover:brightness-110 disabled:opacity-50">Guardar SKU</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>