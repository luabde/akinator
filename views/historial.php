<?php
    if (!empty($historial)) {
        foreach ($historial as $fila) {
            echo "<h4>".htmlspecialchars($fila['nombre'])."</h4>";
        }
    } else {
        echo "<h4>No tens cap historial.</h4>";
    }
?>