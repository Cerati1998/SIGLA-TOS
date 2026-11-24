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
