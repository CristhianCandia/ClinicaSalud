<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Datos de prueba para acceso rápido. Contraseña para todos: clinica123
class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $hash = Hash::make('clinica123');
        $ahora = now();
        $rol = fn (string $nombre) => DB::table('rol')->where('nombre', $nombre)->value('id_rol');

        $crearUsuario = function (string $rolNombre, string $usuario, string $correo) use ($hash, $rol) {
            return DB::table('usuario')->insertGetId([
                'id_rol' => $rol($rolNombre),
                'nombre_usuario' => $usuario,
                'correo' => $correo,
                'contrasena_hash' => $hash,
                'estado' => true,
            ], 'id_usuario');
        };

        // Administrador y recepcionista
        $idAdmin = $crearUsuario('ADMINISTRADOR', 'cristhian', 'cristhian@gmail.com');
        DB::table('administrador')->insert([
            'id_usuario' => $idAdmin, 'nombres' => 'Cristhian', 'apellidos' => 'Administrador',
            'telefono' => '70000001', 'fecha_contratacion' => now()->toDateString(), 'estado' => true,
        ]);

        $idRecep = $crearUsuario('RECEPCIONISTA', 'anabel', 'anabel@gmail.com');
        DB::table('recepcionista')->insert([
            'id_usuario' => $idRecep, 'nombres' => 'Anabel', 'apellidos' => 'Recepcionista',
            'telefono' => '71111111', 'fecha_contratacion' => now()->toDateString(), 'estado' => true,
        ]);

        // Médico con su especialidad, horarios del ejemplo del documento y un feriado
        $idMedicoUsuario = $crearUsuario('MEDICO', 'mario', 'mario@gmail.com');
        $idMedico = DB::table('medico')->insertGetId([
            'id_usuario' => $idMedicoUsuario, 'codigo' => 'MED-0001', 'registro_profesional' => 'REG-98765',
            'nombres' => 'Mario', 'apellidos' => 'Pérez', 'telefono' => '72222222',
            'correo' => 'mario@gmail.com', 'estado' => true,
        ], 'id_medico');

        $idMedGeneral = DB::table('especialidad')->where('nombre', 'Medicina General')->value('id_especialidad');
        DB::table('medico_especialidad')->insert(['id_medico' => $idMedico, 'id_especialidad' => $idMedGeneral]);

        DB::table('horario_atencion')->insert([
            ['id_medico' => $idMedico, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'duracion_cita_min' => 30, 'estado' => true],
            ['id_medico' => $idMedico, 'dia_semana' => 3, 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'duracion_cita_min' => 30, 'estado' => true],
        ]);

        DB::table('bloqueo_agenda')->insert([
            'id_medico' => null, 'tipo' => 'FERIADO', 'fecha_inicio' => '2026-12-25', 'fecha_fin' => '2026-12-25',
            'motivo' => 'Navidad', 'estado' => true, 'id_usuario_registro' => $idAdmin,
        ]);

        // Pacientes: Alex tiene cuenta de usuario; los demás no
        $idPacienteUsuario = $crearUsuario('PACIENTE', 'alex', 'alex@gmail.com');
        DB::table('paciente')->insert([
            'codigo' => 'PAC-0001', 'ci' => '12345678', 'nombres' => 'Alex', 'apellidos' => 'Gómez',
            'fecha_nacimiento' => '1995-05-15', 'sexo' => 'M', 'telefono' => '73333333',
            'correo' => 'alex@gmail.com', 'tipo_sangre' => 'O+', 'id_usuario' => $idPacienteUsuario, 'estado' => true,
        ]);

        DB::table('paciente')->insert([
            ['codigo' => 'PAC-0002', 'ci' => '87654321', 'nombres' => 'Laura', 'apellidos' => 'Méndez', 'fecha_nacimiento' => '1988-03-12', 'sexo' => 'F', 'telefono' => '74444444', 'correo' => 'laura.mendez@email.com', 'direccion' => 'Av. Principal #123', 'tipo_sangre' => 'A+', 'estado' => true],
            ['codigo' => 'PAC-0003', 'ci' => '11223344', 'nombres' => 'Carlos', 'apellidos' => 'Ruiz', 'fecha_nacimiento' => '1975-11-25', 'sexo' => 'M', 'telefono' => '75555555', 'correo' => 'carlos.ruiz@email.com', 'direccion' => 'Calle 5 #45', 'tipo_sangre' => 'B-', 'estado' => true],
            ['codigo' => 'PAC-0004', 'ci' => '99887766', 'nombres' => 'Sofía', 'apellidos' => 'Torres', 'fecha_nacimiento' => '2000-09-15', 'sexo' => 'F', 'telefono' => '76666666', 'correo' => 'sofia.torres@email.com', 'direccion' => 'Zona Sur, Bloque 2', 'tipo_sangre' => 'AB+', 'estado' => true],
        ]);
    }
}
