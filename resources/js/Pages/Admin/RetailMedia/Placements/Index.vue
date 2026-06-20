<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit2, Trash2, X, Layers, Save, AlertCircle, Eye } from 'lucide-vue-next';

const props = defineProps({
    placements: { type: Array, required: true }
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const currentPlacementId = ref(null);

const form = useForm({
    name: '',
    code: '',
    max_items: 5,
    is_active: true
});

const openCreateModal = () => {
    isEditing.value = false;
    currentPlacementId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (placement) => {
    isEditing.value = true;
    currentPlacementId.value = placement.id;
    form.clearErrors();
    form.name = placement.name;
    form.code = placement.code;
    form.max_items = placement.max_items;
    form.is_active = placement.is_active;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('retail-media.ad-placements.update', currentPlacementId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('retail-media.ad-placements.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    }
};

const deletePlacement = (id) => {
    if (confirm('¿Eliminar este espacio publicitario? Se verificará que no posea creativos activos.')) {
        router.delete(route('retail-media.ad-placements.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Espacios Comerciales - Retail Media" />
        <template #header>Zonas de Monetización Publicitaria</template>

        <div class="space-y-6">
            <div class="flex items-center justify-between bg-card border p-4 rounded-xl shadow-sm">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Ad Placements</h3>
                    <p class="text-xs text-muted-foreground">Configuración física de contenedores de banners en la pasarela cliente.</p>
                </div>
                <button @click="openCreateModal" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">
                    <Plus :size="16" /> Agregar Espacio
                </button>
            </div>

            <div class="border rounded-xl bg-card overflow-x-auto shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                            <th class="p-4">Identificador Estructura</th>
                            <th class="p-4">Código Técnico</th>
                            <th class="p-4">Límite Elementos</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        <tr v-for="placement in placements" :key="placement.id" class="hover:bg-muted/30 transition-colors">
                            <td class="p-4 font-medium text-foreground">{{ placement.name }}</td>
                            <td class="p-4 font-mono text-xs font-bold text-primary">{{ placement.code }}</td>
                            <td class="p-4 font-mono text-xs">{{ placement.max_items }} uds max</td>
                            <td class="p-4">
                                <span :class="placement.is_active ? 'text-green-600' : 'text-muted-foreground'" class="text-xs font-bold uppercase tracking-wider">
                                    {{ placement.is_active ? '● Activo' : '○ Inactivo' }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(placement)" class="p-2 border rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                                        <Edit2 :size="14" />
                                    </button>
                                    <button @click="deletePlacement(placement.id)" class="p-2 border rounded-lg hover:bg-destructive/10 text-destructive transition-colors">
                                        <Trash2 :size="14" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-card border rounded-2xl w-full max-w-md shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h2 class="text-sm font-black uppercase tracking-wider">Configurar Espacio Comercial</h2>
                        <button @click="closeModal" class="p-1.5 hover:bg-muted rounded-lg text-muted-foreground"><X :size="16" /></button>
                    </div>
                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold uppercase text-muted-foreground">Nombre Identificador</label>
                            <input type="text" v-model="form.name" class="w-full border rounded-lg p-2.5 bg-background text-sm" placeholder="Ej: Banner Principal Home Superior" required />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold uppercase text-muted-foreground">Código de Inyección (Único)</label>
                            <input type="text" v-model="form.code" :disabled="isEditing" class="w-full border rounded-lg p-2.5 bg-background text-sm font-mono uppercase" placeholder="HOME_HERO" required />
                            <div v-if="form.errors.code" class="text-xs text-destructive font-semibold mt-1">{{ form.errors.code }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Máximo Banners Rotativos</label>
                                <input type="number" v-model="form.max_items" class="w-full border rounded-lg p-2.5 bg-background text-sm font-mono" min="1" max="50" required />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold uppercase text-muted-foreground">Estado Operativo</label>
                                <div class="flex items-center h-full">
                                    <input type="checkbox" v-model="form.is_active" id="is_active_placement" class="rounded border-gray-300 text-primary focus:ring-primary mr-2" />
                                    <label密 for="is_active_placement" class="text-xs font-bold uppercase text-foreground">Habilitado</label密>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 border-t flex items-center justify-end gap-3">
                            <button type="button" @click="closeModal" class="border rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wider bg-background text-foreground hover:bg-muted">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">
                                <Save :size="14" /> Guardar Espacio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>