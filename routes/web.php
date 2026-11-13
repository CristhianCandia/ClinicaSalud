<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\PacienteRegistroController;
use App\Http\Controllers\PacientePerfilController;
use App\Http\Controllers\ProgramarCitaController;

// CU-01 Iniciar sesión
Route::get('/', fn () => redirect()->route(auth()->check() ? 'inicio' : 'login'));

Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'iniciarSesion'])
    ->middleware('throttle:5,1')   // máximo 5 intentos por minuto
    ->name('login.enviar');

Route::middleware('auth')->group(function () {
    Route::get('/inicio', [AuthController::class, 'inicio'])->name('inicio');
    Route::post('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');
});

// Ruta de prueba del control por rol (para verificar el 403)
Route::middleware(['auth', 'rol:ADMINISTRADOR'])
    ->get('/administracion', fn () => 'Solo administradores')
    ->name('administracion');

// RUTAS DE ADMINISTRADOR
Route::middleware(['auth', 'rol:ADMINISTRADOR'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Gestión de Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle');

    // Gestión de Especialidades
    Route::get('/especialidades', [EspecialidadController::class, 'index'])->name('especialidades.index');
    Route::post('/especialidades', [EspecialidadController::class, 'store'])->name('especialidades.store');
    Route::get('/especialidades/{id}/edit', [EspecialidadController::class, 'edit'])->name('especialidades.edit');
    Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])->name('especialidades.update');
    Route::patch('/especialidades/{id}/toggle', [EspecialidadController::class, 'toggleEstado'])->name('especialidades.toggle');

    // Gestión de Médicos
    Route::get('/medicos', [MedicoController::class, 'index'])->name('medicos.index');
    Route::get('/medicos/nuevo', [MedicoController::class, 'create'])->name('medicos.create');
    Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
    Route::get('/medicos/{id}/edit', [MedicoController::class, 'edit'])->name('medicos.edit');
    Route::put('/medicos/{id}', [MedicoController::class, 'update'])->name('medicos.update');
    Route::patch('/medicos/{id}/toggle', [MedicoController::class, 'toggleEstado'])->name('medicos.toggle');

    // Gestión de Horarios (CU-08)
    Route::get('/horarios', [HorarioController::class, 'index'])->name('horarios.index');
    Route::post('/horarios', [HorarioController::class, 'store'])->name('horarios.store');
    Route::get('/horarios/{id}/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
    Route::put('/horarios/{id}', [HorarioController::class, 'update'])->name('horarios.update');
    Route::patch('/horarios/{id}/toggle', [HorarioController::class, 'toggleEstado'])->name('horarios.toggle');
});

// RUTAS DE RECEPCIONISTA
Route::middleware(['auth', 'rol:RECEPCIONISTA'])->prefix('recepcionista')->name('recepcionista.')->group(function () {
    Route::get('/dashboard', function () {
        return view('recepcionista.dashboard');
    })->name('dashboard');

    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
    Route::get('/pacientes/{id}/edit', [PacienteController::class, 'edit'])->name('pacientes.edit');
    Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
    Route::patch('/pacientes/{id}/toggle', [PacienteController::class, 'toggleEstado'])->name('pacientes.toggle');

    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/nuevo', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
});

// CU-05: registro público del paciente
Route::get('/registro', [PacienteRegistroController::class, 'create'])->name('registro');
Route::post('/registro', [PacienteRegistroController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('registro.guardar');

// CU-05: perfil del paciente
Route::middleware(['auth', 'rol:PACIENTE'])->group(function () {
    Route::get('/mi-perfil', [PacientePerfilController::class, 'edit'])->name('paciente.perfil');
    Route::put('/mi-perfil', [PacientePerfilController::class, 'update'])->name('paciente.perfil.update');
});

// CU-09: consulta de disponibilidad (recepcionista y paciente)
Route::middleware(['auth', 'rol:RECEPCIONISTA,PACIENTE'])
    ->get('/disponibilidad', [DisponibilidadController::class, 'index'])
    ->name('disponibilidad.index');

// CU-10: programar cita (recepcionista y paciente)
// Importante: /citas/programar debe ir antes de /citas/{id}
Route::middleware(['auth', 'rol:RECEPCIONISTA,PACIENTE'])->group(function () {
    Route::get('/citas/programar', [ProgramarCitaController::class, 'create'])->name('citas.create');
    Route::post('/citas', [ProgramarCitaController::class, 'store'])->name('citas.store');
    Route::get('/citas/{id}', [ProgramarCitaController::class, 'comprobante'])->name('citas.comprobante');
});