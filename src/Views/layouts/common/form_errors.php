<div class="alert alert-danger">
    <?= $message; ?>
    <?php if (! empty($errors['pdo'])) { ?>
        <?php foreach ($errors['pdo'] as $error) : ?>
            <div><?= $error; ?></div>
        <?php endforeach ?>
    <?php } ?>
    <?php if (! empty($errors['logic'])) { ?>
        <?php foreach ($errors['logic'] as $error) : ?>
            <div><?= $error; ?></div>
        <?php endforeach ?>
    <?php } ?>
    <?php if (! empty($errors['data'])) { ?>
        <?php foreach ($errors['data'] as $data) : ?>
            <?php foreach ($data as $error) : ?>
                <div><?= $error; ?></div>
            <?php endforeach ?>
        <?php endforeach ?>
    <?php } ?>
</div>