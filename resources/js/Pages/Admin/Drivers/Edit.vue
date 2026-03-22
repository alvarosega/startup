<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { 
    Save, ArrowLeft, ShieldCheck, User, Truck, FileImage, 
    ExternalLink, AlertTriangle, CheckCircle2, Building2,
    Cpu, Terminal, Fingerprint, Phone, Wifi, WifiOff,
    Bike, Car, Camera, Info, XCircle
} from 'lucide-vue-next'

const props = defineProps({ 
    driver: Object,
    branches: Array
})

const d = props.driver.data
const activeTab = ref('info')

const form = useForm({
    branch_id: d.branch_id || '',
    first_name: d.profile?.first_name || '',
    last_name: d.profile?.last_name || '',
    license_number: d.profile?.license_number || '',
    license_plate: d.profile?.license_plate || '',
    vehicle_type: d.profile?.vehicle_type || 'moto',
    status: d.status,
    rejection_reason: d.profile?.rejection_reason || '',
    // Campos requeridos por el Request aunque no se editen aquí
    phone: d.phone,
    email: d.email,
})

const submit = () => {
    form.put(route('admin.drivers.update', d.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AdminLayout>
        <div class="px-4 py-6 max-w-7xl mx-auto space-y-6">
            
            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-destructive/10 border-l-4 border-destructive animate-in fade-in slide-in-from-top-4">
                <div class="flex items-center gap-3 text-destructive mb-2">
                    <AlertTriangle :size="20" />
                    <span class="font-mono font-black text-xs uppercase tracking-widest">Bloqueo de Validación Detectado</span>
                </div>
                <ul class="space-y-1 ml-8">
                    <li v-for="(error, field) in form.errors" :key="field" class="text-[10px] font-mono text-destructive uppercase">
                        // {{ field }}: {{ Array.isArray(error) ? error[0] : error }}
                    </li>
                </ul>
            </div>

            <div class="flex justify-between items-center border-b border-primary/30 pb-6 group/header">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.drivers.index')" class="p-2 border border-border hover:border-primary transition-all">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-primary uppercase italic tracking-tighter">
                            EXPEDIENTE CONDUCTOR // {{ d.id.split('-')[0].toUpperCase() }}
                        </h1>
                        <p class="text-[10px] font-mono text-muted-foreground flex items-center gap-2 uppercase">
                            <Cpu :size="12" /> STATUS ACTUAL: <span class="text-primary font-black">{{ d.status }}</span>
                        </p>
                    </div>
                </div>

                <button @click="submit" :disabled="form.processing" 
                        class="px-6 py-3 bg-primary text-primary-foreground font-mono text-xs border border-primary/50 hover:shadow-neon-primary transition-all flex items-center gap-2 italic font-black">
                    <Save :size="16" /> {{ form.processing ? 'PROCESANDO...' : 'GUARDAR_CAMBIOS' }}
                </button>
            </div>

            <div class="flex gap-2 border-b border-border/50">
                <button v-for="tab in [{id:'info', label:'DATOS GENERALES'}, {id:'documents', label:'VERIFICACIÓN DOCUMENTAL'}]" 
                        :key="tab.id" @click="activeTab = tab.id"
                        class="px-6 py-3 text-[10px] font-mono font-bold transition-all uppercase"
                        :class="activeTab === tab.id ? 'text-primary border-b-2 border-primary italic' : 'text-muted-foreground hover:text-primary'">
                    {{ tab.label }}
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-show="activeTab === 'info'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in duration-500">
                    
                    <div class="space-y-4">
                        <div class="border border-border p-6 bg-card/30 space-y-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-2 opacity-10"><ShieldCheck :size="40" /></div>
                            
                            <h3 class="font-mono font-black text-xs text-primary flex items-center gap-2 uppercase">
                                <ShieldCheck :size="16" /> Control de Estado
                            </h3>

                            <div class="space-y-5">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono text-muted-foreground block uppercase font-bold tracking-widest">Sucursal de Base *</label>
                                    <select v-model="form.branch_id" 
                                            :class="{'border-destructive': form.errors.branch_id}"
                                            class="w-full bg-background border border-border p-3 font-mono text-xs focus:border-primary outline-none transition-all">
                                        <option value="">-- SELECCIONAR BASE --</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <p v-if="form.errors.branch_id" class="text-[9px] text-destructive font-bold uppercase">{{ form.errors.branch_id }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-mono text-muted-foreground block uppercase font-bold tracking-widest">Estado del Sujeto *</label>
                                    <select v-model="form.status" 
                                            :class="{'border-destructive': form.errors.status}"
                                            class="w-full bg-background border border-border p-3 font-mono text-xs focus:border-primary outline-none transition-all">
                                        <option value="pending">PENDIENTE DE REVISIÓN</option>
                                        <option value="active">APROBADO / ACTIVO</option>
                                        <option value="inactive">SUSPENDIDO / BLOQUEADO</option>
                                        <option value="rejected">RECHAZADO</option>
                                    </select>
                                    <p v-if="form.errors.status" class="text-[9px] text-destructive font-bold uppercase">{{ form.errors.status }}</p>
                                </div>

                                <div v-if="form.status === 'rejected'" class="space-y-2 animate-in slide-in-from-top-2">
                                    <label class="text-[10px] font-mono text-destructive block uppercase font-bold tracking-widest">Motivo de Rechazo</label>
                                    <textarea v-model="form.rejection_reason" placeholder="EXPLICACIÓN PARA EL CONDUCTOR..." 
                                              class="w-full bg-background border border-destructive/30 p-3 font-mono text-xs outline-none h-24 focus:border-destructive" />
                                </div>
                            </div>
                        </div>

                        <div class="border border-border p-6 bg-muted/20 space-y-3">
                            <h4 class="text-[9px] font-mono font-black text-muted-foreground uppercase tracking-[0.2em]">Contacto Registrado</h4>
                            <div class="flex items-center gap-3 text-xs font-mono"><Phone :size="14" class="text-primary" /> {{ d.phone }}</div>
                            <div class="flex items-center gap-3 text-xs font-mono"><Mail :size="14" class="text-primary" /> {{ d.email }}</div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 border border-border p-8 bg-card/20 space-y-8 relative">
                        <h3 class="font-mono font-black text-xs text-primary border-b border-primary/20 pb-4 uppercase">Identidad Biográfica y Vehicular</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-muted-foreground uppercase tracking-widest italic">// Nombres</label>
                                <input v-model="form.first_name" type="text" class="w-full bg-background border border-border/50 p-3 font-mono text-sm focus:border-primary outline-none uppercase" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-muted-foreground uppercase tracking-widest italic">// Apellidos</label>
                                <input v-model="form.last_name" type="text" class="w-full bg-background border border-border/50 p-3 font-mono text-sm focus:border-primary outline-none uppercase" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-muted-foreground uppercase tracking-widest italic">// Tipo de Unidad</label>
                                <select v-model="form.vehicle_type" class="w-full bg-background border border-border/50 p-3 font-mono text-sm outline-none focus:border-primary">
                                    <option value="moto">MOTOCICLETA</option>
                                    <option value="car">AUTOMÓVIL</option>
                                    <option value="truck">CAMIONETA</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-muted-foreground uppercase tracking-widest italic">// Nº Licencia</label>
                                <input v-model="form.license_number" type="text" class="w-full bg-background border border-border/50 p-3 font-mono text-sm focus:border-primary outline-none" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-mono font-bold text-muted-foreground uppercase tracking-widest italic">// Placa</label>
                                <input v-model="form.license_plate" type="text" class="w-full bg-background border border-border/50 p-3 font-mono text-sm focus:border-primary outline-none uppercase" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'documents'" class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-500">
                    <div v-for="doc in [
                        {id: 'ci', label: 'Cédula de Identidad', path: d.profile?.ci_front_path}, 
                        {id: 'license', label: 'Licencia de Conducir', path: d.profile?.license_path}
                    ]" :key="doc.id" class="border border-border p-6 bg-card/10 space-y-4 group/doc">
                        
                        <div class="flex justify-between items-center">
                            <h4 class="text-[10px] font-mono font-black text-primary uppercase italic">{{ doc.label }}</h4>
                            <a v-if="doc.path" :href="doc.path" target="_blank" class="text-cyan-500 hover:text-cyan-400 transition-colors flex items-center gap-2">
                                <span class="text-[8px] font-black uppercase">Ampliar</span> <ExternalLink :size="14" />
                            </a>
                        </div>
                        
                        <div class="aspect-video bg-black/40 border-2 border-dashed border-border flex items-center justify-center overflow-hidden relative">
                            <img v-if="doc.path" :src="doc.path" class="w-full h-full object-contain group-hover/doc:scale-105 transition-transform duration-700" />
                            <div v-else class="text-center space-y-2">
                                <XCircle :size="32" class="mx-auto text-destructive/40" />
                                <span class="text-[8px] font-mono text-muted-foreground uppercase tracking-widest">Documento_No_Cargado</span>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 p-6 bg-primary/5 border border-dashed border-primary/30 flex gap-4 items-start">
                        <Info :size="20" class="text-primary shrink-0 mt-1" />
                        <div class="space-y-1">
                            <p class="text-[10px] font-mono font-black text-primary uppercase tracking-widest">Protocolo de Verificación Documental</p>
                            <p class="text-[9px] font-mono text-muted-foreground leading-relaxed uppercase">
                                Antes de cambiar el estado a "ACTIVO", confirme que los documentos no estén vencidos y que la placa coincida con la unidad física.
                                La sucursal asignada será la responsable de la liquidación de este conductor.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>