<?php
class Curso {
    public $id;
    public $codigo;
    public $nombre;
    public $descripcion;
    public $fecha_inicio;
    public $duracion;
    public $docente_id;

    public function __construct($row) {
        $this->id = $row['id'];
        $this->codigo = $row['codigo'];
        $this->nombre = $row['nombre'];
        $this->descripcion = $row['descripcion'];
        $this->fecha_inicio = $row['fecha_inicio'];
        $this->duracion = $row['duracion'];
        $this->docente_id = $row['docente_id'];
    }
}

?>