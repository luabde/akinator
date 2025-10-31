<?php
    include_once __DIR__ . '/controllers/session.php';
    include_once __DIR__ . '/controllers/gameController.php';
    include_once __DIR__ . '/controllers/databaseController.php';

    // Se inicializa el juego con la función de session
    inicializar_juego();

    $total = getTotalPreguntas($db);
    echo "total de las preguntas: $total";
?>