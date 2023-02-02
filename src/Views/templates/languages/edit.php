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
        <?= form_hidden('id', $language->id ?? 0); ?>
        <div class="mb-3">
            <?= form_label('Title', 'title', ['class' => 'form-label']); ?>
            <?= form_input('title', $post['title'] ?? $language->title ?? '', ['class' => 'form-control' , 'id' => 'title']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Alias', 'alias', ['class' => 'form-label']); ?>
            <?= form_input('alias', $post['alias'] ?? $language->alias ?? '', ['class' => 'form-control' , 'id' => 'alias']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Icon', 'icon', ['class' => 'form-label']); ?>
            <?php $icon = $post['icon'] ?? $language->icon ?? ''; ?>
            <?php if (! empty($icon)) : ?>
                <?php if (file_exists(FCPATH . '/' . trim($icon, '/ '))) : ?>
                    <img class="d-block mb-3" src="<?= $icon; ?>" height="24">
                <?php else : ?>
                    <p>Not found!</p>
                <?php endif; ?>
            <?php endif; ?>
            <?= form_input('icon', $post['icon'] ?? $language->icon ?? '', ['class' => 'form-control' , 'id' => 'icon']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Order', 'sort_order', ['class' => 'form-label']); ?>
            <?= form_input('sort_order', $post['sort_order'] ?? $language->sort_order ?? 1000, ['class' => 'form-control' , 'id' => 'sort_order']); ?>
        </div>
        <div class="mb-3 overflow-hidden">
            <?= anchor(route_by_alias('languages_list'), 'Back', ['class' => 'btn btn-secondary float-start']); ?>
            <?= form_submit('submit', 'Update', ['class' => 'btn btn-primary float-end']); ?>
        </div>
    <?= form_close(); ?>
<?= $this->endSection(); ?>