<?php
    include_once __DIR__ . '/../config/database.php';
    
  
// include_once __DIR__ . '/../config/database.php';

$db = getConnection();

    function getTotalPreguntas($db){
        $query = 'SELECT COUNT(*) FROM personajes';
        $stmt = $db->query($query);           // ejecutar la consulta
        $total = $stmt->fetchColumn();        // obtener el resultado
        return $total;
    }
?>
