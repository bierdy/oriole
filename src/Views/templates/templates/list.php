<?= $this->extend('templates/default.php'); ?>

<?= $this->section('template'); ?>
    <?php if (! empty($templates)) : ?>
        <p class="text-end">Templates count: <?= count($templates); ?></p>
        <p class="text-end">
            <?php if (! empty($resources_count)) : ?>
                <?= anchor(route_by_alias('delete_all_templates'), 'Delete all templates', ['class' => 'btn btn-danger modal-alert-link', 'data-alert-link-text' => "There are $resources_count resources assigned to all templates. To delete all templates first delete all resources."]); ?>
            <?php else : ?>
                <?= anchor(route_by_alias('delete_all_templates'), 'Delete all templates', ['class' => 'btn btn-danger modal-confirm-link', 'data-confirm-link-text' => 'Are you sure you want to delete all templates?']); ?>
            <?php endif; ?>
        </p>
        <div class="table-responsive mb-3">
            <table class="table link-secondary m-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Template handler</th>
                        <th>Unique</th>
                        <th>Resources count</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($templates as $template) { ?>
                        <tr>
                            <td><?= $template->id; ?></td>
                            <td>
                                <?php if (! empty($template->icon)) : ?>
                                    <i class="<?= $template->icon; ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= anchor(route_by_alias('edit_template', $template->id), $template->title, ['class' => 'link-secondary text-decoration-none']); ?>
                            </td>
                            <td><?= $template->template_handler; ?></td>
                            <td>
                                <?php if ($template->is_unique) : ?>
                                    <i class="bi bi-check"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= $template->resources_count; ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($template->created_at)); ?></td>
                            <td><?= date('Y.m.d H:i:s', strtotime($template->updated_at)); ?></td>
                            <td>
                                <div class="text-end">
                                    <?php if (empty($template->is_active)) : ?>
                                        <?= anchor(route_by_alias('activate_template', $template->id), '<i class="bi bi-toggle-off"></i>'); ?>
                                    <?php else : ?>
                                        <?= anchor(route_by_alias('deactivate_template', $template->id), '<i class="bi bi-toggle-on"></i>'); ?>
                                    <?php endif; ?>
                                    <?php if (! empty($template->resources_count)) : ?>
                                        <?= anchor(route_by_alias('delete_template', $template->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-alert-link', 'data-alert-link-text' => "There are $template->resources_count resources with the template \"$template->title\". To delete a template uninstall this template from all resources assigned it."]); ?>
                                    <?php else : ?>
                                        <?= anchor(route_by_alias('delete_template', $template->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-confirm-link', 'data-confirm-link-text' => "Are you sure you want to delete template \"$template->title\"?"]); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p>There are no templates.</p>
    <?php endif; ?>
    <div class="mb-3 overflow-hidden">
        <?= anchor(route_by_alias('add_template'), 'Add template', ['class' => 'btn btn-primary float-end']); ?>
    </div>
<?= $this->endSection(); ?>