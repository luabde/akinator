<div class="biblioteca">
    <?php
    foreach($lista as $personaje){
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