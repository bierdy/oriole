<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($languages)) : ?>
        <p class="text-end">Languages count: <?= count($languages); ?></p>
        <p class="text-end">
            <?php if (! empty($variables_count)) : ?>
                <?= anchor(route_by_alias('delete_all_languages'), 'Delete all languages', ['class' => 'btn btn-danger modal-alert-link', 'data-alert-link-text' => "There are {$variables_count} variables that has the language assigned. To delete all languages first unassigned all languages from all variables."]); ?>
            <?php else : ?>
                <?= anchor(route_by_alias('delete_all_languages'), 'Delete all languages', ['class' => 'btn btn-danger modal-confirm-link', 'data-confirm-link-text' => 'Are you sure you want to delete all languages?']); ?>
            <?php endif; ?>
        </p>
        <div class="table-responsive mb-3">
            <table class="table link-secondary m-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Code</th>
                        <th>Default</th>
                        <th>Order</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($languages as $language) { ?>
                        <tr>
                            <td><?= $language->id; ?></td>
                            <td>
                                <?php if (! empty($language->icon)) : ?>
                                    <?php if (file_exists(FCPATH . '/' . trim($language->icon, '/ '))) : ?>
                                        <img src="<?= $language->icon; ?>" height="24">
                                    <?php else : ?>
                                        Not found!
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= anchor(route_by_alias('edit_language', $language->id), $language->title, ['class' => 'link-secondary text-decoration-none']); ?>
                            </td>
                            <td><?= $language->code; ?></td>
                            <td>
                                <?php if ($language->default) : ?>
                                    <i class="bi bi-check"></i>
                                <?php endif ?>
                            </td>
                            <td><?= $language->order; ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($language->created_at)); ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($language->updated_at)); ?></td>
                            <td>
                                <div class="text-end">
                                    <?php if (empty($language->default)) : ?>
                                        <?= anchor(route_by_alias('set_default_language', $language->id), 'Set default'); ?>
                                    <?php endif ?>
                                    <?php if (empty($language->active)) : ?>
                                        <?= anchor(route_by_alias('activate_language', $language->id), '<i class="bi bi-toggle-off"></i>'); ?>
                                    <?php else : ?>
                                        <?= anchor(route_by_alias('deactivate_language', $language->id), '<i class="bi bi-toggle-on"></i>'); ?>
                                    <?php endif ?>
                                    <?php if (! empty($language->variables_count)) : ?>
                                        <?= anchor(route_by_alias('delete_language', $language->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-alert-link', 'data-alert-link-text' => "There are {$language->variables_count} variables with the language &quot;{$language->title}&quot;. To delete a language uninstall this language from all variables assigned it."]); ?>
                                    <?php else : ?>
                                        <?= anchor(route_by_alias('delete_language', $language->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-confirm-link', 'data-confirm-link-text' => "Are you sure you want to delete language &quot;{$language->title}&quot;?"]); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p>There are no languages.</p>
    <?php endif ?>
    <div class="mb-3 overflow-hidden">
        <?= anchor(route_by_alias('add_language'), 'Add language', ['class' => 'btn btn-primary float-end']); ?>
    </div>
<?= $this->endSection(); ?>