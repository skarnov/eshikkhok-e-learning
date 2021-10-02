<?php
class dev_site_statistics{
    var $allow_in_admin = false;

	function __construct(){
		jack_register($this);
        }

    function init(){
	    doAction('after_theme_loaded', array($this, 'updateSiteHit'));

        apiRegister($this,'updateSiteHit');

        $args = array(
            'widget_id' => 'pageViewStatistics',
            'jack_object' => $this,
            'render_action' => 'pageViewStatistics',
            'widget_title' => 'Page View Statistics',
            'widget_size' => 6,
            'add_permission' => true
            );
        register_dashboard_widgets($args);

        $args = array(
            'widget_id' => 'deviceBrowserStatistics',
            'jack_object' => $this,
            'render_action' => 'deviceBrowserStatistics',
            'widget_title' => 'Device-Browser Statistics',
            'widget_size' => 6,
            'add_permission' => true
            );
        register_dashboard_widgets($args);

        $args = array(
            'widget_id' => 'visitorLocationStatistics',
            'jack_object' => $this,
            'render_action' => 'visitorLocationStatistics',
            'widget_title' => 'Visitor-Location Statistics',
            'widget_size' => 6,
            'add_permission' => true
            );
        register_dashboard_widgets($args);
        }

    function updateSiteHit(){
        //session_write_close();
        $ipTime = new Timer();
        global $devdb, $gMan, $_config;
        if($gMan->call_type == 'admin' || $gMan->call_type == 'api') return null;
        /*
        //get location info
        $caller = curl_init();

        //curl_setopt($caller, CURLOPT_URL, 'http://ip-api.com/json/'.$_SERVER['REMOTE_ADDR']);
        curl_setopt($caller, CURLOPT_URL, 'http://ip-api.com/json/212.138.92.10');
        curl_setopt($caller, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($caller, CURLOPT_RETURNTRANSFER, true);


        $locInfo = curl_exec($caller);
        $locInfo = json_decode(trim($locInfo), TRUE);


        curl_close($caller);


        if($locInfo['status'] == 'fail'){
            $locInfo['countryCode'] = 'UNKNOWN';
            $locInfo['country'] = 'UNKNOWN';
            }
        */
        $locInfo = array();
        $locInfo['countryCode'] = 'UNKNOWN';
        $locInfo['country'] = 'UNKNOWN';
        //Site Hits Query Here
        /*
         * id = ID
         * requestedUrl = current_url()
         * finalUrl = $_404 ? url('404') : $load_page
         * userId = $_config['user'] ? $_config['user']['pk_user_id'] : 0
         * ip1 = $_SESSION['REMOTE_ADDR']
         * ip2 = null
         * userAgent = $_SESSION['HTTP_USER_AGENT'],
         * deviceType = isMobile() ? 'mobile' : 'computer'
         * requestTimeFloat = $_SESSION['REQUEST_TIME_FLOAT'],
         * requestTime = $_SESSION['REQUEST_TIME']
         * callType = $gMan['call_type']
         * */
        if(@function_exists(get_browser())) $uAgent = get_browser(null,true);
        else $uAgent = getBrowser();

        $insert_data = array(
            'referer' => $_SERVER['HTTP_REFERER'],
            'requestedUrl' => current_url(),
            'finalUrl' => $gMan->publicRouter ? $gMan->publicRouter['load_page'] : $gMan->load_file,
            'linkType' => isset($_config['current_content']) ? 'post' : 'page',               'userId' => true ? $_config['user']['pk_user_id'] : 0,
            'linkStatus' => $gMan->publicRouter['404'] ?  '404' : ($gMan->publicRouter['maintenance'] ? '503' : '200'),
            'linkTitle' => generate_page_title(),
            'refererDomain' => get_domain($_SERVER['HTTP_REFERER']) ? get_domain($_SERVER['HTTP_REFERER']) : null,
            'userId' => true ? $_config['user']['pk_user_id'] : 0,
            'ipOne' => $_SERVER['REMOTE_ADDR'],
            'ipTwo' => null,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'],
            'browser' => (($uAgent['browser']==NULL) || (strlen($uAgent['browser'])<=0)) ? 'Unknown' : $uAgent['browser'],
            'operatingSystem' => $uAgent['platform'],
            'deviceType' => isMobile() ? 'mobile' : 'computer',
            'countryCode' => $locInfo['countryCode'],
            'countryName' => $locInfo['country'],
            'requestTimeFloat' => '',
            'requestTime' => date ('Y-m-d H:i:s'),
            );

        //return $insert_data;

        $insertion = $devdb->insert_update('dev_site_hits',$insert_data);

        $ipTime->_stop();
        return $ipTime->elapsed_time;
        }

    function visitorLocationStatistics(){
        global $devdb;
        $countries = array();
        $ips_sql = "SELECT COUNT(*) AS country_total,countryName FROM dev_site_hits GROUP BY countryCode";
        $ips = $devdb->get_results($ips_sql);
        $sum = 0;
        foreach($ips as $i=>$v){
            $sum = $v['country_total'] + $sum;

            }

        foreach($ips as $i=>$v){
            $countries[$i]['percentage'] = round(($v['country_total']*100)/$sum);
            $countries[$i]['country'] = isset($v['countryName']) ? $v['countryName'] : 'UNKNOWN';
            }
        $last_countries_sql = "SELECT countryName,requestTime FROM dev_site_hits ORDER BY requestTime DESC LIMIT 0,3";
        $last_countries = $devdb->get_results($last_countries_sql);
        ?>
        <div class="stat-panel">
            <!-- Success background, bordered, without top and bottom borders, without left border, without padding, vertically and horizontally centered text, large text -->
            <!-- /.stat-cell -->
            <!-- Without padding, extra small text -->
            <div class="stat-cell col-sm-5 bordered bg-success no-border-r padding-sm-hr valign-top">
                <!-- Add parent div.stat-rows if you want build nested rows -->
                <h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-white"></i>&nbsp;&nbsp;Countries</h4>
                <div class="stat-rows">
                    <ul class="list-group bg-warning no-margin">
                        <?php
                        foreach($countries as $i=>$v){
                            ?>
                            <li class="list-group-item no-border-hr bg-success padding-lg">
                                <?php echo ucwords($v['country'])?> <span class="label label-warning pull-right"><?php echo $v['percentage']?>%</span>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div> <!-- /.stat-rows -->
            </div>
            <div class="stat-cell col-sm-7 bordered bg-success no-border-r padding-sm-hr valign-top">
                <!-- Add parent div.stat-rows if you want build nested rows -->
                <h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-white"></i>&nbsp;&nbsp;Last 3 Visits</h4>
                <div class="stat-rows">
                    <ul class="list-group bg-warning no-margin">
                        <?php
                        foreach($last_countries as $i=>$v){
                            ?>
                            <li class="list-group-item no-border-hr bg-success padding-lg">
                                <?php echo ucwords($v['countryName'])?> <span class="label label-warning pull-right"><?php echo print_date($v['requestTime'])?></span>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div><!-- /.stat-rows -->
            </div><!-- /.stat-cell -->
        </div>
        <?php
        }

    function deviceBrowserStatistics(){
        global $devdb,$_config;

        $browser_count_sql = "SELECT COUNT(pk_id) AS TOTAL,browser  FROM dev_site_hits GROUP BY browser";
        $browser_count = $devdb->get_results($browser_count_sql);

        $all_count_sql = "SELECT COUNT(pk_id) AS TOTAL FROM dev_site_hits";
        $all_count = $devdb->get_row($all_count_sql);
        foreach($browser_count as $i=>$v){
            $browser_count[$i]['percentage'] = round(($v['TOTAL']*100)/$all_count['TOTAL'],1);
            }
        $device_count_sql = "SELECT COUNT(pk_id) AS TOTAL,deviceType  FROM dev_site_hits GROUP BY deviceType";
        $device_count = $devdb->get_results($device_count_sql);
        foreach($device_count as $i=>$v){
            $device_count[$i]['percentage'] = round(($v['TOTAL']*100)/$all_count['TOTAL'],1);
            }

        ?>
        <div class="stat-panel">
            <div class="stat-row">
                <!-- Bordered, without right border, top aligned text -->
                <div class="stat-cell col-sm-4 bordered bg-color-grey no-border-r padding-sm-hr valign-top">
                    <!-- Small padding, without top padding, extra small horizontal padding -->
                    <h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-white"></i>&nbsp;&nbsp;From Browsers</h4>
                    <!-- Without margin -->
                    <ul class="list-group bg-info no-margin">
                        <?php
                            foreach($browser_count as $i=>$v){
                                ?>
                                <li class="list-group-item no-border-hr bg-info padding-lg">
                                    <?php echo ucwords($v['browser'])?> <span class="label pull-right"><?php echo $v['percentage']?>%</span>
                                </li>
                                <?php
                                }
                        ?>
                    </ul>
                </div> <!-- /.stat-cell -->
                <!-- Primary background, small padding, vertically centered text -->
                <div class="stat-cell col-sm-4 bordered bg-info no-border-r padding-sm-hr valign-top">
                    <!-- Small padding, without top padding, extra small horizontal padding -->
                    <h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-white"></i>&nbsp;&nbsp;From Devices</h4>
                    <!-- Without margin -->
                    <ul class="list-group bg-info no-margin">
                        <?php
                        foreach($device_count as $i=>$v){
                            ?>
                            <li class="list-group-item no-border-hr bg-color-grey padding-lg">
                                <?php echo ucwords($v['deviceType'])?> <span class="label bg-info pull-right"><?php echo $v['percentage']?>%</span>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        }

    function pageViewStatistics(){
        global $_config,$devdb;
        $count_today_sql = "SELECT COUNT(pk_id) AS COUNT_TODAY FROM dev_site_hits WHERE requestTime>='".date('Y-m-d 00:00:00')."' AND requestTime<='".date('Y-m-d 23:59:59')."'";
        $count_today = $devdb->get_row($count_today_sql);

        $count_yesterday_sql = "SELECT COUNT(pk_id) AS COUNT_YESTERDAY FROM dev_site_hits WHERE requestTime>='".date('Y-m-d 00:00:00',strtotime("-1 days"))."' AND requestTime<='".date('Y-m-d 23:59:59',strtotime("-1 days"))."'";
        $count_yesterday = $devdb->get_row($count_yesterday_sql);

        $count_month_sql = "SELECT COUNT(pk_id) AS COUNT_MONTH FROM dev_site_hits WHERE requestTime>='".date('Y-m-01 00:00:00')."' AND requestTime<='".date('Y-m-t 23:59:59')."'";
        $count_month = $devdb->get_row($count_month_sql);

        $count_lifetime_sql = "SELECT COUNT(pk_id) AS COUNT_LIFETIME FROM dev_site_hits";
        $count_lifetime = $devdb->get_row($count_lifetime_sql);

        $previous_week = strtotime("-1 week");
        $previous_day = strtotime('-1 day');

        $start_week = date("Y-m-d 00:00:00",$previous_week);
        $end_week = date("Y-m-d 23:59:59",$previous_day);
        $week_count = array();
        for($i=0;$i<7;$i++){
            $week_count_sql = "SELECT COUNT(pk_id) AS COUNT_WEEK FROM dev_site_hits WHERE requestTime>='".date("Y-m-d 00:00:00",strtotime('-1 week +'.$i.'day'))."' AND requestTime<='".date("Y-m-d 23:59:59",strtotime('-1 week +'.$i.'day'))."'";
            $week_count[date("Y-m-d",strtotime('-1 week +'.$i.'day'))] = $devdb->get_row($week_count_sql);
            }

        ?>
        <div class="stat-panel">
            <div class="stat-row">
                <div class="stat-cell col-sm-7 bg-color-grey padding-sm valign-middle">
                    <!--h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-eye text-white"></i>&nbsp;&nbsp;Last Week Total Site Hits</h4-->
                    <div id="site-hits-graph" class="bg-color-green graph" style="height: 180px;"></div>
                </div>
                <!-- Bordered, without right border, top aligned text -->
                <div class="stat-cell col-sm-5 bordered bg-color-green no-border-r padding-sm  valign-top">
                    <!-- Small padding, without top padding, extra small horizontal padding -->
                    <!--h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-white"></i>&nbsp;&nbsp;Total Site Hits</h4-->
                    <!-- Without margin -->
                    <ul class="list-group bg-color-green no-margin">
                        <!-- Without left and right borders, extra small horizontal padding -->
                        <li class="list-group-item no-border-hr bg-color-grey padding-lg">
                            Today <span class="label bg-color-green pull-right"><?php echo formatNumber($count_today['COUNT_TODAY'])?></span>
                        </li> <!-- / .list-group-item -->
                        <!-- Without left and right borders, extra small horizontal padding -->
                        <li class="list-group-item no-border-hr bg-color-grey padding-lg">
                            Yesterday <span class="label bg-color-green pull-right"><?php echo formatNumber($count_yesterday['COUNT_YESTERDAY'])?></span>
                        </li> <!-- / .list-group-item -->
                        <!-- Without left and right borders, without bottom border, extra small horizontal padding -->
                        <li class="list-group-item no-border-hr bg-color-grey no-border-b padding-lg">
                            This Month <span class="label bg-color-green pull-right"><?php echo formatNumber($count_month['COUNT_MONTH'])?></span>
                        </li> <!-- / .list-group-item -->
                        <li class="list-group-item no-border-hr bg-color-grey no-border-b padding-lg">
                            Lifetime <span class="label bg-color-green pull-right"><?php echo formatNumber($count_lifetime['COUNT_LIFETIME'])?></span>
                        </li>
                    </ul>
                </div> <!-- /.stat-cell -->
                <!-- Primary background, small padding, vertically centered text -->
            </div>
        </div>
        <script>
            init.push(function () {
                var uploads_data = [
                    <?php
                     foreach($week_count as $i=>$v){
                        ?>
                    {week_days: '<?php echo date('D', strtotime($i));?>', v: <?php echo $v['COUNT_WEEK'] ?>},
                    <?php
                    }
                ?>
                ];
                Morris.Line({
                    element: 'site-hits-graph',
                    data: uploads_data,
                    parseTime : false,
                    xkey: 'week_days',
                    ykeys: ['v'],
                    labels: ['Total Site Hits'],
                    lineColors: ['#fff'],
                    lineWidth: 2,
                    pointSize: 4,
                    gridLineColor: 'rgba(255,255,255,.5)',
                    resize: true,
                    gridTextColor: '#fff',
                    xLabels: "week_days"

                });
            });
        </script>
        <?php
        }

    function addSiteHit(){
        global $gMan;

        if(isset($gMan->publicRouter['valid_call']) && $gMan->publicRouter['valid_call']){
            load_js_footer(array(
                function(){
                    ob_start();
                    ?>
                    <script type="text/javascript">
                        if(typeof $ !== 'undefined'){
                            $.ajax({
                                url : _root_path_ + '/api/dev_site_statistics/updateSiteHit',
                                data : {
                                    internalToken : _internalToken_
                                    },
                                type: 'POST'
                                });
                            }
                        else{
                            (function($) {
                                $.ajax({
                                    url : _root_path_ + '/api/dev_site_statistics/updateSiteHit',
                                    data : {
                                        internalToken : _internalToken_
                                        },
                                    type: 'POST',
                                    success: function(ret) {
                                    //console.log(ret)}
                                        }
                                    });
                                })(jQuery);
                            }
                    </script>
                    <?php
                    $out = ob_get_clean();
                    return $out;
                    },
                ));
            }
        }

    static function getVisitorCount(){
        global $devdb;
        $sql = "SELECT COUNT(pk_id) AS VISITORS FROM dev_site_hits";
        $visitor_counter = $devdb->get_row($sql);
        return $visitor_counter['VISITORS'];
        }

    static function get_country_from_IP($ip = NULL, $purpose = "location", $deep_detect = TRUE){
        //$start_ = new Timer();
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
            );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                            );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                    }
                }
            }
        //$start_->_stop();
        //pre($start_->elapsed_time,0);
        ?>

        <?php
        return $output;
        }
	}
new dev_site_statistics;

function getVisitorCount(){
    if(class_exists('dev_site_statistics')) return dev_site_statistics::getVisitorCount();
    else return 0;
    };

function getCountryFromIP($ip = NULL, $purpose = "location", $deep_detect = TRUE){
    if(class_exists('dev_site_statistics')) return dev_site_statistics::get_country_from_IP($ip, $purpose, $deep_detect);
    else return 0;
    }