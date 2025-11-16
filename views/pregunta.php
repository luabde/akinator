<div class="info">
  <p>Personajes posibles: <?= $num_personajes ?? 0 ?></p>
  <p>Preguntas respondidas: <?= $preguntas_respondidas ?></p>
</div>

<?php if ($pregunta): ?>
    <p class="pregunta"><?= htmlspecialchars($pregunta['texto'] ?? '') ?></p>

    <form method="POST" action="juego.php">
        <input type="hidden" name="pregunta_id" value="<?= $pregunta_actual['id'] ?? '' ?>">
        <div class="botones">
            <button type="submit" name="respuesta" value="1" class="btn-primary">Sí</button>
            <button type="submit" name="respuesta" value="0" class="btn-primary">No</button>
        </div>
    </form>
<?php else: ?>
    <p>No hay más preguntas disponibles.</p>
<?php endif; ?>