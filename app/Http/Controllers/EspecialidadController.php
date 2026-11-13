<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

// CU-06 Gestionar especialidades
class EspecialidadController extends Controller
{
    public function index()
    {
        return $this->vista();
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $nombre = $this->normalizar($datos['nombre']);

        // Flujo 4 y excepción "Nombre repetido (normalizado)"
        if ($this->nombreRepetido($nombre)) {
            return back()->withInput()
                ->withErrors(['nombre' => 'La especialidad ya está registrada.']);
        }

        Especialidad::create([
            'nombre' => $nombre,
            'descripcion' => $datos['descripcion'] ?? null,
            'estado' => true,
        ]);

        return redirect()->route('admin.especialidades.index')
            ->with('exito', 'Especialidad registrada.');
    }

    public function edit($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        return $this->vista(['editando' => $especialidad]);
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $datos = $this->validar($request);
        $nombre = $this->normalizar($datos['nombre']);

        if ($this->nombreRepetido($nombre, $especialidad->id_especialidad)) {
            return back()->withInput()
                ->withErrors(['nombre' => 'La especialidad ya está registrada.']);
        }

        $especialidad->update([
            'nombre' => $nombre,
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return redirect()->route('admin.especialidades.index')
            ->with('exito', 'Especialidad actualizada.');
    }

    public function toggleEstado($id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $especialidad->update(['estado' => ! $especialidad->estado]);

        return redirect()->route('admin.especialidades.index')
            ->with('exito', $especialidad->estado ? 'Especialidad activada.' : 'Especialidad desactivada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:60'],
            'descripcion' => ['nullable', 'string', 'max:200'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 60 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 200 caracteres.',
        ]);
    }

    // Quita espacios sobrantes y deja uno solo entre palabras
    private function normalizar(string $texto): string
    {
        return preg_replace('/\s+/u', ' ', trim($texto));
    }

    // Compara sin importar mayúsculas ni espacios (RF04)
    private function nombreRepetido(string $nombre, ?int $ignorarId = null): bool
    {
        return Especialidad::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->when($ignorarId, fn ($q) => $q->where('id_especialidad', '!=', $ignorarId))
            ->exists();
    }

    private function vista(array $extra = [])
    {
        $especialidades = Especialidad::query()
            ->select('especialidad.*')
            ->selectRaw('(SELECT COUNT(*) FROM medico_especialidad me
                          WHERE me.id_especialidad = especialidad.id_especialidad) AS medicos_count')
            ->orderBy('nombre')
            ->get();

        return view('admin.especialidades.index', array_merge([
            'especialidades' => $especialidades,
            'editando' => null,
        ], $extra));
    }
}