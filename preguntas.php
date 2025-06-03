<?php
function createPregunta(mysqli $dbConnection, string $texto_pregunta, float $puntaje, int $id_evaluacion): int {
    $sql = "INSERT INTO Pregunta (texto_pregunta, puntaje, id_evaluacion) VALUES (?, ?, ?)";
    
    $stmt = $dbConnection->prepare($sql);
    if ($stmt === false) {
        // Error en la preparación
        // error_log($dbConnection->error);
        return 0;
    }
    
    // 'sdi' significa: string, double, integer
    $stmt->bind_param("sdi", $texto_pregunta, $puntaje, $id_evaluacion);
    
    if ($stmt->execute()) {
        $lastId = $dbConnection->insert_id;
        $stmt->close();
        return (int)$lastId;
    } else {
        // Error en la ejecución
        // error_log($stmt->error);
        $stmt->close();
        return 0;
    }
}

function getAllPreguntas(mysqli $dbConnection): array {
    $preguntas = [];
    $sql = "SELECT id_pregunta, texto_pregunta, puntaje, id_evaluacion FROM Pregunta";
    
    $result = $dbConnection->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convertir tipos para consistencia
            $row['id_pregunta'] = (int)$row['id_pregunta'];
            $row['puntaje'] = (float)$row['puntaje'];
            $row['id_evaluacion'] = (int)$row['id_evaluacion'];
            $preguntas[] = $row;
        }
        $result->free();
    } else {
        // Error en la consulta
        // error_log($dbConnection->error);
    }
    
    return $preguntas;
}

?>
