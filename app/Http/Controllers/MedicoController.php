<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// CU-07 Gestionar médicos
class MedicoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $especialidad = $request->input('especialidad');
        $like = '%' . $buscar . '%';

        $medicos = Medico::query()
            ->with('especialidades')
            ->when($buscar !== '', function ($q) use ($like) {
                $q->where(function ($w) use ($like) {
                    $w->where('codigo', 'ilike', $like)
                      ->orWhere('nombres', 'ilike', $like)
                      ->orWhere('apellidos', 'ilike', $like);
                });
            })
            ->when($especialidad, fn ($q) => $q->whereHas('especialidades',
                fn ($e) => $e->where('especialidad.id_especialidad', $especialidad)))
            ->orderBy('codigo')
            ->get();

        return view('admin.medicos.index', [
            'medicos' => $medicos,
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'buscar' => $buscar,
            'especialidadSeleccionada' => $especialidad,
        ]);
    }

    public function create()
    {
        return view('admin.medicos.form', $this->datosFormulario(null));
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(null), $this->mensajes());

        DB::transaction(function () use ($datos) {
            // Flujo 5: código MED-0001, MED-0002, ... (mismo criterio que CU-04)
            $id = (int) DB::selectOne("SELECT nextval(pg_get_serial_sequence('medico','id_medico')) AS id")->id;

            $medico = new Medico();
            $medico->forceFill([
                'id_medico' => $id,
                'codigo' => 'MED-' . str_pad($id, 4, '0', STR_PAD_LEFT),
                'registro_profesional' => $datos['registro_profesional'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'id_usuario' => $datos['id_usuario'] ?? null,
                'estado' => true,
                'fecha_creacion' => now(),
            ])->save();

            $medico->especialidades()->attach($datos['especialidades']);
        });

        return redirect()->route('admin.medicos.index')->with('exito', 'Médico registrado.');
    }

    public function edit($id)
    {
        $medico = Medico::with('especialidades')->findOrFail($id);

        return view('admin.medicos.form', $this->datosFormulario($medico));
    }

    public function update(Request $request, $id)
    {
        $medico = Medico::findOrFail($id);
        $datos = $request->validate($this->reglas($medico->id_medico), $this->mensajes());

        DB::transaction(function () use ($medico, $datos) {
            $medico->forceFill([
                'registro_profesional' => $datos['registro_profesional'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'id_usuario' => $datos['id_usuario'] ?? null,
                'fecha_actualizacion' => now(),
            ])->save();

            $medico->especialidades()->sync($datos['especialidades']);
        });

        return redirect()->route('admin.medicos.index')->with('exito', 'Médico actualizado.');
    }

    public function toggleEstado($id)
    {
        $medico = Medico::findOrFail($id);
        $nuevoEstado = ! $medico->estado;

        // Igual que CU-04: al desactivar al médico, también se desactiva su cuenta
        DB::transaction(function () use ($medico, $nuevoEstado) {
            $medico->forceFill(['estado' => $nuevoEstado, 'fecha_actualizacion' => now()])->save();

            if ($medico->id_usuario) {
                Usuario::where('id_usuario', $medico->id_usuario)->update(['estado' => $nuevoEstado]);
            }
        });

        return back()->with('exito', $nuevoEstado ? 'Médico activado.' : 'Médico desactivado.');
    }

    private function reglas(?int $id): array
    {
        return [
            'registro_profesional' => ['required', 'string', 'max:20',
                Rule::unique('medico', 'registro_profesional')->ignore($id, 'id_medico')],
            'nombres' => ['required', 'string', 'max:60'],
            'apellidos' => ['required', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:15'],
            'correo' => ['required', 'email', 'max:120',
                Rule::unique('medico', 'correo')->ignore($id, 'id_medico')],
            'id_usuario' => ['nullable', 'integer', 'exists:usuario,id_usuario',
                Rule::unique('medico', 'id_usuario')->ignore($id, 'id_medico')],
            'especialidades' => ['required', 'array', 'min:1'],
            'especialidades.*' => ['integer', 'exists:especialidad,id_especialidad'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'registro_profesional.required' => 'El registro profesional es obligatorio.',
            'registro_profesional.unique' => 'Ya existe un médico con este registro profesional.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'correo.unique' => 'Ya existe un médico con este correo.',
            'id_usuario.unique' => 'Esa cuenta ya está vinculada a otro médico.',
            'especialidades.required' => 'Marque al menos una especialidad.',
            'especialidades.min' => 'Marque al menos una especialidad.',
        ];
    }

    private function datosFormulario(?Medico $medico): array
    {
        $rolMedico = Rol::where('nombre', 'MEDICO')->value('id_rol');

        // Cuentas que ya tienen otro médico no se ofrecen de nuevo
        $ocupadas = Medico::whereNotNull('id_usuario')
            ->when($medico, fn ($q) => $q->where('id_medico', '!=', $medico->id_medico))
            ->pluck('id_usuario');

        // Se muestran las activas y también las inactivas que el médico ya tenga marcadas
        $marcadas = $medico ? $medico->especialidades->pluck('id_especialidad') : collect();

        return [
            'medico' => $medico,
            'especialidades' => Especialidad::where('estado', true)
                ->orWhereIn('id_especialidad', $marcadas)
                ->orderBy('nombre')
                ->get(),
            'cuentas' => Usuario::where('id_rol', $rolMedico)
                ->whereNotIn('id_usuario', $ocupadas)
                ->orderBy('nombre_usuario')
                ->get(),
        ];
    }
}