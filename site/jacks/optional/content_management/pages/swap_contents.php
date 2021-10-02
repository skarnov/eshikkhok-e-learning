<?php
$pageManager = jack_obj('dev_page_management');
$movingContents = array();

$pageA = isset($_POST['pageA']) ? $_POST['pageA'] : null;
$cTypeA = isset($_POST['cTypeA']) ? $_POST['cTypeA'] : null;
$pageB = isset($_POST['pageB']) ? $_POST['pageB'] : null;
$cTypeB = isset($_POST['cTypeB']) ? $_POST['cTypeB'] : null;
//pre($_POST);
if(isset($_POST['init_swap'])){
    if($_POST['swap_type'] == 'pagetopage'){
        $args = array(
            'page_id' => $pageA == '-1' ? null : $pageA,
            'select_fields' => array('content_title', 'fk_page_id'),
            'data_only' => true,
            );
        $movingContents = $this->get_contents($args);
        }
    else if($_POST['swap_type'] == 'cTypetoCType'){
        $args = array(
            'content_types' => $cTypeA == '-1' ? null : $cTypeA,
            'select_fields' => array('content_title', 'fk_content_type_id'),
            'data_only' => true,
            );
        $movingContents = $this->get_contents($args);
        }
    else if($_POST['swap_type'] == 'pageCTypetoPageCType'){
        $args = array(
            'content_types' => $cTypeA == '-1' ? null : $cTypeA,
            'page_id' => $pageA == '-1' ? null : $pageA,
            'select_fields' => array('content_title', 'fk_content_type_id', 'fk_page_id'),
            'data_only' => true,
            );
        $movingContents = $this->get_contents($args);
        }
    }
else if(isset($_POST['swap_confirmed'])){
    if($_POST['swap_type'] == 'pagetopage'){
        $sql = "UPDATE dev_contents SET fk_page_id = '".$pageB."' WHERE 1 ";

        if($pageA != '-1') $sql .= " AND fk_page_id = '".$pageA."'";

        $ret = $devdb->query($sql);

        if($ret && !isset($ret['error'])){
            cleanCache('content');
            cleanCache('page');
            add_notification($ret.' contents were moved successfully.');
            }
        else print_errors($ret['error']);
        }
    else if($_POST['swap_type'] == 'cTypetoCType'){
        $sql = "UPDATE dev_contents SET fk_content_type_id = '".($cTypeB ? $cTypeB : '')."' WHERE 1 ";

        if($cTypeA != '-1') $sql .= " AND fk_content_type_id = '".$cTypeA."'";

        $ret = $devdb->query($sql);

        if($ret && !isset($ret['error'])){
            cleanCache('content');
            cleanCache('page');
            add_notification($ret.' contents were moved successfully.');
            }
        else print_errors($ret['error']);
        }
    elseif($_POST['swap_type'] == 'pageCTypetoPageCType'){
        $updates = array();
        if($pageB != '-1') $updates[] = " fk_page_id = '".$pageB."' ";
        if($cTypeB != '-1') $updates[] = " fk_content_type_id = '".$cTypeB."' ";
        $updates = $updates ? implode(',', $updates) : null;

        if($updates){
            $from = array();
            if($pageA != '-1') $from[] = " fk_page_id = '".$pageA."' ";
            if($cTypeA != '-1') $from[] = " fk_content_type_id = '".$cTypeA."' ";
            $from = $from ? implode(' AND ', $from) : '1';

            $sql = "UPDATE dev_contents SET ".$updates." WHERE ".$from;
            $ret = $devdb->query($sql);
            if($ret && !isset($ret['error'])){
                cleanCache('content');
                cleanCache('page');
                add_notification($ret.' contents were moved successfully.');
                //TODO: register system event
                }
            }
        }
    header('location: '.current_url());
    exit();
    }
else if(isset($_POST['swap_canceled'])){
    if(isset($_POST['swap_type']) && isset($_POST['swap_canceled'])){
        add_notification('No swap took place.');
        header('location: '.current_url());
        exit();
        }
    }

doAction('render_start');
?>
<div class="page-header">
    <h1>Manage Contents</h1>
</div>
<?php
if(isset($_POST['init_swap'])){
    if($_POST['swap_type'] == 'pagetopage'){
        ?>
        <div class="table-danger table-responsive">
            <div class="table-header">
                Based on your choice, following swaps will take place, please review carefully.<br />When done, either confirm this swap or cancel it at the bottom of the table
            </div>
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Content</th>
                    <th>From Page</th>
                    <th>To Page</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($movingContents['data']){
                    foreach($movingContents['data'] as $item){
                        if($item['fk_page_id']){
                            $fromPage = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                            $fromPage = processToRender($fromPage['page_title']);
                            }
                        else $fromPage = 'None';

                        if($pageB){
                            $toPage = $pageManager->get_a_page($pageB, array('tiny' => true));
                            $toPage = processToRender($toPage['page_title']);
                            }
                        else $toPage = 'None';

                        ?>
                        <tr>
                            <td><?php echo processToRender($item['content_title']); ?></td>
                            <td><?php echo $fromPage; ?></td>
                            <td><?php echo $toPage; ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="table-footer">
                <form method="post" action="">
                    <input type="hidden" name="swap_type" value="pagetopage">
                    <input type="hidden" name="pageA" value="<?php echo $pageA; ?>">
                    <input type="hidden" name="pageB" value="<?php echo $pageB; ?>">
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'config',
                        'icon' => 'icon_swap',
                        'name' => 'swap_confirmed',
                        'text' => 'Confirm Swap',
                        'title' => 'Confirm Swap',
                        'size' => 'sm',
                        ));
                    ?>
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'delete',
                        'icon' => 'icon_stop',
                        'name' => 'swap_canceled',
                        'text' => 'Cancel',
                        'title' => 'Cancel',
                        'size' => 'sm',
                        ));
                    ?>
                </form>
            </div>
        </div>
        <?php
        }
    if($_POST['swap_type'] == 'cTypetoCType'){
        ?>
        <div class="table-danger table-responsive">
            <div class="table-header">
                Based on your choice, following swaps will take place, please review carefully.<br />When done, either confirm this swap or cancel it at the bottom of the table
            </div>
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Content</th>
                    <th>From Content Type</th>
                    <th>To Content Type</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($movingContents['data']){
                    foreach($movingContents['data'] as $item){
                        if(isset($_config['content_types'][$item['fk_content_type_id']]))
                            $fromCType = $_config['content_types'][$item['fk_content_type_id']]['title'];
                        else $fromCType = 'None';

                        if($cTypeB && isset($_config['content_types'][$cTypeB]))
                            $toCType = $_config['content_types'][$cTypeB]['title'];
                        else $toCType = 'None';
                        ?>
                        <tr>
                            <td><?php echo processToRender($item['content_title']); ?></td>
                            <td><?php echo $fromCType; ?></td>
                            <td><?php echo $toCType; ?></td>
                        </tr>
                        <?php
                        }
                    }
                ?>
                </tbody>
            </table>
            <div class="table-footer">
                <form method="post" action="">
                    <input type="hidden" name="swap_type" value="cTypetoCType">
                    <input type="hidden" name="cTypeA" value="<?php echo $cTypeA; ?>">
                    <input type="hidden" name="cTypeB" value="<?php echo $cTypeB; ?>">
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'config',
                        'icon' => 'icon_swap',
                        'name' => 'swap_confirmed',
                        'text' => 'Confirm Swap',
                        'title' => 'Confirm Swap',
                        'size' => 'sm',
                        ));
                    ?>
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'delete',
                        'icon' => 'icon_stop',
                        'name' => 'swap_canceled',
                        'text' => 'Cancel',
                        'title' => 'Cancel',
                        'size' => 'sm',
                        ));
                    ?>
                </form>
            </div>
        </div>
        <?php
        }
    else if($_POST['swap_type'] == 'pageCTypetoPageCType'){
        ?>
        <div class="table-danger table-responsive">
            <div class="table-header">
                Based on your choice, following swaps will take place, please review carefully.<br />When done, either confirm this swap or cancel it at the bottom of the table
            </div>
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th rowspan="2">Content</th>
                    <th colspan="2" class="tac">OLD</th>
                    <th colspan="2" class="tac">NEW</th>
                </tr>
                <tr>
                    <th class="tac">Page</th>
                    <th class="tac">Content Type</th>
                    <th class="tac">Page</th>
                    <th class="tac">Content Type</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if($movingContents['data']){
                    foreach($movingContents['data'] as $item){
                        if($item['fk_page_id']){
                            $fromPage = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                            $fromPage = processToRender($fromPage['page_title']);
                            }
                        else $fromPage = 'None';

                        if(isset($_config['content_types'][$item['fk_content_type_id']]))
                            $fromCType = $_config['content_types'][$item['fk_content_type_id']]['title'];
                        else $fromCType = 'None';

                        $toPage = '';
                        if($pageB == '-1') $toPage = $fromPage;
                        elseif($pageB == '0') $toPage = 'None';
                        else{
                            $toPage = $pageManager->get_a_page($pageB, array('tiny' => true));
                            $toPage = processToRender($toPage['page_title']);
                            }

                        $toCType = '';
                        if($cTypeB == '-1') $toCType = $fromCType;
                        elseif($cTypeB == '0') $toCType = 'None';
                        else{
                            if($cTypeB && isset($_config['content_types'][$cTypeB]))
                                $toCType = $_config['content_types'][$cTypeB]['title'];
                            else $toCType = 'None';
                            }
                        ?>
                        <tr>
                            <td><?php echo processToRender($item['content_title']); ?></td>
                            <td class="tac"><?php echo $fromPage; ?></td>
                            <td class="tac"><?php echo $fromCType; ?></td>
                            <td class="tac"><?php echo $toPage; ?></td>
                            <td class="tac"><?php echo $toCType; ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="table-footer">
                <form method="post" action="">
                    <input type="hidden" name="swap_type" value="pageCTypetoPageCType">
                    <input type="hidden" name="pageA" value="<?php echo $pageA; ?>">
                    <input type="hidden" name="pageB" value="<?php echo $pageB; ?>">
                    <input type="hidden" name="cTypeA" value="<?php echo $cTypeA; ?>">
                    <input type="hidden" name="cTypeB" value="<?php echo $cTypeB; ?>">
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'config',
                        'icon' => 'icon_config',
                        'name' => 'swap_confirmed',
                        'text' => 'Confirm Swap',
                        'title' => 'Confirm Swap',
                        'size' => 'sm',
                    ));
                    ?>
                    <?php
                    echo submitButtonGenerator(array(
                        'action' => 'delete',
                        'icon' => 'icon_delete',
                        'name' => 'swap_canceled',
                        'text' => 'Cancel',
                        'title' => 'Cancel',
                        'size' => 'sm',
                    ));
                    ?>
                </form>
            </div>
        </div>
        <?php
        }
    }
else{
    ?>
    <div class="note note-danger">
        <h4>Be Cautious! Read Carefully</h4>
        In this panel you will be moving contents from one page to another, one content type to another and one page+content type to another. These actions can destroy your content layout.<br /><br />
        Proceed only if you know what you are doing.
    </div>
    <div class="panel panel-dark panel-warning">
        <div class="panel-heading">
            <span class="panel-title">Move all contents of <strong>Page A</strong> to <strong>Page B</strong></span>
        </div>
        <form id="pageAtoPageB" method="post" action="">
            <div class="panel-body">
                <input type="hidden" name="swap_type" value="pagetopage">
                <div class="row">
                    <div class="col-sm-6">
                        <label>Select Page A</label>
                        <select class="form-control" name="pageA" required>
                            <option value="-1">Any</option>
                            <option value="0">None</option>
                            <?php  echo getPageSelectOptions()?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label>Select Page B</label>
                        <select class="form-control" name="pageB" required>
                            <option value="0">None</option>
                            <?php  echo getPageSelectOptions()?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <?php
                echo submitButtonGenerator(array(
                    'action' => 'config',
                    'icon' => 'icon_search',
                    'name' => 'init_swap',
                    'text' => 'Check Swapable Contents',
                    'title' => 'Check Swapable Contents',
                    'size' => 'sm',
                ));
                ?>
            </div>
        </form>
    </div>
    <div class="panel panel-dark panel-info">
        <div class="panel-heading">
            <span class="panel-title">Move all contents of <strong>Content Type A</strong> to <strong>Content Type B</strong></span>
        </div>
        <form id="cTypeAtoCTypeB" method="post" action="">
            <div class="panel-body">
                <input type="hidden" name="swap_type" value="cTypetoCType">
                <div class="row">
                    <div class="col-sm-6">
                        <label>Select Content Type A</label>
                        <select class="form-control" name="cTypeA" required>
                            <option value="-1">Any</option>
                            <option value="">None</option>
                            <?php
                            foreach($_config['content_types'] as $i=>$content_type){
                                if($content_type['exceptional']) continue;
                                ?>
                                <option value="<?php echo $i; ?>"><?php echo $content_type['title']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label>Select Content Type B</label>
                        <select class="form-control" name="cTypeB" required>
                            <option value="">None</option>
                            <?php
                            foreach($_config['content_types'] as $i=>$content_type){
                                if($content_type['exceptional']) continue;
                                ?>
                                <option value="<?php echo $i; ?>"><?php echo $content_type['title']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <?php
                echo submitButtonGenerator(array(
                    'action' => 'config',
                    'icon' => 'icon_search',
                    'name' => 'init_swap',
                    'text' => 'Check Swapable Contents',
                    'title' => 'Check Swapable Contents',
                    'size' => 'sm',
                ));
                ?>
            </div>
        </form>
    </div>
    <div class="panel panel-dark panel-success">
        <div class="panel-heading">
            <span class="panel-title">Move all contents of <strong>Page A Content Type A</strong> to <strong>Page B Content Type B</strong></span>
        </div>
        <form id="pageAcTypeAtoPageBCTypeB" method="post" action="">
            <div class="panel-body">
                <input type="hidden" name="swap_type" value="pageCTypetoPageCType">
                <div class="row">
                    <div class="col-sm-6">
                        <fieldset>
                            <legend>From</legend>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Select Page A</label>
                                    <select class="form-control" name="pageA">
                                        <option value="-1">Any</option>
                                        <option value="0">None</option>
                                        <?php  echo getPageSelectOptions()?>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label>Select Content Type A</label>
                                    <select class="form-control" name="cTypeA">
                                        <option value="-1">Any</option>
                                        <option value="">None</option>
                                        <?php
                                        foreach($_config['content_types'] as $i=>$content_type){
                                            if($content_type['exceptional']) continue;
                                            ?>
                                            <option value="<?php echo $i; ?>"><?php echo $content_type['title']; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-sm-6">
                        <fieldset>
                            <legend>To</legend>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Select Page B</label>
                                    <select class="form-control" name="pageB">
                                        <option value="-1">Don't Change</option>
                                        <option value="0">No Page</option>
                                        <?php  echo getPageSelectOptions()?>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label>Select Content Type B</label>
                                    <select class="form-control" name="cTypeB">
                                        <option value="-1">Don't Change</option>
                                        <option value="">No Content Type</option>
                                        <?php
                                        foreach($_config['content_types'] as $i=>$content_type){
                                            if($content_type['exceptional']) continue;
                                            ?>
                                            <option value="<?php echo $i; ?>"><?php echo $content_type['title']; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <?php
                echo submitButtonGenerator(array(
                    'action' => 'config',
                    'icon' => 'icon_search',
                    'name' => 'init_swap',
                    'text' => 'Check Swapable Contents',
                    'title' => 'Check Swapable Contents',
                    'size' => 'sm',
                ));
                ?>
            </div>
        </form>
    </div>
    <?php
    }
?>
