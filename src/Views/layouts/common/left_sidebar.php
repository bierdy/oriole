<?= '' /*anchor(route_to('Wagtail\Controllers\Back\Resources::add', 0), 'Add resource', ['class' => 'btn btn-light'])*/; ?>

<?php if (! empty($resources_tree)) : ?>
    <div class="resources-tree mt-3">
        <ul class="tree collapse show" data-tree-id="0" data-tree-level="0">
            <?= '' /*buildResourcesTree($resources_tree)*/; ?>
        </ul>
    </div>
<?php else : ?>
    <p>There are no resources.</p>
<?php endif; ?>