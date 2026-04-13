# Propuesta: flujo de comentarios de revision con seguimiento de cambios

## Objetivo

Permitir que el revisor comente una secuencia didactica de forma puntual y que el docente pueda:

- ver exactamente que se le observo;
- responder cada observacion;
- marcar que ya realizo el cambio;
- dejar evidencia de antes y despues;
- regresar la secuencia a revision con trazabilidad.

La meta es que el sistema no solo diga "tiene comentarios", sino que cuente la historia completa del ajuste.

## Estado actual del sistema

Hoy ya existe:

- `didactic_plan_reviews`: guarda la revision general por ronda;
- `didactic_plan_review_comments`: guarda comentarios del revisor;
- pantalla de revisor para emitir observaciones;
- visualizacion general de la secuencia;
- flujo de estados `DRAFT -> SUBMITTED -> UNDER_REVIEW -> FEEDBACK -> ...`.

Hoy todavia falta:

- respuesta del docente por comentario;
- marcar si un comentario ya fue atendido;
- comparar contenido previo contra contenido corregido;
- distinguir comentarios abiertos, atendidos y validados;
- una UI de conversacion por observacion.

## Idea funcional recomendada

### 1. Comentario puntual por bloque

Cada comentario debe apuntar a una parte concreta de la secuencia:

- `PLAN`
- `UNIT`
- `MODULE`
- `EVALUATION`
- `REFERENCE`

Idealmente tambien debe guardar una referencia mas fina al campo afectado.

Ejemplos:

- `general_objective`
- `unit.title`
- `module.topic_description`
- `criterion.description`

## Flujo deseado

### Paso 1. Revisor observa

El revisor entra a la secuencia y genera observaciones como:

- "El objetivo general no esta redactado en terminos medibles."
- "En la Unidad 2 faltan horas practicas."
- "El criterio de evaluacion no coincide con la evidencia."

Cada observacion queda como un item independiente.

### Paso 2. El sistema congela evidencia del momento

Al crear el comentario, el sistema debe guardar una captura del valor observado.

Ejemplo:

- campo observado: `general_objective`
- valor observado: "Comprender el tema"

Esto sirve para poder mostrar despues el "antes".

### Paso 3. Docente corrige

Cuando la secuencia vuelve a `FEEDBACK`, el docente puede:

- abrir cada comentario;
- ver que le pidieron;
- editar la secuencia;
- responder el comentario con una nota tipo "Se actualizo el objetivo con verbo observable";
- marcarlo como "atendido".

### Paso 4. El sistema compara

Cuando el docente guarda cambios o reenvia la secuencia, el sistema debe capturar el valor nuevo del campo relacionado.

Ejemplo:

- antes: "Comprender el tema"
- despues: "Desarrollar una aplicacion web funcional usando componentes reutilizables"

### Paso 5. Revisor valida

En la siguiente ronda, el revisor ve por comentario:

- observacion original;
- respuesta del docente;
- antes;
- despues;
- estado del comentario.

Entonces puede:

- validarlo como resuelto;
- reabrirlo;
- agregar una nueva observacion.

## Estados sugeridos para cada comentario

En vez de solo `is_resolved`, conviene manejar algo asi:

- `OPEN`: comentario emitido por el revisor.
- `ADDRESSED`: el docente dice que ya lo atendio.
- `RESOLVED`: el revisor confirma que quedo correcto.
- `REOPENED`: el revisor considera que sigue pendiente.
- `CANCELLED`: comentario descartado por error o duplicado.

## Estructura de datos recomendada

### Opcion MVP sobre la tabla actual

Extender `didactic_plan_review_comments` con campos como:

- `field_path`
- `observed_value_snapshot`
- `teacher_response`
- `teacher_responded_at`
- `teacher_responded_by_user_id`
- `updated_value_snapshot`
- `comment_status_code`
- `validated_by_user_id`
- `validated_at`

Ventaja:

- cambio pequeño;
- rapido de implementar;
- suficiente para un primer release.

Desventaja:

- si luego quieres conversacion larga por comentario, se queda corto.

### Opcion mas robusta

Separar en dos tablas:

- `didactic_plan_review_comments`
- `didactic_plan_review_comment_events`

La primera seria el comentario principal.
La segunda guardaria eventos como:

- comentario del revisor;
- respuesta del docente;
- reapertura;
- validacion;
- nota adicional.

Ventaja:

- historial completo;
- mas flexible;
- mejor para auditoria.

Desventaja:

- implementacion mas larga.

## Recomendacion realista

Hacerlo en 2 fases.

### Fase 1. MVP funcional

Agregar a cada comentario:

- campo afectado;
- snapshot antes;
- respuesta del docente;
- snapshot despues;
- estado del comentario.

Con eso ya logras:

- comentarios puntuales;
- evidencia de cambio;
- validacion por parte del revisor.

### Fase 2. Historial conversacional

Agregar eventos o mensajes por comentario para permitir:

- varias respuestas;
- aclaraciones del revisor;
- reaperturas multiples;
- seguimiento historico fino.

## UI sugerida

### Vista del revisor

Cada comentario como tarjeta:

- ubicacion: `Unidad 1 > Modulo 2 > topic_description`
- severidad
- comentario del revisor
- valor anterior
- respuesta del docente
- valor nuevo
- estado

Acciones:

- `Validar cambio`
- `Reabrir comentario`
- `Agregar nueva observacion`

### Vista del docente

En el detalle de la secuencia o en una bandeja de feedback:

- lista de comentarios abiertos;
- filtro por pendientes/resueltos;
- formulario de respuesta por comentario;
- vista comparativa antes/despues.

Acciones:

- `Ir al campo observado`
- `Responder comentario`
- `Marcar como atendido`

## Regla importante de negocio

Un comentario no deberia cerrarse automaticamente solo porque el docente edito algo.

Lo correcto es:

1. el docente responde y marca atendido;
2. el revisor confirma si de verdad quedo resuelto.

Eso evita falsos positivos.

## Alcance recomendado para la primera implementacion

Primero implementar para estos bloques:

- objetivo general del plan;
- unidades;
- modulos;
- criterios de evaluacion.

Dejar para despues:

- referencias;
- comentarios sobre combinaciones de varios campos;
- diff visual palabra por palabra.

## Resultado esperado

Con este flujo el sistema ya no solo diria "hay observaciones", sino:

- que se observo;
- donde se observo;
- como estaba antes;
- que cambio el maestro;
- que respondio el maestro;
- si el revisor acepto el cambio.

Eso vuelve mucho mas claro el seguimiento academico y tambien sirve para auditoria institucional.

## Siguiente paso recomendado de implementacion

Orden sugerido:

1. extender modelo y migracion de comentarios;
2. mostrar comentarios en la vista del docente;
3. permitir respuesta del docente por comentario;
4. mostrar comparativa antes/despues al revisor;
5. permitir resolver o reabrir comentario.

## Nota para este proyecto

Con la estructura actual del sistema, este feature encaja bien con el flujo existente de `FEEDBACK` y `UNDER_REVIEW`.
No hace falta rehacer toda la planeacion; lo mejor es extender el modulo de revision que ya existe.
