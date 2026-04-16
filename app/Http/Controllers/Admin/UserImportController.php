<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\Imports\UserImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ValidateUserImportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserImportController extends Controller
{
    public const PREVIEW_SESSION_KEY = 'admin.user_import.preview';

    public function __construct(
        protected UserImportService $userImportService,
    ) {
    }

    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $preview = $request->session()->get(self::PREVIEW_SESSION_KEY);

        return Inertia::render('ImportarDatosView', [
            'status' => session('status'),
            'templateUrl' => route('demo.importar.template', absolute: false),
            'validateUrl' => route('demo.importar.validate', absolute: false),
            'importUrl' => route('demo.importar.store', absolute: false),
            'rules' => $user !== null ? $this->userImportService->templateRulesFor($user) : [],
            'preview' => is_array($preview) ? ($preview['display'] ?? null) : null,
        ]);
    }

    public function downloadTemplate(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $path = $this->userImportService->templatePathFor($user);

        return response()->download($path, 'plantilla-importacion-usuarios.xlsx')->deleteFileAfterSend(true);
    }

    public function validateImport(ValidateUserImportRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        try {
            $preview = $this->userImportService->analyze($request->file('file'), $user);
        } catch (RuntimeException $exception) {
            $request->session()->forget(self::PREVIEW_SESSION_KEY);

            return redirect()
                ->route('demo.importar')
                ->withErrors(['file' => $exception->getMessage()]);
        }

        $request->session()->put(self::PREVIEW_SESSION_KEY, $preview);

        $display = $preview['display'] ?? null;
        $status = is_array($display) && ($display['can_import'] ?? false)
            ? 'Archivo validado correctamente. Ya puedes ejecutar la importacion.'
            : 'Archivo analizado. Corrige las filas con error y vuelve a validar.';

        return redirect()
            ->route('demo.importar')
            ->with('status', $status);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $preview = $request->session()->get(self::PREVIEW_SESSION_KEY);

        if (! is_array($preview) || ! isset($preview['prepared_rows'])) {
            return redirect()
                ->route('demo.importar')
                ->with('status', 'Primero valida un archivo antes de importarlo.');
        }

        $display = $preview['display'] ?? [];
        $preparedRows = $preview['prepared_rows'] ?? [];

        if (($display['can_import'] ?? false) !== true || ! is_array($preparedRows) || $preparedRows === []) {
            return redirect()
                ->route('demo.importar')
                ->with('status', 'La importacion no puede ejecutarse mientras existan errores de validacion.');
        }

        $result = $this->userImportService->import($preparedRows, $user);
        $request->session()->forget(self::PREVIEW_SESSION_KEY);

        return redirect()
            ->route('demo.importar')
            ->with('status', "Importacion completada: {$result['total']} filas procesadas, {$result['created']} creadas y {$result['updated']} actualizadas.");
    }
}
