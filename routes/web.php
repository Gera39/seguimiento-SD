<?php

use App\Http\Controllers\Planning\DidacticPlanController;
use App\Http\Controllers\Planning\DidacticPlanTransitionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Login');
});

Route::get('/recuperar-contrasena', function () {
    return Inertia::render('PasswordRecoveryView');
})->name('password.recovery');

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('DashboardView');
    })->name('dashboard');

    Route::get('/visualizacion-secuencia/{didacticPlan}', [DidacticPlanController::class, 'show'])
        ->name('plans.show');

    Route::middleware('role:DOCENTE')->group(function () {
        Route::get('/panel-docente', function () {
            return Inertia::render('PanelDocente');
        })->name('panel.docente');

        Route::get('/mis-secuencias', [DidacticPlanController::class, 'index'])->name('demo.secuencias');
        Route::get('/planeaciones/crear', [DidacticPlanController::class, 'create'])->name('plans.create');
        Route::post('/planeaciones', [DidacticPlanController::class, 'store'])->name('plans.store');
        Route::get('/planeaciones/{didacticPlan}/editar', [DidacticPlanController::class, 'edit'])
            ->middleware('plan.editable')
            ->name('plans.edit');
        Route::match(['put', 'patch'], '/planeaciones/{didacticPlan}', [DidacticPlanController::class, 'update'])
            ->middleware('plan.editable')
            ->name('plans.update');
        Route::delete('/planeaciones/{didacticPlan}', [DidacticPlanController::class, 'destroy'])
            ->middleware('plan.editable')
            ->name('plans.destroy');
        Route::post('/planeaciones/{didacticPlan}/submit', [DidacticPlanTransitionController::class, 'submit'])
            ->middleware('plan.editable')
            ->name('plans.submit');
    });

    Route::middleware('role:REVISOR')->group(function () {
        Route::get('/panel-revisor', function () {
            return Inertia::render('PanelRevisor');
        })->name('panel.revisor');

        Route::get('/validaciones', function () {
            return Inertia::render('ValidacionSecuencias');
        })->name('demo.validaciones');

        Route::get('/visualizacion-validacion/{didacticPlan}', [DidacticPlanTransitionController::class, 'showReview'])
            ->name('plans.review.show');
        Route::post('/planeaciones/{didacticPlan}/start-review', [DidacticPlanTransitionController::class, 'startReview'])
            ->name('plans.start-review');
        Route::post('/planeaciones/{didacticPlan}/feedback', [DidacticPlanTransitionController::class, 'feedback'])
            ->name('plans.feedback');
    });

    Route::middleware('role:DIRECTIVO')->group(function () {
        Route::get('/panel-director', function () {
            return Inertia::render('PanelDirector');
        })->name('panel.director');

        Route::get('/panel-coordinacion', function () {
            return Inertia::render('PanelDirector');
        })->name('panel.coordinacion');

        Route::get('/validacion-final/{didacticPlan}', [DidacticPlanTransitionController::class, 'showFinalValidation'])
            ->name('plans.final.show');
        Route::post('/planeaciones/{didacticPlan}/authorize', [DidacticPlanTransitionController::class, 'authorizePlan'])
            ->name('plans.authorize');
    });

    Route::middleware('role:ADMIN,DIRECTIVO,REVISOR')->group(function () {
        Route::get('/reportes', function () {
            return Inertia::render('ReporteSecuencias');
        })->name('demo.reportes');
    });

    Route::middleware('role:ADMIN,DIRECTIVO')->group(function () {
        Route::get('/docentes', function () {
            return Inertia::render('DocentesView');
        })->name('demo.docentes');

        Route::get('/importar-datos', function () {
            return Inertia::render('ImportarDatosView');
        })->name('demo.importar');

        Route::get('/asignacion-permisos', function () {
            return Inertia::render('AsignacionPermisos');
        })->name('demo.permisos');
    });

    Route::get('/prueba', function () {
        return Inertia::render('PuebaView');
    })->name('demo.preview');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
