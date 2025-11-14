<?php
    require_once '../config/database.php';

    class Usuario{
        private $db;
        public function __construct(){
            $this->db = conectarDB();
        }

        public function findByEmail($email){
            // Nos aseguramos de que en el email no haya posibilidades de un injection con el real escape string de mysqli
            $email = mysqli_real_escape_string($this->db, $email);
            
            $query = "SELECT * FROM usuarios WHERE email = '{$email}'";
            
            // Hacemos la query a la bd
            $resultado = mysqli_query($this->db, $query);

            // En el caso de que haya resultado en este caso usuario, converitremos este en un array asociativo y lo devolveremos. Sino entra al if se devuelve null directamente
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                return mysqli_fetch_assoc($resultado);
            }
                return null;
            }

        public function createUser($nombre_usuario, $email, $password){
            // Nos aseguramos de que no haya intentos de injection en el nombre de usuario y en el email
            $nombre_usuario = mysqli_real_escape_string($this->db, $nombre_usuario);
            $email = mysqli_real_escape_string($this->db, $email);

            // Hasheamos la password para mayor seguridad
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO usuarios (nombre_usuario, email, contrasena) VALUES ('$nombre_usuario', '$email', '$passwordHash')";

            // Hacemos la consulta a la bd y la devolvemos
            return mysqli_query($this->db, $query);

            // var_dump(($db));
            // $nombre_usuario = "prueba";
            // $email = "correo@correo.com";
            // $password = "1234";

            // // Hasheamos la password para mayor seguridad
            // $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            // echo "$passwordHash";
            // $query = "INSERT INTO usuarios (nombre_usuario, email, contrasena) VALUES ('$nombre_usuario', '$email', '$passwordHash')";

            // mysqli_query($db, $query);
        }
    }
?>