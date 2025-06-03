<?php
// db_connection.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aprendePeru";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Establecer el charset a utf8mb4 para soportar caracteres especiales y emojis
if (!$conn->set_charset("utf8mb4")) {

}

?>
