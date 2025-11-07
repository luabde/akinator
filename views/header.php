<?php
  // session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akinator PHP</title>
  <link rel="stylesheet" href="../public/css/general.css">
</head>
<body>
  <nav>
    <p>LOGO</p>
    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
      <a href="#"><?php echo $_SESSION['usuario']; ?></a>
    <?php else: ?>
      <a href="../public/login.php">Iniciar sesión</a>
    <?php endif; ?>
  </nav>