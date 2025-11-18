<?php
require_once '../config/database.php';
$db = conectarDB();

session_start();

// Condicional para volver a empezar una nueva partida.
// Se reseta el session para que salga de nuevo empezar
if (isset($_GET['seccio']) && $_GET['seccio'] === 'nueva') {
    // RESETEAR TODAS LAS VARIABLES DEL JUEGO
    unset($_SESSION['vista']);
    unset($_SESSION['preguntas_disponibles']);
    unset($_SESSION['pregunta_actual']);
    unset($_SESSION['preguntas_info']);
    unset($_SESSION['preguntas_respondidas']);
    unset($_SESSION['personajes_posibles']);
    unset($_SESSION['personaje_adivinado']);
    unset($_SESSION['personajes_posibles_lista']);

    $_SESSION['vista'] = 'inicio';
    
    header("Location: index.php");
    exit;
}

// Inicialización segura (solo si no existen)
$_SESSION['historial'] = $_SESSION['historial'] ?? [];
$_SESSION['vista'] = $_SESSION['vista'] ?? 'inicio';

// -------------------
// FUNCIONES
// -------------------
// function mostraHistorial() {
//     if (!empty($_SESSION['historial'])) {
//         echo "<ul class='llista-sidebar'>";
//         foreach (array_reverse($_SESSION['historial']) as $h) echo "<li>$h</li>";
//         echo "</ul>";
//     } else {
//         echo "<h2 class='historial-buit'>No hi ha encerts</h2>";
//     }
// }
$seccio = $_GET['seccio'] ?? '';
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>Akinator DC</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&family=Bangers&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/footer.css">
</head>
<body>
<div class="sidebar">
    <h2>Menu</h2>
    <div class="botones">
        <a href="?seccio=nueva">
            <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#212529"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240v80H200v560h560v-240h80v240q0 33-23.5 56.5T760-120H200Zm440-400v-120H520v-80h120v-120h80v120h120v80H720v120h-80Z"/></svg>
            <span>Nova partida</span>
        </a>
        <a href="?seccio=historial">
            <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#212529"><path d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/></svg>
            <span>Historial</span>
        </a>
        <a href="?seccio=biblioteca">
            <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px" fill="#212529"><path d="M400-400h160v-80H400v80Zm0-120h320v-80H400v80Zm0-120h320v-80H400v80Zm-80 400q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320Zm0-80h480v-480H320v480ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm160-720v480-480Z"/></svg>
            <span>Biblioteca</span>
        </a>
    </div>

    <?php if ($seccio==='historial'): ?>
        <div class="sidebar-content"><h2>Historial</h2>
        <?php
            require '../controllers/userController.php';
            $controller = new userController();
            $controller->mostrarHistorial();
        ?>   
        <!-- <?php mostraHistorial(); ?>
            <form method="post">
                <?php if(!empty($_SESSION['historial'])): ?>
                    <button name="reiniciar" class="btn-no">Esborrar historial</button>
                <?php endif; ?>
            </form> -->
        </div>
    <?php endif; ?>

    <?php if ($seccio==='biblioteca'): ?>
        <div class="sidebar-content"><h2>Biblioteca</h2>
        <?php
            require '../controllers/personajesController.php';
            $controller = new PersonajeController();
            $controller->obtenerPersonajes();
        ?>
        </div>
    <?php endif; ?>

    <div class="sidebar-handle"></div>
</div>

<div class="right-container">
    <?php require '../views/header.php'?>

        <div class="main">
            <div class="puntos">
            <?php
                $vista = $_SESSION['vista'] ?? 'inicio';
            ?>

                <?php if ($vista === 'inicio'): ?> 
                    
                    <h1>Pensa en un personatge!</h1>
                    <p>I jo intentaré endevinar-lo amb preguntes de sí/no!</p>
                    <form method="POST" action="../controllers/gameController.php">
                    <button name="inicio" class="btn-primary-si">Començar partida</button>
                </form>
                <?php elseif ($vista === 'pregunta'): ?> 
                    <?php
                        $pregunta = $_SESSION['pregunta_actual'];
                        $preguntas_respondidas = $_SESSION['preguntas_respondidas'];
                        $num_personajes = $_SESSION['personajes_posibles'];

                        include '../views/pregunta.php';
                    ?>
                <?php endif?>

                <?php
                    if ($vista === 'adivinar') {
                        // Mostrar personaje adivinado
                        $personaje_adivinado = $_SESSION['personaje_adivinado'];
                        include '../views/adivinar.php';
                        
                    } elseif ($vista === 'lista') {
                        // Mostrar lista de personajes
                        $personajes_posibles = $_SESSION['personajes_posibles_lista'];
                        $num_personajes = count($personajes_posibles);
                        include '../views/lista.php';
                        
                    } elseif ($vista === 'sin_resultados') {
                        // No se encontró ningún personaje
                        include '../views/sin_resultados.php';
                        
                    } elseif ($vista === 'error') {
                        echo "<h2>Error: No hi ha més preguntes disponibles</h2>";
                    }
                ?>
            </div>
        </div>
    <?php require '../views/footer.php';?>
</div>
</body>
</html>
