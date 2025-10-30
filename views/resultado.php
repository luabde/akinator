<div class="resultado">
  <?php if ($resultado == 'ganado'): ?>
    <h2>🎉 ¡Gané! ¡Lo adiviné!</h2>
    <p class="pregunta">¡Sabía que era <strong><?= htmlspecialchars($personaje_nombre) ?></strong>!</p>
    <p>¡Gracias por jugar!</p>
  <?php else: ?>
    <h2>Oh no, me equivoqué</h2>
    <p>Pensé que era <strong><?= htmlspecialchars($personaje_nombre) ?></strong></p>
    <p>¡Pero me equivoqué! ¿Quieres jugar otra vez?</p>
  <?php endif; ?>
</div>

<form action="index.php" method="GET">
  <button class="btn-primary" type="submit">Jugar de nuevo</button>
</form>