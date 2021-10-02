<?php
global $multilingualFields;

$groupFound = false;
$tagGroup = $_GET['tag_group'] ? $_GET['tag_group'] : null;
if($tagGroup){
    $tagGroupDetail = $this->get_tag_group($tagGroup);
    if($tagGroupDetail) $groupFound = true;
    }

if(!$groupFound){
    $allGroups = $this->get_tag_group();
    foreach($allGroups as $i=>$v){
        header('Location: '.build_url(array('tag_group' => $v['pk_tag_group_id'])));
        exit();
        }
    }

if($_GET['delete']){
    $sql = "SELECT tag_title FROM dev_tags WHERE pk_tag_id = '".$_GET['delete']."'";
    $the_tag = $devdb->get_row($sql);
    $sql = "DELETE FROM dev_tags WHERE pk_tag_id = '".$_GET['delete']."'";
    $delete = $devdb->query($sql);

    if($delete){
        $sql = "DELETE FROM dev_content_tag_relation WHERE fk_tag_id = '".$_GET['delete']."'";
        $deleted = $devdb->query($sql);

        add_notification('Tag "'.processToRender($the_tag['tag_title']).'" and all tag-content relation has been deleted.','warning');
        user_activity::add_activity('Tag (ID: '.$_GET['delete'].') has been deleted.','success', 'delete');

        header('location:'.build_url(null, array('delete')));
        exit();
        }
    }
$per_page_items = 10;
$start = $_GET['start'] ? $_GET['start'] : 0;
$args = array(
    'tag_group' => $tagGroup,
	'order_by' => array('col' => 'pk_tag_id', 'order' => 'DESC'),
	'limit' => array(
		'start' => $start*$per_page_items,
		'count' => $per_page_items,
		),
	);
if($tagGroupDetail['is_hierarchical']){
    unset($args['limit']);
    unset($args['order_by']);
    $args['data_only'] = true;
    }

$tags = $this->get_tags($args);

if($tagGroupDetail['is_hierarchical']) $pagination = '';
else $pagination = pagination($tags['total'],$per_page_items,$start);

$tagGroupDetail['tag_group_title'] = dbReadableString($tagGroupDetail['tag_group_title']);
doAction('render_start');
?>
<div class="page-header">
    <h1>All <?php echo $tagGroupDetail['tag_group_title'] ?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                //'href' => build_url(array('action' => 'add_edit_tag')),
                'action' => 'add',
                'icon' => 'icon_add',
                'text' => 'New '.$tagGroupDetail['tag_group_title'],
                'title' => 'Create New '.$tagGroupDetail['tag_group_title'],
                'size' => 'sm',
                'classes' => 'add_edit_item',
                ));
            ?>
        </div>
    </div>
</div>
<div class="table-primary table-responsive">
    <table class="item-table table table-bordered">
        <thead>
        <tr>
            <!--th>Tag ID</th-->
            <th>Title</th>
            <!--th>Tag Slug</th-->
            <!--<th>Description</th>-->
            <!--th>Tag Group</th-->
            <th class="tac">Language Status</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($tagGroupDetail['is_hierarchical']){
            function get_tags_in_hierarchy_row($selected = null, &$tags, $parent = 0, $level = 0){
                global $_config, $tagGroupDetail;
                //TODO: Cache required
                $output = '';
                $paddingLeft = 8;
                for($i=0;$i<$level;$i++){$paddingLeft += 20;}
                foreach($tags as $i=>$v){
                    if($v['fk_tag_id'] == $parent){
                        ?>
                        <tr id="item_id_<?php echo $v['pk_tag_id']?>">
                            <td style="padding-left: <?php echo $paddingLeft; ?>px"><?php echo processToRender($v['tag_title'])?></td>
                            <td class="tac vam">
                                <?php
                                foreach($_config['langs'] as $eachLang){
                                    $hasData = strlen(processToRender($v['tag_title'], $eachLang['id'], true)) ? true : false;
                                    ?>
                                    <span class="label label-<?php echo $hasData ? 'info' : 'default'; ?>"><?php echo strtoupper($eachLang['id']); ?></span>
                                    <?php
                                }
                                ?>
                            </td>
                            <td class="tar action_column">
                                <div class="btn-toolbar">
                                    <?php
                                    echo linkButtonGenerator(array(
                                        //'href' => build_url(array('action' => 'add_edit_tag', 'edit' => $item['pk_tag_id'])),
                                        'action' => 'edit',
                                        'icon' => 'icon_edit',
                                        'text' => 'Edit',
                                        'title' => 'Edit '.$tagGroupDetail['tag_group_title'],
                                        'classes' => 'add_edit_item',
                                        'attributes' => array('data-id' => $v['pk_tag_id']),
                                    ));
                                    echo linkButtonGenerator(array(
                                        'action' => 'remove',
                                        'icon' => 'icon_remove',
                                        'text' => 'Delete',
                                        'title' => 'Delete '.$tagGroupDetail['tag_group_title'],
                                        'classes' => 'confirm_delete',
                                        'attributes' => array('rel' => build_url(array('delete' => $v['pk_tag_id'])))
                                    ));
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                        unset($tags[$i]);
                        $output .= get_tags_in_hierarchy_row($selected, $tags, $v['pk_tag_id'], $level+1);
                        }
                    }
                return $output;
                }
            get_tags_in_hierarchy_row(null, $tags['data']);
            }
        else{
            foreach($tags['data'] as $i=>$item){
                $itemTitle = processToRender($item['tag_title']);
                ?>
                <tr id="item_id_<?php echo $item['pk_tag_id']?>">
                    <!--td><?php echo $item['pk_tag_id']?></td-->
                    <td><?php echo processToRender($item['tag_title'])?></td>
                    <!--td><?php echo $item['tag_slug']?></td-->
                    <!--<td><?php /*echo $item['tag_description']*/?></td>-->
                    <!--td><?php echo $item['tag_group_title']?></td-->
                    <td class="tac vam">
                        <?php
                        foreach($_config['langs'] as $eachLang){
                            $hasData = strlen(processToRender($itemTitle, $eachLang['id'], true)) ? true : false;
                            ?>
                            <span class="label label-<?php echo $hasData ? 'info' : 'default'; ?>"><?php echo strtoupper($eachLang['id']); ?></span>
                            <?php
                        }
                        ?>
                    </td>
                    <td class="tar action_column">
                        <div class="btn-toolbar">
                            <?php
                            echo linkButtonGenerator(array(
                                //'href' => build_url(array('action' => 'add_edit_tag', 'edit' => $item['pk_tag_id'])),
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit '.$tagGroupDetail['tag_group_title'],
                                'classes' => 'add_edit_item',
                                'attributes' => array('data-id' => $item['pk_tag_id']),
                            ));
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete '.$tagGroupDetail['tag_group_title'],
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $item['pk_tag_id'])))
                            ));
                            ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
            }
        ?>
        </tbody>
    </table>
    <div class="table-footer tar">
        <?php echo $pagination?>
    </div>
</div>
<div class="dn">
    <div id="ajax_form_container"></div>
</div>
<script type="text/javascript">
    var tagGroup = '<?php echo $tagGroupDetail['tag_group_title'] ?>';
    var form_api = '<?php echo url('api/'.get_class($this).'/get_tag_form')?>';
    var form_submit_api = '<?php echo url('api/'.get_class($this).'/add_edit_tags')?>';
    init.push(function(){
        emptyTableFill($('.item-table'));

        <?php if($_GET['new_item']): ?>
        $("HTML, BODY").animate({ scrollTop: $('#item_id_<?php echo $_GET['new_item']?>').offset().top}, 100);
        <?php endif; ?>

        $(document).on('click', '.add_edit_item', function(){
            var ths = $(this);
            var data_id = ths.attr('data-id');
            var is_update = typeof data_id !== 'undefined' ? data_id : false;
            var thsRow = is_update ? ths.closest('tr') : null;
            var thsTable = $('.item-table');
            var thsTableBody = thsTable.find('tbody');

            //if(is_update && !edit_permission) return null;
            //else if(!add_permission) return null;

            new in_page_add_event({
                form_title: (is_update ? 'Update '+tagGroup : 'Add New '+tagGroup),
                edit_mode: true,
                edit_form_url: form_api,
                submit_button: is_update ? 'UPDATE' : 'ADD',
                form_container: $('#ajax_form_container'),
                ths: ths,
                url: form_submit_api,
                additional_data : is_update ? {tag_id: data_id, tag_group: <?php echo $tagGroup ?>} : {tag_group: <?php echo $tagGroup ?>},
                callback: function(data){
                    window.location.href = build_url({'new_item' : is_update ? data_id : data['pk_tag_id']});
                    //window.location.reload();
                    return false;
                    //TODO: Fix this. Do not reload. Create button generator functions from PHP to JS and use them to create buttons
                    var updattedRow = '<tr>\
                                            <td>'+data.tag_title+'</td>\
                                            <!--td>'+nl2br(data.tag_description)+'</td-->\
                                            <td class="tar action_column">\
                                                '+(edit_permission ? '<a class="btn btn-xs btn-primary btn-flat btn-labeled add_edit_item" data-id="'+data.pk_user_id+'" href="javascript:"><i class="btn-label icon fa fa-edit"></i>Edit</a>':'')+'\
                                                '+(delete_permission ? '<a class="btn btn-xs btn-danger btn-flat btn-labeled delete_contact" data-id="'+data.pk_user_id+'" href="javascript:"><i class="btn-label icon fa fa-trash"></i>Delete</a>':'')+'\
                                            </td>\
                                        </tr>';
                    if(is_update) thsRow.replaceWith(updattedRow);
                    else thsTableBody.prepend(updattedRow);

                    $('.item-table').tableUpdated();
                    }
                });
            });
        });

</script>