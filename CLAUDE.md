<laravel-boost-guidelines>
=== .ai/flujo-de-trabajo rules ===

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

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
