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
function mostraHistorial() {
    if (!empty($_SESSION['historial'])) {
        echo "<ul class='llista-sidebar'>";
        foreach (array_reverse($_SESSION['historial']) as $h) echo "<li>$h</li>";
        echo "</ul>";
    } else {
        echo "<h2 class='historial-buit'>No hi ha encerts</h2>";
    }
}

// -------------------
// Afegir nou personatge amb fitxer
// -------------------
// $missatge = '';
// $final = false;
// if (isset($_POST['afegir_personatge'])) {
//     $nom = trim($_POST['nou_personatge']);
//     $desc = trim($_POST['descripcion_personatge']);
    
//     if (isset($_FILES['imagen_personatge']) && $_FILES['imagen_personatge']['error'] === UPLOAD_ERR_OK) {
//         $tmp_name = $_FILES['imagen_personatge']['tmp_name'];
//         $filename = basename($_FILES['imagen_personatge']['name']);
//         $upload_dir = __DIR__ . '/uploads/';
//         if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
//         $target_file = $upload_dir . $filename;

//         if (move_uploaded_file($tmp_name, $target_file)) {
//             $img = 'uploads/' . $filename;

//             $stmt = $db->prepare("INSERT INTO personajes (nombre, descripcion, imagen_url) VALUES (?, ?, ?)");
//             $stmt->execute([$nom, $desc, $img]);

//             $_SESSION['historial'][] = $nom;
//             $missatge = "<h2>Personatge afegit correctament: $nom</h2>
//                          <form method='post'>
//                              <button name='start' class='btn'>Jugar de nou</button>
//                          </form>";
//             $final = true;
//             $_SESSION['condiciones'] = [];
//         } else {
//             $missatge = "<p>Error pujant la imatge.</p>";
//         }
//     } else {
//         $missatge = "<p>Has de seleccionar una imatge.</p>";
//     }
// }

$seccio = $_GET['seccio'] ?? '';

// $preguntas = $db->query("SELECT * FROM preguntas")->fetchAll(PDO::FETCH_ASSOC);

// if (isset($_POST['start'])) {
//     $_SESSION['condiciones'] = [];
//     $pregunta = $preguntas[array_rand($preguntas)];
// } elseif (isset($_POST['pregunta_id'], $_POST['respuesta'])) {
//     $id_pregunta = (int)$_POST['pregunta_id'];
//     $respuesta = (int)$_POST['respuesta'];

//     $_SESSION['condiciones'][$id_pregunta] = $respuesta;

//     $sql = "SELECT p.* FROM personajes p WHERE NOT EXISTS (SELECT 1 FROM respuestas_personajes r WHERE r.personaje_id = p.id AND (";
//     $condiciones = [];
//     foreach ($_SESSION['condiciones'] as $id_p => $resp) {
//         $condiciones[] = "(r.pregunta_id = $id_p AND r.respuesta != $resp)";
//     }
//     $sql .= implode(" OR ", $condiciones) . "))";

//     $resultado = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
//     $comptador_restants = count($resultado);

//     if ($comptador_restants == 1) {
//         $personaje = $resultado[0];
//         $_SESSION['historial'][] = $personaje['nombre'];
//         $_SESSION['condiciones'] = [];

//         $missatge = "
//             <div class='resultado'>
//                 <h2>El personatge és: <span>{$personaje['nombre']}</span></h2>
//                 <img src='{$personaje['imagen_url']}' alt='{$personaje['nombre']}' class='foto-gran'>
//                 <p>{$personaje['descripcion']}</p>
//             </div>";
//         $final = true;
//     } elseif ($comptador_restants == 0) {
//         $_SESSION['condiciones'] = [];
//         $missatge = "
//             <h2>No hi ha coincidències</h2>
//             <p>Vols afegir el personatge que havies pensat?</p>
//             <form method='post' enctype='multipart/form-data'>
//                 <input type='text' name='nou_personatge' placeholder='Nom del personatge' required class='input-text'><br><br>
//                 <textarea name='descripcion_personatge' placeholder='Descripció' required class='input-textarea'></textarea><br><br>
//                 <input type='file' name='imagen_personatge' accept='image/*' required class='input-file'><br><br>
//                 <button type='submit' name='afegir_personatge' class='btn'>Afegir</button>
//             </form>";
//         $final = true;
//     } else {
//         $preguntes_fetes = array_keys($_SESSION['condiciones']);
//         $pendents = array_filter($preguntas, fn($p) => !in_array($p['id'], $preguntes_fetes));

//         if (empty($pendents)) {
//             $_SESSION['condiciones'] = [];
//             $missatge = "<h2>No queden preguntes</h2>";
//             $final = true;
//         } else {
//             $pregunta = $pendents[array_rand($pendents)];
//         }
//     }
// }
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

    <?php if ($seccio==='historial'): ?>
        <div class="sidebar-content"><h2>Historial</h2>
            <?php mostraHistorial(); ?>
            <form method="post">
                <?php if(!empty($_SESSION['historial'])): ?>
                    <button name="reiniciar" class="btn-no">Esborrar historial</button>
                <?php endif; ?>
            </form>
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
            <?php
                $vista = $_SESSION['vista'] ?? 'inicio';
                echo "VISTA: $vista";
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
    <?php require '../views/footer.php';?>
</div>
</body>
</html>
