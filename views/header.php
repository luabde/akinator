<?php
  // session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akinator PHP</title>
  <link rel="stylesheet" href="../public/style.css">
</head>
<body>

  <nav class="header">
    <span class="header-title">AKINATOR DC</span> 

    <div class="login-container">
      <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
        <img src="img/user-icon.png" id="user-icon">
        <p><?php echo $_SESSION['usuario']; ?></p>
        <a href="../controllers/loginController.php?action=logout" class="btn-login">Tancar sessió</a>
      <?php else: ?>
        <a href="../public/login.php" class="btn-login">Iniciar sessió</a>
      <?php endif; ?>
    </div>
    </nav>