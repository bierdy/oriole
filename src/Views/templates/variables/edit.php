<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($errors)) : ?>
        <?= $this->render('layouts/common/form_errors.php'); ?>
    <?php elseif (! empty($message)) : ?>
        <div class="alert alert-success">
            <?= $message; ?>
        </div>
    <?php endif ?>
    <?= form_open(); ?>
        <?= form_hidden('id', $variable->id ?? 0); ?>
        <div class="mb-3">
            <?= form_label('Title', 'title', ['class' => 'form-label']); ?>
            <?= form_input('title', $post['title'] ?? $variable->title ?? '', ['class' => 'form-control' , 'id' => 'title']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Alias', 'alias', ['class' => 'form-label']); ?>
            <?= form_input('alias', $post['alias'] ?? $variable->alias ?? '', ['class' => 'form-control' , 'id' => 'alias']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Variable handler', 'variable_handler', ['class' => 'form-label']); ?>
            <?= form_input('variable_handler', $post['variable_handler'] ?? $variable->variable_handler ?? '', ['class' => 'form-control' , 'id' => 'variable_handler']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Settings', 'settings', ['class' => 'form-label']); ?>
            <?= form_textarea('settings', $post['settings'] ?? $variable->settings ?? '', ['class' => 'form-control' , 'id' => 'settings']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Variable view', 'variable_view', ['class' => 'form-label']); ?>
            <?= form_input('variable_view', $post['variable_view'] ?? $variable->variable_view ?? '', ['class' => 'form-control' , 'id' => 'variable_view']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Validation rules', 'validation_rules', ['class' => 'form-label']); ?>
            <?= form_textarea('validation_rules', $post['validation_rules'] ?? $variable->validation_rules ?? '', ['class' => 'form-control' , 'id' => 'validation_rules']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Language', 'language_id', ['class' => 'form-label']); ?>
            <?= form_dropdown('language_id', $languages_options, $post['language_id'] ?? $variable->language_id ?? '0', ['class' => 'form-control' , 'id' => 'language_id']); ?>
        </div>
        <div class="mb-3 overflow-hidden">
            <?= anchor(route_by_alias('variables_list'), 'Back', ['class' => 'btn btn-secondary float-start']); ?>
            <?= form_submit('submit', 'Update', ['class' => 'btn btn-primary float-end']); ?>
        </div>
    <?= form_close(); ?>
<?= $this->endSection(); ?>