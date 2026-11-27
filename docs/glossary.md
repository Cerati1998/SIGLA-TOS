# Glosario · SIGLA 2.0 (TOS)

Registra el único nombre en inglés de cada término del negocio sin traducción obvia. Antes de crear un nombre en el código, consulta esta tabla; los términos nuevos se agregan cuando el usuario los aprueba.

| Término (español) | Nombre en el código (inglés) | Notas |
| --- | --- | --- |
| Sello | `seal` | Tipo de dispositivo de seguridad (H06). |
| Precinto | `container_seal` | Sello que se usa en contenedores. En H06 es un tipo aparte de `seal`, por decisión del usuario. |
| Cinta | `tape` | Tipo de dispositivo de seguridad (H06). |
| Funda | `pouch` | Bolsa del kit, con código de barras (H14). Desde T76 es un dispositivo de seguridad de la categoría `pouch` (`kind` del tipo de dispositivo): llega en un lote de su naviera y es el depósito del kit. |
| Formato (de inspección) | `inspection_sheet` | Formato de inspección que trae el kit, con su código de barras. Se guarda como `inspection_sheet_code` en la certificación (H17, aprobado por el usuario el 2026-11-17). |
| Gate out | `gate_out` | Salida de la unidad del depósito. En SIGLA 1.0 está la solicitud de retiro. |
| Remolcador | `tractor` | Unidad que jala el semirremolque (H23). |
| Semirremolque | `semi_trailer` | Unidad que lleva la carga (H23). |
| Tipo de documento | `identity` | Tabla `identities` y columna `identity_id`, catálogo SUNAT 06. |
| Tipo de dispositivo de seguridad | `security_device_type` | Tabla `security_device_types` (H06). Su clase (sello, precinto, cinta, funda u otro) es la columna `kind`. |
| Anuncio de ingreso de lotes | `security_device_announcement` | Recepción de una guía de un proveedor, para una naviera, con uno o más lotes (H37). En pantalla se dice siempre «anuncio de ingreso de lotes», para no confundirlo con otros anuncios de ingreso (de contenedores, de unidades). Antes de H37 era el origen de un solo lote. |
| Lote | `security_device_lot` | Dispositivos de seguridad de un mismo tipo y una misma serie, dentro de un anuncio de ingreso de lotes (H07, H37). |
| Dispositivo de seguridad | `security_device` | Cada unidad con su serie única y su estado (H07). |
| Inicio de serie | `series_prefix` | Letras y números con que empieza la serie, por ejemplo GSW (H07). |
| Final de serie | `series_suffix` | Letras opcionales con que termina la serie, por ejemplo la A de GWF00100A (H07). |
| Correlativo | `series_start`, `series_end` | Número que, con el prefijo de la serie y los ceros, forma la serie de un dispositivo (H07). |
| Guía (de remisión) | `dispatch_number` | Número de la guía con que llegó el lote (H07). |
| Solicitud de retiro | `gate_out_request` | Solicitud de SIGLA 1.0 que se consulta por booking y contenedor (H05). Su número es `reference`. |
| Cliente (operador) | `client` | Operador de la solicitud de retiro (H05). |
| Embarcador | `shipper` | Dueño de la carga, cliente de la naviera (H05). |
| Brevete | `license` | Licencia de conducir. En los conductores locales es el DNI con una letra delante (H05). |
| Carrocería | `body` | Tipo de carrocería del vehículo que trae SIGLA 1.0, como REMOLCADOR o PLATAFORMA (H05). |
| Activado para despacho | `activated_at`, `activated_by` | Marca de un dispositivo de seguridad, aparte de su estado, que se pone cuando la naviera confirma su activación: cuándo y quién lo activó (H29). |
| A granel | `bulk` | Despacho de dispositivos sueltos, sin kit (H30). |
| Asignación interna o externa | `assignment` (`internal`, `external`) | Lado al que va un dispositivo de un tipo que se reparte, como la cinta de inspección (T55). En un kit, lo da el campo donde se escanea (H14). |
| Asignación | `certification` | Vincular una solicitud de retiro con su kit crea la certificación; no hay tabla aparte (H17, aprobado por el usuario el 2026-11-20). |
| Inspección interna o externa | `inspection` (`internal`, `external`) | Las secciones de un tipo de una certificación, que se fotografían en cualquier orden o en paralelo. Usa el tipo de la sección, `CertificationSectionType` (H18, aprobado por el usuario el 2026-11-20). |
| Sección de una certificación | `certification_step` | Copia fija de una sección del catálogo en la certificación, con los dispositivos del kit colocados en ella (H18, aprobado por el usuario el 2026-11-20). |
| Foto (de una sección) | `certification_photo` | Foto de un paso, con su miniatura (H18, aprobado por el usuario el 2026-11-20). |
| Reapertura | `certification_reopening` | Volver a abrir una certificación cerrada, con su motivo (H19, aprobado por el usuario el 2026-11-20). |
| Sección de checkpoint | `checkpoint` | Tercer tipo de sección del catálogo, junto a interna y externa, en `CertificationSectionType`. No lleva dispositivos propios (H33, aprobado por el usuario el 2026-11-24). |
| Qué pide el paso (del checkpoint) | `checkpoint_kind` (`photos`, `verification`) | Si el paso pide fotos o verifica series. Enum `CheckpointSectionKind` (H33, aprobado por el usuario el 2026-11-24). |
| Secciones que verifica | `certification_section_verifications` | Secciones internas o externas cuyas series muestra un paso de verificación. Relación `verifiedSections` (H33, aprobado por el usuario el 2026-11-24). |
| Checkpoint | `checkpoint` | Tabla `checkpoints`. Uno por certificación: nace la primera vez que alguien abre la unidad y guarda la foto del contenedor, las observaciones y quién lo cerró y cuándo (H34, aprobado por el usuario el 2026-11-24). |
| Paso del checkpoint | `checkpoint_step` | Copia de una sección de checkpoint del catálogo en el checkpoint, con su orden y lo que pide, fotos o verificación (H34, aprobado por el usuario el 2026-11-24). |
| Foto del checkpoint | `checkpoint_photo` | Foto de un paso de fotos, o foto extra del último paso, sin paso, con quién la subió y cuándo (H34, aprobado por el usuario el 2026-11-24). |
| Marca (de una serie) | `checkpoint_mark` (`is_correct`) | Correcta o incorrecta, para cada serie de un paso de verificación, con quién la marcó y cuándo (H34, aprobado por el usuario el 2026-11-24). |
| Foto del contenedor | `container_photo_path` | Columnas del checkpoint con la foto opcional del contenedor, su miniatura, quién la tomó y cuándo. No lee el número (H34, aprobado por el usuario el 2026-11-24). |
| Observaciones (del checkpoint) | `observations` | Texto opcional del último paso del checkpoint (H34, aprobado por el usuario el 2026-11-24). |
| Reinspección | `reinspection` | Tabla `reinspections`. La inicia el checkpoint con un motivo cuando hay series incorrectas; guarda quién la inició y la cerró, y cuándo. Un checkpoint puede tener varias (H35, aprobado por el usuario el 2026-11-26). |
| Dispositivo a reemplazar (de la reinspección) | `reinspection_device` | Tabla `reinspection_devices`. Cada serie incorrecta de una reinspección, con la sección de la certificación donde está y su reemplazo cuando se hace (H35, aprobado por el usuario el 2026-11-26). |
| Sección reabierta | `reopened step` | La sección de la certificación donde quedó una serie incorrecta. No es una tabla: sale de los dispositivos a reemplazar. `Reinspection::reopenedStepIds()` (H35, aprobado por el usuario el 2026-11-26). |
| En reinspección (estado) | `in_reinspection` | Estado de la certificación en Asignaciones mientras tiene una reinspección abierta. Se calcula, no se guarda. `CertificationStatus::InReinspection` (H35, aprobado por el usuario el 2026-11-26). |
| Serie reemplazada (en el checkpoint) | `checkpoint_marks.reinspection_id` | La marca de la serie nueva que trajo una reinspección: la que verifica la segunda vuelta (H35, aprobado por el usuario el 2026-11-26). |
| Marca superada | `checkpoint_marks.superseded_by_reinspection_id` | La marca incorrecta de la serie que salió. Se conserva, pero ya no cuenta para cerrar (H35, aprobado por el usuario el 2026-11-26). |
| Iniciado por (del checkpoint) | `checkpoints.started_by` | Quién abrió la unidad por primera vez en «Registrar checkpoint», cuando nace el checkpoint (H36, aprobado por el usuario el 2026-11-26). |
| Estado del checkpoint | `CheckpointStatus` (`in_progress`, `in_reinspection`, `closed`) | En proceso, En reinspección o Cerrado. Se calcula, no se guarda (H36, aprobado por el usuario el 2026-11-26). Lo empezado y sin terminar se llama «En proceso» en todo el sistema, nunca «A medias» (decisión del usuario del 2026-11-26). |
| Dispositivo extra | `extra device` | Dispositivo de un despacho a granel con motivo Operación, enlazado al kit de una certificación, usado o sin usar. Consulta `Certification::extraDevices()` (H38, aprobado por el usuario el 2026-11-24). |
| Reemplazo (de un dispositivo) | `security_device_replacement` | Cambio de un dispositivo del kit, ya colocado en una sección, por un dispositivo extra del mismo tipo, con su motivo (H38, aprobado por el usuario el 2026-11-24). |
| Modo de despacho | `dispatch_mode` (`random`, `linear`) | Si los dispositivos de una naviera salen en cualquier orden (Aleatorio) o en orden de correlativo, sin saltarse ninguno (Lineal), al armar un kit y al despachar a granel. Enum `DispatchMode` (T82, pedido por el usuario el 2026-11-25). |
| Modelo de dispositivo GPS | `gps_device_model` | Tabla `gps_device_models`, modelo `GpsDeviceModel`: el modelo de un candado, una cerradura o un GPS portátil, como Hunter Block (H39, aprobado por el usuario el 2026-11-27). |
| Marca (del modelo GPS) | `brand_id` | Un proveedor (`providers`); la relación es `brand()` (H39, aprobado por el usuario el 2026-11-27). |
| Categoría (del modelo GPS) | `category` | Enum `GpsDeviceCategory`: `container_lock` (candado para contenedor), `door_lock` (cerradura de puerta), `light_vehicle_lock` (candado para unidad liviana), `portable_lock` (candado portátil), `portable_tracker` (GPS portátil) y `hopper_control` (control de tolva). Lista fija, sin pantalla (H39, aprobado por el usuario el 2026-11-27). |
| Apertura (del modelo GPS) | `opening_type` | Enum `GpsOpeningType`: `remote` (remota), `manual` (manual) y `none` (sin apertura) (H39, aprobado por el usuario el 2026-11-27). |
| Código de producto y código de tipo (del proveedor de monitoreo) | `monitoring_product_code`, `monitoring_device_type_code` | Los que reporta la plataforma de monitoreo por cada equipo: `IdProducto` e `IdTipoDispositivo` en 24HM (H39, aprobado por el usuario el 2026-11-27). |
| Dispositivo GPS | `gps_device` | Tabla `gps_devices`, modelo `GpsDevice`: cada candado o GPS físico, como HBR140 (H40, aprobado por el usuario el 2026-11-27). |
| Código (del dispositivo GPS) | `code` | El que lleva el equipo y el `Alias` de 24HM; se guarda en mayúsculas (H40, aprobado por el usuario el 2026-11-27). |
| ID de 24HM | `monitoring_asset_id` | El `IdActivo` del dispositivo en 24HM (H40, aprobado por el usuario el 2026-11-27). |
| Propiedad (del dispositivo GPS) | `ownership` | Enum `GpsDeviceOwnership`: `own` (propio) y `rented` (alquilado) (H40, aprobado por el usuario el 2026-11-27). |
| Proveedor del alquiler | `rental_provider_id` | Un proveedor (`providers`); la relación es `rentalProvider()` (H40, aprobado por el usuario el 2026-11-27). |
| Estado (del dispositivo GPS) | `status` | Enum `GpsDeviceStatus`, por ahora solo `available` (disponible). Desactivado es `deleted_at` (H40, aprobado por el usuario el 2026-11-27). |
| Foto de referencia | `photo_path`, `thumbnail_path` | La foto del dispositivo GPS y su miniatura, en el disco privado (H40, aprobado por el usuario el 2026-11-27). |
| Último reporte (de 24HM) | `last_reported_at`, `last_latitude`, `last_longitude`, `last_address`, `service_status`, `operational_status`, `supply_voltage` | Lo que 24HM dice del dispositivo GPS en su último reporte: fecha y hora (UTC), posición, dirección, estados y voltaje (H42, aprobado por el usuario el 2026-11-27). |
| Última verificación | `last_checked_at` | Cuándo se consultó a 24HM por última vez con éxito (H42, aprobado por el usuario el 2026-11-27). |
| Conectividad | `GpsConnectivity` | Enum: `connected` (Conectado, reportó hace menos de 24 horas), `no_signal` (Sin señal) y `unchecked` (Sin verificar) (H42, aprobado por el usuario el 2026-11-27). |
| Cliente de 24HM | `Gps24hmClient`, `MonitoringReport` | La clase que habla con el servicio SOAP de 24HM y el último reporte que devuelve (H42, aprobado por el usuario el 2026-11-27). |
