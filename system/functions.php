<?php
function autoCompleteSubmitDataToRefillData($ids, $labels, $toJSON = true){
    $output = array();
    if($ids){
        if(is_array($ids)){
            foreach($ids as $i=>$v){
                array_push($output, array('id' => $v, 'label' => $labels[$i]));
                }
            }
        else array_push($output, array('id' => $ids, 'label' => $labels));
        }
    return $toJSON ? to_json_object($output) : $output;
    }
	
function buttonGenerator($config = array()){
    $defaultConfig = array(
        'type' => 'link', //link, button
        'button_type' => 'submit', //submit,reset,button
        'href' => 'javascript:',
        'action' => 'navigation', //active,add,navigation,edit,update,delete,remove,inactive,disabled,returned,not_verified
        'attributes' => array(), //send as attribue=>value pair
        'id' => '',
        'size' => 'xs',
        'classes' => '',//space separated or array
        'title' => '',
        'icon' => '',
        'text' => '',
        'name' => '',
        'value' => '',
        );
    $config = array_replace_recursive($defaultConfig, $config);

    $additionalClasses = $config['classes'] ? (is_array($config['classes']) ? implode(' ', $config['classes']) : $config['classes']) : '';

    $class = ' class="'.implode(' ',array('btn', 'btn-flat', 'btn-labeled', 'btn-'.$config['size'], 'btn-'.has_project_settings('action_colors,'.$config['action']), $additionalClasses)).'" ';
    $id = ' id="'.$config['id'].'" ';
    $href = ' href="'.$config['href'].'" ';
    $title = ' title="'.$config['title'].'" ';
    $button_type = ' type="'.$config['button_type'].'" ';
    $icon = getProjectSettings('action_icons,'.$config['icon'], $config['icon']);
    $attributes = array();
    $name = ' name="'.$config['name'].'" ';
    $value = ' value="'.$config['value'].'" ';
    if($config['attributes']){
        foreach($config['attributes'] as $i=>$v){
            $attributes[] = $i.'="'.$v.'"';
            }
        }
    $attributes = implode(' ', $attributes);

    if($config['type'] == 'link')
        $output = '<a '.$class.$id.$href.$title.$attributes.'><i class="btn-label fa fa-'.$icon.'"></i>'.$config['text'].'</a>';
    elseif($config['type'] == 'button')
        $output = '<button '.$name.$value.$button_type.$class.$id.$title.$attributes.'><i class="btn-label fa fa-'.$icon.'"></i>'.$config['text'].'</button>';

    return $output;
    }
function resetButtonGenerator($config = array()){
    $config['type'] = 'button';
    $config['button_type'] = 'reset';
    return buttonGenerator($config);
    }
function buttonButtonGenerator($config = array()){
    $config['type'] = 'button';
    $config['button_type'] = 'button';
    return buttonGenerator($config);
    }
function submitButtonGenerator($config = array()){
    $config['type'] = 'button';
    $config['button_type'] = 'submit';
    return buttonGenerator($config);
    }
function linkButtonGenerator($config = array()){
    $config['type'] = 'link';
    return buttonGenerator($config);
    }
/* SQL Related */
function getTablePrimaryKey($table){
    global $devdb;
    $sql = "SHOW KEYS FROM ".$table." WHERE Key_name = 'PRIMARY'";
    $data = $devdb->get_row($sql);
    if($data) return $data['Column_name'];
    else return null;
    }
function sql_group_by($param){
    $group_by_sql = '';

    if($param['group_by']){
        $group_by_sql = ' GROUP BY ' . implode(', ', $param['group_by']);
        }

    return $group_by_sql;
    }
function sql_order_by($param, $default_col = null, $default_order = null){
    $order_by_sql = '';

    if($param['order_by']){
        if($param['order_by']['col']){
            $order_by_sql = " ORDER BY ".$param['order_by']['col']." ";
            $order_by_sql .= $param['order_by']['order'] ? ' '.$param['order_by']['order'] : ($default_order ? ' '.$default_order : ' ASC');
            }
        }
    elseif($param['multi_order_by'] || $param['multiple_order_by']){
        if($param['multiple_order_by']) $param['multi_order_by'] = $param['multiple_order_by'];
        $order_by_sql = ' ORDER BY ';
        foreach($param['multi_order_by'] as $order_by){
            $order_by_sql .= $order_by['col']." ";
            $order_by_sql .= $order_by['order'] ? ' '.$order_by['order'] : ($default_order ? ' '.$default_order : ' ASC');
            $order_by_sql .= ', ';
            }
        $order_by_sql = rtrim($order_by_sql,', ');
        }
    elseif($default_col != null){
        $order_by_sql = " ORDER BY ".$default_col." ";
        $order_by_sql .= $default_order ? ' '.$default_order : ' ASC';
        }

    return $order_by_sql;
    }
function sql_limit_by($param, $default_start = null, $default_count = null){
    $limit_sql = '';
    if(is_array($param['limit'])){
        $limit_sql = " LIMIT ".($param['limit']['start'] ? $param['limit']['start'] : ($default_start ? $default_start : 0)).", ".($param['limit']['count'] ? $param['limit']['count'] : ($default_count ? $default_count : 10));
        }
    elseif($default_start != null){
        $limit_sql = " LIMIT ".$default_start.", ".($default_count ? $default_count : 10);
        }

    return $limit_sql;
    }
function sql_condition_maker($loop_condition = array(), $param = array()){
    $loop_condition = !$loop_condition ? array() : $loop_condition;
    $condition = '';

    $EQUAL_IN = null;
    $NOT_EQUAL_IN = null;
    $LIKE = null;
    $COMBINED_LIKE = null;
    $NOT_LIKE = null;
    $STARTED_LIKE = null;
    $NOT_STARTED_LIKE = null;
    $DATE_RANGE_FILTER = null;
    $OPERATOR = null;
    $GROUP_LIKE = null;
    $BETWEEN_INCLUSIVE = null;
    $BETWEEN_EXCLUSIVE = null;
    $LESS_THAN = null;
    $LESS_THAN_EQUAL = null;
    $GREATER_THAN = null;
    $GREATER_THAN_EQUAL = null;

    $COMBINED_LIKE_OPERATOR = $param['COMBINED_LIKE_OPERATOR'] ? ' '.$param['COMBINED_LIKE_OPERATOR'].' ' : ' AND ';

    if(isset($param['GREATER_THAN_EQUAL'])){
        $GREATER_THAN_EQUAL = $param['GREATER_THAN_EQUAL'];
        unset($param['GREATER_THAN_EQUAL']);
        }
    if(isset($param['GREATER_THAN'])){
        $GREATER_THAN = $param['GREATER_THAN'];
        unset($param['GREATER_THAN']);
        }
    if(isset($param['LESS_THAN_EQUAL'])){
        $LESS_THAN_EQUAL = $param['LESS_THAN_EQUAL'];
        unset($param['LESS_THAN_EQUAL']);
        }
    if(isset($param['LESS_THAN'])){
        $LESS_THAN = $param['LESS_THAN'];
        unset($param['LESS_THAN']);
        }
    if(isset($param['BETWEEN_INCLUSIVE'])){
        $BETWEEN_INCLUSIVE = $param['BETWEEN_INCLUSIVE'];
        unset($param['BETWEEN_INCLUSIVE']);
        }
    if(isset($param['BETWEEN_EXCLUSIVE'])){
        $BETWEEN_EXCLUSIVE = $param['BETWEEN_EXCLUSIVE'];
        unset($param['BETWEEN_EXCLUSIVE']);
        }
    if(isset($param['COMBINED_LIKE'])){
        $COMBINED_LIKE = $param['COMBINED_LIKE'];
        unset($param['COMBINED_LIKE']);
        }
    if(isset($param['OPERATOR'])){
        $OPERATOR = $param['OPERATOR'];
        unset($param['OPERATOR']);
        }
    if(isset($param['DATE_RANGE'])){
        $DATE_RANGE_FILTER = $param['DATE_RANGE'];
        unset($param['DATE_RANGE']);
        }
    if(isset($param['NOT'])){
        $NOT_EQUAL_IN = $param['NOT'];
        unset($param['NOT']);
        }
    if(isset($param['LIKE'])){
        $LIKE = $param['LIKE'];
        unset($param['LIKE']);
        }
    if(isset($param['GROUP_LIKE'])){
        $GROUP_LIKE = $param['GROUP_LIKE'];
        unset($param['GROUP_LIKE']);
        }
    if(isset($param['STARTED_LIKE'])){
        $STARTED_LIKE = $param['STARTED_LIKE'];
        unset($param['STARTED_LIKE']);
        }
    if(isset($param['NOT_LIKE'])){
        $NOT_LIKE = $param['NOT_LIKE'];
        unset($param['NOT_LIKE']);
        }
    if(isset($param['NOT_STARTED_LIKE'])){
        $NOT_STARTED_LIKE = $param['NOT_STARTED_LIKE'];
        unset($param['NOT_STARTED_LIKE']);
        }

    $EQUAL_IN = $param;

    if($BETWEEN_INCLUSIVE){
        foreach($BETWEEN_INCLUSIVE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ( ".$loop_condition[$i]." >= '".$v['left']."' AND ".$loop_condition[$i]." <= '".$v['right']."' )";
                }
            }
        }
    if($BETWEEN_EXCLUSIVE){
        foreach($BETWEEN_EXCLUSIVE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ( ".$loop_condition[$i]." > '".$v['left']."' AND ".$loop_condition[$i]." < '".$v['right']."' )";
                }
            }
        }

    if($LESS_THAN){
        foreach($LESS_THAN as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ".$loop_condition[$i]." < '".$v."' ";
                }
            }
        }

    if($LESS_THAN_EQUAL){
        foreach($LESS_THAN_EQUAL as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ".$loop_condition[$i]." <= '".$v."' ";
                }
            }
        }

    if($GREATER_THAN){
        foreach($GREATER_THAN as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ".$loop_condition[$i]." > '".$v."' ";
                }
            }
        }

    if($GREATER_THAN_EQUAL){
        foreach($GREATER_THAN_EQUAL as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $condition .= " AND ".$loop_condition[$i]." >= '".$v."' ";
                }
            }
        }

    //Processing COMBINED LIKE
    if($COMBINED_LIKE){
        $each_condition = array();
        foreach($COMBINED_LIKE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v))
                $each_condition[] = $loop_condition[$i]." LIKE '%".$v."%'";
            }
        if($each_condition) $condition .= " AND ( ".implode(' '.$COMBINED_LIKE_OPERATOR.' ', $each_condition)." )";
        }

    //Processing EQUAL IN
    if($EQUAL_IN){
        foreach($EQUAL_IN as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                if(is_array($v))
                    $condition .= " AND ".$loop_condition[$i]." IN ('".implode("','",$v)."')";
                else $condition .= " AND ".$loop_condition[$i]." = '".$v."'";
                }
            }
        }

    //Processing Date Range
    if($DATE_RANGE_FILTER){
        foreach($DATE_RANGE_FILTER as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                $derivedDateRange = date_filter($v);
                if($derivedDateRange){
                    if(isset($derivedDateRange['start']) && isset($derivedDateRange['end'])){
                        $condition .= " AND (".$loop_condition[$i]." >= '".date_to_db($derivedDateRange['start'])."' AND ".$loop_condition[$i]." <= '".date_to_db($derivedDateRange['end'])."') ";
                        }
                    }
                }
            }
        }

    //Processing NOT EQUALS
    if($NOT_EQUAL_IN){
        foreach($NOT_EQUAL_IN as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                if(is_array($v))
                    $condition .= " AND ".$loop_condition[$i]." NOT IN ('".implode("','",$v)."')";
                else $condition .= " AND ".$loop_condition[$i]." != '".$v."'";
                }
            }
        }

    //Processing LIKES
    if($LIKE){
        foreach($LIKE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v))
                if(is_array($loop_condition[$i])){
                    $condition .= " AND ".$loop_condition[$i]." LIKE '%".$v."%'";
                    }
                else $condition .= " AND ".$loop_condition[$i]." LIKE '%".$v."%'";
            }
        }

    //Processing NOT LIKES
    if($NOT_LIKE){
        foreach($NOT_LIKE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v))
                $condition .= " AND ".$loop_condition[$i]." NOT LIKE '%".$v."%'";
            }
        }

    //Processing STARTED LIKES
    if($STARTED_LIKE){
        foreach($STARTED_LIKE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                if(isset($v) && $v !== null){
                    $condition .= " AND ".$loop_condition[$i]." LIKE '".$v."%'";
                    }
                }
            }
        }

    //Processing NOT STARTED LIKES
    if($NOT_STARTED_LIKE){
        foreach($NOT_STARTED_LIKE as $i=>$v){
            if(isset($loop_condition[$i]) && !is_null($v)){
                if(isset($v) && $v !== null){
                    $condition .= " AND ".$loop_condition[$i]." NOT LIKE '".$v."%'";
                    }
                }
            }
        }

    return $condition;
    }
function sql_data_collector($sql = '', $count_sql = '', $param = array(), $retAs = ARRAY_A){
    global $devdb;
    $data = array();
    $dataOnly = isset($param['data_only']) ? $param['data_only'] : false;

    if($param['sql_only'])
        return $sql;

    if($param['count_only']){
        $ret = $devdb->get_var($count_sql);
        return $ret ? $ret : 0;
        }

    if($param['single'] && $sql)
        $data = $devdb->get_row($sql, $retAs);
    else{
        if($sql){
            $data['data'] = $param['index_with'] ? $devdb->get_results($sql, $param['index_with'], $retAs) : $devdb->get_results($sql, null, $retAs);
            $data['result_total'] = $devdb->last_total_rows;
            }

        if(!$dataOnly && $count_sql){
            $data['total'] = $devdb->get_var($count_sql);
            $data['total'] = $data['total'] ? $data['total'] : 0;
            }
        }

    return $data;
    }
function process_sql_operation($loop_condition = array(), $condition = '', $sql = '', $count_sql = '', $param = array()){
    if($loop_condition === null) $loop_condition = array();
    if($condition === null) $condition = '';
    //if($sql === null) $sql = '';
    //if($count_sql === null) $count_sql = '';
    if($param === null) $param = array();

    $condition .= sql_condition_maker($loop_condition, $param);

    $order = sql_order_by($param);
    $limit = sql_limit_by($param);
    $groupBy = sql_group_by($param);

    $sql = $sql ? $sql.$condition.$groupBy.$order.$limit : $sql;
    $count_sql = $count_sql ? $count_sql.$condition.$groupBy : $count_sql;

    $data = sql_data_collector($sql, $count_sql, $param);

    if($param['add_sql']) $data['sql'] = $sql;

    return $data;
    }
/********************/

/* Date Time Datetime Related */
function date_filter($dateRange, $addTime = 0){
    global $_config;

    $startDate = '';
    $endDate = date('d-m-Y');
    if($dateRange == 'today'){
        $startDate = date('d-m-Y');
        $endDate = date('d-m-Y');
        }
    elseif($dateRange == 'yesterday'){
        $startDate = date_minus_days(date('d-m-Y'),1);
        $endDate = date_minus_days(date('d-m-Y'),1);
        }
    elseif($dateRange == 'tomorrow'){
        $startDate = date_add_days(date('d-m-Y'),1);
        $endDate = date_add_days(date('d-m-Y'),1);
        }
    elseif($dateRange == 'upcoming'){
        $startDate = null;
        $endDate = date_minus_days(date('d-m-Y'),1);
        }
    elseif($dateRange == 'earlier'){
        $startDate = date_add_days(date('d-m-Y'),1);
        $endDate = null;
        }
    elseif($dateRange == 'this_year'){
        $startDate = date('01-01-Y');
        $endDate = date('31-12-Y');
        }
    elseif($dateRange == 'next_7_days'){
        $startDate = date('d-m-Y');
        $endDate = date_add_days(date('d-m-Y'),7);
        }
    elseif($dateRange == 'next_15_days'){
        $startDate = date('d-m-Y');
        $endDate = date_add_days(date('d-m-Y'),15);
        }
    elseif($dateRange == 'next_30_days'){
        $startDate = date('d-m-Y');
        $endDate = date_add_days(date('d-m-Y'),30);
        }
    elseif($dateRange == 'next_6_months'){
        $startDate = date('d-m-Y');
        $endDate = date_add_months(date('d-m-Y'),6);
        }
    elseif($dateRange == 'next_12_months'){
        $startDate = date('d-m-Y');
        $endDate = date_add_months(date('d-m-Y'),12);
        }
    elseif($dateRange == '7')//last 7 days
        $startDate = date('d-m-Y', strtotime('-7 days'));
    elseif($dateRange == '30')//last 30 days
        $startDate = date('d-m-Y', strtotime('-30 days'));
    elseif($dateRange == 'this_month'){
        $startDate = date('01-m-Y');
        $endDate = date('t-m-Y');
        }
    elseif($dateRange == 'last_month'){
        $startDate = date('d-m-Y',strtotime('first day of previous month'));
        $endDate = date('d-m-Y',strtotime('last day of previous month'));
        }
    elseif($dateRange == 'this_session'){
        $startDate = date_to_user($_config['current_academic_session']['session_opening_date']);
        $endDate = date_to_user($_config['current_academic_session']['session_closing_date']);
        }
    elseif($dateRange == 'all_time'){
        return null;
        }
    elseif(is_array($dateRange)){
        if(isset($dateRange['start']) && isset($dateRange['end'])){
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            }
        else{
            $startDate = $dateRange['date'];
            $endDate = $dateRange['date'];
            }
        }
    if($addTime){
        $startDate .= ' 00:00:00';
        $endDate .= ' 23:59:59';
        }
    return array('start' => $startDate, 'end' => $endDate);
    }
function extract_date($date){
    $data = explode(' ',$date);
    return $data[0];
    }
function date_to_db($date, $explode_by = '-', $join_by = '-'){
    //user input : 15-04-2014
    //db output : 2014-04-15
    list($d,$m,$Y) = explode($explode_by,$date);
    if($d > 31) return $date;
    else return $Y.$join_by.$m.$join_by.$d;
    }
function datetime_to_db($data, $explode_by = '-', $join_by = '-'){
    list($date,$time) = explode(' ',$data);
    list($d,$m,$Y) = explode($explode_by,$date);
    $date = $Y.$join_by.$m.$join_by.$d;
    return $date.' '.$time;
    }
function datetime_to_user($data, $explode_by = '-', $join_by = '-'){
    list($date,$time) = explode(' ',$data);
    list($Y,$m,$d) = explode($explode_by,$date);
    $date = $d.$join_by.$m.$join_by.$Y;
    return $date.' '.$time;
    }
function date_to_user($date, $explode_by = '-', $join_by = '-'){
    if(!$date) return null;
    $date = explode(' ',$date);
    $date = $date[0];
    list($Y,$m,$d) = explode($explode_by,$date);
    return $d.$join_by.$m.$join_by.$Y;
    }
function time_to_user($data){
    $data = explode(' ',$data);
    if(isset($data[1])) $data = $data[1];
    else $data = $data[0];

    return $data;
    }
function emptyDate($date){
    $date = str_replace('0000-00-00','',$date);
    if(strlen($date)) return false;
    else return true;
    }
function emptyTime($time){
    $time = str_replace('00:00:00','',$time);
    if(strlen($time)) return false;
    else return true;
    }
function emptyDateTime($datetime){
    $datetime = str_replace('0000-00-00 00:00:00','',$datetime);
    if(strlen($datetime)) return false;
    else return true;
    }
function compare_dates($date1,$date2){
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    if($date1 < $date2) return -1;
    elseif($date1 == $date2) return 0;
    else return 1;
    }
function date_interval($date1,$date2,$inclusive = false){
    $comparision = compare_dates($date1,$date2);

    if($inclusive){
        $date2 = date_add_days($date2,1);
        }
    $start = new DateTime($date1);
    $end = new DateTime($date2);
    $interval = $start->diff($end,true);

    return array(
        'comparision' => $comparision,
        'interval' => $interval
        );
    }
function interval_array($date1,$date2,$inclusive = false){
    $out = date_interval($date1,$date2,$inclusive);

    $datetime['y'] = $out['interval']->y;
    $datetime['m'] = $out['interval']->m;
    $datetime['d'] = $out['interval']->d;
    $datetime['h'] = $out['interval']->h;
    $datetime['i'] = $out['interval']->i;
    $datetime['s'] = $out['interval']->s;
    return $datetime;
    }
function print_date_interval($interval, $y = true, $m = true, $d = true, $h = true, $i = true, $s = false){
    $output = '';
    if($y && $interval->y) $output .= $interval->y.' years ';
    if($m && $interval->m) $output .= $interval->m.' months ';
    if($d && $interval->d) $output .= $interval->d.' days ';
    if($h && $interval->h) $output .= $interval->h.' hours ';
    if($i && $interval->i) $output .= $interval->i.' minutes ';
    if($s && $interval->s) $output .= $interval->s.' seconds ';

    return $output;
    }
function print_date($date,$break_date_time = false, $time = true, $addSymbol = false){
    if(is_array($date)){
        $temp = $date;
        $date = $temp[0] ? $temp[0] : null;
        $break_date_time = isset($temp[1]) ? $temp[1] : false;
        $time = isset($temp[2]) ? $temp[2] : true;
        }
    if(!$date) return;
    $date_output = ($addSymbol ? '<i class="fa fa-calendar"></i> ' : '').date('d M, Y',strtotime($date));
    $time_output = $time ? ($addSymbol ? '<i class="fa fa-clock-o"></i> ' : ' AT ').date('h:i A',strtotime($date)) : '';

    if($break_date_time) return $date_output.'<br />'.$time_output;
    else return $date_output.' '.$time_output;
    }
function get_days($date1,$date2){
    $data1 = explode(' ',$date1);
    $d1= new DateTime ($data1[0]);
    $data2 = explode(' ',$date2);
    $d2= new DateTime ($data2[0]);

    $interval = date_diff($d1, $d2);
    //pre($interval);
    return $interval->format('%R%a');
    }
function get_mins($date1,$date2){
    $datetime1 = strtotime($date1);
    $datetime2 = strtotime($date2);
    $interval  = abs($datetime2 - $datetime1);
    $minutes   = round($interval / 60);

    return $minutes;
    }
function get_time_ago($time){
    $get_interval = interval_array($time,date('Y-m-d H:i:s'));
    if($get_interval['y'] > 0 || $get_interval['m'] || $get_interval['d'] > 0) return 'on '.print_date($time,false,false);
    elseif(($get_interval['d'] == 0) && ($get_interval['h'] >= 2)) return $get_interval['h']. ' hours ago';
    elseif(($get_interval['h'] >= 1) && ($get_interval['h'] < 2)) return 'About an hour ago';
    elseif(($get_interval['h'] == 0) && ($get_interval['i'] >= 2)) return $get_interval['i']. ' minutes ago';
    elseif(($get_interval['i'] >= 1) && ($get_interval['i'] < 2)) return 'About a minute ago';
    elseif(($get_interval['i'] == 0) && ($get_interval['s'] <= 60)) return 'Few seconds ago';

    return null;
    }
function _checkDate($date, $separator = '-', $format = 'Y-m-d'){

    $formatParts = explode($separator, $format);
    $dateParts = explode($separator, $date);

    if($formatParts[0] == 'Y') $y = $dateParts[0];
    elseif($formatParts[0] == 'm') $m = $dateParts[0];
    elseif($formatParts[0] == 'd') $d = $dateParts[0];

    if($formatParts[1] == 'Y') $y = $dateParts[1];
    elseif($formatParts[1] == 'm') $m = $dateParts[1];
    elseif($formatParts[1] == 'd') $d = $dateParts[1];

    if($formatParts[2] == 'Y') $y = $dateParts[2];
    elseif($formatParts[2] == 'm') $m = $dateParts[2];
    elseif($formatParts[2] == 'd') $d = $dateParts[2];

    return checkdate($m, $d, $y);
    }
function _checkTime($time, $_24H = true, $separator = ':'){
    $parts = explode($separator, $time);
    $h = intval($parts[0]);
    $m = isset($parts[1]) ? intval($parts[1]) : null;
    $s = isset($parts[2]) ? intval($parts[2]) : null;

    $max_hour = $_24H ? 24 : 12;
    if($h < 0 || $h > $max_hour) return false;
    if($m < 0 || $m > 59) return false;
    if($s < 0 || $s > 59) return false;

    //if($h == 0 && $m == 0 && $s == 0) return false;

    return true;
    }
function _checkDateTime($dateTime, $dateFormat = 'Y-m-d', $separator = '-'){
    list($date, $time) = explode(' ', $dateTime);
    if(!_checkDate($date, $separator, $dateFormat)) return false;
    if(!_checkTime($time)) return false;

    return true;
    }
function date_add_days($date,$days,$DMY = false){
    if($DMY) return date('d-m-Y',strtotime($date." + ".$days." day"));
    else return date('Y-m-d',strtotime($date." + ".$days." day"));
    }
function date_add_weeks($date,$weeks){
    return date('Y-m-d',strtotime($date." + ".$weeks." week"));
    }
function date_add_months($date,$months){
    return date('Y-m-d',strtotime($date." + ".$months." month"));
    }
function date_add_years($date,$years){
    return date('Y-m-d',strtotime($date." + ".$years." year"));
    }
function date_minus_days($date,$days){
    return date('Y-m-d',strtotime($date." - ".$days." day"));
    }
function date_minus_weeks($date,$weeks){
    return date('Y-m-d',strtotime($date." - ".$weeks." week"));
    }
function date_minus_months($date,$months){
    return date('Y-m-d',strtotime($date." - ".$months." month"));
    }
function date_minus_years($date,$years){
    return date('Y-m-d',strtotime($date." - ".$years." year"));
    }
function microtime_flot(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
function get_age($birthdate){
    $current = new DateTime(date('Y-m-d H:i:s'));
    $otherdate = new DateTime($birthdate);

    $diff = $current->diff($otherdate);

    $result = $diff->y;

    return $result;
}
function datesInRange($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {
        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}
/*----------*/

/* Random */
function randomDate($lower = '2006-10-10', $higher = '2008-10-10'){
    return date('Y-m-d',mt_rand(strtotime($lower),strtotime($higher)));
    }
function randomThis($data){
    return $data[rand(0,count($data)-1)];
    }
function randomString($type = 'numeric',$count = 11){
    $numeric = array(0,1,2,3,4,5,6,7,8,9);
    $alpha = array('a','b','c','d','e','f','g','h','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $ALPHA = array('A','B','C','D','E','F','G','H','J','K','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $alphaMix = array('a','b','c','d','e','f','g','h','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $alphanumeric = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $ALPHAnumeric = array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','J','K','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $alphaMixnumeric = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    $selected = $type;

    $out = '';
    for($i=0;$i<$count;$i++){
        $out .= $selected[rand(0,count($selected)-1)];
        }
    return $out;
    }
function randomEmail($count = 5,$service = 'gmail'){
    return randomString('alpha',$count).'_'.date('YmdHis').'@'.$service.'.com';
    }
/**************/

/* Youtube related */
function youtube_id_from_url($url) {
    $pattern =
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
    ;
    $result = preg_match($pattern, $url, $matches);
    if (false !== $result) {
        return $matches[1];
    }
    return false;
    }
function youtube_video_info($url){
    $toUrl = 'http://www.youtube.com/oembed?url='.urlencode($url).'&format=json';
    return json_decode(@file_get_contents($toUrl));
    }
function youtube_video_info_by_id($vId){
    return json_decode(file_get_contents(sprintf('http://gdata.youtube.com/feeds/api/videos/%s?v=2&alt=jsonc', urlencode($vId))));
    }
function getYouTubeIdFromURL($url){
    $url_string = parse_url($url, PHP_URL_QUERY);
    parse_str($url_string, $args);
    return isset($args['v']) ? $args['v'] : false;
    }
/*****************/

/* Number related */
function numberToCurrency($number){
    return number_format($number, 2, '.', ',');
    }
function currencyToNumber($currency){
    return str_replace(',','',$currency);
    }
function formatNumber($number,$only_number = false,$only_specifier = false){
    $num = 0;
    $specifier = '';

    if($number < 1000)
        $num = $number;
    elseif($number < 1000000){
        $num = $number/1000;
        $specifier = 'K';
        }
    elseif($number < 1000000000){
        $num = $number/1000000;
        $specifier = 'M';
        }
    else{
        $num = $number/1000000000;
        $specifier = 'B';
        }
    @list($a,$b) = explode('.',$num);
    $decimal = (int)substr($b,0,2) ? (int)substr($b,0,2) : null;
    $num = $a.($decimal ? '.'.$decimal : '');

    if($only_number) return $num;
    elseif($only_specifier) return $specifier;
    else return $num.$specifier;
    }
function marks_round($val,$points=2){
    return round($val,$points);
    }
function convert_currency($from, $to = 'BDT', $amount = 0, $api='google'){
    $from_currency = $from;
    $to_currency = $to;
    $amount = $amount ? $amount : 1;
    $converted = '';
    if($from == $to) return $amount;
    if($api == 'google'){
        $results = google_convertCurrency($from_currency,$to_currency,$amount);
        $regularExpression     = '#\<span class=bld\>(.+?)\<\/span\>#s';
        preg_match($regularExpression, $results, $finalData);
        $converted = $finalData[1];
        $converted = explode(' ',$converted);
        $converted = doubleval($converted[0]);
        }

    return $converted;
    }
function google_convertCurrency($from,$to,$amount){
    $url = "http://finance.google.com/finance/converter?a=$amount&from=$from&to=$to";
    $request = curl_init();
    $timeOut = 0;
    curl_setopt ($request, CURLOPT_URL, $url);
    curl_setopt ($request, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($request, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt ($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
    $response = curl_exec($request);
    curl_close($request);
    return $response;
    }
/****************/

/* String related */
function getChangeLanguageLink($toLanguage, $theUrl = null){
    global $_config;

    $theUrl = !$theUrl ? current_url() : $theUrl;
    @list($urlPath, $urlQuery) = explode('?',$theUrl);

    $currentUrlLanguage = null;

    $urlPath = trim(str_replace(_path('root'),'',$urlPath),'/');
    if(strlen($urlPath)){
        if(strpos($urlPath, '/')){
            $currentUrlLanguage = substr($urlPath, 0, strpos($urlPath,'/'));
            $urlPath = substr($urlPath, strlen($currentUrlLanguage));
            }
        else{
            $currentUrlLanguage = $urlPath;
            $urlPath = '';
            }
        }

    if(!strlen($currentUrlLanguage) || in_array($currentUrlLanguage, array_keys($_config['langs'])) === false){
        $urlPath = $currentUrlLanguage.$urlPath;
        $theUrl = _path('root').'/'.$toLanguage.(strlen($urlPath) ? '/'.$urlPath : '').(strlen($urlQuery) ? '?'.$urlQuery : '');
        }
    else{
        $theUrl = preg_replace('/\/'.$currentUrlLanguage.'([\/?])*/',"/".$toLanguage."$1",$theUrl);
        }
    $theUrl = trim($theUrl, '/');
    return $theUrl;
    }
function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
    }
function escapeString($text){
    $text = str_replace("'","\'",$text);
    $text = str_replace('"','\"',$text);

    return $text;
    }
function deEscapeString($text){
    $text = str_replace("\'","'",$text);
    $text = str_replace('\"','"',$text);

    return $text;
    }
function fix_description_image($text, $replacements = array()){
    $root = _path('root').'/';

    $_replacements = array(
        '../../' => $root,
        '../' => $root,
        );
    $_replacements = array_merge_recursive($_replacements, $replacements);

    $text = strtr($text, $_replacements);

    return $text;
    }
function searchResultText($total,$start,$count,$thisPage,$type = 'results'){
    $s = ($count ? ($start * $count)+1 : 0);
    $c = (($start * $count)+$thisPage);
    $total = !$total ? 0 : $total;
    $s = !$total ? 0 : $s;
    return sprintf('Displaying %s <strong>from %d to %d</strong> out of total <strong>%d</strong>', $type, $s, $c, $total);//'Displaying '.$type.' <strong>from '.($count ? ($start * $count)+1 : 0).' to '.(($start * $count)+$thisPage).'</strong> out of total <strong>'.$total.'</strong>';
    }
function activeFilter($name){
    echo $_GET[$name] ? ' activeFilterField ' : '';
    }
function get_sortable_column_link($label, $field, $iconType = 'amount'){
    $getOrderBy = $_GET['order_by'] ? $_GET['order_by'] : '';
    $getOrder = $_GET['order'] ? $_GET['order'] : '';
    $icons = array(
        'amount' => array(
            'ASC' => 'fa-sort-amount-asc',
            'DESC' => 'fa-sort-amount-desc',
            ),
        'alpha' => array(
            'ASC' => 'fa-sort-alpha-asc',
            'DESC' => 'fa-sort-alpha-desc',
            ),
        'numeric' => array(
            'ASC' => 'fa-sort-numeric-asc',
            'DESC' => 'fa-sort-numeric-desc',
            ),
        );
    ?>
    <a class="<?php echo $getOrderBy == $field ? 'activeSortColumn' : '' ?>" href="<?php echo build_url(array('order_by' => $field, 'order' => ($getOrderBy == $field ? ($getOrder == 'ASC' ? 'DESC' : 'ASC') : 'ASC'))) ?>"><?php echo  $label ?>&nbsp;<?php echo ($getOrderBy == $field ? ($getOrder == 'ASC' ? '<i class="fa '.$icons[$iconType]['ASC'].'"></i>' : '<i class="fa '.$icons[$iconType]['DESC'].'"></i>') : '<i class="fa fa-sort"></i>') ?></a>
    <?php
    }
function str_startsWith($string, $query){
    if(substr($string, 0, strlen($query)) === $query) return true;
    else return false;
	}
function remove_rn($s,$replaceWith = ''){
    return preg_replace('/(\\\r\\\n)/i',$replaceWith,$s);
    }
function remove_rn_reverse($s){
    return preg_replace('/(\\r\\\n\)/i','',$s);
    }
function handleEmptyStrings($data, $handle = 'N/A'){
    return strlen($data) ? $data : $handle;
    }
function handleIndexEmptyStrings($data, $index, $handle = 'N/A'){
    if(!$data) return $handle;
    if(isset($data[$index])) return dbReadableString($data[$index], false, $handle);
    else return $handle;
	}
function _mb_substr($string, $start = 0, $length = 100, $suffix = '...'){
    $suffix_len = mb_strlen($suffix);
    $total_len = mb_strlen($string);

    if($total_len <= $length) $suffix = '';

    return mb_substr($string, $start, $length - $suffix_len).$suffix;
    }
function dbReadableString($text, $upper = true, $handle = 'N/A'){
    $text = $upper ? strtoupper($text) : ucwords($text);
    return str_replace('_',' ',handleEmptyStrings($text, $handle));
    }
function hex2rgb($hex, $asString = true) {
    $hex = str_replace("#", "", $hex);

    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    }
    else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    $rgb = array($r, $g, $b);
    if($asString) $rgb = implode(',',$rgb);
    return $rgb;
	}
function reverse($str = null){
    if(!$str) return '';
    $replace = array(
        "'\\",'"\\'
        );
    $replaceWith = array(
        "'",'"'
        );
    return str_replace($replace,$replaceWith,join('', array_reverse(preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY))));
    }
/**********/

/* file folder RELATED */
function get_file_mime_type( $filename, $debug = false ) {
    /*Third Party function from filemanager tinymce plugin*/
    if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) && function_exists( 'finfo_close' ) ) {
        $fileinfo = finfo_open( FILEINFO_MIME );
        $mime_type = finfo_file( $fileinfo, $filename );
        finfo_close( $fileinfo );

        if ( ! empty( $mime_type ) ) {
            if ( true === $debug )
                return array( 'mime_type' => $mime_type, 'method' => 'fileinfo' );
            return $mime_type;
        }
    }
    if ( function_exists( 'mime_content_type' ) ) {
        $mime_type = mime_content_type( $filename );

        if ( ! empty( $mime_type ) ) {
            if ( true === $debug )
                return array( 'mime_type' => $mime_type, 'method' => 'mime_content_type' );
            return $mime_type;
        }
    }

    $mime_types = array(
        'ai'      => 'application/postscript',
        'aif'     => 'audio/x-aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'asc'     => 'text/plain',
        'asf'     => 'video/x-ms-asf',
        'asx'     => 'video/x-ms-asf',
        'au'      => 'audio/basic',
        'avi'     => 'video/x-msvideo',
        'bcpio'   => 'application/x-bcpio',
        'bin'     => 'application/octet-stream',
        'bmp'     => 'image/bmp',
        'bz2'     => 'application/x-bzip2',
        'cdf'     => 'application/x-netcdf',
        'chrt'    => 'application/x-kchart',
        'class'   => 'application/octet-stream',
        'cpio'    => 'application/x-cpio',
        'cpt'     => 'application/mac-compactpro',
        'csh'     => 'application/x-csh',
        'css'     => 'text/css',
        'dcr'     => 'application/x-director',
        'dir'     => 'application/x-director',
        'djv'     => 'image/vnd.djvu',
        'djvu'    => 'image/vnd.djvu',
        'dll'     => 'application/octet-stream',
        'dms'     => 'application/octet-stream',
        'doc'     => 'application/msword',
        'dvi'     => 'application/x-dvi',
        'dxr'     => 'application/x-director',
        'eps'     => 'application/postscript',
        'etx'     => 'text/x-setext',
        'exe'     => 'application/octet-stream',
        'ez'      => 'application/andrew-inset',
        'flv'     => 'video/x-flv',
        'gif'     => 'image/gif',
        'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'hdf'     => 'application/x-hdf',
        'hqx'     => 'application/mac-binhex40',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'ice'     => 'x-conference/x-cooltalk',
        'ief'     => 'image/ief',
        'iges'    => 'model/iges',
        'igs'     => 'model/iges',
        'img'     => 'application/octet-stream',
        'iso'     => 'application/octet-stream',
        'jad'     => 'text/vnd.sun.j2me.app-descriptor',
        'jar'     => 'application/x-java-archive',
        'jnlp'    => 'application/x-java-jnlp-file',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'js'      => 'application/x-javascript',
        'kar'     => 'audio/midi',
        'kil'     => 'application/x-killustrator',
        'kpr'     => 'application/x-kpresenter',
        'kpt'     => 'application/x-kpresenter',
        'ksp'     => 'application/x-kspread',
        'kwd'     => 'application/x-kword',
        'kwt'     => 'application/x-kword',
        'latex'   => 'application/x-latex',
        'lha'     => 'application/octet-stream',
        'lzh'     => 'application/octet-stream',
        'm3u'     => 'audio/x-mpegurl',
        'man'     => 'application/x-troff-man',
        'me'      => 'application/x-troff-me',
        'mesh'    => 'model/mesh',
        'mid'     => 'audio/midi',
        'midi'    => 'audio/midi',
        'mif'     => 'application/vnd.mif',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'audio/mpeg',
        'mp3'     => 'audio/mpeg',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpga'    => 'audio/mpeg',
        'ms'      => 'application/x-troff-ms',
        'msh'     => 'model/mesh',
        'mxu'     => 'video/vnd.mpegurl',
        'nc'      => 'application/x-netcdf',
        'odb'     => 'application/vnd.oasis.opendocument.database',
        'odc'     => 'application/vnd.oasis.opendocument.chart',
        'odf'     => 'application/vnd.oasis.opendocument.formula',
        'odg'     => 'application/vnd.oasis.opendocument.graphics',
        'odi'     => 'application/vnd.oasis.opendocument.image',
        'odm'     => 'application/vnd.oasis.opendocument.text-master',
        'odp'     => 'application/vnd.oasis.opendocument.presentation',
        'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'     => 'application/vnd.oasis.opendocument.text',
        'ogg'     => 'application/ogg',
        'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
        'oth'     => 'application/vnd.oasis.opendocument.text-web',
        'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
        'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott'     => 'application/vnd.oasis.opendocument.text-template',
        'pbm'     => 'image/x-portable-bitmap',
        'pdb'     => 'chemical/x-pdb',
        'pdf'     => 'application/pdf',
        'pgm'     => 'image/x-portable-graymap',
        'pgn'     => 'application/x-chess-pgn',
        'png'     => 'image/png',
        'pnm'     => 'image/x-portable-anymap',
        'ppm'     => 'image/x-portable-pixmap',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'ps'      => 'application/postscript',
        'qt'      => 'video/quicktime',
        'ra'      => 'audio/x-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rgb'     => 'image/x-rgb',
        'rm'      => 'audio/x-pn-realaudio',
        'roff'    => 'application/x-troff',
        'rpm'     => 'application/x-rpm',
        'rtf'     => 'text/rtf',
        'rtx'     => 'text/richtext',
        'sgm'     => 'text/sgml',
        'sgml'    => 'text/sgml',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'silo'    => 'model/mesh',
        'sis'     => 'application/vnd.symbian.install',
        'sit'     => 'application/x-stuffit',
        'skd'     => 'application/x-koan',
        'skm'     => 'application/x-koan',
        'skp'     => 'application/x-koan',
        'skt'     => 'application/x-koan',
        'smi'     => 'application/smil',
        'smil'    => 'application/smil',
        'snd'     => 'audio/basic',
        'so'      => 'application/octet-stream',
        'spl'     => 'application/x-futuresplash',
        'src'     => 'application/x-wais-source',
        'stc'     => 'application/vnd.sun.xml.calc.template',
        'std'     => 'application/vnd.sun.xml.draw.template',
        'sti'     => 'application/vnd.sun.xml.impress.template',
        'stw'     => 'application/vnd.sun.xml.writer.template',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        'swf'     => 'application/x-shockwave-flash',
        'sxc'     => 'application/vnd.sun.xml.calc',
        'sxd'     => 'application/vnd.sun.xml.draw',
        'sxg'     => 'application/vnd.sun.xml.writer.global',
        'sxi'     => 'application/vnd.sun.xml.impress',
        'sxm'     => 'application/vnd.sun.xml.math',
        'sxw'     => 'application/vnd.sun.xml.writer',
        't'       => 'application/x-troff',
        'tar'     => 'application/x-tar',
        'tcl'     => 'application/x-tcl',
        'tex'     => 'application/x-tex',
        'texi'    => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz'     => 'application/x-gzip',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'torrent' => 'application/x-bittorrent',
        'tr'      => 'application/x-troff',
        'tsv'     => 'text/tab-separated-values',
        'txt'     => 'text/plain',
        'ustar'   => 'application/x-ustar',
        'vcd'     => 'application/x-cdlink',
        'vrml'    => 'model/vrml',
        'wav'     => 'audio/x-wav',
        'wax'     => 'audio/x-ms-wax',
        'wbmp'    => 'image/vnd.wap.wbmp',
        'wbxml'   => 'application/vnd.wap.wbxml',
        'wm'      => 'video/x-ms-wm',
        'wma'     => 'audio/x-ms-wma',
        'wml'     => 'text/vnd.wap.wml',
        'wmlc'    => 'application/vnd.wap.wmlc',
        'wmls'    => 'text/vnd.wap.wmlscript',
        'wmlsc'   => 'application/vnd.wap.wmlscriptc',
        'wmv'     => 'video/x-ms-wmv',
        'wmx'     => 'video/x-ms-wmx',
        'wrl'     => 'model/vrml',
        'wvx'     => 'video/x-ms-wvx',
        'xbm'     => 'image/x-xbitmap',
        'xht'     => 'application/xhtml+xml',
        'xhtml'   => 'application/xhtml+xml',
        'xls'     => 'application/vnd.ms-excel',
        'xml'     => 'text/xml',
        'xpm'     => 'image/x-xpixmap',
        'xsl'     => 'text/xml',
        'xwd'     => 'image/x-xwindowdump',
        'xyz'     => 'chemical/x-xyz',
        'zip'     => 'application/zip'
    );

    $ext = strtolower( array_pop( explode( '.', $filename ) ) );

    if ( ! empty( $mime_types[$ext] ) ) {
        if ( true === $debug )
            return array( 'mime_type' => $mime_types[$ext], 'method' => 'from_array' );
        return $mime_types[$ext];
    }

    if ( true === $debug )
        return array( 'mime_type' => 'application/octet-stream', 'method' => 'last_resort' );
    return 'application/octet-stream';
}
function delete_folder($path){
    //Folder Delete (Recursive)
    if (is_dir($path) === true){
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            delete_folder(realpath($path) . '/' . $file);
            }
        return rmdir($path);
        }
    else if (is_file($path) === true) {
        return unlink($path);
        }
    return false;
    }
function url_image($url,$username = null,$overwrite=false){
    $is_saved = false;
    $filename = basename($url);
    $complete_save_loc = _path('uploads','absolute').'/'.($username ? $username.'/' : '').$filename;
    if(file_exists($complete_save_loc)){
        $file = @file_get_contents($url);
        if($file === false) return null;
        if($overwrite == true) $is_saved = file_put_contents($complete_save_loc, $file);
        else{
            $fName = getFilename($url);
            $ext = getExtension($url);
            //TODO: We must use microseconds to be more precise that the added number is unique
            $filename = $fName.'_'.date('H_i_s').'.'.$ext;
            $complete_save_loc = _path('uploads','absolute').'/'.($username ? $username.'/' : '').$filename;
            //$file = @file_get_contents($url);
            //if($file === false) return null;
            $is_saved = file_put_contents($complete_save_loc, $file);
            }
        }
    else{
        $file = @file_get_contents($url);
        if($file === false) return null;
        $is_saved = file_put_contents($complete_save_loc, $file);
        }
    if($is_saved === false) return null;
    else return $filename;
    }
function is_image($file){
    if(!$file) return false;

    $mime_types = array('image/png','image/jpeg','image/jpg','image/gif','image/bmp','image/x-windows-bmp','image/x-icon');
    if(function_exists('getimagesize')){
        $data = getimagesize($file);
        if(in_array($data['mime'],$mime_types)) return true;
        else return false;
        }
    elseif(function_exists('mime_content_type')){
        $data = mime_content_type($file);
        if(in_array($data,$mime_types)) return true;
        else return false;
        }
    }
function isExtension($file,$extention){
    $ext = getExtension($file);
    if(is_array($extention)){
        $found = false;
        foreach($extention as $v){
            if($v == $ext){
                $found = true;
                break;
                }
            }
        return $found;
        }
    elseif($extention == $ext) return true;
    else return false;
    }
function isMime($file, $mime){
    $info = mime_content_type($file);
    if($info == $mime) return true;
    else false;
    }
function isPhp($file){
    $info = mime_content_type($file);
    if($info == 'text/x-php') return true;
    else false;
    }
function getExtension($file){
    $info = pathinfo($file);
    return $info['extension'];
    }
function getFilename($file){
    $info = pathinfo($file);
    return $info['filename'];
    }
function getBasename($file){
    $info = pathinfo($file);
    return $info['basename'];
    }
function copy_move_file($sourceDir,$destinationDir,$do = 'move',$del_dir = false){
    if(!file_exists($destinationDir))
        mkdir($destinationDir, 0777, true);

    if(is_dir($destinationDir)){
        if(is_writable($destinationDir)){
            if ($handle = opendir($sourceDir)) {
                while (false !== ($file = readdir($handle))) {
                    if (is_file($sourceDir . '/' . $file)) {
                        if($do == 'move')
                            rename($sourceDir . '/' . $file, $destinationDir . '/' . $file);
                        else copy($sourceDir . '/' . $file, $destinationDir . '/' . $file);
                        }
                    }
                closedir($handle);

                if($del_dir) rmdir($destinationDir);
                }
            else return false;
        }
        else return false;
        }
    else return false;

    return true;
    }
function get_file_extension($file_name) {
    return substr(strrchr($file_name,'.'),1);
    }
function zip_extractor($file, $path = null, $fileNameAsFolder = true, $delete_zip = true){
    if(!$path){
        $path = dirname($file);
        }
    else{
        if($fileNameAsFolder) $path .= '/'.basename($file,'.zip').'/';
        }
    $ret = array();
    //check if file exists
    if(file_exists($file) === false) $ret['error'][] = 'Given file doesn\'t exist';

    if(!isset($ret['error']) || !$ret['error']){
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === TRUE) {
            // extract it to the path we determined above
            $zip->extractTo($path);
            $zip->close();
            $ret['success'] = true;
            //delete the uploaded zip file
            if($delete_zip) unlink($file);
            }
        else $ret['error'][] = 'Failed to open the ZIP file';
        }

    return !$ret ? $path : $ret;
    }
/* -------------------- */

/* helper functions */
function returnArray(){return array('error' => array(), 'success' => array());}

function doUnserialize($data){
    $data = @unserialize($data);
    if($data === false) return array();
    else return $data;
    }
function print_errors($errors){
    foreach($errors as $e){
        add_notification($e, 'error');
        }
    }
function csvToArray($file, $fileDelimiter = ','){
    $array = $fields = array(); $i = 0;
    $handle = @fopen($file, "r");
    if ($handle) {
        while (($row = fgetcsv($handle, 4096, $fileDelimiter)) !== false) {
            if (empty($fields)) {
                $fields = $row;
                continue;
                }
            foreach ($row as $k=>$value) {
                $array[$i][$fields[$k]] = $value;
                }
            $i++;
            }
        if (!feof($handle)) {
            echo "Error: Unexpected error in Function: ".__FUNCTION__.", File: ".__FILE__.", Line: ".__LINE__."\n";
            }
        fclose($handle);
        }

    return $array;
    }
function _header($loc, $status = null, $exit = true){
    //pre(debug_backtrace());
    http_response_code($status);
    include($loc);
    if($exit) exit();
    }
function _process_toposort($pointer, &$dependency, &$order, &$pre_processing, &$reportError){
    if(in_array($pointer, $pre_processing)){
        //TODO: Find out why the $reportError isn't working
        //we must send back the two colliding jack names
        return false;
        }
    else $pre_processing[] = $pointer;

    if($dependency[$pointer]){
        foreach($dependency[$pointer] as $i=>$v){
            if(isset($dependency[$v])){
                if(!_process_toposort($v, $dependency, $order, $pre_processing, $reportError)) return false;
                }
            if(!in_array($v,$order)) $order[] = $v;
            $preProcessingKey = array_search($v, $pre_processing);
            if($preProcessingKey !== false) unset($pre_processing[$preProcessingKey]);
            }
        }

    if(!in_array($pointer,$order)) $order[] = $pointer;
    $preProcessingKey = array_search($pointer, $pre_processing);
    if($preProcessingKey !== false) unset($pre_processing[$preProcessingKey]);
    return true;
    }
function _topological_sort($data, $dependency, &$reportError = null){
    $order = array();
    $pre_processing = array();
    foreach($data as $i=>$v){
        if(!_process_toposort($i,$dependency,$order, $pre_processing, $reportError)) return false;
        }
    return $order;
    }
function hash_password($pass){
    if(function_exists('password_hash'))
        return password_hash($pass, PASSWORD_BCRYPT);
    else return null;
    }
function verify_password($given_password,$hash, $old = false){
    if($old){
        return md5($given_password) == $hash ? true : false;
        }
    else{
        if(function_exists('password_verify'))
            return password_verify($given_password, $hash);
        else return false;
        }
    }
function openssl_token($length = 25){
    $isStrong = null;
    $bytes = openssl_random_pseudo_bytes($length, $isStrong);
    $hex = bin2hex($bytes);

    return $hex;
    }
function getBrowser(){
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
        }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
        }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
        }

    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
        }
    elseif(preg_match('/Firefox/i',$u_agent)){
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
        }
    elseif(preg_match('/Chrome/i',$u_agent)){
        $bname = 'Google Chrome';
        $ub = "Chrome";
        }
    elseif(preg_match('/Safari/i',$u_agent)){
        $bname = 'Apple Safari';
        $ub = "Safari";
        }
    elseif(preg_match('/Opera/i',$u_agent)){
        $bname = 'Opera';
        $ub = "Opera";
        }
    elseif(preg_match('/Netscape/i',$u_agent)){
        $bname = 'Netscape';
        $ub = "Netscape";
        }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
        }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
            }
        else {
            $version= $matches['version'][1];
            }
        }
    else {
        $version= $matches['version'][0];
        }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'browser'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
        );
    }
function get_domain($url){
    //$url = 'http://google.com/dhasjkdas/sadsdds/sdda/sdads.html';
    $host = parse_url($url, PHP_URL_HOST);
    return $host; // prints 'google.com'
    }
function encryptData($data, $key){
    $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB);
    $encoded = base64_encode($data);

    return $encoded;
    }
function decryptData($data, $key){
    $decoded = base64_decode($data);
    $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB));

    return $decrypted;
    }
function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                //$string .= $remainder < 100 ? $conjunction : $separator;
                $string .= $remainder < 100 ? $conjunction : ' ';
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}
function headerOnError($msg, $to){
    add_notification($msg, 'error');
    header('Location: '.$to);
    exit();
    }
function get_back_class_name($trace = null){
    return $trace[1]['class'];
    }
function get_back_function_name($trace = null){
    if(!$trace) $trace = debug_backtrace();
    return $trace[1]['function'];
    }
function pagination($total, $per_page, $start, $items = 5, $style = 'table_footer'){
    $total_num_pages = ceil($total/$per_page);
    $this_page = $start + 1;

    if($this_page > $total_num_pages) return '';

    if($this_page - 2 > 0) $start_from = $this_page - 2;
    else $start_from = 1;

    if($this_page + 2 <= $total_num_pages) $start_to = $this_page + 2;
    else $start_to = $total_num_pages;

    if($start_from == $start_to) return '';

    if($style == 'basic') {
        $_data = "<div class='pagination'><ul>";

        if($start_from > 1) $_data .= '<li><a href="'.build_url(array('start'=>($start_from - 2))).'">&laquo;</a></li>';

        for($i=$start_from;$i<=$start_to;$i++){
            if($i == $this_page)
                $_data .= '<li class="active"><a href="javascript:">'.$i.'</a></li>';
            else
                $_data .= '<li><a href="'.build_url(array('start'=>($i-1))).'">'.$i.'</a></li>';
        }

        if($start_to < $total_num_pages) $_data .= '<li><a href="'.build_url(array('start'=>($i-1))).'">&raquo;</a></li>';

        $_data .= "</ul></div>";
        }
    elseif($style == 'table_footer'){
        $_data = "<div class='DT-pagination'><ul class='pagination'>";

        if ($start_from > 1) $_data .= '<li class="paginate_button previous"><a href="' . build_url(array('start' => ($start_from - 2))) . '">Previous</a></li>';

        for ($i = $start_from; $i <= $start_to; $i++) {
            if ($i == $this_page)
                $_data .= '<li class="paginate_button active"><a href="javascript:">' . $i . '</a></li>';
            else
                $_data .= '<li class="paginate_button"><a href="' . build_url(array('start' => ($i - 1))) . '">' . $i . '</a></li>';
        }

        if ($start_to < $total_num_pages) $_data .= '<li class="paginate_button next"><a href="' . build_url(array('start' => ($i-1))) . '">Next</a></li>';

        $_data .= "</ul></div>";
    }
    return $_data;
}
function build_url($replace = array(),$delete = array(),$url = null){
    if(!$replace) $replace = array();
    if(!$delete) $delete = array();
    if(!$url) $url = null;

    $cur_url = $url ? $url : current_url();//'http://www.ijc.com/admin?start=2&id=10';//

    $parts = explode('?',$cur_url);
    $cur_url = $parts[0];

    $cur = @$parts[1] ? $parts[1] : NULL;
    if($cur) $cur = explode('&',$cur);

    if(is_array($cur) && is_array($replace)){
        foreach($cur as $i=>$v){
            @$temp_parts = explode('=',$v);
            if(isset($replace[$temp_parts[0]])){
                $temp_parts[1] = $replace[$temp_parts[0]];
                $cur[$i] = implode('=',$temp_parts);
                unset($replace[$temp_parts[0]]);
            }
        }
    }
    elseif(!$cur && is_array($replace)){
        $cur = array();
        foreach($replace as $i=>$v){
            $cur[] = $i.'='.$v;
            unset($replace[$i]);
        }
    }

    if(is_array($replace)){
        foreach($replace as $i=>$v){
            $cur[] = $i.'='.$v;
        }
    }

    if(is_array($cur) && is_array($delete)){
        foreach($delete as $d){
            foreach($cur as $i=>$v){
                @$temp_parts = explode('=',$v);
                if($temp_parts[0] == $d){
                    unset($cur[$i]);
                }
            }
        }
    }

    return $cur_url.($cur ? '?'.implode('&',$cur) : '');
}
function hidden_form_for_current_url($fields=array()){
    $form = "";
    $cur_url = $_SERVER['REQUEST_URI'];//'http://www.ijc.com/admin?start=2&id=10';//
    $parts = explode('?',$cur_url);
    $cur_url = $parts[0];

    $cur = @$parts[1] ? $parts[1] : NULL;
    if($cur) $cur = explode('&',$cur);
    else return $form;

    if(is_array($cur)){
        foreach($cur as $i=>$v){
            @$temp_parts = explode('=',$v);
            if($temp_parts[0] != "" && $temp_parts[1] != ""){
                if($fields){
                    if(in_array($temp_parts[0],$fields))
                        $form .= '<input type="hidden" name="'.$temp_parts[0].'" value="'.$temp_parts[1].'" />';
                }
                //else
                //$form .= '<input type="hidden" name="'.$temp_parts[0].'" value="'.$temp_parts[1].'" />';
            }
        }
    }

    return $form;
}
function filterForm($form_config = array(), $pageVars = array(), $data = array()){
    global $formPro;
    $data = !$data ? $_GET : $data;
    if(is_array($form_config)){
        $form_config = array(
            'noFormTag' => true,
            'noSubmitButton' => true,
            'fields' => $form_config
            );
        }
    ?>
    <div class="panel">
        <div class="panel-heading">
            <span class="panel-title"><i class="panel-title-icon fa fa-filter"></i>&nbsp;Filter</span>
        </div>
        <div class="panel-body p5 pb15 pt15">
            <form class="filter_form" role="form" name="filter_form" method="get" action="">
                <?php
                echo hidden_form_for_current_url($pageVars);
                echo is_array($form_config) ? $formPro->form_creator($form_config, $data) : $form_config;
                ?>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-xs btn-success btn-flat btn-labeled"><i class="btn-label fa fa-filter"></i>FILTER</button>
                    <button type="button" class="btn btn-xs btn-danger btn-flat btn-labeled" onclick="clearFilters(this);"><i class="btn-label fa fa-trash"></i>CLEAR</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
function generateIntelImgTag($img, $sizeParam, $class = '', $id = '', $alt = '', $title = '', $path_folder = 'uploads', $save_dir = null, $alternatives = array()){
    global $_config;
    /*$sizeParts = explode('x', $sizeParam);
    if(isset($sizeParts[0])) unset($sizeParts[0]);
    if(isset($sizeParts[1])) unset($sizeParts[1]);
    $resizingAttributes = $sizeParts ? implode('x', $sizeParts) : '';*/

    ?>
   <!-- <img
            data-img="<?php /*echo $img; */?>"
            data-size="<?php /*echo $sizeParam; */?>"
            data-path="<?php /*echo $path_folder; */?>"
            data-save-dir="<?php /*echo $save_dir; */?>"
            data-alternatives="<?php /*echo json_encode($alternatives); */?>"

            src="<?php /*echo get_image($_config['default_no_image'], $sizeParam) */?>"

            class="processImageRequest <?php /*echo $class; */?>"
            id="<?php /*echo $id; */?>"
            title="<?php /*echo $title; */?>"
            alt="<?php /*echo $alt; */?>" />-->

    <img
            src="<?php echo get_image($img, $sizeParam) ?>"
            class="<?php echo $class; ?>"
            id="<?php echo $id; ?>"
            title="<?php echo $title; ?>"
            alt="<?php echo $alt; ?>" />
    <?php
    }
/**********/

/* Array related */
function object_to_array($data){
    if (is_array($data) || is_object($data)){
        $result = array();
        foreach ($data as $key => $value){
            $result[$key] = object_to_array($value);
            }
        return $result;
        }
    return $data;
    }
function to_json_object($array){
	return json_encode($array, JSON_FORCE_OBJECT);
	}
function nonEmptyImplode($data, $glue = ' '){
    return implode($glue, array_filter($data, function($var){return !($var == "" || $var == null);}));
    }
/*************/

/* Cookie related */
function _setCookie($cookieName, $cookieValue, $cookieDuration){
    $durationIn = strtolower(substr($cookieDuration,-1,1));
    if($durationIn == 's'){
        $cookieDuration = str_replace('s','',$cookieDuration);
        }
    elseif($durationIn == 'm'){
        $cookieDuration = str_replace('m','',$cookieDuration);
        $cookieDuration *= 60;
        }
    elseif($durationIn == 'h'){
        $cookieDuration = str_replace('h','',$cookieDuration);
        $cookieDuration *= 3600;
        }
    elseif($durationIn == 'd'){
        $cookieDuration = str_replace('d','',$cookieDuration);
        $cookieDuration *= 3600 * 24;
        }

    setcookie ($cookieName, $cookieValue, time() + $cookieDuration);
    }
function _getCookie($cookieName){
    if(isset($_COOKIE[$cookieName])) return $_COOKIE[$cookieName];
    else return null;
    }
function _deleteCookie($cookieName){
    unset($_COOKIE[$cookieName]);
    @setcookie($cookieName, null, -1);
    return;
    }
/************/

/* Multi-Language Related */
function processToStore($oldData = '', $newData = '', $language = null){
    if(!$oldData) $oldData = '';
    if(!$newData) $newData = '';
    global $_config;
    if (!$language) $language = $_config['slang'];
    $totalReplaced = 0;
    $find = '/{:' . $language . '}(\X*?){:' . $language . '}/m';
    $replacement = '{:' . $language . '}' . $newData . '{:' . $language . '}';

    $storeData = preg_replace($find,
                $replacement,
                $oldData,
                -1,
                $totalReplaced
                );

    if(!$totalReplaced) $storeData = $oldData.$replacement;

    return $storeData;
    }

function processToRender($data, $language = null, $strictCheck = false){
    global $_config;
    if(!$language) $language = $_config['slang'];
    if(!strlen($data)) return '';

    $outputData = '';
    $matches = array();
    $find = '/{:' . $language . '}(\X*?){:' . $language . '}/m';
    $ret = preg_match($find, $data, $matches);

    if($ret) $outputData = isset($matches[1]) ? $matches[1] : '';
    elseif(!$strictCheck){//no match for required language
        //check match for default language
        $matches = array();
        $find = '/{:' . $_config['dlang'] . '}(\X*?){:' . $_config['dlang'] . '}/m';
        $ret = preg_match($find, $data, $matches);
        if ($ret) $outputData = isset($matches[1]) ? $matches[1] : '';
        else{//no match even for the default language
            //check match for any language, the first one available will be sent back
            $find = '/{:(?>' . implode('|', array_keys($_config['langs'])) . ')}(\X*?){:(?>' . implode('|', array_keys($_config['langs'])) . ')}/m';
            //pre($find);
            $ret = preg_match_all($find, $data, $matches);
            if (!$ret && strlen($data)) $outputData = $data;
            else $outputData = $matches[1][0];
            }
        }

    return $outputData;
    }
/*************/
