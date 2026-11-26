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
  - `App\Actions\Admin\Users\Driver\RestoreDriverAction`: Creada para restaurar conductores con soft delete y `deleted_epoch = 0`, marcando `was_previously_deleted = true` y estatus `pending`. Corregido byte inicial (eliminado newline antes de `<?php`), resolviendo el error `FatalError: strict_types declaration must be the very first statement`.
  - Escaneo exhaustivo a nivel de bytes en todos los archivos `.php` de `app/`, `routes/`, `config/`, `bootstrap/` y `database/` confirmando 0 archivos con bytes espurios o previos a `<?php`.
  - `App\Actions\Admin\Users\Driver\GetDriversListAction`: Reescrito para usar `$paginator->through(...)` exponiendo `full_name`, `license_plate`, `vehicle_type`, `branch`, `is_online`, `last_seen_at` y fechas formateadas, eliminando la dependencia inexistente de `DriverResource`. Corregida conversión del enum respaldado `DriverStatus` a string mediante `is_object($driver->status) ? $driver->status->value : (string) $driver->status`, resolviendo el error de casteo en PHP 8.4.
  - Salvaguarda preventiva en `ChangeDriverStatusAction`, `StoreDriverAction`, `ChangeCustomerStatusAction` y `StoreCustomerAction` con verificación `Schema::hasTable('audit_logs')` para evitar excepciones si la tabla no existe en la base de datos remota.
  - `App\Actions\Admin\Users\Customer\GetCustomersListAction`: Estandarizado a `LengthAwarePaginator` mediante `$paginator->through(...)`, exponiendo `full_name`, sucursal asignada, scoring y estado.
  - `App\Http\Controllers\Web\Admin\User\DriverController`: Limpieza de imports, retorno tipado del paginador directo y respuesta JSON limpia en `searchDeleted`.
  - `App\Http\Controllers\Web\Admin\User\CustomerController`: Retorno de `customers` paginado y listado de sucursales activas ordenadas.
- **Frontend Inertia / Vue 3 y Unificación Modular de Usuarios**:
  - **Unificación Modular en Barra Lateral (`Sidebar.vue`)**: Se condensaron los enlaces dispersos de Personal, Clientes y Repartidores en un único acceso unificado: **"Usuarios"** (`icon: 'manage_accounts'`), con coincidencia de patrones reactiva `['staff.*', 'customers.*', 'drivers.*']`.
  - **Componente de Navegación por Pestañas (`UserNavTabs.vue`)**: Creado componente transversal con soporte para RBAC, permitiendo alternar instantáneamente entre **Personal y Operadores**, **Clientes (B2C)** y **Repartidores** dentro de la misma interfaz.
  - Integración de `<UserNavTabs />` en `Staff/Index.vue`, `Customers/Index.vue` y `Drivers/Index.vue`.
  - `Customers/Index.vue`: Estilo sobrio y denso sin KPIs. Input de búsqueda con debounce de 300ms, badges de scoring de confianza y estado, paginación con `<Pagination :meta="customers" />` y modal de restauración.
  - `Drivers/Index.vue`: Estilo sobrio y denso sin KPIs. Rutas corregidas a `drivers.*`, filtros combinables, badges semánticos, indicador de conexión en línea/desconectado, drawer de gobernanza de estatus y drawer de recuperación.
  - `Drivers/Create.vue`: Rutas corregidas a `drivers.store` y `drivers.index`, interfaz dividida en 2 bloques (Credenciales y Datos Logísticos) con validaciones directas.
- **Verificación**:
  - Build de Vite exitoso (`2544 modules transformed`, `built in 7.10s`) sin errores ni advertencias de sintaxis.

---

## 13. Plan de Implementación: Auditoría, Saneamiento y Unificación del Ecosistema de Catálogo y Proveedores

### Objetivo
Auditar exhaustivamente, sanear y unificar la experiencia de usuario y arquitectura del módulo de **Catálogo** (`/catalog/products`, `/catalog/categories`, `/catalog/brands`) y **Proveedores** (`/operations/providers`). Consolidar el catálogo bajo una navegación transversal por pestañas (`CatalogNavTabs`) y una sola entrada unificada en la barra lateral; preservar Proveedores como módulo operativo independiente; erradicar la sobrecarga de consultas SQL de KPIs innecesarios (`GetProductStatsAction`, `GetBrandStatsAction`); subsanar errores en JavaScript en el acordeón de SKUs de productos; y estandarizar la paginación con `LengthAwarePaginator` y `<Pagination :meta="..." />` en Marcas y Categorías.

### Pasos de la Tarea
1. [x] **Arquitectura y Navegación Transversal (`CatalogNavTabs.vue`)**:
   - Crear componente de navegación de pestañas superiores para **Productos y SKUs** (`admin.catalog.products.*`), **Categorías** (`admin.catalog.categories.*`) y **Marcas Comerciales** (`admin.catalog.brands.*`).
   - Mantener **Proveedores** (`admin.operations.providers.*`) separado como acceso autónomo en el grupo "Operaciones" de la barra lateral con ícono `factory`.
   - Unificar la entrada de Catálogo en `Sidebar.vue` con ícono `category` y soporte para coincidencia de rutas agrupadas (`['admin.catalog.products.*', 'admin.catalog.categories.*', 'admin.catalog.brands.*']`).
2. [x] **Saneamiento y Optimización de Productos (`Products/Index.vue` & `ProductController.php`)**:
   - Eliminar `GetProductStatsAction` y las 4 consultas agregadas `COUNT(*)` en cada carga de listado.
   - Purgar los 4 bloques KPI superiores de `Products/Index.vue` manteniendo un diseño denso, sobrio y operativo.
   - Corregir el bug de `ReferenceError` en `toggleProductSkus`: `expandedProducts.value.filter(prodId => prodId !== id)` en lugar del identificador no definido `rowId`.
   - Declarar cursores computados `prev` y `next` para la navegación por cursor.
   - Incorporar búsqueda reactiva con debounce de 300ms (`handleSearchInput`).
3. [x] **Refactorización de Marcas (`BrandController.php`, `ListBrandsAction.php` & `Brands/Index.vue`)**:
   - Eliminar `GetBrandStatsAction` del controlador y purgar `stats` de las props en frontend.
   - Refactorizar `ListBrandsAction` para retornar directamente `LengthAwarePaginator` mediante `$paginator->through(...)`, exponiendo datos planos y links canónicos.
   - Integrar `<CatalogNavTabs />` y sustituir el bloque defectuoso de paginación por el componente estándar `<Pagination :meta="brands" />`.
   - Incorporar búsqueda reactiva con debounce de 300ms.
4. [x] **Refactorización de Categorías (`ListCategoriesAction.php` & `Categories/Index.vue`)**:
   - Adaptar `ListCategoriesAction` para usar `$paginator->through(...)` en la colección de categorías.
   - Integrar `<CatalogNavTabs />` en el encabezado de `Categories/Index.vue`.
   - Incorporar el componente de paginación `<Pagination :meta="categories" />` bajo la tabla de categorías.
   - Incorporar búsqueda reactiva con debounce de 300ms.
5. [x] **Auditoría y Afinado de Proveedores (`Providers/Index.vue`)**:
   - Confirmar preservación como módulo independiente y autónomo.
   - Agregar búsqueda reactiva con debounce de 300ms (`handleSearchInput`).
   - Mantener tabla densa con códigos, NIT, datos de contacto, condiciones comerciales y paginación por cursores.
6. [x] **Compilación y Verificación**:
   - Ejecutar `npm run build` confirmando 0 errores de compilación y empaquetado Vite.

### Resumen de Cambios Realizados
- **Backend (PHP / Laravel)**:
  - `app/Http/Controllers/Web/Admin/Catalog/ProductController.php`: Eliminada inyección y ejecución de `GetProductStatsAction`. Se ahorran 4 consultas pesadas por petición HTTP.
  - `app/Http/Controllers/Web/Admin/Catalog/BrandController.php`: Eliminada inyección y ejecución de `GetBrandStatsAction`.
  - `app/Actions/Admin/Catalog/Brand/ListBrandsAction.php`: Tipado de retorno `LengthAwarePaginator`, usando `$paginator->through(...)` con mapeo de proveedores, categorías y zonas de mercado, asegurando compatibilidad total con la estructura esperada por `Pagination.vue`.
  - `app/Actions/Admin/Catalog/Category/ListCategoriesAction.php`: Paginación mediante `$paginator->through(...)` manteniendo la evaluación diferida (`closure`) de SKUs por góndola sin sobrecargar el listado inicial.
- **Frontend (Vue 3 / Inertia)**:
  - `resources/js/Components/Admin/Catalog/CatalogNavTabs.vue`: Creado con diseño sobrio y navegación fluida entre Productos, Categorías y Marcas.
  - `resources/js/Components/Admin/Sidebar.vue`: Acceso unificado "Catálogo" con coincidencia multifactorial de rutas y enlace directo a Productos; Proveedores conservado con acceso directo bajo Operaciones.
  - `resources/js/Pages/Admin/Catalog/Products/Index.vue`: KPIs purgados, pestañas transversales añadidas, acordeón de SKUs corregido (`prodId !== id`), cursores `prev`/`next` declarados y búsqueda reactiva con debounce.
  - `resources/js/Pages/Admin/Catalog/Brands/Index.vue`: Pestañas transversales, props limpias sin `stats`, debounce en búsqueda y paginación estandarizada `<Pagination :meta="brands" />`.
  - `resources/js/Pages/Admin/Catalog/Categories/Index.vue`: Pestañas transversales, debounce en búsqueda y paginación estandarizada `<Pagination :meta="categories" />`.
  - `resources/js/Pages/Admin/Operations/Providers/Index.vue`: Búsqueda reactiva con debounce de 300ms y tabla densa operativa.
- **Verificación**:
  - Compilación exitosa en Vite (`2545 modules transformed`, `built in 7.27s`) sin advertencias ni errores.

---

## 14. Plan de Implementación: Auditoría, Corrección y Navegación del Ecosistema de Inventarios y Stock

### Objetivo
Auditar exhaustivamente, corregir errores fatales latentes en lógica de contingencia de lotes (reserva de seguridad y aislamiento a cuarentena), reactivar las búsquedas deshabilitadas en backend (Precios, Compras y Bajas), dotar de filtrado por sucursales a los balances globales de stock y dotar al ecosistema de una barra de navegación transversal por pestañas (`InventoryNavTabs`) con inputs de búsqueda reactivos con debounce de 300ms, preservando los accesos directos independientes en la barra lateral conforme a las directivas del usuario.

### Pasos de la Tarea
1. [x] **Subsanación de Errores Fatales en Acciones de Lotes**:
   - `TransferToSafetyAction`: Corregir lectura de `branch_id` y `sku_id` directamente del modelo `$lot` en lugar de propiedades inexistentes en `$dto`.
   - `IsolateToQuarantineAction`: Reescribir la lógica de aislamiento y liberación para alternar el flag `is_quarantine`, registrar el movimiento respectivo (`BLOCK_QUARANTINE` o `RELEASE_QUARANTINE`) y actualizar el acumulador `total_quarantine` en `InventoryBalance` sin descontar erróneamente la existencia del lote ni llamar a campos inexistentes en `$dto`.
   - `TransferSafetyRequest` e `IsolateQuarantineRequest`: Armonizar autorización RBAC admitiendo permisos `manage_inventory` además de `super_admin`.
2. [x] **Activación de Búsquedas en Backend**:
   - `ListPricesAction`: Implementar filtro de búsqueda por coincidencia de SKU, producto o nombre de sucursal.
   - `ListPurchasesAction`: Implementar filtro por número de documento, razón social/comercial de proveedor, sucursal o SKU/producto.
   - `ListRemovalsAction`: Implementar filtro por código de acta de baja, motivo, notas, sucursal o SKU/producto.
3. [x] **Filtrado por Sucursal en Balances de Existencias**:
   - `InventoryController@index`: Exponer colección de sucursales activas ordenadas por nombre e inyectar `branch_id` en los filtros de Inertia.
   - `ListInventoryBalancesAction`: Añadir discriminador `when($branchId, fn($q, $id) => $q->where('branch_id', $id))` y búsqueda compuesta por nombre de sucursal.
   - `Stock/Index.vue`: Incorporar selector de sucursales con reactividad inmediata y preservación de cursores de paginación.
4. [x] **Navegación Unificada por Pestañas y Debounce Reactivo**:
   - Crear componente [InventoryNavTabs.vue](file:///c:/Users/SEGA/Desktop/startup/resources/js/Components/Admin/Inventory/InventoryNavTabs.vue) integrando:
     - **Balances y Stock** (`admin.inventory.*`)
     - **Ingresos y Compras** (`admin.purchases.*`)
     - **Mermas y Bajas** (`admin.removals.*`)
     - **Tarifas y Precios** (`admin.prices.*`)
   - Integrar `<InventoryNavTabs />` en las cuatro vistas maestras de inventario.
   - Implementar debounce de 300ms (`handleSearchInput`) en todos los inputs de búsqueda de los 4 módulos.
5. [x] **Compilación y Verificación**:
   - Verificar integridad de cabeceras en todos los archivos `.php` modificados.
   - Ejecutar `npm run build` confirmando 0 errores de compilación (`2546 modules transformed`, `built in 6.92s`).

### Resumen de Cambios Realizados
- **Backend (PHP / Laravel)**:
  - `app/Http/Requests/Admin/Inventory/TransferSafetyRequest.php`: Autorización para `super_admin` o permiso `manage_inventory`.
  - `app/Http/Requests/Admin/Inventory/IsolateQuarantineRequest.php`: Autorización para `super_admin` o permiso `manage_inventory`.
  - `app/Actions/Admin/Inventory/Stock/TransferToSafetyAction.php`: Corregido acceso a `$lot->branch_id` y `$lot->sku_id`.
  - `app/Actions/Admin/Inventory/Stock/IsolateToQuarantineAction.php`: Lógica completa de aislamiento (`BLOCK_QUARANTINE`) y liberación (`RELEASE_QUARANTINE`) con ajuste de `total_quarantine` en `InventoryBalance`.
  - `app/Actions/Admin/Inventory/Price/ListPricesAction.php`: Búsqueda reactiva activada.
  - `app/Actions/Admin/Inventory/Purchase/ListPurchasesAction.php`: Búsqueda reactiva activada.
  - `app/Actions/Admin/Inventory/Removal/ListRemovalsAction.php`: Búsqueda reactiva activada.
  - `app/Actions/Admin/Inventory/Stock/ListInventoryBalancesAction.php`: Filtro por `branch_id` integrado.
  - `app/Http/Controllers/Web/Admin/Inventory/InventoryController.php`: Suministro de `branches` y filtro `branch_id`.
- **Frontend (Vue 3 / Inertia)**:
  - `resources/js/Components/Admin/Inventory/InventoryNavTabs.vue`: Componente creado con permisos RBAC y navegación fluida entre los 4 submódulos de stock.
  - `resources/js/Pages/Admin/Inventory/Stock/Index.vue`: Pestañas superiores, selector de sucursales, búsqueda con debounce de 300ms y cursor pagination con sucursal.
  - `resources/js/Pages/Admin/Inventory/Prices/Index.vue`: Pestañas superiores, búsqueda con debounce de 300ms y disposición sobria.
  - `resources/js/Pages/Admin/Inventory/Purchases/Index.vue`: Pestañas superiores y búsqueda con debounce de 300ms.
  - `resources/js/Pages/Admin/Inventory/Removals/Index.vue`: Pestañas superiores y búsqueda con debounce de 300ms.
- **Verificación**:
  - Compilación exitosa en Vite (`2546 modules transformed`, `built in 6.92s`) sin advertencias ni errores.

---

## 15. Rediseño Root Apple Glass Minimalista y Aislamiento Estricto de Admin

### Objetivo y Contexto
Auditar, aislar físicamente y rediseñar por completo la raíz visual del silo administrativo (`admin.startup.test`) bajo la estética Apple Minimalist con efecto Glassmorphism auténtico (`backdrop-blur`, bordes translúcidos con micro-relieve, radios squircle y lienzo ambiental difuso), garantizando un desacople total entre el módulo Admin y los módulos de Clientes (B2C) y Repartidores (Driver).

### Decisiones Arquitectónicas y Estilísticas
1. **Aislamiento Físico de Estilos (`resources/css/admin.css`)**:
   - Se creó un archivo CSS exclusivo para la administración, compilado por separado en `vite.config.js` y consumido únicamente por `resources/views/admin.blade.php`.
   - Se removieron todas las definiciones de `[data-context="admin"]` y estilos de tablas/inputs administrativos de `resources/css/app.css` para evitar colisiones con el front B2C o el app de conductores.
2. **Sistema de Diseño Apple Glass (`admin.css`)**:
   - **Tipografía**: Pila nativa Apple (`-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", Inter, sans-serif`) con tracking refinado (`letter-spacing: -0.015em`).
   - **Squircles**: Radios curvados Apple de 8px a 22px (`--radius-sm: 8px`, `--radius-md: 12px`, `--radius-lg: 16px`, `--radius-xl: 22px`).
   - **Efecto Glassmorphism**: Utilidades `.glass-panel`, `.glass-card`, `.glass-dock`, `.glass-header` con `backdrop-blur-xl/2xl`, fondos translúcidos al 70-80% y bordes finos `rgba(255,255,255,0.4)` / `rgba(255,255,255,0.08)`.
   - **Sombras Multicapa Apple**: `--shadow-glass` y `--shadow-glass-elevated` para generar elevación táctil limpia sin saturación oscura.
3. **Lienzo Ambiental Refractivo (`AdminLayout.vue`)**:
   - Inyección de orbes de gradiente ambiental fijas (`blur-[150px]` a `blur-[170px]`) detrás del contenido, permitiendo que el `backdrop-blur` tenga contraste lumínico real para refractar tanto en tema claro como oscuro.
   - Header flotante `.glass-header` con acento sutil, insignia de sucursal en squircle y slot de acciones.
4. **Dock Lateral Apple Squircle (`Sidebar.vue` y `SidebarLink.vue`)**:
   - Dock de navegación flotante translúcido con desenfoque de 28px (`backdrop-blur-28px`), isotipo "DU" en contenedor squircle con borde brillante y micro-glare.
   - Íconos de navegación en squircles de 40x40px (`rounded-xl`), con estado activo de fondo suave (`bg-primary/12`), micro-borde (`border-primary/25`) y píldora luminosa indicadora vertical.
   - Tooltips flotantes estilo cápsula Apple (`rounded-full`) y soporte móvil en barra inferior con modal glass bottom sheet.

### Resumen de Cambios Realizados
- **Configuración y Vistas Base**:
  - `vite.config.js`: Incorporado `'resources/css/admin.css'` a la matriz de inputs.
  - `resources/views/admin.blade.php`: Reemplazado `app.css` por `admin.css`.
  - `resources/css/app.css`: Eliminadas 145 líneas de tokens y utilidades de admin.
  - `resources/css/admin.css`: Creada hoja de estilo dedicada con tokens y utilidades Apple Glass.
- **Componentes Vue**:
  - `resources/js/Layouts/AdminLayout.vue`: Rediseño completo con orbes ambientales y header translúcido.
  - `resources/js/Components/Admin/Sidebar.vue`: Rediseño a Apple Dock translúcido con squircles y cápsula tooltip.
  - `resources/js/Components/Admin/SidebarLink.vue`: Squircles Apple, glifos rellenos al activar e indicador luminoso.
- **Verificación**:
  - Compilación exitosa en Vite (`admin.css: 159.03 kB`, `app.css: 149.59 kB`, `2547 modules transformed`).



