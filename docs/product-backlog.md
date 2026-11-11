# Product Backlog · SIGLA 2.0 (TOS)

**Estado:** en construcción. Épicas aprobadas el 2026-10-26. Historias aprobadas el 2026-10-27: E1 (H01 a H04), E7 (H05), E8 (H09, H10), E2 (H06 a H08), E3 (H11, H12), E4 (H13 a H16), E9 (H21 a H23) y E5 (solo H17).

**En revisión desde el 2026-10-28:** el proceso posterior a la asignación del kit a la certificación (H18, H19 y H20 de E5, y toda E6). El límite acordado es H17: se queda lo que llega hasta iniciar la certificación y asignarle el kit. Lo posterior se vuelve a revisar con el usuario antes de darlo por aprobado. Lo conversado queda marcado y no se borra.

Product Goal: ver `docs/product-goal.md`.

## Orden de las épicas (aprobado)
E1, E7, E8, E2, E3, E4, E9, E5, E6.

## Roles del módulo (todos INTRANET)
- Admin
- Supervisor
- Usuario de bodega
- Inspector certificado (checker de lavado)
- Inspector checkpoint

## Decisiones de producto y técnicas
- Todo web, responsivo para celular, con stack TALL. La APK queda fuera por ahora.
- Inicio de sesión con correo y contraseña para todos los usuarios.
- Se usa el starter kit oficial de Livewire con Fortify (sesión, cierre de sesión, recuperación de contraseña), sin Jetstream (decisión del 2026-10-29). Sin registro público de cuentas: los usuarios los crea el admin.
- Hay dos tipos de acceso: INTRANET (personal de la empresa: operaciones, patio, inspecciones, checkpoint, backoffice y futuros módulos del DT aduanero) y EXTRANET (usuarios que pertenecen a un operador; un operador puede tener n usuarios). En esta primera versión solo se construye la intranet; el sistema deja preparada la separación de rutas (grupos `/intranet` y `/extranet`, cada uno con su archivo de rutas; el de extranet todavía no tiene rutas; el login, la recuperación de contraseña y el perfil son comunes y van fuera de esos grupos). El tipo de acceso (columna `scope` en `users`) se agrega cuando llegue la extranet (decisión del usuario del 2026-11-02).
- Los roles y permisos se agregan al final, al culminar E6, con `laravel-permission` (decisión del usuario del 2026-11-03). Hasta entonces, cualquier usuario con sesión puede usar todas las pantallas de la intranet. Ver H24.
- Un rol por usuario (suposición por confirmar antes de construir H24).
- Regla común de escaneo: todo se escanea (dispositivos y kits tienen código propio; el kit lleva funda con código de barras). Hoy el escáner llena el campo activo y el dato se guarda solo, de forma asíncrona, sin botón de guardar. SIGLA 2.0 debe tener esa misma capacidad. La pantalla muestra qué escaneos se guardaron y cuáles fallaron, para poder reintentar (suposición por confirmar: el escáner funciona como teclado).
- Puente con SIGLA 1.0 (decisión revisada el 2026-10-27): al iniciar una certificación se consulta SIGLA 1.0 una sola vez y SIGLA 2.0 guarda en la certificación una copia permanente de los datos de la solicitud (cliente, producto, booking, naviera y contenedor) más la referencia al despacho (`source` = api mientras dependa de SIGLA 1.0; `web` cuando SIGLA 2.0 tenga su módulo de solicitudes y despachos y apunte directo a su tabla de solicitudes u operaciones). Así la certificación y cualquier PDF futuro no dependen de que SIGLA 1.0 siga existiendo. Los datos llegan validados desde una operación real, casi al final del despacho.
- Transportista, conductor y vehículos (remolcador y semirremolque) tienen catálogos propios (E9) y son independientes entre sí: un conductor o vehículo no depende de un transportista (pueden cambiar de transportista o tercerizar). La relación entre ellos se guarda en cada certificación. Al iniciar la certificación se hace un findOrCreate: si ya existen se enlazan; si no, se crean. Se reconocen por RUC (transportista), DNI (conductor) y placa (vehículo). El número de documento y de brevete no cambian.
- Datos que entrega la API de SIGLA 1.0: transportista (RUC, razón social); conductor (DNI, brevete, nombres y apellidos); vehículo (marca, categoría —N1, N2, N3, O1, O2, O3…—, placa, y tipo de vehículo como texto: remolcador, semirremolque plataforma o semirremolque contenedora). Cliente y producto no tienen catálogo y se guardan como datos de la certificación.
- Las líneas navieras y los proveedores son tablas maestras de SIGLA 2.0, con su propio módulo (E8).
- Por ahora solo se manejan contenedores de 40'. Lo que pide cada sección es igual para todos. Si entran otras medidas, se amplía.
- La certificación es un proceso OPCIONAL que va después del EIR del gate out (el EIR se hace a todas las unidades y todavía no entra en SIGLA 2.0). Se practica solo a algunas navieras. SIGLA 2.0 no valida ni obliga si una unidad debe certificarse ni impide cerrar un gate out sin certificación; el inspector decide. Esa regla sería una historia nueva cuando se migre el gate out. No seremos minuciosos en ese detalle por ahora.
- Lo que pide cada sección (tipo de dispositivo y cantidad) no existe en BUMMOCK; el técnico lo tiene interiorizado. Es una oportunidad de mejora de SIGLA 2.0.
- Recorrido del kit: (1) bodega crea el kit y le escanea dispositivos, (2) bodega lo despacha a un trabajador (H25), (3) el inspector busca la funda en su app e inicia la certificación asignándole ese kit. **Lo que ocurre después de este paso (cómo se colocan los dispositivos, fotos, cierre) está EN REVISIÓN.**
- Una certificación usa un solo kit. "El ticket" que se asigna al iniciar es la solicitud de retiro.
- Datos personales (DNI y brevete): solo los ven los roles que los necesitan. No es asesoría legal.
- El modelo de datos se documenta en `docs/database.md`, que parte del ER del usuario y es la fuente de verdad. Claude Code lo mantiene durante la Construcción (cambiado el 2026-11-04).
- Los kits se despachan a trabajadores (H25), propios o de un proveedor. Un trabajador no se enlaza directamente a un usuario: el inspector que inició sesión se reconoce comparando el tipo y número de documento de su usuario con los del trabajador (decisión del usuario del 2026-11-04).
- Por ahora hay una sola empresa (`companies`), cargada por seeder y modificable en su pantalla (H27), y cada sucursal pertenece a ella. Las sucursales también se gestionan en pantalla (H27). Si el sistema escala a multitenancy, se pasa a varias empresas (decisión del usuario del 2026-11-04).
- EN REVISIÓN (conversado, no aprobado): todas las secciones obligatorias para cerrar una certificación, con al menos una foto cada una y varias fotos permitidas por sección.

## Notas para la Construcción (desde el ER del usuario)
- Falta qué dispositivos y cuántos pide cada sección (H12). El ER ya trae la tabla con su cantidad y clave compuesta. Se podría renombrar `certificacion_criteria_items` (el ER nuevo ya lo llama `certification_criteria_items`). (Resuelto el 2026-11-04: se llamará `certification_sections`.)
- El tipo de kit guarda la cantidad por tipo de dispositivo en el ER nuevo (apoya H13, criterio 7, que sigue por confirmar).
- Índices únicos individuales en `type_kits_has_security_device_types` y `branches_has_users`: probablemente deberían ser compuestos. (Resuelto el 2026-11-04: `branches_has_users` se descarta y el tipo de kit usa una clave compuesta con cantidad.)
- `security_certifications` tiene `gate_out_request_id` único: definir si la reinspección reabre la certificación o crea otra. (Resuelto el 2026-11-04: la reabre.)
- `security_certifications` guarda zona de patio, pero la gestión de patio está fuera del Product Goal: definir de dónde sale ese dato. (Resuelto el 2026-11-04: lo elige el inspector al iniciar, de un catálogo de zonas, H26.)
- `security_certifications` guarda horas de inicio y cierre sin fecha: revisar. (Resuelto el 2026-11-04: pasan a fecha y hora.)
- `kits_dispatches` guarda receptor y quien asigna como trabajadores (`workers`), pero el receptor es un inspector certificado con cuenta de usuario: reconsiderar ese vínculo. (Resuelto el 2026-11-04: el receptor es un trabajador, H25; quien entrega es el usuario que despacha.)
- La certificación debe guardar la copia de los datos de la solicitud y referencias a transportista, conductor, remolcador y semirremolque (el ER aún no lo trae).
- Traducir el texto del tipo de vehículo a los tres valores del catálogo; si no coincide, crear igual y marcar para revisión (por confirmar). (Resuelto el 2026-11-11 en H23: el tipo es remolcador o semirremolque según venga de `placa_tracto` o `placa_carreta`, la carrocería va a un catálogo gestionable y el vehículo se registra directo, sin marca de revisión.)
- Pendiente: qué significan la semana y la zona de patio del kit, y si la zona se elige al crear el kit. (Resuelto el 2026-11-04: ver H14.)
- Pendiente: cómo se reconoce la naviera que trae la API contra el catálogo de E8 y qué pasa si no existe. (Resuelto el 2026-11-04: ver H17.)
- Tipos de contenedor: por ahora solo 40'; supuesto de que la lista se carga una vez, sin pantalla de gestión.
- EN REVISIÓN (lo posterior a la asignación del kit): fotos por sección y su peso desde celular, vínculo dispositivo ↔ sección ↔ certificación, PDF de la certificación, zonas del formato (exteriores, interiores, puertas) y todo el checkpoint.

## Épicas

### E1 · Usuarios y acceso
- Que cada persona entre con su cuenta y vea solo lo que le corresponde según su rol.
- Depende de: ninguna. Todas las demás dependen de E1.

**H01 · Iniciar sesión**
- Como usuario del sistema (cualquier rol), quiero iniciar sesión con mi correo y contraseña, para entrar a lo que mi rol me permite.
- Criterios de aceptación:
  1. Con correo y contraseña correctos, el usuario entra al sistema.
  2. Con datos incorrectos, ve un mensaje de error que no dice si falló el correo o la contraseña.
  3. Un usuario desactivado no puede entrar (cambiado el 2026-11-03 en H04: se desactiva con Soft Deletes, columna `deleted_at` en `users`, en vez de `is_active`; la pantalla para desactivar está en H04).
  4. La pantalla se puede usar bien desde el celular.

**H02 · Cerrar sesión**
- Como usuario del sistema, quiero cerrar mi sesión, para que nadie más use mi cuenta en un celular o computadora compartidos.
- Criterios de aceptación:
  1. Al cerrar sesión, el usuario vuelve a la pantalla de inicio de sesión.
  2. Después de cerrar sesión, no puede volver a ninguna pantalla interna con el botón "atrás".

**H03 · Recuperar contraseña**
- Como usuario del sistema, quiero recuperar mi contraseña por correo, para volver a entrar si la olvido sin pedir ayuda al admin.
- Criterios de aceptación:
  1. Desde el inicio de sesión, el usuario pide recuperar su contraseña con su correo.
  2. Si el correo existe, recibe un enlace para crear una contraseña nueva.
  3. Si el correo no existe, ve el mismo mensaje que si existiera.
  4. El enlace vence a los 60 minutos y solo se puede usar una vez (confirmado el 2026-11-03).
  5. Un usuario desactivado no puede recuperar acceso: no recibe el enlace, y uno pedido antes de la desactivación deja de servir.
  6. Solo funciona para usuarios intranet por ahora (todos los usuarios lo son; no hace falta un filtro hasta que llegue la extranet).
- Riesgo técnico: el envío de correo saliente del servidor; revisar entregabilidad en la tarea de Construcción.

**H04 · Gestionar usuarios intranet**
- Como usuario de la intranet, quiero crear, editar, desactivar y reactivar usuarios intranet, para controlar quién entra al sistema. (Cambiado el 2026-11-03: la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. Se crea un usuario con nombre, correo, tipo y número de documento, y sucursal. El tipo de documento se elige de una lista cargada por seeder, sin pantalla propia, y la sucursal de las activas, que se gestionan en H27 (cambiado el 2026-11-04; se construye en T19).
  2. No se pueden crear dos usuarios con el mismo correo, aunque uno esté desactivado.
  3. Se editan los datos de un usuario.
  4. Un usuario se desactiva en vez de borrarlo, con Soft Deletes, para conservar el historial (confirmado el 2026-11-03). Un usuario desactivado no puede iniciar sesión y su sesión abierta se cierra en su siguiente petición. Se reactiva desde la lista. Nadie puede desactivarse ni reactivarse a sí mismo.
  5. Al crear un usuario se genera una contraseña aleatoria, que se muestra para copiarla y entregársela. No se vuelve a mostrar y no se envía por correo (confirmado el 2026-11-03).
  6. Por ahora, cualquier usuario con sesión usa esta pantalla. "Solo el admin" pasa a H24.
  7. No existe registro público.
  8. La pantalla se puede usar bien desde el celular.
  9. No se pueden crear dos usuarios con el mismo tipo y número de documento, aunque uno esté desactivado (T19).
  10. Un usuario pertenece a una sola sucursal (T19).
  11. La lista muestra la sucursal de cada usuario y se filtra por ella, también por una desactivada (pedido por el usuario el 2026-11-04).
  12. Al crear un usuario con DNI, el sistema consulta el servicio externo como en H10 (al completar los 8 dígitos o con el botón de la lupa) y completa el nombre. Se puede corregir y, si el servicio falla, se llena a mano. No se consulta al editar ni con otros tipos de documento (decidido por el usuario el 2026-11-04). El nombre se completa como "Nombres Apellidos", con mayúscula inicial en cada palabra, y en el modal de usuario el tipo y el número de documento van antes del nombre (confirmado por el usuario el 2026-11-09; T31 del Sprint 2).

**H24 · Roles y permisos** (propuesta del 2026-11-03, sin aprobar; se construye al culminar E6)
- Como admin, quiero asignar un rol a cada usuario, para que cada persona vea y use solo lo que le corresponde.
- Criterios de aceptación:
  1. El admin asigna y cambia el rol de un usuario (admin, supervisor, usuario de bodega, inspector certificado, inspector checkpoint).
  2. Solo el admin ve y usa la pantalla de usuarios.
  3. Cada pantalla y cada acción validan el rol en el servidor.
  4. Las pantallas de empresa y sucursales (H27) se protegen con un permiso de acceso (indicado por el usuario el 2026-11-04).
  5. Las pantallas de líneas navieras y proveedores (H09, H10) quedan solo para el admin. La de tipos de dispositivo (H06) queda para el admin y el usuario de bodega, y solo el admin desactiva (T21, 2026-11-04).
- Usa `laravel-permission`, que se instala recién en esta historia (indicado por el usuario el 2026-11-03).
- Supuesto: un rol por usuario.

**H27 · Modificar la empresa y gestionar sus sucursales** (propuesta del 2026-11-04, sin aprobar; las sucursales se sumaron a pedido del usuario el mismo día)
- Como admin, quiero modificar los datos de la empresa, incluidos los de facturación electrónica, para mantenerlos correctos y tenerlos listos si el producto llega a facturar.
- Criterios de aceptación:
  1. Hay una pantalla con los datos de la única empresa. No se crea ni se elimina desde aquí.
  2. Se editan los datos de identificación: RUC, razón social, nombre comercial (opcional), dirección y ubigeo. El RUC debe tener 11 dígitos y el ubigeo 6, y la razón social y la dirección son obligatorias.
  3. Se puede subir o cambiar el logo, en formato png o jpg, hasta 2 MB.
  4. Se editan los datos de facturación electrónica, todos opcionales: cuenta de detracción, encabezado y pie de factura, usuario y clave SOL, client id y client secret, certificado (archivo PEM con su clave privada) y modo de producción (apagado por defecto).
  5. La clave SOL, el client secret y el certificado se guardan cifrados y no se vuelven a mostrar: la pantalla indica si ya están cargados, y dejarlos vacíos conserva el valor guardado (confirmado por el usuario el 2026-11-04).
  6. Las credenciales no se cargan por seeder.
  7. Al guardar se muestra un aviso, y si hay errores se indican junto a cada campo.
  8. Por ahora, cualquier usuario con sesión usa esta pantalla, incluso para reemplazar las credenciales: el usuario aceptó ese riesgo el 2026-11-04. En H24 se protege con un permiso de acceso.
  9. La pantalla se puede usar bien desde el celular.
  10. Hay una lista de sucursales en Administración, con búsqueda por código o nombre y filtro por estado. (Pasó de Configuración a Administración a pedido del usuario el 2026-11-04.)
  11. Se crea y se edita una sucursal con nombre, ubigeo de 6 dígitos y dirección, y con teléfono, correo y sitio web opcionales. Pertenece a la única empresa.
  12. El código se asigna solo al crear: si ya existe 0002, sigue 0003, contando también las desactivadas (indicado por el usuario el 2026-11-04). No se escribe ni se edita.
  13. Una sucursal se desactiva con Soft Deletes y se puede reactivar. Una desactivada ya no se ofrece al crear o editar un usuario.
  14. No se puede desactivar una sucursal con usuarios activos: el aviso dice cuántos son (decisión del usuario del 2026-11-04). Tampoco la última sucursal activa.
  15. Un usuario desactivado cuya sucursal está desactivada no se puede reactivar hasta asignarle una activa.
- La referencia es la pantalla de compañía del sistema actual. SIGLA no factura todavía: estos datos solo se guardan, sin ningún proceso que los use, por si el Product Goal llega a incluir la facturación (decisión del usuario del 2026-11-04).

### E7 · Puente con SIGLA 1.0
- Épica temporal: consulta la solicitud de retiro en la API de SIGLA 1.0 (sistema actual en producción) hasta migrar todo el módulo de gate out. Entonces el puente se retira.
- Depende de: E1. La necesita E5.
- Dependencia externa: la API de SIGLA 1.0 está creada a medias; falta que entregue todos los datos filtrando por `numero_contenedor` + `booking`. Terminarla antes de construir H05 (trabajo del sistema viejo; ponerlo en el sprint como tarea o impedimento). (Resuelto el 2026-11-10: la API ya entrega la solicitud con `op=GetDespatch`, filtrando por booking y contenedor, por HTTPS y con token Bearer.)

**H05 · Consultar la solicitud de retiro desde SIGLA 1.0**
- Como inspector certificado, quiero buscar una solicitud de retiro con el número de contenedor y el booking, para ver los datos de la unidad y del conductor antes de certificar.
- Criterios de aceptación:
  1. La búsqueda pide número de contenedor y booking, y ambos son obligatorios. Se escriben a mano y se pasan a mayúsculas mientras se escriben (decidido por el usuario el 2026-11-10).
  2. Si la solicitud existe, se muestran: cliente, producto, booking, naviera, conductor (con documento de identidad y brevete), remolcador, semirremolque y contenedor asignado. Los datos menores se definieron el 2026-11-10: embarcador (RUC y nombre), nave y viaje, destino y fecha de despacho; de cada vehículo, placa, carrocería, marca y categoría. No se muestran el modelo, el color ni las fechas internas de SIGLA 1.0.
  3. Si no existe ninguna solicitud con esa combinación, se muestra un mensaje claro.
  4. Si SIGLA 1.0 no responde, se muestra un mensaje claro y el usuario puede reintentar sin perder lo que escribió.
  5. Los datos son solo de lectura. SIGLA 2.0 no modifica nada en SIGLA 1.0.
  6. Solo usuarios con sesión iniciada y un rol autorizado pueden consultar (por confirmar: inspector certificado y supervisor). Hasta H24, cualquier usuario con sesión.
  7. La pantalla se puede usar bien desde el celular.
  8. El cliente es el operador de la solicitud. El embarcador es el dueño de la carga (cliente de la naviera) (indicado por el usuario el 2026-11-10).
  9. El DNI del conductor es su brevete sin la letra inicial (B46099190 → 46099190), porque los conductores son locales. Si el brevete no tiene esa forma, no se muestra DNI (indicado por el usuario el 2026-11-10).
  10. Si SIGLA 1.0 trae más de una solicitud con esa combinación, se muestran todas, una debajo de otra (suposición aprobada con el diseño el 2026-11-10).
- La consulta es en vivo. Al iniciar una certificación (H17) esos datos se guardan; la consulta de H05 en sí no guarda nada.
- La autenticación del puente es una decisión técnica para la Construcción.

### E8 · Maestros: líneas navieras y proveedores
- Que el admin gestione los catálogos de líneas navieras y proveedores, para usarlos al ingresar lotes (E2), armar kits (E4) y en lo que venga después (estimaciones, extranet, depósito temporal).
- Depende de: E1. La necesitan E2 y E4.

**H09 · Gestionar líneas navieras**
- Como usuario de la intranet, quiero crear, editar, desactivar y reactivar líneas navieras, para elegirlas al ingresar lotes y armar kits. (Cambiado el 2026-11-04 en T21: la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. Se crea una línea naviera con código y nombre.
  2. No se pueden crear dos líneas con el mismo código, aunque una esté desactivada. El código se guarda sin espacios a los lados y en mayúsculas, así `msk` y `MSK` son el mismo (confirmado por el usuario el 2026-11-04).
  3. Se editan el código y el nombre de una línea.
  4. Una línea se desactiva en vez de borrarla, con Soft Deletes, y se reactiva desde la lista. Una línea desactivada no aparece al ingresar lotes ni armar kits, pero se conserva en lo que ya existe (confirmado el 2026-11-04).
  5. Hay una lista de líneas, ordenada por código, que se puede buscar por código o nombre y filtrar por estado (el orden y el filtro, confirmados por el usuario el 2026-11-04).
  6. La pantalla se puede usar bien desde el celular.
- Quién la usa: por ahora, cualquier usuario con sesión. "Solo el admin" pasa a H24 (confirmado el 2026-11-04).
- En el menú va en el grupo "Maestros" (decisión del usuario del 2026-11-04).
- Diseño: se construye desde la plantilla "Lista con tabla", sin diseño propio en el lienzo (decisión del usuario del 2026-11-04).
- Pasar las navieras de SIGLA 1.0 a SIGLA 2.0 queda fuera de esta historia: es una tarea aparte, sin fecha todavía. Hasta entonces, las líneas se crean a mano (decisión del usuario del 2026-11-04).

**H10 · Gestionar proveedores**
- Como usuario de la intranet, quiero crear, editar, desactivar y reactivar proveedores, para elegirlos al ingresar lotes de dispositivos. (Cambiado el 2026-11-04 en T21: la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. Se crea un proveedor con tipo y número de documento, razón social, dirección, correo y teléfono. Son obligatorios el tipo y el número de documento y la razón social; dirección, correo y teléfono son opcionales (confirmado el 2026-11-04).
  2. No se pueden crear dos proveedores con el mismo tipo y número de documento, aunque uno esté desactivado.
  3. Se editan los datos de un proveedor.
  4. Un proveedor se desactiva en vez de borrarlo, con Soft Deletes, y se reactiva desde la lista (confirmado el 2026-11-04).
  5. Hay una lista, ordenada por razón social, que se puede buscar por razón social o número de documento y filtrar por estado (el orden y el filtro, confirmados por el usuario el 2026-11-09).
  6. La pantalla se puede usar bien desde el celular.
  7. Al completar los dígitos de un RUC (11) o un DNI (8), o al pulsar el botón de la lupa, el sistema consulta un servicio externo y completa los datos que encuentre. El usuario puede corregirlos antes de guardar.
  8. Si el servicio no responde o no encuentra el documento, el usuario puede llenar a mano y guardar igual.
  9. Si el documento tiene un formato incorrecto, se avisa antes de consultar.
- Quién la usa: por ahora, cualquier usuario con sesión. "Solo el admin" pasa a H24 (confirmado el 2026-11-04).
- En el menú va en el grupo "Maestros", debajo de Líneas navieras (confirmado por el usuario el 2026-11-09).
- Diseño: aprobado por el usuario el 2026-11-04 en la página "E8 · Maestros" del lienzo, con un botón naranja de lupa siempre pegado al número de documento.
- Reglas de la consulta, aprobadas con el diseño: el formato incorrecto se avisa al salir del campo; mientras consulta, Guardar y Razón social quedan desactivados; con DNI, los nombres y apellidos van en Razón social; el documento duplicado se avisa al pulsar Guardar; al editar solo se vuelve a consultar si cambia el número; los tipos de documento son solo RUC y DNI.
- Servicio de consulta de RUC y DNI: Decolecta, elegido por el usuario, mediante `SunatService` (T22). También lo usarán H21 y H22.
- El DNI viaja en la URL de Decolecta, por HTTPS, porque ese servicio solo lo recibe así, y nunca va en nuestros logs. Es una excepción a la regla de datos personales, aceptada por el usuario el 2026-11-04.
- El ER guarda además cuenta corriente, detalle del servicio y sucursal; se dejaron fuera por no necesitarse en este módulo.

### E2 · Dispositivos de seguridad
- Que bodega registre dispositivos por lote y consulte su estado.
- Depende de: E1 y E8 (H07 usa naviera y proveedor). La necesitan E4 y E5.
- Un anuncio es el origen del lote (de quién viene y cómo se identifican sus series); se crea uno por cada lote, así que bodega no lo gestiona aparte.
- Cada dispositivo es único. Un segundo lote con la misma serie tiene otro rango.

**H06 · Gestionar tipos de dispositivo**
- Como usuario de la intranet, quiero crear, editar, desactivar y reactivar tipos de dispositivo de seguridad, para que el sistema sepa qué dispositivos existen y se puedan registrar. (Cambiado el 2026-11-04 en T21: la parte de roles pasa a H24 y los tipos ya no se eliminan.)
- Criterios de aceptación:
  1. Se crea un tipo con nombre y tipo (sello, precinto, cinta u otro).
  2. No se pueden crear dos tipos con el mismo nombre, aunque uno esté desactivado.
  3. Se edita el nombre de un tipo. El tipo (sello, precinto, cinta u otro) se elige al crear y no se cambia después (confirmado por el usuario el 2026-11-04).
  4. Un tipo se desactiva en vez de borrarlo, con Soft Deletes, y se reactiva desde la lista. Un tipo desactivado no aparece al registrar lotes ni armar kits, pero se conserva en lo que ya existe.
  5. Los tipos no se eliminan, aunque no se hayan usado: solo se desactivan (confirmado el 2026-11-04).
  6. Por ahora, cualquier usuario con sesión usa esta pantalla. Que solo el admin y el usuario de bodega la vean, y que solo el admin desactive, pasa a H24 (confirmado el 2026-11-04).
  7. La pantalla se puede usar bien desde el celular.
  8. Hay una lista de tipos, ordenada por nombre, que se puede buscar por nombre y filtrar por estado (confirmado por el usuario el 2026-11-04).
- En el menú va en el grupo "Maestros", debajo de "Líneas navieras" (decisión del usuario del 2026-11-04).
- Diseño: se construye desde la plantilla "Lista con tabla", sin diseño propio en el lienzo (decisión del usuario del 2026-11-04).
- Riesgos: duplicados con nombres parecidos; renombrar un tipo con lotes y kits afecta lo ya registrado (considerar registrar quién cambió qué).

**H07 · Ingresar un lote de dispositivos**
- Como usuario de bodega, quiero ingresar un lote escribiendo su origen y su rango de correlativos, para que el sistema genere todas las series y los dispositivos queden registrados.
- Criterios de aceptación:
  1. Se ingresan: naviera, proveedor, tipo de dispositivo, inicio de serie (por ejemplo GSW), mínimo de dígitos, correlativo inicial y final, final de serie (opcional, solo letras), fecha de recepción, número de guía y observaciones (opcional).
  2. Solo se puede elegir un tipo de dispositivo activo.
  3. La cantidad se calcula sola desde el rango (ejemplo: del 200 al 399 son 200). El correlativo inicial no puede ser mayor que el final.
  4. Antes de guardar, se muestra una vista previa con la cantidad, la primera serie y la última.
  5. Al guardar, el sistema genera todas las series uniendo la serie y el correlativo, sin separador, completando con ceros a la izquierda según el mínimo de dígitos. Ejemplo: serie GSW, mínimo 7 dígitos, del 200 al 399 da GSW0000200 a GSW0000399. Si un correlativo tiene más dígitos que el mínimo, se deja completo (confirmado por el usuario el 2026-11-09, T27). Si hay final de serie, va pegado al final: inicio GWF, mínimo 5 dígitos, del 100 al 200 y final A da GWF00100A a GWF00200A (pedido por el usuario el 2026-11-10).
  6. Si alguna serie ya existe en el sistema, no se guarda nada y se muestra cuáles chocan.
  7. El guardado es todo o nada.
  8. Las unidades quedan en estado Disponible, listas para armar kits (confirmado el 2026-11-09, T27).
  9. Queda registrado quién ingresó el lote y cuándo.
  10. Un lote trae como máximo 10 000 series: el sistema no deja ingresar un rango mayor. Se guarda en el momento, sin cola, y la pantalla muestra que está guardando (confirmado el 2026-11-09, T27).
  11. La pantalla se puede usar bien desde el celular.
- Quién ingresa lotes: por ahora, cualquier usuario con sesión. "Solo bodega y admin" pasa a H24 (confirmado el 2026-11-09, T27).
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Formulario" (T30, aprobado por el usuario el 2026-11-10).
- En el menú va en el grupo "Bodega", como "Lotes de dispositivos" (decisión del usuario del 2026-11-10).
- Una carga desde Excel con series sueltas sería otra historia (no pedida por ahora).

**H08 · Consultar lotes y dispositivos**
- Como usuario de bodega, supervisor o admin, quiero ver los lotes ingresados y buscar un dispositivo por su serie, para saber qué tengo, en qué estado está y de qué lote viene.
- Criterios de aceptación:
  1. Hay una lista de lotes con tipo de dispositivo, naviera, proveedor, serie, rango, cantidad y fecha de recepción.
  2. La lista se puede filtrar por naviera, proveedor, tipo de dispositivo y fecha.
  3. Al abrir un lote, se ven sus dispositivos con su serie y su estado.
  4. Se puede buscar un dispositivo por su serie, escribiéndola o escaneándola. Al escanear, la serie llena el campo y busca sola.
  5. Para cada dispositivo se ve su estado, su lote y, si ya está en un kit, a cuál pertenece.
  6. Se ve cuántos dispositivos hay por estado en cada lote.
  7. Pantalla de solo consulta.
  8. La pantalla se puede usar bien desde el celular.
- Estados de un dispositivo: Disponible, En un kit, Usado y Dañado. Por ahora ninguna historia marca un dispositivo como Dañado; el estado queda definido para cuando una lo use (confirmado el 2026-11-09, T27).
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Lista con tabla" (T30, aprobado por el usuario el 2026-11-10).
- Vive en la misma entrada del menú que H07: grupo "Bodega", "Lotes de dispositivos".

**H28 · Corregir un lote** (propuesta del 2026-11-10, pedida por el usuario al revisar H08; reglas 2 y 3 confirmadas por el usuario el mismo día)
- Como usuario de bodega, quiero corregir los datos de un lote ya ingresado, para arreglar un error de digitación sin borrar el lote y volver a ingresarlo.
- Criterios de aceptación:
  1. El detalle del lote tiene un botón "Editar", y cada fila de la lista de lotes tiene uno al lado de "Ver" (pedido por el usuario el 2026-11-10). Los dos abren el formulario de H07 con los datos del lote.
  2. Naviera, proveedor, tipo de dispositivo, fecha de recepción, guía y observaciones se pueden corregir siempre.
  3. Inicio de serie, mínimo de dígitos, correlativos y final de serie solo se pueden corregir si ningún dispositivo del lote cambió de estado (todos siguen Disponible). Si alguno cambió, esos campos se muestran bloqueados con un aviso que explica por qué.
  4. Al cambiar la serie, el sistema rehace todas las series del lote con las reglas de H07: vista previa, correlativo inicial no mayor que el final, máximo 10 000 series y, si alguna serie nueva ya existe en otro lote, no se guarda nada y se muestra cuáles chocan.
  5. El guardado es todo o nada.
  6. Al guardar, se vuelve al detalle del lote con un aviso.
  7. La pantalla se puede usar bien desde el celular.
- Quién corrige lotes: por ahora, cualquier usuario con sesión, como en H07. Los roles pasan a H24.
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Formulario" (T36, aprobado por el usuario el 2026-11-10).

### E3 · Secciones del contenedor
- Tabla gestionable de secciones (criterios de inspección): unas 20 internas y unas 10 externas.
- Depende de: E1 (y de H06 para H12). La necesitan E4, E5 y E6.

**H11 · Gestionar secciones de certificación**
- Como admin, quiero crear, editar, ordenar y desactivar las secciones que se inspeccionan en un contenedor, marcando si son internas o externas, para que la certificación y el checkpoint pidan siempre lo correcto.
- Criterios de aceptación:
  1. El admin crea una sección con nombre y tipo (interna o externa), por ejemplo "Fotografía de evaporadores".
  2. No se pueden crear dos secciones con el mismo nombre.
  3. El admin edita el nombre y el tipo de una sección.
  4. El admin define el orden en que aparecen las secciones; es el orden de los pasos del multi-step form de la certificación (por confirmar).
  5. El admin desactiva una sección en vez de borrarla. Una desactivada no aparece en certificaciones nuevas, pero se conserva en las ya hechas (por confirmar).
  6. Hay una lista que se puede filtrar por interna o externa.
  7. Cambiar las secciones no modifica certificaciones ya cerradas.
  8. Solo el admin usa esta pantalla (por confirmar).
  9. La pantalla se puede usar bien desde el celular.
- Riesgo: agregar o desactivar una sección a mitad de una certificación en curso puede dejar el sistema inconsistente.
- Duda abierta (en revisión): el formato de la certificación agrupa en tres zonas (exteriores, interiores, puertas); falta saber si las puertas son un tercer tipo.
- Construida el 2026-11-10 (T35) con supuestos que quedan por confirmar en T34, a pedido del usuario: las puertas no son un tipo (solo interna o externa, P1 de `database.md`); el orden es uno solo para internas y externas y es el de los pasos (criterio 4); una sección desactivada sale del orden y no aparece en certificaciones nuevas (criterio 5); una nueva o reactivada va al final; el nombre no se repite aunque una esté desactivada; por ahora, cualquier usuario con sesión usa la pantalla (criterio 8, hasta H24). Se ordena arrastrando en escritorio y con subir y bajar en el teléfono, y se guarda al momento.

**H12 · Definir qué dispositivos lleva cada sección**
- Como admin, quiero indicar para cada sección qué tipos de dispositivo lleva y cuántos de cada uno, para que el inspector sepa qué colocar en cada sección durante la certificación.
- Criterios de aceptación:
  1. Al editar una sección, el admin agrega uno o más tipos de dispositivo con su cantidad. Ejemplo: "Fotografía de evaporadores" lleva 2 sellos.
  2. Solo se pueden elegir tipos de dispositivo activos.
  3. Una sección puede llevar más de un tipo (ejemplo: 1 sello y 1 cinta).
  4. Una sección puede no llevar ningún dispositivo, por ejemplo si solo pide una foto (por confirmar).
  5. La cantidad es un número entero mayor que cero.
  6. No se puede repetir el mismo tipo dos veces en una sección; se cambia su cantidad.
  7. El admin puede quitar un tipo de una sección o cambiar su cantidad.
  8. En la lista de secciones se ve qué lleva cada una de un vistazo.
  9. Cambiar lo que lleva una sección no modifica certificaciones ya cerradas.
  10. Esta regla es igual para todos los contenedores de 40'.
  11. Solo el admin usa esta pantalla (por confirmar).
  12. La pantalla se puede usar bien desde el celular.
- Duda: algunos dispositivos podrían ir en un punto único del contenedor (por ejemplo la puerta) y no por sección; ver si entra en esta lista.

### E4 · Kits
- Que bodega arme kits con dispositivos escaneados, los despache a un trabajador y los consulte.
- Depende de: E1, E2, E3, E8. La necesita E5.

**H13 · Gestionar tipos de kit**
- Como admin, quiero crear tipos de kit indicando para qué tipo de contenedor son y qué tipos de dispositivo lleva cada uno, para que bodega solo pueda armar kits con lo que corresponde.
- Criterios de aceptación:
  1. El admin crea un tipo de kit con nombre, tipo de contenedor y los tipos de dispositivo que puede llevar.
  2. Solo se pueden elegir tipos de dispositivo activos.
  3. No se repite el mismo tipo de dispositivo dentro de un tipo de kit.
  4. No se pueden crear dos tipos de kit con el mismo nombre.
  5. El admin edita un tipo de kit. El cambio no modifica los kits ya creados (por confirmar).
  6. El admin desactiva un tipo de kit en vez de borrarlo. Uno desactivado no aparece al crear kits.
  7. Cantidad (por confirmar): cada tipo de dispositivo del tipo de kit lleva cuántos, por ejemplo 6 precintos.
  8. Solo el admin usa esta pantalla (por confirmar).
  9. La pantalla se puede usar bien desde el celular.

**H25 · Gestionar trabajadores** (propuesta del 2026-11-04, sin aprobar)
- Como admin, quiero crear, editar y desactivar trabajadores, propios o de un proveedor, para elegirlos como receptores al despachar kits.
- Criterios de aceptación:
  1. El admin crea un trabajador con nombre, tipo y número de documento, e indica si es propio o de un proveedor. Si es de un proveedor, elige uno activo (H10).
  2. No se pueden crear dos trabajadores con el mismo tipo y número de documento, aunque uno esté desactivado.
  3. El admin edita los datos de un trabajador.
  4. El admin desactiva un trabajador en vez de borrarlo, y lo reactiva desde la lista. Uno desactivado no aparece al despachar kits, pero se conserva en los despachos ya hechos.
  5. Hay una lista que se puede buscar por nombre o número de documento.
  6. Un trabajador no se enlaza directamente a un usuario. Para que un inspector vea los kits que le despacharon, su usuario y su trabajador deben tener el mismo tipo y número de documento.
  7. El número de documento solo lo ven los roles que lo necesitan. No es asesoría legal.
  8. Solo el admin usa esta pantalla (por confirmar).
  9. La pantalla se puede usar bien desde el celular.

**H26 · Gestionar zonas de patio** (propuesta del 2026-11-04, sin aprobar)
- Como admin, quiero crear, editar y desactivar las zonas de patio de cada sucursal, para que el inspector elija una al iniciar una certificación.
- Criterios de aceptación:
  1. El admin crea una zona con sucursal, nombre y capacidad.
  2. No se pueden crear dos zonas con el mismo nombre en la misma sucursal (por confirmar).
  3. El admin edita los datos de una zona.
  4. El admin desactiva una zona en vez de borrarla. Una desactivada no aparece al iniciar una certificación, pero se conserva en las ya hechas.
  5. Hay una lista que se puede buscar por nombre y filtrar por sucursal.
  6. La capacidad es un dato informativo: el sistema no controla cuánto hay en cada zona (por confirmar). La gestión de patio sigue fuera del Product Goal.
  7. Solo el admin usa esta pantalla (por confirmar).
  8. La pantalla se puede usar bien desde el celular.

**H14 · Crear un kit y agregarle dispositivos**
- Como usuario de bodega, quiero crear un kit y agregarle dispositivos escaneándolos, para dejarlo listo para despacharlo.
- Criterios de aceptación:
  1. Se ingresan los datos generales: código de barras de la funda (escaneado o escrito), tipo de kit y naviera. Cuáles son obligatorios se confirma antes de construir.
  2. El código de la funda no puede repetirse en otro kit.
  3. Solo se pueden elegir tipos de kit y navieras activos.
  4. Los dispositivos se agregan escaneando uno por uno. Cada escaneo llena el campo y se agrega solo al kit, sin botón de guardar. El guardado es asíncrono (regla común).
  5. Solo se aceptan dispositivos de los tipos que permite el tipo de kit; si es de otro tipo, mensaje claro y no se agrega.
  6. Un dispositivo solo puede estar en un kit; si ya está en otro, se muestra en cuál.
  7. Solo se aceptan dispositivos disponibles, no usados ni dañados (estados exactos por confirmar).
  8. Se ve la lista de lo escaneado, con qué se guardó y qué falló, para reintentar.
  9. Mientras el kit no esté despachado, bodega puede quitar un dispositivo, que vuelve a quedar disponible.
  10. Si el tipo de kit define cantidades (H13, criterio 7), se muestra el avance (ejemplo: "4 de 6 precintos") y se avisa si falta alguno (por confirmar si deja guardar el kit incompleto).
  11. Queda registrado quién creó el kit y cuándo.
  12. La pantalla se puede usar bien desde el celular, con el cursor listo en el campo de escaneo después de cada lectura.
- La semana no se escribe: es la semana ISO de la fecha de creación del kit, en hora de Lima, y se usa para listar y filtrar en H16. La zona de patio no va en el kit: la elige el inspector al iniciar la certificación, H17 (decisión del 2026-11-04).

**H15 · Despachar kits**
- Como usuario de bodega, quiero despachar uno o más kits a un trabajador, para dejarlos disponibles para certificar.
- Criterios de aceptación:
  1. Se elige el trabajador que recibe los kits, de la lista de trabajadores activos (H25). Quién entrega es el usuario de bodega que despacha, y queda registrado solo.
  2. Los kits se agregan escaneando la funda de cada uno. Cada escaneo llena el campo y se agrega solo a la lista, sin botón de guardar.
  3. Solo se pueden despachar kits que no estén ya despachados; si ya lo está, se muestra en qué despacho.
  4. No se puede despachar un kit vacío (por confirmar).
  5. Cada kit queda en un solo despacho.
  6. Al despachar, el contenido del kit ya no se puede cambiar.
  7. Queda registrado quién hizo el despacho y cuándo.
  8. Hay una lista de despachos con kits, receptor y fecha, que se puede filtrar.
  9. Se ve qué escaneos se guardaron y cuáles fallaron, para reintentar.
  10. La pantalla se puede usar bien desde el celular.
  11. El inspector ve en su app los kits despachados al trabajador que tiene su mismo tipo y número de documento. Un kit despachado a otro trabajador no le aparece. Si ningún trabajador coincide, no ve kits (decisión del 2026-11-04).
- Fuera de esta historia: la asignación del kit a un contenedor concreto y la búsqueda de la funda por el inspector ocurren al certificar (E5).

**H16 · Consultar kits y despachos**
- Como usuario de bodega, supervisor o admin, quiero ver los kits y sus despachos, y buscar un kit por el código de su funda, para saber qué contiene, en qué estado está y quién lo tiene.
- Criterios de aceptación:
  1. Hay una lista de kits con código de funda, tipo de kit, naviera, semana, estado y cuántos dispositivos lleva.
  2. La lista se puede filtrar por tipo de kit, naviera, semana y estado.
  3. Se puede buscar un kit por el código de su funda, escribiéndolo o escaneándolo; al escanear, llena el campo y busca solo.
  4. Al abrir un kit, se ven sus dispositivos con su serie y su tipo.
  5. Para un kit despachado, se ve a qué trabajador y en qué despacho está, y quién lo despachó.
  6. Para un kit ya usado en una certificación, se ve a qué certificación pertenece (depende de E5; por confirmar).
  7. Pantalla de solo consulta.
  8. La pantalla se puede usar bien desde el celular.
- Pendiente: estados de un kit (por confirmar: armado, despachado, usado).
- Falta una historia de E4, que se escribe al llegar a E6: reasignar dispositivos a una unidad en reinspección (en revisión junto con E6).

### E9 · Transportistas, conductores y vehículos
- Los catálogos de transportistas (empresas), conductores y vehículos (remolcador y semirremolque), con sus pantallas de gestión. También se crean solos al iniciar una certificación, cuando vienen de SIGLA 1.0 y no existen todavía.
- Se reconocen por RUC (transportista), DNI (conductor) y placa (vehículo). Son catálogos independientes entre sí. No hay pantallas de gestión de la relación: esta se guarda en cada certificación.
- No va dentro de E7 porque el puente es temporal y estos catálogos se quedan (los usará el módulo de solicitudes y despachos de SIGLA 2.0).
- Depende de: E1. La necesita E5 (H17).

**H21 · Gestionar transportistas**
- Como admin, quiero crear, editar y desactivar transportistas, para tener su registro ordenado y poder enlazarlos a cada certificación. (Cambiado el 2026-11-11 en T40: por ahora la usa cualquier usuario con sesión; la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. El admin crea un transportista con RUC y razón social. Dirección, correo y teléfono son opcionales.
  2. No se pueden crear dos transportistas con el mismo RUC.
  3. Al escribir el RUC, el sistema consulta el servicio externo y completa los datos que encuentre, como en H10. Si falla, se llena a mano.
  4. El admin edita los datos de un transportista.
  5. El admin desactiva un transportista en vez de borrarlo, con Soft Deletes, y lo reactiva desde la lista. Como nunca se borra, uno con certificaciones tampoco se elimina (resuelto el 2026-11-11 en T40: no hay botón de eliminar).
  6. Hay una lista, ordenada por razón social, que se puede buscar por razón social o RUC y filtrar por estado (aprobado con el diseño el 2026-11-11).
  7. Un transportista se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17), con RUC y razón social.
  8. Solo el admin usa esta pantalla: pasa a H24. Hasta culminar E6, cualquier usuario con sesión la usa (decisión del 2026-11-03).
  9. La pantalla se puede usar bien desde el celular.
- El criterio 7 se construye con H17; H21 deja listo el catálogo.
- El tipo de documento es siempre RUC: no se elige.
- En el menú va dentro de "Maestros", en el desplegable "Transporte", junto con Conductores (H22) y Vehículos (H23) (cambiado por el usuario el 2026-11-11; antes era un grupo propio).
- No se asignan conductores ni vehículos al transportista: siguen siendo catálogos independientes y la relación se guarda en cada certificación (confirmado por el usuario el 2026-11-11).
- Diseño: aprobado por el usuario el 2026-11-11 en la página "E9 · Transportistas, conductores y vehículos" del lienzo.

**H22 · Gestionar conductores**
- Como admin, quiero crear, editar y desactivar conductores, para tener su registro ordenado y poder enlazarlos a cada certificación. (Cambiado el 2026-11-11 en T41: por ahora la usa cualquier usuario con sesión; la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. El admin crea un conductor con DNI, brevete, nombres y apellidos. Los nombres y apellidos van en un solo campo, como los entrega la API de SIGLA 1.0. El brevete es obligatorio (decisiones del usuario del 2026-11-11).
  2. No se pueden crear dos conductores con el mismo DNI, ni con el mismo brevete, aunque uno esté desactivado. El brevete es texto libre de hasta 15 caracteres, en mayúsculas y sin espacios, para servir también a extranjeros (aprobado con el diseño el 2026-11-11).
  3. Al escribir el DNI, el sistema consulta el servicio externo y completa los nombres y apellidos que encuentre, como en H10. El usuario puede corregirlos, y si el servicio falla llena a mano (confirmado con el diseño el 2026-11-11).
  4. El admin edita los datos de un conductor.
  5. El admin desactiva un conductor en vez de borrarlo, con Soft Deletes, y lo reactiva desde la lista. Como nunca se borra, uno con certificaciones tampoco se elimina (resuelto el 2026-11-11 en T41: no hay botón de eliminar).
  6. Hay una lista, ordenada por nombres y apellidos, que se puede buscar por DNI, brevete o nombre y filtrar por estado. La búsqueda no viaja en la URL, para que el DNI no quede en ella (aprobado con el diseño el 2026-11-11).
  7. Un conductor se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17).
  8. Los datos personales (DNI y brevete) solo los ven los roles que los necesitan. Cuáles son se resuelve en H24; hasta entonces los ve cualquier usuario con sesión (T41, sprint 2). Esto no es asesoría legal.
  9. Solo el admin crea, edita y desactiva conductores: pasa a H24. Hasta culminar E6, cualquier usuario con sesión la usa (decisión del 2026-11-03).
  10. La pantalla se puede usar bien desde el celular.
- El conductor no queda ligado a ningún transportista.
- El criterio 7 se construye con H17; H22 deja listo el catálogo.
- En el menú va en Maestros → Transporte, debajo de Transportistas (cambiado por el usuario el 2026-11-11).
- Diseño: aprobado por el usuario el 2026-11-11 en la página "E9 · Transportistas, conductores y vehículos" del lienzo.

**H23 · Gestionar vehículos**
- Como admin, quiero crear, editar y desactivar vehículos (remolcadores y semirremolques), para tener su registro ordenado y poder enlazarlos a cada certificación. (Cambiado el 2026-11-11 en T42: por ahora la usa cualquier usuario con sesión; la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. El admin crea un vehículo con placa, marca, tipo (remolcador o semirremolque), carrocería y categoría. La carrocería se elige de un catálogo gestionable, con su propia pantalla para crear, editar, desactivar y reactivar; una desactivada no se elige en vehículos nuevos, pero los que la tienen la conservan. La categoría es la lista fija de SIGLA 1.0: N1, N2, N3, O1, O2, O3, O4 y Otro, con N3 por defecto. (Cambiado el 2026-11-11 a pedido del usuario: antes eran tres tipos fijos, remolcador, semirremolque plataforma y semirremolque contenedora.)
  2. No se pueden crear dos vehículos con la misma placa, aunque uno esté desactivado. La placa se guarda en mayúsculas, sin espacios ni guiones, como `ABC123`, igual que SIGLA 1.0 (decisión del usuario del 2026-11-11).
  3. El admin edita los datos de un vehículo.
  4. El admin desactiva un vehículo en vez de borrarlo, con Soft Deletes, y lo reactiva desde la lista. Como nunca se borra, uno con certificaciones tampoco se elimina (resuelto el 2026-11-11 en T42: no hay botón de eliminar).
  5. Hay una lista, ordenada por placa, con búsqueda por placa o marca y filtros por tipo, carrocería, categoría y estado.
  6. Un vehículo se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17). H23 deja listo el Service `VehicleRegistry`, que lo busca por placa y, si no existe, lo registra con su marca, categoría, tipo y carrocería; si la carrocería tampoco existe, la crea. H17 solo lo llama.
  7. Ya no hay marca "por revisar": lo que manda SIGLA 1.0 va directo al catálogo (cambiado el 2026-11-11 a pedido del usuario).
  8. Solo el admin crea, edita y desactiva: pasa a H24. Hasta culminar E6, cualquier usuario con sesión la usa (decisión del 2026-11-03).
  9. La pantalla se puede usar bien desde el celular.
- El vehículo no queda ligado a ningún transportista.
- En el menú van Vehículos y Carrocerías en Maestros → Transporte, debajo de Conductores.
- Diseño: aprobado por el usuario el 2026-11-11 en la página "E9 · Transportistas, conductores y vehículos" del lienzo.

### E5 · Certificación del contenedor
- Que el inspector certificado certifique el contenedor con su kit. Aprobado hasta aquí: iniciar la certificación con la solicitud de retiro y la funda del kit (H17). Lo posterior (colocar los dispositivos, fotos por sección, cerrar, consultar, PDF y reinspección) está EN REVISIÓN.
- La certificación es opcional (ver decisiones). El inspector decide a qué unidades aplicarla.
- Fuera: EIR del gate out y fin de lavado (proceso de gate out de SIGLA, aún no se construye).
- Depende de: E1, E2, E3, E4, E7, E9. La necesita E6.

**H17 · Iniciar una certificación** (aprobada)
- Como inspector certificado, quiero iniciar una certificación eligiendo la solicitud de retiro y el kit que me despacharon, para empezar a certificar esa unidad.
- Criterios de aceptación:
  1. El inspector busca la solicitud con el número de contenedor y el booking (la consulta de H05) y la elige.
  2. Al elegirla, el sistema consulta SIGLA 1.0 una sola vez y guarda en la certificación los datos de la solicitud: cliente, producto, booking, naviera y contenedor, además de la referencia al despacho.
  3. El transportista, el conductor, el remolcador y el semirremolque se enlazan con sus catálogos (E9). Si ya existen, se enlazan; si no, se crean solos. Se reconocen por RUC, DNI y placa.
  4. Si falla algo al guardar, no queda una certificación a medias. Se guarda todo o nada.
  5. Si SIGLA 1.0 no responde, se muestra un mensaje claro y el inspector puede reintentar.
  6. Una solicitud de retiro tiene una sola certificación. Si ya existe, se muestra esa en vez de crear otra. La reinspección reabre esa misma certificación (decisión del 2026-11-04).
  7. El inspector busca su kit escaneando o escribiendo el código de la funda. Solo encuentra kits despachados al trabajador que tiene su mismo tipo y número de documento, y ve su contenido como información.
  8. Un kit solo se usa en una certificación (por confirmar).
  9. Queda registrado quién certifica, la fecha y la hora de inicio.
  10. Al iniciar, el inspector pasa al formulario por pasos de las secciones (depende de H18, que está en revisión).
  11. Los datos personales del conductor (DNI y brevete) solo los ven los roles que los necesitan. No es asesoría legal.
  12. La pantalla se puede usar bien desde el celular.
  13. Al iniciar, el inspector elige la zona de patio, del catálogo de H26 (agregado el 2026-11-04).
- La naviera que trae la API se copia como texto y código, sin enlazarla al catálogo de E8, así que no hace falta que exista en él (decisión del 2026-11-04). Cliente y producto no tienen catálogo.
- **Propuesta de cambio (2026-11-10, por aprobar en el PR de H05):** la naviera ya no se copia: se enlaza a `shipping_lines`, y el código es el de SIGLA 2.0 (la API solo trae el nombre, por ejemplo `SEATRADE`). Antes de construir H17 falta decidir cómo se reconoce la naviera de la API en el catálogo y qué pasa si no existe. El cliente es el operador de la solicitud, y el tipo de vehículo sale de `carroceria_tracto` y `carroceria_carreta` (falta la lista completa de valores).

**H18 · Completar las secciones con fotos (EN REVISIÓN, no aprobada)**
- Versión conversada, a revisar: el inspector recorre las secciones una por una (multi-step form, en el orden de H11), ve qué dispositivos y cuántos lleva cada una (H12) y toma las fotos; cada sección puede llevar más de una foto y necesita al menos una; el avance se guarda en cada paso; puede volver atrás y reemplazar fotos; puede dejar la certificación a medias.
- Dudas abiertas: cómo se colocan los dispositivos en cada sección (el sistema podría asignarlos por defecto desde el kit y el inspector solo etiquetaría), cuántas fotos por sección y cómo se reducen desde celular (unas 30 por unidad).

**H19 · Cerrar la certificación (EN REVISIÓN, no aprobada)**
- Versión conversada, a revisar: resumen de secciones con y sin foto; todas las secciones obligatorias para cerrar; confirmación antes de cerrar; una certificación cerrada no se edita; el kit y sus dispositivos quedan usados; solo cierra quien la inició (por confirmar).
- Riesgo: si una sección no se puede fotografiar por una avería, el inspector queda trabado.

**H20 · Consultar certificaciones (EN REVISIÓN, no aprobada)**
- Versión conversada, a revisar: lista filtrable por fecha, naviera, inspector y estado; búsqueda por contenedor, booking o placa; detalle con los datos guardados de la solicitud, transportista, conductor, vehículos, kit y fotos por sección; solo consulta; datos personales del conductor visibles solo para roles que los necesitan; quién ve todas o solo las suyas (por confirmar).
- Pendiente: la generación del PDF de la certificación (hoy ya existe un formato "Registro de contenedores" con contenedor, fecha, horas y cintas por zona) y en qué momento se genera.

### E6 · Checkpoint (EN REVISIÓN, sin historias aprobadas)
- Validación del contenedor ya cargado, fuera del depot, antes de llegar al puerto de embarque. Solo aplica a unidades que fueron certificadas.
- Lo conversado, a revisar: buscar la certificación cerrada sin checkpoint cerrado, validar las series de las cintas y sellos de las partes externas (marcando cada una como correcta o no), subir foto por ítem y fotos de la unidad (tracto, semirremolque, puertas), iniciar la reinspección con su motivo, bloquear el cierre mientras la unidad esté en reinspección.
- Flujo de reinspección conversado: (1) checkpoint la inicia, (2) bodega carga la nueva asignación [E4], (3) el inspector certificado reinspecciona [E5], (4) checkpoint valida y cierra. Las secciones se reemplazan completas.
- Fuera de SIGLA: aviso por otros medios de que algo viene roto; retiro del candado GPS.
- Depende de: E1, E3, E5 (y usa partes de E4).

## Fuera del Product Goal (ideas para después, sin historias)
- Estimaciones.
- Gestión de patio y stock. (El catálogo de zonas de H26 sí entra, solo como lista para elegir.)
- Gate out (EIR del gate out, fin de lavado) y la regla de exigir certificación al cerrar un gate out.
- Técnicos reefer y estructura, customer, inspector estructura gate in.
- Extranet: operadores y sus usuarios.
- Tipos de contenedor distintos de 40'.

## Suposiciones por confirmar
- Los inspectores siempre tienen internet.
- SIGLA solo registra el resultado del checkpoint; el aviso de cinta rota se hace fuera del sistema.
- Cómo se migra lo que hoy está en BUMMOCK, y si hacen falta reportes o historial por contenedor.
- Si el despacho de dispositivos sueltos a una zona de patio (que aparece en el ER) sirve para la reinspección.
