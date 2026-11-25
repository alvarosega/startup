# Blueprint del Proyecto: CyberMarket / ElectricLuxury

Documento maestro y fuente única de la verdad (*Single Source of Truth*) para el desarrollo, mantenimiento y evolución de la plataforma.

---

## 1. Visión General del Proyecto y Propósito

**CyberMarket (ElectricLuxury)** es una plataforma integral de comercio electrónico de alta velocidad y logística en tiempo real (Quick-Commerce / Multi-Branch E-Commerce) construida con **Laravel 12**, **Vue 3 (Inertia.js)** y **Tailwind CSS**.

### Capacidades Principales
- **Arquitectura Multitenant de Silos y Roles**:
  - **Silo Admin (`adm/`)**: Gestión integral de catálogo, precios, inventarios multizona, kardex, operaciones de sucursales (cajas, horarios, excepciones), auditoría de usuarios, retail media y centro de comando logístico en tiempo real.
  - **Silo Customer (`/`)**: Tienda electrónica ultrarrápida impulsada por Inertia.js con soporte para compras como invitado (UUID persistido), sincronización reactiva, geolocalización satelital para asignación de sucursales de despacho, carruseles de productos, bundles atómicos y configurables, carrito, perfiles y seguimiento de pedidos.
  - **Silo Driver (`/driver`)**: Plataforma para repartidores con registro multifase, verificación documental, conmutación de estado en línea/fuera de línea, reclamo de órdenes, validación de recogida por QR/código, rastreo de ruta y confirmación de entrega en tiempo real.
  - **Silo API REST (`/api/v1`)**: Endpoints desacoplados autenticados vía Sanctum para integraciones externas y operaciones administrativas.

- **Logística y Geolocalización en Tiempo Real**:
  - Delimitación geográfica de zonas de mercado y cobertura de sucursales mediante **Leaflet** y polígonos **Geoman**.
  - Geocodificación inversa vía OpenStreetMap / Nominatim.
  - Tracking y telemetría de conductores vía Redis (`RedisLocationService`) y WebSockets (**Laravel Reverb** + **Laravel Echo**).

- **Gestión de Inventario Avanzada**:
  - Control de lotes (`InventoryLot`), balances (`InventoryBalance`), movimientos (`InventoryMovement`) y kardex.
  - Manejo de stock de seguridad (*Safety Stock*), transferencias y cuarentena (*Quarantine*).
  - Compras y recepciones a proveedores, y solicitudes de baja/merma (`RemovalRequest`).

---

## 2. Pila Tecnológica y Estándares

### Backend
- **Framework**: Laravel 12.x sobre PHP 8.2+
- **Autenticación**: Multi-Guard con sesiones y tokens (`super_admin`, `customer`, `driver`, `sanctum`).
- **Permisos & Roles**: Spatie Laravel Permission (`role`, `permission`).
- **ORM & BD**: Eloquent ORM con MariaDB / MySQL y SQLite.
- **Cache & Telemetría**: Redis (Predis) para localización en tiempo real y cachés de alta frecuencia.
- **WebSockets**: Laravel Reverb + Laravel Echo.
- **Patrones de Diseño**:
  - **Fat Models & Action Classes**: Separación estricta de lógica de negocio en `App\Actions\{Admin, Customer, Driver}`.
  - **Domain Services**: Lógica transversal en `App\Services\{Cart, Customer, Finance, Geo, Inventory, Logistics, Order}`.
  - **Data Transfer Objects (DTOs)**: Objetos fuertemente tipados inmutables (`App\DTOs\`).
  - **Idempotencia**: Middleware `CheckIdempotency` para transacciones seguras de checkout y pagos.

### Frontend
- **Framework**: Vue 3 (Composition API con `<script setup>`).
- **Adaptador SPA**: Inertia.js 3.0 (`@inertiajs/vue3`).
- **Enrutamiento**: Ziggy Vue para generación de rutas de Laravel en el cliente.
- **Estilos**: Tailwind CSS 3.4 con plugins oficiales (`@tailwindcss/forms`, `@tailwindcss/typography`, `@tailwindcss/aspect-ratio`).
- **Iconografía**: `lucide-vue-next`.
- **Mapas y GIS**: Leaflet 1.9+, Leaflet Geoman (`@geoman-io/leaflet-geoman-free`), MarkerCluster y `@vue-leaflet/vue-leaflet`.
- **Animaciones y UX**: Anime.js, animaciones fluidas, barra de progreso personalizada F1-Orange (`#f97316`), soporte nativo de tema Claro/Oscuro (`useTheme`).

---

## 3. Estructura de Directorios Clave

```
startup/
├── app/
│   ├── Actions/               # Clases de acción individuales por silo (Admin, Customer, Driver)
│   ├── Architecture/          # Reglas arquitectónicas y contratos de dominio
│   ├── Casts/                 # Castings personalizados de Eloquent
│   ├── DTOs/                  # Data Transfer Objects (p. ej. RegisterCustomerData)
│   ├── Enums/                 # Enumeraciones tipadas de PHP 8.2+
│   ├── Http/
│   │   ├── Controllers/Web/   # Controladores Web lean (Admin, Customer, Driver)
│   │   ├── Controllers/Api/   # Controladores API REST v1
│   │   ├── Middleware/        # Middlewares Inertia específicos (HandleAdmin, HandleCustomer, HandleDriver)
│   │   └── Requests/          # Validación de formularios
│   ├── Models/
│   │   ├── Bundle/            # Bundle, BundleItem
│   │   ├── Catalog/           # Brand, Category, Product, Sku
│   │   ├── Inventory/         # InventoryBalance, Lot, Movement, Price, Purchase, Removal
│   │   ├── Operations/        # Branch, BranchDailyCash, BranchSchedule, Provider
│   │   ├── RetailMedia/       # AdCampaign, AdCreative, AdPlacement
│   │   ├── Sanctum/           # Tokens desacoplados por tipo de usuario
│   │   └── Users/             # Admin, Customer, Driver y sus perfiles, logs, direcciones
│   ├── Rules/                 # Reglas de validación personalizadas (cobertura geográfica, identidad global)
│   ├── Services/              # Servicios de dominio (Cart, Geo, Finance, Logistics, Inventory)
│   └── Traits/                # Traits reutilizables (HasUv7, ValidatesGlobalIdentity)
├── database/
│   ├── migrations/            # Esquema relacional estructurado por fechas y dominios
│   └── seeders/               # Población de datos completa para pruebas y entorno local
├── resources/
│   ├── css/app.css            # Configuración Tailwind y estilos base
│   └── js/
│       ├── Components/        # Componentes Vue reutilizables (Admin, Customer, Shop, Auth, Base)
│       ├── Composables/       # Composables reactivos (useTheme, etc.)
│       ├── Layouts/           # AdminLayout.vue, ShopLayout.vue, DriverLayout.vue
│       ├── Pages/             # Vistas de Inertia agrupadas por silo (Admin, Customer, Driver, Legal, Public)
│       └── app.js             # Punto de entrada de Vue 3 + Inertia + Ziggy
└── routes/
    ├── admin.php              # Rutas del silo administrativo (/adm)
    ├── customer.php           # Rutas del silo de clientes (/)
    ├── driver.php             # Rutas del silo de repartidores (/driver)
    ├── api.php                # Endpoints API REST (/api/v1)
    ├── channels.php           # Canales de eventos en tiempo real
    └── web.php                # Enrutador principal y servicios geográficos auxiliares
```

---

## 4. Esquema de Datos y Modelos

| Dominio | Modelos Principales | Propósito |
| :--- | :--- | :--- |
| **Usuarios & Auth** | `Admin`, `Customer`, `Driver`, `CustomerProfile`, `DriverProfile`, `CustomerAddress` | Gestión desacoplada de identidades con perfiles dedicados y registro geolocalizado de direcciones. |
| **Catálogo** | `Category`, `Brand`, `Product`, `Sku`, `Bundle`, `BundleItem` | Jerarquía de productos con variantes (SKUs), marcas, categorías y paquetes combinados con precios especiales. |
| **Operaciones** | `Branch`, `BranchSchedule`, `BranchDailyCash`, `BranchScheduleException`, `Provider` | Sucursales con polígonos de cobertura, horarios semanales, festivos, control de apertura/cierre de caja y proveedores. |
| **Inventario & Precios**| `InventoryBalance`, `InventoryLot`, `InventoryMovement`, `Price`, `Purchase`, `RemovalRequest` | Control de stock físico y de seguridad, compras a proveedores con lote y fecha de vencimiento, kardex y lista de precios. |
| **Zonas & Cobertura** | `MarketZone`, tablas pivote | Coordenadas geoespaciales para determinar disponibilidad de catálogo y tiempos de entrega. |
| **Ventas & Pedidos** | `Cart`, `CartItem`, `Order`, `OrderItem`, `CheckoutSnapshot`, `IdempotencyKey` | Flujo de venta con instantánea inmutable del checkout, validación de pago y seguimiento de estados. |
| **Retail Media** | `AdCampaign`, `AdPlacement`, `AdCreative` | Sistema de pauta publicitaria en carruseles y banners de la tienda digital. |

---

## 5. Diseño Visual, UX y Estilo

- **Paleta y Tema**:
  - Color de acento de alta energía: Naranja Fórmula 1 (`#f97316`) para indicadores de progreso e interactividad.
  - Modo dual Claro/Oscuro persistido en `localStorage` mediante composable `useTheme`.
  - Tarjetas con profundidad y sombreado suave con efecto de elevación.
- **Componentes Interactivos**:
  - Carruseles para marcas, categorías, productos destacados y bundles (`CategoryCarousel`, `HeroCarousel`, etc.).
  - Pickers interactivos de mapas para selección y verificación de dirección (`ClientLocationPicker`, `AdminLocationPicker`).
  - Animaciones fluidas para transiciones y microinteracciones de compras.

---

## 6. Arquitectura de Autenticación y Autorización Administrativa (RBAC Multi-Sucursal)

### Jerarquía de Administradores
1. **Administrador Maestro (`super_admin`)**:
   - `branch_id = null` (Alcance Global).
   - Acceso irrestricto a todos los módulos: creación de sucursales, configuración del sistema, catálogo maestro, finanzas globales, auditoría y asignación de usuarios/roles.
2. **Administrador de Sucursal (`branch_manager`)**:
   - `branch_id = UUID` (Asignado a una sucursal específica).
   - Control operativo completo de su sucursal: apertura/cierre de caja, horarios y excepciones, inventario local, compras, órdenes, despacho y permisos delegados. Puede además gestionar catálogo si se le asigna el permiso correspondiente.
3. **Operadores de Área de Sucursal**:
   - `branch_id = UUID` (Asignado a una sucursal específica).
   - Roles especializados:
     - **Almacén / Stock (`branch_warehouse`)**: `manage_inventory`, `manage_purchases`, `manage_removals`.
     - **Despacho / Logística (`branch_logistics`)**: `manage_orders`, `manage_dispatch`, `view_logistics_radar`.
     - **Caja / Finanzas (`branch_cashier`)**: `manage_branch_cash`.

### Matriz de Permisos Granulares (Guard: `super_admin`)
- `manage_catalog`: Creación y edición de productos, categorías, marcas y combos.
- `manage_branches`: Control y configuración de sucursales.
- `manage_branch_schedules`: Gestión de horarios semanales y excepciones/feriados.
- `manage_branch_cash`: Apertura, cierre y arqueo de caja diaria.
- `manage_inventory`: Consulta de existencias, kardex y lotes de sucursal.
- `manage_prices`: Creación y modificación de precios.
- `manage_purchases`: Ingreso de compras a proveedores.
- `manage_removals`: Registro de mermas y bajas de inventario.
- `manage_orders`: Revisión, aprobación/rechazo de pagos y preparación de órdenes.
- `manage_dispatch`: Despacho a repartidores y seguimiento de entregas.
- `view_logistics_radar`: Visualización del mapa de telemetría de repartidores en tiempo real.
- `manage_users`: Gestión de clientes y personal operativo.
- `manage_drivers`: Aprobación y gestión de repartidores.

---

## 7. Plan de Implementación Actual: Sistema de Autenticación y Autorización Multi-Sucursal

### Objetivo
Permitir el acceso al silo administrativo (`adm/login`) no solo al `super_admin`, sino también a los administradores de sucursal (`branch_manager`) y operadores de área (`branch_warehouse`, `branch_logistics`, `branch_cashier`), limitando sus permisos y el alcance de datos según su `branch_id`.

### Pasos de la Tarea
1. [x] **Actualizar blueprint.md**: Documentar la jerarquía de 3 niveles, roles y matriz de permisos.
2. [x] **Definir roles y permisos en RolesAndPermissionsSeeder**: Crear los nuevos roles (`super_admin`, `branch_manager`, `branch_warehouse`, `branch_logistics`, `branch_cashier`) y sincronizar sus permisos correspondientes.
3. [x] **Actualizar LoginAdminAction**: Permitir el acceso a administradores con roles válidos y validar asignación de sucursal para no-maestros.
4. [x] **Adaptar HandleAdminInertiaRequests**: Inyectar roles, matriz completa de permisos y datos de la sucursal activa en las props compartidas de Inertia.
5. [x] **Ajustar rutas administrativas (routes/admin.php)**: Desacoplar el middleware exclusivo `role:super_admin` reemplazándolo por permisos granulares (`role_or_permission:...`).
6. [x] **Actualizar Sidebar.vue**: Hacer dinámico el menú lateral para que cada usuario solo vea los accesos correspondientes a sus permisos y mostrar sucursal activa.
7. [x] **Validación y Pruebas**: Verificación completa del bundle Vite y consistencia de roles y autenticación.

---

## 9. Plan de Implementación Actual: Desacoplamiento por Subdominios (Fase 1: Subdominio Admin)

### Objetivo
Configurar el silo administrativo para que opere a través de un subdominio dedicado (`admin.tudominio.com` en producción, `admin.startup.test` o `admin.127.0.0.1.nip.io` en local), eliminando prefijos artificiales de ruta (`/adm/`) y logrando aislamiento total de sesiones, cookies y seguridad de red.

### Arquitectura de Enrutamiento
- **Host administrativo**: `ADMIN_DOMAIN` (ej. `admin.startup.test` o `admin.127.0.0.1.nip.io`).
- **Rutas clave del subdominio**:
  - `admin.dominio.com/login` -> Portal de login administrativo (`admin.login`).
  - `admin.dominio.com/dashboard` -> Centro de mando (`dashboard.index`).
  - `admin.dominio.com/catalog/*` -> Gestión de catálogo.
  - `admin.dominio.com/operations/*` -> Sucursales, cajas, horarios.
  - `admin.dominio.com/inventory/*` -> Stock, kardex, compras.

### Pasos de Implementación
1. [x] **Definir plan para subdominio admin en blueprint.md**: Documentar arquitectura y estrategia.
2. [x] **Configurar variables de entorno en .env y .env.example**: Introducir `APP_DOMAIN` y `ADMIN_DOMAIN`.
3. [x] **Actualizar bootstrap/app.php**: Reemplazar `prefix('adm')` por `domain(env('ADMIN_DOMAIN'))` en el silo admin.
4. [x] **Ajustar redirecciones de auth y middleware**: Adaptar `redirectGuestsTo` y `redirectUsersTo` para reconocer el host administrativo.
5. [x] **Ruta raíz de subdominio**: Añadir redirección inteligente de `/` hacia `/dashboard` o `/login` en `routes/admin.php`.
6. [x] **Validación y Pruebas**: Verificación de sintaxis, consistencia de rutas y preparación para Laravel Herd.

---

## 10. Plan de Implementación Actual: Módulo Dinámico de Personal / Operadores y Sucursales

### Objetivo
Construir el módulo integral de administración de operadores (`users/staff`), permitiendo al SuperAdmin dar de alta, editar, suspender y asignar roles y permisos dinámicos a cada usuario en la tabla `admins`, con vinculación directa y reactiva hacia sus sucursales correspondientes.

### Arquitectura Funcional
1. **Asignación Granular de Roles**:
   - Cada operador puede poseer uno o varios roles (`super_admin`, `branch_manager`, `branch_warehouse`, `branch_logistics`, `branch_cashier`).
   - Si se asigna el rol `super_admin`, el campo de sucursal es nulo/global.
   - Si se asignan roles de sucursal, el selector dinámico exige vincular la sucursal activa.
   - Múltiples gerentes por sucursal soportados.
2. **Tabla Densa de Alta Productividad**:
   - Búsqueda en tiempo real por nombre, email y teléfono.
   - Filtros instantáneos por Sucursal, Rol y Estado (Activo/Suspendido).
   - Interruptor rápido de activación/suspensión inline (`toggle-status`).
   - Indicador visual de última conexión (`last_login_at`) y estado en vivo (`last_seen_at`).
3. **Interconexión con Sucursales**:
   - En el listado y edición de sucursales, visualización de los gerentes asignados y contador de operadores activos.

### Pasos de la Tarea
1. [x] **Registrar plan en blueprint.md**: Documentar arquitectura, flujos y directrices UX.
2. [x] **Crear DTO y FormRequests**: `StaffData`, `StoreStaffRequest`, `UpdateStaffRequest`.
3. [x] **Crear Action Classes de Dominio**: `ListStaffAction`, `CreateStaffAction`, `UpdateStaffAction`, `ToggleStaffStatusAction`, `GetStaffFormOptionsAction`.
4. [x] **Crear StaffController y Rutas**: Controlador lean y rutas bajo `users/staff`.
5. [x] **Construir Vistas Vue (Inertia)**: `Index.vue` (tabla densa con filtros y métricas), `Create.vue` y `Edit.vue`.
6. [x] **Actualizar Sidebar.vue**: Agregar ícono y enlace a Personal en grupo Operativa.
7. [x] **Vincular en Sucursales**: Agregar conteo de personal y gerentes en la tabla de sucursales (`ListBranches` + `Branches/Index.vue`).
8. [x] **Compilación y Formato de Commits**: Validar build Vite y entregar los comandos de commit en texto.

### Resumen de Cambios Realizados
- **Regla Estricta de Diseño UX: Sobriedad Operativa (Sin KPIs)**:
  - Se prohíben las tarjetas de métricas o KPIs en la parte superior de las vistas. Todo el diseño debe ser sobrio, compacto y directo, optimizando el espacio vertical para visualización de registros y eliminando queries innecesarias de conteo en la base de datos.
- **Dominio Admin / Staff**:
  - DTO tipado `App\DTOs\Admin\User\StaffData` con validación estricta y sincronización de roles/permisos.
  - FormRequests `StoreStaffRequest` y `UpdateStaffRequest` con reglas condicionales para obligatoriedad de `branch_id` cuando no es `super_admin`.
  - Actions desacopladas bajo `App\Actions\Admin\Users\Staff\`:
    - `ListStaffAction`: Listado paginado de alto rendimiento con búsqueda debounce por nombre/email/teléfono, y filtros combinables por sucursal, rol y estado (sin overhead de KPIs).
    - `CreateStaffAction`: Creación segura de administradores con bcrypt y asignación Spatie.
    - `UpdateStaffAction`: Actualización de datos y contraseña opcional.
    - `ToggleStaffStatusAction`: Inversión de estado activo/suspendido con guardia contra auto-desactivación.
    - `GetStaffFormOptionsAction`: Proveedor de datos para selectores de sucursales, roles y permisos directos.
  - Controlador `App\Http\Controllers\Web\Admin\User\StaffController` con respuestas estandarizadas Inertia y redirecciones fluidas con mensajes flash.
- **Frontend Inertia / Vue 3**:
  - `Admin/Users/Staff/Index.vue`: Tabla densa y sobria con filtros rápidos, badges semánticos de roles, switch inline de estado y paginación (completamente libre de KPIs).
  - `Admin/Users/Staff/Create.vue`: Formulario reactivo con selector de roles por tarjetas interactivas, vinculación condicional de sucursal y permisos directos.
  - `Admin/Users/Staff/Edit.vue`: Edición integral de operador con prellenado reactivo.
  - `Components/Admin/Sidebar.vue`: Acceso directo a Personal (`staff.index`) bajo la categoría operativa.
- **Interconexión Dinámica con Sucursales**:
  - `ListBranches.php`: Eager-loading de operadores activos (`admins`) y filtrado de gerentes asignados (`hasRole('branch_manager')`).
  - `Admin/Operations/Branches/Index.vue`: Columna de "Personal Asignado" que muestra gerentes y conteo con enlace para prefiltrar en `staff.index`.

---

## 11. Plan de Implementación Actual: Auditoría y Correcciones del Módulo de Sucursales

### Objetivo
Corregir los fallos identificados en la auditoría del módulo de Sucursales (`operations/branches`), garantizando compatibilidad total con MariaDB en consultas espaciales GIS, sincronización rigurosa de autorización RBAC en FormRequests y robustez en la configuración inicial de horarios para nuevas sucursales.

### Pasos de la Tarea
1. [x] **Registrar auditoría en blueprint.md**: Documentar hallazgos de compatibilidad MariaDB, permisos RBAC y edge cases de horarios.
2. [x] **Compatibilidad Espacial MariaDB**: Eliminar el parámetro `'axis-order=long-lat'` en `CreateBranch.php`, `UpdateBranch.php`, `Branch.php`, `BranchQueryService.php` y `RegisterCustomerAction.php`, limitando `ST_GeomFromText` a 2 argumentos.
3. [x] **Armonización de Autorización RBAC en FormRequests**: Actualizar `authorize()` en `StoreBranchRequest`, `UpdateBranchRequest`, `UpdateBranchSchedulesRequest`, `StoreBranchScheduleExceptionRequest`, `OpenDailyCashRequest` y `CloseDailyCashRequest` para soportar permisos granulares (`manage_branches`, `manage_branch_schedules`, `manage_branch_cash`) y validar aislamiento por sucursal para operadores locales.
4. [x] **Hidratación de Horarios Semanales (7 días)**: Garantizar que `BranchScheduleController@index` entregue siempre la colección completa de 7 días (0 a 6), evitando bloqueos en la vista y errores de validación en sucursales nuevas.
5. [x] **Compilación y Verificación**: Ejecutar `npm run build` y entregar comandos de commit en texto.

### Resumen de Remediaciones Aplicadas
- **Compatibilidad Espacial 100% MariaDB**:
  - Estandarización a 2 parámetros `ST_GeomFromText(wkt, 4326)` en `CreateBranch`, `UpdateBranch`, `Branch::scopeWithinCoverage`, `BranchQueryService::getCollidingBranches` y `RegisterCustomerAction`. Se eliminó por completo `'axis-order=long-lat'`.
  - Reemplazo de funciones propietarias de MySQL 8 `ST_Latitude` y `ST_Longitude` por los estándares universales OGC/SQL-MM `ST_Y(location) as lat` y `ST_X(location) as lng` en `ListBranches` y `GetBranchForEditAction`, resolviendo el error `FUNCTION ST_Latitude does not exist` en MariaDB.
- **Alineación de Seguridad y RBAC**:
  - Los 6 FormRequests (`StoreBranchRequest`, `UpdateBranchRequest`, `UpdateBranchSchedulesRequest`, `StoreBranchScheduleExceptionRequest`, `OpenDailyCashRequest`, `CloseDailyCashRequest`) ahora permiten operar a usuarios con roles operativos y permisos asignados (`manage_branches`, `manage_branch_schedules`, `manage_branch_cash`).
  - Para usuarios no `super_admin`, se valida estrictamente que solo puedan ejecutar operaciones sobre su propia sucursal asignada (`$user->branch_id === $branchId`).
- **Resiliencia de Horarios Semanales**:
  - `BranchScheduleController@index` hidrata en memoria los 7 días de la semana (0 a 6) si no existiesen en base de datos, garantizando que sucursales recién creadas puedan configurar sus horarios sin errores de validación.

---

## 12. Plan de Implementación Actual: Auditoría, Reparación y Modernización del Ecosistema de Usuarios (Clientes y Repartidores)

### Objetivo
Auditar exhaustivamente y modernizar los submódulos de **Clientes B2C** (`/users/customers`) y **Repartidores / Drivers** (`/users/drivers`), eliminando dependencias inexistentes (`DriverResource`), corrigiendo nombres de rutas Ziggy rotas, implementando acciones ausentes (`RestoreDriverAction`), garantizando compatibilidad de paginación (`LengthAwarePaginator` con `$paginator->through()`) e implementando interfaces sobrias, densas y de alta productividad completamente libres de bloques KPI.

### Pasos de la Tarea
1. [x] **Auditoría de Dominio y Controladores**: Detección de fallos en rutas (`admin.users.drivers.*` vs `drivers.*`), recursos no existentes (`DriverResource`) y acciones faltantes (`RestoreDriverAction`).
2. [x] **Creación de Acción de Restauración de Repartidores**: Implementar `RestoreDriverAction` con sincronización de `deleted_epoch = 0`, reactivación en estado `pending` y registro en logs de auditoría si la tabla existe.
3. [x] **Refactorización de Actions de Listado**: Modernizar `GetCustomersListAction` y `GetDriversListAction` para retornar `LengthAwarePaginator` con transformaciones nativas (`through`), entregando `full_name`, contactos, sucursales y estatus sin romper la paginación ni requerir Resources externos.
4. [x] **Armonización de Controladores**: Actualizar `CustomerController` y `DriverController` para consumir las nuevas acciones, limpiar imports y manejar respuestas JSON y redirecciones con mensajes flash.
5. [x] **Modernización Frontend Customers (`Customers/Index.vue`)**:
   - Tabla densa y sobria sin métricas ni KPIs superiores.
   - Búsqueda reactiva con debounce de 300ms, filtros por sucursal y estado activo/inactivo con botón de limpieza.
   - Paginación estandarizada mediante `<Pagination :meta="customers" />`.
   - Visualización de avatar con iniciales, score de confianza dinámico y switch rápido inline de estado.
   - Modal de recuperación de cuentas eliminadas por teléfono.
6. [x] **Modernización Frontend Drivers (`Drivers/Index.vue` y `Drivers/Create.vue`)**:
   - Corrección total de llamadas de rutas a `drivers.*`.
   - Tabla densa con estatus operativo, vehículo/placa monospace, conexión en tiempo real y paginación con `<Pagination :meta="drivers" />`.
   - Drawer lateral derecho para gobernanza de estatus (aprobación, rechazo con justificación documental obligatoria y suspensión preventiva con invalidación de sesiones).
   - Drawer de recuperación de conductores dados de baja por teléfono.
   - Formulario de alta corporativa en `Create.vue` con diseño sobrio y validaciones integradas.
7. [x] **Compilación y Verificación**: Ejecutar `npm run build` confirmando 0 errores de compilación en Vue 3 y Vite.

### Resumen de Cambios Realizados
- **Dominio y Backend**:
  - `App\Actions\Admin\Users\Driver\RestoreDriverAction`: Creada para restaurar conductores con soft delete y `deleted_epoch = 0`, marcando `was_previously_deleted = true` y estatus `pending`.
  - `App\Actions\Admin\Users\Driver\GetDriversListAction`: Reescrito para usar `$paginator->through(...)` exponiendo `full_name`, `license_plate`, `vehicle_type`, `branch`, `is_online`, `last_seen_at` y fechas formateadas, eliminando la dependencia inexistente de `DriverResource`.
  - `App\Actions\Admin\Users\Customer\GetCustomersListAction`: Estandarizado a `LengthAwarePaginator` mediante `$paginator->through(...)`, exponiendo `full_name`, sucursal asignada, scoring y estado.
  - `App\Http\Controllers\Web\Admin\User\DriverController`: Limpieza de imports, retorno tipado del paginador directo y respuesta JSON limpia en `searchDeleted`.
  - `App\Http\Controllers\Web\Admin\User\CustomerController`: Retorno de `customers` paginado y listado de sucursales activas ordenadas.
- **Frontend Inertia / Vue 3**:
  - `Customers/Index.vue`: Estilo sobrio y denso sin KPIs. Input de búsqueda con debounce de 300ms, badges de scoring de confianza y estado, paginación con `<Pagination :meta="customers" />` y modal de restauración.
  - `Drivers/Index.vue`: Estilo sobrio y denso sin KPIs. Rutas corregidas a `drivers.*`, filtros combinables, badges semánticos, indicador de conexión en línea/desconectado, drawer de gobernanza de estatus y drawer de recuperación.
  - `Drivers/Create.vue`: Rutas corregidas a `drivers.store` y `drivers.index`, interfaz dividida en 2 bloques (Credenciales y Datos Logísticos) con validaciones directas.
- **Verificación**:
  - Build de Vite exitoso (`2543 modules transformed`, `built in 7.92s`) sin errores ni advertencias de sintaxis.





