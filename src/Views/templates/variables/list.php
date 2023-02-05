<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($variables)) : ?>
        <p class="text-end">Variables count: <?= count($variables); ?></p>
        <p class="text-end">
            <?= anchor(route_by_alias('delete_all_variables'), 'Delete all variables', ['class' => 'btn btn-danger modal-confirm-link', 'data-confirm-link-text' => 'Are you sure you want to delete all variables?' . PHP_EOL . 'All values also will be deleted.']); ?>
        </p>
        <div class="table-responsive mb-3">
            <table class="table link-secondary m-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Alias</th>
                        <th>Language</th>
                        <th>Templates count</th>
                        <th>Resources count</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($variables as $variable) { ?>
                        <tr>
                            <td><?= $variable->id; ?></td>
                            <td>
                                <?= anchor(route_by_alias('edit_variable', $variable->id), $variable->title, ['class' => 'link-secondary text-decoration-none']); ?>
                            </td>
                            <td><?= $variable->alias; ?></td>
                            <td><?= $variable->language_title; ?></td>
                            <td><?= $variable->templates_count; ?></td>
                            <td><?= $variable->values_count; ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($variable->created_at)); ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($variable->updated_at)); ?></td>
                            <td>
                                <div class="text-end">
                                    <?php if (empty($variable->is_active)) : ?>
                                        <?= anchor(route_by_alias('activate_variable', $variable->id), '<i class="bi bi-toggle-off"></i>'); ?>
                                    <?php else : ?>
                                        <?= anchor(route_by_alias('deactivate_variable', $variable->id), '<i class="bi bi-toggle-on"></i>'); ?>
                                    <?php endif; ?>
                                    <?= anchor(route_by_alias('delete_variable', $variable->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-confirm-link', 'data-confirm-link-text' => "Are you sure you want to delete variable \"$variable->title\"?"  . PHP_EOL . "The values of this variable for all resources using this variable also will be deleted."]); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p>There are no variables.</p>
    <?php endif; ?>
    <div class="mb-3 overflow-hidden">
        <?= anchor(route_by_alias('add_variable'), 'Add variable', ['class' => 'btn btn-primary float-end']); ?>
    </div>
<?= $this->endSection(); ?>