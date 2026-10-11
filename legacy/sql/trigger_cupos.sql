-- Versión simplificada del trigger de cupos (Compatible con todas las versiones de MySQL)
-- Eliminar triggers existentes si existen

DROP TRIGGER IF EXISTS verificar_cupos_disponibles;
DROP TRIGGER IF EXISTS verificar_cupos_al_actualizar;
DROP PROCEDURE IF EXISTS ObtenerInfoCupos;
DROP FUNCTION IF EXISTS TieneCuposDisponibles;

-- Trigger simple para verificar cupos disponibles
DELIMITER $$

CREATE TRIGGER verificar_cupos_disponibles
    BEFORE INSERT ON inscripciones
    FOR EACH ROW
BEGIN
    DECLARE cupos_totales INT DEFAULT 0;
    DECLARE inscripciones_actuales INT DEFAULT 0;
    
    -- Obtener cupos totales del evento
    SELECT cupos INTO cupos_totales
    FROM eventos 
    WHERE id = NEW.evento_id;
    
    -- Verificar si el evento existe
    IF cupos_totales IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'El evento especificado no existe';
    END IF;
    
    -- Contar inscripciones actuales para este evento
    SELECT COUNT(*) INTO inscripciones_actuales
    FROM inscripciones 
    WHERE evento_id = NEW.evento_id;
    
    -- Verificar si hay cupos disponibles
    IF inscripciones_actuales >= cupos_totales THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No hay cupos disponibles para este curso';
    END IF;
    
END$$

DELIMITER ;

-- Trigger para actualizar inscripciones
DELIMITER $$

CREATE TRIGGER verificar_cupos_al_actualizar
    BEFORE UPDATE ON inscripciones
    FOR EACH ROW
BEGIN
    DECLARE cupos_totales INT DEFAULT 0;
    DECLARE inscripciones_actuales INT DEFAULT 0;
    
    -- Solo verificar si se está cambiando el evento_id o activando una inscripción
    IF (OLD.evento_id != NEW.evento_id) OR 
       (IFNULL(OLD.estado, 'activo') != 'activo' AND NEW.estado = 'activo') THEN
        
        -- Obtener cupos totales del evento
        SELECT cupos INTO cupos_totales
        FROM eventos 
        WHERE id = NEW.evento_id;
        
        -- Verificar si el evento existe
        IF cupos_totales IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'El evento especificado no existe';
        END IF;
        
        -- Contar inscripciones actuales activas (excluyendo la actual)
        SELECT COUNT(*) INTO inscripciones_actuales
        FROM inscripciones 
        WHERE evento_id = NEW.evento_id 
        AND id != NEW.id 
        AND (estado = 'activo' OR estado IS NULL);
        
        -- Verificar si hay cupos disponibles
        IF inscripciones_actuales >= cupos_totales THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'No hay cupos disponibles para este curso';
        END IF;
        
    END IF;
    
END$$

DELIMITER ;

-- Procedimiento almacenado simple
DELIMITER $$

CREATE PROCEDURE ObtenerInfoCupos(IN curso_id INT)
BEGIN
    SELECT 
        e.id,
        e.nombre_evento,
        e.cupos as cupos_totales,
        IFNULL(inscripciones_count.total, 0) as inscripciones_actuales,
        (e.cupos - IFNULL(inscripciones_count.total, 0)) as cupos_disponibles,
        CASE 
            WHEN (e.cupos - IFNULL(inscripciones_count.total, 0)) <= 0 THEN 'LLENO'
            WHEN (e.cupos - IFNULL(inscripciones_count.total, 0)) <= 5 THEN 'POCOS_CUPOS'
            ELSE 'DISPONIBLE'
        END as estado_cupos
    FROM eventos e
    LEFT JOIN (
        SELECT evento_id, COUNT(*) as total
        FROM inscripciones
        WHERE evento_id = curso_id AND (estado = 'activo' OR estado IS NULL)
        GROUP BY evento_id
    ) inscripciones_count ON e.id = inscripciones_count.evento_id
    WHERE e.id = curso_id;
END$$

DELIMITER ;

-- Función simplificada
DELIMITER $$

CREATE FUNCTION TieneCuposDisponibles(curso_id INT) 
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE cupos_totales INT DEFAULT 0;
    DECLARE inscripciones_actuales INT DEFAULT 0;
    DECLARE resultado BOOLEAN DEFAULT FALSE;
    
    -- Obtener cupos totales del evento
    SELECT cupos INTO cupos_totales
    FROM eventos 
    WHERE id = curso_id;
    
    -- Si el evento no existe, retornar FALSE
    IF cupos_totales IS NULL THEN
        SET resultado = FALSE;
    ELSE
        -- Contar inscripciones actuales activas
        SELECT COUNT(*) INTO inscripciones_actuales
        FROM inscripciones 
        WHERE evento_id = curso_id AND (estado = 'activo' OR estado IS NULL);
        
        -- Determinar si hay cupos disponibles
        IF cupos_totales > inscripciones_actuales THEN
            SET resultado = TRUE;
        ELSE
            SET resultado = FALSE;
        END IF;
    END IF;
    
    RETURN resultado;
END$$

DELIMITER ;
