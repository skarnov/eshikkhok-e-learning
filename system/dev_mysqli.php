<?php
defined('RESULT_SET') or define('RESULT_SET', 'RESULT_SET');
defined('ARRAY_O') or define('ARRAY_O', 'ARRAY_O');
defined('ARRAY_A') or define('ARRAY_A', 'ARRAY_A');

class dev_mysqli{
    var $dbuser = false;
    var $dbpassword = false;
    var $dbname = false;
    var $dbhost = false;
    var $dbport = false;
    var $encoding = false;
    var $dbh = null;
    var $executed_queries = 0;
    var $query_debug_data = array();

    var $rows_affected = null;
    var $last_insert_id = null;
    var $last_total_rows = null;
    var $last_errors = array();
    var $last_results = null;

    function __construct($dbuser, $dbpassword, $dbname, $dbhost = 'localhost', $encoding = 'utf8', $dbport = 3306){
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->dbport = $dbport;
        $this->encoding = $encoding;
        }
    function connect(){
        $ret = true;

        $this->dbh = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, '', $this->dbport);

        if($this->dbh->connect_errno){
            trigger_error('Error establishing mySQLi database connection. Correct user/password? Correct hostname? Database server running?',E_USER_WARNING);
            $return_val = false;
            }

        $this->dbh->set_charset($this->encoding);

        return $ret;
        }

    function select(){
        $ret = true;

        if(!$this->dbh){
            trigger_error('mySQLi database connection is not active',E_USER_WARNING);
            $ret = false;
            }
        else if(!@$this->dbh->select_db($this->dbname)){
            trigger_error('Could not select the database as active',E_USER_WARNING);
            $ret = false;
            }

        return $ret;
        }

    function quick_connect(){
        $ret = true;

        if (!$this->connect()) $ret = false;
        else if (!$this->select()) $ret = false ;
        else $ret = true;

        return $ret;
        }

    function escape($str){
        if(get_magic_quotes_gpc()){
            $str = stripslashes($str);
            }
        return $this->dbh->escape_string($str);
        }

    function deep_escape($value){
        if(is_array($value)){
            foreach($value as $i=>$v){
                if(is_array($v)) $value[$i] = $this->deep_escape($v);
                else $value[$i] = $this->escape($v);
                }
            }
        else $value = $this->escape($value);

        return $value;
        }

    function sysdate(){
        return 'NOW()';
        }

    function disconnect(){
        $this->executed_queries = 0;
        @$this->dbh->close();
        }

    function query($query = null, $index_with = null, $output = ARRAY_A){
        global $SYSTEM_DEBUG;

        $this->last_errors = array();
        $this->rows_affected = null;
        $this->last_insert_id = null;
        $this->last_total_rows = null;

        if(is_object($this->last_results)){
            $this->last_results->free_result();
            $this->last_results = null;
            }

        if($this->executed_queries >= 500){
            $this->disconnect();
            $this->quick_connect();
            }

        $query = trim($query);

        if($SYSTEM_DEBUG){
            $bt = debug_backtrace();
            $btSave = array();
            foreach($bt as $v){
                $btSave[] = array('line' => $v['line'],'file' => $v['file']);
                }
            $this->query_debug_data[] = array('query' => $query, 'source' => $btSave);
            }

        $this->executed_queries++;

        $this->last_results = @$this->dbh->query($query);

        if($this->last_results === false){
            foreach($this->dbh->error_list as $i=>$v){
                $this->last_errors[] = $v['error'];
                }
            return false;
            }

        // Query was a Data Manipulation Query (insert, delete, update, replace, ...)
        if(!is_object($this->last_results)){
            $this->rows_affected = @$this->dbh->affected_rows;

            if(preg_match("/^(insert|replace)\s+/i", $query))
                $this->last_insert_id = @$this->dbh->insert_id;

            return true;
            }
        // Query was a Data Query Query (select, show, ...)
        else{
            if($this->last_results === false){
                foreach($this->dbh->error_list as $i=>$v){
                    trigger_error($v['error'],E_USER_WARNING);
                    }
                }

            $this->last_total_rows = $this->last_results->num_rows;

            if($output == ARRAY_O || $output == ARRAY_A){
                $modified_data = array();
                if($output == ARRAY_O){
                    if($index_with){
                        while($row = $this->last_results->fetch_object()){
                            $modified_data[$row->$index_with] = $row;
                            }
                        $this->last_results->free_result();
                        $this->last_results = null;
                        return $modified_data;
                        }
                    else{
                        $count = 0;
                        while($row = $this->last_results->fetch_object()){
                            $modified_data[$count++] = $row;
                            }
                        $this->last_results->free_result();
                        $this->last_results = null;
                        return $modified_data;
                        }
                    }
                else if($output == ARRAY_A){
                    if($index_with){
                        while($row = $this->last_results->fetch_assoc()){
                            $modified_data[$row[$index_with]] = $row;
                            }
                        $this->last_results->free_result();
                        $this->last_results = null;
                        return $modified_data;
                        }
                    else{
                        $count = 0;
                        while($row = $this->last_results->fetch_assoc()){
                            $modified_data[$count++] = $row;
                            }
                        $this->last_results->free_result();
                        $this->last_results = null;
                        return $modified_data;
                        }
                    }
                }

            //$this->last_results->free_result();

            return true;
            }
        }

    function get_results($query = null, $index_with = null, $output = ARRAY_A){
        if($query){
            $data = $this->query($query, $index_with, $output);
            return $data ? $data : array();
            }
        return null;
        }

    function get_row($query = null, $output = ARRAY_A){
        if($query){
            $data = $this->query($query, null, $output);
            return $data ? $data[0] : array();
            }
        return null;
        }

    function get_var($query = null){
        if($query){
            $data = $this->query($query, null, RESULT_SET);
            if($data){
                while($row = $this->last_results->fetch_object()){
                    if($row){
                        $values = array_values(get_object_vars($row));
                        if(isset($values[0]) && $values[0]!=='') return $values[0];
                        else return null;
                        }
                    break;
                    }
                $this->last_results->free_result();
                $this->last_results = null;
                }
            }
        return null;
        }

    function insert_update($table, $data, $condition = ''){
        $is_update = strlen($condition) ? true : false;

        if($is_update) $sql = "UPDATE ".$table." SET ";
        else $sql = "INSERT INTO ".$table."(";

        if(!$is_update){
            foreach($data as $i=>$v){
                $sql .= "`".$this->escape($i)."`,";
                }
            $sql = rtrim($sql,",").") VALUES(";
            }

        //sanitize $data with escape function
        foreach($data as $i=>$v){
            $v = $this->escape($v);
            $i = $this->escape($i);

            if($is_update)
                $sql .= "".$i."='".$v."', ";
            else
                $sql .= "'".$v."', ";
            }

        $sql = rtrim($sql,", ").(!$is_update ? ')' : '');

        if($is_update) $sql .= " WHERE ".$condition;

        $query = $this->query($sql);

        $ret = array();

        if($query === true)
            $ret['success'] = $is_update ? $this->rows_affected : $this->last_insert_id;
        else
            $ret['error'] = $this->last_errors;

        return $ret;
        }
    }