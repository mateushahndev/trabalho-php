<?php
$flashSuccess = \App\Core\Session::getFlash('success');
$flashError   = \App\Core\Session::getFlash('error');
?>

<?php if (isset($flashSuccess)): ?>
    <div class="flash flash-success" data-autohide="8000">
        <span class="flash-icon">✓</span>
        <?= $flashSuccess ?>
    </div>
<?php endif; ?>

<?php if (isset($flashError)): ?>
    <div class="flash flash-error" data-autohide="8000">
        <span class="flash-icon">✗</span>
        <?= $flashError ?>
    </div>
<?php endif; ?>
