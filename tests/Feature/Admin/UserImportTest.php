<?php

namespace Tests\Feature\Admin;

use App\Domain\Admin\Imports\UserImportService;
use App\Domain\Admin\Imports\UserImportWorkbook;
use App\Domain\Security\Enums\RoleCode;
use App\Http\Controllers\Admin\UserImportController;
use App\Models\Career;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\Security\SecurityCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_the_user_import_template(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $this->assignRole($admin, RoleCode::ADMIN);

        $this->actingAs($admin)
            ->get(route('demo.importar.template', absolute: false))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=plantilla-importacion-usuarios.xlsx');
    }

    public function test_admin_can_validate_a_user_import_file_and_see_preview(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $career = Career::query()->create([
            'code' => 'TIC',
            'name' => 'Tecnologias de la Informacion',
            'short_name' => 'TIC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $this->assignRole($admin, RoleCode::ADMIN);

        $file = $this->makeWorkbook([
            ['EMP-900', 'Alta Masiva', 'alta.masiva@example.com', 'Temporal123!', 'DOCENTE,REVISOR', $career->code, 'SI', 'SI'],
        ]);

        $this->actingAs($admin)
            ->post(route('demo.importar.validate', absolute: false), [
                'file' => $file,
            ])
            ->assertRedirect(route('demo.importar', absolute: false))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->get(route('demo.importar', absolute: false))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ImportarDatosView')
                ->where('preview.summary.detected', 1)
                ->where('preview.summary.valid', 1)
                ->where('preview.can_import', true)
                ->where('preview.rows.0.action', 'CREAR')
                ->where('preview.rows.0.reviewer_scope_label', 'TIC'));
    }

    public function test_admin_can_import_valid_rows_from_excel_file(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $career = Career::query()->create([
            'code' => 'MEC',
            'name' => 'Mecatronica',
            'short_name' => 'MEC',
            'educational_level' => 'TSU',
            'duration_terms' => 6,
            'is_active' => true,
        ]);

        $this->assignRole($admin, RoleCode::ADMIN);

        $file = $this->makeWorkbook([
            ['EMP-901', 'Importado Uno', 'importado.uno@example.com', 'Temporal123!', 'DOCENTE', '', 'SI', 'SI'],
            ['EMP-902', 'Importado Dos', 'importado.dos@example.com', 'Temporal123!', 'REVISOR', $career->code, 'NO', 'NO'],
        ]);

        $this->actingAs($admin)
            ->post(route('demo.importar.validate', absolute: false), [
                'file' => $file,
            ])
            ->assertRedirect(route('demo.importar', absolute: false));

        $this->actingAs($admin)
            ->post(route('demo.importar.store', absolute: false))
            ->assertRedirect(route('demo.importar', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'importado.uno@example.com',
            'employee_number' => 'EMP-901',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'importado.dos@example.com',
            'employee_number' => 'EMP-902',
            'is_active' => false,
        ]);

        $importedReviewer = User::query()->where('email', 'importado.dos@example.com')->firstOrFail();
        $this->assertTrue($importedReviewer->hasRole(RoleCode::REVISOR));
        $this->assertDatabaseHas('user_role_assignments', [
            'user_id' => $importedReviewer->id,
            'career_id' => $career->id,
            'is_active' => true,
        ]);
    }

    public function test_invalid_rows_block_the_import_until_the_file_is_fixed(): void
    {
        $this->seed(SecurityCatalogSeeder::class);

        $admin = User::factory()->create();
        $this->assignRole($admin, RoleCode::ADMIN);

        $file = $this->makeWorkbook([
            ['EMP-903', 'Fila Invalida', 'fila.invalida@example.com', 'Temporal123!', 'DOCENTE', 'TIC', 'SI', 'SI'],
        ]);

        $this->actingAs($admin)
            ->post(route('demo.importar.validate', absolute: false), [
                'file' => $file,
            ])
            ->assertRedirect(route('demo.importar', absolute: false));

        $this->actingAs($admin)
            ->get(route('demo.importar', absolute: false))
            ->assertInertia(fn (Assert $page) => $page
                ->where('preview.can_import', false)
                ->where('preview.summary.invalid', 1)
                ->where('preview.rows.0.errors.0', 'Solo puedes indicar carreras_revisor si la fila incluye el rol REVISOR.'));

        $this->actingAs($admin)
            ->post(route('demo.importar.store', absolute: false))
            ->assertRedirect(route('demo.importar', absolute: false));

        $this->assertDatabaseMissing('users', [
            'email' => 'fila.invalida@example.com',
        ]);
    }

    protected function makeWorkbook(array $rows): UploadedFile
    {
        $path = app(UserImportWorkbook::class)->create(UserImportService::TEMPLATE_HEADERS, $rows);

        return new UploadedFile(
            $path,
            'usuarios.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    protected function assignRole(User $user, RoleCode $roleCode): void
    {
        $roleId = Role::query()->where('code', $roleCode->value)->value('id');

        UserRoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'is_active' => true,
        ]);
    }
}
