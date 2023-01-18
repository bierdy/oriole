class App
{
    modules = [];
    
    elements = {
        'body': '#body',
        'header': '.header',
        'footer': '.footer',
        'left_sidebar': '.left-sidebar',
        'resizer': '.resizer',
        'modal_alert_link': '.modal-alert-link',
        'modal_confirm_link': '.modal-confirm-link',
        'ck_editor': '.ck-editor',
        'resources_tree': '.resources-tree',
        'form_template_variables': '.form-template-variables',
    };
    
    config = {
        'app': {},
        'cookie': {},
    };
    
    constructor(modules)
    {
        this.modules = modules;
    }
    
    init()
    {
        this.modules.forEach(function(module) {
            if (module in app)
                app[module]();
            else
                console.log('There is no method ' + module + ' in object app');
        });
    }
    
    nativeModalEvents()
    {
        $(app.elements.modal_alert_link).on('click', function() {
            alert($(this).data('alertLinkText'));
            
            return false;
        });
        
        $(app.elements.modal_confirm_link).on('click', function() {
            let response = confirm($(this).data('confirmLinkText')),
                dropdown_menu = $(this).closest('.dropdown-menu');
            
            if (dropdown_menu.length)
                dropdown_menu.click();
            
            return response;
        });
    }
    
    ckEditor()
    {
        let editors = document.querySelectorAll(app.elements.ck_editor);
        for(let i = 0; i < editors.length; ++i)
        {
            ClassicEditor
                .create(editors[i], {
                    toolbar:
                        [
                            "heading",
                            '|',
                            "bold",
                            "italic",
                            "link",
                            "bulletedList",
                            "numberedList",
                            '|',
                            "indent",
                            "outdent",
                            '|',
                            //"imageUpload",
                            "blockQuote",
                            "insertTable",
                            //"mediaEmbed",
                            "undo",
                            "redo",
                            //'|',
                            //"selectAll",
                            //"ckfinder",
                            //"imageTextAlternative",
                            //"imageStyle:full",
                            //"imageStyle:side",
                            //"tableColumn",
                            //"tableRow",
                            //"mergeTableCells",
                        ],
                    /*
                    ckfinder: {
                        uploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json'
                    },
                    */
                    language: 'en',
                })
                /*
                .then(editor => {
                    console.log(Array.from(editor.ui.componentFactory.names()));
                })
                */
                .catch(error => {
                    console.log(error);
                });
        }
    }
    
    formTemplateVariables()
    {
        let form_template_variables = document.querySelector(app.elements.form_template_variables);
        
        if (! form_template_variables)
            return;
        
        form_template_variables.addEventListener('dragstart', (e) => {
            if (! e.target || e.target.getAttribute('draggable') !== 'true')
            {
                e.preventDefault();
                return;
            }
        
            e.target.classList.add('selected');
        });
    
        form_template_variables.addEventListener('dragend', (e) => {
            if (! e.target)
                return;
        
            e.target.classList.remove('selected');
            
            form_template_variables.querySelectorAll('.card-placeholder, .card-body-placeholder').forEach((element) => {
                element.classList.remove('is-insertable');
            });
        });
    
        form_template_variables.addEventListener('dragover', (e) => {
            e.preventDefault();
        
            let selected_item = form_template_variables.querySelector('.selected');
    
            if (! selected_item)
                return;
            
            let selected_previous_placeholder = selected_item.previousElementSibling;
            let selected_next_placeholder = selected_item.nextElementSibling;
            let current_placeholder = e.target;
            let is_movable = false;
            
            if (current_placeholder === selected_previous_placeholder || current_placeholder === selected_next_placeholder)
                is_movable = false;
            else if (selected_item.classList.contains('card'))
                is_movable = current_placeholder.classList.contains('card-placeholder');
            else if (selected_item.classList.contains('card-item'))
                is_movable = current_placeholder.classList.contains('card-body-placeholder');
        
            if (! is_movable)
                return;
    
            current_placeholder.classList.add('is-insertable');
        });
    
        form_template_variables.addEventListener('dragleave', (e) => {
            if (! e.target)
                return;
        
            e.target.classList.remove('is-insertable');
        });
        
        form_template_variables.addEventListener('drop', (e) => {
            if (! e.target)
                return;
        
            let selected_item = form_template_variables.querySelector('.selected');
    
            if (! selected_item)
                return;
            
            let selected_previous_placeholder = selected_item.previousElementSibling;
            let selected_next_placeholder = selected_item.nextElementSibling;
            let current_placeholder = e.target;
            let current_parent = current_placeholder.parentElement;
            let is_droppable = current_placeholder.classList.contains('is-insertable');
            
            if (! is_droppable)
                return;
            
            current_parent.insertBefore(selected_next_placeholder, current_placeholder.nextSibling);
            current_parent.insertBefore(selected_item, current_placeholder.nextSibling);
            
            $(form_template_variables).find('.card.active-variables').each(function(index, card) {
                $(card).children('.card-order').val(index);
    
                $(card).find('.card-item').each(function(index, card_item) {
                    $(card_item).children('.card-item-checked').val(1);
                    $(card_item).children('.card-item-order').val(index);
                    $(card_item).children('.card-item-variable-group-id').val($(card).children('.card-id').val());
                });
            });
    
            $(form_template_variables).find('.card.hidden-variables .card-item').each(function(index, card_item) {
                $(card_item).children('.card-item-checked').val(1);
                $(card_item).children('.card-item-order').val('');
                $(card_item).children('.card-item-variable-group-id').val('');
            });
    
            $(form_template_variables).find('.card.available-variables .card-item').each(function(index, card_item) {
                $(card_item).children('.card-item-checked').val('');
                $(card_item).children('.card-item-order').val('');
                $(card_item).children('.card-item-variable-group-id').val('');
            });
        });
    }
    
    resourcesTree()
    {
        let resources_tree = $(app.elements.resources_tree);
        
        if (! resources_tree.length)
            return;
        
        let branches = resources_tree.find('.branch');
        
        if (! branches.length)
            return;
        
        let cookies_resources_tree = Cookies.get(app.config.cookie.prefix + 'resources_tree');
        
        cookies_resources_tree = cookies_resources_tree ? JSON.parse(cookies_resources_tree) : {'open_branches': []};
        
        let addBranchIdToCookie = function(branch_id)
        {
            branch_id = Number(branch_id);
            
            cookies_resources_tree.open_branches.push(branch_id);
            Cookies.set(
                app.config.cookie.prefix + 'resources_tree',
                JSON.stringify(cookies_resources_tree),
                {
                    domain: app.config.cookie.domain,
                    expires: 365,
                    path: app.config.cookie.path,
                    secure: app.config.cookie.secure,
                    sameSite: app.config.cookie.samesite,
                }
            );
        }
        
        let removeBranchIdFromCookie = function(branch_id)
        {
            branch_id = Number(branch_id);
            
            if (cookies_resources_tree.open_branches.indexOf(branch_id) === -1)
                return;
            
            cookies_resources_tree.open_branches.splice(cookies_resources_tree.open_branches.indexOf(branch_id), 1);
            Cookies.set(
                app.config.cookie.prefix + 'resources_tree',
                JSON.stringify(cookies_resources_tree),
                {
                    domain: app.config.cookie.domain,
                    expires: 365,
                    path: app.config.cookie.path,
                    secure: app.config.cookie.secure,
                    sameSite: app.config.cookie.samesite,
                }
            );
        }
    
        branches.each(function(index, value) {
            let branch = $(value),
                branch_arrow = branch.find('> .content > .main > .arrow'),
                tree = branch.find('.tree:first'),
                toggling = false;
            
            branch_arrow.on('click', function() {
                if (toggling)
                    return false;
    
                tree.toggle({
                    duration: 300,
                    start: () => {
                        toggling = true;
                        
                        if (! branch.hasClass('open'))
                        {
                            branch.addClass('open');
                            addBranchIdToCookie(branch.data('branchId'));
                        }
                        else
                        {
                            branch.removeClass('open');
                            removeBranchIdFromCookie(branch.data('branchId'));
    
                            let child_branches = tree.find('.branch');
    
                            if (child_branches.length)
                                child_branches.each(function(index, value) {
                                    removeBranchIdFromCookie($(value).data('branchId'));
                                });
                        }
                    },
                    complete: () => {
                        toggling = false;
    
                        let child_branches = tree.find('.branch'),
                            child_trees = tree.find('.tree');
    
                        child_branches.removeClass('open');
                        child_trees.removeClass('show');
                        child_trees.css('display', '');
                    },
                });
            });
        });
        
        $(app.elements.resources_tree + ' .dropdown-item').on('click', function(e) {
            e.stopPropagation();
        });
        
        
        
        
        
        let resources_tree_ = document.querySelector(app.elements.resources_tree);
        
        resources_tree_.addEventListener('dragstart', (e) => {
            if (! e.target || e.target.getAttribute('draggable') !== 'true')
            {
                e.preventDefault();
                return;
            }
            
            e.target.classList.add('selected');
        });
        
        resources_tree_.addEventListener('dragend', (e) => {
            if (! e.target)
                return;
            
            e.target.classList.remove('selected');
            resources_tree_.querySelectorAll('.branch').forEach((element) => {
                element.classList.remove('is-insertable-before');
                element.classList.remove('is-insertable-inside');
                element.classList.remove('is-insertable-after');
            });
        });
        
        resources_tree_.addEventListener('dragover', (e) => {
            e.preventDefault();
            
            let selected_element = resources_tree_.querySelector('.selected');
            
            if (! selected_element)
                return;
            
            let selected_branch = selected_element.closest('.branch');
            let current_element = e.target;
            let current_branch = current_element.closest('.branch');
            let is_movable = selected_element !== current_element && current_element.classList.contains('title');
            
            if (! is_movable)
                return;
            
            let current_element_y_hover_position = getCurrentElementYHoverPosition(e.clientY, current_element);
            
            if (selected_branch.contains(current_element))
                return;
            
            if (current_element_y_hover_position === 'top')
            {
                current_branch.classList.add('is-insertable-before');
                current_branch.classList.remove('is-insertable-inside');
                current_branch.classList.remove('is-insertable-after');
            }
            else if (current_element_y_hover_position === 'center')
            {
                current_branch.classList.remove('is-insertable-before');
                current_branch.classList.add('is-insertable-inside');
                current_branch.classList.remove('is-insertable-after');
            }
            else if (current_element_y_hover_position === 'bottom')
            {
                current_branch.classList.remove('is-insertable-before');
                current_branch.classList.remove('is-insertable-inside');
                current_branch.classList.add('is-insertable-after');
            }
        });
        
        resources_tree_.addEventListener('dragleave', (e) => {
            if (! e.target)
                return;
            
            e.target.closest('.branch').classList.remove('is-insertable-before');
            e.target.closest('.branch').classList.remove('is-insertable-inside');
            e.target.closest('.branch').classList.remove('is-insertable-after');
        });
        
        resources_tree_.addEventListener('drop', (e) => {
            if (! e.target)
                return;
            
            let selected_element = resources_tree_.querySelector('.selected');
    
            if (! selected_element)
                return;
            
            let selected_branch = selected_element.closest('.branch');
            let selected_branch_tree = selected_branch.closest('.tree');
            let current_element = e.target;
            let current_branch = current_element.closest('.branch');
            let current_branch_tree = null;
            
            if (current_branch.classList.contains('is-insertable-inside'))
            {
                current_branch_tree = current_branch.querySelector('.tree');
                
                current_branch_tree.append(selected_branch);
                current_branch.classList.add('childs-exist');
                
                handleSelectedBranchTree(selected_branch_tree);
                
                setBranchParent(selected_branch.dataset.branchId, current_branch.dataset.branchId);
                setBranchOrder(selected_branch.dataset.branchId, current_branch_tree.children.length - 1);
            }
            else
            {
                if (current_branch.classList.contains('is-insertable-before'))
                {
                    current_branch_tree = current_branch.closest('.tree');
                    current_branch_tree.insertBefore(selected_branch, current_branch);
                }
                else if (current_branch.classList.contains('is-insertable-after'))
                {
                    if (current_branch.querySelector('.tree').children.length && current_branch.querySelector('.tree').classList.contains('show'))
                    {
                        current_branch_tree = current_branch.querySelector('.tree');
                        
                        current_branch_tree.insertBefore(selected_branch, current_branch_tree.querySelector('.branch'));
                    }
                    else
                    {
                        current_branch_tree = current_branch.closest('.tree');
                        
                        if (current_branch.nextElementSibling !== null)
                            current_branch_tree.insertBefore(selected_branch, current_branch.nextElementSibling);
                        else
                            current_branch_tree.append(selected_branch);
                    }
                }
                
                if (current_branch_tree === null)
                    return;
                
                handleSelectedBranchTree(selected_branch_tree);
                
                if (selected_branch_tree !== current_branch_tree)
                {
                    if (current_branch_tree.closest('.branch') !== null)
                        setBranchParent(selected_branch.dataset.branchId, current_branch_tree.closest('.branch').dataset.branchId);
                    else
                        setBranchParent(selected_branch.dataset.branchId, 0);
                    
                    [].slice.call(current_branch_tree.children).forEach((element, index) => {
                        setBranchOrder(element.dataset.branchId, index);
                    });
                }
            }
            
            current_branch.classList.remove('is-insertable-before');
            current_branch.classList.remove('is-insertable-inside');
            current_branch.classList.remove('is-insertable-after');
        });
        
        const getCurrentElementYHoverPosition = (y_cursor_position, current_element) => {
            let current_element_bounding_client_rect = current_element.getBoundingClientRect();
            let current_element_y_position = current_element_bounding_client_rect.y;
            let current_element_height = current_element_bounding_client_rect.height;
            
            switch(true)
            {
                case y_cursor_position < current_element_y_position + 12 :
                    return 'top';
                case y_cursor_position > current_element_y_position + current_element_height - 12 :
                    return 'bottom';
                default :
                    return 'center';
            }
        };
        
        const setBranchParent = (branch_id, branch_parent_id) => {
            let base_url = app.trimSlashes(app.config.app.currentBaseURL),
                back_root_path = app.trimSlashes(app.config.app.backRootPath);
            
            back_root_path = back_root_path.length ? '/' + back_root_path : '';
            
            $.ajax({
                url: base_url + back_root_path + '/resources/set-parent/' + branch_id + '/' + branch_parent_id,
                method: 'get',
                dataType: 'json',
                success: (data) => {
                
                }
            });
        }
        
        const setBranchOrder = (branch_id, order) => {
            let base_url = app.trimSlashes(app.config.app.currentBaseURL),
                back_root_path = app.trimSlashes(app.config.app.backRootPath);
            
            back_root_path = back_root_path.length ? '/' + back_root_path : '';
            
            $.ajax({
                url: base_url + back_root_path + '/resources/set-order/' + branch_id + '/' + order,
                method: 'get',
                dataType: 'json',
                success: (data) => {
                
                }
            });
        }
        
        const handleSelectedBranchTree = (selected_branch_tree) => {
            if (selected_branch_tree.children.length === 0)
            {
                selected_branch_tree.classList.remove('show');
                selected_branch_tree.closest('.branch').classList.remove('childs-exist', 'open');
                removeBranchIdFromCookie(selected_branch_tree.closest('.branch').dataset.branchId);
            }
            else
            {
                [].slice.call(selected_branch_tree.children).forEach((element, index) => {
                    setBranchOrder(element.dataset.branchId, index);
                });
            }
        }
    }
    
    leftSidebar()
    {
        let left_sidebar = document.querySelector(app.elements.left_sidebar);
        let resizer = document.querySelector(app.elements.resizer);
        let x = 0;
        let w = 0;
        
        if (! left_sidebar || ! resizer)
            return;
    
        const mouseDownHandler = function(e)
        {
            x = e.clientX;
        
            const styles = window.getComputedStyle(left_sidebar);
            w = parseInt(styles.width, 10);
        
            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
    
            resizer.classList.add('resizing');
        };
    
        const mouseMoveHandler = function(e)
        {
            const dx = w + e.clientX - x;
            
            left_sidebar.style.width = dx + 'px';
            Cookies.set(
                app.config.cookie.prefix + 'left_sidebar_width',
                dx,
                {
                    domain: app.config.cookie.domain,
                    expires: 365,
                    path: app.config.cookie.path,
                    secure: app.config.cookie.secure,
                    sameSite: app.config.cookie.samesite,
                }
            );
        };
    
        const mouseUpHandler = function()
        {
            resizer.classList.remove('resizing');
            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);
        };
    
        resizer.addEventListener('mousedown', mouseDownHandler);
    }
    
    initConfig()
    {
        if (window.app_config)
            app.config.app = window.app_config;
        
        if (window.cookie_config)
            app.config.cookie = window.cookie_config;
    }
    
    trimSlashes(string)
    {
        return string.replace(/^\/|\/$/g, '');
    }
}

let app = new App(['initConfig', 'nativeModalEvents', 'resourcesTree', 'formTemplateVariables', 'ckEditor', 'leftSidebar']);
app.init();