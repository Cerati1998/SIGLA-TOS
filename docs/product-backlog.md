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
- Hay dos tipos de acceso: INTRANET (personal de la empresa: operaciones, patio, inspecciones, checkpoint, backoffice y futuros módulos del DT aduanero) y EXTRANET (usuarios que pertenecen a un operador; un operador puede tener n usuarios). En esta primera versión solo se construye la intranet; el sistema deja preparada la separación de rutas y del tipo de acceso.
- Un rol por usuario (suposición por confirmar antes de construir).
- Regla común de escaneo: todo se escanea (dispositivos y kits tienen código propio; el kit lleva funda con código de barras). Hoy el escáner llena el campo activo y el dato se guarda solo, de forma asíncrona, sin botón de guardar. SIGLA 2.0 debe tener esa misma capacidad. La pantalla muestra qué escaneos se guardaron y cuáles fallaron, para poder reintentar (suposición por confirmar: el escáner funciona como teclado).
- Puente con SIGLA 1.0 (decisión revisada el 2026-10-27): al iniciar una certificación se consulta SIGLA 1.0 una sola vez y SIGLA 2.0 guarda en la certificación una copia permanente de los datos de la solicitud (cliente, producto, booking, naviera y contenedor) más la referencia al despacho (`source` = api mientras dependa de SIGLA 1.0; `web` cuando SIGLA 2.0 tenga su módulo de solicitudes y despachos y apunte directo a su tabla de solicitudes u operaciones). Así la certificación y cualquier PDF futuro no dependen de que SIGLA 1.0 siga existiendo. Los datos llegan validados desde una operación real, casi al final del despacho.
- Transportista, conductor y vehículos (remolcador y semirremolque) tienen catálogos propios (E9) y son independientes entre sí: un conductor o vehículo no depende de un transportista (pueden cambiar de transportista o tercerizar). La relación entre ellos se guarda en cada certificación. Al iniciar la certificación se hace un findOrCreate: si ya existen se enlazan; si no, se crean. Se reconocen por RUC (transportista), DNI (conductor) y placa (vehículo). El número de documento y de brevete no cambian.
- Datos que entrega la API de SIGLA 1.0: transportista (RUC, razón social); conductor (DNI, brevete, nombres y apellidos); vehículo (marca, categoría —N1, N2, N3, O1, O2, O3…—, placa, y tipo de vehículo como texto: remolcador, semirremolque plataforma o semirremolque contenedora). Cliente y producto no tienen catálogo y se guardan como datos de la certificación.
- Las líneas navieras y los proveedores son tablas maestras de SIGLA 2.0, con su propio módulo (E8).
- Por ahora solo se manejan contenedores de 40'. Lo que pide cada sección es igual para todos. Si entran otras medidas, se amplía.
- La certificación es un proceso OPCIONAL que va después del EIR del gate out (el EIR se hace a todas las unidades y todavía no entra en SIGLA 2.0). Se practica solo a algunas navieras. SIGLA 2.0 no valida ni obliga si una unidad debe certificarse ni impide cerrar un gate out sin certificación; el inspector decide. Esa regla sería una historia nueva cuando se migre el gate out. No seremos minuciosos en ese detalle por ahora.
- Lo que pide cada sección (tipo de dispositivo y cantidad) no existe en BUMMOCK; el técnico lo tiene interiorizado. Es una oportunidad de mejora de SIGLA 2.0.
- Recorrido del kit: (1) bodega crea el kit y le escanea dispositivos, (2) bodega lo despacha a un inspector certificado, (3) el inspector busca la funda en su app e inicia la certificación asignándole ese kit. **Lo que ocurre después de este paso (cómo se colocan los dispositivos, fotos, cierre) está EN REVISIÓN.**
- Una certificación usa un solo kit. "El ticket" que se asigna al iniciar es la solicitud de retiro.
- Datos personales (DNI y brevete): solo los ven los roles que los necesitan. No es asesoría legal.
- El diseño de la base de datos lo hace Claude Code durante la Construcción. El ER del usuario es referencia de negocio.
- EN REVISIÓN (conversado, no aprobado): todas las secciones obligatorias para cerrar una certificación, con al menos una foto cada una y varias fotos permitidas por sección.

## Notas para la Construcción (desde el ER del usuario)
- Falta qué dispositivos y cuántos pide cada sección (H12). El ER ya trae la tabla con su cantidad y clave compuesta. Se podría renombrar `certificacion_criteria_items` (el ER nuevo ya lo llama `certification_criteria_items`).
- El tipo de kit guarda la cantidad por tipo de dispositivo en el ER nuevo (apoya H13, criterio 7, que sigue por confirmar).
- Índices únicos individuales en `type_kits_has_security_device_types` y `branches_has_users`: probablemente deberían ser compuestos.
- `security_certifications` tiene `gate_out_request_id` único: definir si la reinspección reabre la certificación o crea otra.
- `security_certifications` guarda zona de patio, pero la gestión de patio está fuera del Product Goal: definir de dónde sale ese dato.
- `security_certifications` guarda horas de inicio y cierre sin fecha: revisar.
- `kits_dispatches` guarda receptor y quien asigna como trabajadores (`workers`), pero el receptor es un inspector certificado con cuenta de usuario: reconsiderar ese vínculo.
- La certificación debe guardar la copia de los datos de la solicitud y referencias a transportista, conductor, remolcador y semirremolque (el ER aún no lo trae).
- Traducir el texto del tipo de vehículo a los tres valores del catálogo; si no coincide, crear igual y marcar para revisión (por confirmar).
- Pendiente: qué significan la semana y la zona de patio del kit, y si la zona se elige al crear el kit.
- Pendiente: cómo se reconoce la naviera que trae la API contra el catálogo de E8 y qué pasa si no existe.
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
  3. Un usuario desactivado no puede entrar (por confirmar).
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
  4. El enlace vence y solo se puede usar una vez (por confirmar).
  5. Un usuario desactivado no puede recuperar acceso.
  6. Solo funciona para usuarios intranet por ahora.
- Riesgo técnico: el envío de correo saliente del servidor; revisar entregabilidad en la tarea de Construcción.

**H04 · Gestionar usuarios intranet**
- Como admin, quiero crear, editar y desactivar usuarios intranet y asignarles un rol, para controlar quién entra al sistema y qué puede hacer.
- Criterios de aceptación:
  1. El admin crea un usuario con nombre, correo y rol (admin, supervisor, usuario de bodega, inspector certificado, inspector checkpoint).
  2. No se pueden crear dos usuarios con el mismo correo.
  3. El admin edita los datos de un usuario y cambia su rol.
  4. El admin desactiva un usuario en vez de borrarlo, para conservar el historial (por confirmar). Un usuario desactivado no puede iniciar sesión.
  5. Un usuario creado no entra hasta definir su contraseña, por ejemplo con un enlace enviado a su correo (por confirmar).
  6. Solo el admin ve y usa esta pantalla.
  7. No existe registro público.
  8. La pantalla se puede usar bien desde el celular.
- Supuesto: un rol por usuario.

### E7 · Puente con SIGLA 1.0
- Épica temporal: consulta la solicitud de retiro en la API de SIGLA 1.0 (sistema actual en producción) hasta migrar todo el módulo de gate out. Entonces el puente se retira.
- Depende de: E1. La necesita E5.
- Dependencia externa: la API de SIGLA 1.0 está creada a medias; falta que entregue todos los datos filtrando por `numero_contenedor` + `booking`. Terminarla antes de construir H05 (trabajo del sistema viejo; ponerlo en el sprint como tarea o impedimento).

**H05 · Consultar la solicitud de retiro desde SIGLA 1.0**
- Como inspector certificado, quiero buscar una solicitud de retiro con el número de contenedor y el booking, para ver los datos de la unidad y del conductor antes de certificar.
- Criterios de aceptación:
  1. La búsqueda pide número de contenedor y booking, y ambos son obligatorios.
  2. Si la solicitud existe, se muestran: cliente, producto, booking, naviera, conductor (con documento de identidad y brevete), remolcador, semirremolque y contenedor asignado. Los datos menores se definen antes de construir.
  3. Si no existe ninguna solicitud con esa combinación, se muestra un mensaje claro.
  4. Si SIGLA 1.0 no responde, se muestra un mensaje claro y el usuario puede reintentar sin perder lo que escribió.
  5. Los datos son solo de lectura. SIGLA 2.0 no modifica nada en SIGLA 1.0.
  6. Solo usuarios con sesión iniciada y un rol autorizado pueden consultar (por confirmar: inspector certificado y supervisor).
  7. La pantalla se puede usar bien desde el celular.
- La consulta es en vivo. Al iniciar una certificación (H17) esos datos se guardan; la consulta de H05 en sí no guarda nada.
- La autenticación del puente es una decisión técnica para la Construcción.

### E8 · Maestros: líneas navieras y proveedores
- Que el admin gestione los catálogos de líneas navieras y proveedores, para usarlos al ingresar lotes (E2), armar kits (E4) y en lo que venga después (estimaciones, extranet, depósito temporal).
- Depende de: E1. La necesitan E2 y E4.

**H09 · Gestionar líneas navieras**
- Como admin, quiero crear, editar y desactivar líneas navieras, para elegirlas al ingresar lotes y armar kits.
- Criterios de aceptación:
  1. El admin crea una línea naviera con código y nombre.
  2. No se pueden crear dos líneas con el mismo código.
  3. El admin edita el código y el nombre de una línea.
  4. El admin desactiva una línea en vez de borrarla. Una línea desactivada no aparece al ingresar lotes ni armar kits, pero se conserva en lo que ya existe (por confirmar).
  5. Hay una lista de líneas que se puede buscar por código o nombre.
  6. La pantalla se puede usar bien desde el celular.
- Quién la usa: solo el admin (por confirmar).
- Si SIGLA 1.0 ya tiene las navieras, pasarlas a SIGLA 2.0 es una tarea de Construcción.

**H10 · Gestionar proveedores**
- Como admin, quiero crear, editar y desactivar proveedores, para elegirlos al ingresar lotes de dispositivos.
- Criterios de aceptación:
  1. El admin crea un proveedor con tipo y número de documento, razón social, dirección, correo y teléfono. Cuáles son obligatorios se confirma antes de construir.
  2. No se pueden crear dos proveedores con el mismo tipo y número de documento.
  3. El admin edita los datos de un proveedor.
  4. El admin desactiva un proveedor en vez de borrarlo (por confirmar).
  5. Hay una lista que se puede buscar por razón social o número de documento.
  6. La pantalla se puede usar bien desde el celular.
  7. Al escribir un RUC o un DNI, el sistema consulta un servicio externo y completa los datos que encuentre. El usuario puede corregirlos antes de guardar.
  8. Si el servicio no responde o no encuentra el documento, el usuario puede llenar a mano y guardar igual.
  9. Si el documento tiene un formato incorrecto, se avisa antes de consultar.
- Quién la usa: solo el admin (por confirmar).
- Decisiones técnicas para la Construcción: qué servicio de terceros para la consulta de RUC/DNI y su costo (el proyecto nace para dejar de pagar por registro). La consulta podría servir luego en otros formularios.
- El ER guarda además cuenta corriente, detalle del servicio y sucursal; se dejaron fuera por no necesitarse en este módulo.

### E2 · Dispositivos de seguridad
- Que bodega registre dispositivos por lote y consulte su estado.
- Depende de: E1 y E8 (H07 usa naviera y proveedor). La necesitan E4 y E5.
- Un anuncio es el origen del lote (de quién viene y cómo se identifican sus series); se crea uno por cada lote, así que bodega no lo gestiona aparte.
- Cada dispositivo es único. Un segundo lote con la misma serie tiene otro rango.

**H06 · Gestionar tipos de dispositivo**
- Como admin o usuario de bodega, quiero crear y editar tipos de dispositivo de seguridad, y como admin, quiero además desactivarlos o eliminarlos, para que el sistema sepa qué dispositivos existen y se puedan registrar.
- Criterios de aceptación:
  1. El admin y el usuario de bodega crean un tipo con nombre y tipo (sello, precinto, cinta u otro).
  2. No se pueden crear dos tipos con el mismo nombre.
  3. El admin y el usuario de bodega editan el nombre de un tipo.
  4. Solo el admin desactiva un tipo. Un tipo desactivado no aparece al registrar lotes ni armar kits, pero se conserva en lo que ya existe.
  5. Si un tipo ya se usó en lotes o kits, no se puede eliminar, solo desactivar (por confirmar).
  6. Los demás roles no ven esta pantalla.
  7. La pantalla se puede usar bien desde el celular.
- Riesgos: duplicados con nombres parecidos; renombrar un tipo con lotes y kits afecta lo ya registrado (considerar registrar quién cambió qué).

**H07 · Ingresar un lote de dispositivos**
- Como usuario de bodega, quiero ingresar un lote escribiendo su origen y su rango de correlativos, para que el sistema genere todas las series y los dispositivos queden registrados.
- Criterios de aceptación:
  1. Se ingresan: naviera, proveedor, tipo de dispositivo, serie (por ejemplo GSW), mínimo de dígitos, correlativo inicial y final, fecha de recepción, número de guía y observaciones (opcional).
  2. Solo se puede elegir un tipo de dispositivo activo.
  3. La cantidad se calcula sola desde el rango (ejemplo: del 200 al 399 son 200). El correlativo inicial no puede ser mayor que el final.
  4. Antes de guardar, se muestra una vista previa con la cantidad, la primera serie y la última.
  5. Al guardar, el sistema genera todas las series uniendo la serie y el correlativo, completando con ceros a la izquierda según el mínimo de dígitos (por confirmar).
  6. Si alguna serie ya existe en el sistema, no se guarda nada y se muestra cuáles chocan.
  7. El guardado es todo o nada.
  8. Las unidades quedan disponibles para armar kits (estado inicial exacto por confirmar antes de construir).
  9. Queda registrado quién ingresó el lote y cuándo.
  10. Un lote grande no traba la pantalla. Cuántas series llegan en el lote más grande se confirma antes de construir.
  11. La pantalla se puede usar bien desde el celular.
- Quién ingresa lotes: usuario de bodega y admin (por confirmar).
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
- Pendiente: los estados de un dispositivo (por confirmar: disponible, en un kit, usado, dañado). Definirlos antes de construir.

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
- Que bodega arme kits con dispositivos escaneados, los despache a un inspector certificado y los consulte.
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

**H14 · Crear un kit y agregarle dispositivos**
- Como usuario de bodega, quiero crear un kit y agregarle dispositivos escaneándolos, para dejarlo listo para despacharlo.
- Criterios de aceptación:
  1. Se ingresan los datos generales: código de barras de la funda (escaneado o escrito), tipo de kit, naviera, semana y zona de patio. Cuáles son obligatorios se confirma antes de construir.
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
- Pendiente: qué significan la semana y la zona de patio del kit.

**H15 · Despachar kits**
- Como usuario de bodega, quiero despachar uno o más kits a un inspector certificado, para dejarlos disponibles para certificar.
- Criterios de aceptación:
  1. Se elige el inspector certificado que recibe los kits, de la lista de usuarios con ese rol. Quién entrega es el usuario de bodega que despacha, y queda registrado solo.
  2. Los kits se agregan escaneando la funda de cada uno. Cada escaneo llena el campo y se agrega solo a la lista, sin botón de guardar.
  3. Solo se pueden despachar kits que no estén ya despachados; si ya lo está, se muestra en qué despacho.
  4. No se puede despachar un kit vacío (por confirmar).
  5. Cada kit queda en un solo despacho.
  6. Al despachar, el contenido del kit ya no se puede cambiar.
  7. Queda registrado quién hizo el despacho y cuándo.
  8. Hay una lista de despachos con kits, receptor y fecha, que se puede filtrar.
  9. Se ve qué escaneos se guardaron y cuáles fallaron, para reintentar.
  10. La pantalla se puede usar bien desde el celular.
  11. El inspector ve en su app los kits que le despacharon. Un kit despachado a otro inspector no le aparece (por confirmar).
- Fuera de esta historia: la asignación del kit a un contenedor concreto y la búsqueda de la funda por el inspector ocurren al certificar (E5).

**H16 · Consultar kits y despachos**
- Como usuario de bodega, supervisor o admin, quiero ver los kits y sus despachos, y buscar un kit por el código de su funda, para saber qué contiene, en qué estado está y quién lo tiene.
- Criterios de aceptación:
  1. Hay una lista de kits con código de funda, tipo de kit, naviera, semana, estado y cuántos dispositivos lleva.
  2. La lista se puede filtrar por tipo de kit, naviera, semana y estado.
  3. Se puede buscar un kit por el código de su funda, escribiéndolo o escaneándolo; al escanear, llena el campo y busca solo.
  4. Al abrir un kit, se ven sus dispositivos con su serie y su tipo.
  5. Para un kit despachado, se ve a qué inspector y en qué despacho está, y quién lo despachó.
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
- Como admin, quiero crear, editar y desactivar transportistas, para tener su registro ordenado y poder enlazarlos a cada certificación.
- Criterios de aceptación:
  1. El admin crea un transportista con RUC y razón social. Dirección, correo y teléfono son opcionales.
  2. No se pueden crear dos transportistas con el mismo RUC.
  3. Al escribir el RUC, el sistema consulta el servicio externo y completa los datos que encuentre, como en H10. Si falla, se llena a mano.
  4. El admin edita los datos de un transportista.
  5. El admin desactiva un transportista en vez de borrarlo. Uno con certificaciones no se puede eliminar (por confirmar).
  6. Hay una lista que se puede buscar por razón social o RUC.
  7. Un transportista se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17), con RUC y razón social.
  8. Solo el admin usa esta pantalla (por confirmar).
  9. La pantalla se puede usar bien desde el celular.

**H22 · Gestionar conductores**
- Como admin, quiero crear, editar y desactivar conductores, para tener su registro ordenado y poder enlazarlos a cada certificación.
- Criterios de aceptación:
  1. El admin crea un conductor con DNI, brevete, nombres y apellidos.
  2. No se pueden crear dos conductores con el mismo DNI.
  3. Al escribir el DNI, el sistema consulta el servicio externo y completa los nombres y apellidos que encuentre, como en H10. El usuario puede corregirlos, y si el servicio falla llena a mano (por confirmar).
  4. El admin edita los datos de un conductor.
  5. El admin desactiva un conductor en vez de borrarlo. Uno con certificaciones no se puede eliminar (por confirmar).
  6. Hay una lista que se puede buscar por DNI, brevete o nombre.
  7. Un conductor se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17).
  8. Los datos personales (DNI y brevete) solo los ven los roles que los necesitan. Cuáles son se confirma antes de construir. Esto no es asesoría legal.
  9. Solo el admin crea, edita y desactiva conductores (por confirmar).
  10. La pantalla se puede usar bien desde el celular.
- El conductor no queda ligado a ningún transportista.

**H23 · Gestionar vehículos**
- Como admin, quiero crear, editar y desactivar vehículos (remolcadores y semirremolques), para tener su registro ordenado y poder enlazarlos a cada certificación.
- Criterios de aceptación:
  1. El admin crea un vehículo con placa, marca, categoría (N1, N2, N3, O1, O2, O3…) y tipo de vehículo (remolcador, semirremolque plataforma o semirremolque contenedora).
  2. No se pueden crear dos vehículos con la misma placa.
  3. El admin edita los datos de un vehículo.
  4. El admin desactiva un vehículo en vez de borrarlo. Uno con certificaciones no se puede eliminar (por confirmar).
  5. Hay una lista con búsqueda por placa o marca, y filtros por categoría y tipo de vehículo.
  6. Un vehículo se crea solo al iniciar una certificación si viene de SIGLA 1.0 y todavía no existe (H17), con el tipo ya completo.
  7. Si el texto del tipo no coincide con ninguno del catálogo, el vehículo se crea igual y queda marcado para revisión (por confirmar).
  8. Solo el admin crea, edita y desactiva (por confirmar).
  9. La pantalla se puede usar bien desde el celular.
- El vehículo no queda ligado a ningún transportista.

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
  6. Una solicitud de retiro tiene una sola certificación. Si ya existe, se muestra esa en vez de crear otra (por confirmar, y se cruza con la reinspección).
  7. El inspector busca su kit escaneando o escribiendo el código de la funda. Solo encuentra kits que le despacharon a él, y ve su contenido como información.
  8. Un kit solo se usa en una certificación (por confirmar).
  9. Queda registrado quién certifica, la fecha y la hora de inicio.
  10. Al iniciar, el inspector pasa al formulario por pasos de las secciones (depende de H18, que está en revisión).
  11. Los datos personales del conductor (DNI y brevete) solo los ven los roles que los necesitan. No es asesoría legal.
  12. La pantalla se puede usar bien desde el celular.
- Por confirmar: cómo se reconoce la naviera de la API contra el catálogo de E8 y qué pasa si no existe. Cliente y producto no tienen catálogo.

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
- Gestión de patio y stock.
- Gate out (EIR del gate out, fin de lavado) y la regla de exigir certificación al cerrar un gate out.
- Técnicos reefer y estructura, customer, inspector estructura gate in.
- Extranet: operadores y sus usuarios.
- Tipos de contenedor distintos de 40'.

## Suposiciones por confirmar
- Los inspectores siempre tienen internet.
- SIGLA solo registra el resultado del checkpoint; el aviso de cinta rota se hace fuera del sistema.
- Cómo se migra lo que hoy está en BUMMOCK, y si hacen falta reportes o historial por contenedor.
- Si el despacho de dispositivos sueltos a una zona de patio (que aparece en el ER) sirve para la reinspección.
