<?php
//include_once "ezSQL/ez_sql_core.php";
//include_once "ezSQL/ez_sql_mysqli.php";
include_once "dev_mysqli.php";

if(getProjectSettings('db')){
    $devdb = new dev_mysqli(getProjectSettings('db,user'),getProjectSettings('db,password'),getProjectSettings('db,database'),getProjectSettings('db,host'));
    $connected = $devdb->connect();
    if(!$connected){
        trigger_error('Sorry, we could not connect to Database Server at this moment.', E_USER_ERROR);
        exit();
        }
    else{
        $dbSelected = $devdb->select();
        if(!$dbSelected){
            trigger_error('Sorry, we could not connect to Database at this moment.', E_USER_ERROR);
            exit();
            }
        }

    $devdb->query('SET SESSION sql_mode = ""');
    }
else{
    trigger_error('No database configuration found.', E_ERROR);
    exit();
    }

function db_dump(){
    global $devdb, $_config;

    ?>
    <h1>Queries</h1>
    <h2>Total: <?php echo $devdb->num_queries ?></h2>
    <table border="1" cellspacing="0" cellpadding="5">
        <?php
        foreach($devdb->all_queries as $i=>$v){
            $v['query'] = preg_replace('!\s+!', ' ', str_replace("\n",'',$v['query']));
            ?>
            <tr>
                <td><?php echo $v['query'] ?></td>
                <td>
                    <?php
                    foreach($v['source'] as $source){
                        echo $source['line'].' - '.$source['file'].'<hr />';
                        }
                    ?>
                </td>
            </tr>
            <?php
            }
        ?>
    </table>
    <?php
    //pre($_config,0);
    //pre($_SESSION,0);
    }
if($SYSTEM_DEBUG) register_shutdown_function('db_dump');