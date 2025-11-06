
<?php
    require '../config/database.php';
    $db = conectarDB();

    $errores = [];

    // Autenticar el usuario
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        // Obtenemos el email y la password y evitamos que introduzcan codigo no deseado.
        $email = mysqli_real_escape_string($db, filter_var($_POST['email'], FILTER_VALIDATE_EMAIL));

        $password = mysqli_real_escape_string($db, $_POST['password']);

        // Validamos que los campos no esten vacios
        if(!$email){
            $errores[] = "El email es obligatorio o no es válido";
        }

        if(!$password){
            $errores[] = "La contraseña es obligatoria";
        }

        if(empty($errores)){
            // Miramos si el user existe
            $query = "SELECT * FROM usuarios WHERE email = '{$email}'";
            $resultado = mysqli_query($db, $query);

            if($resultado->num_rows){
                // Mirar si el password es correcto
                $usuario = mysqli_fetch_assoc($resultado);
                var_dump($usuario);
                // Verificar si la contraseña es correcta o no
                $auth = password_verify($password, $usuario['contrasena']);

                var_dump($auth);
            }else{
                $errores[] = "El usuario no existe";
            }
        }


    }
    // Se incluye el header 
    require '../views/header.php';
?>
<div class="login-container">
  <h2>Iniciar sesión</h2>

  <?php foreach($errores as $key):?>
    <div class="errores-login-form">
        <?php echo $key;?>
    </div>
    <?php endforeach;?>

  <form method="POST" novalidate>
    <fieldset>
        <legend>Email y password</legend>
        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Tu email" id="email" required>

        <label for="password">Contraseña</label>
        <input type="password" name="password" placeholder="Tu contraseña" id="password" required>
    </fieldset>
    <input type="submit" value="Iniciar Session">
  </form>

  <p class="registro-link">
    ¿No tienes cuenta? <a href="#">Regístrate aquí</a>
  </p>
</div>
<?php
    require '../views/footer.php';
?>