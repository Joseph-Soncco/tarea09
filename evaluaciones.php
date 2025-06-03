<?php
function createEvaluacion(mysqli $dbConnection, string $titulo, int $id_area, string $fechaInicio, string $fechaFin, int $tiempoLimite, float $puntajeMaximo): int {
    $sql = "INSERT INTO Evaluacion (titulo, id_area, fechaInicio, fechaFin, tiempoLimite, puntajeMaximo) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $dbConnection->prepare($sql);
    if ($stmt === false) {
        // Error en la preparación de la consulta
        // Considera registrar el error: error_log($dbConnection->error);
        return 0;
    }
    
    // 'sissid' significa: string, integer, string, string, integer, double
    $stmt->bind_param("sissid", $titulo, $id_area, $fechaInicio, $fechaFin, $tiempoLimite, $puntajeMaximo);
    
    if ($stmt->execute()) {
        $lastId = $dbConnection->insert_id;
        $stmt->close();
        return (int)$lastId;
    } else {
        // Error en la ejecución
        // Considera registrar el error: error_log($stmt->error);
        $stmt->close();
        return 0;
    }
}

function getAllEvaluaciones(mysqli $dbConnection): array {
    $evaluaciones = [];
    $sql = "SELECT id_evaluacion, titulo, id_area, fechaCrea, fechaInicio, fechaFin, tiempoLimite, puntajeMaximo FROM Evaluacion";
    
    $result = $dbConnection->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convertir tipos si es necesario para consistencia
            $row['id_evaluacion'] = (int)$row['id_evaluacion'];
            $row['id_area'] = (int)$row['id_area'];
            $row['tiempoLimite'] = (int)$row['tiempoLimite'];
            $row['puntajeMaximo'] = (float)$row['puntajeMaximo'];
            $evaluaciones[] = $row;
        }
        $result->free();
    } else {
        // Error en la consulta
        // Considera registrar el error: error_log($dbConnection->error);
    }
    
    return $evaluaciones;
}

?>
