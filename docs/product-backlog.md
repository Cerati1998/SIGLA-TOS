# Product Backlog · SIGLA 2.0 (TOS)

**Estado:** en construcción. Épicas aprobadas el 2026-10-26. Historias aprobadas el 2026-10-27: E1 (H01 a H04), E7 (H05), E8 (H09, H10), E2 (H06 a H08), E3 (H11, H12), E4 (H13 a H16), E9 (H21 a H23) y E5 (solo H17). El 2026-11-20 se aprobaron H18, H19 y H20 (E5).

**Propuestas del 2026-11-13, pedidas por el usuario:** H29 (activar dispositivos para despacho, E2) y H30 (despachar dispositivos a granel, E4). Sus "por confirmar" se cerraron en T46 y el usuario las aprobó el 2026-11-13.

**Pedida por el usuario el 2026-11-17:** H31 (gestionar clientes, E8). La necesita H30: un despacho a granel por venta se hace a una empresa cliente.

**En revisión desde el 2026-10-28:** el proceso posterior a la asignación del kit a la certificación (H18, H19 y H20 de E5, y toda E6). El límite acordado es H17: se queda lo que llega hasta iniciar la certificación y asignarle el kit. Lo posterior se vuelve a revisar con el usuario antes de darlo por aprobado. Lo conversado queda marcado y no se borra.

**Revisadas el 2026-11-19 en T54:** H18, H19 y H20 quedaron aprobadas el 2026-11-20, para construirse en el Sprint 3 (T59 a T61). E6 sigue en revisión y pasa al Sprint 4.

**Aprobadas el 2026-11-23:** H33 a H36 (E6, checkpoint), armadas con el usuario a partir del video del checkpoint actual. Sus suposiciones se cerraron y el usuario las aprobó ese mismo día (T67).

**Pedida y aprobada por el usuario el 2026-11-24:** H37 (ingresar un anuncio de ingreso de lotes, E2), que cambia H07, H08, H28 y H29: un anuncio de ingreso de lotes trae n lotes y cada lote, n dispositivos. Se construye en el Sprint 4 (T78).

**Pedida y aprobada por el usuario el 2026-11-24:** H38 (reemplazar dispositivos del kit con dispositivos extra, E5), que sale de la reunión de correcciones del 2026-11-23 y cambia H18, H19, H20 y H30; la usa H35. Se construye en el Sprint 4 (T79), antes de la reinspección.

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
- Regla común de escaneo: todo se escanea (dispositivos y kits tienen código propio; el kit lleva funda con código de barras). Hoy el escáner llena el campo activo y el dato se guarda solo, de forma asíncrona, sin botón de guardar. SIGLA 2.0 debe tener esa misma capacidad. La pantalla muestra qué escaneos se guardaron y cuáles fallaron, para poder reintentar. El escáner funciona como teclado: escribe la serie en el campo activo y termina con Enter, y SIGLA agrega o busca al recibir el Enter (confirmado por el usuario el 2026-11-13, T46).
- Puente con SIGLA 1.0 (decisión revisada el 2026-10-27): al iniciar una certificación se consulta SIGLA 1.0 una sola vez y SIGLA 2.0 guarda en la certificación una copia permanente de los datos de la solicitud (cliente, producto, booking, naviera y contenedor) más la referencia al despacho (`source` = api mientras dependa de SIGLA 1.0; `web` cuando SIGLA 2.0 tenga su módulo de solicitudes y despachos y apunte directo a su tabla de solicitudes u operaciones). Así la certificación y cualquier PDF futuro no dependen de que SIGLA 1.0 siga existiendo. Los datos llegan validados desde una operación real, casi al final del despacho.
- Transportista, conductor y vehículos (remolcador y semirremolque) tienen catálogos propios (E9) y son independientes entre sí: un conductor o vehículo no depende de un transportista (pueden cambiar de transportista o tercerizar). La relación entre ellos se guarda en cada certificación. **Cambiado el 2026-11-11 (T45, corrección de H22):** los conductores van por empresa transportista, porque en la extranet cada transportista registrará los suyos. Un conductor puede estar en varios transportistas (tabla puente `carrier_driver`), se registra una sola vez con su DNI y se desactiva en cada uno por separado. Los vehículos siguen independientes. Al iniciar la certificación se hace un findOrCreate: si ya existen se enlazan; si no, se crean. Se reconocen por RUC (transportista), DNI (conductor) y placa (vehículo). El número de documento y de brevete no cambian.
- Datos que entrega la API de SIGLA 1.0: transportista (RUC, razón social); conductor (DNI, brevete, nombres y apellidos); vehículo (marca, categoría —N1, N2, N3, O1, O2, O3…—, placa, y tipo de vehículo como texto: remolcador, semirremolque plataforma o semirremolque contenedora). Cliente y producto no tienen catálogo y se guardan como datos de la certificación.
- Las líneas navieras y los proveedores son tablas maestras de SIGLA 2.0, con su propio módulo (E8).
- Por ahora solo se manejan contenedores de 40'. Lo que pide cada sección es igual para todos. Si entran otras medidas, se amplía.
- La certificación es un proceso OPCIONAL que va después del EIR del gate out (el EIR se hace a todas las unidades y todavía no entra en SIGLA 2.0). Se practica solo a algunas navieras. SIGLA 2.0 no valida ni obliga si una unidad debe certificarse ni impide cerrar un gate out sin certificación; el inspector decide. Esa regla sería una historia nueva cuando se migre el gate out. No seremos minuciosos en ese detalle por ahora.
- Lo que pide cada sección (tipo de dispositivo y cantidad) no existe en BUMMOCK; el técnico lo tiene interiorizado. Es una oportunidad de mejora de SIGLA 2.0.
- Recorrido del kit: (1) bodega crea el kit y le escanea dispositivos, (2) bodega lo despacha a un trabajador (H25), (3) el inspector busca la funda en su app e inicia la certificación asignándole ese kit. **Lo que ocurre después de este paso (cómo se colocan los dispositivos, fotos, cierre) está EN REVISIÓN.**
- Una certificación usa un solo kit. "El ticket" que se asigna al iniciar es la solicitud de retiro.
- Activación para despacho (decisión del usuario del 2026-11-13, H29): bodega envía por correo, fuera de SIGLA, las series a la naviera para que las active. Cuando la naviera responde que sí, en SIGLA se activan por rango. Es una marca aparte del estado del dispositivo, y es obligatoria para armar un kit y para todo despacho, de un kit o a granel. (Corrección del 2026-11-20, T62: no aplica a los tipos de dispositivo que no requieren aprobación en lote, H06.)
- Datos personales (DNI y brevete): solo los ven los roles que los necesitan. No es asesoría legal.
- El modelo de datos se documenta en `docs/database.md`, que parte del ER del usuario y es la fuente de verdad. Claude Code lo mantiene durante la Construcción (cambiado el 2026-11-04).
- Los kits se despachan a trabajadores (H25), propios o de un proveedor. Un trabajador no se enlaza directamente a un usuario. El trabajador que recibe solo queda registrado en el despacho: no limita qué kits ve el inspector, que puede escanear cualquier kit Despachado (cambiado por el usuario el 2026-11-17, en H15; antes el inspector se reconocía comparando el tipo y número de documento de su usuario con los del trabajador, decisión del 2026-11-04).
- Por ahora hay una sola empresa (`companies`), cargada por seeder y modificable en su pantalla (H27), y cada sucursal pertenece a ella. Las sucursales también se gestionan en pantalla (H27). Si el sistema escala a multitenancy, se pasa a varias empresas (decisión del usuario del 2026-11-04).
- Hosting y despliegue (decisión del usuario del 2026-11-18, T57): SIGLA 2.0 se aloja en un cPanel (con SSH, PHP 8.4 (la versión más alta que ofrece; confirmado por el usuario el 2026-11-18) y MySQL 8.0 (verificado en el servidor el 2026-11-19); Supervisor lo instaló el proveedor el 2026-11-19: la cola corre con un worker `queue:work` bajo Supervisor, y las tareas programadas, por cron). Se despliega con CI/CD en GitHub Actions: las pruebas corren en cada PR y en cada push a `main`, y el código se sube al servidor por SSH solo cuando el usuario lanza el despliegue a mano desde Actions (decisión del usuario del 2026-11-20; antes se desplegaba solo al pasar las pruebas en `main`). El despliegue manual solo acepta `main` y no espera a las pruebas: antes de lanzarlo hay que revisar que pasaron. Cada entorno es un environment de GitHub. Como secreto solo va la clave privada SSH (`SSH_PRIVATE_KEY`). Como variables van el sitio (`APP_URL`), `SSH_HOST`, `SSH_PORT`, `SSH_USER` y `DEPLOY_PATH`; la huella del servidor (`SSH_KNOWN_HOSTS`) es opcional y, si falta, el despliegue la toma en cada ejecución. El `.env` vive solo en el servidor y no pasa por GitHub. La URL final es `https://sigla-tos.macromarlogistics.com` (confirmada por el usuario el 2026-11-18). Sin acceso root, los dominios solo pueden apuntar dentro de `public_html`: el código va fuera de `public_html` (`DEPLOY_PATH`) y la carpeta del subdominio dentro de `public_html` es un enlace simbólico a `current/public`. Se despliega con el patrón de releases (decisión del usuario del 2026-11-18): cada despliegue va a su propia carpeta en `releases/`, `current` apunta a la activa y `shared/` guarda lo que sobrevive entre releases (`.env`, `storage/` y los ajustes de PHP de cPanel). Se guardan las últimas 5 releases, y para volver a una anterior basta con cambiar el enlace `current`.
- Prueba con el cliente en producción (decisión del usuario del 2026-11-18, T57): no hay un entorno de prueba aparte. Mientras se construye, el cliente prueba lo último que se desplegó desde `main` en el dominio final y con la base final, cargada solo con datos de prueba (sin DNI ni brevetes reales). Al terminar las pruebas se corre a mano `migrate:fresh` y solo los seeders necesarios, y desde ahí la base guarda datos reales. El despliegue nunca corre `migrate:fresh` ni seeders.
- EN REVISIÓN (conversado, no aprobado): todas las secciones obligatorias para cerrar una certificación, con al menos una foto cada una y varias fotos permitidas por sección.

## Notas para la Construcción (desde el ER del usuario)
- Falta qué dispositivos y cuántos pide cada sección (H12). El ER ya trae la tabla con su cantidad y clave compuesta. Se podría renombrar `certificacion_criteria_items` (el ER nuevo ya lo llama `certification_criteria_items`). (Resuelto el 2026-11-04: se llamará `certification_sections`.)
- El tipo de kit guarda la cantidad por tipo de dispositivo en el ER nuevo (apoya H13, criterio 7). (Confirmado por el usuario el 2026-11-12.)
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
  6. Por definir: qué rol puede anular la asignación de un kit (H32) y qué rol puede descartar dispositivos de un lote (T65). Pedido por el usuario el 2026-11-20.
- Usa `laravel-permission`, que se instala recién en esta historia (indicado por el usuario el 2026-11-03).
- Supuesto: un rol por usuario.

**H27 · Modificar la empresa y gestionar sus sucursales** (propuesta del 2026-11-04, aprobada el 2026-11-13, ya construida; las sucursales se sumaron a pedido del usuario el 2026-11-04)
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

### E8 · Maestros: líneas navieras, proveedores y clientes
- Que el admin gestione los catálogos de líneas navieras, proveedores y clientes, para usarlos al ingresar lotes (E2), armar kits y despachar a granel (E4) y en lo que venga después (estimaciones, extranet, depósito temporal). Los clientes se sumaron el 2026-11-17 (H31).
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
- En el menú va en el grupo "Maestros", debajo de Líneas navieras (confirmado por el usuario el 2026-11-09). Desde H31 va dentro del desplegable «Terceros», junto a Clientes (cambiado por el usuario el 2026-11-17).
- Diseño: aprobado por el usuario el 2026-11-04 en la página "E8 · Maestros" del lienzo, con un botón naranja de lupa siempre pegado al número de documento.
- Reglas de la consulta, aprobadas con el diseño: el formato incorrecto se avisa al salir del campo; mientras consulta, Guardar y Razón social quedan desactivados; con DNI, los nombres y apellidos van en Razón social; el documento duplicado se avisa al pulsar Guardar; al editar solo se vuelve a consultar si cambia el número; los tipos de documento son solo RUC y DNI.
- Servicio de consulta de RUC y DNI: Decolecta, elegido por el usuario, mediante `SunatService` (T22). También lo usarán H21 y H22.
- El DNI viaja en la URL de Decolecta, por HTTPS, porque ese servicio solo lo recibe así, y nunca va en nuestros logs. Es una excepción a la regla de datos personales, aceptada por el usuario el 2026-11-04.
- El ER guarda además cuenta corriente, detalle del servicio y sucursal; se dejaron fuera por no necesitarse en este módulo.

**H31 · Gestionar clientes** (pedida por el usuario el 2026-11-17, en T52)
- Como usuario de la intranet, quiero crear, editar, desactivar y reactivar clientes, para elegirlos al despachar dispositivos a granel por venta (H30).
- Criterios de aceptación:
  1. Se crea un cliente con tipo y número de documento, razón social, dirección, correo, teléfono y condición de pago. Son obligatorios el tipo y el número de documento, la razón social y la condición; dirección, correo y teléfono son opcionales, como en H10.
  2. La condición de pago es Contado o Crédito. Al crear un cliente viene marcada Contado. Al elegir Crédito aparece el campo «Días de crédito», obligatorio, un número entero de 1 a 365; con Contado el campo se oculta y los días no se guardan (pedido por el usuario el 2026-11-17, al revisar la construcción).
  3. No se pueden crear dos clientes con el mismo tipo y número de documento, aunque uno esté desactivado.
  4. Se editan los datos de un cliente, también su condición.
  5. Un cliente se desactiva en vez de borrarlo, con Soft Deletes, y se reactiva desde la lista. Uno desactivado no aparece al despachar, pero se conserva en lo que ya existe.
  6. Hay una lista, ordenada por razón social, que se puede buscar por razón social o número de documento y filtrar por estado. La lista muestra también la condición, y con Crédito sus días (por ejemplo «Crédito · 30 días»).
  7. La consulta del RUC o el DNI funciona como en H10 (criterios 7 a 9): completa los datos que encuentre, se puede llenar a mano si el servicio falla y el formato incorrecto se avisa antes de consultar.
  8. La pantalla se puede usar bien desde el celular.
- Decisión del usuario del 2026-11-17: es una copia del modelo de proveedores (H10) con un solo atributo más, la condición de pago (Contado o Crédito, por defecto Contado), y los días cuando es a crédito. Reusa `SunatService` y las mismas reglas de la consulta.
- Quién la usa: por ahora, cualquier usuario con sesión. Los roles pasan a H24.
- En el menú va en el grupo "Maestros", dentro de un desplegable nuevo, «Terceros», que reúne Proveedores y Clientes, en ese orden (decisión del usuario del 2026-11-17). El desplegable queda donde estaba Proveedores, debajo de Líneas navieras.
- Diseño: el de H10, con el campo Condición. Se agrega al lienzo para aprobarlo antes de construir.
- Fuera de esta historia: pasar los clientes de SIGLA 1.0 y usarlos en la certificación (H17), donde el cliente de la solicitud sigue siendo un dato copiado de la API.

### E2 · Dispositivos de seguridad
- Que bodega registre dispositivos por lote, consulte su estado y los active para despacho cuando la naviera lo confirma (H29, agregado el 2026-11-13).
- Depende de: E1 y E8 (H07 usa naviera y proveedor). La necesitan E4 y E5.
- Un anuncio es el origen del lote (de quién viene y cómo se identifican sus series); se crea uno por cada lote, así que bodega no lo gestiona aparte. (Cambiado el 2026-11-24 en H37, a pedido del usuario: el anuncio es el ingreso de bodega, con la naviera, el proveedor, la guía y la fecha, y trae uno o más lotes; el tipo, la serie y el rango son de cada lote.)
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
- Corrección del 2026-11-16 (T55), pedida por el usuario: la cinta interna y la externa son un solo tipo, la **cinta de inspección**. El seeder carga solo esta. Al crear y editar un tipo hay una casilla, «Se reparte entre asignación interna y externa», que hace que el tipo de kit pida su cantidad repartida (H13). El seeder la marca solo en la cinta de inspección. Se puede cambiar al editar.
- Corrección del 2026-11-20 (T62), pedida por el usuario: al crear y editar un tipo hay dos casillas más, que se validan al ingresar un lote (H07):
  - «No utiliza serie», desmarcada por defecto. Si se marca, el lote de ese tipo no pide el inicio ni el final de serie: pide el mínimo de dígitos y el correlativo inicial y el final, y la serie es solo el correlativo con ceros (ver H07).
  - «Requiere aprobación en lote», marcada por defecto. Si se desmarca, los dispositivos de los lotes de ese tipo no necesitan la aprobación de la naviera (H29) para armar un kit (H14) ni para despacharlos a granel (H30).
  - Las dos se ven en la lista de tipos. Los tipos que ya existen quedan con los valores por defecto (usan serie y requieren aprobación), así que nada cambia hasta que alguien los marque. Las casillas se pueden cambiar al editar solo mientras el tipo no tenga lotes. Cuando ya tiene uno, quedan bloqueadas con un aviso, como el tipo (criterio 3), y si el tipo se creó mal se crea otro (confirmado por el usuario el 2026-11-20).
- Corrección del 2026-11-24 (T76), pedida por el usuario el 2026-11-23: hay una categoría más, **Funda** (criterios 1 y 3: sello, precinto, cinta, funda u otro). La funda del kit es un dispositivo de esa categoría: llega en un lote de su naviera (H07) y pasa la aprobación de la naviera si su tipo la requiere (H29), como los demás (confirmado por el usuario el 2026-11-24). No hay «lote de tipo bolsa»: todo es Funda. El seeder carga el tipo «Funda». Diseño: «T76 · Funda en el kit», página «E4 · Kits» del lienzo, aprobado por el usuario el 2026-11-24.

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
  8. Las unidades quedan en estado Inactivo hasta que se registre la aprobación de la naviera (H29). (Cambiado el 2026-11-16 en H29; antes quedaban Disponibles, confirmado el 2026-11-09 en T27.)
  9. Queda registrado quién ingresó el lote y cuándo.
  10. Un lote trae como máximo 10 000 series: el sistema no deja ingresar un rango mayor. Se guarda en el momento, sin cola, y la pantalla muestra que está guardando (confirmado el 2026-11-09, T27).
  11. La pantalla se puede usar bien desde el celular.
- Quién ingresa lotes: por ahora, cualquier usuario con sesión. "Solo bodega y admin" pasa a H24 (confirmado el 2026-11-09, T27).
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Formulario" (T30, aprobado por el usuario el 2026-11-10).
- En el menú va en el grupo "Bodega", como "Lotes de dispositivos" (decisión del usuario del 2026-11-10).
- Una carga desde Excel con series sueltas sería otra historia (no pedida por ahora).
- Corrección del 2026-11-20 (T62), pedida por el usuario, según las dos casillas nuevas del tipo de dispositivo (H06):
  - **Tipo que no utiliza serie.** El formulario oculta el inicio y el final de serie y pide el mínimo de dígitos como ahora, más el correlativo inicial y el final, que son obligatorios. La serie de cada dispositivo es solo el correlativo con ceros a la izquierda hasta ese mínimo, sin letras: mínimo 6, del 25 al 30 da 000025 a 000030 (confirmado por el usuario el 2026-11-20). La cantidad, la vista previa y el máximo de 10 000 siguen igual (criterios 3, 4 y 10). Una serie es única dentro de su tipo de dispositivo: dos lotes del mismo tipo no pueden traer la misma serie, y si un lote trae una que ya existe en su tipo, no se guarda nada y se muestra cuáles chocan (criterio 6). Dos tipos distintos sí pueden tener la misma serie: por ejemplo, un candado y un sello pueden ser 000050. Esta regla vale solo para los tipos que no utilizan serie. Los tipos con serie siguen como hoy: su serie es única en todo SIGLA, así que un GSW0000200 no puede existir en dos tipos. Cuando una serie existe en dos tipos sin serie, la búsqueda de dispositivos (H08) muestra todas las coincidencias, y el despacho a granel (H30) usa un selector de tipo opcional (ver H08 y H30). Todo esto lo confirmó el usuario el 2026-11-20.
  - **Tipo que no requiere aprobación en lote.** Sus dispositivos nacen **Disponibles**, no Inactivos (excepción al criterio 8), y se pueden usar de inmediato para armar un kit o despacharlos a granel.
  - El formulario cambia en cuanto se elige el tipo: con un tipo sin serie se ocultan el inicio y el final de serie, y con un tipo sin aprobación se avisa que los dispositivos quedarán Disponibles. Diseño: se ajusta en la página "E2 · Dispositivos de seguridad" del lienzo y se aprueba antes de construir.
- Corrección del 2026-11-24 (T75), pedida por el usuario el 2026-11-23: el formulario suma el campo **Cantidad**, entre el correlativo inicial y el final. Con el inicial y la cantidad, el final se calcula solo (inicial + cantidad − 1: del 200 con 200 da 399). Si se escribe el final a mano, la cantidad se recalcula; si cambia el inicial y hay cantidad, se mantiene la cantidad y se mueve el final (confirmado por el usuario el 2026-11-24). La cantidad no se guarda: sale del rango, y el servidor valida el rango como antes (criterios 3 y 10). Vale también para los tipos que no utilizan serie y al corregir un lote (H28). Diseño: página "E2 · Dispositivos de seguridad" del lienzo, aprobado por el usuario el 2026-11-24.
- Cambio del 2026-11-24 (H37), pedido por el usuario: el lote ya no se ingresa solo. Se ingresa dentro de un anuncio: la naviera, el proveedor, la fecha de recepción y el número de guía pasan a la cabecera del anuncio, y cada lote lleva el tipo, la serie, los correlativos y las observaciones. Las reglas de las series de esta historia siguen valiendo para cada lote. El máximo de 10 000 series (criterio 10) pasa a ser por anuncio. Ver H37.

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
- Estados de un dispositivo: Disponible, En un kit, Usado y Dañado. Por ahora ninguna historia marca un dispositivo como Dañado; el estado queda definido para cuando una lo use (confirmado el 2026-11-09, T27). Desde el 2026-11-13 (T46) hay un quinto estado, Despachado, para los dispositivos despachados a granel (H30). Desde el 2026-11-16 (H29) hay un estado Inactivo, con el que nace cada dispositivo hasta que se registra la aprobación de la naviera; reemplaza a la marca aparte de activación que se había acordado el 2026-11-13.
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Lista con tabla" (T30, aprobado por el usuario el 2026-11-10).
- Vive en la misma entrada del menú que H07: grupo "Bodega", "Lotes de dispositivos".
- Corrección del 2026-11-20 (T65), pedida por el usuario:
  - La columna «Kit» de los dispositivos del lote muestra la funda del kit en que está el dispositivo, en cualquier estado. Hasta ahora quedaba siempre en «—».
  - Cada dispositivo del lote se puede **descartar**, por ejemplo por llegar en mal estado o romperse después: de un lote de 100 se pueden descartar 5. Hay un estado nuevo, **Descartado**.
  - Solo se descarta un dispositivo Inactivo o Disponible que no esté en un kit ni despachado. Se pide un motivo obligatorio y queda registrado quién lo descartó y cuándo. El descarte no se deshace.
  - Un dispositivo Descartado no se agrega a un kit (H14) ni se despacha a granel (H30). El detalle del lote lo cuenta aparte.
  - Descartar cambia el estado del dispositivo, así que la serie del lote ya no se puede corregir (H28, criterio 3).
  - Hay un segundo camino de descarte, desde un kit cuya asignación se anuló (H32).
- Corrección del 2026-11-24 (T77), pedida por el usuario el 2026-11-23: el descarte pide, además del motivo, una **foto obligatoria**, tomada con la cámara y comprimida en el navegador como en H18, con un visor para verla en grande, volver a tomarla o quitarla. Cada dispositivo Descartado del detalle del lote tiene el botón «Ver descarte», y solo esos: abre un modal con el motivo, la foto (que se amplía) y quién lo descartó y cuándo (confirmado por el usuario el 2026-11-24). No está en la búsqueda por serie. Diseño: «T77 · Foto al descartar un dispositivo», página «E2 · Dispositivos de seguridad» del lienzo, aprobado por el usuario el 2026-11-24.
  - Diseño: desde las plantillas, sin lienzo propio (decisión del usuario del 2026-11-20): un botón rojo por fila y un modal con el motivo, como «Reaperturar» de H19.
- Corrección del 2026-11-20 (T62), decidida por el usuario: como dos tipos que no utilizan serie pueden tener la misma serie (H07), al buscar una serie se muestran todas las coincidencias, cada una con su tipo y su lote.
- Cambio del 2026-11-24 (H37), pedido por el usuario: la lista de lotes (criterios 1 y 2) pasa a ser una lista de anuncios, y los lotes se ven en el detalle de su anuncio. El detalle del lote (criterios 3, 5 y 6), la búsqueda por serie (criterio 4), la aprobación (H29) y el descarte siguen como están. Ver H37.

**H28 · Corregir un lote** (propuesta del 2026-11-10, pedida por el usuario al revisar H08; reglas 2 y 3 confirmadas por el usuario el mismo día)
- Como usuario de bodega, quiero corregir los datos de un lote ya ingresado, para arreglar un error de digitación sin borrar el lote y volver a ingresarlo.
- Criterios de aceptación:
  1. El detalle del lote tiene un botón "Editar", y cada fila de la lista de lotes tiene uno al lado de "Ver" (pedido por el usuario el 2026-11-10). Los dos abren el formulario de H07 con los datos del lote.
  2. Naviera, proveedor, tipo de dispositivo, fecha de recepción, guía y observaciones se pueden corregir siempre.
  3. Inicio de serie, mínimo de dígitos, correlativos y final de serie solo se pueden corregir si ningún dispositivo del lote cambió de estado (todos siguen Inactivos; antes de H29, Disponibles). Si alguno cambió, esos campos se muestran bloqueados con un aviso que explica por qué.
  4. Al cambiar la serie, el sistema rehace todas las series del lote con las reglas de H07: vista previa, correlativo inicial no mayor que el final, máximo 10 000 series y, si alguna serie nueva ya existe en otro lote, no se guarda nada y se muestra cuáles chocan.
  5. El guardado es todo o nada.
  6. Al guardar, se vuelve al detalle del lote con un aviso.
  7. La pantalla se puede usar bien desde el celular.
- Quién corrige lotes: por ahora, cualquier usuario con sesión, como en H07. Los roles pasan a H24.
- Diseño: página "E2 · Dispositivos de seguridad" del lienzo, desde la plantilla "Formulario" (T36, aprobado por el usuario el 2026-11-10).
- Corrección del 2026-11-20 (T62): al corregir un lote se aplican las mismas reglas de H07 según las casillas de su tipo (H06). En un lote cuyo tipo no requiere aprobación, el criterio 3 se cumple si todos sus dispositivos siguen Disponibles (ninguno en un kit ni despachado). El tipo de dispositivo solo se puede cambiar por otro con las mismas dos casillas («No utiliza serie» y «Requiere aprobación en lote»). Si las casillas son distintas, el cambio se bloquea con un aviso que explica por qué. Así, cambiar el tipo nunca toca el estado ni las series de los dispositivos ya ingresados (confirmado por el usuario el 2026-11-20).
- Corrección del 2026-11-24 (T75): el formulario trae la **Cantidad** de H07, calculada desde el rango del lote. Cuando la serie está bloqueada (criterio 3), la cantidad también.
- Cambio del 2026-11-24 (H37), pedido por el usuario: se corrige el anuncio. La naviera, el proveedor, la fecha y la guía (criterio 2) se corrigen siempre en su cabecera. Cada lote se corrige con las reglas de esta historia (criterios 2 a 5: tipo, serie, rango y observaciones). Además, un lote se puede quitar del anuncio si ninguno de sus dispositivos cambió de estado, y el anuncio siempre queda con al menos un lote. Ver H37.

**H29 · Activar dispositivos para despacho** (pedida por el usuario el 2026-11-13; "por confirmar" cerrados en T46 y aprobada ese mismo día)
- Como usuario de bodega, quiero registrar la aprobación de la naviera por tramos de un lote, para que solo se armen kits y se despachen dispositivos que la naviera ya aprobó.
- Criterios de aceptación (cambiados el 2026-11-16, a pedido del usuario, antes de construir):
  1. Bodega envía las series a la naviera por correo, fuera de SIGLA. SIGLA no envía el correo ni lo guarda: solo registra la aprobación cuando llega la respuesta positiva.
  2. Un dispositivo nace **Inactivo** al ingresar su lote (H07): no está disponible. Al registrar la aprobación de la naviera pasa a **Disponible**. Inactivo es un estado más, no una marca aparte: Inactivo → Disponible → En un kit → Usado (o Despachado, H30, o Dañado).
  3. La aprobación se registra solo desde el detalle del lote (H08), con el correlativo inicial y el final de un tramo del lote. Ejemplo: de un lote de 500, del 100 al 149, y esos 50 pasan a Disponible. Un lote se aprueba por partes, en varios registros. No hay aprobación por serie ni desde la lista de lotes.
  4. El tramo debe estar dentro del lote y el correlativo inicial no puede ser mayor que el final; si no, no se registra nada y se avisa.
  5. Solo pasan a Disponible los dispositivos Inactivos del tramo. Los que ya estaban aprobados, en cualquier estado, se dejan como están y solo se cuentan.
  6. Se guarda en el momento, sin cola, y la pantalla muestra que está guardando. Un lote no pasa de 10 000 dispositivos (H07), así que un tramo tampoco.
  7. Antes de registrar se muestra una vista previa con cuántos dispositivos pasan a Disponible, la primera serie, la última y cuántos del tramo ya estaban aprobados. Si todos ya lo estaban, no hay nada que registrar.
  8. El registro es todo o nada.
  9. Una aprobación no se deshace en SIGLA: si la naviera responde que no, simplemente no se registra.
  10. Cada registro queda en una bitácora de aprobaciones del lote: fecha y hora, quién lo registró, el tramo (correlativo y serie, inicial y final) y cuántos pasaron a Disponible. Cada dispositivo aprobado queda enlazado a su registro.
  11. En el detalle del lote, «Historial de aprobaciones» abre un panel lateral con la bitácora de ese lote, del más reciente al más antiguo. No hay pantalla aparte.
  12. El detalle del lote cuenta los Inactivos junto a los demás estados, y la lista de lotes muestra una columna «Aprobados» con cuántos dispositivos ya no están Inactivos (por ejemplo, 120 de 200), con un filtro: sin aprobar, a medias y todo aprobado.
  13. Armar un kit (H14) y todo despacho, de un kit (H15) o a granel (H30), solo aceptan dispositivos Disponibles, es decir, ya aprobados.
  14. Por ahora, cualquier usuario con sesión registra aprobaciones. Los roles pasan a H24.
  15. La pantalla se puede usar bien desde el celular.
- Cambios del 2026-11-16, pedidos por el usuario antes de construir: la activación deja de ser una marca aparte y pasa a ser el estado Inactivo, con el que nacen los dispositivos; se quitan la aprobación del lote entero de una vez y la aprobación por serie inicial y final desde la lista de lotes (y con ellas la regla de huecos y la de varios lotes); se suma la bitácora de aprobaciones con su panel lateral en el detalle del lote. Si el tramo trae dispositivos ya aprobados, se aprueban solo los Inactivos y se cuentan los demás. Esto también cambia H07 (los dispositivos nacen Inactivos), H08 (el conteo y la columna) y H28 (un lote se corrige solo si todos sus dispositivos siguen Inactivos).
- Diseño: aprobado el 2026-11-13 en la página "E2 · Dispositivos de seguridad" del lienzo (T47). Se actualiza con los cambios del 2026-11-16 y espera una nueva aprobación.
- Los dispositivos ingresados antes de H29 pasan a Inactivos: bodega registra su aprobación por tramos antes de armar kits con ellos.
- Corrección del 2026-11-20 (T62), pedida por el usuario: la aprobación solo aplica a los lotes cuyo tipo de dispositivo tiene marcada «Requiere aprobación en lote» (H06), que es lo normal. Si el tipo no la requiere, sus dispositivos nacen Disponibles (H07), el detalle del lote no ofrece registrar aprobaciones y el criterio 13 se cumple sin aprobación. En la lista de lotes, su columna «Aprobados» dice «No requiere» en vez de «120 de 200», y el filtro de aprobación suma esa opción: sin aprobar, a medias, todo aprobado y no requiere. En el detalle del lote se ocultan «Registrar aprobación» e «Historial de aprobaciones» (confirmado por el usuario el 2026-11-20).
- Cambio del 2026-11-24 (H37): la aprobación sigue siendo por tramos de cada lote, desde el detalle del lote, sin cambios. La columna «Aprobados» y su filtro (criterio 12) pasan de la lista de lotes a la tabla de lotes del detalle del anuncio, sin el filtro. Ver H37.

**H37 · Ingresar un anuncio de ingreso de lotes** (pedida por el usuario el 2026-11-23; armada y aprobada con el usuario el 2026-11-24; cambia H07, H08, H28 y H29)
- Como usuario de bodega, quiero ingresar en un solo anuncio la guía de un proveedor con todos los lotes que trae, para registrar de una vez lo que llegó y consultarlo por anuncio, por lote y por dispositivo.
- Criterios de aceptación:
  1. Un **anuncio de ingreso de lotes** tiene una cabecera con la naviera, el proveedor, la fecha de recepción y el número de guía, todos obligatorios. Un anuncio es de una sola naviera: todos sus lotes son de ella.
  2. Debajo de la cabecera, un formulario agrega los **lotes** del anuncio. Cada lote lleva el tipo de dispositivo, la serie (inicio, mínimo de dígitos y final), los correlativos (inicial, cantidad y final) y las observaciones (opcional), con las mismas reglas de H07: solo tipos activos, tipos sin serie y sin aprobación (T62), la cantidad (T75) y la funda (T76).
  3. Cada lote agregado entra en una lista del anuncio con su tipo, su primera y su última serie y su cantidad. Antes de guardar, cada lote de la lista se puede ver (un modal de solo lectura con sus datos y las series que se van a crear, con un buscador) y quitar (pedido por el usuario el 2026-11-24, al revisar el diseño). Un anuncio puede traer lotes de tipos distintos.
  4. El anuncio se guarda con todos sus lotes juntos, todo o nada. Se necesita al menos un lote.
  5. Si alguna serie ya existe en el sistema, o dos lotes del mismo anuncio traen la misma serie, no se guarda nada y se muestra cuáles chocan (H07, criterio 6, con las reglas de unicidad de T62).
  6. Un anuncio trae como máximo 10 000 series, sumando todos sus lotes. Se guarda en el momento, sin cola, y la pantalla muestra que está guardando.
  7. Los dispositivos nacen Inactivos, o Disponibles si su tipo no requiere aprobación (H07, criterio 8, y T62).
  8. Queda registrado quién ingresó el anuncio y cuándo.
  9. Bodega → la lista de **anuncios** muestra, por cada uno, su número, la naviera, el proveedor, la guía, la fecha de recepción, cuántos lotes trae y cuántos dispositivos. Se filtra por naviera, proveedor y fecha. El buscador encuentra un anuncio por su guía o por la serie de uno de sus lotes: una serie completa (GSW0000250) muestra el anuncio cuyo lote la trae en su rango, y solo el inicio (GSW) muestra los anuncios con lotes de esa serie. La serie va en el buscador, no en los filtros (decisión del usuario del 2026-11-24).
  10. El **detalle del anuncio** muestra su cabecera y sus lotes, cada uno con su tipo, su rango, su cantidad, la columna «Aprobados» (H29) y «Ver». Desde ahí se agregan más lotes a un anuncio ya guardado, con las reglas de los criterios 2 a 7, y el tope del criterio 6 cuenta también los lotes que ya tiene.
  11. El **detalle del lote** muestra sus dispositivos como hoy (H08): estados, aprobación por tramos (H29), su historial y el descarte (T65 y T77), con un enlace de vuelta a su anuncio.
  12. **Corrección:** la cabecera se corrige siempre. Cada lote se corrige con las reglas de H28. Un lote se puede quitar de un anuncio guardado si ninguno de sus dispositivos cambió de estado, con confirmación; el anuncio siempre queda con al menos un lote.
  13. Por ahora, cualquier usuario con sesión usa estas pantallas. Los roles pasan a H24.
  14. Las pantallas se usan bien desde el celular.
- Decisiones del usuario del 2026-11-24: la naviera va en la cabecera; todo se guarda junto; el tope de 10 000 es por anuncio; se pueden quitar lotes sin uso; la lista es de anuncios y la serie se busca en el buscador; se parte de cero con `migrate:fresh`, como en T76, y `LocalTestingSeeder` crea anuncios con varios lotes.
- Suposiciones confirmadas por el usuario el 2026-11-24: el anuncio se numera con su propia secuencia, `AN-000001`; la aprobación de la naviera (H29) sigue por tramos de cada lote, sin cambios; las observaciones van solo en cada lote, no en la cabecera; la entrada del menú sigue en el grupo «Bodega» y se llama «Ingreso de lotes»; los títulos y las migas de las vistas dicen «Anuncios de ingreso de lotes» (corregido por el usuario el 2026-11-24, al probar la construcción).
- Término (decisión del usuario del 2026-11-24): en pantalla se dice siempre «anuncio de ingreso de lotes», para no confundirlo con otros anuncios de ingreso que vendrán (de contenedores, de unidades). Al construir se registra en `docs/glossary.md`.
- Fuera de esta historia: la carga desde Excel, un PDF del anuncio y los roles (H24).
- Toca lo que usa E6: el lote y su anuncio están debajo de los kits, la certificación y el reparto en secciones. Cuando llegue a `main`, la rama del checkpoint lo trae.
- Diseño: «H37 · …», página «E2 · Dispositivos de seguridad» del lienzo, desde las plantillas «Formulario» y «Lista con tabla», aprobado por el usuario el 2026-11-24: el nuevo anuncio con sus tres bloques (anuncio, agregar lote y lotes del anuncio, con «Ver» y «Quitar»), sus estados, la lista, el detalle, la corrección del anuncio y de cada lote, y sus versiones móviles. Decisiones de diseño: los choques entre lotes del anuncio y el tope se revisan al agregar el lote, y las series que ya existen, al guardar; quitar un lote sin guardar no pide confirmación y uno guardado sí; «Agregar lotes» usa la misma pantalla con la cabecera en solo lectura; el anuncio y cada lote se corrigen por separado; el último lote no muestra «Quitar». En el celular, los lotes del detalle del anuncio se ven en tarjetas, como en el diseño, y no en la tabla con desplazamiento: es una excepción solo para esta vista; las rules no cambian (decisión del usuario del 2026-11-24).

### E3 · Secciones del contenedor
- Tabla gestionable de secciones (criterios de inspección): unas 20 internas y unas 10 externas.
- Depende de: E1 (y de H06 para H12). La necesitan E4, E5 y E6.

**H11 · Gestionar secciones de certificación**
- Como admin, quiero crear, editar, ordenar y desactivar las secciones que se inspeccionan en un contenedor, marcando si son internas o externas, para que la certificación y el checkpoint pidan siempre lo correcto.
- Criterios de aceptación:
  1. El admin crea una sección con nombre y tipo (interna o externa), por ejemplo "Fotografía de evaporadores".
  2. No se pueden crear dos secciones del mismo tipo con el mismo nombre, aunque una esté desactivada. Una interna y una externa sí pueden llamarse igual, por ejemplo «Puertas» interna y «Puertas» externa. (Cambiado el 2026-11-20 en T63, a pedido del usuario; antes el nombre no se repetía nunca.)
  3. El admin edita el nombre y el tipo de una sección.
  4. El admin define el orden en que aparecen las secciones; es el orden de los pasos del multi-step form de la certificación. Es un solo orden para internas y externas.
  5. El admin desactiva una sección en vez de borrarla. Una desactivada sale del orden y no aparece en certificaciones nuevas, pero se conserva en las ya hechas. Una nueva o reactivada va al final.
  6. Hay una lista que se puede filtrar por interna o externa.
  7. Cambiar las secciones no modifica certificaciones ya cerradas.
  8. Solo el admin usa esta pantalla. Hasta H24, cualquier usuario con sesión.
  9. La pantalla se puede usar bien desde el celular.
- Riesgo: agregar o desactivar una sección a mitad de una certificación en curso puede dejar el sistema inconsistente.
- Resuelto el 2026-11-12 (T34): las puertas no son un tercer tipo; son una sección externa más, con sus cintas y precintos (P1 de `database.md`). El usuario confirmó todos los supuestos de la construcción.
- Construida el 2026-11-10 (T35) con supuestos que se confirmaron en T34 el 2026-11-12: las puertas no son un tipo (solo interna o externa, P1 de `database.md`); el orden es uno solo para internas y externas y es el de los pasos (criterio 4); una sección desactivada sale del orden y no aparece en certificaciones nuevas (criterio 5); una nueva o reactivada va al final; el nombre no se repite aunque una esté desactivada; por ahora, cualquier usuario con sesión usa la pantalla (criterio 8, hasta H24). Se ordena arrastrando en escritorio y con subir y bajar en el teléfono, y se guarda al momento.

**H12 · Definir qué dispositivos lleva cada sección**
- Como admin, quiero indicar para cada sección qué tipos de dispositivo lleva y cuántos de cada uno, para que el inspector sepa qué colocar en cada sección durante la certificación.
- Criterios de aceptación:
  1. Al crear o editar una sección, el admin agrega uno o más tipos de dispositivo con su cantidad. Ejemplo: "Fotografía de evaporadores" lleva 2 sellos.
  2. Solo se pueden elegir tipos de dispositivo activos.
  3. Una sección puede llevar más de un tipo (ejemplo: 1 sello y 1 cinta).
  4. Una sección puede no llevar ningún dispositivo, por ejemplo si solo pide una foto. En la lista se ve como «Solo foto».
  5. La cantidad es un número entero mayor que cero.
  6. No se puede repetir el mismo tipo dos veces en una sección; se cambia su cantidad.
  7. El admin puede quitar un tipo de una sección o cambiar su cantidad.
  8. En la lista de secciones se ve qué lleva cada una de un vistazo.
  9. Cambiar lo que lleva una sección no modifica certificaciones ya cerradas.
  10. Esta regla es igual para todos los contenedores de 40'.
  11. Solo el admin usa esta pantalla. Hasta H24, cualquier usuario con sesión.
  12. La pantalla se puede usar bien desde el celular.
- Resuelto el 2026-11-12 (T34): no hay un punto único del contenedor. La puerta es una sección externa más y lleva sus dispositivos como cualquier otra.
- Decisiones del usuario del 2026-11-12: una sección puede ir sin dispositivos (criterio 4); el paso de los dispositivos va también al crear la sección, no solo al editar (criterio 1); desactivar un tipo de dispositivo no se bloquea aunque una sección lo use: la sección lo conserva, marcado, hasta que lo quiten.
- Diseño: página "E3 · Secciones del contenedor" del lienzo (T34, aprobado por el usuario el 2026-11-12): paso 2 en los modales de H11, con filas de tipo y cantidad, vista previa de lo que verá el inspector y columna «Lleva» en la lista.
- Supuesto de la construcción: la cantidad llega hasta 999, como en los tipos de kit.

### E4 · Kits
- Que bodega arme kits con dispositivos escaneados, los despache a un trabajador y los consulte. Desde el 2026-11-13 también despacha dispositivos sueltos, a granel (H30).
- Depende de: E1, E2, E3, E8. La necesita E5.

**H13 · Gestionar tipos de kit**
- Como admin, quiero crear tipos de kit indicando para qué tipo de contenedor son y qué tipos de dispositivo lleva cada uno, para que bodega solo pueda armar kits con lo que corresponde.
- Criterios de aceptación:
  1. El admin crea un tipo de kit con nombre, tipo de contenedor y los tipos de dispositivo que puede llevar.
  2. Solo se pueden elegir tipos de dispositivo activos.
  3. No se repite el mismo tipo de dispositivo dentro de un tipo de kit.
  4. No se pueden crear dos tipos de kit con el mismo nombre.
  5. El admin edita un tipo de kit. El cambio no modifica los kits ya creados.
  6. El admin desactiva un tipo de kit en vez de borrarlo, y lo reactiva desde la lista. Uno desactivado no aparece al crear kits.
  7. Cada tipo de dispositivo del tipo de kit lleva su cantidad, un entero mayor que cero, por ejemplo 6 precintos. Un tipo de kit lleva al menos un tipo de dispositivo.
  8. Solo el admin usa esta pantalla. Hasta H24, cualquier usuario con sesión.
  9. La pantalla se puede usar bien desde el celular.
- Decisiones del usuario del 2026-11-12 (T34): la cantidad va en el tipo de kit (criterio 7) y editar no toca los kits ya armados (criterio 5). La pantalla va en Bodega → Tipos de kit. Desactivar un tipo de dispositivo no se bloquea aunque un tipo de kit lo use: el tipo de kit lo conserva, marcado, hasta que lo quiten. Los tipos de contenedor se cargan por seeder (15, dados por el usuario) y no tienen pantalla.
- Diseño: página "E4 · Kits" del lienzo (T34, aprobado por el usuario el 2026-11-12): lista con el contenido desplegable y modal en dos pasos, con un interruptor y una cantidad por tipo de dispositivo y un resumen al lado.
- Supuestos de la construcción: el nombre se guarda como se escribe, sin pasarlo a mayúsculas; la cantidad llega hasta 999.
- Corrección del 2026-11-16 (T55), pedida por el usuario: un tipo de dispositivo con la casilla «Se reparte entre asignación interna y externa» (H06), como la cinta de inspección, no lleva una sola cantidad. Su tarjeta muestra dos contadores, **Interna** y **Externa**, enteros desde 0 que suman al menos 1 y hasta 999. La cantidad es su suma, calculada sola. El resumen y la lista muestran el reparto. Si un tipo de dispositivo empieza a repartirse después de guardado el tipo de kit, al editarlo se pide el reparto desde 0.
- Corrección del 2026-11-24 (T76): todo tipo de kit lleva exactamente un tipo de dispositivo Funda (H06), con cantidad 1 fija: su tarjeta no tiene contador y dice «1 por kit». Sin funda no se guarda («Todo tipo de kit lleva una funda: enciende una.») y con dos tampoco («Un tipo de kit lleva una sola funda: deja encendida solo una.»).

**H25 · Gestionar trabajadores** (propuesta del 2026-11-04, aprobada por el usuario el 2026-11-13)
- Como admin, quiero crear, editar y desactivar trabajadores, propios o de un proveedor, para elegirlos como receptores al despachar kits.
- Criterios de aceptación:
  1. El admin crea un trabajador con nombre, tipo y número de documento, e indica si es propio o de un proveedor. Si es de un proveedor, elige uno activo (H10).
  2. No se pueden crear dos trabajadores con el mismo tipo y número de documento, aunque uno esté desactivado.
  3. El admin edita los datos de un trabajador.
  4. El admin desactiva un trabajador en vez de borrarlo, y lo reactiva desde la lista. Uno desactivado no aparece al despachar kits, pero se conserva en los despachos ya hechos.
  5. Hay una lista que se puede buscar por nombre o número de documento.
  6. Un trabajador no se enlaza directamente a un usuario. (Cambiado por el usuario el 2026-11-17, en H15: ya no se compara su documento con el del usuario, porque el inspector puede usar cualquier kit Despachado.)
  7. El número de documento solo lo ven los roles que lo necesitan. Cuáles son se resuelve en H24; hasta entonces lo ve cualquier usuario con sesión, como en H22 (decisión del usuario del 2026-11-13). No es asesoría legal.
  8. Solo el admin usa esta pantalla: pasa a H24. Hasta culminar E6, cualquier usuario con sesión la usa, como en H22 (decisión del usuario del 2026-11-13).
  9. La pantalla se puede usar bien desde el celular.
- Decisiones del usuario del 2026-11-13 (T43): con DNI, al crear, se consulta RENIEC al completar los 8 dígitos o con la lupa y se completa el nombre, que se puede corregir; si el servicio falla, se llena a mano (la misma regla de H04, criterio 12). Con otro tipo de documento no hay consulta. La pantalla va en Maestros → Trabajadores, debajo de Proveedores.
- Diseño: aprobado por el usuario el 2026-11-13 en la página "E4 · Kits" del lienzo: lista con tabla (documento, nombres y apellidos, procedencia y estado; filtros de procedencia y estado) y modal de crear y editar.
- Cambios pedidos por el usuario el 2026-11-13, al revisar la construcción: el trabajador lleva un cargo, opcional y en texto libre (hasta 100 caracteres), que también se ve en la lista; el proveedor se elige con un selector que busca en el servidor mientras se escribe (por razón social o documento, solo activos), sin que lo buscado viaje en la URL; y un botón «Agregar» junto al campo Proveedor, en crear y en editar, abre encima el modal «Nuevo proveedor» de H10 y deja elegido el proveedor creado. Diseño actualizado en el lienzo.
- Supuestos aprobados con el diseño: nombres y apellidos en un solo campo, como en H22; la lista se ordena por nombres y apellidos y la búsqueda no viaja en la URL, para que el documento no quede en ella; la consulta de DNI solo se hace al crear, como en usuarios; si se desactiva el proveedor de un trabajador, este lo conserva, y al editar solo se ofrecen los proveedores activos más el que ya tenía.

**H26 · Gestionar zonas de patio** (propuesta del 2026-11-04, aprobada el 2026-11-13)
- Como admin, quiero crear, editar y desactivar las zonas de patio de cada sucursal, para que el inspector elija una al iniciar una certificación.
- Criterios de aceptación:
  1. El admin crea una zona con sucursal, nombre y capacidad.
  2. No se pueden crear dos zonas con el mismo nombre en la misma sucursal, aunque la otra esté desactivada (decisión del usuario del 2026-11-13).
  3. El admin edita los datos de una zona.
  4. El admin desactiva una zona en vez de borrarla. Una desactivada no aparece al iniciar una certificación, pero se conserva en las ya hechas.
  5. Hay una lista que se puede buscar por nombre y filtrar por sucursal.
  6. La capacidad es obligatoria, un número entero mayor que 0, y solo informativa: el sistema no controla cuánto hay en cada zona (decisión del usuario del 2026-11-13). La gestión de patio sigue fuera del Product Goal.
  7. Hasta culminar E6, cualquier usuario con sesión usa esta pantalla, por la regla de roles; quién la usa después se define con H24.
  8. La pantalla se puede usar bien desde el celular.
- Diseño: aprobado por el usuario el 2026-11-13 en la página "E4 · Kits" del lienzo: lista con tabla en Maestros → Zonas de patio (nombre, sucursal, capacidad y estado; filtros de sucursal y estado) y modal de crear y editar.
- Supuestos aprobados con el diseño: la capacidad se cuenta en contenedores; la lista se ordena por sucursal y nombre; al crear y editar solo se ofrecen sucursales activas, y al editar también la que la zona ya tenía.
- Pedido del usuario al aprobarla, el 2026-11-13: un seeder deja una sola zona por defecto, «Zona certificación», con capacidad 50, en la única sucursal del seeder de sucursales, que pasa a ser solo `0001` Depot Paita - Macromar.

**H14 · Crear un kit y agregarle dispositivos**
- Como usuario de bodega, quiero crear un kit y agregarle dispositivos escaneándolos, para dejarlo listo para despacharlo.
- Criterios de aceptación:
  1. Se ingresan los datos generales: código de barras de la funda (escaneado o escrito), tipo de kit y naviera. Los tres son obligatorios (T46, 2026-11-13).
  2. El código de la funda no puede repetirse en otro kit.
  3. Solo se pueden elegir tipos de kit y navieras activos.
  4. Los dispositivos se agregan escaneando uno por uno. Cada escaneo llena el campo y se agrega solo al kit, sin botón de guardar. El guardado es asíncrono (regla común).
  5. Solo se aceptan dispositivos de los tipos que permite el tipo de kit; si es de otro tipo, mensaje claro y no se agrega.
  6. Un dispositivo solo puede estar en un kit; si ya está en otro, se muestra en cuál.
  7. Solo se aceptan dispositivos en estado Disponible y activados para despacho (H29); si no lo está, mensaje claro y no se agrega (pedido por el usuario el 2026-11-13; estados confirmados en T46).
  8. Se ve la lista de lo escaneado, con qué se guardó y qué falló, para reintentar.
  9. Mientras el kit no esté despachado, bodega puede quitar un dispositivo, que vuelve a quedar disponible.
  10. Se muestra el avance por tipo de dispositivo según las cantidades del tipo de kit (H13, criterio 7), por ejemplo "4 de 6 precintos", y se avisa lo que falta. El kit incompleto se guarda para completarlo después, pero no se despacha hasta estar completo (H15). No se aceptan más dispositivos de un tipo que los que pide el tipo de kit (T46, 2026-11-13).
  11. Queda registrado quién creó el kit y cuándo.
  12. La pantalla se puede usar bien desde el celular, con el cursor listo en el campo de escaneo después de cada lectura.
  13. Solo se aceptan dispositivos de lotes de la misma naviera del kit; si es de otra, mensaje claro y no se agrega. Cada naviera activa sus propias series (T46, 2026-11-13).
  14. El kit está Armando mientras le falten dispositivos y pasa solo a Armado al completarse. Si se le quita un dispositivo, vuelve a Armando (T46, 2026-11-13).
- Diseño: aprobado por el usuario el 2026-11-13 en la página "E4 · Kits" del lienzo (T47), y cambiado a su pedido el 2026-11-16, tomando como referencia la pantalla de BUMMOCK: el paso 2 tiene un campo de escaneo por cada tipo de dispositivo que lleva el kit; la serie se escanea o se escribe en el campo de su tipo y se agrega con Enter, y si es de otro tipo se rechaza con el motivo. Al completarse un tipo, su campo se bloquea y el cursor pasa solo al siguiente. Al costado, el avance total, lo que falta y los últimos escaneos.
- Cinta de inspección (cambio del usuario del 2026-11-16, por T55): tiene dos campos, primero **Externa** y después **Interna**, con las cantidades del tipo de kit, y cada cinta guarda a qué lado va. Suposición por confirmar: SIGLA no revisa que los correlativos de las externas sean menores que los de las internas.
- Corrección del 2026-11-20 (T62), al construir: como dos tipos que no utilizan serie pueden tener la misma serie (H07), al escanear en un campo se busca primero el dispositivo del tipo de ese campo. Si la serie solo existe en otro tipo, se rechaza como hasta ahora.
- Lista de kits (decisión del usuario del 2026-11-16): H14 deja una lista base en Bodega → Kits (funda, tipo de kit, naviera, semana, avance y estado, con «Nuevo» y abrir el kit), para retomar un kit Armando. H16 le suma filtros, búsqueda y el detalle del despacho.
- La semana no se escribe: es la semana ISO de la fecha de creación del kit, en hora de Lima, y se usa para listar y filtrar en H16. La zona de patio no va en el kit: la elige el inspector al iniciar la certificación, H17 (decisión del 2026-11-04).
- Corrección del 2026-11-24 (T76), pedida por el usuario el 2026-11-23: la funda que se escanea en la cabecera (criterio 1) es un dispositivo de categoría Funda (H06). Para crear el kit tiene que ser del tipo Funda que lleva el tipo de kit, de un lote de la naviera del kit (criterio 13) y estar Disponible, es decir aprobada si su tipo lo requiere (criterio 7). Si no, el campo dice por qué y no se crea el kit. Al crearlo, la funda entra sola en su contenido y queda En un kit; en el paso 2 va primero, completa, sin campo de escaneo, y no se quita (excepción al criterio 9). Su estado sigue al del kit: se despacha, se usa o se descarta con él. Los datos anteriores se borran con `migrate:fresh`, porque el sistema está en marcha blanca, así que no hay kits viejos que convertir (decisión del usuario del 2026-11-24).

**H15 · Despachar kits**
- Como usuario de bodega, quiero despachar uno o más kits a un trabajador, para dejarlos disponibles para certificar.
- Criterios de aceptación:
  1. Se elige el trabajador que recibe los kits, de la lista de trabajadores activos (H25). Quién entrega es el usuario de bodega que despacha, y queda registrado solo; se muestra en el despacho en solo lectura (decisión del usuario del 2026-11-17).
  2. Los kits se agregan escaneando la funda de cada uno: cada escaneo llena el campo y marca el kit solo, sin botón de guardar. También se marcan con su casilla en una tabla de kits Armados y sin despachar, que se filtra por tipo de kit, naviera y funda (cambio del usuario del 2026-11-17, tomado del despacho de BUMMOCK). Un despacho puede llevar kits de distintos tipos.
  3. Solo se pueden despachar kits que no estén ya despachados; si ya lo está, se muestra en qué despacho.
  4. Solo se despacha un kit Armado, es decir, completo. Un kit vacío o incompleto (Armando) no se despacha, y se muestra qué le falta (T46, 2026-11-13).
  5. Cada kit queda en un solo despacho.
  6. Al despachar, el contenido del kit ya no se puede cambiar.
  7. Queda registrado quién hizo el despacho y cuándo.
  8. Hay una lista de despachos con kits, receptor y fecha, que se puede filtrar.
  9. Se ve qué escaneos se guardaron y cuáles fallaron, para reintentar.
  10. La pantalla se puede usar bien desde el celular.
  11. El trabajador que recibe queda registrado en el despacho, pero no limita qué kits ve el inspector: en H17 puede usar cualquier kit Despachado (cambiado por el usuario el 2026-11-17; antes solo veía los despachados al trabajador con su mismo documento).
  12. Solo se despacha un kit si todos sus dispositivos están activados para despacho (H29). Como H14 solo acepta dispositivos activados y una activación no se deshace, la regla se cumple sola; se deja escrita porque la marca es obligatoria para todo despacho (pedido por el usuario el 2026-11-13).
  13. Al despacharse, el kit pasa a Despachado. Sus dispositivos siguen En un kit (T46, 2026-11-13).
  14. Cada kit completo (Armado, Despachado o Usado) tiene un PDF solo con su contenido (sus dispositivos, con serie, tipo y asignación), sin los datos del despacho. Cada despacho tiene su propio PDF: cuándo se despachó, quién lo entregó y a quién, los kits entregados y el contenido de cada kit por su funda (pedido por el usuario el 2026-11-17; separados a su pedido el mismo día).
- Diseño: aprobado por el usuario el 2026-11-13 en la página "E4 · Kits" del lienzo (T47) y cambiado a su pedido el 2026-11-17, tomando como referencia el despacho de BUMMOCK: el despacho es un solo modal, «Despacho masivo de kits», que se abre desde la lista de despachos (Bodega → Despachos). Arriba, el trabajador que recibe y quién entrega; abajo, el escaneo de la funda y la tabla de kits Armados con casillas, «Solo marcados», contadores y resumen por tipo. No hay un paso «Asignar» aparte. La lista de despachos muestra número (D-000013), fecha, semana, trabajador que recibe, kits con su resumen por tipo y quién despachó, con filtros de semana, trabajador y tipo de kit. Aprobado el 2026-11-17.
- Supuestos aprobados con el diseño: el número del despacho es «D-» y su id con seis dígitos, sin columna propia; la semana del despacho es la semana ISO de su fecha, en hora de Lima; lo marcado vive en el modal hasta pulsar «Despachar» y se conserva al cambiar de página o de filtro. Al despachar, la pantalla del kit ya no ofrece escanear ni quitar.
- Fuera de esta historia: editar un despacho y el QR del despacho. La asignación del kit a un contenedor concreto y la búsqueda de la funda por el inspector ocurren al certificar (E5).

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
  9. Los despachos a granel (H30) no van en esta lista: tienen la suya propia, en Bodega → Despachos → «A granel» (cambiado por el usuario el 2026-11-17; antes iban aquí marcados «A granel», T46).
- Diseño: se construye desde la plantilla "Lista con tabla", sin diseño propio (decisión del usuario del 2026-11-13, T46).
- Estados de un kit (T46, 2026-11-13): Armando (incompleto), Armado (completo, listo para despachar), Despachado (en un despacho, H15) y Usado (en una certificación, H17).
- Falta una historia de E4, que se escribe al llegar a E6: reasignar dispositivos a una unidad en reinspección (en revisión junto con E6). (Resuelto el 2026-11-23: los dispositivos de la reinspección llegan por un despacho a granel con motivo Operación, H30, y se reemplazan en H35.)

**H30 · Despachar dispositivos a granel** (pedida por el usuario el 2026-11-13; "por confirmar" cerrados en T46 y aprobada ese mismo día; rediseñada el 2026-11-17 siguiendo el despacho a granel de BUMMOCK)
- Como usuario de bodega, quiero despachar dispositivos sueltos a un trabajador, sin armar un kit, para venderlos a un cliente o para reponer los de un kit en operación.
- Criterios de aceptación:
  1. Los despachos a granel tienen su propia lista, en Bodega → Despachos → «A granel», separada de la de despachos de kits. Cada despacho se numera con su propia secuencia, `DG-000001`.
  2. El despacho se registra con el trabajador que recibe, elegido de la lista de trabajadores activos (H25) como en H15. Quién entrega es el usuario que despacha, y queda registrado solo, con la fecha y la semana.
  3. El motivo de salida es obligatorio: Venta u Operación. Con Venta se elige un cliente activo (H31). Con Operación se elige el kit despachado al que se asignan los dispositivos: se ofrecen los kits despachados cuya certificación no se ha cerrado (hoy, todos los Despachados, porque H17 aún no existe). El selector lista y busca los contenedores de las certificaciones cuyo kit está **Usado** (se usa al cerrar la certificación, H19), cada uno con el kit que se le asignó al iniciarla (H17), por ejemplo «MSKU1234567 · KIT-000046»; también se busca por funda. Al elegir un contenedor se toma su kit original, y los dispositivos del despacho quedan enlazados a ese kit, para mantener la relación entre el contenedor, el kit y los dispositivos, como en una reinspección. Un kit que no está Usado no se ofrece. (Cambiado el 2026-11-20 en T64, a pedido del cliente y del usuario: un kit Usado siempre tiene contenedor. Antes se ofrecían los kits Despachados cuya certificación no se había cerrado y se buscaban solo por funda.)
  4. Se elige el patio de destino entre las zonas de patio activas de la sede del usuario (H26). Es obligatorio (suposición por confirmar).
  5. Se puede escribir una observación, opcional.
  6. Los dispositivos se agregan de tres formas: escaneando uno por uno, y cada escaneo se agrega solo a la lista; por rango, con la serie inicial y la final; o eligiendo el tipo de dispositivo, la serie inicial y la cantidad, y el sistema calcula hasta dónde llega. La serie inicial puede ser la serie completa (HLS0000009) o solo su prefijo (HLS), y con el prefijo se empieza por el primer disponible de esa serie (pedido por el usuario el 2026-11-17, al probar la construcción). En los campos de rango y de cantidad, Enter muestra la vista previa.
  7. El rango y el cálculo por cantidad siguen las reglas de H29: las series deben existir, los huecos se ignoran, el máximo es de 10 000 dispositivos y se muestra una vista previa antes de agregar.
  8. Solo se despachan dispositivos Disponibles, es decir, aprobados por la naviera (H29). Un dispositivo en un kit no se despacha a granel. Por escaneo, el que no cumple se rechaza con un mensaje claro; por rango o por cantidad, si alguno no cumple, no se agrega ninguno y se muestra cuáles.
  9. Se ve qué escaneos se agregaron y cuáles fallaron, con su motivo, para reintentar.
  10. Al despacharse, los dispositivos pasan al estado Despachado. Cada dispositivo queda en un solo despacho, y el despacho se guarda todo o nada.
  11. Cada despacho tiene una página de detalle con sus datos y, por cada tipo de dispositivo, su rango, su cantidad y sus series. No tiene PDF.
  12. Por ahora, cualquier usuario con sesión despacha. Los roles pasan a H24.
  13. La pantalla se puede usar bien desde el celular.
- Decisiones del usuario del 2026-11-13 (T46): el receptor es un trabajador; el estado nuevo es Despachado; sirve para reponer dispositivos (por ejemplo, uno que se rompe al colocarlo) y para la reinspección de E6.
- Decisiones del usuario del 2026-11-17, al rediseñar con BUMMOCK: tabla y lista propias, sin PDF y con página de detalle (antes iba en la lista de H16 con el chip «A granel»); motivo de salida Venta u Operación; con Venta, el cliente (H31, creada para esto); con Operación, el kit despachado cuya certificación sigue abierta. Si una certificación cerrada necesita otro precinto, por ejemplo por su estado físico, primero se reabre y recién entonces su kit aparece para el despacho por Operación (la reapertura se define en H19 y E6). (Reemplazado el 2026-11-20 en T64: por Operación solo se ofrecen kits Usados, es decir, de certificaciones ya cerradas.) Un solo patio, el de destino; observación opcional; los dispositivos se agregan por escaneo, por rango y por tipo con cantidad; numeración `DG-000001`.
- Depende de: H31, por el cliente de una venta (2026-11-17).
- Corrección del 2026-11-20 (T62), decidida por el usuario: como dos tipos que no utilizan serie pueden tener la misma serie (H07), el despacho suma un selector de tipo de dispositivo, opcional, para el escaneo y el rango. Si no se elige el tipo y la serie existe en más de un tipo, no se agrega y se avisa «Elige el tipo de dispositivo». Si se elige, solo se buscan series de ese tipo. La forma por tipo y cantidad no cambia, porque ya lleva el tipo. El selector va arriba de las pestañas y sirve para «Escanear» y «Por rango» (al construir, el 2026-11-20; en el diseño estaba dentro de la columna del escaneo).
- Cambio del 2026-11-24 (H38), decidido por el usuario: por Operación se ofrecen los contenedores de las certificaciones en curso, reabiertas y cerradas, no las anuladas (criterio 3). Reemplaza la regla de T64, que solo ofrecía kits Usados. Los dispositivos de ese despacho son los extras de la asignación, que el inspector usa para reemplazar los del kit.
- Fuera de esta historia: editar o anular un despacho, su PDF, y cómo se usan después los dispositivos a granel (cuándo pasan a Usado y cómo los ve el inspector), que se define al revisar E6 y lo posterior a H17.
- Diseño: el del 2026-11-13 (página "E4 · Kits", T47) se rehace con estas decisiones y se vuelve a aprobar en el lienzo antes de construir.

### E9 · Transportistas, conductores y vehículos
- Los catálogos de transportistas (empresas), conductores y vehículos (remolcador y semirremolque), con sus pantallas de gestión. También se crean solos al iniciar una certificación, cuando vienen de SIGLA 1.0 y no existen todavía.
- Se reconocen por RUC (transportista), DNI (conductor) y placa (vehículo). Son catálogos independientes entre sí. No hay pantallas de gestión de la relación: esta se guarda en cada certificación. (Cambiado el 2026-11-11 en T45: los conductores se gestionan desde cada transportista; ver H22.)
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
- No se asignan conductores ni vehículos al transportista: siguen siendo catálogos independientes y la relación se guarda en cada certificación (confirmado por el usuario el 2026-11-11). **Cambiado ese mismo día en T45 (corrección de H22):** cada fila tiene el botón Conductores, que abre los conductores de esa empresa. Los vehículos siguen sin asignarse.
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
- El conductor no queda ligado a ningún transportista. **Corrección del 2026-11-11 (T45), a pedido del usuario, pensando en la extranet:**
  - Los conductores van por empresa transportista. Conductores sale del menú: se llega con el botón Conductores de cada fila de Transportistas, que abre un modal con los conductores de esa empresa.
  - Un conductor puede estar en varios transportistas y se registra una sola vez con su DNI. "Agregar conductor" pide el DNI: si ya está registrado, lo enlaza con sus datos sin consultar RENIEC; si no, lo consulta y lo crea. Si ya está en la empresa, se avisa; si estaba desactivado en ella, lo reactiva.
  - Editar cambia los datos del conductor en todas las empresas. Desactivar y reactivar solo afectan a la empresa desde la que se hace (reemplaza al criterio 5 en ese punto).
  - Diseño de la corrección aprobado por el usuario el 2026-11-11 en el lienzo.
- El criterio 7 se construye con H17; H22 deja listo el catálogo.
- En el menú va en Maestros → Transporte, debajo de Transportistas (cambiado por el usuario el 2026-11-11).
- Diseño: aprobado por el usuario el 2026-11-11 en la página "E9 · Transportistas, conductores y vehículos" del lienzo.

**H23 · Gestionar vehículos**
- Como admin, quiero crear, editar y desactivar vehículos (remolcadores y semirremolques), para tener su registro ordenado y poder enlazarlos a cada certificación. (Cambiado el 2026-11-11 en T42: por ahora la usa cualquier usuario con sesión; la parte de roles pasa a H24.)
- Criterios de aceptación:
  1. El admin crea un vehículo con placa, marca, tipo (remolcador o semirremolque), carrocería, categoría y color. El color es opcional y de texto libre, en mayúsculas (agregado a pedido del usuario el 2026-11-11). La carrocería se elige de un catálogo gestionable, con su propia pantalla para crear, editar, desactivar y reactivar; una desactivada no se elige en vehículos nuevos, pero los que la tienen la conservan. La categoría es la lista fija de SIGLA 1.0: N1, N2, N3, O1, O2, O3, O4 y Otro, con N3 por defecto. (Cambiado el 2026-11-11 a pedido del usuario: antes eran tres tipos fijos, remolcador, semirremolque plataforma y semirremolque contenedora.)
  2. No se pueden crear dos vehículos con la misma placa, aunque uno esté desactivado. La placa se guarda en mayúsculas, sin espacios ni guiones, como `ABC123`, igual que SIGLA 1.0 (decisión del usuario del 2026-11-11).
  3. El admin edita los datos de un vehículo.
  4. El admin desactiva un vehículo en vez de borrarlo, con Soft Deletes, y lo reactiva desde la lista. Como nunca se borra, uno con certificaciones tampoco se elimina (resuelto el 2026-11-11 en T42: no hay botón de eliminar).
  5. Hay una lista, ordenada por placa, con búsqueda por placa o marca y filtros por tipo, carrocería, categoría y estado.
  6. Un vehículo se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17). H23 deja listo el Service `VehicleRegistry`, que lo busca por placa y, si no existe, lo registra con su marca, categoría, tipo, carrocería y color; si la carrocería tampoco existe, la crea. H17 solo lo llama.
  7. Ya no hay marca "por revisar": lo que manda SIGLA 1.0 va directo al catálogo (cambiado el 2026-11-11 a pedido del usuario).
  8. Solo el admin crea, edita y desactiva: pasa a H24. Hasta culminar E6, cualquier usuario con sesión la usa (decisión del 2026-11-03).
  9. La pantalla se puede usar bien desde el celular.
- El vehículo no queda ligado a ningún transportista.
- En el menú van Vehículos y Carrocerías en Maestros → Transporte, debajo de Conductores.
- Diseño: aprobado por el usuario el 2026-11-11 en la página "E9 · Transportistas, conductores y vehículos" del lienzo.

### E5 · Certificación del contenedor
- Que el inspector certificado certifique el contenedor con su kit. Aprobado: iniciar la certificación (H17), completar las secciones con fotos (H18), cerrarla (H19) y consultarla con su PDF (H20); H18 a H20 se aprobaron el 2026-11-20. Desde el 2026-11-24, reemplazar dispositivos del kit con dispositivos extra (H38). La reinspección sigue EN REVISIÓN con E6.
- La certificación es opcional (ver decisiones). El inspector decide a qué unidades aplicarla.
- Fuera: EIR del gate out y fin de lavado (proceso de gate out de SIGLA, aún no se construye).
- Depende de: E1, E2, E3, E4, E7, E9. La necesita E6.

**H17 · Iniciar una certificación** (aprobada)
- Como inspector certificado, quiero iniciar una certificación eligiendo la solicitud de retiro y el kit que me despacharon, para empezar a certificar esa unidad.
- Criterios de aceptación:
  1. El inspector busca la solicitud con el número de contenedor y el booking (la consulta de H05) y la elige. En pantalla el contenedor se escribe en dos partes, 4 letras y 7 números, pero se guarda y se consulta junto (ABCU1234567). Si SIGLA 1.0 trae más de una solicitud, se toma la de despacho más reciente (decisiones del usuario del 2026-11-17).
  2. La certificación guarda el id de la solicitud, su booking, su contenedor, su cliente y su producto, además de los enlaces a la naviera y a los catálogos del criterio 3. El DNI y el brevete del conductor están en el catálogo de conductores (E9) y no se copian en la certificación (cambiado por el usuario el 2026-11-20 en T58: el cliente y el producto se guardan como texto al asignar, para que el detalle y el PDF no dependan de SIGLA 1.0; antes, desde el 2026-11-17, se consultaban cada vez).
  3. El transportista, el conductor, el remolcador y el semirremolque se enlazan con sus catálogos (E9). Si ya existen, se enlazan; si no, se crean solos. Se reconocen por RUC, DNI y placa. Desde el 2026-11-11 (T45), el conductor también se enlaza con el transportista de esa misma solicitud, si todavía no lo está.
  4. Si falla algo al guardar, no queda una certificación a medias. Se guarda todo o nada.
  5. Si SIGLA 1.0 no responde, se muestra un mensaje claro y el inspector puede reintentar.
  6. Una solicitud de retiro tiene una sola certificación. Si ya existe, se muestra esa en vez de crear otra. La reinspección reabre esa misma certificación (decisión del 2026-11-04).
  7. El inspector busca su kit escaneando con la cámara del teléfono o escribiendo el código de la funda. Encuentra cualquier kit Despachado, sin importar a qué trabajador se despachó, y ve su contenido como información (cambiado por el usuario el 2026-11-17, en H15). El kit debe ser de la misma naviera de la solicitud; si no, se rechaza (decisión del usuario del 2026-11-17). Al iniciar, el kit sigue Despachado.
  8. Un kit solo se usa en una certificación (confirmado por el usuario el 2026-11-17).
  9. Queda registrado quién certifica, la fecha y la hora de inicio.
  10. Al asignar, el inspector vuelve al listado de Asignaciones (H20), donde la unidad queda con sus dos inspecciones pendientes (cambiado el 2026-11-20 en T58; antes pasaba al formulario de las secciones).
  11. Los datos personales del conductor (DNI y brevete) solo los ven los roles que los necesitan. No es asesoría legal. Salen del catálogo de conductores (E9), que se llena desde SIGLA 1.0 al asignar, sin copiarse en la certificación (corregido el 2026-11-20 en T58); los roles llegan con H24.
  12. La pantalla se puede usar bien desde el celular.
  13. Al iniciar, el inspector elige la zona de patio, del catálogo de H26 (agregado el 2026-11-04), entre las activas de su sede (2026-11-17).
  14. El inspector puede escanear o escribir el código de barras del formato de inspección, que es opcional y no se repite en otra certificación (agregado por el usuario el 2026-11-17).
  15. En el menú, Certificación lleva Solicitudes de retiro, Inspección y Checkpoint, que se ve desactivada y sin enlace hasta que exista E6 (decisión del usuario del 2026-11-17). Desde el 2026-11-20 (T58), Inspección es un desplegable con Certificaciones, que abre un panel con tarjetas con icono: Asignación (esta historia), Inspección interna e Inspección externa (H18), y Asignaciones (H20), agregada por el usuario el 2026-11-20.
  16. Al asignar, el kit se valida contra las secciones activas (H11 y H12): si sus dispositivos no alcanzan o sobran, no se asigna y se dice qué falta o qué sobra (agregado por el usuario el 2026-11-20 en T58; antes se validaba al entrar a las secciones, en H18).
- La naviera que trae la API (solo su nombre, por ejemplo `SEATRADE`) se enlaza a `shipping_lines`: se busca por nombre sin distinguir mayúsculas; si está desactivada, se reactiva; si no existe, se crea con un código de 4 caracteres tomado del nombre, sin espacios ni signos (SEATRADE queda SEAT), y si ese código ya existe se le suma un número (MAER1, MAER2). Reemplaza la decisión del 2026-11-04 de copiarla como texto (decisión del usuario del 2026-11-17, que cierra la propuesta del 2026-11-10). (Reemplazado el 2026-11-23 en T74, ver abajo.) Cliente y producto no tienen catálogo.
- Corrección del 2026-11-23 (T74), pedida por el usuario: SIGLA 1.0 escribe las navieras distinto que la maestra (`CHIQUITA` es CHQT · Chiquita Brands International), y al no coincidir se creaba una naviera repetida y se rechazaba el kit. Ahora, al buscar la solicitud, su naviera se cuadra con las navieras activas por pasos: el nombre de SIGLA 1.0 ya recordado en la línea, el nombre o el código exactos sin distinguir mayúsculas, el nombre que empieza con ese texto y el que lo contiene. Se toma sola solo si hay una única coincidencia. Si no hay ninguna, o hay varias, el inspector elige la naviera en el paso 1, con las que coinciden primero; sin elegirla no se busca el kit. Su elección queda recordada en la línea, y las próximas solicitudes con ese nombre se cuadran solas. Ya no se crean ni se reactivan navieras desde la asignación: si una naviera no está en la maestra, se registra antes en Líneas navieras. El kit se valida contra la naviera cuadrada y la certificación guarda su FK (criterio 7). El seeder carga cómo escribe SIGLA 1.0 cada naviera que tiene y suma las 4 que faltaban, con su código de SIGLA 1.0 y su nombre comercial: SEAB · Seaboard Marine, CSP · Container Sudamérica Perú, SVC · Servicontainer y MLS · Macromar Logistics. Suposición por confirmar: una coincidencia automática no se cambia desde la asignación. Diseño: página E5 del lienzo, «T74 · Naviera de SIGLA 1.0». El cliente es el operador de la solicitud, y el tipo de vehículo sale de `carroceria_tracto` y `carroceria_carreta`.
- Corrección del 2026-11-24 (T76): la funda del kit es su depósito y no se usa en la inspección: queda fuera del reparto entre las secciones y de lo que falta o sobra (criterio 16). En el contenido del kit se muestra aparte (decisión del usuario del 2026-11-24).
- Diseño aprobado en el lienzo el 2026-11-17 (página E5): en el teléfono, un paso por pantalla (solicitud, kit y zona) con una barra fija de Atrás y Siguiente; en escritorio, los tres pasos juntos. La cámara usa el paquete `barcode-detector`, aprobado por el usuario el 2026-11-17.

**H18 · Hacer la inspección interna y la externa con fotos** (aprobada el 2026-11-20 en T54; cambiada ese día en T58 por decisión del usuario: dos inspecciones independientes)
- Como inspector certificado, quiero hacer la inspección interna o la externa de una unidad ya asignada, tomando las fotos de sus secciones, para dejar la evidencia de cada parte del contenedor y de los dispositivos que lleva.
- Criterios de aceptación:
  1. Cada unidad asignada (H17) tiene dos inspecciones: la interna, con las secciones internas activas del catálogo (H11), y la externa, con las externas. Se hacen en cualquier orden o al mismo tiempo, por el mismo inspector o por dos distintos.
  2. Las tarjetas Inspección interna e Inspección externa del panel de Certificaciones abren la lista de unidades asignadas que tienen esa inspección pendiente o a medias, con su contenedor y su avance. Arriba hay un buscador por contenedor y un botón para escanear la funda. Al tocar una unidad empieza su paso a paso.
  3. El sistema reparte solo los dispositivos del kit entre las secciones, según lo que pide cada una (H12). Las cintas de inspección se reparten entre internas y externas como indica el tipo de kit (T55). El inspector no elige los dispositivos. El kit ya se validó al asignar (H17, criterio 16).
  4. El inspector recorre las secciones de esa inspección una por una, en el orden de H11, en un formulario por pasos con barra de progreso. En cada paso ve el nombre de la sección y los dispositivos que lleva, con su serie. Puede ir a cualquier sección y pasar a la siguiente sin foto: la sección queda pendiente.
  5. Cada sección lleva de 1 a n fotos, sin máximo. Cada foto se toma con la cámara del teléfono, una por vez, nunca desde la galería. En escritorio, las inspecciones se ven en solo lectura, con el aviso de que las fotos se toman desde el teléfono.
  6. Cada foto se comprime en el navegador antes de subirse, sin paquete nuevo: de unos 7 MB a unos 700 KB. Al comprimirla se le imprimen encima la fecha, la hora y el contenedor.
  7. La subida empieza apenas se toma la foto: su miniatura aparece al instante con su avance, y el inspector puede seguir tomando otras. Si una subida falla, se reintenta tocando la foto. Lo ya subido se muestra en miniatura liviana.
  8. De cada foto queda quién la subió y la fecha y hora de la subida. De cada inspección se muestra su inspector y su inicio y fin, tomados de su primera y su última foto.
  9. El avance se guarda en cada foto. El inspector puede volver atrás, ver una foto en grande, borrarla (con confirmación) o reemplazarla, y dejar la inspección a medias. Cualquier inspector la puede seguir.
  10. Una inspección está completa cuando cada una de sus secciones tiene al menos una foto.
  11. Una certificación cerrada (H19) no se puede editar hasta que se reapertura.
  12. La pantalla se usa bien desde el celular.
- Fuera: marcar daños por sección (son del EIR del gate out) y la reinspección (E6).
- Versión conversada, a revisar: el inspector recorre las secciones una por una (multi-step form, en el orden de H11), ve qué dispositivos y cuántos lleva cada una (H12) y toma las fotos; cada sección puede llevar más de una foto y necesita al menos una; el avance se guarda en cada paso; puede volver atrás y reemplazar fotos; puede dejar la certificación a medias.
- Dudas abiertas: cómo se colocan los dispositivos en cada sección (el sistema podría asignarlos por defecto desde el kit y el inspector solo etiquetaría), cuántas fotos por sección y cómo se reducen desde celular (unas 30 por unidad).
- Decisiones del usuario del 2026-11-19 (T54): el sistema reparte solo los dispositivos del kit entre las secciones, según lo que pide cada una (H12), y el inspector solo toma las fotos. Las fotos se comprimen en el navegador con lo que este ya trae, sin paquete nuevo, de unos 7 MB a unos 700 KB. Cada foto se toma siempre con la cámara, nunca desde la galería. La subida empieza apenas se toma la foto y lo ya subido carga liviano en pantalla. El botón de tomar foto abre la cámara del teléfono, una foto por vez, y no la galería. Cada sección lleva de 1 a n fotos, sin máximo, guardadas en una tabla propia de fotos (una fila por foto). De cada foto queda quién la subió y la fecha y hora de la subida, y al comprimirla se le imprimen encima la fecha, la hora y el contenedor, como marca de agua. Si los dispositivos del kit no alcanzan o sobran para lo que piden las secciones activas, no se puede avanzar: se avisa al entrar a las secciones y el inspector pide otro kit. Se confirma la versión conversada (recorrido por pasos, avance guardado en cada paso, volver atrás, borrar o reemplazar fotos y dejarla a medias), con un multi-step form que muestra una barra de progreso.
- Cambio del 2026-11-24 (H38): mientras la certificación está en curso o reabierta, la inspección suma la opción «Dispositivos extra», para reemplazar un dispositivo del kit ya colocado por uno despachado a granel para esa asignación. La sección del reemplazo pide al menos una foto nueva (ver H38).

**H19 · Cerrar y reaperturar la certificación** (aprobada el 2026-11-20 en T54; cambiada ese día en T58 por decisión del usuario: se cierra desde el listado de Asignaciones y se puede reaperturar)
- Como inspector certificado, quiero cerrar la certificación cuando sus dos inspecciones están completas, y reaperturarla si hace falta corregirla, para dejarla terminada con sus dispositivos como usados.
- Criterios de aceptación:
  1. El cierre es manual, desde el listado de Asignaciones (H20). El botón «Cerrar» se activa solo cuando la inspección interna y la externa están completas; mientras tanto la fila muestra el avance de cada una, y con las dos completas, «Lista para cerrar».
  2. No hay excepciones: si una sección no tiene foto, no se cierra.
  3. Se pide confirmación antes de cerrar.
  4. Cualquier inspector puede cerrarla. Queda registrado quién la cerró, con la fecha y la hora.
  5. Al cerrar, el kit pasa a Usado y sus dispositivos también. Todo se guarda junto o nada.
  6. Una certificación cerrada no se edita.
  7. Desde el mismo listado, una certificación cerrada se puede reaperturar con un motivo obligatorio. Queda registrado quién la reaperturó, cuándo y por qué, y se ve en el detalle. Al reaperturar, el kit y sus dispositivos siguen en Usado: solo se pueden corregir o sumar fotos, y después se vuelve a cerrar.
  8. La pantalla se usa bien desde el celular.
- Fuera: la reinspección del checkpoint (E6, Sprint 4).
- Versión conversada, a revisar: resumen de secciones con y sin foto; todas las secciones obligatorias para cerrar; confirmación antes de cerrar; una certificación cerrada no se edita; el kit y sus dispositivos quedan usados; solo cierra quien la inició (por confirmar).
- Riesgo: si una sección no se puede fotografiar por una avería, el inspector queda trabado.
- Decisiones del usuario del 2026-11-19 (T54): cualquier inspector puede seguir y cerrar una certificación, aunque no la haya iniciado, y queda registrado quién la cerró, con la fecha y la hora (reemplaza «solo cierra quien la inició»). No hay excepción para una sección sin foto: sin al menos una foto en cada sección no se cierra, y la certificación queda a medias hasta resolverlo (el riesgo se acepta). Se confirma el resto de lo conversado: resumen de secciones con y sin foto, confirmación antes de cerrar, una cerrada no se edita, y al cerrar el kit y sus dispositivos pasan a Usado. La reapertura para la reinspección queda fuera: se define con E6 en el Sprint 4.
- Cambio del 2026-11-24 (H38): al reaperturar, además de corregir o sumar fotos (criterio 7), se pueden reemplazar dispositivos del kit por dispositivos extra. Mientras una sección con reemplazo no tenga su foto nueva, la certificación no se cierra.

**H20 · Listado de asignaciones y consulta de certificaciones** (aprobada el 2026-11-20 en T54; cambiada ese día en T58 por decisión del usuario: la consulta es el mismo listado de Asignaciones)
- Como usuario de la intranet, quiero un listado de las unidades asignadas, para seguir su avance, cerrarlas o reaperturarlas, consultarlas y descargar el PDF de las cerradas.
- Criterios de aceptación:
  1. La tarjeta Asignaciones del panel de Certificaciones abre el listado de Asignaciones, con el botón «Nueva asignación» (H17); la tarjeta Asignación abre la nueva asignación (cambiado por el usuario el 2026-11-20, al construir H19: el listado tiene su propia tarjeta, con cuántas unidades están listas para cerrar). Se filtra por fecha, naviera, inspector y estado (a medias, lista para cerrar, cerrada o reaperturada), y se busca por contenedor, booking o placa.
  2. Cada fila muestra el avance de las dos inspecciones (por ejemplo, Interna 8/13 y Externa 7/7) y las acciones que correspondan: ver, cerrar y reaperturar (H19), y PDF.
  3. Por ahora todos los usuarios con sesión ven todas las certificaciones; la restricción por rol llega con H24.
  4. El detalle es de solo consulta: datos de la solicitud, transportista, conductor, vehículos, kit, cada inspección con su inspector, inicio y fin, y cada sección con sus dispositivos y sus fotos. Si se reaperturó, muestra quién, cuándo y el motivo.
  5. El PDF se descarga solo de una certificación cerrada. Lleva la cabecera (con el nombre y el DNI del conductor, del catálogo de conductores), los dispositivos asignados y las fotos por sección, con el formato de PDF del lienzo.
  6. En el PDF, los dispositivos asignados van con tres dibujos propios del contenedor: el interior con las puertas abiertas, con su lista de dispositivos de las secciones internas, y los dos exteriores (lado de las puertas y lado de la máquina) juntos, con una sola lista de las secciones externas. Cada dispositivo lleva su serie y su sección.
  7. El listado y el detalle se usan bien desde el celular.
- Fuera: marcas numeradas sobre los dibujos (Sprint 4), tabla de daños por área (EIR del gate out), lista de procedimientos de seguridad y trazabilidad GPS.
- Versión conversada, a revisar: lista filtrable por fecha, naviera, inspector y estado; búsqueda por contenedor, booking o placa; detalle con los datos guardados de la solicitud, transportista, conductor, vehículos, kit y fotos por sección; solo consulta; datos personales del conductor visibles solo para roles que los necesitan; quién ve todas o solo las suyas (por confirmar).
- Pendiente: la generación del PDF de la certificación (hoy ya existe un formato "Registro de contenedores" con contenedor, fecha, horas y cintas por zona) y en qué momento se genera.
- Decisiones del usuario del 2026-11-19 (T54): por ahora todos los usuarios con sesión ven todas las certificaciones, por la regla de roles vigente; quién ve todas o solo las suyas se define con H24. El PDF se hace con DomPDF y toma como referencia el informe de inspección actual (parecido o mejorado, no igual: ese formato es de Risk Protection S.A.). El bloque de cintas usa tres dibujos propios del contenedor en SVG (interior con puertas abiertas, exterior del lado de las puertas y exterior del lado de la máquina) y, junto a cada uno, sus cintas con la serie y la sección, armadas desde el reparto de H18. Las marcas numeradas sobre el dibujo, en el lugar de cada sección, pasan al Sprint 4 como mejora. La tabla de daños por área del formato de referencia no va: es del EIR del gate out, una inspección previa a la certificación que se construye en otra etapa; cuando exista, se podrá sumar a este PDF. Tampoco van la lista de procedimientos de seguridad ni la trazabilidad GPS (los puntos del recorrido). El PDF lleva solo la cabecera, los dispositivos asignados y las fotos, con el formato de PDF ya estandarizado en el lienzo, y se descarga solo de una certificación cerrada. Se confirma el resto de lo conversado (filtros, búsqueda y detalle de solo consulta).
- Cambio del 2026-11-24 (H38): el PDF y el detalle muestran el dispositivo nuevo en la sección del reemplazo. El detalle suma la sección «Dispositivos reemplazados» (fecha y hora, quién, motivo, sección, anterior → nuevo y foto del descarte); el PDF no la lleva.

**H32 · Anular la asignación de un kit** (pedida por el cliente y aprobada por el usuario el 2026-11-20)
- Como inspector o supervisor, quiero anular la asignación de un kit a su certificación cuando se cancela el servicio de despacho, para inhabilitar el kit o reutilizarlo según responda la naviera.
- Criterios de aceptación:
  1. Desde el listado de Asignaciones (H20), una certificación que nunca se cerró tiene la acción «Anular asignación». Una certificación cerrada, o reaperturada después de cerrarse, no se anula, porque al cerrarla su kit y sus dispositivos pasaron a Usado (H19). (Precisado al construir, el 2026-11-20.)
  2. Se pide un motivo obligatorio y la decisión sobre el kit, según la respuesta de la naviera: **Reutilizar el kit** o **Inhabilitar el kit**. Se pide confirmación antes de anular.
  3. Con «Reutilizar», el kit vuelve a Despachado con sus dispositivos, que dejan de estar repartidos en las secciones de la certificación, y se puede asignar a otra certificación (H17).
  4. Con «Inhabilitar», el kit queda **Inhabilitado** y todos sus dispositivos pasan a Descartado (T65), con el motivo de la anulación. Un kit Inhabilitado no se asigna, no se despacha ni recibe dispositivos.
  5. La certificación queda **Anulada**: se conserva, ya no se edita ni se cierra, y no tiene PDF. Queda registrado quién la anuló, cuándo, el motivo y la decisión sobre el kit, y se ve en su detalle (H20).
  6. El listado de Asignaciones muestra el estado Anulada y lo suma a su filtro de estado.
  7. Todo se guarda junto o nada.
  8. Por ahora, cualquier usuario con sesión anula asignaciones. Qué rol puede hacerlo se define en H24.
  9. La pantalla se usa bien desde el celular.
- Un kit va en una sola certificación que no esté anulada (H17, criterio 8): al reutilizarlo, la anulada conserva su referencia al kit.
- Depende de: H18 a H20 (listado de Asignaciones y detalle) y T65 (estado Descartado). Se construye en una rama que parte de la de H20.
- Diseño: desde las plantillas, sin lienzo propio (decisión del usuario del 2026-11-20): un botón por fila y un modal con el motivo y la decisión, como «Reaperturar» de H19.
- Corrección del 2026-11-24 (T77): con «Inhabilitar el kit» (criterio 4) se pide además una **foto obligatoria** del kit, una sola, que queda como foto del descarte de cada uno de sus dispositivos y se ve en su «Ver descarte» (H08). Con «Reutilizar» no se pide (decisión del usuario del 2026-11-24). El modal de anular quedó dibujado en el diseño de T77.

**H38 · Reemplazar dispositivos del kit con dispositivos extra** (pedida por el usuario el 2026-11-24 en la reunión de correcciones y aprobada ese mismo día; cambia H18, H19, H20 y H30, y la usa H35)
- Como inspector certificado, quiero ver los dispositivos extra que bodega despachó a granel para mi asignación y reemplazar con ellos un dispositivo del kit ya colocado, para que el PDF y el detalle muestren lo que de verdad quedó en el contenedor y quede registro de cada cambio.
- Criterios de aceptación:
  1. El despacho a granel por Operación (H30, criterio 3) ofrece los contenedores de las certificaciones **en curso, reabiertas y cerradas**, no las anuladas. Cambia la regla de T64, que solo ofrecía kits Usados.
  2. Los dispositivos de ese despacho son los **dispositivos extra** de esa asignación.
  3. Mientras la certificación está en curso o reabierta, la inspección (H18) tiene la opción **«Dispositivos extra»**. Lista cada extra con su tipo, su serie, su despacho (`DG-000123`) y si está sin usar o a qué dispositivo reemplazó.
  4. Para **reemplazar**, el inspector elige un dispositivo del kit ya colocado en una sección y un extra sin usar del **mismo tipo**, escribe un **motivo obligatorio** (texto libre) y toma la **foto del descarte** (T77).
  5. El extra ocupa la sección del anterior y pasa a Usado al cerrar la certificación, como el resto del kit. El anterior pasa a **Descartado**, con el motivo y la foto.
  6. La sección del reemplazo pide **al menos una foto nueva**. Las anteriores se conservan marcadas «antes del reemplazo» y ya no cuentan. Mientras falte esa foto, la certificación no se cierra.
  7. Con la certificación **cerrada**, primero se reapertura (H19) y después se reemplaza. Una certificación anulada no admite reemplazos.
  8. El PDF y el detalle de la certificación (H20) muestran el dispositivo **nuevo** en su sección. El detalle suma una sección **«Dispositivos reemplazados»** con fecha y hora, quién, el motivo, la sección, el cambio de anterior a nuevo (por ejemplo, PRC0000120 → PRC0000455) y la foto del descarte.
  9. Los extras sin usar siguen **Despachados** y se ven como «sin usar». Devolverlos a bodega queda fuera.
  10. Cada reemplazo se guarda todo junto o nada.
  11. Por ahora, cualquier usuario con sesión reemplaza. Los roles llegan con H24.
  12. La pantalla se usa bien desde el celular.
- Caso que la origina (contado por el usuario el 2026-11-24): un inspector colocó el kit y, al cerrar la puerta y poner el precinto, notó que había olvidado dentro del contenedor los documentos del conductor. Tuvo que romper el precinto, abrir la puerta y sacarlos, y el kit quedó con un precinto roto. Bodega le despachó otro a granel para esa asignación, y el inspector lo colocó y siguió. Hoy SIGLA solo registra el despacho a granel hacia la operación: no cuándo se reemplazó, por qué ni a qué dispositivo.
- Decisiones del usuario del 2026-11-24: los extras se despachan con la inspección en curso y también con la certificación cerrada; si está cerrada, primero se reapertura para reemplazar y, si sigue en curso, se reemplaza directo. El que sale pasa a Descartado con la foto obligatoria del descarte (T77). La sección pide foto nueva, como en H35. Los sobrantes quedan Despachados. Los extras solo reemplazan: no se agregan sin sacar uno. El extra es del mismo tipo que el que sale. El motivo es texto libre obligatorio. Se construye en el Sprint 4, antes de T71.
- Suposiciones confirmadas por el usuario el 2026-11-24: la funda no se reemplaza con esta opción, porque no está en ninguna sección (T76); el PDF no lleva la lista de reemplazos, solo el dispositivo nuevo en su sección, y la lista queda en el detalle.
- H35 reusa este reemplazo y su registro en su criterio 5 (el dispositivo con ✗ se reemplaza por un extra).
- Fuera: agregar un extra sin sacar ninguno, devolver extras a bodega y editar o anular un reemplazo.
- Depende de: H18 a H20, H30 y T77 (foto del descarte).
- Diseño: página «E5 · Certificación» del lienzo, aprobado por el usuario el 2026-11-24: la sección con «Dispositivos extra» y «Reemplazar», la lista de extras, el modal de reemplazo, la sección después del reemplazo, el detalle con «Dispositivos reemplazados» (teléfono y escritorio) y sus estados. Se reemplaza desde el teléfono; en escritorio, como en H18, es solo lectura. Por la regla del lienzo se actualizó también el selector de Operación en los estados de H30 (E4).
- Decisión del usuario del 2026-11-24, al empezar T79: el extra que entra reemplaza al descartado también en el contenido del kit. Si la asignación se anula (H32), el extra sigue en el kit como cualquier otro: con «Reutilizar» vuelve a Despachado con el kit y con «Inhabilitar» pasa a Descartado con los demás. El descartado por el reemplazo sigue Descartado.

### E6 · Checkpoint (historias aprobadas el 2026-11-23)
- Validación del contenedor ya cargado, fuera del depot, antes de llegar al puerto de embarque. Solo aplica a unidades que fueron certificadas.
- Se arma a partir del video del checkpoint actual (`documentation/checkpoint.mp4`), revisado con el usuario el 2026-11-23: número de contenedor, 14 pasos con barra de avance (fotos de placa, DNI y transporte; panel frontal; cintas de evaporadores y máquina; verificación de cintas externas; paredes, pisos y puertas; cinta de puerta y su verificación; sellos de naviera; sello cable y su verificación; candado satelital, opcional; cintas de puerta; panorámica de puertas; observaciones y fotos extra, opcionales) y «Finalizar».
- Decisiones del usuario del 2026-11-23:
  - Los pasos no son fijos: salen del catálogo de secciones, como en la certificación. El catálogo suma un tercer tipo, Checkpoint (H33).
  - Cada sección de verificación se enlaza a una o más secciones de la certificación, internas o externas, y muestra las series que el sistema repartió ahí (H18). Se aceptan internas porque, según el documento de certificación, la cinta de puerta, el precinto de naviera y el sello cable quedan en secciones internas (cambiado por el usuario en T67; antes solo externas). El inspector marca cada serie como correcta (✓) o incorrecta (✗).
  - Con una ✗ se inicia la reinspección, y el checkpoint no se cierra hasta que la serie quede correcta (H35).
  - En la reinspección se rehacen solo las secciones de la certificación enlazadas a las series con ✗, no la certificación entera.
  - Los dispositivos nuevos llegan por un despacho a granel con motivo Operación (H30), que ya enlaza los dispositivos al kit del contenedor. No hace falta un motivo nuevo ni un segundo kit.
  - No hay secciones opcionales: todas las secciones, también las de checkpoint, son obligatorias y piden al menos una foto (aclarado por el usuario en T67; antes se había anotado que cada sección de checkpoint se marcaba como obligatoria u opcional). Las observaciones y las fotos extra del final no son secciones del catálogo y siguen siendo opcionales.
  - El checkpoint no lleva PDF por ahora; solo se consulta en su detalle (H36).
  - El retiro de candado, que la app actual tiene en su menú, queda fuera.
  - H24 (roles y permisos) no entra en el Sprint 4: queda para el Sprint 5, con E6 ya probado.
- Lo conversado antes, que estas historias reemplazan: buscar la certificación cerrada sin checkpoint cerrado, validar las series de las cintas y sellos de las partes externas (marcando cada una como correcta o no), subir foto por ítem y fotos de la unidad (tracto, semirremolque, puertas), iniciar la reinspección con su motivo, bloquear el cierre mientras la unidad esté en reinspección. Flujo de reinspección conversado: (1) checkpoint la inicia, (2) bodega carga la nueva asignación [E4], (3) el inspector certificado reinspecciona [E5], (4) checkpoint valida y cierra. Las secciones se reemplazan completas.
- Fuera de SIGLA: aviso por otros medios de que algo viene roto; retiro del candado GPS.
- Depende de: E1, E3, E5 (y usa H30 de E4).

**H33 · Secciones de checkpoint** (aprobada el 2026-11-23 en T67; cambia H11 y H12)
- Como admin, quiero crear y ordenar las secciones del checkpoint en el mismo catálogo de secciones, para que el checkpoint pida siempre lo correcto sin tocar código.
- Criterios de aceptación:
  1. El catálogo de secciones (H11) suma el tipo **Checkpoint**, además de Interna y Externa, y la lista se filtra también por ese tipo.
  2. Una sección de checkpoint es **de fotos** o **de verificación**.
  3. Una sección de verificación se enlaza a una o más secciones activas de la certificación, internas o externas. Por ejemplo, «Verificación de cintas de evaporadores» se enlaza a «Evaporadores cerrados» y «Ventilador» (externas), y «Verificación de cinta de puerta» a «Kit de precintos en piso» (interna).
  4. Todas las secciones de checkpoint son obligatorias. Una de fotos pide al menos una foto, como las internas y externas; una de verificación no pide fotos, solo que cada serie quede marcada, como en el video (las fotos de esas cintas se toman en una sección de fotos). No hay marca de opcional (aclarado por el usuario en T67).
  5. Las secciones de checkpoint no llevan tipos de dispositivo (H12): sus dispositivos salen de las secciones enlazadas.
  6. Las secciones de checkpoint entran en el mismo orden único de H11, junto con las internas y externas. El checkpoint toma solo las suyas, en ese orden, como cada inspección (decisión del usuario del 2026-11-23, T67).
  7. El nombre no se repite dentro del tipo Checkpoint, como en H11.
  8. Desactivar una sección enlazada no rompe la sección de checkpoint: el enlace se conserva, marcado como desactivada, hasta que el admin lo quite, como los tipos desactivados en H12. Al verificar, se siguen mostrando las series que las certificaciones tengan en esa sección (decisión del usuario del 2026-11-23, T67).
  9. Cambiar las secciones no modifica checkpoints ya cerrados.
  10. Por ahora, cualquier usuario con sesión usa la pantalla; los roles llegan con H24.
  11. La pantalla se usa bien desde el celular.
- Seeders (decisión del usuario del 2026-11-23, T67): las secciones internas y externas se cargan desde el documento «Proceso de certificación de contenedores» (`documentation/`), y las de checkpoint desde el video, menos «Candado satelital y sello», que queda fuera hasta decidir cómo se maneja una unidad sin candado. Hoy no hay seeder de secciones para producción: solo `LocalTestingSeeder` crea seis de ejemplo. El seeder carga solo las secciones (nombre, tipo y orden), sin sus dispositivos: qué lleva cada una lo configura el admin en pantalla (H12), con los tipos reales, porque el documento nombra dispositivos que aún no son tipos (bolsa de seguridad, precinto GPS, precintos de vacío). En la interna, los ítems repetidos del documento se llaman «Puerta lado derecho» (6), «Puerta lado izquierdo» (7), «Pared interna» (9) y «Cintas de pared interna» (10) (confirmado por el usuario el 2026-11-23). El ítem 1 de cada tabla (escribir el número de contenedor) no es sección. En la externa, los ítems 16 y 17 («Mostrar dibujo de contenedor parte externa derecha / izquierda», 1 foto) sí son secciones con foto: «Dibujo parte externa derecha» y «Dibujo parte externa izquierda» (decisión del usuario del 2026-11-23). Quedan 17 internas y 16 externas.
- Seeder de checkpoint (aprobado por el usuario el 2026-11-23, T67), en este orden, después de las internas y externas: (1) Placa, cédula y transporte; (2) Panorámica del panel frontal; (3) Cintas de evaporadores y máquina; (4) Verificación de cintas de evaporadores → Evaporadores cerrados y Ventilador (externas); (5) Paredes, pisos externos y puertas; (6) Cinta de puerta; (7) Verificación de cinta de puerta → Kit de precintos en piso (interna); (8) Sellos de naviera; (9) Sello cable; (10) Verificación de sello cable → Puerta cerrada y panorámica (interna); (11) Cintas de puerta y checkpoint; (12) Panorámica de puertas. Las secciones sin enlace son de fotos. Sellos de naviera queda como sección de fotos, sin verificación propia.

- Diseño: página «E6 · Checkpoint» del lienzo, aprobado en T68 el 2026-11-24 (ronda 3): la sección de checkpoint en el modal de H12 (Tipo, «¿Qué pide este paso?» y «¿Qué secciones verifica?»), con el listado de secciones de E3 actualizado.

**H34 · Hacer el checkpoint de una unidad** (aprobada el 2026-11-23 en T67)
- Como inspector checkpoint, quiero recorrer las secciones del checkpoint de una unidad certificada, tomando sus fotos y verificando sus cintas y sellos, para confirmar que la unidad llega al puerto como salió del depot.
- Criterios de aceptación:
  1. En el menú, Certificación → Checkpoint deja de estar desactivado (H17, criterio 15) y pasa a ser un desplegable, como Inspección, con «Registrar checkpoint» y «Checkpoints» (H36). «Registrar checkpoint» abre la lista de unidades con checkpoint pendiente o a medias, con un buscador por contenedor (4 letras y 7 números, escrito o escaneado). Primero van las que vuelven de una reinspección. (Cambiado por el usuario el 2026-11-24 en T68: antes Checkpoint abría directo la lista.) El contenedor se da de tres formas: escribiéndolo a mano, que siempre está disponible, o, de forma opcional, escaneándolo o tomándole una foto, como en la app actual. La foto queda como evidencia en el checkpoint y no lee el número: el inspector igual lo escribe o lo escanea (decisión del usuario del 2026-11-24; leer el número de la foto pediría un paquete de OCR nuevo). Antes de construir H34 se actualiza su diseño en el lienzo con el botón «Tomar foto».
  2. Solo aparecen certificaciones cerradas que no tienen el checkpoint cerrado. Una certificación anulada (H32) no aparece.
  3. Una certificación tiene un solo checkpoint. Si hay una ✗, ese mismo checkpoint queda en reinspección y, al volver, se sigue y se cierra; todo su historial queda en él (decisión del usuario del 2026-11-23, T67).
  4. El inspector recorre las secciones activas de tipo Checkpoint, en su orden, en un formulario por pasos con barra de progreso, como la inspección de H18. Puede ir a cualquier paso y dejar el checkpoint a medias; cualquier inspector lo puede seguir.
  5. **Paso de fotos:** se toman de 1 a n fotos, con las mismas reglas de H18: solo con la cámara del teléfono, comprimidas en el navegador, con la fecha, la hora y el contenedor impresos, y subida apenas se toma cada una. En escritorio se ve en solo lectura.
  6. **Paso de verificación:** se listan las series que la certificación dejó en las secciones enlazadas, y el inspector marca cada una como correcta (✓) o incorrecta (✗). La marca se guarda al tocarla. Si las secciones enlazadas no tienen dispositivos, el paso dice «Sin dispositivos que verificar».
  7. Al final hay un campo de observaciones y fotos extra, ambos opcionales.
  8. «Finalizar» se activa cuando cada sección de fotos tiene al menos una foto y cada serie de las secciones de verificación está marcada.
  9. Si todas las series están en ✓, el checkpoint queda **cerrado**. Queda registrado quién lo cerró, con la fecha y la hora. Si hay alguna ✗, se pasa a la reinspección (H35).
  10. De cada foto y de cada marca queda quién la hizo y cuándo.
  11. Un checkpoint cerrado no se edita.
  12. Por ahora, cualquier usuario con sesión hace el checkpoint; los roles llegan con H24.
  13. La pantalla se usa bien desde el celular.

- Diseño: página «E6 · Checkpoint» del lienzo, aprobado en T68 (ronda 1 el 2026-11-23 y el menú el 2026-11-24): elegir la unidad, el paso de verificación con los botones «Correcta» e «Incorrecta», y el último paso con observaciones, fotos extra, resumen y «Finalizar». El paso de fotos es el de H18. Con todas las series correctas, el botón dice «Finalizar y cerrar».

**H35 · Reinspección desde el checkpoint** (aprobada el 2026-11-23 en T67)
- Como inspector checkpoint, quiero iniciar la reinspección cuando una cinta o un sello no está bien, para que el inspector certificado cambie solo lo que falló y yo vuelva a verificarlo antes de cerrar.
- Criterios de aceptación:
  1. Al finalizar un checkpoint con al menos una ✗, se pide un motivo obligatorio y confirmación. El checkpoint queda **En reinspección** y no se cierra.
  2. En la certificación se reabren solo las secciones donde el sistema dejó las series con ✗ (H18). La certificación queda **En reinspección** en el listado de Asignaciones (H20), con su motivo.
  3. Bodega despacha a granel los dispositivos nuevos con motivo Operación, eligiendo el contenedor (H30, sin cambios).
  4. Las unidades en reinspección aparecen en la lista de Inspección interna o externa (H18), según el tipo de las secciones reabiertas, marcadas «Reinspección» y con solo esas secciones.
  5. En cada sección reabierta, el inspector certificado ve los dispositivos marcados con ✗ y escanea, por cada uno, uno nuevo del mismo tipo, despachado a granel para ese contenedor. El nuevo queda en la sección y pasa a Usado; el anterior pasa a Descartado (T65), con el motivo de la reinspección (decisión del usuario del 2026-11-23, T67).
  6. El inspector certificado vuelve a tomar las fotos de la sección reabierta. Las fotos anteriores se conservan, marcadas «antes de la reinspección», y se ven en el detalle (H20 y H36), pero ya no cuentan: la sección necesita al menos una foto nueva (decisión del usuario del 2026-11-23, T67).
  7. Cuando todas las secciones reabiertas tienen su dispositivo nuevo y al menos una foto nueva, el inspector certificado cierra la reinspección, como en H19. Queda registrado quién y cuándo.
  8. Con la reinspección cerrada, la unidad vuelve a la lista del checkpoint, que verifica de nuevo solo las series reemplazadas: muestra solo los pasos de verificación con series nuevas, y lo que ya estaba en ✓ y las fotos del checkpoint se conservan (decisión del usuario del 2026-11-23, T67). Si todas están en ✓, se cierra. Si vuelve a haber una ✗, se inicia otra reinspección.
  9. Una certificación en reinspección no se anula (H32), no se reapertura (H19) ni ofrece su PDF (H20) hasta cerrar la reinspección (el PDF, decidido en el diseño el 2026-11-24, T68).
  10. Todo se guarda junto o nada.
  11. Por ahora, cualquier usuario con sesión inicia y cierra reinspecciones; los roles llegan con H24.
  12. La pantalla se usa bien desde el celular.
- Reemplaza la historia de E4 que faltaba («reasignar dispositivos a una unidad en reinspección»): los dispositivos llegan por H30.
- Cambio del 2026-11-24 (H38): el reemplazo del criterio 5 usa el de H38 y queda en el mismo registro de «Dispositivos reemplazados», con el motivo de la reinspección.

- Diseño: página «E6 · Checkpoint» del lienzo, aprobado en T68 (ronda 1 el 2026-11-23 y ronda 2 el 2026-11-24): el motivo al finalizar, la sección reabierta para el inspector certificado, el listado de Asignaciones con «En reinspección» y su cierre, y la segunda vuelta del checkpoint. En las listas de Inspección interna y externa, la unidad en reinspección lleva la etiqueta «Reinspección», sobre el diseño de H18.

**H36 · Consultar checkpoints** (aprobada el 2026-11-23 en T67)
- Como usuario de la intranet, quiero un listado de los checkpoints y su detalle, para seguir su avance y consultar lo verificado.
- Criterios de aceptación:
  1. Un listado de checkpoints, filtrable por fecha, naviera, inspector y estado (a medias, en reinspección o cerrado), con búsqueda por contenedor, booking o placa.
  2. Cada fila muestra el avance (por ejemplo, 9/14 pasos) y la acción «Ver».
  3. El detalle es de solo consulta: datos de la certificación, cada sección con sus fotos, cada verificación con su marca ✓/✗, las observaciones y las fotos extra, y cada reinspección con su motivo, quién la inició y la cerró, y los dispositivos que se reemplazaron.
  4. Desde el detalle de la certificación (H20) se llega a su checkpoint, si tiene.
  5. Sin PDF por ahora (decisión del usuario del 2026-11-23).
  6. Por ahora todos los usuarios con sesión ven todos los checkpoints; los roles llegan con H24.
  7. El listado y el detalle se usan bien desde el celular.

- Diseño: página «E6 · Checkpoint» del lienzo, aprobado en T68 el 2026-11-24 (ronda 3): listado y detalle, en escritorio y teléfono. El detalle muestra las reinspecciones (motivo, sección reabierta, reemplazo y segunda verificación) y cada paso con sus fotos o sus series marcadas. El detalle de la certificación (H20) suma el botón «Ver checkpoint».

## Fuera del Product Goal (ideas para después, sin historias)
- Estimaciones.
- Gestión de patio y stock. (El catálogo de zonas de H26 sí entra, solo como lista para elegir.)
- Gate out (EIR del gate out, fin de lavado) y la regla de exigir certificación al cerrar un gate out.
- Técnicos reefer y estructura, customer, inspector estructura gate in.
- Extranet: operadores y sus usuarios.
- Tipos de contenedor distintos de 40'.
- PDF del checkpoint (decisión del usuario del 2026-11-23: por ahora solo se consulta en su detalle, H36).
- Retiro de candado, que hoy está en la app del checkpoint (decisión del usuario del 2026-11-23).

## Suposiciones por confirmar
- Los inspectores siempre tienen internet.
- SIGLA solo registra el resultado del checkpoint; el aviso de cinta rota se hace fuera del sistema.
- Cómo se migra lo que hoy está en BUMMOCK, y si hacen falta reportes o historial por contenedor.
- Si el despacho de dispositivos sueltos a una zona de patio (que aparece en el ER) sirve para la reinspección. (Resuelto el 2026-11-13: el despacho a granel es la historia H30 y va a un trabajador, no a una zona de patio; su uso en la reinspección se define al revisar E6.)
