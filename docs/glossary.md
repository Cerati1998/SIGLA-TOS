# Glosario · SIGLA 2.0 (TOS)

Registra el único nombre en inglés de cada término del negocio sin traducción obvia. Antes de crear un nombre en el código, consulta esta tabla; los términos nuevos se agregan cuando el usuario los aprueba.

| Término (español) | Nombre en el código (inglés) | Notas |
| --- | --- | --- |
| Sello | `seal` | Tipo de dispositivo de seguridad (H06). |
| Precinto | `container_seal` | Sello que se usa en contenedores. En H06 es un tipo aparte de `seal`, por decisión del usuario. |
| Cinta | `tape` | Tipo de dispositivo de seguridad (H06). |
| Funda | `pouch` | Bolsa del kit, con código de barras (H14). |
| Gate out | `gate_out` | Salida de la unidad del depósito. En SIGLA 1.0 está la solicitud de retiro. |
| Remolcador | `tractor` | Unidad que jala el semirremolque (H23). |
| Semirremolque | `semi_trailer` | Unidad que lleva la carga (H23). |
| Tipo de documento | `identity` | Tabla `identities` y columna `identity_id`, catálogo SUNAT 06. |
| Tipo de dispositivo de seguridad | `security_device_type` | Tabla `security_device_types` (H06). Su clase (sello, precinto, cinta u otro) es la columna `kind`. |
| Anuncio | `security_device_announcement` | Origen de un lote: naviera, proveedor, tipo y cómo se arman sus series (H07). |
| Lote | `security_device_lot` | Dispositivos de seguridad que llegan juntos, con su fecha de recepción y su guía (H07). |
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
