<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Trash2, Edit, Calendar, LayoutGrid, Layers, Eye } from 'lucide-vue-next';
import axios from 'axios';

defineProps({
    bundles: Array
});

const selectedBundle = ref(null);
const bundleItems = ref([]);
const loadingItems = ref(false);

const loadBundleItems = async (bundle) => {
    selectedBundle.value = bundle;
    loadingItems.value = true;
    bundleItems.value = [];
    
    try {
        const response = await axios.get(route('admin.bundles.items', bundle.id));
        bundleItems.value = response.data.items || [];
    } catch (error) {
        alert('ERROR_RESOLUCIÓN: No se pudo extraer la matriz de componentes.');
    } finally {
        loadingItems.value = false;
    }
};

const deleteBundle = (id) => {
    if (confirm('¿CONFIRMAR DESTRUCCIÓN? Se darán de baja las dependencias publicitarias activas.')) {
        router.delete(route('admin.bundles.destroy', id), {
            preserveScroll: true,
            onSuccess: () => { selectedBundle.value = null; }
        });
    }
};
</script>

<template>
    <Head title="Estrategias Comerciales - Combos" />
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center select-none font-sans">
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-neutral-900 dark:text-neutral-50 uppercase italic">Macro Agrupadores // Combos</h1>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-mono tracking-wider uppercase mt-0.5">Gestión de empaquetados promocionales y vigencias estacionales</p>
                </div>
                <Link :href="route('admin.bundles.create')" class="bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-50 dark:hover:bg-neutral-200 text-white dark:text-neutral-950 px-4 py-2 rounded-md transition-colors text-xs font-bold font-mono tracking-wider flex items-center gap-2">
                    <Plus :size="14" /> CONFIGURAR_NUEVO_COMBO
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 font-mono text-xs">
            <div class="lg:col-span-7 border border-neutral-200 dark:border-neutral-800 rounded-md bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/70 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-800 text-[10px] font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 select-none">
                            <th class="p-3">COMBO_DESIGNACIÓN</th>
                            <th class="p-3 w-28">ESTRATEGIA</th>
                            <th class="p-3 w-28 text-center">ESTADO</th>
                            <th class="p-3 w-24 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="bd in bundles" :key="bd.id" :class="selectedBundle?.id === bd.id ? 'bg-neutral-50 dark:bg-neutral-950/40 font-bold' : ''" class="hover:bg-neutral-50/40 dark:hover:bg-neutral-800/10 transition-colors cursor-pointer" @click="loadBundleItems(bd)">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="bd.image_url" :src="bd.image_url" class="w-8 h-8 rounded border border-neutral-200 dark:border-neutral-700 object-cover shrink-0 select-none" />
                                    <div v-else class="w-8 h-8 bg-neutral-100 dark:bg-neutral-800 rounded flex items-center justify-center border border-neutral-200 dark:border-neutral-700 text-neutral-400 shrink-0 select-none"><LayoutGrid :size="14"/></div>
                                    <div class="min-w-0">
                                        <div class="text-neutral-900 dark:text-white truncate uppercase font-bold tracking-tight select-all">{{ bd.name }}</div>
                                        <div class="text-[9px] text-neutral-400 flex items-center gap-1 mt-0.5 select-none">
                                            <Calendar :size="10"/> {{ bd.starts_at ? bd.starts_at.slice(0,10) : 'INMEDIATO' }} // {{ bd.ends_at ? bd.ends_at.slice(0,10) : 'ETERNO' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-bold select-none text-[10px]">
                                <span :class="bd.type === 'TEMPLATE' ? 'text-blue-500' : 'text-neutral-600 dark:text-neutral-400'">{{ bd.type }}</span>
                            </td>
                            <td class="p-3 text-center select-none">
                                <span :class="bd.is_active ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-700 border-rose-200 dark:border-rose-800'" class="px-2 py-0.5 text-[9px] font-black rounded-sm border">
                                    {{ bd.is_active ? 'SINC' : 'BAJA' }}
                                </span>
                            </td>
                            <td class="p-3 text-right select-none" @click.stop>
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('admin.bundles.edit', bd.id)" class="text-neutral-400 hover:text-neutral-900 dark:hover:text-white p-1 rounded border border-transparent hover:border-neutral-200 dark:hover:border-neutral-700 transition-all"><Edit :size="13"/></Link>
                                    <button @click="deleteBundle(bd.id)" class="text-neutral-300 hover:text-rose-600 p-1 rounded border border-transparent hover:border-rose-200 dark:hover:border-rose-900/40 transition-all"><Trash2 :size="13"/></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="bundles.length === 0">
                            <td colspan="4" class="p-16 text-center text-neutral-400 select-none italic font-sans">
                                Ningún macro agrupador parametrizado en el sistema core.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="lg:col-span-5 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-md p-4 h-fit min-h-[300px] flex flex-col justify-between shadow-xs select-none">
                <div v-if="selectedBundle">
                    <div class="border-b border-neutral-200 dark:border-neutral-800 pb-2 mb-3">
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">// COMPONENTES_DESGLOSE</div>
                        <h3 class="text-sm font-black text-neutral-900 dark:text-white uppercase mt-1 italic tracking-tight">{{ selectedBundle.name }}</h3>
                    </div>

                    <div v-if="loadingItems" class="p-12 text-center text-neutral-400 font-mono text-[11px] uppercase tracking-wider flex items-center justify-center gap-1.5">
                        <span class="w-4 h-4 border-2 border-neutral-400 border-t-transparent animate-spin rounded-full"></span>
                        <span>Mapeando Relaciones Atómicas...</span>
                    </div>

                    <div v-else class="space-y-1.5 max-h-[380px] overflow-y-auto pr-1">
                        <div v-for="item in bundleItems" :key="item.id" class="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded p-2.5 flex justify-between items-center shadow-xs">
                            <div class="min-w-0 pr-2">
                                <div class="font-bold text-neutral-900 dark:text-neutral-100 uppercase truncate text-[11px] select-all">{{ item.sku_name }}</div>
                                <div class="text-[9px] text-neutral-400 mt-0.5 select-all font-mono">{{ item.sku_code }}</div>
                            </div>
                            <div class="px-2.5 py-1 bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded font-black text-xs text-neutral-900 dark:text-white shrink-0 select-all">
                                {{ Number(item.quantity).toFixed(3) }} U
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="my-auto text-center text-neutral-400 italic py-12 px-4 font-sans">
                    <Layers class="mx-auto text-neutral-300 dark:text-neutral-800 mb-2" :size="32" />
                    Seleccione un combo maestro de la grilla para auditar analíticamente sus artículos vinculados.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>