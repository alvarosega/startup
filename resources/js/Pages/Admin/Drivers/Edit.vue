<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { 
  Save, 
  ArrowLeft, 
  ShieldCheck, 
  User, 
  Truck, 
  FileImage, 
  ExternalLink, 
  AlertTriangle, 
  CheckCircle2,
  Bike,
  Car
} from 'lucide-vue-next'

const props = defineProps({ driver: Object })

const form = useForm({
  first_name: props.driver.first_name,
  last_name: props.driver.last_name,
  phone: props.driver.phone,
  license_number: props.driver.license_number,
  license_plate: props.driver.license_plate,
  vehicle_type: props.driver.vehicle_type,
  is_identity_verified: !!props.driver.is_identity_verified,
  is_active: !!props.driver.is_active
})

const submit = () => form.put(route('admin.drivers.update', props.driver.id))

const getImageUrl = (path) => path ? `/storage/${path}` : null

const getVehicleIcon = (type) => {
  if (type === 'moto') return Bike
  if (type === 'car') return Car
  return Truck
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto py-8 px-4">
      
      <!-- Header -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
        <div class="flex items-center gap-4">
          <Link 
            :href="route('admin.drivers.index')" 
            class="inline-flex items-center justify-center gap-2 rounded-lg font-medium border border-input bg-transparent hover:bg-accent hover:text-accent-foreground transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed p-2 rounded-full"
          >
            <ArrowLeft :size="20" />
          </Link>
          
          <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-2xl md:text-3xl font-display font-semibold tracking-tight">
                {{ form.first_name }} {{ form.last_name }}
              </h1>
              
              <span 
                v-if="form.is_identity_verified" 
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-success/10 text-success border-success/20 hover:bg-success/15 transition-colors duration-200 gap-1.5"
              >
                <CheckCircle2 :size="12" /> VERIFICADO
              </span>
              
              <span 
                v-else 
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-warning/10 text-warning border-warning/20 hover:bg-warning/15 transition-colors duration-200 animate-pulse gap-1.5"
              >
                <AlertTriangle :size="12" /> PENDIENTE
              </span>
            </div>
            
            <p class="text-sm text-muted-foreground font-mono mt-1">
              ID: #{{ driver.id }} • {{ form.phone }}
            </p>
          </div>
        </div>

        <button 
          @click="submit" 
          :disabled="form.processing"
          class="inline-flex items-center justify-center gap-2 rounded-lg font-medium bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed px-6 py-3.5 text-base shadow-lg hover:shadow-xl"
        >
          <Save :size="18" />
          <span v-if="form.processing" class="animate-spin rounded-full border-2 border-border border-t-primary h-4 w-4"></span>
          <span v-else>Guardar Cambios</span>
        </button>
      </div>

      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Panel Izquierdo -->
        <div class="space-y-6">
          
          <!-- Estado de Verificación -->
          <div class="bg-card rounded-xl border border-border shadow-md overflow-hidden transition-all duration-300 p-6"
               :class="form.is_identity_verified ? 'border-success/30 ring-4 ring-success/10' : 'border-warning/30 ring-4 ring-warning/10'">
            
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-semibold text-foreground flex items-center gap-2">
                <ShieldCheck :class="form.is_identity_verified ? 'text-success' : 'text-warning'" :size="18" />
                Estado de Verificación
              </h3>
              
              <label class="relative inline-flex items-center cursor-pointer">
                <input 
                  type="checkbox" 
                  v-model="form.is_identity_verified" 
                  class="sr-only peer" 
                />
                <div class="w-14 h-7 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-background after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-background after:border-border after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-success"></div>
              </label>
            </div>
            
            <p class="text-sm text-muted-foreground leading-relaxed">
              <span v-if="!form.is_identity_verified">
                Revisa cuidadosamente los documentos a la derecha. Si coinciden con los datos, activa el interruptor para habilitar al conductor.
              </span>
              <span v-else>
                Este conductor tiene permiso para operar. Desactiva el interruptor si detectas irregularidades.
              </span>
            </p>
            
            <div class="mt-4 pt-4 border-t border-border/30 flex items-center gap-3">
              <input 
                type="checkbox" 
                v-model="form.is_active" 
                id="is_active"
                class="w-5 h-5 rounded border-border text-primary focus:ring-2 focus:ring-ring focus:ring-offset-2"
              />
              <label for="is_active" class="text-sm font-medium text-foreground cursor-pointer">
                Cuenta Activa (Login)
              </label>
            </div>
          </div>

          <!-- Datos Editables -->
          <div class="bg-card rounded-xl border border-border shadow-md overflow-hidden p-6 space-y-4">
            
            <div class="flex items-center gap-2 text-muted-foreground border-b border-border/50 pb-2 mb-2">
              <User :size="16" /> 
              <span class="text-xs font-semibold uppercase">Datos Editables</span>
            </div>
            
            <!-- Nombre -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-foreground">Nombre</label>
              <input 
                v-model="form.first_name" 
                type="text"
                class="w-full bg-background border border-input rounded-lg px-4 py-2.5 text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed"
                :class="form.errors.first_name ? 'border-error' : ''"
              />
              <p v-if="form.errors.first_name" class="text-sm text-error mt-1">
                {{ form.errors.first_name }}
              </p>
            </div>
            
            <!-- Apellido -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-foreground">Apellido</label>
              <input 
                v-model="form.last_name" 
                type="text"
                class="w-full bg-background border border-input rounded-lg px-4 py-2.5 text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed"
                :class="form.errors.last_name ? 'border-error' : ''"
              />
              <p v-if="form.errors.last_name" class="text-sm text-error mt-1">
                {{ form.errors.last_name }}
              </p>
            </div>
            
            <!-- Licencia -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-foreground">Licencia</label>
              <input 
                v-model="form.license_number" 
                type="text"
                class="w-full bg-background border border-input rounded-lg px-4 py-2.5 text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed"
                :class="form.errors.license_number ? 'border-error' : ''"
              />
              <p v-if="form.errors.license_number" class="text-sm text-error mt-1">
                {{ form.errors.license_number }}
              </p>
            </div>
            
            <!-- Placa -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-foreground">Placa</label>
              <input 
                v-model="form.license_plate" 
                type="text"
                class="w-full bg-background border border-input rounded-lg px-4 py-2.5 text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all duration-200 ease-smooth disabled:opacity-50 disabled:cursor-not-allowed"
                :class="form.errors.license_plate ? 'border-error' : ''"
              />
              <p v-if="form.errors.license_plate" class="text-sm text-error mt-1">
                {{ form.errors.license_plate }}
              </p>
            </div>
            
            <!-- Tipo de Vehículo -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-foreground">Tipo de Vehículo</label>
              <select 
                v-model="form.vehicle_type" 
                class="w-full bg-background border border-input rounded-lg px-4 py-2.5 text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all duration-200 ease-smooth"
                :class="form.errors.vehicle_type ? 'border-error' : ''"
              >
                <option value="moto">Moto</option>
                <option value="car">Auto</option>
                <option value="truck">Camión</option>
              </select>
              <p v-if="form.errors.vehicle_type" class="text-sm text-error mt-1">
                {{ form.errors.vehicle_type }}
              </p>
            </div>
            
          </div>
        </div>

        <!-- Panel Derecho (Evidencia Documental) -->
        <div class="lg:col-span-2 space-y-6">
          
          <div class="bg-card rounded-xl border border-border shadow-md overflow-hidden p-6">
            
            <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
              <FileImage class="text-primary" :size="18" /> 
              Evidencia Documental
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              
              <!-- Carnet de Identidad -->
              <div class="space-y-2">
                <div class="flex justify-between text-xs">
                  <span class="font-semibold text-muted-foreground uppercase">Carnet de Identidad</span>
                </div>
                
                <div class="relative group rounded-xl overflow-hidden border-2 border-border bg-muted/20 aspect-video flex items-center justify-center">
                  <img 
                    v-if="props.driver.profile_docs?.ci_front_path" 
                    :src="getImageUrl(props.driver.profile_docs.ci_front_path)" 
                    class="w-full h-full object-contain bg-black/5" 
                  />
                  
                  <div v-else class="text-muted-foreground/70 flex flex-col items-center">
                    <AlertTriangle :size="24" class="mb-2" />
                    <span class="text-xs">No subido</span>
                  </div>

                  <a 
                    v-if="props.driver.profile_docs?.ci_front_path" 
                    :href="getImageUrl(props.driver.profile_docs.ci_front_path)" 
                    target="_blank"
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all duration-200 cursor-pointer"
                  >
                    <div class="bg-background text-foreground px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2">
                      <ExternalLink :size="14" /> 
                      Ver Original
                    </div>
                  </a>
                </div>
              </div>

              <!-- Licencia de Conducir -->
              <div class="space-y-2">
                <div class="flex justify-between text-xs">
                  <span class="font-semibold text-muted-foreground uppercase">Licencia de Conducir</span>
                  <span class="font-mono bg-muted px-2 py-1 rounded">
                    {{ form.license_number || 'Sin número' }}
                  </span>
                </div>
                
                <div class="relative group rounded-xl overflow-hidden border-2 border-border bg-muted/20 aspect-video flex items-center justify-center">
                  <img 
                    v-if="props.driver.profile_docs?.license_photo_path" 
                    :src="getImageUrl(props.driver.profile_docs.license_photo_path)" 
                    class="w-full h-full object-contain bg-black/5" 
                  />
                  
                  <div v-else class="text-muted-foreground/70 flex flex-col items-center">
                    <AlertTriangle :size="24" class="mb-2" />
                    <span class="text-xs">No subido</span>
                  </div>

                  <a 
                    v-if="props.driver.profile_docs?.license_photo_path" 
                    :href="getImageUrl(props.driver.profile_docs.license_photo_path)" 
                    target="_blank"
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all duration-200 cursor-pointer"
                  >
                    <div class="bg-background text-foreground px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2">
                      <ExternalLink :size="14" /> 
                      Ver Original
                    </div>
                  </a>
                </div>
              </div>

              <!-- Foto Vehículo -->
              <div class="space-y-2">
                <div class="flex justify-between text-xs">
                  <span class="font-semibold text-muted-foreground uppercase">Foto Vehículo</span>
                  <span class="font-mono bg-muted px-2 py-1 rounded">
                    {{ form.license_plate || 'Sin placa' }}
                  </span>
                </div>
                
                <div class="relative group rounded-xl overflow-hidden border-2 border-border bg-muted/20 aspect-video flex items-center justify-center">
                  <img 
                    v-if="props.driver.profile_docs?.vehicle_photo_path" 
                    :src="getImageUrl(props.driver.profile_docs.vehicle_photo_path)" 
                    class="w-full h-full object-contain bg-black/5" 
                  />
                  
                  <div v-else class="text-muted-foreground/70 flex flex-col items-center">
                    <Truck :size="24" class="mb-2" />
                    <span class="text-xs">Opcional / No subido</span>
                  </div>

                  <a 
                    v-if="props.driver.profile_docs?.vehicle_photo_path" 
                    :href="getImageUrl(props.driver.profile_docs.vehicle_photo_path)" 
                    target="_blank"
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all duration-200 cursor-pointer"
                  >
                    <div class="bg-background text-foreground px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2">
                      <ExternalLink :size="14" /> 
                      Ver Original
                    </div>
                  </a>
                </div>
              </div>

            </div>
            
            <!-- Información adicional -->
            <div v-if="driver.created_at" class="mt-6 pt-6 border-t border-border/30">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="text-xs text-muted-foreground">Creado el</span>
                  <p class="font-medium text-foreground">
                    {{ new Date(driver.created_at).toLocaleDateString('es-ES', { 
                      day: '2-digit', 
                      month: '2-digit', 
                      year: 'numeric' 
                    }) }}
                  </p>
                </div>
                
                <div>
                  <span class="text-xs text-muted-foreground">Última actualización</span>
                  <p class="font-medium text-foreground">
                    {{ new Date(driver.updated_at).toLocaleDateString('es-ES', { 
                      day: '2-digit', 
                      month: '2-digit', 
                      year: 'numeric' 
                    }) }}
                  </p>
                </div>
                
                <div>
                  <span class="text-xs text-muted-foreground">Verificado el</span>
                  <p class="font-medium text-foreground">
                    {{ driver.verified_at ? new Date(driver.verified_at).toLocaleDateString('es-ES', { 
                      day: '2-digit', 
                      month: '2-digit', 
                      year: 'numeric' 
                    }) : 'No verificado' }}
                  </p>
                </div>
                
                <div>
                  <span class="text-xs text-muted-foreground">Vehículo</span>
                  <p class="font-medium text-foreground flex items-center gap-1">
                    <component :is="getVehicleIcon(form.vehicle_type)" :size="14" class="text-muted-foreground" />
                    {{ form.vehicle_type === 'moto' ? 'Moto' : form.vehicle_type === 'car' ? 'Auto' : 'Camión' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sección de Observaciones (opcional) -->
          <div class="bg-card rounded-xl border border-border shadow-md overflow-hidden p-6">
            <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
              <AlertTriangle :size="18" class="text-warning" />
              <span>Observaciones del Sistema</span>
            </h3>
            
            <div class="space-y-3">
              <div v-if="!form.is_identity_verified" class="bg-warning/5 border border-warning/20 rounded-lg p-3">
                <div class="flex items-start gap-2">
                  <AlertTriangle :size="16" class="text-warning mt-0.5" />
                  <div>
                    <p class="text-sm font-medium text-warning">Verificación Pendiente</p>
                    <p class="text-xs text-muted-foreground mt-1">
                      Este conductor no puede aceptar viajes hasta que sea verificado.
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="!form.is_active" class="bg-error/5 border border-error/20 rounded-lg p-3">
                <div class="flex items-start gap-2">
                  <AlertTriangle :size="16" class="text-error mt-0.5" />
                  <div>
                    <p class="text-sm font-medium text-error">Cuenta Inactiva</p>
                    <p class="text-xs text-muted-foreground mt-1">
                      El conductor no podrá iniciar sesión en la aplicación.
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="form.is_identity_verified && form.is_active" class="bg-success/5 border border-success/20 rounded-lg p-3">
                <div class="flex items-start gap-2">
                  <CheckCircle2 :size="16" class="text-success mt-0.5" />
                  <div>
                    <p class="text-sm font-medium text-success">Estado Óptimo</p>
                    <p class="text-xs text-muted-foreground mt-1">
                      El conductor está completamente verificado y puede operar normalmente.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>