<?php

namespace Database\Seeders\Security;

use App\Domain\Security\Enums\PermissionCode;
use App\Domain\Security\Enums\RoleCode;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SecurityCatalogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            RoleCode::ADMIN->value => ['name' => 'Administrador', 'description' => 'Control total del sistema'],
            RoleCode::DIRECTIVO->value => ['name' => 'Directivo', 'description' => 'Autorizacion final y supervision'],
            RoleCode::DOCENTE->value => ['name' => 'Docente', 'description' => 'Captura y envio de planeaciones'],
            RoleCode::REVISOR->value => ['name' => 'Revisor', 'description' => 'Revision tecnica y retroalimentacion'],
        ];

        foreach ($roles as $code => $attributes) {
            Role::query()->updateOrCreate(
                ['code' => $code],
                $attributes + ['is_system' => true],
            );
        }

        $permissions = [
            PermissionCode::USERS_MANAGE->value => ['name' => 'Gestionar usuarios', 'description' => 'Administrar usuarios'],
            PermissionCode::ROLES_ASSIGN->value => ['name' => 'Asignar roles', 'description' => 'Asignar y revocar roles'],
            PermissionCode::CATALOGS_MANAGE->value => ['name' => 'Gestionar catalogos', 'description' => 'Administrar catalogos academicos'],
            PermissionCode::PLANS_CREATE->value => ['name' => 'Crear planeaciones', 'description' => 'Crear planeaciones didacticas'],
            PermissionCode::PLANS_EDIT_OWN->value => ['name' => 'Editar planeaciones propias', 'description' => 'Editar planeaciones propias en estado editable'],
            PermissionCode::PLANS_DELETE_OWN->value => ['name' => 'Eliminar planeaciones propias', 'description' => 'Eliminar planeaciones propias en borrador'],
            PermissionCode::PLANS_SUBMIT_OWN->value => ['name' => 'Enviar planeaciones propias', 'description' => 'Solicitar revision'],
            PermissionCode::PLANS_VIEW_OWN->value => ['name' => 'Consultar planeaciones propias', 'description' => 'Consultar planeaciones propias'],
            PermissionCode::PLANS_REVIEW_ASSIGNED->value => ['name' => 'Revisar planeaciones asignadas', 'description' => 'Revision tecnica'],
            PermissionCode::PLANS_FEEDBACK_CREATE->value => ['name' => 'Emitir retroalimentacion', 'description' => 'Crear observaciones'],
            PermissionCode::PLANS_AUTHORIZE->value => ['name' => 'Autorizar planeaciones', 'description' => 'Autorizacion final'],
            PermissionCode::PLANS_VIEW_ALL->value => ['name' => 'Consultar todas las planeaciones', 'description' => 'Consulta institucional'],
            PermissionCode::REPORTS_VIEW->value => ['name' => 'Consultar reportes', 'description' => 'Consultar reportes'],
            PermissionCode::WORKFLOW_OVERRIDE->value => ['name' => 'Sobrescribir workflow', 'description' => 'Intervencion administrativa excepcional'],
        ];

        foreach ($permissions as $code => $attributes) {
            Permission::query()->updateOrCreate(
                ['code' => $code],
                $attributes,
            );
        }

        $rolePermissionMap = [
            RoleCode::ADMIN->value => array_keys($permissions),
            RoleCode::DIRECTIVO->value => [
                PermissionCode::PLANS_AUTHORIZE->value,
                PermissionCode::PLANS_VIEW_ALL->value,
                PermissionCode::REPORTS_VIEW->value,
            ],
            RoleCode::DOCENTE->value => [
                PermissionCode::PLANS_CREATE->value,
                PermissionCode::PLANS_EDIT_OWN->value,
                PermissionCode::PLANS_DELETE_OWN->value,
                PermissionCode::PLANS_SUBMIT_OWN->value,
                PermissionCode::PLANS_VIEW_OWN->value,
            ],
            RoleCode::REVISOR->value => [
                PermissionCode::PLANS_REVIEW_ASSIGNED->value,
                PermissionCode::PLANS_FEEDBACK_CREATE->value,
                PermissionCode::PLANS_VIEW_ALL->value,
                PermissionCode::REPORTS_VIEW->value,
            ],
        ];

        foreach ($rolePermissionMap as $roleCode => $permissionCodes) {
            $role = Role::query()->where('code', $roleCode)->firstOrFail();

            $permissionIds = Permission::query()
                ->whereIn('code', $permissionCodes)
                ->pluck('id')
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
