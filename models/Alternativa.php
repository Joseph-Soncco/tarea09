<?php
require_once __DIR__ . '/../config/Database.php';
class Alternativa
{
    private $conn;
    private $table_name = "Alternativa";
    public $id;
    public $pregunta_id;
    public $texto_alternativa;
    public $es_correcta;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create()
    {
        $query = "
            INSERT INTO {$this->table_name} 
                (texto_alternativa, esCorrecta, id_pregunta)
            VALUES 
                (:texto_alternativa, :esCorrecta, :id_pregunta)
        ";
        $stmt = $this->conn->prepare($query);
        $this->texto_alternativa = htmlspecialchars(strip_tags($this->texto_alternativa));
        $this->es_correcta = (int) $this->es_correcta;
        $this->pregunta_id = (int) $this->pregunta_id;
        $stmt->bindParam(':texto_alternativa', $this->texto_alternativa);
        $stmt->bindParam(':esCorrecta',        $this->es_correcta);
        $stmt->bindParam(':id_pregunta',       $this->pregunta_id);
        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll()
    {
        $query = "
            SELECT 
                a.id_alternativa AS id,
                a.id_pregunta       AS pregunta_id,
                p.texto_pregunta,
                a.texto_alternativa,
                a.esCorrecta        AS es_correcta
            FROM {$this->table_name} a
            LEFT JOIN Pregunta p 
                   ON a.id_pregunta = p.id_pregunta
            ORDER BY a.id_pregunta, a.id_alternativa ASC
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
                a.id_alternativa AS id,
                a.id_pregunta       AS pregunta_id,
                p.texto_pregunta,
                a.texto_alternativa,
                a.esCorrecta        AS es_correcta
            FROM {$this->table_name} a
            LEFT JOIN Pregunta p 
                   ON a.id_pregunta = p.id_pregunta
            WHERE a.id_alternativa = :id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByPregunta($pregunta_id)
    {
        $pregunta_id = (int) $pregunta_id;
        $query = "
            SELECT 
                a.id_alternativa AS id,
                a.id_pregunta       AS pregunta_id,
                p.texto_pregunta,
                a.texto_alternativa,
                a.esCorrecta        AS es_correcta
            FROM {$this->table_name} a
            LEFT JOIN Pregunta p 
                   ON a.id_pregunta = p.id_pregunta
            WHERE a.id_pregunta = :pregunta_id
            ORDER BY a.id_alternativa ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pregunta_id', $pregunta_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCorrectasByPregunta($pregunta_id)
    {
        $pregunta_id = (int) $pregunta_id;
        $query = "
            SELECT 
                a.id_alternativa AS id,
                a.id_pregunta       AS pregunta_id,
                p.texto_pregunta,
                a.texto_alternativa,
                a.esCorrecta        AS es_correcta
            FROM {$this->table_name} a
            LEFT JOIN Pregunta p 
                   ON a.id_pregunta = p.id_pregunta
            WHERE a.id_pregunta = :pregunta_id 
              AND a.esCorrecta = 1
            ORDER BY a.id_alternativa ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pregunta_id', $pregunta_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createMultiple(array $alternativas)
    {
        try {
            $this->conn->beginTransaction();
            $query = "
                INSERT INTO {$this->table_name} 
                    (texto_alternativa, esCorrecta, id_pregunta)
                VALUES 
                    (:texto_alternativa, :esCorrecta, :id_pregunta)
            ";
            $stmt = $this->conn->prepare($query);
            $inserted_ids = [];
            foreach ($alternativas as $item) {
                $text   = htmlspecialchars(strip_tags($item['texto_alternativa']));
                $isCorr = (int) $item['es_correcta'];
                $pid    = (int) $item['pregunta_id'];
                $stmt->bindParam(':texto_alternativa', $text);
                $stmt->bindParam(':esCorrecta',        $isCorr);
                $stmt->bindParam(':id_pregunta',       $pid);
                if ($stmt->execute()) {
                    $inserted_ids[] = (int) $this->conn->lastInsertId();
                } else {
                    throw new Exception("No se pudo insertar esta alternativa.");
                }
            }
            $this->conn->commit();
            return $inserted_ids;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function validateCorrectAnswers($pregunta_id)
    {
        $pregunta_id = (int) $pregunta_id;
        $query = "
            SELECT COUNT(*) AS total_correctas
            FROM {$this->table_name}
            WHERE id_pregunta = :pregunta_id 
              AND esCorrecta = 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pregunta_id', $pregunta_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['total_correctas'] > 0);
    }
}
