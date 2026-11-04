# Modelo de datos · SIGLA 2.0 (TOS)

Este documento es la **fuente de verdad del modelo de datos**. Crece con el producto: solo tiene diagrama y diccionario de las tablas que existen o que entran en el sprint en curso. Lo que viene después queda como decisiones guardadas (sección 7), sin diagrama, y se promueve al diccionario cuando su historia entra a un sprint.

**Alcance actual:** Sprint 1 (2026-11-02 a 2026-11-06) · **Última revisión:** 2026-11-04 · **Estado:** borrador para aprobar por el usuario.

Base de partida: el ER del usuario (`SIGLA2.0.mwb`, 29 tablas), las migraciones del repo y el cuestionario de la tarea T18. Si el ER y este documento difieren, manda este documento.

## 1. Cómo se mantiene

1. Toda migración que cree o cambie una tabla actualiza este archivo **en el mismo PR**: diagrama, diccionario y estado de la tabla.
2. Al empezar un sprint, se mueven de la sección 7 al diccionario las tablas de las historias que entran, ajustadas a lo que diga su criterio de aceptación.
3. Si el código y este documento se contradicen, se avisa al usuario. No se decide en silencio.
4. Una tabla solo pasa a `en código` cuando su migración está en `main`.

### Estados de una tabla

| Estado | Significa |
| --- | --- |
| `framework` | La crea Laravel o un paquete; no se diseña aquí. |
| `en código` | Ya existe una migración en `main`. |
| `planeada (T##/H##)` | Entra en este sprint; la crea la tarea o historia indicada. |
| `siguiente` | Diseño decidido, pero es de un sprint posterior (sección 7). |
| `por decidir` | Falta una decisión del usuario (sección 8). |
| `futura` | Fuera del Product Goal actual. |

## 2. Convenciones (aplican a toda tabla nueva)

- **Nombres en inglés**, tablas en plural `snake_case`, modelos en singular. Valores guardados en inglés (`active`, `available`). Pantallas y mensajes en español.
- **Llaves:** `id()` (bigint sin signo) y `foreignId()`. El ER usaba `int`: no se respeta.
- **Marcas de tiempo:** `timestamps()` en todas las tablas. Las tablas puente puras pueden omitirlas.
- **Maestros que se desactivan:** `softDeletes()` (`deleted_at`), nunca una columna de estado. Un maestro desactivado sigue ocupando sus índices únicos: se **reactiva**, no se vuelve a crear.
- **Nulos:** `NOT NULL` por defecto. Solo es opcional lo que la historia dice que es opcional. El ER traía casi todo nulo: no se respeta.
- **Estados de proceso** (kit, dispositivo, certificación): columna `status` con un `enum` de PHP en inglés, no `softDeletes`.
- **Escrituras en varias tablas:** todo o nada, dentro de una transacción.
- **Compatibilidad:** las migraciones corren desde cero y funcionan en **MariaDB 10.6** (producción cPanel). Nunca se hace ingeniería directa del `.mwb` (su intercalación es de MySQL 8). La intercalación es la de `config/database.php` (`utf8mb4_unicode_ci`).
- **Zona horaria:** la app guarda en UTC (`config/app.php`). Lo que depende del calendario local se calcula en `America/Lima`.
- **Datos personales:** DNI, brevete y número de documento solo los ven los roles que los necesitan; nunca van en URLs ni en logs.
- **Excepción documentada:** `identities` es un catálogo fijo cargado por seeder, sin pantalla y sin `deleted_at`.

## 3. Tablas del Sprint 1

| Tabla | Épica / tarea | Estado | Nombre en el ER |
| --- | --- | --- | --- |
| `users` | E1 · H04 (T15) y T19 | `en código`; T19 la amplía | `users` |
| `passkeys` | E1 · H01 | `en código` | (no estaba en el ER) |
| `identities` | E1 · T19 | `planeada (T19)` | (nueva; catálogo SUNAT 06) |
| `branches` | E1 · T19 | `planeada (T19)` | `branches` |
| `shipping_lines` | E8 · H09 | `planeada (H09)` | `shipping_lines` |
| `providers` | E8 · H10 | `planeada (H10)` | `providers` |
| `security_device_types` | E2 · H06 | `planeada (H06)` | `security_device_types` |
| `password_reset_tokens`, `sessions` | E1 · H01/H03 | `framework` | — |
| `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | — | `framework` | — |

## 4. Diagramas

### 4.1 Usuarios, identidad y sucursales (E1)

```mermaid
erDiagram
  identities ||--o{ users : "tipo de documento"
  branches ||--o{ users : "pertenece a"
  users ||--o{ passkeys : "tiene"

  identities {
    string id PK "código SUNAT, varchar(50)"
    string description
  }
  branches {
    bigint id PK
    string code UK
    string name
  }
  users {
    bigint id PK
    string name
    string email UK
    string identity_id FK "T19"
    string document_number "T19, UK con identity_id"
    bigint branch_id FK "T19"
    datetime deleted_at "null = activo"
  }
  passkeys {
    bigint id PK
    bigint user_id FK
    string credential_id UK
  }
```

### 4.2 Maestros: navieras, proveedores y tipos de dispositivo (E8, E2)

```mermaid
erDiagram
  identities ||--o{ providers : "tipo de documento"
  users ||--o{ shipping_lines : "created_by"

  shipping_lines {
    bigint id PK
    string code UK
    string name
    bigint created_by FK
    datetime deleted_at
  }
  providers {
    bigint id PK
    string identity_id FK
    string document_number "UK con identity_id"
    string business_name
    datetime deleted_at
  }
  security_device_types {
    bigint id PK
    string name UK
    string kind "seal, bolt, tape, other"
    datetime deleted_at
  }
```

## 5. Diccionario de tablas

Notación: `UK` único · `FK` llave foránea. Todo es `NOT NULL` salvo donde dice «null».

### 5.1 `users` · `en código` (T19 la amplía)

Ya existe: `id`, `name`, `email` (UK), `email_verified_at` (null), `password`, `remember_token` (null), columnas de doble factor (null), `profile_photo_path` (null), `timestamps`, `deleted_at` (null; reemplazó a `is_active` el 2026-11-03).

T19 agrega:

| Columna | Tipo | Notas |
| --- | --- | --- |
| `identity_id` | varchar(50) FK → `identities.id` | Tipo de documento. |
| `document_number` | varchar(12) | Obligatorio. UK compuesta `(identity_id, document_number)`. |
| `branch_id` | bigint FK → `branches.id` | Obligatoria. Un usuario pertenece a **una sola** sucursal. |

Consecuencias:
- H04: el modal de crear y editar pide tipo y número de documento y sucursal.
- Se descarta `current_team_id` y la tabla puente `branches_has_users` del ER.
- H15 y H17: `identity_id` + `document_number` también sirven para reconocer al inspector que inició sesión contra `workers` (sección 7).
- Las tres columnas son obligatorias. Si en algún entorno ya hay usuarios, la migración de T19 tiene que rellenarlos antes de volverlas obligatorias (los seeders usan DNI `1` y la sucursal sembrada).

### 5.2 `passkeys` · `en código`

Creada por el starter kit. `id`, `user_id` (FK → `users`, borra en cascada), `name`, `credential_id` (UK), `credential` (json), `last_used_at` (null), `timestamps`.

### 5.3 `identities` · `planeada (T19)`

Catálogo SUNAT 06. Seeder, sin pantalla. **Sin** `deleted_at` (excepción).

| Columna | Tipo | Notas |
| --- | --- | --- |
| `id` | varchar(50) PK | Código SUNAT; no autoincremental. |
| `description` | varchar(200) | |
| `timestamps` | | |

Doce filas: `-` Sin documento · `0` DOC.TRIB. · `1` DNI · `2` Código IICL · `4` Carnet de extranjería · `6` RUC · `7` Pasaporte · `A` Cédula Diplomática · `B` DOC.IDENT.EXTRANJERO · `C` TIN · `D` IN · `E` TAM.

Reglas: en usuarios (y luego en trabajadores) se puede elegir cualquier código. Con `-` el número sigue siendo obligatorio y único.

### 5.4 `branches` · `planeada (T19)`

Se cargan por seeder, sin pantalla.

| Columna | Tipo | Notas |
| --- | --- | --- |
| `id` | bigint PK | |
| `code` | varchar(20) | UK. |
| `name` | varchar(255) | |
| `phone`, `email`, `website`, `ubigeo`, `address`, `dt_code` | varchar | null. Datos informativos del ER. |
| `timestamps`, `deleted_at` | | |

### 5.5 `shipping_lines` · `planeada (H09)`

| Columna | Tipo | Notas |
| --- | --- | --- |
| `id` | bigint PK | |
| `code` | varchar(20) | UK (criterio 2). |
| `name` | varchar(100) | |
| `created_by` | bigint FK → `users` | Ver P7. |
| `timestamps`, `deleted_at` | | Desactivar = `deleted_at`. |

### 5.6 `providers` · `planeada (H10)`

Global (sin sucursal). Solo lo que pide H10; `cuenta_cte` y `detalle_servicio` del ER quedan fuera.

| Columna | Tipo | Notas |
| --- | --- | --- |
| `id` | bigint PK | |
| `identity_id` | varchar(50) FK | Tipo de documento (RUC o DNI, según H10). |
| `document_number` | varchar(12) | UK compuesta con `identity_id` (criterio 2). |
| `business_name` | varchar(255) | ER: `razon_social`. |
| `address`, `email`, `phone` | varchar | Obligatoriedad por confirmar antes de construir (H10, criterio 1); hasta entonces, null. |
| `timestamps`, `deleted_at` | | |

### 5.7 `security_device_types` · `planeada (H06)`

| Columna | Tipo | Notas |
| --- | --- | --- |
| `id` | bigint PK | |
| `name` | varchar(100) | UK (criterio 2). |
| `kind` | varchar(20) | Enum de PHP, lista fija: `seal` (sello), `bolt` (precinto; nombre por confirmar en T20), `tape` (cinta), `other` (otro). ER: `type`. |
| `timestamps`, `deleted_at` | | Solo el admin desactiva (criterio 4). |

## 6. Cambios del ER original que ya aplican

- `int` → `bigint`; casi todo nulo → `NOT NULL`.
- Tablas descartadas: `branches_has_users`.
- Columnas descartadas: `users.current_team_id`, `providers.branch_id`, `providers.cuenta_cte`, `providers.detalle_servicio`.
- Nuevas: `users.identity_id/document_number/branch_id`, tabla `identities`.
- Renombres: `razon_social` → `business_name`, `type` → `kind` en tipos de dispositivo.

## 7. Decisiones guardadas para sprints siguientes

No se migran ahora. Cada fila se amplía (diagrama y diccionario) cuando su historia entra a un sprint. Los nombres son los decididos; los marcados «propuesta» no están confirmados.

| Tabla (nombre en el ER) | Historia | Decisiones |
| --- | --- | --- |
| `security_device_announcements` (`security_device_types_announcement`) | H07 | Se mantiene separada del lote. Propuesta: prefijo de serie en columna propia `series_prefix`; `series_start/series_end` enteros (el ER los tenía como texto); el proveedor vive solo aquí. |
| `security_device_lots` (`security_device_lots`) | H07 | Se quita `provider_id` (propuesta). `qty` → `quantity`, `date_reception` → `received_at`, `observations` → `notes`, `despatch_number` → `dispatch_number`. |
| `security_devices` (`s_device_lots_units`) | H07/H08 | `serie_number` → `serial_number` (UK). `status` guarda `available`, `in_kit`, `used`, `damaged`. Se descarta `location`. |
| `certification_sections` (`certification_criteria_items`) | H11 | `type` solo `internal`/`external`. Se agrega `sort_order`. `softDeletes`. |
| `certification_section_security_device_type` | H12 | PK compuesta `(section, device type)` + `quantity`. Las «puertas» no son un tipo: son una ubicación configurada (P1). |
| `container_types` (`container_types`) | H13 | Definida con el volcado del usuario: `code` varchar(6) UK, `iso_code` varchar(4) UK, `description`, `is_reefer`, `length/width/height` decimal(8,2), `deleted_at`. Seeder con 15 de 16 filas (se omite `20ST`/`22G0`). Sin pantalla. |
| `kit_types` (`type_kits`) | H13 | Se desactiva con `deleted_at` (sin `status`). |
| `kit_type_security_device_type` | H13 | PK compuesta + `quantity`. Los dos únicos sueltos del ER bloqueaban el uso. |
| `yard_zones` (`yard_zones`) | H26 (nueva) | Se gestiona con historia nueva, antes de H14. `branch_id`, `name`, `capacity`, `softDeletes`. |
| `kits` (`kits`) | H14 | `series_code` UK. `status`: `built`, `dispatched`, `used`. La semana no es columna: es la semana ISO de `created_at` en hora de Lima. Sin zona de patio: la elige el inspector al certificar. |
| `kit_security_device` (`kits_device_units`) | H14 | Un dispositivo solo está en un kit. |
| `workers` (`workers`) | H25 (nueva) | Recibe los kits. Propio o de proveedor (`provider_id` null si `is_own`). Con `identity_id` + `document_number` UK. **Sin llave hacia `users`:** el inspector se reconoce comparando `identity_id` + `document_number` de su usuario y del trabajador; si no coinciden, no ve kits (decisión del usuario del 2026-11-04). |
| `kit_dispatches` (`kits_dispatches`) | H15 | `receiver_id` → `workers`; quien entrega es `created_by`; se descarta `assigned_by`. |
| `kit_dispatch_items` (`kit_dispatches_assigned_units`) | H15 | Lleva `id`; `kit_id` UK. |
| `carriers`, `drivers`, `vehicles` (no estaban en el ER) | H21–H23 | Catálogos independientes, `softDeletes`. Identificación por `identity_id` + `document_number` (RUC/DNI), brevete y placa. Vehículo: `category` N1, N2, N3, O1, O2, O3, O4 y `type` `tractor`, `flatbed_semitrailer`, `container_semitrailer` (listas fijas), más `needs_review`. |
| `security_certifications` (`security_certifications`) | H17 | Una por solicitud de retiro; la reinspección la reabre. `source` `api`/`web`: con `api`, la naviera y la solicitud se copian como texto y código (sin FK); con `web`, saldrá FK desde la futura tabla de solicitudes. `yard_zone_id` la elige el inspector al iniciar. `started_at`/`closed_at` pasan a datetime. |
| `certification_photos` (`external_/internal_certification_photos`) | H18 | `por decidir` (E5 en revisión). Propuesta: una sola tabla. |
| `device_dispatches`, `device_dispatches_units` | E6 | `por decidir` hasta revisar E6. |
| `company`, `affectations`, `clients`, `containers` | — | `futura`. `containers` trae ids sueltos sin tabla (`reefer_technolgy_id`, `reefer_machine_id`, `warehouse_id`, `own_line_id`). |

Tablas de roles y permisos: se definen al aprobar H24.

## 8. Pendientes por decidir

| # | Tema | Detalle |
| --- | --- | --- |
| P1 | «Puertas» | Dónde se guarda la ubicación configurada que dice a qué punto va cada tipo de dispositivo del kit. Se resuelve con H12. |
| P2 | Anuncios y lotes | Confirmar prefijo en columna propia, series numéricas y proveedor solo en el anuncio. Se cierra al entrar H07. |
| P3 | Fotos | Una tabla o dos. Depende de la revisión de H18. |
| P4 | Certificación | Valores de `status` y si remolcador y semirremolque son obligatorios. |
| P5 | Glosario (T20) | Nombres en inglés de precinto/sello/cinta, funda, gate out, remolcador y semirremolque. |
| P6 | Llave de `identities` | Se respeta `id` varchar(50) con el código SUNAT. Alternativa: `id` numérico y `code` aparte. |
| P7 | `created_by` y seeders | Las filas cargadas por seeder o migradas de SIGLA 1.0 no tienen usuario autor: definir si `created_by` admite null o se siembra un usuario del sistema. Afecta a `shipping_lines` en este sprint. |
| P8 | Obligatoriedad en proveedores | Qué datos de H10 son obligatorios (H10, criterio 1). |
| P9 | Largo de `document_number` | Se eligió varchar(12), suficiente para DNI y RUC. Comprobar antes de T19 si alcanza para los demás códigos del catálogo (pasaporte, cédula, TIN). |