<?php
global $multilingualFields;
$pageManager = jack_obj('dev_page_management');

doAction('list_content_process_on_get', $_GET);

$theContentTypeID = $_GET['content_type'] ? $_GET['content_type'] : null;
$theContentType = $theContentTypeID ? $_config['content_types'][$theContentTypeID] : array();

if(!$theContentType){
    foreach($_config['content_types'] as $i=>$v){
        header('Location: '.build_url(array('content_type' => $i)));
        exit();
        }
    }

$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 20;

$args = array(
	'status' => array('published','draft','pending'),
    'content_types' => $theContentTypeID,
    'ignore_parent' => true,
    'include_meta' => true,
	'order_by' => array(
		'col' => 'dev_contents.created_at',
		'order' => 'DESC'
		),
	'limit' => array(
		'start' => $start*$per_page_items,
		'count' => $per_page_items
		),
	);

if($_GET['content_status']) $args['status'] = array($_GET['content_status']);
if($_GET['page_id']) $args['page_id'] = $_GET['page_id'];
if($_GET['sort_by']) $args['order_by']['col'] = 'dev_contents.'.$_GET['sort_by'];
if($_GET['order']) $args['order_by']['order'] = $_GET['order'];
if($_GET['q']) $args['q'] = $_GET['q'];
if($args['q']){
    $args['full_string'] = true;
    $args['q_only_title'] = true;
    }

$contents = $this->get_contents($args);

$pagination = pagination($contents['total'],$per_page_items,$start);

doAction('render_start');
?>
<div class="page-header">
    <h1>All <?php echo $theContentType ? $theContentType['title'] : 'Contents'; ?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('add_contents')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_contents')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New '.$theContentType['title'],
                    'title' => 'Create New '.$theContentType['title'],
                    'size' => 'sm',
                ));
                ?>
            <?php endif;?>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<div class="form-group col-sm-2">
    <label>Title</label>
    <input type="text" class="form-control" name="q" placeholder="Type title ..." value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<div class="form-group col-sm-2">
    <label>Status</label>
    <select class="form-control" name="content_status">
        <option value="">Any</option>
        <option value="published" <?php echo $_GET['content_status'] == 'published' ? 'selected' : ''?>>Published</option>
        <option value="draft" <?php echo $_GET['content_status'] == 'draft' ? 'selected' : ''?>>Draft</option>
        <!--<option value="pending" <?php /*echo $_GET['content_status'] == 'pending' ? 'selected' : ''*/?>>Pending</option>-->
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Pages</label>
    <select class="form-control" name="page_id">
        <option value="">Any</option>
        <option value="-1" <?php echo isset($_GET['page_id']) && $_GET['page_id'] == '-1' ? 'selected' : ''; ?>>No Page</option>
        <?php
        echo getPageSelectOptions($_GET['page_id'],null,0,null,true);
        ?>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Sort by</label>
    <select class="form-control" name="sort_by">
        <option value="created_at" >Create Time</option>
        <option value="content_published_time" <?php echo $_GET['sort_by'] == 'content_published_time' ? 'selected' : ''; ?>>Published Time</option>
        <option value="modified_at" <?php echo $_GET['sort_by'] == 'modified_at' ? 'selected' : ''; ?>>Last Modified Time</option>
        <option value="content_view_count" <?php echo $_GET['sort_by'] == 'content_view_count' ? 'selected' : ''; ?>>Views</option>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Sort Order</label>
    <select class="form-control" name="order">
        <option value="">Descending</option>
        <option value="ASC" <?php echo $_GET['order'] == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
    </select>
</div>
<?php
$formFilter = ob_get_clean();
filterForm($formFilter, array('content_type'));
?>
<div class="table-primary table-responsive">
    <div class="table-header"><?php echo searchResultText($contents['total'], $start, $per_page_items, count($contents['data']), $theContentType['title']); ?></div>
    <table class="content-table table table-bordered table-condensed table-hover">
        <thead>
        <tr>
            <th class="tac">Status</th>
            <th>Content</th>
            <th>Page</th>
            <th>Last Modified</th>
            <th>Published On</th>
            <th class="tac">Language Status</th>
            <th class="tar">Views</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($contents['data']){
            foreach($contents['data'] as $i=>$item){
                $itemTitle = $item['content_title'];

                if($multilingualFields['dev_contents']){
                    foreach($item as $index=>$value){
                        if(in_array($index, $multilingualFields['dev_contents']) !== false)
                            $item[$index] = processToRender($item[$index]);
                    }
                }
                $thePage = null;
                if($item['fk_page_id']) $thePage = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                ?>
                <tr>
                    <td class="tac vam"><span class="fa fa-circle fa-2x text-<?php echo $item['content_status'] == 'draft' ? 'default' : 'success' ?>"></span></span></td>
                    <td>
                        <?php
                        echo $item['content_title'];
                        ?>
                    </td>
                    <td><?php echo $thePage ? processToRender($thePage['page_title']) : 'N/A'; ?></td>
                    <td class=""><?php echo print_date($item['modified_at'],true)?></td>
                    <td class=""><?php echo print_date($item['content_published_time'],true)?></td>
                    <td class="tac vam">
                        <?php
                        foreach($_config['langs'] as $eachLang){
                            $hasData = strlen(processToRender($itemTitle, $eachLang['id'], true)) ? true : false;
                            ?>
                            <a href="<?php echo has_permission('edit_contents') ? build_url(array('action' => 'add_edit_contents', 'edit' => $item['pk_content_id'], 'change_language' => $eachLang['id'])) : ''; ?>" class="label label-<?php echo $hasData ? 'info' : 'default'; ?>"><?php echo strtoupper($eachLang['id']); ?></a>
                            <?php
                        }
                        ?>
                    </td>
                    <td class="tar"><?php echo $item['content_view_count']?></td>
                    <td class="tar action_column">
                        <?php
                        doAction('list_content_add_button',$item,$myUrl);
                        ?>
                        <?php if(has_permission('edit_contents')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'href' => build_url(array('action' => 'add_edit_contents', 'edit' => $item['pk_content_id'])),
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit Content',
                            ));
                            ?>
                        <?php endif;?>
                        <?php if(has_permission('delete_contents')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Content',
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $item['pk_content_id'])))
                            ));
                            ?>
                        <?php endif;?>
                    </td>
                </tr>
                <?php
            }
            }
        ?>
        </tbody>
    </table>
    <?php
    if($pagination){
        ?>
        <div class="table-footer oh">
            <?php echo $pagination?>
        </div>
        <?php
        }
    ?>
</div>
<div id="theContentCopyForm" style="display: none;" title="Copy Content to Another Page">
    <form name="copyContent" action="" method="get">
        <input name="copy" type="hidden" id="copy" value="" />
        <strong>Content:</strong> <span id="copyContentTitle"></span><br />
        <br />
        <strong>Page:</strong> <span id="copyCurrentPage"></span>
        <hr class="mt10 mb10" />
        <div class="form-group">
            <label>Select a Page</label>
            <select class="form-control" id="page" name="page" required="required">
                <option value="">Select One</option>
                <?php
                echo getPageSelectOptions();
                ?>
            </select>
        </div>
    </form>
</div>
<script type="text/javascript">
    var myUrl = '<?php echo $myUrl?>';
    init.push(function(){
        emptyTableFill($('.content-table'));
        var theContentCopyForm = $('#theContentCopyForm');
        var copyContent_dialog = theContentCopyForm.dialog({
            autoOpen: false,
            width: 500,
            height: 300,
            modal: true,
            show: { effect: "blind", duration: 500 },
            hide: { effect: "blind", duration: 500 },
            buttons:{
                'Cancel' : {
                    'text' : 'Cancel',
                    'click': function(){
                        copyContent_dialog.dialog('close');
                        }
                    },
                'Save': {
                    'text' : 'Copy',
                    'click' : function(){
                        theContentCopyForm.find('form').submit();
                        }
                    }
                }
            });
        $('.copyContent').click(function(){
            theContentCopyForm.find('#copyContentTitle').html('');
            theContentCopyForm.find('#copyCurrentPage').html('');
            clear_form(theContentCopyForm);
            theContentCopyForm.find('#copy').val($(this).attr('data-id'));
            theContentCopyForm.find('#copyContentTitle').html($(this).attr('data-title'));
            theContentCopyForm.find('#copyCurrentPage').html($(this).attr('data-page'));
            copyContent();
            });
        function copyContent(){
            copyContent_dialog.dialog('open');
            }
        });
</script>