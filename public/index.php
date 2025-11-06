<?php
// -------------------
// Conexión a DB
// -------------------
$host = 'localhost';
$dbname = 'akinator_dc';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de connexió: " . $e->getMessage());
}

session_start();
if (!isset($_SESSION['historial'])) $_SESSION['historial'] = [];
if (!isset($_SESSION['condiciones'])) $_SESSION['condiciones'] = [];

// -------------------
// FUNCIONES
// -------------------
function mostraHistorial() {
    if (!empty($_SESSION['historial'])) {
        echo "<ul class='llista-sidebar'>";
        foreach (array_reverse($_SESSION['historial']) as $h) echo "<li>$h</li>";
        echo "</ul>";
    } else {
        echo "<p class='historial-buit'>No hi ha encerts</p>";
    }
}

function mostraBiblioteca($db) {
    $sql = "SELECT nombre, imagen_url, descripcion FROM personajes ORDER BY nombre ASC";
    $personatges = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if ($personatges) {
        echo "<div class='biblioteca'>";
        foreach ($personatges as $p) {
            echo "
            <div class='card'>
                <div class='card-inner'>
                    <img src='{$p['imagen_url']}' alt='{$p['nombre']}' class='foto'>
                    <h3>{$p['nombre']}</h3>
                    <p>{$p['descripcion']}</p>
                </div>
            </div>";
        }
        echo "</div>";
    } else {
        echo "<p>No hi ha personatges registrats</p>";
    }
}

// -------------------
// Afegir nou personatge amb fitxer
// -------------------
$missatge = '';
$final = false;
if (isset($_POST['afegir_personatge'])) {
    $nom = trim($_POST['nou_personatge']);
    $desc = trim($_POST['descripcion_personatge']);
    
    if (isset($_FILES['imagen_personatge']) && $_FILES['imagen_personatge']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['imagen_personatge']['tmp_name'];
        $filename = basename($_FILES['imagen_personatge']['name']);
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $img = 'uploads/' . $filename;

            $stmt = $db->prepare("INSERT INTO personajes (nombre, descripcion, imagen_url) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $desc, $img]);

            $_SESSION['historial'][] = $nom;
            $missatge = "<h2>Personatge afegit correctament: $nom</h2>
                         <form method='post'>
                             <button name='start' class='btn'>Jugar de nou</button>
                         </form>";
            $final = true;
            $_SESSION['condiciones'] = [];
        } else {
            $missatge = "<p>Error pujant la imatge.</p>";
        }
    } else {
        $missatge = "<p>Has de seleccionar una imatge.</p>";
    }
}

// -------------------
// Juego
// -------------------
if (isset($_POST['reiniciar'])) {
    $_SESSION['historial'] = [];
    $_SESSION['condiciones'] = [];
    header("Location: index.php");
    exit;
}

$seccio = $_GET['seccio'] ?? '';

$pregunta = null;
$comptador_restants = 0;

$preguntas = $db->query("SELECT * FROM preguntas")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['start'])) {
    $_SESSION['condiciones'] = [];
    $pregunta = $preguntas[array_rand($preguntas)];
} elseif (isset($_POST['pregunta_id'], $_POST['respuesta'])) {
    $id_pregunta = (int)$_POST['pregunta_id'];
    $respuesta = (int)$_POST['respuesta'];

    $_SESSION['condiciones'][$id_pregunta] = $respuesta;

    $sql = "SELECT p.* FROM personajes p WHERE NOT EXISTS (SELECT 1 FROM respuestas_personajes r WHERE r.personaje_id = p.id AND (";
    $condiciones = [];
    foreach ($_SESSION['condiciones'] as $id_p => $resp) {
        $condiciones[] = "(r.pregunta_id = $id_p AND r.respuesta != $resp)";
    }
    $sql .= implode(" OR ", $condiciones) . "))";

    $resultado = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $comptador_restants = count($resultado);

    if ($comptador_restants == 1) {
        $personaje = $resultado[0];
        $_SESSION['historial'][] = $personaje['nombre'];
        $_SESSION['condiciones'] = [];

        $missatge = "
            <div class='resultado'>
                <h2>El personatge és: <span>{$personaje['nombre']}</span></h2>
                <img src='{$personaje['imagen_url']}' alt='{$personaje['nombre']}' class='foto-gran'>
                <p>{$personaje['descripcion']}</p>
            </div>";
        $final = true;
    } elseif ($comptador_restants == 0) {
        $_SESSION['condiciones'] = [];
        $missatge = "
            <h2>No hi ha coincidències</h2>
            <p>Vols afegir el personatge que havies pensat?</p>
            <form method='post' enctype='multipart/form-data'>
                <input type='text' name='nou_personatge' placeholder='Nom del personatge' required class='input-text'><br><br>
                <textarea name='descripcion_personatge' placeholder='Descripció' required class='input-textarea'></textarea><br><br>
                <input type='file' name='imagen_personatge' accept='image/*' required class='input-file'><br><br>
                <button type='submit' name='afegir_personatge' class='btn'>Afegir</button>
            </form>";
        $final = true;
    } else {
        $preguntes_fetes = array_keys($_SESSION['condiciones']);
        $pendents = array_filter($preguntas, fn($p) => !in_array($p['id'], $preguntes_fetes));

        if (empty($pendents)) {
            $_SESSION['condiciones'] = [];
            $missatge = "<h2>No queden preguntes</h2>";
            $final = true;
        } else {
            $pregunta = $pendents[array_rand($pendents)];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>Akinator DC</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<input type="checkbox" id="toggle-sidebar">
<div class="sidebar">
    <label for="toggle-sidebar" class="toggle-btn"></label>
    <a href="?seccio=">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffffff"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240v80H200v560h560v-240h80v240q0 33-23.5 56.5T760-120H200Zm440-400v-120H520v-80h120v-120h80v120h120v80H720v120h-80Z"/></svg>
        <span>Nova partida</span>
    </a>
    <a href="?seccio=historial">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffffff"><path d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/></svg>
        <span>Historial</span>
    </a>
    <a href="?seccio=biblioteca">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#ffffffff"><path d="M400-400h160v-80H400v80Zm0-120h320v-80H400v80Zm0-120h320v-80H400v80Zm-80 400q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320Zm0-80h480v-480H320v480ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm160-720v480-480Z"/></svg>
        <span>Biblioteca</span>
    </a>

    <?php if ($seccio==='historial'): ?>
        <div class="sidebar-content"><p>Historial</p>
            <?php mostraHistorial(); ?>
            <form method="post">
                <?php if(!empty($_SESSION['historial'])): ?>
                    <button name="reiniciar" class="btn-no">Esborrar historial</button>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($seccio==='biblioteca'): ?>
        <div class="sidebar-content"><p>Biblioteca</p>
            <?php mostraBiblioteca($db); ?>
        </div>
    <?php endif; ?>

    <div class="sidebar-handle"></div>
</div>

<div class="right-container">
<div class="topbar">
    <span class="topbar-title">AKINATOR DC</span> 

    <div class="login-container">
        <button class="btn-login">Login</button>
    </div>
</div>

<div class="main">
    <?php if(!isset($_POST['start']) && !isset($_POST['pregunta_id']) && !$final): ?>
        <h1>Pensa en un personatge</h1>
        <p>I jo intentaré endevinar-lo amb preguntes de sí/no!</p>
        <form method="post">
            <button name="start" class="btn">Començar partida</button>
        </form>
    <?php elseif($final): ?>
        <?= $missatge ?>
        <?php if (!isset($_POST['afegir_personatge'])): ?>
            <form method="post">
                <button name="start" class="btn">Jugar de nou</button>
                <button name="reiniciar" class="btn-no">Sortir</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <div class="restants">
            Estic pensant en <?= $comptador_restants ?> personatges possibles
        </div>
        <h2><?= $pregunta['texto'] ?></h2>
        <form method="post">
            <input type="hidden" name="pregunta_id" value="<?= $pregunta['id'] ?>">
            <button name="respuesta" value="1" class="btn">Sí</button>
            <button name="respuesta" value="0" class="btn-no">No</button>
        </form>
    <?php endif; ?>
</div>
</div>

</body>
</html>
