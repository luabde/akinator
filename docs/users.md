# Documentació Sistema de Gestió d'Usuaris - Akinator DC

## Índex
1. [Introducció](#introducció)
2. [Arquitectura MVC](#arquitectura-mvc)
4. [Model (userModel.php)](#model-usermodelphp)
5. [Controller (userController.php)](#controller-usercontrollerphp)
6. [Vista (login.php)](#vista-loginphp)
7. [Flux de funcionament](#flux-de-funcionament)
8. [Gestió d'errors](#gestió-derrors)
9. [Seguretat](#seguretat)

---

## Introducció

Aquest document descriu el sistema de gestió d'usuaris implementat per al projecte **Akinator DC**. El sistema permet:

- Registre de nous usuaris
- Inici de sessió
- Tancament de sessió
- Validació de dades
- Gestió d'errors
- Protecció contra injeccions SQL

---

## Arquitectura MVC

El sistema segueix el patró **Model-Vista-Controlador (MVC)** per mantenir el codi organitzat i escalable:

- Vista (login.php i header.php) Les vistes mostren al login.php els formularis de registre i iniciar sessió. Al header, es mostra en cas de que el usuari estigui loguejat es mostra el usuari amb possibilitat de tancar la sessió
- Controller (userController.php) Es gestiona la lògica del negoci i les validacions
- Model (userModel.php) És el que interactua amb la base de dades.

---

## 🗄️ Base de dades

### Taula: `usuarios`

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Camps:**
- `id`: Identificador únic de l'usuari
- `nombre_usuario`: Nom que mostrarà l'aplicació
- `email`: Correu electrònic (únic, utilitzat per al login)
- `contrasena`: Contrasenya hashejada amb `password_hash()`
- `created_at`: Data de creació del compte

---

## Model (userModel.php)

### Descripció
El model `Usuario` gestiona totes les interaccions amb la base de dades relacionades amb els usuaris.

### Mètodes

#### `__construct()`
Inicialitza la connexió a la base de dades.

```php
public function __construct() {
    $this->db = conectarDB();
}
```

---

#### `findByEmail($email)`
Busca un usuari per email a la base de dades.

**Paràmetres:**
- `$email` (string): Email de l'usuari a buscar

**Retorna:**
- Array associatiu amb les dades de l'usuari si existeix
- `null` si no es troba l'usuari

**Exemple:**
```php
$usuario = new Usuario();
$user = $usuario->findByEmail('exemple@email.com');

if ($user) {
    echo "Usuari trobat: " . $user['nombre_usuario'];
} else {
    echo "Usuari no existeix";
}
```

**Seguretat:**
- Utilitza `mysqli_real_escape_string()` per prevenir injeccions SQL

---

#### `createUser($nombre_usuario, $email, $password)`
Crea un nou usuari a la base de dades.

**Paràmetres:**
- `$nombre_usuario` (string): Nom de l'usuari
- `$email` (string): Email de l'usuari
- `$password` (string): Contrasenya en text pla (s'hasheja automàticament)

**Retorna:**
- `true` si l'usuari es crea correctament
- `false` si hi ha un error

**Exemple:**
```php
$usuario = new Usuario();
$resultado = $usuario->createUser('Joan', 'joan@email.com', 'password123');

if ($resultado) {
    echo "Usuari creat correctament";
}
```

**Seguretat:**
- Hasheja la contrasenya amb `password_hash(PASSWORD_DEFAULT)`
- Utilitza `mysqli_real_escape_string()` per prevenir injeccions SQL

---

## Controller (userController.php)

### Descripció
El controlador `userController` gestiona la lògica de negoci, validacions i flux de l'aplicació.

### Propietats

```php
private $model;      // Instància del model Usuario
private $errores;    // Array d'errors de validació
```

---

### Mètodes

#### `__construct()`
Inicialitza el controlador i crea una instància del model.

```php
public function __construct() {
    $this->model = new Usuario();
}
```

---

#### `login()`
Gestiona el procés d'inici de sessió.

**Flux:**
1. Obté i valida l'email i contrasenya del formulari
2. Comprova que els camps no estiguin buits
3. Busca l'usuari a la base de dades
4. Verifica la contrasenya amb `password_verify()`
5. Si tot és correcte, crea la sessió i redirigeix
6. Si hi ha errors, els guarda en sessió i torna al formulari

**Validacions:**
- Email vàlid (utilitza `FILTER_VALIDATE_EMAIL`)
- Contrasenya no buida
- Usuari existeix a la BD
- Contrasenya correcta

**Sessions creades:**
```php
$_SESSION['usuario'] = $usuario['nombre_usuario'];
$_SESSION['login'] = true;
```

---

#### `signIn()`
Gestiona el procés de registre d'un nou usuari.

**Flux:**
1. Obté les dades del formulari (nom, email, contrasenya)
2. Valida tots els camps
3. Comprova que l'email no existeixi ja
4. Crea l'usuari a la base de dades
5. Inicia sessió automàticament
6. Redirigeix a la pàgina principal

**Validacions:**
- Nom d'usuari no buit
- Email vàlid i únic
- Contrasenya mínima de 6 caràcters
- Email no registrat prèviament

---

#### `logOut()`
Tanca la sessió de l'usuari actual.

**Accions:**
1. Estableix `$_SESSION['login']` a `false`
2. Elimina `$_SESSION['usuario']`
3. Redirigeix a la pàgina d'inici

---

### Enrutament

El controlador utilitza un `switch` per determinar quina acció executar segons el paràmetre GET `action`:

```php
$action = $_GET['action'];
$userController = new userController();

switch($action) {
    case 'login':
        $userController->login();
        break;
    case 'signin':
        $userController->signIn();
        break;
    case 'logout':
        $userController->logOut();
        break;
}
```

**URLs d'exemple:**
- `userController.php?action=login` → Processa inici de sessió
- `userController.php?action=signin` → Processa registre
- `userController.php?action=logout` → Tanca sessió

Aquestes URL s'envien a través dels formularis de login, registre i la creu per tancar la sessió que en aquest cas és `<a></a>`

---

## Vista (login.php)

### Descripció
Pàgina que conté els formularis d'inici de sessió i registre.

### Funcionament

La vista canvia entre dos formularis segons el paràmetre GET `login`:

```php
$form = $_GET['login'] ?? '';

if (empty($form) || $form == 'login') {
    // Mostra formulari de login
} else {
    // Mostra formulari de registre
}
```

---

### Formulari de Login

**URL:** `login.php` o `login.php?login=login`

**Camps:**
- Email (input type="email")
- Contrasenya (input type="password")

**Action:** `../controllers/userController.php?action=login`

**Codi:**
```html
<form method="POST" action="../controllers/userController.php?action=login">
    <label for="email">Email</label>
    <input type="email" name="email" placeholder="El teu email" id="email" required>

    <label for="password">Contrasenya</label>
    <input type="password" name="password" placeholder="La teva contrasenya" id="password" required>

    <input type="submit" value="Iniciar sessió">
</form>
```

---

### Formulari de Registre

**URL:** `login.php?login=signIn`

**Camps:**
- Nom d'usuari (input type="text")
- Email (input type="email")
- Contrasenya (input type="password")

**Action:** `../controllers/userController.php?action=signin`

**Codi:**
```html
<form method="POST" action="../controllers/userController.php?action=signin">
    <label for="nomUsuari">Nom d'usuari</label>
    <input type="text" name="nomUsuari" placeholder="El teu nom d'usuari" id="nomUsuari" required>

    <label for="email">Email</label>
    <input type="email" name="email" placeholder="El teu email" id="email" required>

    <label for="password">Contrasenya</label>
    <input type="password" name="password" placeholder="La teva contrasenya" id="password" required>

    <input type="submit" value="Crear compte">
</form>
```

---

## Flux de funcionament

### Flux d'inici de sessió (Login)

```
1. Usuari omple formulari login.php

2. POST -> userController.php?action=login

3. userController->login()
   - Valida email i password
   - Crida $model->findByEmail()

4. userModel->findByEmail()
   - Consulta BD: SELECT * FROM usuarios WHERE email = ?
   - Retorna dades usuari o null

5. userController verifica password
   - password_verify($password, $usuario['contrasena'])

6a. Si correcte:
    - Crea $_SESSION['usuario']
    - Crea $_SESSION['login'] = true
    - Redirect → index.php
         
6b. Si incorrecte:
    - Guarda errors en $_SESSION['errores_login']
    - Redirect → login.php?login=login
```

---

### Flux de registre (Sign In)

```
1. Usuari omple formulari login.php?login=signIn

2. POST → userController.php?action=signin

3. userController->signIn()
   - Valida nom, email, password
   - Comprova email únic amb $model->findByEmail()

4. Si validacions OK:
   - Crida $model->createUser()

5. userModel->createUser()
   - Hasheja password: password_hash()
   - INSERT INTO usuarios (nombre_usuario, email, contrasena)
   - Retorna true/false

6a. Si correcte:
    - Crea $_SESSION['usuario']
    - Crea $_SESSION['login'] = true
    - Redirect → index.php
         
6b. Si incorrecte:
    - Guarda errors en $_SESSION['errores_login']
    - Redirect → login.php?login=signIn
```

---

### Flux de tancament de sessió (Logout)

```
1. Usuari clica botó logout (header.php)

2. GET → userController.php?action=logout

3. userController->logOut()
   - $_SESSION['login'] = false
   - unset($_SESSION['usuario'])
   - Redirect → index.php
```

---

## Gestió d'errors

### Sistema d'errors

Els errors es gestionen mitjançant sessions per mantenir-los entre les diferents pàgines:

```php
// Al controlador
$_SESSION['errores_login'] = $this->errores;
header('Location: ../public/login.php?login=login');

// A la vista
$errores = $_SESSION['errores_login'] ?? [];
unset($_SESSION['errores_login']); // Neteja després de mostrar
```

---

### Errors de validació

#### Login
- "L'email es obligatori o no es vàlid"
- "La contrassenya és obligatoria"
- "L'usuari no existeix"
- "La contrassenya no es correcta"

#### Registre
- "El nom d'usuari es obligatori"
- "L'email es obligatori o no es vàlid"
- "El correu ja existeix"
- "La contrassenya és obligatoria"
- "La contrassenya ha de tenir al menys 6 caràcters"
- "Error al crear el usuario"

---

### Visualització d'errors a la vista

```php
<?php if (!empty($errores)): ?>
  <div class="errores-container">
    <?php foreach($errores as $error): ?>
      <div class="errores-login-form">
        <?php echo $error; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
```

---

## Seguretat

### Mesures de seguretat implementades

#### 1. Protecció contra injeccions SQL
```php
$email = mysqli_real_escape_string($this->db, $email);
```
- Escapa caràcters especials abans d'inserir a la BD

---

#### 2. Hashejat de contrasenyes
```php
// Al crear usuari
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Al verificar
$auth = password_verify($password, $usuario['contrasena']);
```
- **Mai** es guarden contrasenyes en text pla
- Utilitza l'algoritme bcrypt per defecte

---

#### 3. Validació d'email
```php
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
```
- Utilitza filtres natius de PHP per validar formats

---

#### 4. Sessions segures
```php
session_start(); // Al començament de cada script
$_SESSION['login'] = true; // Flag d'autenticació
```
- Control d'accés basat en sessions
- Protecció de pàgines privades

---

#### 5. Emails únics
```php
if ($this->model->findByEmail($email)) {
    $this->errores = "El correu ja existeix";
}
```
- Evita duplicats a la base de dades
- Restricció UNIQUE a nivell de BD