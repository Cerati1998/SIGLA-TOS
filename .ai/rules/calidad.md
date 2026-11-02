---
paths:
  - 'app/**'
  - 'database/**'
  - 'routes/**'
  - 'tests/**'
  - phpstan.neon
  - pint.json
---

# Calidad

## Pruebas
PHPUnit, no Pest. Cada criterio de aceptación de la historia tiene al menos una prueba.

## Formato y análisis estático
Pint y Larastan deben pasar antes de abrir el PR. Larastan en nivel 7.
