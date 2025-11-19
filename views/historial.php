<div class="historial">
    <?php if (!empty($historial)): ?>
        <?php foreach ($historial as $fila): ?>
            <ul>
                <?php foreach ($fila as $valor): ?>
                    <h4><?= htmlspecialchars((string)$valor) ?></h4>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay historial disponible.</p>
    <?php endif; ?>
</div>
