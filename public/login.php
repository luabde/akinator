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
<body>

<div>
    <img src="img/" alt="Akinator">
</div>


  <div class="login-wrapper">
    <?php
      require '../config/database.php';
      $db = conectarDB();

      $errores = [];

      if($_SERVER['REQUEST_METHOD'] === 'POST'){
          $email = mysqli_real_escape_string($db, filter_var($_POST['email'], FILTER_VALIDATE_EMAIL));
          $password = mysqli_real_escape_string($db, $_POST['password']);

          if(!$email){
              $errores[] = "El email es obligatorio o no es válido";
          }
          if(!$password){
              $errores[] = "La contraseña es obligatoria";
          }

          if(empty($errores)){
              $query = "SELECT * FROM usuarios WHERE email = '{$email}'";
              $resultado = mysqli_query($db, $query);

              if($resultado->num_rows){
                  $usuario = mysqli_fetch_assoc($resultado);
                  $auth = password_verify($password, $usuario['contrasena']);

                  if($auth){
                      session_start();
                      $_SESSION['usuario'] = $usuario['nombre_usuario'];
                      $_SESSION['login'] = true;
                      header('Location: index.php');
                  } else {
                      $errores[] = "La contraseña no es correcta";
                  }
              } else {
                  $errores[] = "El usuario no existe";
              }
          }
      }

      $form = $_GET['login'] ?? '';

      if(empty($form) || $form == 'login'){
    ?>

    <div class="login-form">
      <h2>Iniciar sessió</h2>

      <?php foreach($errores as $key): ?>
        <div class="errores-login-form">
          <?php echo $key; ?>
        </div>
      <?php endforeach; ?>

      <form method="POST" novalidate>
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
    ?>

    <div class="signIn-container">
      <h2>Registra't</h2>

      <?php foreach($errores as $key): ?>
        <div class="errores-login-form">
          <?php echo $key; ?>
        </div>
      <?php endforeach; ?>

      <form method="POST" novalidate>
        <fieldset>
          <legend>Nom d'usuari, Email i contrasenya</legend>
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

  <?php require '../views/footer.php'; ?>
</body>
</html>
