<?php

namespace App\Domain\Admin\Imports;

use App\Domain\Admin\UserManagementService;
use App\Models\Career;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserImportService
{
    public const TEMPLATE_HEADERS = [
        'numero_empleado',
        'nombre',
        'correo',
        'contrasena_temporal',
        'roles',
        'carreras_revisor',
        'forzar_cambio_contrasena',
        'activo',
    ];

    public function __construct(
        protected UserImportWorkbook $workbook,
        protected UserManagementService $userManagementService,
    ) {
    }

    public function templateRulesFor(User $actor): array
    {
        $allowedRoles = $this->userManagementService->availableRolesFor($actor)
            ->pluck('code')
            ->implode(', ');

        return [
            'Descarga y usa la plantilla antes de cargar el archivo. El orden de columnas debe mantenerse igual.',
            "Los roles permitidos para tu perfil son: {$allowedRoles}. Se pueden combinar con coma, por ejemplo: DOCENTE,REVISOR.",
            'Si una fila trae rol REVISOR, la columna carreras_revisor puede ir vacia para alcance global o incluir codigos activos separados por coma.',
            'La cuenta se identifica por correo. Si el correo ya existe, el registro se actualiza; si no existe, se crea.',
            'La contrasena temporal es obligatoria para cuentas nuevas. En cuentas existentes puede dejarse vacia para conservar la actual.',
            'Las columnas forzar_cambio_contrasena y activo aceptan SI o NO.',
        ];
    }

    public function templatePathFor(User $actor): string
    {
        $sampleCareer = Career::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->value('code');

        $allowedRoles = $this->userManagementService->availableRolesFor($actor)
            ->pluck('code')
            ->values();

        $sampleRows = [
            [
                'DOC-100',
                'Docente Demo',
                'docente.demo@uth.edu.mx',
                'Temporal123!',
                $allowedRoles->contains('DOCENTE') ? 'DOCENTE' : $allowedRoles->first(),
                '',
                'SI',
                'SI',
            ],
            [
                'REV-200',
                'Revisor Demo',
                'revisor.demo@uth.edu.mx',
                'Temporal123!',
                $allowedRoles->contains('REVISOR') ? 'REVISOR' : $allowedRoles->first(),
                $sampleCareer ?? '',
                'SI',
                'SI',
            ],
        ];

        return $this->workbook->create(self::TEMPLATE_HEADERS, $sampleRows);
    }

    public function analyze(UploadedFile $file, User $actor): array
    {
        $sheet = $this->workbook->read($file->getRealPath());
        $headers = array_map([$this, 'normalizeHeader'], $sheet['headers']);

        if ($headers !== self::TEMPLATE_HEADERS) {
            throw new RuntimeException('La plantilla no coincide con el formato esperado. Descarga la version actual antes de importar.');
        }

        if ($sheet['rows'] === []) {
            throw new RuntimeException('El archivo no contiene registros para validar.');
        }

        if (count($sheet['rows']) > 1000) {
            throw new RuntimeException('El archivo supera el limite permitido de 1000 filas por carga.');
        }

        $allowedRoles = $this->userManagementService->availableRolesFor($actor)
            ->pluck('code')
            ->all();
        $activeCareers = Career::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'short_name', 'name'])
            ->keyBy(fn (Career $career) => strtoupper($career->code));
        $existingUsersByEmail = User::query()
            ->get()
            ->keyBy(fn (User $user) => strtolower($user->email));
        $existingUsersByEmployeeNumber = User::query()
            ->whereNotNull('employee_number')
            ->get()
            ->keyBy(fn (User $user) => strtolower((string) $user->employee_number));

        $emailOccurrences = [];
        $employeeOccurrences = [];

        foreach ($sheet['rows'] as $row) {
            $mapped = $this->mapRow($row);

            if ($mapped['correo'] !== '') {
                $emailOccurrences[strtolower($mapped['correo'])] = ($emailOccurrences[strtolower($mapped['correo'])] ?? 0) + 1;
            }

            if ($mapped['numero_empleado'] !== '') {
                $employeeOccurrences[strtolower($mapped['numero_empleado'])] = ($employeeOccurrences[strtolower($mapped['numero_empleado'])] ?? 0) + 1;
            }
        }

        $displayRows = [];
        $preparedRows = [];
        $toCreate = 0;
        $toUpdate = 0;
        $invalid = 0;

        foreach ($sheet['rows'] as $index => $row) {
            $rowNumber = $index + 2;
            $mapped = $this->mapRow($row);
            $existingUser = $existingUsersByEmail->get(strtolower($mapped['correo']));
            $roles = $this->parseList($mapped['roles'], true);
            $reviewerCareerCodes = $this->parseList($mapped['carreras_revisor'], true);
            $errors = [];

            $employeeNumber = $this->normalizeText($mapped['numero_empleado']);
            $name = $this->normalizeText($mapped['nombre']);
            $emailText = $this->normalizeText($mapped['correo']);
            $email = $emailText !== null ? strtolower($emailText) : '';
            $password = trim((string) $mapped['contrasena_temporal']);
            $mustChangePassword = $this->parseBooleanValue(
                $mapped['forzar_cambio_contrasena'],
                default: true,
                fieldLabel: 'forzar_cambio_contrasena',
                errors: $errors,
            );
            $isActive = $this->parseBooleanValue(
                $mapped['activo'],
                default: true,
                fieldLabel: 'activo',
                errors: $errors,
            );

            if ($employeeNumber !== null && mb_strlen($employeeNumber) > 30) {
                $errors[] = 'El numero_empleado no puede exceder 30 caracteres.';
            }

            if ($name === null) {
                $errors[] = 'El nombre es obligatorio.';
            } elseif (mb_strlen($name) > 255) {
                $errors[] = 'El nombre no puede exceder 255 caracteres.';
            }

            if ($email === '') {
                $errors[] = 'El correo es obligatorio.';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo no tiene un formato valido.';
            } elseif (($emailOccurrences[$email] ?? 0) > 1) {
                $errors[] = 'El correo se repite dentro del mismo archivo.';
            }

            if ($employeeNumber !== null && ($employeeOccurrences[strtolower($employeeNumber)] ?? 0) > 1) {
                $errors[] = 'El numero_empleado se repite dentro del mismo archivo.';
            }

            if ($roles === []) {
                $errors[] = 'Debes indicar al menos un rol.';
            }

            $invalidRoles = array_values(array_diff($roles, $allowedRoles));

            if ($invalidRoles !== []) {
                $errors[] = 'Se detectaron roles no permitidos: '.implode(', ', $invalidRoles).'.';
            }

            if ($password !== '' && mb_strlen($password) < 8) {
                $errors[] = 'La contrasena temporal debe tener al menos 8 caracteres.';
            }

            if ($existingUser === null && $password === '') {
                $errors[] = 'La contrasena temporal es obligatoria para usuarios nuevos.';
            }

            if (! in_array('REVISOR', $roles, true) && $reviewerCareerCodes !== []) {
                $errors[] = 'Solo puedes indicar carreras_revisor si la fila incluye el rol REVISOR.';
            }

            $reviewerCareerIds = [];
            $reviewerCareerLabels = [];

            foreach ($reviewerCareerCodes as $careerCode) {
                $career = $activeCareers->get($careerCode);

                if ($career === null) {
                    $errors[] = "La carrera {$careerCode} no existe o no esta activa.";
                    continue;
                }

                $reviewerCareerIds[] = $career->id;
                $reviewerCareerLabels[] = $career->short_name ?: $career->name;
            }

            if ($employeeNumber !== null) {
                $employeeOwner = $existingUsersByEmployeeNumber->get(strtolower($employeeNumber));

                if ($employeeOwner !== null && ($existingUser === null || ! $employeeOwner->is($existingUser))) {
                    $errors[] = 'El numero_empleado ya pertenece a otra cuenta.';
                }
            }

            if ($existingUser !== null && $actor->is($existingUser) && $isActive === false) {
                $errors[] = 'No puedes desactivar tu propia cuenta desde la importacion.';
            }

            $hasErrors = $errors !== [];
            $action = $existingUser === null ? 'CREAR' : 'ACTUALIZAR';

            if ($action === 'CREAR') {
                $toCreate++;
            } else {
                $toUpdate++;
            }

            if ($hasErrors) {
                $invalid++;
            } else {
                $preparedRows[] = [
                    'email' => $email,
                    'employee_number' => $employeeNumber,
                    'name' => $name,
                    'password' => $password === '' ? null : $password,
                    'must_change_password' => $mustChangePassword,
                    'is_active' => $isActive,
                    'roles' => $roles,
                    'reviewer_career_ids' => array_values(array_unique($reviewerCareerIds)),
                ];
            }

            $displayRows[] = [
                'row_number' => $rowNumber,
                'action' => $action,
                'employee_number' => $employeeNumber,
                'name' => $name ?? '',
                'email' => $email,
                'roles_label' => implode(', ', $roles),
                'reviewer_scope_label' => in_array('REVISOR', $roles, true)
                    ? ($reviewerCareerLabels === [] ? 'Todas las carreras' : implode(', ', $reviewerCareerLabels))
                    : 'No aplica',
                'must_change_password' => $mustChangePassword,
                'is_active' => $isActive,
                'errors' => $errors,
            ];
        }

        return [
            'display' => [
                'file_name' => $file->getClientOriginalName(),
                'summary' => [
                    'detected' => count($sheet['rows']),
                    'valid' => count($preparedRows),
                    'invalid' => $invalid,
                    'to_create' => $toCreate,
                    'to_update' => $toUpdate,
                ],
                'can_import' => $preparedRows !== [] && $invalid === 0,
                'rows' => $displayRows,
            ],
            'prepared_rows' => $preparedRows,
        ];
    }

    public function import(array $preparedRows, User $actor): array
    {
        $availableRoles = $this->userManagementService->availableRolesFor($actor);
        $allowedRoleCodes = $availableRoles->pluck('code')->all();

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($preparedRows, $actor, $availableRoles, $allowedRoleCodes, &$created, &$updated): void {
            foreach ($preparedRows as $row) {
                $user = User::query()->where('email', $row['email'])->first();
                $isNew = $user === null;

                if ($isNew) {
                    $user = new User();
                    $user->email = $row['email'];
                    $user->email_verified_at = now();
                    $created++;
                } else {
                    $updated++;
                }

                $payload = [
                    'name' => $row['name'],
                    'must_change_password' => $row['must_change_password'],
                    'is_active' => $row['is_active'],
                ];

                if ($row['employee_number'] !== null) {
                    $payload['employee_number'] = $row['employee_number'];
                }

                if ($row['password'] !== null) {
                    $payload['password'] = Hash::make($row['password']);
                }

                $user->fill($payload);
                $user->save();

                $roleCodes = array_values(array_intersect($row['roles'], $allowedRoleCodes));
                $this->userManagementService->deactivateAssignableRoles($user, $availableRoles);
                $this->userManagementService->syncRoleAssignments($user, $roleCodes, $row['reviewer_career_ids'], $actor->id);
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($preparedRows),
        ];
    }

    protected function mapRow(array $row): array
    {
        $mapped = [];

        foreach (self::TEMPLATE_HEADERS as $index => $header) {
            $mapped[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $mapped;
    }

    protected function normalizeHeader(string $header): string
    {
        return strtolower(trim($header));
    }

    protected function normalizeText(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function parseList(string $value, bool $uppercase = false): array
    {
        $items = collect(preg_split('/[;,|]/', $value) ?: [])
            ->map(static fn ($item) => trim((string) $item))
            ->filter(static fn ($item) => $item !== '')
            ->map(fn ($item) => $uppercase ? strtoupper($item) : $item)
            ->unique()
            ->values();

        return $items->all();
    }

    protected function parseBooleanValue(string $value, bool $default, string $fieldLabel, array &$errors): bool
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '') {
            return $default;
        }

        return match ($normalized) {
            'SI', 'S', 'TRUE', '1', 'ACTIVO' => true,
            'NO', 'N', 'FALSE', '0', 'INACTIVO' => false,
            default => tap($default, function () use (&$errors, $fieldLabel): void {
                $errors[] = "El valor de {$fieldLabel} debe ser SI o NO.";
            }),
        };
    }
}
