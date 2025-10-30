<div class="info">
  <p>He reducido las opciones a <?= $num_personajes ?> personajes posibles</p>
</div>

<div class="resultado">
  <h2>¿Es alguno de estos personajes?</h2>
  
  <form method="POST" action="procesar.php">
    <div class="personajes-lista">
      <?php foreach ($personajes_posibles as $personaje): ?>
        <label class="personaje-card">
          <input type="radio" name="personaje_seleccionado" value="<?= $personaje['id'] ?>" required>
          <span><?= htmlspecialchars($personaje['nombre']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
    
    <button type="submit" class="btn-primary mt-2">Confirmar</button>
  </form>
</div>