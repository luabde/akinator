<?php
if (!$logueado) {
    echo "<h4>Inicia la sessió en un compte.</h4>";
    return;
}

if (empty($historial)) {
    echo "<h4>No has encertat cap personatge.</h4>";
    return;
}

foreach ($historial as $fila) {
    echo "<h4>" . htmlspecialchars($fila['nombre']) . "</h4>";
}
?>
