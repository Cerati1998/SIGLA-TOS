# Flujo de trabajo de SIGLA 2.0

Solo usamos Scrum (Scrum Guide 2020). No usamos GSD.

## 1. Antes de empezar una historia

1. Trabaja solo la historia que el usuario te indique con su ID
   (por ejemplo: "trabaja la H01"). No elijas tú la historia ni pases
   a otra por tu cuenta.
2. Lee la historia en `docs/product-backlog.md` y sus tareas en
   `docs/sprint-N.md` (N es el sprint actual). Lee también
   `docs/definition-of-done.md`.
3. Dile al usuario, en pocas líneas:
   - qué entendiste de la historia,
   - qué vas a construir,
   - qué no vas a construir (lo que queda fuera).
4. Si falta información, pregunta de a una pregunta por vez.
   No supongas. Si algo es una suposición tuya, dilo como
   "suposición por confirmar".
5. No escribas código hasta que el usuario confirme tu resumen.
6. Si la historia no está en el sprint actual, o su diseño en el
   lienzo no está aprobado, avísalo y espera la decisión del usuario.

## 2. Diseño en el lienzo

El lienzo de diseño está en:
https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca

1. El usuario te pasará el enlace del lienzo. Con ese enlace, busca el
   diseño de la historia en la página de su épica (E1 a E9). El nombre
   del diseño lleva el ID de la historia (por ejemplo
   "H01 · Iniciar sesión").
   Si no puedes abrir el lienzo, díselo al usuario y pídele una captura
   de la pantalla. No construyas sin ver el diseño.
2. Construye solo desde un diseño que el usuario haya aprobado. Si no
   existe o no está aprobado, no lo inventes: avisa al usuario y espera.
3. Parte de las plantillas base (página "Plantillas base"): acceso,
   panel, lista con tabla y formulario. No crees layouts nuevos si una
   plantilla sirve.
4. Respeta la identidad visual aprobada (página "Identidad visual"):
   colores, tipografías y escala de tamaños. No uses valores de otra
   fuente.
5. Cada pantalla debe funcionar también en teléfono. Las plantillas
   tienen su versión móvil como guía.
6. Si el diseño y la historia se contradicen, o el diseño no cubre un
   caso (error, lista vacía, carga), pregunta al usuario. No decidas tú.
7. Si el usuario cambia el diseño durante la construcción, el lienzo
   se actualiza primero y después el código.

## 3. Construcción

1. Trabaja en una rama propia por historia, por ejemplo
   `feature/H01-iniciar-sesion`. Nunca trabajes directo en `main`.
2. Usa lo que ya trae el proyecto: Laravel 13, Livewire 4, Flux
   (versión gratuita), Tailwind 4 y Fortify. No agregues paquetes
   nuevos sin preguntar al usuario.
3. Usa las herramientas de Laravel Boost (documentación, base de
   datos, skills) en vez de recordar de memoria cómo funciona algo.
4. Construye solo lo que pide la historia. Si ves algo útil que no
   está en la historia, anótalo y dilo al final. No lo construyas.
5. Avanza en pasos pequeños y haz commits claros, uno por cambio con
   sentido.
6. Los textos que ve el usuario en pantalla van en español.
7. Escribe pruebas automáticas con PHPUnit para el comportamiento
   principal de la historia (el camino normal y los errores más
   importantes). Deben pasar antes de pedir la revisión. Pruebas
   mínimas: no busques cubrir todo.
8. Antes de dar la historia por terminada, cumple todo lo que pide
   `docs/definition-of-done.md`. Si algo no se puede cumplir, dilo.

## 4. Revisión y pull request (PR)

1. Cuando termines, revisa tu propio trabajo:
   - cada criterio de aceptación de la historia, uno por uno,
   - que se parezca al diseño aprobado del lienzo,
   - que se vea bien en teléfono (390 px) y en escritorio,
   - que pasen las pruebas y las herramientas de calidad del proyecto
     (Pint y Larastan).
2. Si algo falla o no se pudo cumplir, dilo claro. No lo ocultes ni lo
   des por terminado.
3. Cuando todo lo anterior esté en orden, avisa al usuario y dile cómo
   probar la historia. No abras el PR todavía: espera a que el usuario
   la pruebe y dé su visto bueno. Si pide cambios, hazlos y vuelve a
   avisarle.
4. Con el visto bueno del usuario, sube la rama y abre tú mismo el PR
   con `gh`. Va hacia `main` y su título lleva el ID de la historia
   (por ejemplo "H01 · Iniciar sesión").
5. La descripción del PR dice, en pocas líneas:
   - qué historia es y qué se hizo,
   - qué quedó fuera,
   - cómo probarlo,
   - lo que el usuario debe decidir, si hay algo.
6. Nunca hagas el merge. Lo hace el usuario después de revisar el PR.
7. Si el usuario pide cambios en el PR, hazlos en la misma rama y en el
   mismo PR. No abras otro.

## 5. Reglas permanentes (rules)

1. Cuando el usuario te corrija algo, o te diga cómo quiere que se haga
   algo, y eso podría aplicar a futuras historias, pregúntale una sola vez:
   "¿Quieres que sea siempre así? ¿Lo anoto en las rules?"
2. Solo anótala si el usuario responde que sí. Si dice que no, o que es
   solo para esta historia, no la anotes.
3. Para anotarla usa `record-rule` de Laravel Boost. No crees ni edites
   a mano archivos dentro de `.ai/rules/`.
4. Escribe la regla corta y clara, con la razón en una línea, y dile
   al usuario qué dice exactamente antes de guardarla.
5. No anotes como regla instrucciones sobre una sola pantalla o una sola
   historia. Tampoco anotes reglas que contradigan lo ya anotado:
   avisa de la contradicción y deja que el usuario decida.

## 6. Marcar las tareas en el sprint

1. Las tareas de la historia están en `docs/sprint-N.md` (N es el sprint
   actual), con su ID (T##) y la marca `[ ]` o `[x]`.
2. Marca `[x]` solo las tareas de la historia que trabajaste, y solo si
   cumplen lo que pide `docs/definition-of-done.md`.
3. Junto a la marca, agrega entre paréntesis una nota corta con la fecha
   y el PR (por ejemplo: "Hecho el 2026-11-03, PR #5").
4. El cambio va en el mismo PR de la historia, no en uno aparte.
5. No cambies el Sprint Goal, la capacidad, las horas estimadas ni las
   tareas de otras historias. No agregues ni quites tareas.
6. Si faltó una tarea, sobró una, o las horas se quedaron cortas, díselo
   al usuario. Él decide qué cambia en el sprint.
7. Si el trabajo cambió algo del producto (un criterio, una regla o una
   historia), propón el cambio en `docs/product-backlog.md` dentro del
   mismo PR y avísalo en la descripción del PR, para que el usuario lo
   vea antes del merge. No cambies el backlog por tu cuenta sin que el
   usuario lo vea.
