-- Migración para mover campos obligatorios de categoría a evento
-- Y agregar nota/asistencia mínima

-- 1. Agregar campos a la tabla eventos
ALTER TABLE eventos 
ADD COLUMN requiere_nota BOOLEAN DEFAULT FALSE COMMENT 'indica si el evento requiere calificación obligatoria',
ADD COLUMN requiere_asistencia BOOLEAN DEFAULT FALSE COMMENT 'indica si el evento requiere control de asistencia obligatorio',
ADD COLUMN nota_minima DECIMAL(4,2) DEFAULT NULL COMMENT 'nota mínima requerida cuando es obligatoria (0-10)',
ADD COLUMN asistencia_minima DECIMAL(5,2) DEFAULT NULL COMMENT 'asistencia mínima requerida cuando es obligatoria (0-100)';

-- 2. Migrar datos existentes de categorías a eventos
UPDATE eventos e 
JOIN categorias_evento c ON e.categoria_id = c.id 
SET 
    e.requiere_nota = c.requiere_nota,
    e.requiere_asistencia = c.requiere_asistencia,
    e.nota_minima = CASE WHEN c.requiere_nota = 1 THEN 7.0 ELSE NULL END,
    e.asistencia_minima = CASE WHEN c.requiere_asistencia = 1 THEN 70.0 ELSE NULL END;

-- 3. Opcional: Eliminar campos de categorías (comentado por seguridad)
-- ALTER TABLE categorias_evento 
-- DROP COLUMN requiere_nota,
-- DROP COLUMN requiere_asistencia;
