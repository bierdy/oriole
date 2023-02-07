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
        <?= form_hidden('id', $template->id ?? 0); ?>
        <div class="mb-3">
            <?= form_label('Title', 'title', ['class' => 'form-label']); ?>
            <?= form_input('title', $post['title'] ?? $template->title ?? '', ['class' => 'form-control' , 'id' => 'title']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Icon', 'icon', ['class' => 'form-label']); ?>
            <?php $icon = $post['icon'] ?? $template->icon ?? ''; ?>
            <?php if (! empty($icon)) : ?>
                <i class="<?= $icon; ?> link-secondary d-block mb-3"></i>
            <?php endif; ?>
            <?= form_input('icon', $post['icon'] ?? $template->icon ?? '', ['class' => 'form-control' , 'id' => 'icon']); ?>
        </div>
        <div class="mb-3">
            <?= form_label('Template handler', 'template_handler', ['class' => 'form-label']); ?>
            <?= form_input('template_handler', $post['template_handler'] ?? $template->template_handler ?? '', ['class' => 'form-control' , 'id' => 'template_handler']); ?>
        </div>
        <div class="form-check mb-3">
            <?= form_hidden('is_unique', 0); ?>
            <?= form_checkbox('is_unique', 1, $post['is_unique'] ?? $template->is_unique ?? 0, ['class' => 'form-check-input mb-1' , 'id' => 'is_unique']); ?>
            <?= form_label('Unique', 'is_unique', ['class' => 'form-label']); ?>
        </div>
        <div class="form-template-variables mb-3">
            <div class="mb-3">
                Variables
            </div>
            <div>
                <?= anchor(route_by_alias('add_variable_group', $template->id), 'Add variable group', ['class' => 'btn btn-outline-primary']); ?>
            </div>
            <div class="mb-3">
                <div class="card-placeholder">
                    <i class="bi bi-caret-right-fill"></i>
                </div>
                <?php if (! empty($template_variable_groups)) { ?>
                    <?php foreach($template_variable_groups as $template_variable_group) { ?>
                        <div class="card active-variables" draggable="true">
                            <?= form_input(['type' => 'hidden', 'name' => "template_variable_groups[$template_variable_group->id][id]", 'value' => $template_variable_group->id, 'class' => 'card-id']); ?>
                            <?= form_input(['type' => 'hidden', 'name' => "template_variable_groups[$template_variable_group->id][sort_order]", 'value' => $template_variable_group->sort_order, 'class' => 'card-order']); ?>
                            <div class="card-header">
                                <?= $template_variable_group->title; ?>
                                <div class="text-end">
                                    <?= anchor(route_by_alias('edit_variable_group', $template_variable_group->id), '<i class="bi bi-pencil"></i>'); ?>
                                    <?= anchor(route_by_alias('delete_variable_group', $template_variable_group->id), '<i class="bi bi-trash link-danger"></i>', ['class' => 'modal-confirm-link', 'data-confirm-link-text' => "Are you sure you want to delete variable group \"$template_variable_group->title\"?" . PHP_EOL . "Resources variables values will not be deleted."]); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-body-placeholder">
                                    <i class="bi bi-caret-right-fill"></i>
                                </div>
                                <?php if (! empty($variable_group_variables)) { ?>
                                    <?php foreach($variable_group_variables as $variable_group_variable) { ?>
                                        <?php if ($variable_group_variable->variable_group_id !== $template_variable_group->id) continue; ?>
                                        <div class="card-item" draggable="true">
                                            <div class="card-item-title"><?= $variables[$variable_group_variable->variable_id]->title; ?></div>
                                            <div class="card-item-name"><?= $variables[$variable_group_variable->variable_id]->alias; ?></div>
                                            <?= form_input(['type' => 'hidden', 'name' => "variables[$variable_group_variable->variable_id][template_variable_id]", 'value' => $template_variables[$variable_group_variable->variable_id]->id ?? 0, 'class' => 'card-item-id']); ?>
                                            <?= form_input(['type' => 'hidden', 'name' => "variables[$variable_group_variable->variable_id][checked]", 'value' => 1, 'class' => 'card-item-checked']); ?>
                                            <?= form_input(['type' => 'hidden', 'name' => "variables[$variable_group_variable->variable_id][sort_order]", 'value' => $variable_group_variable->sort_order, 'class' => 'card-item-order']); ?>
                                            <?= form_input(['type' => 'hidden', 'name' => "variables[$variable_group_variable->variable_id][variable_group_id]", 'value' => $template_variable_group->id, 'class' => 'card-item-variable-group-id']); ?>
                                            <?= form_input(['type' => 'hidden', 'name' => "variables[$variable_group_variable->variable_id][variable_group_id_original]", 'value' => $template_variable_group->id, 'class' => 'card-item-variable-group-id-original']); ?>
                                        </div>
                                        <div class="card-body-placeholder">
                                            <i class="bi bi-caret-right-fill"></i>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-placeholder">
                            <i class="bi bi-caret-right-fill"></i>
                        </div>
                    <?php } ?>
                    <hr class="mt-0">
                <?php } ?>
                <div class="card hidden-variables mb-3">
                    <div class="card-header">
                        Hidden variables
                    </div>
                    <div class="card-body">
                        <div class="card-body-placeholder">
                            <i class="bi bi-caret-right-fill"></i>
                        </div>
                        <?php if (! empty($template_variables)) { ?>
                            <?php foreach($template_variables as $template_variable) { ?>
                                <?php if (in_array($template_variable->variable_id, array_column($variable_group_variables, 'variable_id'))) continue; ?>
                                <div class="card-item" draggable="true">
                                    <div class="card-item-title"><?= $variables[$template_variable->variable_id]->title; ?></div>
                                    <div class="card-item-name"><?= $variables[$template_variable->variable_id]->alias; ?></div>
                                    <?= form_input(['type' => 'hidden', 'name' => "variables[$template_variable->variable_id][template_variable_id]", 'value' => $template_variables[$template_variable->variable_id]->id ?? 0, 'class' => 'card-item-id']); ?>
                                    <?= form_input(['type' => 'hidden', 'name' => "variables[$template_variable->variable_id][checked]", 'value' => 1, 'class' => 'card-item-checked']); ?>
                                    <?= form_input(['type' => 'hidden', 'name' => "variables[$template_variable->variable_id][sort_order]", 'value' => '', 'class' => 'card-item-order']); ?>
                                    <?= form_input(['type' => 'hidden', 'name' => "variables[$template_variable->variable_id][variable_group_id]", 'value' => '', 'class' => 'card-item-variable-group-id']); ?>
                                </div>
                                <div class="card-body-placeholder">
                                    <i class="bi bi-caret-right-fill"></i>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <hr>
                <div class="card available-variables mb-3">
                    <div class="card-header">
                        Available variables
                    </div>
                    <div class="card-body">
                        <div class="card-body-placeholder">
                            <i class="bi bi-caret-right-fill"></i>
                        </div>
                        <?php if (! empty($variables)) { ?>
                                <?php foreach($variables as $variable_id => $variable) { ?>
                                    <?php if (isset($template_variables[$variable->id])) continue; ?>
                                    <div class="card-item" draggable="true">
                                        <div class="card-item-title"><?= $variable->title; ?></div>
                                        <div class="card-item-name"><?= $variable->alias; ?></div>
                                        <?= form_input(['type' => 'hidden', 'name' => "variables[$variable->id][template_variable_id]", 'value' => 0, 'class' => 'card-item-id']); ?>
                                        <?= form_input(['type' => 'hidden', 'name' => "variables[$variable->id][checked]", 'value' => '', 'class' => 'card-item-checked']); ?>
                                        <?= form_input(['type' => 'hidden', 'name' => "variables[$variable->id][sort_order]", 'value' => '', 'class' => 'card-item-order']); ?>
                                        <?= form_input(['type' => 'hidden', 'name' => "variables[$variable->id][variable_group_id]", 'value' => '', 'class' => 'card-item-variable-group-id']); ?>
                                    </div>
                                    <div class="card-body-placeholder">
                                        <i class="bi bi-caret-right-fill"></i>
                                    </div>
                                <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3 overflow-hidden">
            <?= anchor(route_by_alias('templates_list'), 'Back', ['class' => 'btn btn-secondary float-start']); ?>
            <?= form_submit('submit', 'Update', ['class' => 'btn btn-primary float-end']); ?>
        </div>
    <?= form_close(); ?>
<?= $this->endSection(); ?>