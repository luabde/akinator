# Documentació Sistema de Biblioteca - Akinator DC

## Índex
1. [Introducció](#introducció)
2. [Arquitectura MVC](#arquitectura-mvc)
3. [Model (personajesModel.php)](#model-personajesmodelphp)
4. [Controller (personajesController.php)](#controller-personajescontrollerphp)
5. [Vista (biblioteca.php)](#vista-bibliotecaphp)
6. [Flux de funcionament](#flux-de-funcionament)
7. [Integració amb index.php](#integració-amb-indexphp)

---

## Introducció

Aquest document descriu el sistema de biblioteca implementat per al projecte **Akinator DC**. El sistema permet:

- Visualització de tots els personatges disponibles
- Presentació de fitxes de personatges amb imatge i descripció
- Accés ràpid a la informació completa dels personatges
- Interfície de tarjetes responsive

---

## Arquitectura MVC

El sistema segueix el patró **Model-Vista-Controlador (MVC)**:

- **Model** (personajesModel.php): Interactua amb la base de dades per obtenir la informació dels personatges
- **Controller** (personajesController.php): Gestiona la lògica de negoci i coordina el model amb la vista
- **Vista** (biblioteca.php): Mostra els personatges en format de targetes visuals

---

## Base de dades

### Taula: `personajes`

```sql
CREATE TABLE personajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255),
    -- Columnas adicionales para preguntas (ej: es_heroe, tiene_poderes, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Camps principals:**
- `id`: Identificador únic del personatge
- `nombre`: Nom del personatge
- `descripcion`: Descripció detallada del personatge
- `imagen_url`: Ruta relativa de la imatge del personatge
- **Columnes addicionals**: Camps per a cada pregunta del joc (valors 0/1)

---

## Model (personajesModel.php)

### Descripció
La classe `Personaje` gestiona les operacions de base de dades relacionades amb els personatges del joc.

### Propietats

```php
private $arrayPersonajes;  // Array amb tots els personatges
private $db;               // Connexió a la base de dades
```

---

### Mètodes

#### `__construct()`
Inicialitza la connexió a la base de dades.

```php
public function __construct() {
    $this->db = conectarDB();
}
```

---

#### `obtenerPersonajes()`
Obté tots els personatges de la base de dades.

**Paràmetres:**
- Cap

**Retorna:**
- Array associatiu amb tots els personatges i les seves dades

**Exemple:**
```php
$personajeModel = new Personaje();
$personajes = $personajeModel->obtenerPersonajes();

foreach ($personajes as $personaje) {
    echo $personaje['nombre'];
}
```

**Implementació:**
```php
public function obtenerPersonajes() {
    $query = 'SELECT * FROM personajes';
    $resultado = mysqli_query($this->db, $query);
    
    // Convertim el resultat en un array
    $this->arrayPersonajes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    return $this->arrayPersonajes;
}
```

---

#### `filtrarPersonajes($preguntas_info)`
Filtra els personatges segons les respostes de l'usuari durant el joc.

**Paràmetres:**
- `$preguntas_info` (array): Array associatiu amb la informació de les preguntes respondides

**Estructura de `$preguntas_info`:**
```php
[
    id_pregunta => [
        'columna' => 'es_heroe',
        'respuestaUser' => 1  // 1 = sí, 0 = no, null = no respondida
    ]
]
```

**Retorna:**
- Array amb els personatges que compleixen tots els criteris
- Array buit si no hi ha coincidències

**Exemple:**
```php
$preguntas_info = [
    1 => ['columna' => 'es_heroe', 'respuestaUser' => 1],
    2 => ['columna' => 'tiene_poderes', 'respuestaUser' => 1]
];

$personajes_filtrados = $personajeModel->filtrarPersonajes($preguntas_info);
```

**Implementació:**
```php
public function filtrarPersonajes($preguntas_info) {
    // Construir la query base
    $query = "SELECT * FROM personajes WHERE 1=1";
    
    // Per cada pregunta respondida, afegir un AND
    foreach ($preguntas_info as $id_pregunta => $info) {
        if ($info['respuestaUser'] !== null) {
            $columna = mysqli_real_escape_string($this->db, $info['columna']);
            $respuesta = (int)$info['respuestaUser'];
            $query .= " AND `$columna` = $respuesta";
        }
    }
    
    $query .= " ORDER BY nombre";
    
    $resultado = mysqli_query($this->db, $query);
    
    if (!$resultado) {
        echo "Error en query: " . mysqli_error($this->db);
        return [];
    }
    
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}
```

**Seguretat:**
- Utilitza `mysqli_real_escape_string()` per prevenir injeccions SQL
- Converteix la resposta a int per assegurar el tipus de dada

---

## Controller (personajesController.php)

### Descripció
El controlador `PersonajeController` actua com a intermediari entre el model i la vista.

### Propietats

```php
private $model;  // Instància del model Personaje
```

---

### Mètodes

#### `__construct()`
Inicialitza el controlador i crea una instància del model.

```php
public function __construct() {
    $this->model = new Personaje();
}
```

---

#### `obtenerPersonajes()`
Obté tots els personatges i carrega la vista de biblioteca.

**Flux:**
1. Crida al model per obtenir la llista de personatges
2. Passa les dades a la vista mitjançant `include`
3. La variable `$lista` queda disponible a la vista

**Exemple:**
```php
$controller = new PersonajeController();
$controller->obtenerPersonajes();
```

**Implementació:**
```php
public function obtenerPersonajes() {
    $lista = $this->model->obtenerPersonajes();
    
    // Al fer el include, la llista estarà disponible a biblioteca.php
    include '../views/biblioteca.php';
}
```

---

## Vista (biblioteca.php)

### Descripció
Vista que mostra tots els personatges en format de targetes visuals amb efecte de gir.

### Estructura HTML

La vista genera automàticament una targeta per cada personatge:

```html
<div class="biblioteca">
    <div class="card">
        <div class="card-inner">
            <img src="./ruta/imagen.jpg" alt="Nombre" class="foto">  
            <h3>Nombre del Personatge</h3>
            <p>Descripció del personatge...</p>
        </div>
    </div>
    <!-- Més targetes... -->
</div>
```

---

### Codi PHP

```php
<div class="biblioteca">
    <?php
    foreach($lista as $personaje) {
        echo "
        <div class='card'>
            <div class='card-inner'>
                <img src='./{$personaje['imagen_url']}' alt='{$personaje['nombre']}' class='foto'>  
                <h3>{$personaje['nombre']}</h3>
                <p>{$personaje['descripcion']}</p>
            </div>
        </div>";
    }
    ?>
</div>
```

### Característiques visuals

- **Layout**: Grid responsive de targetes
- **Targetes**: Efecte de hover amb transformació 3D
- **Imatges**: Les imatges dels personatges s'adapten al contenidor
- **Text**: Nom destacat i descripció llegible

---

## Flux de funcionament

### Flux de visualització de la biblioteca

```
1. Usuari clica "Biblioteca" al menú lateral (index.php)

2. URL: index.php?seccio=biblioteca

3. index.php detecta el paràmetre GET
   - if ($seccio === 'biblioteca')

4. Es crida al controlador:
   - $controller = new PersonajeController()
   - $controller->obtenerPersonajes()

5. PersonajeController->obtenerPersonajes()
   - Crida $this->model->obtenerPersonajes()

6. Personaje->obtenerPersonajes()
   - Query: SELECT * FROM personajes
   - Retorna array amb tots els personatges

7. El controlador fa include de la vista
   - include '../views/biblioteca.php'
   - La variable $lista està disponible

8. La vista genera el HTML
   - Bucle foreach sobre $lista
   - Crea una targeta per cada personatge

9. Es renderitza la biblioteca al sidebar
   - Zona .sidebar-content mostra les targetes
```

---

## Integració amb index.php

### Detecció de la secció

```php
$seccio = $_GET['seccio'] ?? '';

<?php if ($seccio === 'biblioteca'): ?>
    <div class="sidebar-content">
        <h2>Biblioteca</h2>
        <?php
            require '../controllers/personajesController.php';
            $controller = new PersonajeController();
            $controller->obtenerPersonajes();
        ?>
    </div>
<?php endif; ?>
```

---

### Enllaç al menú

```html
<a href="?seccio=biblioteca">
    <svg><!-- Icona de llibre --></svg>
    <span>Biblioteca</span>
</a>
```

**URL generada:** `index.php?seccio=biblioteca`

---

## Estils CSS

### Classes principals

- `.biblioteca`: Contenidor principal amb display grid
- `.card`: Contenidor individual de cada targeta
- `.card-inner`: Contingut interior amb efecte 3D
- `.foto`: Imatge del personatge amb border-radius
- `h3`: Títol del personatge
- `p`: Descripció del personatge

### Exemple d'estructura CSS

```css
.biblioteca {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px;
}

.card {
    perspective: 1000px;
    transition: transform 0.3s;
}

.card:hover {
    transform: scale(1.05);
}

.card-inner {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.foto {
    width: 100%;
    height: auto;
    border-radius: 8px;
}
```

---

## Consideracions tècniques

### Rendiment

- **Càrrega única**: Tots els personatges es carreguen d'una sola vegada
- **Optimització**: Les imatges haurien d'estar optimitzades per web
- **Paginació**: Per a bases de dades grans, considerar implementar paginació

### Seguretat

- **htmlspecialchars()**: S'utilitza a la vista per prevenir XSS
- **Escapament SQL**: El model utilitza `mysqli_real_escape_string()` en altres mètodes

### Escalabilitat

El sistema està preparat per:
- Afegir filtres de cerca
- Implementar ordenació personalitzada
- Afegir paginació
- Incloure més detalls dels personatges