<?php
if(!has_permission('assign_menu_position')) return;

if($_POST['a']){
	if(is_array($_POST['set'])){
		$sql = "TRUNCATE TABLE dev_menu_assignments";
		$clean_db = $devdb->query($sql);
		foreach($_POST['set'] as $i=>$v){
			$data = array(
				'fk_menu_id' => $v['menu'],
				'fk_menu_pos_id' => $v['position']
				);
			$insert = $devdb->insert_update('dev_menu_assignments',$data);
			}
        user_activity::add_activity('Menu assignments to menu positions has been updated.', 'success', 'update');
        $this->reCacheMenuAssignments();
		}
	
	add_notification('Menu Position has been updated.','success');
	header('location:'.current_url());
	exit();
	}
$menus = $this->get_menus();
$assignments = $this->get_menu_assignments();

doAction('render_start');
?>
<div class="page-header">
    <h1>Assign Menus To Positions</h1>
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
        </div>
    </div>
</div>
<div class="table-primary">
    <form name="menu_assignments" method="post" action="">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Position</th>
            <th>Menu</th>
        </tr>
        </thead>
        <tbody>
            <?php
            $set=0;
            foreach($_config['menu_positions'] as $i=>$item){
                $set++;
                ?>
                <tr>
                    <td>
                        <input type="hidden" name="set[<?php echo $set?>][position]" value="<?php echo $i?>">
                        <?php echo $item?>
                    </td>
                    <td><?php
                        $assigned = '';
                        foreach($assignments as $m=>$n){
                            if($n['fk_menu_pos_id'] == $i){
                                $assigned = $n['fk_menu_id'];
                                unset($assignments[$m]);
                                }
                            }
                        ?>
                        <select class="form-control" name="set[<?php echo $set?>][menu]">
                            <option value="">Select One</option>
                            <?php
                            foreach($menus as $m=>$n){
                                $selected = $n['pk_menu_id'] == $assigned ? 'selected' : '';
                                ?>
                                <option value="<?php echo $n['pk_menu_id']?>" <?php echo $selected?>><?php echo $n['menu_title']?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <div class="table-footer tar">
        <?php echo submitButtonGenerator(array(
            'action' => 'save',
            'size' => '',
            'title' => 'Save Assignments',
            'icon' => 'icon_save',
            'name' => 'a',
            'value' => 'Save Assignments',
            'text' => 'Save Assignments',)) ?>
    </div>
    </form>
</div>