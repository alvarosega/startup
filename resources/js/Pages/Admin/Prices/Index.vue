<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Search, X, Save, Edit2, Trash2, Check, Package, Clock, User
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    branches: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const isDrawerOpen = ref(false);
const activeSku = ref(null);
const activeBranchId = ref(null);
const editingType = ref(null); // Controla qué fila de la matriz se está editando

const form = useForm({
    sku_id: '', branch_id: '', type: '', final_price: 0,
    min_quantity: 1, priority: 0, valid_from: '', valid_to: null,
});

const getPricesForBranch = (sku, branchId) => sku.prices_matrix?.[branchId] || [];
const getRegularPrice = (sku, branchId) => getPricesForBranch(sku, branchId).find(p => p.type === 'regular');
const priceTypes = ['regular', 'offer', 'member', 'wholesale', 'liquidation', 'staff'];

const openManager = (sku, branchId) => {
    activeSku.value = sku;
    activeBranchId.value = branchId;
    editingType.value = null;
    isDrawerOpen.value = true;
};

const startEdit = (type) => {
    editingType.value = type;
    const existing = getPricesForBranch(activeSku.value, activeBranchId.value).find(p => p.type === type);
    
    form.sku_id = activeSku.value.id;
    form.branch_id = activeBranchId.value;
    form.type = type;
    form.final_price = existing?.final_price || activeSku.value.base_price;
    form.min_quantity = existing?.min_quantity || (type === 'wholesale' ? 6 : 1);
    form.priority = existing?.priority || 0;
    form.valid_from = existing?.valid_from?.split('T')[0] || new Date().toISOString().slice(0, 10);
    form.valid_to = existing?.valid_to?.split('T')[0] || null;
};

const saveInline = () => {
    form.post(route('admin.prices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            editingType.value = null;
            // El backend refresca los datos automáticamente vía Inertia
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Control de Precios Auditoría" />

        <div class="max-w-[1600px] mx-auto space-y-6 pb-10 px-4">
            <div class="flex justify-between items-center bg-card p-6 rounded-3xl border border-border">
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Price <span class="text-primary">Audit</span></h1>
                <div class="relative w-96">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" :size="18" />
                    <input v-model="search" type="text" placeholder="EAN o Producto..." class="form-input pl-12 h-12 bg-muted/20 border-none rounded-2xl w-full">
                </div>
            </div>

            <div class="bg-card border border-border rounded-[2rem] overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-muted/30 text-[10px] font-black uppercase tracking-widest text-muted-foreground border-b border-border">
                            <th class="px-8 py-5 border-r border-border min-w-[300px]">Producto</th>
                            <th v-for="branch in branches" :key="branch.id" class="px-6 py-5 text-center">{{ branch.name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <template v-for="product in products.data" :key="product.id">
                            <tr v-for="sku in product.skus" :key="sku.id" class="hover:bg-muted/5 transition-all cursor-pointer" @click="openManager(sku, activeBranchId || branches[0].id)">
                                <td class="px-8 py-4 border-r border-border font-bold text-xs uppercase">{{ sku.name }}</td>
                                <td v-for="branch in branches" :key="branch.id" class="px-4 py-4 text-center">
                                    <span class="font-mono font-black text-sm">
                                        {{ getRegularPrice(sku, branch.id) ? '$' + getRegularPrice(sku, branch.id).final_price : '---' }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <Transition name="slide">
            <div v-if="isDrawerOpen" class="fixed inset-0 z-[60] flex justify-end">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isDrawerOpen = false"></div>
                <div class="relative w-full max-w-4xl bg-background h-full shadow-2xl flex flex-col border-l border-border">
                    
                    <div class="p-8 border-b border-border flex justify-between items-center bg-muted/5">
                        <div>
                            <h2 class="text-2xl font-black uppercase tracking-tighter italic">Gestor Quirúrgico</h2>
                            <p class="text-xs font-bold text-primary uppercase">{{ activeSku?.name }}</p>
                        </div>
                        <button @click="isDrawerOpen = false" class="p-2 hover:bg-muted rounded-full"><X /></button>
                    </div>

                    <div class="flex-1 overflow-auto p-8 custom-scrollbar">
                        <table class="w-full text-[11px] border-collapse">
                            <thead class="text-muted-foreground font-black uppercase tracking-widest border-b border-border">
                                <tr>
                                    <th class="py-4 pr-4">Tipo de Precio</th>
                                    <th class="py-4 px-4 text-center">Valor ($)</th>
                                    <th class="py-4 px-4 text-center">Min Qty</th>
                                    <th class="py-4 px-4 text-center">Prioridad</th>
                                    <th class="py-4 px-4 text-center">Vigencia</th>
                                    <th class="py-4 px-4 text-center">Auditoría</th>
                                    <th class="py-4 pl-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="type in priceTypes" :key="type" class="group">
                                    <td class="py-4 pr-4 font-black uppercase text-primary/80">{{ type }}</td>
                                    
                                    <template v-if="editingType === type">
                                        <td class="py-4 px-2"><input v-model="form.final_price" type="number" step="0.01" class="w-20 text-center font-mono font-black border-primary rounded-lg bg-primary/5"></td>
                                        <td class="py-4 px-2"><input v-model="form.min_quantity" type="number" class="w-14 text-center border-border rounded-lg"></td>
                                        <td class="py-4 px-2"><input v-model="form.priority" type="number" class="w-14 text-center border-border rounded-lg"></td>
                                        <td class="py-4 px-2"><input v-model="form.valid_to" type="date" class="w-28 text-[9px] border-border rounded-lg"></td>
                                        <td class="py-4 px-2 text-center text-muted-foreground italic">Editando...</td>
                                        <td class="py-4 pl-4 text-right">
                                            <button @click="saveInline" class="p-2 bg-success text-white rounded-lg hover:scale-105 transition-all"><Check :size="14"/></button>
                                        </td>
                                    </template>

                                    <template v-else>
                                        <td class="py-4 px-4 text-center font-mono font-black text-sm">
                                            {{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type)?.final_price || '---' }}
                                        </td>
                                        <td class="py-4 px-4 text-center font-bold">
                                            {{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type)?.min_quantity || '---' }}
                                        </td>
                                        <td class="py-4 px-4 text-center text-muted-foreground">
                                            {{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type)?.priority || '0' }}
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span v-if="getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type)?.valid_to" class="flex items-center justify-center gap-1 text-orange-500 font-bold">
                                                <Clock :size="10" /> {{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type).valid_to.split('T')[0] }}
                                            </span>
                                            <span v-else class="text-muted-foreground/30 italic">Perpetuo</span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <div v-if="getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type)" class="flex flex-col items-center">
                                                <span class="text-[8px] uppercase font-black text-muted-foreground flex items-center gap-1">
                                                    <User :size="8"/> {{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type).updater?.name || 'Sistema' }}
                                                </span>
                                                <span class="text-[7px] text-muted-foreground/50">{{ getPricesForBranch(activeSku, activeBranchId).find(p => p.type === type).updated_at.split('T')[0] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 pl-4 text-right">
                                            <button @click="startEdit(type)" class="p-2 hover:bg-primary/10 text-primary rounded-lg transition-colors"><Edit2 :size="14" /></button>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>