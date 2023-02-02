<div class="alert alert-danger">
    <?= $message; ?>
    <?php foreach ($errors as $errorGroup) : ?>
        <?php foreach ($errorGroup as $error) : ?>
            <div><?= $error; ?></div>
        <?php endforeach ?>
    <?php endforeach ?>
</div>