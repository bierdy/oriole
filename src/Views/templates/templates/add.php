<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($errors)) : ?>
        <?= $this->render('layouts/common/form_errors.php'); ?>
    <?php endif; ?>
    <?= form_open(); ?>
        <div class="mb-3">
            <?= form_label('Title', 'title', ['class' => 'form-label']); ?>
            <?= form_input('title', $post['title'] ?? '', ['class' => 'form-control' , 'id' => 'title']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Icon', 'icon', ['class' => 'form-label']); ?>
            <?= form_input('icon', $post['icon'] ?? '', ['class' => 'form-control' , 'id' => 'icon']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Template handler', 'template_handler', ['class' => 'form-label']); ?>
            <?= form_input('template_handler', $post['template_handler'] ?? '', ['class' => 'form-control' , 'id' => 'template_handler']); ?>
        </div>
        <div class="form-check mb-3">
            <?= form_checkbox('is_unique', 1, $post['is_unique'] ?? 0, ['class' => 'form-check-input mb-1' , 'id' => 'is_unique']); ?>
            <?= form_label('Unique', 'is_unique', ['class' => 'form-label']); ?>
        </div>
        <div class="mb-3 overflow-hidden">
            <?= anchor(route_by_alias('templates_list'), 'Back', ['class' => 'btn btn-secondary float-start']); ?>
            <?= form_submit('submit', 'Add', ['class' => 'btn btn-primary float-end']); ?>
        </div>
    <?= form_close(); ?>
<?= $this->endSection(); ?>