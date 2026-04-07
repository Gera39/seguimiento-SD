# UTH - Arquitectura Inicial del Monolito

## Framework seleccionado

Se mantiene `Laravel 12` como framework principal por tres razones practicas:

1. El repositorio ya esta montado sobre Laravel 12 + Inertia + Vue 3, por lo que el costo de evolucion es mucho menor que migrar a Django o ASP.NET Core.
2. Laravel resuelve bien el patron MVC, autenticacion, politicas, colas, sesiones, mail, eventos y validaciones redundantes de servidor.
3. La integracion con SQL Server es estable usando `sqlsrv`, y el frontend actual ya esta organizado para trabajar sobre Inertia sin romper la comunicacion cliente-servidor.

Stack base recomendado:

- Backend: Laravel 12, PHP 8.2+, SQL Server, Laravel Sanctum para sesiones web/API interna si despues se requiere.
- Frontend: Inertia.js + Vue 3 + Vite.
- Seguridad: MFA tipo TOTP como principal y `EMAIL_OTP` como respaldo institucional.
- Sesiones: driver `database` sobre SQL Server, regeneracion de sesion tras login/MFA y cierre de otras sesiones al cambiar password.

## Analisis de entidades y relaciones

### Dominio de seguridad

- `users`: identidad autenticable del sistema.
- `roles`: catalogo de roles fijos (`ADMIN`, `DIRECTIVO`, `DOCENTE`, `REVISOR`).
- `permissions`: permisos atomicos para RBAC.
- `role_permissions`: relacion N:M entre roles y permisos.
- `user_role_assignments`: asignacion de roles por usuario, con alcance opcional por carrera.
- `user_mfa_methods`: metodos MFA activos por usuario.
- `user_mfa_recovery_codes`: codigos de recuperacion por metodo MFA.
- `auth_login_audits`: bitacora de login, MFA y logout.
- `sessions`: sesiones persistentes con bandera de MFA verificada.

Relaciones clave:

- Un `user` puede tener muchos `user_role_assignments`.
- Un `role` tiene muchos `permissions` via `role_permissions`.
- Un `user` puede tener muchos `user_mfa_methods`.
- Un `user` genera muchos `auth_login_audits`.

### Dominio academico

- `careers`: catalogo institucional de carreras/programas.
- `subjects`: catalogo maestro de asignaturas.
- `academic_periods`: periodos oficiales.
- `groups`: grupo academico por carrera y periodo.
- `career_subjects`: malla curricular, relacion N:M entre carrera y asignatura con atributos academicos.
- `group_subject_offerings`: apertura de una asignatura curricular dentro de un grupo.
- `teacher_subject_assignments`: asignacion del docente a la oferta academica.

Relaciones clave:

- Una `career` tiene muchos `groups`.
- Una `career` se vincula con muchas `subjects` via `career_subjects`.
- Un `group` ofrece muchas asignaturas via `group_subject_offerings`.
- Un `teacher_subject_assignment` identifica al docente responsable del contexto academico de la planeacion.

### Dominio de planeaciones didacticas

- `planning_statuses`: catalogo de estados de la maquina.
- `planning_transition_rules`: matriz formal de transiciones permitidas por rol.
- `didactic_plans`: caratula y raiz de la planeacion.
- `didactic_plan_units`: unidades de aprendizaje.
- `didactic_plan_modules`: modulos o sesiones dentro de cada unidad.
- `didactic_plan_evaluation_criteria`: criterios de evaluacion por plan y opcionalmente por unidad.
- `didactic_plan_references`: bibliografia/webgrafia/recursos.
- `didactic_plan_reviews`: ronda de revision tecnica o final.
- `didactic_plan_review_comments`: observaciones puntuales sobre plan, unidad, modulo o criterio.
- `didactic_plan_status_history`: auditoria completa de cambios de estado.
- `didactic_plan_validation_snapshots`: fotografia de validaciones al guardar, enviar y autorizar.
- `vw_didactic_plan_totals`: vista agregada para horas, modulos y porcentajes.

Relaciones clave:

- Un `teacher_subject_assignment` puede tener muchas `didactic_plans`.
- Una `didactic_plan` tiene muchas `didactic_plan_units`.
- Una `didactic_plan_unit` tiene muchos `didactic_plan_modules`.
- Una `didactic_plan` tiene muchos `didactic_plan_evaluation_criteria`.
- Una `didactic_plan` tiene muchas `didactic_plan_reviews`.
- Una `didactic_plan` tiene muchos eventos en `didactic_plan_status_history`.

## Maquina de estados

Estados iniciales definidos:

1. `DRAFT`
2. `SUBMITTED`
3. `UNDER_REVIEW`
4. `FEEDBACK`
5. `AUTHORIZED`
6. `REJECTED`

Transiciones permitidas en la primera version:

1. `DRAFT -> SUBMITTED` por `DOCENTE`
2. `FEEDBACK -> SUBMITTED` por `DOCENTE`
3. `SUBMITTED -> UNDER_REVIEW` por `REVISOR`
4. `UNDER_REVIEW -> FEEDBACK` por `REVISOR`
5. `UNDER_REVIEW -> AUTHORIZED` por `DIRECTIVO`
6. `UNDER_REVIEW -> REJECTED` por `DIRECTIVO`

Regla critica de integridad:

- Cuando la planeacion esta en `SUBMITTED`, `UNDER_REVIEW`, `AUTHORIZED` o `REJECTED`, el docente no puede editar ni eliminar.
- Esa restriccion no queda solo en Laravel: el script SQL agrega triggers para bloquear cambios directos sobre plan, unidades, modulos, criterios y referencias cuando el estado no es editable.
- `FEEDBACK` reabre la edicion, pero ya no permite eliminacion, para conservar trazabilidad.

## Validaciones de negocio

Las validaciones deben ejecutarse en dos capas:

### Cliente

- Vue valida campos requeridos, formatos, rangos, horas no negativas, suma local de porcentajes y feedback inmediato.
- No sustituye la validacion del servidor.

### Servidor

- `FormRequest` valida tipos, obligatoriedad y reglas contextuales.
- Un servicio de dominio valida:
  - suma de `planned_hours` por unidades y modulos;
  - que `SUM(progress_percentage)` por planeacion no exceda 100;
  - que `SUM(weight_percentage)` de criterios no exceda 100;
  - que al enviar a revision o autorizar, los totales requeridos lleguen exactamente a 100;
  - coherencia minima entre criterio, evidencia e instrumento de evaluacion.
- El resultado se guarda en `didactic_plan_validation_snapshots`.

## Estructura de carpetas propuesta

```text
app/
  Domain/
    Academic/
      Models/
      Services/
      Repositories/
      DTOs/
    Planning/
      Models/
      Enums/
      Services/
      StateMachine/
      Validators/
      Actions/
      Repositories/
    Security/
      Models/
      Services/
      Actions/
      DTOs/
  Http/
    Controllers/
      Auth/
      Admin/
      Academic/
      Planning/
      Review/
      Reports/
    Middleware/
    Requests/
      Auth/
      Planning/
      Review/
      Catalogs/
    Resources/
  Policies/
  Providers/
  Support/
    Exceptions/
    Helpers/
    ValueObjects/
database/
  migrations/
  seeders/
  factories/
  sqlserver/
resources/
  js/
    Pages/
      Auth/
      Admin/
      Planning/
      Review/
      Reports/
    Components/
      Auth/
      Planning/
      Review/
      Shared/
    Layouts/
  css/
routes/
  web.php
  auth.php
  admin.php
  planning.php
  review.php
tests/
  Feature/
    Auth/
    Planning/
    Review/
  Unit/
    Planning/
    Security/
```

Notas practicas:

- `Domain/Planning/StateMachine` debe contener la logica de transiciones, no los controladores.
- `Policies` define autorizacion por recurso.
- `Http/Requests` centraliza validacion server-side.
- `routes/*.php` separa la superficie web por modulo, manteniendo el monolito ordenado.

## Middlewares necesarios

### 1. `auth`

Responsabilidad:

- Garantizar que el usuario exista y tenga sesion valida.

Aplica en:

- Todo el sistema excepto login, recuperacion y desafio MFA.

### 2. `active.user`

Responsabilidad:

- Bloquear usuarios desactivados aunque su sesion siga viva.
- Forzar logout si `users.is_active = 0`.

Aplica en:

- Todo acceso autenticado.

### 3. `session.hardened`

Responsabilidad:

- Regenerar sesion en login.
- Validar expiracion de inactividad.
- Comparar huella minima de sesion (`user_agent` y tolerancia de IP si aplica).
- Cerrar sesion si la bandera MFA aun no fue completada para una ruta protegida.

Aplica en:

- Todo acceso autenticado despues del login.

### 4. `mfa.enrolled`

Responsabilidad:

- Si el usuario no tiene `user_mfa_methods.confirmed_at`, redirigir al flujo de activacion MFA.
- Para `ADMIN`, `DIRECTIVO` y `REVISOR` debe ser obligatorio desde el primer acceso.
- Para `DOCENTE`, puede exigirse al primer ingreso productivo si asi lo define la politica institucional.

Aplica en:

- Toda la aplicacion productiva.

### 5. `mfa.verified`

Responsabilidad:

- Validar que la sesion actual ya completo el segundo factor.
- Leer `sessions.is_mfa_verified`.
- Expulsar o reenviar al desafio si el segundo factor expiro o aun no se completa.

Aplica en:

- Todo panel autenticado.

### 6. `role`

Responsabilidad:

- Autorizar acceso por rol global.
- Ejemplo: `role:ADMIN` o `role:REVISOR,DIRECTIVO`.

Aplica en:

- Grupos de rutas por panel.

### 7. `permission`

Responsabilidad:

- Resolver permisos atomicos via RBAC.
- Evitar que el codigo dependa solo del nombre del rol cuando la accion es mas fina.

Aplica en:

- Operaciones sensibles como autorizar, gestionar catalogos o sobrescribir workflow.

### 8. `career.scope`

Responsabilidad:

- Validar que `DIRECTIVO` o `REVISOR` solo accedan a carreras dentro de su alcance en `user_role_assignments.career_id`.
- Si el rol es global (`career_id` nulo), permite acceso total.

Aplica en:

- Revision, reportes y catalogos filtrados por carrera.

### 9. `plan.scope`

Responsabilidad:

- Resolver si el usuario autenticado puede ver la planeacion solicitada.
- `DOCENTE`: solo sus propias planeaciones.
- `REVISOR`: solo planeaciones asignadas o dentro de su alcance.
- `DIRECTIVO`: planeaciones de su carrera o alcance global.
- `ADMIN`: acceso total.

Aplica en:

- Rutas con `didactic_plan`.

### 10. `plan.editable`

Responsabilidad:

- Confirmar en Laravel que la planeacion esta en un estado editable antes de mostrar formularios de edicion.
- Debe leer `planning_statuses.is_teacher_editable`.

Aplica en:

- Edicion y actualizacion de la planeacion por el docente.

Importante:

- `plan.editable` no sustituye la proteccion de base de datos.
- La regla real de bloqueo queda en tres niveles: middleware/policy, servicio de dominio y trigger SQL.

## Politicas por rol

Grupos de acceso recomendados:

```php
Route::middleware(['auth', 'active.user', 'session.hardened', 'mfa.enrolled', 'mfa.verified'])->group(function () {
    Route::middleware(['role:ADMIN'])->prefix('admin');
    Route::middleware(['role:DOCENTE'])->prefix('docente');
    Route::middleware(['role:REVISOR,DIRECTIVO', 'career.scope'])->prefix('revision');
    Route::middleware(['role:DIRECTIVO,ADMIN'])->prefix('autorizacion');
});
```

Reglas operativas:

- `DOCENTE` crea, edita y envia solo sus planeaciones.
- `REVISOR` revisa, comenta y devuelve retroalimentacion.
- `DIRECTIVO` autoriza o rechaza en la fase final.
- `ADMIN` administra catalogos, usuarios, roles y puede intervenir en contingencias auditadas.

## Siguiente iteracion recomendada

1. Convertir este modelo SQL a migraciones Laravel para no depender de cambios manuales.
2. Implementar `Enums` y `StateMachine` del dominio de planeaciones.
3. Sustituir el login visual actual por autenticacion real con MFA.
4. Activar `sqlsrv` en configuracion y mover `SESSION_DRIVER=database`.
5. Crear pruebas Feature para bloqueo de edicion despues de `SUBMITTED`.
