<?php
global $multilingualFields;
if($_GET['delete']){
    if(!has_permission('delete_page')){
        add_notification('You don\'t have enough permission to delete page.','error');
        header('Location:'.build_url(NULL,array('delete')));
        exit();
        }

	$ret = $this->delete_pages($_GET['delete']);
	if($ret){
        cleanCache('content');
        cleanCache('page');
        doAction('after_page_deleted');
		add_notification('Page(s) has been deleted.','success');
		user_activity::add_activity('Page (ID: '.$_GET['delete'].') has been deleted','success', 'delete');
		}
	else add_notification('Page(s) has not been delete, please try again.','error');
	
	header('location:'.$myUrl);
	exit();
	}
$allPages = $this->get_all_pages(array('data_only' => true));

doAction('render_start');
?>
<div class="page-header">
    <h1>All Pages</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('add_page')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_page')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Page',
                    'title' => 'Create New Page',
                    'size' => 'sm',
                ));
                ?>
            <?php endif;?>
        </div>
    </div>
</div>
<div class="table-primary table-responsive">
    <table class="table table-bordered table-striped table-hover table-condensed ">
        <thead>
        <tr>
            <th>Page Title</th>
            <th class="vam">Parent Page</th>
            <th class="vam">Page Type</th>
            <th class="vam">Page Status</th>
            <th class="tac">Language Status</th>
            <th class=" tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($allPages['data'] as $i=>$page){
            if($page['page_as_category'] == 'yes') continue;
            $itemTitle = $page['page_title'];

            if($multilingualFields['dev_pages']){
                foreach($page as $index=>$value){
                    if(in_array($index, $multilingualFields['dev_pages']) !== false)
                        $page[$index] = processToRender($page[$index]);
                    }
                }
            $parentPage = null;
            if($page['parent_page_id']) $parentPage = $this->get_a_page($page['parent_page_id'], array('tiny' => true));
            ?>
            <tr>
                <td>
                    <h5><?php echo $page['page_title']?></h5>
                    <p class="help-block"><?php echo $page['page_slug']?></p>
                </td>
                <td class="vam"><?php echo $parentPage ? processToRender($parentPage['page_title']) : 'N/A'?></td>
                <td class="vam"><?php echo $page['page_type']?></td>
                <td class="vam"><?php echo $page['page_status']?></td>
                <td class="tac vam">
                    <?php
                    foreach($_config['langs'] as $eachLang){
                        $hasData = strlen(processToRender($itemTitle, $eachLang['id'], true)) ? true : false;
                        ?>
                        <a href="<?php echo has_permission('edit_page') ? build_url(array('action' => 'add_edit_page', 'edit' => $page['pk_page_id'], 'change_language' => $eachLang['id'])) : ''; ?>" class="label label-<?php echo $hasData ? 'info' : 'default'; ?>"><?php echo strtoupper($eachLang['id']); ?></a>
                        <?php
                    }
                    ?>
                </td>
                <td class="tar action_column">
                    <?php if(has_permission('edit_page')):?>
                        <?php
                        echo linkButtonGenerator(array(
                            'href' => build_url(array('action' => 'add_edit_page', 'edit' => $page['pk_page_id'])),
                            'action' => 'edit',
                            'icon' => 'icon_edit',
                            'text' => 'Edit',
                            'title' => 'Edit Page',
                        ));
                        ?>
                    <?php endif; ?>
                    <?php
                    /*echo linkButtonGenerator(array(
                        'href' => page_url($page),
                        'action' => 'view',
                        'icon' => 'icon_view',
                        'text' => 'View',
                        'title' => 'View Page',
                        ));*/
                    ?>
                    <?php if($page['is_locked'] == 'no'):?>
                        <?php if(!isset($page['system_page']) && has_permission('delete_page')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Page',
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $page['pk_page_id'])))
                            ));
                            ?>
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