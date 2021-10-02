<?php
$pageManager = jack_obj('dev_page_management');

if($_GET['delete']){
    if(!has_permission('delete_page')){
        add_notification('You don\'t have enough permission to delete this category.','error');
        header('Location:'.build_url(NULL,array('delete')));
        exit();
        }

	$ret = $this->delete_pages($_GET['delete']);
	if($ret){
		add_notification('Category(s) has been deleted.','success');
		user_activity::add_activity('Category '.$_GET['delete'].' has been deleted','success','delete');
		}
	else add_notification('Category has not been delete, please try again.','error');

    header('Location:'.build_url(NULL,array('delete')));
	exit();
	}
$addNew = url('admin/dev_page_management/manage_pages?action=add_edit_page&content_category=yes');
function editOldLink($id){
    return url('admin/dev_page_management/manage_pages?action=add_edit_page&content_category=yes&edit='.$id);
    }
function deleteOldLink($id){
    return url('admin/dev_page_management/manage_pages?action=add_edit_page&content_category=yes&delete='.$id);
    }

doAction('render_start');
?>
<div class="page-header">
    <h1>All Content Categories</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('add_page')):?>
            <a href="<?php echo $addNew?>" class="btn btn-flat btn-labeled" title="">
                <span class="btn-label icon fa fa-plus-circle"></span>
                Add New Category
            </a>
            <?php endif;?>
        </div>
    </div>
</div>
<div class="table-primary table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Title</th>
            <th class="tac vam">Parent Category</th>
            <!--th class="tac vam">Type</th-->
            <th class="tac vam">Status</th>
            <th class="tac vam">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $categoryPages = $pageManager->get_all_pages(array('page_category' => 'yes', 'data_only' => true));

        foreach($categoryPages['data'] as $i=>$page){
            $parentPage = null;
            if($page['parent_page_id']) $parentPage = $pageManager->get_a_page($page['parent_page_id'], array('tiny' => true));
            ?>
            <tr>
                <td>
                    <h5><?php echo $page['page_title']?></h5>
                    <p class="help-block"><?php echo $page['page_slug']?></p>
                </td>
                <td class="tac vam"><?php echo $parentPage ? $parentPage['page_title'] : 'N/A'?></td>
                <!--td class="tac vam"><?php echo $page['page_type']?></td-->
                <td class="tac vam"><?php echo $page['page_status']?></td>
                <td class="tac vam">
                    <?php if($page['is_locked'] == 'no'):?>
                        <?php if(has_permission('edit_page')):?>
                        <a class="btn btn-xs btn-primary" href="<?php echo editOldLink($page['pk_page_id'])?>"><i class="icon fa fa-edit"></i></a>
                        <?php endif; ?>
                        <a target="_blank" class="btn btn-xs btn-primary" href="<?php echo url($page['page_slug']); ?>"><i class="fa fa-eye"></i></a>
                        <?php if(has_permission('delete_page')):?>
                        <a href="javascript:" class="confirm_delete btn-xs btn btn-danger" rel="<?php echo deleteOldLink($page['pk_page_id'])?>" data-delete_title="<?php echo $page['page_title']?>" title="Delete <?php echo $page['page_title']?>"><i class="icon fa fa-times-circle"></i></a>
                        <?php endif?>
                    <?php endif;?>
                </td>
            </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
</div>