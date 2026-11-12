<?php

// Opciones del menú principal por rol (CU-01, flujo 6)
return [
    'ADMINISTRADOR' => ['Inicio', 'Usuarios', 'Especialidades', 'Médicos', 'Horarios de atención',
                        'Pacientes', 'Disponibilidad', 'Citas', 'Agenda médica', 'Reportes'],
    'RECEPCIONISTA' => ['Inicio', 'Pacientes', 'Disponibilidad', 'Citas', 'Agenda médica'],
    'MEDICO'        => ['Inicio', 'Agenda médica'],
    'PACIENTE'      => ['Inicio', 'Mis citas', 'Mi perfil'],
];