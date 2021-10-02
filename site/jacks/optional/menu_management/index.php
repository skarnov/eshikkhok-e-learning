<?php
class dev_menu_management{
    var $thsClass = 'dev_menu_management';
	function __construct(){
		jack_register($this);
		}
    function init(){
        apiRegister($this,'get_menus');
        apiRegister($this,'delete_menu');
        apiRegister($this,'get_menuItems');
        apiRegister($this,'_get_menuItems');
        apiRegister($this,'deleteMenuItems');
        apiRegister($this,'menu_item_edit_form');
        apiRegister($this,'get_menu_assignments');
        apiRegister($this,'add_edit_menu');

        $permissions = array(
            'group_name' => 'Menu Management',
            'permissions' => array(
                'manage_menu' => array(
                    'add_menu' => 'Add Menu',
                    'edit_menu' => 'Edit Menu',
                    'delete_menu' => 'Delete Menu',
                    'config_menu' => 'Configure Menu',
                    'assign_menu_position' => 'Assign Menu to Menu Position',
                    ),
                ),
            );

        if(!isPublic()){
            register_permission($permissions);

            $this->adm_menus();
            }
        }

    function adm_menus(){
        $params = array(
            'label' => 'Menus',
            'description' => 'Manage Menus',
            'menu_group' => 'Administration',
            'action' => 'manage_menus',
            'iconClass' => 'fa-bars',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_menu')) admenu_register($params);
        }

    function manage_menus(){
        if(!has_permission('manage_menu')) return;
        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_menus');

        if($_GET['action'] == 'add_edit_menus')
            include('pages/add_edit_menus.php');
        else if($_GET['action'] == 'assign_menu')
            include('pages/assign_menu.php');
        else if($_GET['action'] == 'settings')
            include('pages/menu_settings.php');
        else
            include('pages/list_menu.php');
        }

    function deleteMenuItems($args = array()){
        global $devdb;

        if(!$args['the_menu']){
            $sql = "SELECT fk_menu_id FROM dev_menu_items WHERE pk_item_id = '".$args['menu_item']."'";
            $the_menu = $devdb->get_row($sql);
            $args['the_menu'] = $the_menu['fk_menu_id'];
            }

        $menu_items = $this->get_menuItems($args['the_menu'],true);
        $delete = $this->recur_deleteMenuItems($args['menu_item'], $menu_items);

        $sql = "DELETE FROM dev_menu_items WHERE pk_item_id IN (".implode(',',$delete).")";
        $deleted = $devdb->query($sql);

        return $deleted;
        }

    function recur_deleteMenuItems($key, &$data){
        $child = array();
        $child[] = $key;
        if($data){
            foreach($data as $i=>$v){
                if($v['fk_item_id'] == $key){
                    $child[] = $v['pk_item_id'];
                    $temp = $this->recur_deleteMenuItems($v['pk_item_id'], $data);
                    if($temp){
                        foreach($temp as $m=>$n){
                            $child[] = $n;
                            }
                        }
                    }
                }
            }
        return $child;
        }

    function add_edit_menu(){
        global $devdb, $_config;

        $ret = array();
        $edit = $_POST['menu_id'] ? $_POST['menu_id'] : NULL;

        if($edit && !has_permission('edit_menu')){
            $ret['error'][] = 'You don\'t have enough permission to edit menu.';
            }
        elseif(!has_permission('add_menu')){
            $ret['error'][] = 'You don\'t have enough permission to add menu.';
            }

        $data = $devdb->deep_escape($_POST);

        if(!$edit){
            $data['menu_slug'] = $data['menu_title'];
            $data['menu_slug'] = form_modifiers::slug($data['menu_slug']);

            $cond = $edit ? " pk_menu_id != '".$edit."'" : '';
            $temp = form_validator::unique($data['menu_slug'],'dev_menus','menu_slug',$cond);
            if($temp !== true)
                $data['menu_slug'] = $temp;

            $temp = form_validator::required($data['menu_slug']);
            if($temp !== true)
                $ret['error'][] = 'Menu Slug '.$temp;
            }

        $temp = form_validator::required($data['menu_title']);
        if($temp !== true)
            $ret['error'][] = 'Menu title '.$temp;

        if(!$ret['error']){
            $insert_data = array(
                'menu_title' => $data['menu_title'],
                );

            if($edit){
                $insert_data['modified_at'] = date('Y-m-d H:i:s');
                $insert_data['modified_by'] = $_config['user']['pk_user_id'];

                $ret = $devdb->insert_update('dev_menus',$insert_data," pk_menu_id = '".$edit."'");
                if($ret['success']){
                    user_activity::add_activity('Menu (ID: '.$edit.') has been updated.','success', 'update');
                    $ret['data'] = array(
                        'pk_menu_id' => $edit,
                        'menu_title' => $insert_data['menu_title'],
                        );
                    }
                }
            else{
                $insert_data['menu_slug'] = $data['menu_slug'];
                $insert_data['created_at'] = date('Y-m-d H:i:s');
                $insert_data['created_by'] = $_config['user']['pk_user_id'];
                $insert_data['modified_at'] = date('Y-m-d H:i:s');
                $insert_data['modified_by'] = $_config['user']['pk_user_id'];

                $ret = $devdb->insert_update('dev_menus',$insert_data);

                if($ret['success']){
                    user_activity::add_activity('Menu (ID: '.$ret['success'].') has been created.','success', 'create');
                    $ret['data'] = array(
                        'pk_menu_id' => $ret['success'],
                        'menu_title' => $insert_data['menu_title'],
                        'menu_slug' => $insert_data['menu_slug']
                        );
                    }
                }
            }

        return $ret;
        }

	function delete_menu($param = array()){
		global $devdb;
		
		if(is_array($param)) $sql = "DELETE FROM dev_menus WHERE pk_menu_id IN (".implode(',',$param).")";
		elseif($param) $sql = "DELETE FROM dev_menus WHERE pk_menu_id = '".$param."'";
		else $sql = "DELETE FROM dev_menus";
		
		$ret = $devdb->query($sql);
		
		return $ret;
		}

	function get_menus($menu_id = NULL){
		global $devdb;
		
		if($menu_id){
			$sql = "SELECT * FROM dev_menus WHERE pk_menu_id='".$menu_id."'";
			$menus = $devdb->get_row($sql);
			} 
		else{
			$sql = "SELECT * FROM dev_menus";
			$menus = $devdb->get_results($sql,'pk_menu_id');
			}

		return $menus;
		}

	function get_each_items($parent, &$childs){
		$pageManager = jack_obj('dev_page_management');

		$menu = '<ul>';
		$childs_found = false;
		foreach($childs as $i=>$v){
			if($v['fk_item_id'] != $parent) continue;
			$item_title = '';
			if($v['fk_page_id']){
                $page = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));
                $item_title = $page['page_title'];
                if(!$v['use_page_title']) $item_title = $v['item_title'];
				}
			else $item_title = $v['item_title'];

            $item_title = processToRender($item_title);

			$childs_found = true;
			$menu .= '<li id="ID_'.$v['pk_item_id'].'"><div class="item"><span class="sortHandle"><i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-ellipsis-v"></i></span>&nbsp;&nbsp;<span class="title">'.$item_title.'</span><span class="pull-right"><a href="javascript:" class="btn btn-xs btn-primary mr5 show_item_detail"><i class="fa fa-edit"></i></a><a href="javascript:" class="remove_menu_item btn btn-xs btn-danger"><i class="icon fa fa-times-circle"></i></a></span></div>'.$this->menu_item_edit_form($v);
            $menu .= $this->get_each_items($v['pk_item_id'],$childs).'</li>';
			}
		$menu .= '</ul>';
		if(!$childs_found) $menu = '';
		return $menu;
	    }

    function reCacheEachMenuItems($menu_id){
        $cacheID = 'each_menu_item_'.$menu_id;
        removeCache($cacheID);
        }

	function get_menuItems( $menu_id, $data_only = false){
		global $devdb, $_config;
        $pageManager = jack_obj('dev_page_management');
        $cacheID = 'each_menu_item_'.$menu_id;
        $result = getCache($cacheID);

        if(!hasCache($result)){
            $result = $devdb->get_results("SELECT * FROM dev_menu_items WHERE fk_menu_id='". $menu_id ."'ORDER BY item_sort_order ASC");
            setCache($result,$cacheID);
            }

        if($data_only) return $result;

		$nav_menu = '<ul class="sortable">';
		foreach($result as $g=>$item){
			$item_title = '';
			if($item['fk_page_id']){
				$page = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                $item_title = $page['page_title'];
                if(!$item['use_page_title']) $item_title = $item['item_title'];
				}
			else $item_title = $item['item_title'];

            $item_title = processToRender($item_title);

			if($item['fk_item_id']) continue;
			$nav_menu .= '<li id="ID_'.$item['pk_item_id'].'"><div class="item"><span class="sortHandle"><i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-ellipsis-v"></i></span>&nbsp;&nbsp;<span class="title">'.$item_title.'</span><span class="pull-right"><a href="javascript:" class="btn btn-xs btn-primary mr5 show_item_detail"><i class="fa fa-edit"></i></a><a href="javascript:" class="remove_menu_item btn btn-xs btn-danger"><i class="icon fa fa-times-circle"></i></a></span></div>'.$this->menu_item_edit_form($item);
			$nav_menu .= $this->get_each_items($item['pk_item_id'],$result).'</li>';
			}
		$nav_menu .= '</ul>';
		
		return $nav_menu;
		}

	function menu_item_edit_form($item){
        global $_config;
        ob_start();
        if($item['fk_page_id']){
            ?>
            <div class="panel menu_edit_form" style="display: none;">
                <div class="panel-body">
                    <form>
                        <input type="hidden" name="menu_item_id" value="<?php echo $item['pk_item_id']?>" />
                        <div class="form-group">
                            <label>Select A Page</label>
                            <select class="form-control" id="menu_item" name="the_page">
                                <option value="">Select One</option>
                                <?php
                                $selected = $item['fk_page_id'] ? $item['fk_page_id'] : null;
                                echo getPageSelectOptions($selected);
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="checkbox">
                                <input class="px use_page_title" type="checkbox" name="use_page_title" value="1" <?php echo $item['use_page_title'] ? 'checked' : ''; ?> />
                                <span class="lbl">Use Page Title as Item Label</span>
                            </label>
                        </div>
                        <div class="form-group custom_item_label" style="display: none" >
                            <label>Label for Item</label>
                            <input type="text" name="item_title" class="form-control" value="<?php echo processToRender($item['item_title']); ?>" />
                        </div>
                        <div class="form-group dn">
                            <label>Menu Item CSS Class</label>
                            <input type="text" name="item_css_class" class="form-control" value="<?php echo $item['item_css_class'] ? $item['item_css_class'] : ''; ?>" />
                            <p class="help-block">Leave blank if you don't understand.</p>
                        </div>
                        <div class="form-group dn">
                            <label>Menu Item Icon CSS Class</label>
                            <input type="text" name="item_icon_class" class="form-control" value="<?php echo $item['item_icon_class'] ? $item['item_icon_class'] : ''; ?>" />
                            <p class="help-block">Leave blank if you don't understand.</p>
                        </div>
                        <?php
                        echo buttonButtonGenerator(array(
                            'action' => 'update',
                            'icon' => 'icon_update',
                            'text' => 'Update',
                            'title' => 'Update Menu Item',
                            'classes' => 'edit_menu_item',
                            'size' => '',
                        ));
                        ?>
                    </form>
                </div>
            </div>
            <?php
            }
        else{
            ?>
            <div class="panel menu_edit_form" style="display: none;">
                <div class="panel-body">
                    <form>
                        <input type="hidden" name="menu_item_id" value="<?php echo $item['pk_item_id']?>" />
                        <?php
                        if($_config['optional_menu_links']){
                            ?>
                            <div class="form-group">
                                <label>Pre-defined Links</label>
                                <select name="optional_item" class="optional_links form-control">
                                    <option value="">Select One</option>
                                    <?php
                                    foreach($_config['optional_menu_links'] as $i=>$v){
                                        $selected = $item['optional_item'] && $item['optional_item'] == $i ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $i ?>" <?php echo $selected ?>><?php echo $v['label'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                            }
                        ?>
                        <div class="form-group">
                            <label>Link Title</label>
                            <input type="text" class="form-control" name="item_title" value="<?php echo processToRender($item['item_title'])?>"/>
                        </div>
                        <div class="form-group">
                            <label>Link URL</label>
                            <input type="text" class="form-control" name="item_ext_url" value="<?php echo $item['item_ext_url']?>"/>
                        </div>
                        <?php
                        echo buttonButtonGenerator(array(
                            'action' => 'update',
                            'icon' => 'icon_update',
                            'text' => 'Update',
                            'title' => 'Update Menu Item',
                            'classes' => 'edit_menu_item',
                            'size' => '',
                        ));
                        ?>
                    </form>
                </div>
            </div>
            <?php
            }
        ?>

        <?php
        return ob_get_clean();
        }

	function _get_each_items($parent, &$childs, $args = array()){
		global $_config;
		$pageManager = jack_obj('dev_page_management');

		$menu = '';
		$childs_found = false;
		foreach($childs as $i=>$v){
			if($v['fk_item_id'] != $parent) continue;
			$item_title = '';
            $page = null;
			if($v['fk_page_id']){
				$page = $pageManager->get_a_page($v['fk_page_id'], array('tiny' => true));
				$item_title = $page['page_title'];
				if(!$v['use_page_title']) $item_title = $v['item_title'];
                $link = url($page['page_slug']);
				}
			else{
			    $item_title = $v['item_title'];
			    if(strlen($v['item_ext_url'])) $link = $v['item_ext_url'];
                else $link = 'javascript:';
                }

            $item_title = processToRender($item_title);

			$childs_found = true;
            $c = $this->_get_each_items($v['pk_item_id'],$childs, $args);

            //pageId and Slug attribute
            $pageIdAttr = $page ? 'data-page-id="'.$page['pk_page_id'].'"' : '';
            $pageSlugAttr = $page ? 'data-page-slug="'.$page['page_slug'].'"' : '';
            $pageLink = $page ? 'data-page-link="'.page_url($page).'"' : '';

            $menu .= '<li '.$pageLink.' '.$pageIdAttr.' '.$pageSlugAttr.' class="'.($v['item_css_class'] ? ' '.$v['item_css_class'] : '').($c ? ' '.$args['li_with_ul_class'].' ' : '').'"><a '.$pageIdAttr.' '.$pageSlugAttr.' class="'.($args['link_class'] ? ' '.$args['link_class'].' ' : '').'" href="'.$link.'">'.($v['item_icon_class'] ? '<i class="fa '.$v['item_icon_class'].'"></i>' : '').$item_title.'</a>';
            $menu .= ($c ? '<ul class="'.($args && $args['inner_ul_class'] ? $args['inner_ul_class'] : '').'">'.$c.'</ul>' : '').'</li>';

			//$menu .= '<li><a href="'.url($page['page_slug']).'">'.$item_title.'</a></li>';
			}
		$menu .= '';
		if(!$childs_found) $menu = NULL;
		return $menu;
	    }

	function _get_menuItems($args=array()){
        $menu_id = $args['menu_id'];
		global $devdb, $_config;
        $pageManager = jack_obj('dev_page_management');

        $cacheID = 'each_menu_item_'.$menu_id;
        $result = getCache($cacheID);

        if(!hasCache($result)){
            $result = $devdb->get_results("SELECT * FROM dev_menu_items WHERE fk_menu_id='". $menu_id ."'ORDER BY item_sort_order ASC");
            setCache($result,$cacheID);
            }

		$nav_menu = !$args['childs_only'] ? '<ul class="'.$args['ul_class'].'">' : '';

		foreach($result as $g=>$item){
            if($item['fk_item_id']) continue;
			$item_title = '';
            $page = null;
			if($item['fk_page_id']){
				$page = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                $item_title = $page['page_title'];
                if(!$item['use_page_title']) $item_title = $item['item_title'];
                $link = url($page['page_slug']);
				}
			else{
                $item_title = $item['item_title'];
                if(strlen($item['item_ext_url'])) $link = $item['item_ext_url'];
                else $link = 'javascript:';
                }

            $item_title = processToRender($item_title);

            $childs = $this->_get_each_items($item['pk_item_id'],$result, $args);

            //pageId and Slug attribute
            $pageIdAttr = $page ? 'data-page-id="'.$page['pk_page_id'].'"' : '';
            $pageSlugAttr = $page ? 'data-page-slug="'.$page['page_slug'].'"' : '';
            $pageLink = $page ? 'data-page-link="'.page_url($page).'"' : '';

			$nav_menu .= '<li '.$pageLink.' '.$pageIdAttr.' '.$pageSlugAttr.' class="'.($item['item_css_class'] ? ' '.$item['item_css_class'].' ' : '').($args['li_class'] ? ' '.$args['li_class'].' ' : '').($childs ? ' '.$args['li_with_ul_class'].' ' : '').'"><a '.$pageIdAttr.' '.$pageSlugAttr.' class="'.($args['link_class'] ? ' '.$args['link_class'].' ' : '').'" href="'.$link.'" >'.($item['item_icon_class'] ? '<i class="fa '.$item['item_icon_class'].'"></i>' : '').$item_title.'</a>';
			$nav_menu .= ($childs ? '<ul class="'.($args['inner_ul_class'] ? ' '.$args['inner_ul_class'].' ' : '').'">'.$childs.'</ul>' : '').'</li>';
			}
		$nav_menu .= !$args['childs_only'] ? '</ul>' : '';

		return $nav_menu;
		}

    function reCacheMenuAssignments(){
        removeCache('menu_assignments');
        $assignments = $this->get_menu_assignments();

        if($assignments){
            foreach($assignments as $i=>$v){
                removeCache('each_menu_assignment_'.$v['fk_menu_pos_id']);
                }
            }
        }

	function get_menu_assignments(){
		global $devdb;

		$pos = array();
        $assignments = getCache('menu_assignments');
        if(!hasCache($assignments)){
            $sql = "SELECT * FROM dev_menu_assignments";
            $assignments = $devdb->get_results($sql,'pk_menu_assign_id');
            setCache($assignments,'menu_assignments');
            }
		return $assignments;
		}


	}
$menu_manager = new dev_menu_management;

function __render_menu($pos, $params = array()){
	$params['ul_class'] = $params['ul_class'] ? $params['ul_class'] : '';
	global $devdb;

    $menu_manager = jack_obj('dev_menu_management');
	if($pos){
        $cacheID = 'each_menu_assignment_'.$pos;
        $assigned = getCache($cacheID);
        if(!hasCache($assigned)){
            $sql = "SELECT * FROM dev_menu_assignments WHERE fk_menu_pos_id = '".$pos."'";
            $assigned = $devdb->get_row($sql);
            setCache($assigned,$cacheID);
            }

		if($assigned){
            $args = array(
                'menu_id' => $assigned['fk_menu_id'],
                'ul_class' => $params['ul_class'] ? $params['ul_class'] : false, //this class will be pushed into top ul
                'childs_only' => $params['childs_only'] ? true : false, //If true the Top UL will be returned, however inner UL will be.
                'li_class' => $params['li_class'] ? $params['li_class'] : false, //class for li
                'deactivate_parents' => $params['deactivate_parents'] ? true : false, //If TRUE any parent element will not have its link associated, href will be javascript#
                'inner_ul_class' => $params['inner_ul_class'] ? $params['inner_ul_class'] : false,
                'link_class' => $params['link_class'] ? $params['link_class'] : false,
                'li_with_ul_class' => $params['li_with_ul_class'] ? $params['li_with_ul_class'] : false,
                );

            if($params['render_function'])
                $the_menu = $params['render_function']($args);
            else
                $the_menu = $menu_manager->_get_menuItems($args);

			return $the_menu;
			}
		}
	return NULL;
	}