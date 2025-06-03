<?php
function createAlternativa(mysqli $dbConnection, string $texto_alternativa, bool $esCorrecta, int $id_pregunta): int {
    $sql = "INSERT INTO Alternativa (texto_alternativa, esCorrecta, id_pregunta) VALUES (?, ?, ?)";
    
    $stmt = $dbConnection->prepare($sql);
    if ($stmt === false) {
        // Error en la preparación
        // error_log($dbConnection->error);
        return 0;
    }
    
    // Convertir booleano a entero (0 o 1) para la base de datos
    $esCorrectaInt = $esCorrecta ? 1 : 0;
    
    // 'sii' significa: string, integer, integer
    $stmt->bind_param("sii", $texto_alternativa, $esCorrectaInt, $id_pregunta);
    
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

function getAllAlternativas(mysqli $dbConnection): array {
    $alternativas = [];
    $sql = "SELECT id_alternativa, texto_alternativa, esCorrecta, id_pregunta FROM Alternativa";
    
    $result = $dbConnection->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convertir tipos para consistencia
            $row['id_alternativa'] = (int)$row['id_alternativa'];
            // esCorrecta en la BD es BOOLEAN, que MySQL trata como TINYINT(1). 
            // fetch_assoc lo devolverá como string '0' o '1', o int 0 o 1.
            // Lo convertimos a un booleano PHP.
            $row['esCorrecta'] = (bool)(int)$row['esCorrecta']; 
            $row['id_pregunta'] = (int)$row['id_pregunta'];
            $alternativas[] = $row;
        }
        $result->free();
    } else {
        // Error en la consulta
        // error_log($dbConnection->error);
    }
    
    return $alternativas;
}
?>
