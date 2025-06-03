<?php
require_once __DIR__ . '/../config/Database.php';
class Pregunta
{
    private $conn;
    private $table_name = "Pregunta";
    public $id;
    public $texto_pregunta;
    public $puntaje;
    public $id_evaluacion;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create()
    {
        $query = "
            INSERT INTO {$this->table_name}
                (texto_pregunta, puntaje, id_evaluacion)
            VALUES
                (:texto_pregunta, :puntaje, :id_evaluacion)
        ";
        $stmt = $this->conn->prepare($query);
        $this->texto_pregunta = htmlspecialchars(strip_tags($this->texto_pregunta));
        $this->puntaje        = (float) $this->puntaje;
        $this->id_evaluacion  = (int) $this->id_evaluacion;
        $stmt->bindParam(':texto_pregunta', $this->texto_pregunta);
        $stmt->bindParam(':puntaje',        $this->puntaje);
        $stmt->bindParam(':id_evaluacion',  $this->id_evaluacion, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll()
    {
        $query = "
            SELECT 
                p.id_pregunta       AS id,
                p.texto_pregunta,
                p.puntaje,
                p.id_evaluacion     AS evaluacion_id,
                e.titulo            AS evaluacion_titulo
            FROM {$this->table_name} p
            LEFT JOIN Evaluacion e 
                   ON p.id_evaluacion = e.id_evaluacion
            ORDER BY p.id_evaluacion, p.id_pregunta ASC
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
                p.id_pregunta       AS id,
                p.texto_pregunta,
                p.puntaje,
                p.id_evaluacion     AS evaluacion_id,
                e.titulo            AS evaluacion_titulo
            FROM {$this->table_name} p
            LEFT JOIN Evaluacion e 
                   ON p.id_evaluacion = e.id_evaluacion
            WHERE p.id_pregunta = :id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByEvaluacion($evaluacion_id)
    {
        $evaluacion_id = (int) $evaluacion_id;
        $query = "
            SELECT 
                p.id_pregunta       AS id,
                p.texto_pregunta,
                p.puntaje,
                p.id_evaluacion     AS evaluacion_id,
                e.titulo            AS evaluacion_titulo
            FROM {$this->table_name} p
            LEFT JOIN Evaluacion e 
                   ON p.id_evaluacion = e.id_evaluacion
            WHERE p.id_evaluacion = :evaluacion_id
            ORDER BY p.id_pregunta ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWithAlternativas($evaluacion_id)
    {
        $evaluacion_id = (int) $evaluacion_id;
        $query = "
            SELECT 
                p.id_pregunta         AS pregunta_id,
                p.texto_pregunta,
                p.puntaje,
                a.id_alternativa      AS alternativa_id,
                a.texto_alternativa,
                a.esCorrecta          AS es_correcta
            FROM {$this->table_name} p
            LEFT JOIN Alternativa a 
                   ON p.id_pregunta = a.id_pregunta
            WHERE p.id_evaluacion = :evaluacion_id
            ORDER BY p.id_pregunta ASC, a.id_alternativa ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $preguntas = [];
        foreach ($rows as $r) {
            $pid = (int) $r['pregunta_id'];
            if (!isset($preguntas[$pid])) {
                $preguntas[$pid] = [
                    'id'              => $pid,
                    'texto_pregunta'  => $r['texto_pregunta'],
                    'puntaje'         => $r['puntaje'],
                    'alternativas'    => []
                ];
            }
            if ($r['alternativa_id'] !== null) {
                $preguntas[$pid]['alternativas'][] = [
                    'id'                => (int) $r['alternativa_id'],
                    'texto_alternativa' => $r['texto_alternativa'],
                    'es_correcta'       => (int) $r['es_correcta']
                ];
            }
        }
        return array_values($preguntas);
    }
}
