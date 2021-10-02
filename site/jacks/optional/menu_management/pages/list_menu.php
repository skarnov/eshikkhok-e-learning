<?php
global $multilingualFields;
if($_GET['delete']){
    if(!has_permission('delete_menu')){
        add_notification('You don\'t have enough permission to delete menu.','error');
        header('Location:'.build_url(NULL,array('delete')));
        exit();
        }

	$ret = $this->delete_menu($_GET['delete']);
	if($ret){
		add_notification('Menu has been deleted.','success');
		user_activity::add_activity('Menu (ID: '.$_GET['delete'].') has been deleted','success', 'delete');
		}
	else add_notification('Menu has not been delete, please try again.','error');
	
	header('location:'.$myUrl);
	exit();
	}

$menus = $this->get_menus();
doAction('render_start');
?>
<div class="page-header">
    <h1>All Menus</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'action' => 'add',
                'icon' => 'icon_add',
                'text' => 'New Menu',
                'title' => 'Create New Menu',
                'size' => 'sm',
                'classes' => 'add_menu',
                ));
            ?>
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(array('action' => 'assign_menu')),
                'action' => 'config',
                'icon' => 'icon_config',
                'text' => 'Assign Menu',
                'title' => 'Assign Menus to Menu Positions',
                'size' => 'sm',
            ));
            ?>
        </div>
    </div>
</div>
<div class="table-primary table-responsive">
    <table class="table table-bordered menu_table table-condensed table-hover">
        <thead>
            <tr>
                <th>Menu Title</th>
                <th>Menu Slug</th>
                <th class="tar action_column">...</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if($menus){
            foreach($menus as $i=>$item){
                ?>
                <tr data-id="<?php echo $item['pk_menu_id']?>">
                    <td class="menu_title"><?php echo $item['menu_title']?></td>
                    <td class="menu_slug"><?php echo $item['menu_slug']?></td>
                    <td class="tar">
                        <?php if(has_permission('edit_menu')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit Menu',
                                'classes' => 'edit_menu',
                                'attributes' => array('data-id' => $item['pk_menu_id'])
                            ));
                            ?>
                        <?php endif; ?>
                        <?php if(has_permission('config_menu')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'href' => build_url(array('action' => 'settings', 'menuID' => $item['pk_menu_id'])),
                                'action' => 'list',
                                'icon' => 'icon_list',
                                'text' => 'Items',
                                'title' => 'Configure Menu Items',
                                ));
                            ?>
                        <?php endif; ?>
                        <?php if(has_permission('delete_menu')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Content',
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $item['pk_menu_id'])))
                                ));
                            ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                }
            }

        ?>
        </tbody>
    </table>
</div>
<div id="add_edit_menu" style="display: none;" title="Add New Menu">
    <input type="hidden" id="menu_id" value="" />
    <div class="form-group">
        <label>Menu Title</label>
        <input type="text" id="menu_title" class="form-control" />
    </div>
</div>
<script type="text/javascript">
    var can_edit_menu = <?php  echo has_permission('edit_menu') ? 'true' : 'false'?>;
    var can_config_menu = <?php  echo has_permission('config_menu') ? 'true' : 'false'?>;
    var can_delete_menu = <?php  echo has_permission('delete_menu') ? 'true' : 'false'?>;
    var myUrl = '<?php echo $myUrl?>';
    init.push(function(){
        var menu_table = $('.menu_table');
        var the_menu_form = $('#add_edit_menu');
        var add_edit_menu_dialog = the_menu_form.dialog({
            autoOpen: false,
            modal: true,
            show: { effect: "blind", duration: 500 },
            hide: { effect: "blind", duration: 500 },
            buttons:{
                'Cancel' : {
                    'text' : 'Cancel',
                    'click': function(){
                        add_edit_menu_dialog.dialog('close');
                        }
                    },
                'Save': {
                    'text' : 'Save',
                    'click' : function(){
                        var menu_title = the_menu_form.find('#menu_title').val();
                        var menu_id = the_menu_form.find('#menu_id').val();

                        var data = {
                            'menu_title' : menu_title,
                            'internalToken' : _internalToken_
                            };

                        if(menu_id) data['menu_id'] = menu_id;

                        $.ajax({
                            beforeSend: show_working('Adding Menu ...'),
                            complete: hide_working(),
                            type: "POST",
                            url: '<?php echo url('api/dev_menu_management/add_edit_menu') ?>',
                            data: data,
                            cache: false,
                            dataType : 'json',
                            success: function(reply_data){
                                var html = '';
                                if(reply_data['error']){
                                    for(i in reply_data['error']){
                                        html += '<p>'+reply_data['error'][i]+'</p>';
                                        }

                                    modern_alert('Errors',html,'error',null);
                                    }
                                else{
                                    html = 'Menu was saved successfully.';
                                    $.growl.notice({title:'Success',message:'Menu was saved successfully.'});
                                    }
                                if(reply_data['success']){
                                    window.location.reload();return;
                                    if(menu_id){
                                        menu_table.find('tr[data-id="'+menu_id+'"] > .menu_title').html(reply_data['data']['menu_title']);
                                        }
                                    else {
                                        menu_table.append('<tr data-id="'+reply_data['data']['pk_menu_id']+'">\
                                                    <td class="menu_title">' + reply_data['data']['menu_title'] + '</td>\
                                                    <td class="menu_slug">' + reply_data['data']['menu_slug'] + '</td>\
                                                    <td class="tac">\
                                                        ' + (can_edit_menu ? '<a href="javascript:" class="edit_menu btn btn-primary btn-xs" data-id="' + reply_data['data']['pk_menu_id'] + '" title="Edit Menu"><i class="icon fa fa-edit"></i></a>' : '') + '\
                                                        ' + (can_config_menu ? '<a class="btn btn-primary btn-xs" href="' + myUrl + '?action=settings&menuID=' + reply_data['data']['pk_menu_id'] + '"><i class="icon fa fa-cog"></i></a>' : '') + '\
                                                        ' + (can_delete_menu ? '<a href="javascript:" class="confirm_delete btn btn-xs btn-danger" rel="' + myUrl + '?delete=' + reply_data['data']['pk_menu_id'] + '" data-delete_title="' + reply_data['data']['menu_title'] + '" title="Delete ' + reply_data['data']['menu_title'] + '"><i class="icon fa fa-times-circle"></i></a>' : '') + '\
                                                    </td>\
                                                </tr>');
                                        }
                                    }
                                }
                            });
                        add_edit_menu_dialog.dialog('close');
                        }
                    }
                }
            });
        $('.add_menu').click(function(){
            clear_form(the_menu_form);
            add_menu();
            });
        function add_menu(){
            add_edit_menu_dialog.dialog('open');
            }
        $('.edit_menu').click(function(){
            var theMenu = $(this);
            var theMenuId = theMenu.closest('tr').attr('data-id');
            var theTitle = theMenu.closest('tr').find('.menu_title').html();
            clear_form(the_menu_form);
            the_menu_form.find('#menu_title').val(theTitle);
            the_menu_form.find('#menu_id').val(theMenuId);
            add_menu();
            });
        });
</script>