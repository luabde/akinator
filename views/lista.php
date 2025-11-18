<div class="info">
  <p>He reducido las opciones a <?= $num_personajes ?> personajes posibles</p>
</div>

<div class="resultado"> 
    <div class="personajes-lista">
      <?php foreach ($personajes_posibles as $personaje): ?>
        <label class="personaje-card">
          <span><?= htmlspecialchars($personaje['nombre']) ?> - </span>
        </label>
      <?php endforeach; ?>
    </div>
  
    <a href="../public/index.php?seccio=nueva" class="btn-primary-si">Intentar de nuevo</a>
</div>  