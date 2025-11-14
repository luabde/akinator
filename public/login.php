<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <title>Akinator DC</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&family=Bangers&display=swap" rel="stylesheet">

  <!-- Estils -->
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<?php
  session_start();
  // Obtener errores de la sesión y limpiarlos
  $errores = $_SESSION['errores_login'] ?? [];
  unset($_SESSION['errores_login']);

?>

<?php require '../views/header.php'?>
<div class="akinator">
  <img src="img/fotoAkinator.png" alt="Akinator" height="600px">

  <div class="login-wrapper">

    <?php
      $form = $_GET['login'] ?? '';
      // En el caso de que no haya nada en form o que form sea login, se mostrará el formulario de login.
      if(empty($form) || $form == 'login'){
    ?>

    <div class="login-form">
      <h2>Iniciar sessió</h2>
        <!-- En el caso de hayan errores, se mostraran a traves de un foreach -->
        <?php if (!empty($errores)): ?>
        <div class="errores-container">
          <?php foreach($errores as $error): ?>
            <div class="errores-login-form">
              <?php echo $error; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <form method="POST" novalidate action="../controllers/userController.php?action=login">
        <label for="email">Email</label>
        <input type="email" name="email" placeholder="El teu email" id="email" required>

        <label for="password">Contrasenya</label>
        <input type="password" name="password" placeholder="La teva contrasenya" id="password" required>

        <input type="submit" value="Iniciar sessió">
      </form>

      <p class="registro-link">
        No tens compte? <a href="login.php?login=signIn">Registra't aquí</a>
      </p>
    </div>

    <?php
      } else {
        // Cuando el form sea algo diferente a login, en este caso signup se mostrará el formulario para registrarse
    ?>

    <div class="signIn-container">
      <h2>Registra't</h2>

      <?php foreach($errores as $key): ?>
        <div class="errores-login-form">
          <?php echo $key; ?>
        </div>
      <?php endforeach; ?>

      <form method="POST" novalidate action="../controllers/userController.php?action=signin">
      <fieldset style="border: none;">          
        <legend>Nom d'usuari, Email i contrasenya</legend>
          <label for="nomUsuari">Nom d'usuari</label>
          <input type="text" name="nomUsuari" placeholder="El teu nom d'usuari" id="nomUsuari" required>

          <label for="email">Email</label>
          <input type="email" name="email" placeholder="El teu email" id="email" required>

          <label for="password">Contrasenya</label>
          <input type="password" name="password" placeholder="La teva contrasenya" id="password" required>
        </fieldset>
        <input type="submit" value="Crear compte">
      </form>

      <p class="registro-link">
        Ja tens compte? <a href="login.php?login=login">Inicia sessió aquí</a>
      </p>
    </div>

    <?php } ?>
  </div>
  </div>
  <?php require '../views/footer.php';?>
</body>
</html>