<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Ajusta la ruta según tu estructura de carpetas

const props = defineProps({
  branches: { type: Array, required: true }
});

// Estados de control de la interfaz
const selectedBranchId = ref('');
const searchQuery = ref('');
const balances = ref([]);
const expandedSkuId = ref(null);

// Estados de carga
const loadingBalances = ref(false);
const loadingDetails = ref(false);

// Estructura de caché reactiva
const detailsCache = reactive({});

/**
 * Carga los saldos consolidados al cambiar la sucursal
 */
const handleBranchChange = async () => {
  expandedSkuId.value = null;
  balances.value = [];
  Object.keys(detailsCache).forEach(key => delete detailsCache[key]);

  if (!selectedBranchId.value) return;

  loadingBalances.value = true;
  try {
    const response = await axios.get(route('admin.inventory.search'), {
      params: { branch_id: selectedBranchId.value }
    });
    balances.value = response.data;
  } catch (error) {
    alert('Error al consultar los saldos de inventario.');
  } finally {
    loadingBalances.value = false;
  }
};

/**
 * Buscador predictivo en el cliente
 */
const filteredBalances = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return balances.value;

  return balances.value.filter(b => 
    b.sku_name.toLowerCase().includes(query) || 
    b.sku_code.toLowerCase().includes(query)
  );
});

/**
 * Control del Acordeón con Lazy Loading y corrección de llaves de parámetros
 */
const toggleAccordion = async (skuId) => {
  if (expandedSkuId.value === skuId) {
    expandedSkuId.value = null;
    return;
  }

  expandedSkuId.value = skuId;

  if (detailsCache[skuId]) return;

  loadingDetails.value = true;
  try {
    // CORRECCIÓN: Se envía el parámetro mapeado como 'skuId' para coincidir con la ruta modificada
    const [lotsResponse, kardexResponse] = await Promise.all([
      axios.get(route('admin.inventory.lots', { skuId: skuId }), { params: { branch_id: selectedBranchId.value } }),
      axios.get(route('admin.inventory.kardex', { skuId: skuId }), { params: { branch_id: selectedBranchId.value } })
    ]);

    detailsCache[skuId] = {
      lots: lotsResponse.data,
      kardex: kardexResponse.data
    };
  } catch (error) {
    expandedSkuId.value = null;
    alert('Error al recuperar los detalles del servidor.');
  } finally {
    loadingDetails.value = false;
  }
};
</script>

<template>
  <AdminLayout>
    <template #header>
      Control de Stock Base
    </template>

    <div class="inventory-container">
      <div class="filters-section bg-card border p-4 rounded-lg mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="filter-group flex flex-col gap-1">
          <label class="text-sm font-medium text-muted-foreground">Sucursal de Auditoría</label>
          <select v-model="selectedBranchId" @change="handleBranchChange" class="w-full border rounded p-2 bg-background text-foreground">
            <option value="">-- Seleccione una sucursal --</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
          </select>
        </div>

        <div class="filter-group flex flex-col gap-1" v-if="selectedBranchId">
          <label class="text-sm font-medium text-muted-foreground">Filtrar Existencias</label>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Buscar por código o nombre de SKU..." 
            class="w-full border rounded p-2 bg-background text-foreground"
          />
        </div>
      </div>

      <div v-if="loadingBalances" class="p-4 text-center text-muted-foreground">
        Consultando balances generales del nodo seleccionado...
      </div>

      <div v-if="!loadingBalances && selectedBranchId && filteredBalances.length > 0" class="overflow-x-auto border rounded-lg bg-card">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
              <th class="p-3">Código</th>
              <th class="p-3">Producto (SKU)</th>
              <th class="p-3">Físico Total</th>
              <th class="p-3">Reservado</th>
              <th class="p-3">En Cuarentena</th>
              <th class="p-3">Disponible</th>
              <th class="p-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <template v-for="balance in filteredBalances" :key="balance.sku_id">
              
              <tr 
                @click="toggleAccordion(balance.sku_id)" 
                class="border-b hover:bg-muted/30 cursor-pointer transition-colors"
                :class="{ 'bg-muted/20': expandedSkuId === balance.sku_id }"
              >
                <td class="p-3 font-mono font-bold">{{ balance.sku_code }}</td>
                <td class="p-3 font-medium">{{ balance.sku_name }}</td>
                <td class="p-3">{{ balance.total_physical.toFixed(3) }}</td>
                <td class="p-3 text-muted-foreground">{{ balance.total_reserved.toFixed(3) }}</td>
                <td class="p-3" :class="{ 'text-amber-600 font-semibold': balance.total_quarantine > 0 }">
                  {{ balance.total_quarantine.toFixed(3) }}
                </td>
                <td class="p-3 text-green-600 font-bold">{{ balance.available.toFixed(3) }}</td>
                <td class="p-3 text-right">
                  <button class="text-xs bg-secondary text-secondary-foreground px-3 py-1 rounded border hover:bg-secondary/80">
                    {{ expandedSkuId === balance.sku_id ? 'Cerrar' : 'Auditar' }}
                  </button>
                </td>
              </tr>

              <tr v-if="expandedSkuId === balance.sku_id" class="bg-muted/10">
                <td colspan="7" class="p-4 border-b">
                  <div v-if="loadingDetails" class="text-center py-4 text-muted-foreground text-xs animate-pulse">
                    Extrayendo registros de lotes y traza de movimientos desde el núcleo...
                  </div>

                  <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="border rounded-lg p-4 bg-card shadow-sm">
                      <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Lotes de Ingreso Activos</h4>
                      <table class="w-full text-xs text-left" v-if="detailsCache[balance.sku_id]?.lots.length > 0">
                        <thead>
                          <tr class="border-b text-muted-foreground">
                            <th class="pb-2">Lote</th>
                            <th class="pb-2">Cant. Actual</th>
                            <th class="pb-2">Vencimiento</th>
                            <th class="pb-2 text-right">Estado</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr 
                            v-for="lot in detailsCache[balance.sku_id].lots" 
                            :key="lot.id" 
                            class="border-b last:border-0"
                            :class="{ 'text-destructive bg-destructive/5': lot.is_expired }"
                          >
                            <td class="py-2 font-mono">{{ lot.lot_code }}</td>
                            <td class="py-2 font-semibold">{{ lot.quantity.toFixed(3) }}</td>
                            <td class="py-2">{{ lot.expiration_date || 'Sin vencimiento' }}</td>
                            <td class="py-2 text-right font-medium">
                              <span v-if="lot.is_expired" class="text-red-600 font-bold">CADUCADO</span>
                              <span v-else class="text-green-600">Disponible</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      <div v-else class="text-xs text-muted-foreground py-2">No se registran lotes físicos con existencias.</div>
                    </div>

                    <div class="border rounded-lg p-4 bg-card shadow-sm">
                      <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Historial de Transacciones (Kárdex)</h4>
                      <div class="max-h-[250px] overflow-y-auto pr-1" v-if="detailsCache[balance.sku_id]?.kardex.length > 0">
                        <table class="w-full text-xs text-left">
                          <thead>
                            <tr class="border-b text-muted-foreground sticky top-0 bg-card">
                              <th class="pb-2">Fecha</th>
                              <th class="pb-2">Operación</th>
                              <th class="pb-2">Cantidad</th>
                              <th class="pb-2">Referencia</th>
                              <th class="pb-2 text-right">Usuario</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="move in detailsCache[balance.sku_id].kardex" :key="move.id" class="border-b last:border-0 hover:bg-muted/40">
                              <td class="py-2 text-muted-foreground">{{ new Date(move.created_at).toLocaleDateString() }}</td>
                              <td class="py-2 font-bold">{{ move.type }}</td>
                              <td class="py-2 font-semibold">{{ move.quantity.toFixed(3) }}</td>
                              <td class="py-2">
                                {{ move.reference }}
                                <div v-if="move.reason" class="text-[10px] text-amber-600 italic">Motivo: {{ move.reason }}</div>
                              </td>
                              <td class="py-2 text-right text-muted-foreground">{{ move.admin_name || 'Sistema' }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div v-else class="text-xs text-muted-foreground py-2">No existen movimientos registrados para este SKU.</div>
                    </div>

                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div v-if="selectedBranchId && filteredBalances.length === 0 && !loadingBalances" class="p-6 text-center text-muted-foreground border rounded-lg bg-card">
        Ningún registro coincide con el criterio de búsqueda ingresado.
      </div>
    </div>
  </AdminLayout>
</template>