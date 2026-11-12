<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// CU-04 Gestionar pacientes
class PacienteController extends Controller
{
    public function index(Request $request)
    {
        return $this->vista($request);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas(), $this->mensajes($request));

        DB::transaction(function () use ($datos) {
            // Toma el siguiente número de la secuencia para construir el código PAC-0001
            $id = (int) DB::selectOne("SELECT nextval(pg_get_serial_sequence('paciente', 'id_paciente')) AS id")->id;

            $paciente = new Paciente($datos);
            $paciente->id_paciente = $id;
            $paciente->codigo = 'PAC-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
            $paciente->estado = true;
            $paciente->save();
        });

        return redirect()->route('recepcionista.pacientes.index')
            ->with('exito', 'Paciente registrado correctamente.');
    }

    public function edit($id)
    {
        return $this->vista(request(), Paciente::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);
        $datos = $request->validate($this->reglas($paciente->id_paciente), $this->mensajes($request));

        $paciente->update($datos);

        return redirect()->route('recepcionista.pacientes.index')
            ->with('exito', 'Paciente actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $paciente = Paciente::findOrFail($id);

        // RN11: el paciente se desactiva, nunca se elimina
        $nuevoEstado = ! $paciente->estado;
        $paciente->update(['estado' => $nuevoEstado]);

        // Si tiene cuenta de usuario vinculada, se desactiva también
        if ($paciente->id_usuario) {
            Usuario::where('id_usuario', $paciente->id_usuario)->update(['estado' => $nuevoEstado]);
        }

        $accion = $nuevoEstado ? 'activado' : 'desactivado';
        return back()->with('exito', "Paciente {$accion} correctamente.");
    }

    private function vista(Request $request, ?Paciente $pacienteEditando = null)
    {
        $buscar = trim((string) $request->input('buscar', ''));

        $pacientes = Paciente::query()
            ->when($buscar !== '', function ($q) use ($buscar) {
                $like = "%{$buscar}%";
                $q->where(function ($w) use ($like) {
                    $w->where('ci', 'ilike', $like)
                      ->orWhere('nombres', 'ilike', $like)
                      ->orWhere('apellidos', 'ilike', $like);
                });
            })
            ->orderByDesc('id_paciente')
            ->get();

        return view('recepcionista.pacientes.index', compact('pacientes', 'pacienteEditando', 'buscar'));
    }

    private function reglas(?int $id = null): array
    {
        return [
            'ci' => ['required', 'string', 'max:15', Rule::unique('paciente', 'ci')->ignore($id, 'id_paciente')],
            'nombres' => ['required', 'string', 'max:60'],
            'apellidos' => ['required', 'string', 'max:60'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'sexo' => ['required', 'in:M,F,O'],
            'telefono' => ['required', 'string', 'max:15'],
            'correo' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:150'],
        ];
    }

    private function mensajes(Request $request): array
    {
        return [
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'Ya existe un paciente registrado con el CI ' . $request->input('ci') . '.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'sexo.required' => 'El sexo es obligatorio.',
            'sexo.in' => 'El sexo seleccionado no es válido.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'correo.email' => 'El correo no es válido.',
        ];
    }
}