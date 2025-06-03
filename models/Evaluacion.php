<?php
require_once __DIR__ . '/../config/Database.php';
class Evaluacion
{
    private $conn;
    private $table_name = "Evaluacion";
    public $id;
    public $titulo;
    public $id_area;
    public $fechaCrea;
    public $fechaInicio;
    public $fechaFin;
    public $tiempoLimite;
    public $puntajeMaximo;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create()
    {
        $query = "
            INSERT INTO {$this->table_name}
                (titulo, id_area, fechaInicio, fechaFin, tiempoLimite, puntajeMaximo)
            VALUES
                (:titulo, :id_area, :fechaInicio, :fechaFin, :tiempoLimite, :puntajeMaximo)
        ";
        $stmt = $this->conn->prepare($query);
        $this->titulo        = htmlspecialchars(strip_tags($this->titulo));
        $this->id_area       = (int) $this->id_area;
        $this->fechaInicio   = htmlspecialchars(strip_tags($this->fechaInicio));
        $this->fechaFin      = htmlspecialchars(strip_tags($this->fechaFin));
        $this->tiempoLimite  = (int) $this->tiempoLimite;
        $this->puntajeMaximo = (float) $this->puntajeMaximo;
        $stmt->bindParam(':titulo',        $this->titulo);
        $stmt->bindParam(':id_area',       $this->id_area,       PDO::PARAM_INT);
        $stmt->bindParam(':fechaInicio',   $this->fechaInicio);
        $stmt->bindParam(':fechaFin',      $this->fechaFin);
        $stmt->bindParam(':tiempoLimite',  $this->tiempoLimite,  PDO::PARAM_INT);
        $stmt->bindParam(':puntajeMaximo', $this->puntajeMaximo);
        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll()
    {
        $query = "
            SELECT 
                e.id_evaluacion   AS id,
                e.titulo,
                e.id_area         AS area_id,
                a.nomArea         AS area_nombre,
                e.fechaCrea       AS fecha_creacion,
                e.fechaInicio     AS fecha_inicio,
                e.fechaFin        AS fecha_fin,
                e.tiempoLimite    AS duracion,
                e.puntajeMaximo   AS puntaje
            FROM {$this->table_name} e
            LEFT JOIN Areas a 
                   ON e.id_area = a.id_area
            ORDER BY e.fechaCrea DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $id = (int) $id;
        $query = "
            SELECT 
                e.id_evaluacion   AS id,
                e.titulo,
                e.id_area         AS area_id,
                a.nomArea         AS area_nombre,
                e.fechaCrea       AS fecha_creacion,
                e.fechaInicio     AS fecha_inicio,
                e.fechaFin        AS fecha_fin,
                e.tiempoLimite    AS duracion,
                e.puntajeMaximo   AS puntaje
            FROM {$this->table_name} e
            LEFT JOIN Areas a 
                   ON e.id_area = a.id_area
            WHERE e.id_evaluacion = :id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByArea($area_id)
    {
        $area_id = (int) $area_id;
        $query = "
            SELECT 
                e.id_evaluacion   AS id,
                e.titulo,
                e.id_area         AS area_id,
                a.nomArea         AS area_nombre,
                e.fechaCrea       AS fecha_creacion,
                e.fechaInicio     AS fecha_inicio,
                e.fechaFin        AS fecha_fin,
                e.tiempoLimite    AS duracion,
                e.puntajeMaximo   AS puntaje
            FROM {$this->table_name} e
            LEFT JOIN Areas a 
                   ON e.id_area = a.id_area
            WHERE e.id_area = :area_id
            ORDER BY e.fechaCrea DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
