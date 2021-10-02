<?php
class dev_tag_management{
    var $thsClass = 'dev_tag_management';
	function __construct(){
		jack_register($this);
		}

    function init(){
        apiRegister($this,'get_tag_form');
        apiRegister($this,'get_tags_autocomplete');
        apiRegister($this,'add_edit_tags');
        apiRegister($this,'get_tags');
        apiRegister($this,'get_tag_group');
        apiRegister($this,'get_tag_group_id_by_slug');
        apiRegister($this,'add_edit_tag_group');
        apiRegister($this,'get_content_tags');
        apiRegister($this,'get_page_tags');
        apiRegister($this,'attach_tags');

        if(!isPublic()) $this->adm_menus();
        }

    function adm_menus(){
	    $tagGroups = $this->get_tag_group();
	    foreach($tagGroups as $i=>$v){
            $params = array(
                'label' => dbReadableString($v['tag_group_title'], false),
                'description' => 'Manage '.ucwords($v['tag_group_title']),
                'menu_group' => 'Contents',
                'url' => url('admin/dev_tag_management/manage_tags?tag_group='.$v['pk_tag_group_id']),
                'iconClass' => 'fa-tag',
                'jack' => $this->thsClass,
                );
            admenu_register($params);
            }


        $params = array(
            'label' => 'Manage Tag Group',
            'description' => 'Manage Tag Group',
            'menu_group' => 'Tags',
            'action' => 'manage_tag_group',
            'iconClass' => 'fa-tags',
            'jack' => $this->thsClass,
            );
        //admenu_register($params);
        }

    function manage_tags(){
        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_tags');

        if($_GET['action'] == 'add_edit_tag')
            include('pages/add_edit_tag.php');
        elseif($_GET['action'] == 'ajax_processor')
            include('pages/ajax_processor.php');
        else
            include('pages/list_tags.php');
        }

    function manage_tag_group(){
        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_tag_group');

        if($_GET['action'] == 'add_edit_tag_group')
            include('pages/add_edit_tag_group.php');
        else
            include('pages/list_tag_group.php');
        }

	function get_content_tags($content_id, $tag_type = 'tag'){
		return $this->get_attached_tags($content_id,'content', $tag_type);
		}

	function get_page_tags($page_id, $tag_type = 'tag'){
		return $this->get_attached_tags($page_id,'page', $tag_type);
		}

	public function get_attached_tags($content_id, $content_type, $tag_type = 'tag'){
		global $devdb;
        $cacheID = 'each_content_tag_'.$content_id.'_'.$content_type.'_'.$tag_type;
		$tags = getCache($cacheID, 'content');
        if(!hasCache($tags)){
            $sql = "SELECT dev_content_tag_relation.*, dev_tags.* FROM dev_content_tag_relation, dev_tags WHERE dev_tags.pk_tag_id = dev_content_tag_relation.fk_tag_id AND dev_content_tag_relation.fk_content_id = '".$content_id."' AND dev_content_tag_relation.content_type = '".$content_type."' AND dev_content_tag_relation.tag_type = '".$tag_type."'";

            $tags = $devdb->get_results($sql,'pk_tag_id');

            setCache($tags,$cacheID, 'content');
            }
		return $tags;
		}
    function get_attached_tags_refill($content_id, $content_type, $tag_type = 'tag'){
	    global $devdb;
	    $cacheID = 'each_content_tag_refill_'.$content_id.'_'.$content_type.'_'.$tag_type;
	    $tags = getCache($cacheID, 'course');
	    if(!hasCache($tags)){
	        $sql = "SELECT pk_tag_id as id, tag_title as label FROM dev_content_tag_relation, dev_tags WHERE dev_tags.pk_tag_id = dev_content_tag_relation.fk_tag_id AND dev_content_tag_relation.fk_content_id = '".$content_id."' AND dev_content_tag_relation.content_type = '".$content_type."' AND dev_content_tag_relation.tag_type = '".$tag_type."'";
            $tags = $devdb->get_results($sql);
            setCache($tags, $cacheID, 'content');
            }
        return $tags;
        }

    function attach_tags($content_id, $content_type, $tags, $tag_type = 'tag'){
        global $devdb;

        $sql = "DELETE FROM dev_content_tag_relation WHERE fk_content_id = '" . $content_id . "' AND content_type = '".$content_type."' AND tag_type = '".$tag_type."'";
        $deleted = $devdb->query($sql);

        $cacheID = 'each_content_tag_'.$content_id.'_'.$content_type.'_'.$tag_type;
        removeCache($cacheID);
        $cacheID = 'each_content_tag_refill_'.$content_id.'_'.$content_type.'_'.$tag_type;
        removeCache($cacheID);

        $sql = "INSERT INTO dev_content_tag_relation(fk_tag_id,fk_content_id,content_type,tag_type) VALUES";

        if ($tags) {
            foreach ($tags as $v) {
                $sql .= "('" . $v . "','" . $content_id . "','".$content_type."', '".$tag_type."'),";
                };

            $sql = rtrim($sql, ',');

            $insert_tags = $devdb->query($sql);
            }
        }

	function get_tag_group($group_id=NULL){
		global $devdb;
		
		if($group_id){
		    $cacheID = 'each_tag_group_'.$group_id;
		    $groups = getCache($cacheID, 'single_tag_group');
		    if(!hasCache($groups)){
                $sql = "SELECT * FROM dev_tags_group WHERE pk_tag_group_id='".$group_id."'";
                $groups = $devdb->get_row($sql);
                setCache($groups, $cacheID, 'single_tag_group');
                }
			} 
		else{
            $cacheID = 'all_tag_groups';
            $groups = getCache($cacheID, 'tag');
            if(!hasCache($groups)){
                $sql = "SELECT * FROM dev_tags_group";
                $groups = $devdb->get_results($sql,'pk_tag_group_id');
                setCache($groups, $cacheID, 'tag');
                }
			}
		
		return $groups;
		}

	function get_tag_group_id_by_slug($group_slug){
		if(!$group_slug) return;
		global $devdb;
        $cacheID = 'each_tag_group_id_by_slug_'.$group_slug;
        $group_id = getCache($cacheID, 'tag');
        if(true || !hasCache($group_id)){
            $sql = "SELECT pk_tag_group_id FROM dev_tags_group WHERE tag_group_slug = '".$group_slug."'";
            $group_id = $devdb->get_row($sql);
            $group_id = $group_id['pk_tag_group_id'];
            setCache($group_id, $cacheID, 'tag');
            }

		return $group_id;
		}
    function get_tags_autocomplete(){
        $param = array_replace_recursive($_POST, $_GET);

        $options = $param['_data'];
        $term = $param['term'];
        $parameters = $options['parameters'];

        if($parameters['tag_group']) $tag_group = $parameters['tag_group'];
        else $tag_group = 'tags';

        $items = $this->get_tags(array(
            //'data_only' => true,
            'select_fields' => array('pk_tag_id as id','tag_title as label'),
            'tag_group_slug' => $tag_group,
            'LIKE' => array('tag_title' => $term),
            'limit' => array('start' => 0, 'count' => 200)
            ));

        $items = $items['data'];

        return $items;
        }
    function get_tags_by_group($group_slug, $id_title_only = true){

        return $this->get_tags(array(
            'tag_group_slug' => $group_slug,
            'select_fields' => $id_title_only ? array('pk_tag_id', 'tag_title') : null,
            'data_only' => true,
            ));

        }
	function get_tags($param = array()){
        //if(!$param['tag_group']) $param['tag_group'] = 'tags';

        if($param['tag_group_slug']){
            $param['tag_group'] = $this->get_tag_group_id_by_slug($param['tag_group_slug']);
            if(!$param['tag_group']) return array('data' => array());
            }

        $cacheID = null;
        if($param['tag_id']) $cacheID = 'tag_by_id_'.$param['tag_id'];
        if($param['tag_group']) $cacheID = 'all_tags_by_group_id_'.$param['tag_group'];

        if(false && $cacheID){
            if($param['select_fields']) $cacheID .= '_selected';
            $data = getCache($cacheID);
            //return $cacheID;
            if(hasCache($data)) return $data;
            if(isset($param['data_only'])) unset($param['data_only']);
            }
        $select_fields = $param['select_fields'] ? implode(',',$param['select_fields']) : '*';
        $from = array('dev_tags','dev_tags_group');
        $condition = " dev_tags.fk_tag_group_id = dev_tags_group.pk_tag_group_id ";

        if($param['used_only']){
            $from[] = 'dev_content_tag_relation';
            $condition .= " AND dev_tags.pk_tag_id = dev_content_tag_relation.fk_tag_id";
            if($param['used_only_type'])
                $condition .= " AND dev_content_tag_relation.tag_type = '".$param['used_only_type']."'";
            if(!$param['group_by']) $param['group_by'] = array();
            $param['group_by'][] = 'pk_tag_id';
            }
        $condition .= " AND 1 ";

		$sql = "SELECT ".$select_fields." FROM ".implode(',', $from)." WHERE  ";
		$count_sql = "SELECT COUNT(pk_tag_id) AS TOTAL FROM ".implode(',', $from)." WHERE ";

        $loop_condition = array(
            'tag_id' => 'pk_tag_id',
            'tag_title' => 'tag_title',
            'tag_group' => 'fk_tag_group_id',
            );

		$data = process_sql_operation($loop_condition,$condition,$sql,$count_sql,$param);

		//if($cacheID) setCache($data, $cacheID);
		return $data;
		}
    function get_tag_form($param = array()){
        $posted_data = $param['post'] ? $param['post'] : array();
        $url_data = $param['get'] ? $param['get'] : array();

        $param = array_merge($param, $posted_data, $url_data);

        $content = array();
        if($param['tag_id']){
            $content = $this->get_tags(array('tag_id' => $param['tag_id'], 'single' => true));
            }

        global $multilingualFields, $_config;

        if($content){
            if($multilingualFields['dev_tags']){
                foreach($content as $index=>$value){
                    if(in_array($index, $multilingualFields['dev_tags']) !== false)
                        $content[$index] = processToRender($content[$index]);
                    }
                }
            }

        $categories = $this->get_tags(array('tag_group' => $param['tag_group'], 'data_only' => true));
        $categories = $categories['data'];

        $tagGroup = $this->get_tag_group($param['tag_group']);

        ob_start();
        ?>
        <form name="add_edit_tag">
            <div class="form-group">
                <label>New</label>
                <input type="text" name="tag_title" id="tag_title" class="form-control" value="<?php echo $content ? $content['tag_title'] : ''?>" />
            </div>
            <?php if($tagGroup['is_hierarchical']): ?>
            <div class="form-group">
                <label>Parent</label>
                <select class="form-control" name="fk_tag_id" id="fk_tag_id">
                    <option value="">--None--</option>
                    <?php echo get_tags_in_hierarchy_select($content['fk_tag_id'], $categories); ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
        <?php
        return array('success' => ob_get_clean());
    }
    function add_edit_tags($param = array()){
        $posted_data = $param['post'] ? $param['post'] : array();
        $url_data = $param['get'] ? $param['get'] : array();

        $param = array_merge($param, $posted_data, $url_data);

        global $devdb;

        $ret = array('error' => array(), 'success' => array());

        $is_update = $param['tag_id'] ? $param['tag_id'] : null;

        $content = array();
        if($is_update){
            $content = $this->get_tags(array('tag_id' => $is_update, 'single' => true));
            }

        if(!$param['tag_group']) $ret['error'][] = 'Tag Group is Required';

        if(!$param['tag_slug']) $param['tag_slug'] = $param['tag_title'];
        $param['tag_slug'] = form_modifiers::slug($param['tag_slug']);

        //validating
        $temp = form_validator::required($param['tag_title']);
        if($temp !== true)
            $ret['error'][] = 'Tag Title '.$temp;

        $cond = $is_update ? " pk_tag_id != '".$is_update."'" : '';
        $temp = form_validator::unique($param['tag_slug'],'dev_tags','tag_slug',$cond);
        if($temp !== true) $param['tag_slug'] = $temp;

        if($ret['error']) return $ret;

        $tag_group_data = $this->get_tag_group($param['tag_group']);
        if(!$tag_group_data){
            $tag_group = $this->get_tag_group_id_by_slug($param['tag_group']);

            if(!$tag_group){
                $tagGroupData = array(
                    'tag_group_title' => $param['tag_group'],
                    'tag_group_slug' => $param['tag_group'],
                    );

                $ret = $this->add_edit_tag_group($tagGroupData);

                if($ret['success']) $tag_group = $ret['success'];
                else $ret['error'][] = 'Could not create Tag Group';
                }
            }
        else $tag_group = $param['tag_group'];

        if($ret['error']) return $ret;

        $insert_data = array(
            'fk_tag_id' => $param['fk_tag_id'] ? $param['fk_tag_id'] : '0',
            'tag_title' => processToStore(($content ? $content['tag_title'] : ''), $param['tag_title']),
            'tag_slug' => $param['tag_slug'],
            'fk_tag_group_id' => $tag_group,
            'tag_description' => processToStore(($content ? $content['tag_description'] : ''), $param['tag_description']),
            'tag_image' => $param['tag_image'],
            );

        if($is_update){
            $ret = $devdb->insert_update('dev_tags',$insert_data," pk_tag_id = '".$is_update."'");
            }
        else{
            $ret = $devdb->insert_update('dev_tags',$insert_data);
            }

        if($ret['success']){
            $tagID = $is_update ? $is_update : $ret['success'];

            removeCache('tag_by_id_'.$tagID);
            removeCache('tag_by_id_'.$tagID.'_selected');
            removeCache('all_tags_by_group_id_'.$tag_group.'_selected');
            removeCache('all_tags_by_group_id_'.$tag_group);

            if($param['for_autocomplete'])
                return array('success' => array('id' => $is_update ? $is_update : $ret['success'], 'label' => $param['tag_title']));
            else{
                $insert_data['pk_tag_id'] = $ret['success'];
                return array('success' => $insert_data);
                }
            }
        else return $ret;
        }
	function add_edit_tag_group($param){
		global $devdb;
		
		if($param['pk_tag_group_id']){
			//update
			$data = array(
				'tag_group_title' => $param['tag_group_title'],
				'tag_group_slug' => $param['tag_group_slug'],
				);	
			$ret = $devdb->insert_update('dev_tags_group',$data);
			removeCache('all_tag_groups');
			cleanCache('single_tag_group');
			}
		else{
			//add
			$data = array(
				'tag_group_title' => $param['tag_group_title'],
				'tag_group_slug' => $param['tag_group_slug'],
				);	
			$ret = $devdb->insert_update('dev_tags_group',$data);
            removeCache('all_tag_groups');
            cleanCache('single_tag_group');
			}
		
		return $ret;
		}
	}
new dev_tag_management;

function get_tags_in_hierarchy(&$tags, $parent = 0){
    //TODO: Cache required
    $output = array();
    foreach($tags as $i=>$v){
        if($v['fk_tag_id'] == $parent){
            $output[$i] = $v;
            unset($tags[$i]);
            $output[$i]['child'] = get_tags_in_hierarchy($tags, $v['pk_tag_id']);
            }
        }
    return $output;
    }

function get_tags_in_hierarchy_select($selected = null, &$tags, $parent = 0, $level = 0){
    //TODO: Cache required
    $output = '';
    $indents = '';
    for($i=0;$i<($level*3);$i++){$indents .= '&nbsp;';}
    foreach($tags as $i=>$v){
        if($v['fk_tag_id'] == $parent){
            $output .= '<option value="'.$v['pk_tag_id'].'" '.($selected && $selected == $v['pk_tag_id'] ? 'selected' : '').'>'.$indents.processToRender($v['tag_title']).'</option>';
            unset($tags[$i]);
            $output .= get_tags_in_hierarchy_select($selected, $tags, $v['pk_tag_id'], $level+1);
            }
        }
    return $output;
    }