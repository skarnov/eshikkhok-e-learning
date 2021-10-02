<?php
global $multilingualFields;
$pageManager = jack_obj('dev_page_management');

$thisMenu = $_GET['menuID'];

if($_POST['ajax_type'] == 'add_menu_item'){
	$data = array(
		'fk_item_id' => 0,
		'item_sort_order' => 0,
		'fk_page_id' => $_POST['the_page'] ? $_POST['the_page'] : 0,
		'fk_menu_id' => $_POST['menu_id'],
		//'optional_item' => $_POST['optional_item'] ? $_POST['optional_item'] : null,
		'item_title' => $_POST['item_title'],
		'item_ext_url' => $_POST['item_ext_url'] ? $_POST['item_ext_url'] : 'javascript:',
		'use_page_title' => $_POST['use_page_title'] ? 1 : 0,
		'item_css_class' => $_POST['item_css_class'],
        'item_icon_class' => $_POST['item_icon_class'],
		'created_at' => date('Y-m-d H:i:s'),
		'created_by' => $_config['user']['pk_user_id'],
		'modified_at' => date('Y-m-d H:i:s'),
		'modified_by' => $_config['user']['pk_user_id'],
		);

    if($multilingualFields['dev_menu_items']){
        foreach($multilingualFields['dev_menu_items'] as $i=>$v){
            if(isset($data[$v])) $data[$v] = processToStore('',$data[$v]);
            }
        }

	$insert = $devdb->insert_update('dev_menu_items',$data);

    if($insert['success']){
        user_activity::add_activity('Menu Item (ID: '.$insert['success'].') has been added to menu (ID: '.$_POST['menu_id'].').', 'success', 'create');
        $data['pk_item_id'] = $insert['success'];
        $return_data = array();
        $return_data['menu_item_id'] = $insert['success'];
        if($data['fk_page_id']){
            $the_page = $pageManager->get_a_page($data['fk_page_id'],array('tiny' => true));
            $return_data['item_title'] = $the_page['page_title'];
            if(!$data['use_page_title']) $return_data['item_title'] = $data['item_title'];
            }
        else $return_data['item_title'] = $data['item_title'];

        $return_data['item_title'] = processToRender($return_data['item_title']);
        $return_data['the_item_form'] = $this->menu_item_edit_form($data);
        $return_data['data'] = $data;
        $this->reCacheEachMenuItems($thisMenu);
        echo json_encode(array('success' => $return_data));
        exit();
        }
    else{
        echo json_encode($insert);
	    exit();
        }
	}
if($_POST['ajax_type'] == 'edit_menu_item'){
    if(!isset($_POST['menu_item_id'])){
        echo json_encode(array('error' => 'Invalid Menu Item'));
        exit();
        }

    $sql = "SELECT * FROM dev_menu_items WHERE pk_item_id = '".$_POST['menu_item_id']."'";
    $preData = $devdb->get_row($sql);

    $data = array(
        'fk_page_id' => $_POST['the_page'] ? $_POST['the_page'] : 0,
        'fk_menu_id' => $_GET['menuID'],
        'item_title' => $_POST['item_title'],
        'item_ext_url' => $_POST['item_ext_url'] ? $_POST['item_ext_url'] : 'javascript:',
        'use_page_title' => $_POST['use_page_title'] ? 1 : 0,
        'item_css_class' => $_POST['item_css_class'],
        'item_icon_class' => $_POST['item_icon_class'],
        'modified_at' => date('Y-m-d H:i:s'),
        'modified_by' => $_config['user']['pk_user_id'],
        );

    if ($multilingualFields['dev_menu_items']) {
        foreach ($multilingualFields['dev_menu_items'] as $i => $v) {
            if (isset($data[$v])) $data[$v] = processToStore($preData[$v], $data[$v]);
            }
        }

    $insert = $devdb->insert_update('dev_menu_items',$data," pk_item_id = '".$_POST['menu_item_id']."'");

    if($insert['success']){
        user_activity::add_activity('Menu Item (ID: '.$_POST['menu_item_id'].') has been updated.', 'success', 'update');
        $data['pk_item_id'] = $_POST['menu_item_id'];
        $return_data = array();
        $return_data['menu_item_id'] = $_POST['menu_item_id'];
        if($data['fk_page_id']){
            $the_page = $pageManager->get_a_page($data['fk_page_id'],array('tiny' => true));
            $return_data['item_title'] = $the_page['page_title'];
            if(!$data['use_page_title']) $return_data['item_title'] = $data['item_title'];
            }
        else $return_data['item_title'] = $_POST['item_title'];

        $return_data['item_title'] = processToRender($return_data['item_title']);
        $return_data['the_item_form'] = $this->menu_item_edit_form($data);

        $this->reCacheEachMenuItems($thisMenu);
        echo json_encode(array('success' => $return_data));
        exit();
        }
    else{
        echo json_encode($insert);
        exit();
    }
    exit();
    }
if($_POST['ajax_type'] == 'save_sorting'){
	$data = $_POST['data'];
	unset($data[0]);
	
	foreach($data as $i=>$v){
		$update = array(
			'fk_item_id' => $v['parent_id'] == 'root' ? '0' : $v['parent_id'],
			'item_sort_order' => $i,
			);
		$update = $devdb->insert_update('dev_menu_items',$update," pk_item_id = '".$v['item_id']."'");
		}
    user_activity::add_activity('Items of Menu (ID: '.$thisMenu.') has been sorted.', 'success', 'update');
    $this->reCacheEachMenuItems($thisMenu);
	$ret = array('success' => 1);
	echo json_encode($ret);
	exit();
	}
if($_POST['ajax_type'] == 'remove_menu_item'){
    if($_POST['item_id']){
        $deleted = $this->deleteMenuItems(array('menu_item' => $_POST['item_id']));
        if($deleted){
            user_activity::add_activity('Menu Item (ID: '.$_POST['item_id'].') has been deleted.', 'success', 'delete');
            echo json_encode(array('success' => 'Item Deleted'));
            }
        else echo json_encode(array('error' => 'Item not found'));
        $this->reCacheEachMenuItems($thisMenu);
        }
    else echo json_encode(array('error' => 'No Item to Remove'));
    exit();
    }
//--------

$the_menu = $this->get_menus($_GET['menuID']);
$menuItems = $this->get_menuItems($_GET['menuID']);

doAction('render_start');
?>
<div class="page-header">
    <h1>Personalise Menu: <strong><?php echo $the_menu['menu_title']?></strong></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => $myUrl,
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Menus',
                'title' => 'All Menus',
                'size' => 'sm',
                ));
            ?>
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(array('action' => 'assign_menu')),
                'action' => 'config',
                'icon' => 'icon_config',
                'text' => 'Assign Menu',
                'title' => 'Assign Menu',
                'size' => 'sm',
                ));
            ?>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="col-sm-4">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#addPage" data-toggle="tab">Page</a></li>
            <li><a href="#addLink" data-toggle="tab">Link/Text</a></li>
        </ul>
        <div class="tab-content tab-content-bordered panel">
            <div class="tab-pane active" id="addPage">
                <form id="menu_item_add_form" action="" class="oh" method="post">
                    <input type="hidden" name="menu_id" value="<?php echo $_GET['menuID']?>" />
                    <div class="form-group">
                        <label>Select A Page</label>
                        <select class="form-control" id="menu_item" name="the_page">
                            <option value="">Select One</option>
                            <?php
                            echo getPageSelectOptions();
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="checkbox">
                            <input class="px use_page_title" type="checkbox" name="use_page_title" value="1" checked />
                            <span class="lbl">Use Page Title as Item Label</span>
                        </label>
                    </div>
                    <div class="form-group custom_item_label" style="display: none">
                        <label>Label for Item</label>
                        <input type="text" name="item_title" class="form-control" value="" />
                    </div>
                    <div class="form-group dn ">
                        <label>Menu Item CSS Class</label>
                        <input type="text" name="item_css_class" class="form-control" value="" />
                        <p class="help-block">Leave blank if you don't understand.</p>
                    </div>
                    <div class="form-group dn">
                        <label>Menu Item Icon CSS Class</label>
                        <input type="text" name="item_icon_class" class="form-control" value="" />
                        <p class="help-block">Leave blank if you don't understand.</p>
                    </div>
                    <div class="tar">
                    <?php
                    echo buttonButtonGenerator(array(
                        'action' => 'add',
                        'icon' => 'icon_add',
                        'text' => 'Add to Menu',
                        'title' => 'Add Item to Menu',
                        'classes' => 'addMenuItem',
                        'size' => '',
                        ));
                    ?>
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="addLink">
                <form id="menu_link_item_add_form" action="" class="oh" method="post">
                    <input type="hidden" name="menu_id" value="<?php echo $_GET['menuID']?>" />
                    <?php
                    if($_config['optional_menu_links']){
                        ?>
                        <div class="form-group">
                            <label>Pre-defined Links</label>
                            <select name="optional_item" class="optional_links form-control">
                                <option value="">Select One</option>
                                <?php
                                foreach($_config['optional_menu_links'] as $i=>$v){
                                    ?>
                                    <option value="<?php echo $i ?>"><?php echo $v['label'] ?></option>
                                    <?php
                                    }
                                ?>
                            </select>
                        </div>
                        <?php
                        }
                    ?>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="item_title" />
                    </div>
                    <div class="form-group">
                        <label>Link</label>
                        <input type="text" class="form-control" name="item_ext_url" />
                        <p class="help-block">Leave blank to use no link</p>
                    </div>
                    <div class="tar">
                        <?php
                        echo buttonButtonGenerator(array(
                            'action' => 'add',
                            'icon' => 'icon_add',
                            'text' => 'Add to Menu',
                            'title' => 'Add Item to Menu',
                            'classes' => 'addMenuItem',
                            'size' => '',
                        ));
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-heading">
                <span class="panel-title">Sort, Arrange &amp; Save Menu</span>
            </div>
            <div class="panel-body">
                <div class="menuItemList">
                    <form action="" method="post">
                        <?php echo $menuItems;?>
                    </form>
                    <div class="tar mt20">
                        <?php
                        echo buttonButtonGenerator(array(
                            'action' => 'save',
                            'icon' => 'icon_save',
                            'text' => 'Save Menu Items',
                            'title' => 'Save Menu Items',
                            'classes' => '',
                            'size' => '',
                            'id' => 'SaveMenuItems'
                        ));
                        ?>
                    </div>
                    <script type="text/javascript">
                        var process_page = '<?php echo $_SERVER['REQUEST_URI']?>';
                        init.push(function () {
                            function saveMenuSorting(showNoti){
                                arraied = $('ul.sortable').nestedSortable('toArray', {startDepthCount: 0});
                                var _data = {
                                    'ajax_type' : 'save_sorting',
                                    'data' : arraied
                                    };
                                $.ajax({
                                    beforeSend: function(){},
                                    complete: function(){},
                                    type: "POST",
                                    url: process_page,
                                    data: _data,
                                    cache: false,
                                    dataType: 'json',
                                    success: function(reply_data){
                                        if(reply_data.success){
                                            if(showNoti) $.growl.warning({ title: "Success", message: "Menu Order was saved successfully", size: 'large' });
                                            }
                                        else
                                            $.growl.error({ title: "Error", message: "Menu Order was not saved successfully", size: 'large' });
                                        }
                                    });
                                }

                            $('ul.sortable').nestedSortable({
                                forcePlaceholderSize: true,
                                handle: '.sortHandle',
                                helper:	'clone',
                                items: 'li',
                                opacity: .6,
                                placeholder: 'placeholder',
                                revert: 250,
                                tabSize: 25,
                                tolerance: 'pointer',
                                toleranceElement: '> div',
                                maxLevels: 3,
                                isTree: true,
                                startCollapsed: true,
                                update: function(){saveMenuSorting(true);}
                                });

                            $(document).on('click','.show_item_detail',function(){
                                if($(this).hasClass('open')){
                                    $(this).closest('li').find('>.menu_edit_form').slideUp('slow');
                                    $(this).removeClass('open');
                                    }
                                else{
                                    $(this).closest('li').find('>.menu_edit_form').slideDown('slow');
                                    $(this).addClass('open');
                                    }
                                });
                            $(document).on('click','.addMenuItem',function(){
                                var ths = $(this);
                                var serialized_data = ths.closest('form').serialize();//$('#menu_item_add_form').serialize();

                                $.ajax({
                                    beforeSend: function(){
                                        show_button_overlay_working(ths);
                                        },
                                    complete:  function(){
                                        hide_button_overlay_working(ths);
                                        },
                                    type: "POST",
                                    url: process_page,
                                    data: 'ajax_type=add_menu_item&'+serialized_data,
                                    cache: false,
                                    dataType : 'json',
                                    success: function(reply_data){
                                        if(reply_data.success){
                                            $.growl.warning({ title: "Success", message: "Menu Item is Added", size: 'large' });
                                            var newItem = $('<li />',{
                                                'id' : 'ID_'+reply_data.success.menu_item_id,
                                                'html' : '<div class="item"><span class="sortHandle"><i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-ellipsis-v"></i></span>&nbsp;&nbsp;<span class="title">' + reply_data.success.item_title + '</span><span class="pull-right"><a href="javascript:" class="btn btn-xs btn-primary mr5 show_item_detail"><i class="fa fa-edit"></i></a><a href="javascript:" class="remove_menu_item btn btn-xs btn-danger"><i class="icon fa fa-times-circle"></i></a></span></div>'+reply_data.success.the_item_form
                                                });
                                            newItem.appendTo("ul.sortable");
                                            newItem.find('.use_page_title').change();
                                            saveMenuSorting(false);
                                            //clear_form(ths.closest('form').closest('form'));
                                            }
                                        else $.growl.error({ title: "Error", message: "Menu Item was not Added, please try again.", size: 'large' });
                                        }
                                    });
                                });

                            $('#SaveMenuItems').click(function(e){
                                saveMenuSorting();
                                });

                            $(document).on('click','.edit_menu_item',function(){
                                var ths = $(this);
                                var serialized_data = ths.closest('form').serialize();
                                var ths_li = ths.closest('li');

                                $.ajax({
                                    beforeSend:function(){
                                        show_button_overlay_working(ths);
                                        },
                                    complete: function(){
                                        hide_button_overlay_working(ths);
                                        },
                                    type: "POST",
                                    url: process_page,
                                    data: 'ajax_type=edit_menu_item&'+serialized_data,
                                    cache: false,
                                    dataType : 'json',
                                    success: function(reply_data){
                                        if(reply_data.success){
                                            $.growl.warning({ title: "Success", message: "Menu Item is Updated", size: 'large' });
                                            ths_li.find('>.item >.title').html(reply_data.success.item_title);
                                            ths_li.find('>.menu_edit_form').remove();
                                            ths_li.find('>.item').after(reply_data.success.the_item_form);
                                            ths_li.find('.use_page_title').change();
                                            }
                                        else $.growl.error({ title: "Error", message: "Menu Item was not updated, please try again.", size: 'large' });
                                        }
                                    });
                                });
                            $(document).on('click','.remove_menu_item',function(e){
                                var ths = $(this);
                                bootbox.confirm({
                                    message: '<span class="text-danger">Do you really want to delete this menu item?</span><br /><br />All child menu items of this menu item will be deleted.',
                                    buttons: {
                                        confirm: {
                                            label: 'Delete',
                                            className: 'btn-success'
                                            },
                                        cancel: {
                                            label: 'Cancel',
                                            className: 'btn-danger'
                                            }
                                        },
                                    callback: function(result) {
                                        if(result){
                                            var item_id = $(ths).closest('li').attr('id').replace('ID_','');

                                            var _data = {
                                                'ajax_type' : 'remove_menu_item',
                                                'item_id' : item_id
                                                };
                                            $.ajax({
                                                beforeSend: show_working('Removing Menu Item ...'),
                                                complete: hide_working(),
                                                type: "POST",
                                                url: process_page,
                                                data: _data,
                                                cache: false,
                                                dataType: 'json',
                                                success: function(reply_data){
                                                    if(reply_data.success){
                                                        $.growl.warning({ title: "Success", message: "Menu Item is Removed", size: 'large' });
                                                        $(ths).closest('li').slideUp(300,function(){
                                                            $(ths).closest('li').remove();
                                                            });
                                                        saveMenuSorting();
                                                        }
                                                    else
                                                        $.growl.error({ title: "Error", message: "Menu Item Not Removed.<br />Please try again", size: 'large' });
                                                    }
                                                });
                                            }
                                        },
                                    className: "bootbox-sm"
                                    });
                                });
                            });
                    </script>
                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    init.push(function(){
        $(document).on('change','.use_page_title',function(){
            if(!$(this).is(':checked')) $(this).closest('form').find('.custom_item_label').slideDown();
            else $(this).closest('form').find('.custom_item_label').slideUp();
            });
        $('.use_page_title').each(function(i,e){$(e).change()});

        var optional_links = <?php echo $_config['optional_menu_links'] ? json_encode($_config['optional_menu_links']) : '{}' ?>;

        $(document).off('change','.optional_links').on('change','.optional_links', function(){
            var ths = $(this);
            var form_ = ths.closest('form');
            if(ths.val()){
                form_.find('[name="item_title"]').val(optional_links[ths.val()]['label']);
                form_.find('[name="item_ext_url"]').val(optional_links[ths.val()]['link']);
                }
            });
        });
</script>