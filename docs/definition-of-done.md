# Definition of Done · SIGLA 2.0 (TOS)

**Estado:** aprobada por el Product Owner / Scrum Master el 2026-10-28.

Una historia se considera terminada solo si cumple todo lo de abajo. Si falta algo, no cuenta como terminada.

**Duración del sprint:** 1 semana (decisión del 2026-10-28).

## Parte 1 · Funcionamiento
1. Cumple todos los criterios de aceptación de su historia.
2. Cada pantalla se puede usar bien desde el celular.
3. Cada pantalla y acción solo está disponible para los roles que corresponden.
4. El usuario la probó y dio su visto bueno antes de abrir el PR.

## Parte 2 · Calidad
1. Tiene pruebas automáticas que cubren sus criterios de aceptación, y todas pasan. Las pruebas se escriben con PHPUnit (decisión del 2026-10-29). (A revisar en el Sprint Planning del Sprint 1 si el tiempo alcanza.)
2. Las pruebas que ya existían siguen pasando.
3. El código sigue las rules registradas en `.ai/rules/`.
4. Los cambios en la base de datos se aplican desde cero sin errores y el sistema arranca con ellos.
5. Pasa la revisión de formato y estilo del código que se defina en el proyecto.
6. No quedan errores ni avisos en pantalla, ni código sobrante de pruebas.
7. Los datos personales (DNI y brevete) no se muestran ni se guardan donde no corresponde, cuando la historia los toca. No es asesoría legal.

## Parte 3 · Registro y entrega
1. El diseño de la historia con pantallas está aprobado por el usuario en el lienzo, con el ID de la historia en el nombre.
2. Las dudas marcadas "por confirmar" en la historia están resueltas y escritas en el backlog antes de empezar a construir.
3. La tarea está marcada como terminada en `docs/sprint-N.md`.
4. Si el trabajo cambió algo del producto (un criterio, una regla o una historia), el cambio queda actualizado en `docs/product-backlog.md` y el usuario lo vio.
5. Si el usuario corrigió algo de la forma de escribir el código, Claude Code le preguntó si lo anota en las rules.
6. Hay un PR abierto con lo hecho, listo para su revisión. El merge lo hace el usuario.
