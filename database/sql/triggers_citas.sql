-- Funciones y triggers de citas (extraído del script v3, sección 9)
-- =====================================================================
-- 9. FUNCIONES Y TRIGGERS DE NEGOCIO
-- =====================================================================

-- 9.1 Validación de agenda al registrar o reprogramar una cita
--     RF05, RF06, RF08, RN04, RN05, RN06, RN08, RN13
CREATE OR REPLACE FUNCTION fn_validar_cita() RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_hoy        DATE := (now() AT TIME ZONE 'America/La_Paz')::DATE;
    v_dia        SMALLINT;
    v_horario    horario_atencion%ROWTYPE;
    v_activo     BOOLEAN;
    v_reprograma BOOLEAN;
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF NEW.estado <> 'PROGRAMADA' THEN
            RAISE EXCEPTION 'Una cita nueva debe registrarse como PROGRAMADA'
                USING ERRCODE = 'check_violation';
        END IF;
        v_reprograma := TRUE;
    ELSE
        v_reprograma := NEW.fecha IS DISTINCT FROM OLD.fecha
                     OR NEW.hora_inicio IS DISTINCT FROM OLD.hora_inicio
                     OR NEW.hora_fin IS DISTINCT FROM OLD.hora_fin
                     OR NEW.id_medico IS DISTINCT FROM OLD.id_medico
                     OR NEW.id_especialidad IS DISTINCT FROM OLD.id_especialidad;
    END IF;

    -- Si no cambia la agenda (solo estado, observación o pago), no hay nada que validar aquí
    IF NOT v_reprograma THEN
        RETURN NEW;
    END IF;

    -- RF08: solo citas PROGRAMADAS o CONFIRMADAS pueden reprogramarse
    IF TG_OP = 'UPDATE' AND OLD.estado NOT IN ('PROGRAMADA', 'CONFIRMADA') THEN
        RAISE EXCEPTION 'Una cita en estado % no puede reprogramarse', OLD.estado
            USING ERRCODE = 'check_violation';
    END IF;

    -- RN06: no se registran ni reprograman citas en fechas pasadas (zona horaria de Bolivia)
    IF NEW.fecha < v_hoy THEN
        RAISE EXCEPTION 'No se pueden registrar ni reprogramar citas en fechas pasadas'
            USING ERRCODE = 'check_violation';
    END IF;

    -- RN04: un médico inactivo no recibe citas nuevas
    SELECT m.estado INTO v_activo FROM medico m WHERE m.id_medico = NEW.id_medico;
    IF FOUND AND NOT v_activo THEN
        RAISE EXCEPTION 'El médico no está activo y no puede recibir citas'
            USING ERRCODE = 'check_violation';
    END IF;

    -- Un paciente inactivo no recibe citas nuevas
    SELECT p.estado INTO v_activo FROM paciente p WHERE p.id_paciente = NEW.id_paciente;
    IF FOUND AND NOT v_activo THEN
        RAISE EXCEPTION 'El paciente no está activo'
            USING ERRCODE = 'check_violation';
    END IF;

    -- RN05 / RF05: la cita debe caer dentro de un horario de atención activo del médico
    v_dia := EXTRACT(ISODOW FROM NEW.fecha)::SMALLINT;
    SELECT h.* INTO v_horario
    FROM horario_atencion h
    WHERE h.id_medico = NEW.id_medico
      AND h.dia_semana = v_dia
      AND h.estado
      AND NEW.hora_inicio >= h.hora_inicio
      AND NEW.hora_fin <= h.hora_fin;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'La cita está fuera del horario de atención del médico'
            USING ERRCODE = 'check_violation';
    END IF;

    -- RN08 / RF05: duración y alineación con los intervalos configurados
    IF EXTRACT(EPOCH FROM (NEW.hora_fin - NEW.hora_inicio)) / 60 <> v_horario.duracion_cita_min THEN
        RAISE EXCEPTION 'La duración de la cita debe ser de % minutos', v_horario.duracion_cita_min
            USING ERRCODE = 'check_violation';
    END IF;
    IF MOD((EXTRACT(EPOCH FROM (NEW.hora_inicio - v_horario.hora_inicio)) / 60)::INTEGER,
           v_horario.duracion_cita_min::INTEGER) <> 0 THEN
        RAISE EXCEPTION 'La cita debe iniciar en intervalos de % minutos desde la hora de atención',
            v_horario.duracion_cita_min
            USING ERRCODE = 'check_violation';
    END IF;

    -- RF05: no se puede agendar en un bloqueo activo (permiso, licencia, vacaciones o feriado)
    IF EXISTS (
        SELECT 1 FROM bloqueo_agenda b
        WHERE b.estado
          AND NEW.fecha BETWEEN b.fecha_inicio AND b.fecha_fin
          AND (b.id_medico = NEW.id_medico OR b.id_medico IS NULL)
    ) THEN
        RAISE EXCEPTION 'La fecha está bloqueada en la agenda del médico'
            USING ERRCODE = 'check_violation';
    END IF;

    RETURN NEW;
END
$$;

CREATE TRIGGER trg_validar_cita
BEFORE INSERT OR UPDATE ON cita
FOR EACH ROW EXECUTE FUNCTION fn_validar_cita();

-- 9.2 Transiciones de estado válidas (RF10)
--     PROGRAMADA -> CONFIRMADA | ATENDIDA | CANCELADA | INASISTENCIA
--     CONFIRMADA -> ATENDIDA | CANCELADA | INASISTENCIA
--     ATENDIDA, CANCELADA e INASISTENCIA son estados finales
CREATE OR REPLACE FUNCTION fn_validar_transicion_cita() RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.estado IS DISTINCT FROM OLD.estado THEN
        IF NOT (
               (OLD.estado = 'PROGRAMADA' AND NEW.estado IN ('CONFIRMADA','ATENDIDA','CANCELADA','INASISTENCIA'))
            OR (OLD.estado = 'CONFIRMADA' AND NEW.estado IN ('ATENDIDA','CANCELADA','INASISTENCIA'))
        ) THEN
            RAISE EXCEPTION 'Transición de estado no permitida: % -> %', OLD.estado, NEW.estado
                USING ERRCODE = 'check_violation';
        END IF;
    END IF;
    RETURN NEW;
END
$$;

CREATE TRIGGER trg_transicion_cita
BEFORE UPDATE OF estado ON cita
FOR EACH ROW EXECUTE FUNCTION fn_validar_transicion_cita();

-- 9.3 Prohibir la eliminación física de citas (RF09, RN10)
CREATE OR REPLACE FUNCTION fn_bloquear_borrado_cita() RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    RAISE EXCEPTION 'Las citas no se eliminan físicamente; use la cancelación'
        USING ERRCODE = 'check_violation';
END
$$;

CREATE TRIGGER trg_no_borrar_cita
BEFORE DELETE ON cita
FOR EACH ROW EXECUTE FUNCTION fn_bloquear_borrado_cita();

-- 9.4 Historial automático de cada cita (RF08, RF12: trazabilidad)
CREATE OR REPLACE FUNCTION fn_historial_cita() RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_actor INTEGER := COALESCE(NEW.id_usuario_actualiza, NEW.id_usuario_registro);
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO cita_historial (id_cita, accion, estado_nuevo, fecha_nueva, hora_nueva, id_usuario)
        VALUES (NEW.id_cita, 'REGISTRO', NEW.estado, NEW.fecha, NEW.hora_inicio, NEW.id_usuario_registro);
        RETURN NEW;
    END IF;

    IF NEW.fecha IS DISTINCT FROM OLD.fecha
       OR NEW.hora_inicio IS DISTINCT FROM OLD.hora_inicio
       OR NEW.id_medico IS DISTINCT FROM OLD.id_medico THEN
        INSERT INTO cita_historial (id_cita, accion, estado_anterior, estado_nuevo,
                                    fecha_anterior, hora_anterior, fecha_nueva, hora_nueva, id_usuario)
        VALUES (NEW.id_cita, 'REPROGRAMACION', OLD.estado, NEW.estado,
                OLD.fecha, OLD.hora_inicio, NEW.fecha, NEW.hora_inicio, v_actor);
    END IF;

    IF NEW.estado IS DISTINCT FROM OLD.estado THEN
        INSERT INTO cita_historial (id_cita, accion, estado_anterior, estado_nuevo, observacion, id_usuario)
        VALUES (NEW.id_cita,
                CASE NEW.estado WHEN 'CONFIRMADA' THEN 'CONFIRMACION'
                                WHEN 'CANCELADA'  THEN 'CANCELACION'
                                ELSE NEW.estado END,
                OLD.estado, NEW.estado,
                LEFT(NEW.motivo_cancelacion, 200), v_actor);
    END IF;

    IF NEW.observacion_consulta IS DISTINCT FROM OLD.observacion_consulta THEN
        INSERT INTO cita_historial (id_cita, accion, estado_nuevo, observacion, id_usuario)
        VALUES (NEW.id_cita, 'OBSERVACION', NEW.estado, LEFT(NEW.observacion_consulta, 200), v_actor);
    END IF;

    RETURN NEW;
END
$$;

CREATE TRIGGER trg_historial_cita
AFTER INSERT OR UPDATE ON cita
FOR EACH ROW EXECUTE FUNCTION fn_historial_cita();

-- 9.5 Bitácora de cambios en citas (auditoría)
CREATE OR REPLACE FUNCTION fn_auditar_cambios() RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    v_ip VARCHAR(45) := COALESCE(inet_client_addr()::VARCHAR, '127.0.0.1');
    v_usuario INTEGER := COALESCE(NEW.id_usuario_actualiza, NEW.id_usuario_registro, 1);
BEGIN
    INSERT INTO bitacora (id_usuario, accion, tabla_afectada, detalle, ip)
    VALUES (v_usuario,
            CASE WHEN TG_OP = 'INSERT' THEN 'CREAR_' ELSE 'ACTUALIZAR_' END || UPPER(TG_TABLE_NAME),
            TG_TABLE_NAME,
            CASE WHEN TG_OP = 'INSERT' THEN 'Registro creado ID=' ELSE 'Registro actualizado ID=' END || NEW.id_cita,
            v_ip);
    RETURN NEW;
END
$$;

CREATE TRIGGER trg_auditoria_cita
AFTER INSERT OR UPDATE ON cita
FOR EACH ROW EXECUTE FUNCTION fn_auditar_cambios();

