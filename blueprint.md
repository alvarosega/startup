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



