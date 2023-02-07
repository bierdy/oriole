<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($errors)) : ?>
        <?= $this->render('layouts/common/form_errors.php'); ?>
    <?php elseif (! empty($message)) : ?>
        <div class="alert alert-success">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <?= form_open(); ?>
        <?= form_hidden('id', $variable_group->id ?? 0); ?>
        <div class="mb-3">
            <?= form_label('Title', 'title', ['class' => 'form-label']); ?>
            <?= form_input('title', $post['title'] ?? $variable_group->title ?? '', ['class' => 'form-control', 'id' => 'title']); ?>
        </div>
        <div class="mb-3 overflow-hidden">
            <?= anchor(route_by_alias('edit_template', $template_variable_group->template_id), 'Back', ['class' => 'btn btn-secondary float-start']); ?>
            <?= form_submit('submit', 'Update', ['class' => 'btn btn-primary float-end']); ?>
        </div>
    <?= form_close(); ?>
<?= $this->endSection(); ?>