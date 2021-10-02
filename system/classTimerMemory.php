<?php
/*
 * Timer Class
 *
 * This class used to keep track of time elapsed.
 *
 * You can create an object with an ID for the timer as the only parameter; such as
 *      $myTimer = new Timer('My_Timer');
 *
 * You can stop the timer like following;
 *      $myTimer->_stop();
 *
 * You can get the elapsed time for you timer as follows,
 *      $elapsedTime = $myTimer->elapsed_time;
 * */
class Timer {
    public $start_time = 0;
    public $end_time = 0;
    public $elapsed_time = 0;
    static $allTimers = array();
    function __construct($name = 'Default'){
        self::$allTimers[$name] = $this;
        $this->start_time = microtime_flot();
        }
    function _stop(){
        $this->end_time = microtime_flot();
        $this->elapsed_time = $this->end_time - $this->start_time;
        }
    }

function allTimers(){
    if(Timer::$allTimers){
        echo "<!--\n----------------\n";
        echo "Times\n";
        echo "--------------------\n";
        foreach(Timer::$allTimers as $i=>$v){
            echo $i.' - '.$v->elapsed_time."\n";
            }
        echo "-->";
        }
    }

/**************************************************************************/

/*
 * Memory class
 *
 * You can use this class to keep track memory used while running the system
 * */
class memory {
    var $start_memory = 0;
    var $end_memory = 0;
    var $memory_taken = 0;
    static $allMemory = array();

    function __construct($name = 'Default'){
        self::$allMemory[$name] = $this;
        $this->start_memory = memory_get_usage();
        }

    function stop($to = 'B'){
        $this->end_memory = memory_get_usage();
        $this->memory_taken = ($this->end_memory)-($this->start_memory);
        if($to != 'B'){
            if($to == 'KB') $this->memory_taken /= 1024;
            if($to == 'MB') $this->memory_taken /= (1024*1024);
            if($to == 'GB') $this->memory_taken /= (1024*1024*1024);
            }
        return $this->memory_taken;
        }
    }

function allMemories(){
    if(memory::$allMemory){
        echo "<!--\n----------------\n";
        echo "Memory\n";
        echo "--------------------\n";
        foreach(memory::$allMemory as $i=>$v){
            echo $i.' - '.$v->memory_taken."\n";
            }
        echo "-->";
        }
    }