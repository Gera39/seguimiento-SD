<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Login');
});

Route::get('/recuperar-contrasena', function () {
    return Inertia::render('PasswordRecoveryView');
})->name('password.recovery');

Route::get('/dashboard', function () {
    return Inertia::render('DashboardView');
})->name('dashboard');

Route::get('/prueba', function () {
    return Inertia::render('PuebaView');
})->name('demo.preview');

Route::get('/docentes', function () {
    return Inertia::render('DocentesView');
})->name('demo.docentes');

Route::get('/reportes', function () {
    return Inertia::render('ReporteSecuencias');
})->name('demo.reportes');

Route::get('/validaciones', function () {
    return Inertia::render('ValidacionSecuencias');
})->name('demo.validaciones');

Route::get('/importar-datos', function () {
    return Inertia::render('ImportarDatosView');
})->name('demo.importar');

Route::get('/asignacion-permisos', function () {
    return Inertia::render('AsignacionPermisos');
})->name('demo.permisos');

Route::get('/visualizacion-validacion', function () {
    return Inertia::render('SecuenciaRevision');
})->name('demo.revision');

Route::get('/mis-secuencias', function () {
    return Inertia::render('Secuencias');
})->name('demo.secuencias');

Route::get('/visualizacion-secuencia', function () {
    return Inertia::render('VisualizacionSecuencia');
})->name('demo.visualizacion');

Route::get('/validacion-final', function () {
    return Inertia::render('ValidacionFinal');
})->name('demo.validacion-final');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
