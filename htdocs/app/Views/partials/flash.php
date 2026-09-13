<?php if (isset($flashSuccess)): ?>
    <div class="flash flash-success">
        <span class="flash-icon">✓</span>
        <?= $flashSuccess ?>
    </div>
<?php endif; ?>

<?php if (isset($flashError)): ?>
    <div class="flash flash-error">
        <span class="flash-icon">✗</span>
        <?= $flashError ?>
    </div>
<?php endif; ?>