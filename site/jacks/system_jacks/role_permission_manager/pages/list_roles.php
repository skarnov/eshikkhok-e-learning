<?php
$per_page_items = 20;
$start = $_GET['start'] ? $_GET['start'] : 0;

if(!strlen($_GET['order_by'])){
    $_GET['order_by'] = 'role_name';
    $_GET['order'] = 'ASC';
    }

$args = array(
	'limit' => array(
		'start' => $start*$per_page_items,
		'count' => $per_page_items,
		),
	);

if($_GET['order_by']) $args['order_by'] = array('col' => $_GET['order_by'], 'order' => ($_GET['order'] ? $_GET['order'] : 'ASC'));

$roles = $this->get_roles($args);
$pagination = pagination($roles['total'], $per_page_items, $start);

doAction('render_start');
?>
<div class="page-header">
    <h1>User Roles</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo buttonButtonGenerator(array(
                'action' => 'add',
                'icon' => 'icon_add',
                'text' => 'New Role',
                'title' => 'Create New Role',
                'size' => 'sm',
                'classes' => 'add_edit_role'
                ));
            echo linkButtonGenerator(array(
                'href' => build_url(array('action' => 'assign_role'), array('order_by','order')),
                'action' => 'add',
                'icon' => 'icon_dot',
                'text' => 'Assign Roles',
                'title' => 'Assign Roles to Users',
                'size' => 'sm',
                ));
            ?>
        </div>
    </div>
</div>
<div class="table-primary table-responsive">
    <div class="table-header">
        <?php echo searchResultText($roles['total'],$start,$per_page_items,$roles['result_total'],'roles')?>
    </div>
    <table class="role-table table-hover table-striped table table-bordered table-condensed">
        <thead>
        <tr>
            <th><?php echo get_sortable_column_link('Role', 'role_name', 'alpha'); ?></th>
            <th>Role Description</th>
            <th class="tar">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($roles['data'] as $i=>$item){
        ?>
            <tr>
                <td><?php echo $item['role_name']?></td>
                <td><?php echo $item['role_description']?></td>
                <td class="tar action_column">
                    <?php
                    echo buttonButtonGenerator(array(
                        'action' => 'edit',
                        'icon' => 'icon_edit',
                        'text' => 'Edit',
                        'title' => 'Edit Role',
                        'classes' => 'add_edit_role',
                        'attributes' => array('data-id' => $item['pk_role_id'])
                    ));
                    ?>
                    <?php
                    echo linkButtonGenerator(array(
                        'href' => build_url(array('action' => 'config_role', 'edit' => $item['pk_role_id'])),
                        'action' => 'config',
                        'icon' => 'icon_config',
                        'text' => 'Config',
                        'title' => 'Set role permissions',
                        ));
                    ?>
                </td>
            </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
    <div class="table-footer oh">
        <?php echo $pagination ? $pagination : ''?>
    </div>
</div>
<div class="dn">
    <div id="ajax_form_container"></div>
</div>
<script type="text/javascript">
    var form_api = _root_path_+'/api/dev_role_permission_management/get_role_form';
    var form_submit_api = _root_path_+'/api/dev_role_permission_management/set_role_form';

    init.push(function(){
        $(document).on('click', '.add_edit_role', function(){
            var ths = $(this);
            var data_id = ths.attr('data-id');
            var is_update = typeof data_id !== 'undefined' ? data_id : false;
            var thsRow = is_update ? ths.closest('tr') : null;

            new in_page_add_event({
                edit_form_success_callback: function(){
                    initCharLimit();
                    },
                edit_mode: true,
                edit_form_url: form_api,
                submit_button: is_update ? 'UPDATE' : 'ADD',
                form_title: is_update ? 'Update Role' : 'Add New Role',
                form_container: $('#ajax_form_container'),
                ths: ths,
                url: form_submit_api,
                additional_data : is_update ? {role_id: data_id} : {},
                callback: function(data){
                    var updattedRow = '\
                        <tr>\
                            <td>'+data.role_name+'</td>\
                            <td>'+data.role_description+'</td>\
                            <td class="tar action_column">\
                                <button class="add_edit_role btn btn-xs btn-info btn-flat btn-labeled" data-id="'+data.pk_role_id+'"><i class="btn-label icon fa fa-edit"></i>Edit</button>\
                                <button class="linked_button btn btn-xs btn-primary btn-flat btn-labeled" data-link="'+build_url({action: 'config_role', 'edit' : data.pk_role_id})+'"><i class="btn-label icon fa fa-cogs"></i>Config</button>\
                            </td>\
                        </tr>\
                        ';
                    if(is_update) thsRow.replaceWith(updattedRow);
                    else $('.role-table tbody').append(updattedRow);
                    }
                });
            });
        });
</script>