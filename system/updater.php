<?php
//multilingual table column updater
function updateMultilingualFields(){
    global $_config, $multilingualFields, $devdb;
    $allLangNotDefault = array_keys($_config['langs']);
    unset($allLangNotDefault[array_search($_config['dlang'], $allLangNotDefault)]);
    $find = '/{:(?>' . implode('|', $allLangNotDefault) . ')}(\X*?){:(?>' . implode('|', $allLangNotDefault) . ')}/m';
    $replacement = '';

    foreach($multilingualFields as $table=>$fields){
        $primaryField = getTablePrimaryKey($table);
        $sql = "SELECT ". $primaryField.",".implode(',', $fields)." FROM ".$table;
        $data = $devdb->get_results($sql);

        foreach($data as $i=>$v){
            foreach($fields as $field){
                $currentData = $v[$field];

                //first check if default language is already there, if there then we ignore
                $matches = array();
                $defaultFind = '/{:' . $_config['dlang'] . '}(\X*?){:' . $_config['dlang'] . '}/';
                $totalMatch = preg_match_all($defaultFind, $currentData, $matches);

                if(!$totalMatch){
                    $matches = array();
                    $totalMatch = preg_match_all($find, $currentData, $matches);

                    if ($totalMatch) $defaultText = str_replace($matches[0], '', $currentData);
                    else $defaultText = $currentData;
                    $defaultText = trim($defaultText);

                    if (strlen($defaultText)) {
                        $defaultText = '{:' . $_config['dlang'] . '}' . $defaultText . '{:' . $_config['dlang'] . '}';
                        $dataToUpdate = isset($matches[0]) ? $matches[0] : array();
                        array_push($dataToUpdate, $defaultText);
                        $updateData = array(
                            $field => implode('', $dataToUpdate)
                        );

                        $devdb->insert_update($table, $updateData, " " . $primaryField . " = " . $v[$primaryField]);
                    }
                }
            }
        }
    }
}
//register_shutdown_function('updateMultilingualFields');

//config table updater
function updateMultilingualConfigFields(){
    global $_config, $multilingualConfigFields, $devdb;
    $allLangNotDefault = array_keys($_config['langs']);
    unset($allLangNotDefault[array_search($_config['dlang'], $allLangNotDefault)]);
    $find = '/{:(?>' . implode('|', $allLangNotDefault) . ')}(\X*?){:(?>' . implode('|', $allLangNotDefault) . ')}/m';
    $replacement = '';

    $sql = "SELECT * FROM dev_config";
    $data = $devdb->get_results($sql);

    foreach ($data as $i => $v) {
        if(in_array($v['config_name'], $multilingualConfigFields) === false) continue;

        $currentData = $v['config_value'];

        //first check if default language is already there, if there then we ignore
        $matches = array();
        $defaultFind = '/{:' . $_config['dlang'] . '}(\X*?){:' . $_config['dlang'] . '}/';
        $totalMatch = preg_match_all($defaultFind, $currentData, $matches);

        if (!$totalMatch) {
            $matches = array();
            $totalMatch = preg_match_all($find, $currentData, $matches);

            if ($totalMatch) $defaultText = str_replace($matches[0], '', $currentData);
            else $defaultText = $currentData;
            $defaultText = trim($defaultText);

            if (strlen($defaultText)) {
                $defaultText = '{:' . $_config['dlang'] . '}' . $defaultText . '{:' . $_config['dlang'] . '}';
                $dataToUpdate = isset($matches[0]) ? $matches[0] : array();
                array_push($dataToUpdate, $defaultText);

                $updateData = array(
                    'config_value' => implode('', $dataToUpdate)
                );
                $devdb->insert_update('dev_config', $updateData, " config_id = " . $v['config_id']);
            }
        }
    }
}
//register_shutdown_function('updateMultilingualConfigFields');

//jack settings table updater
function updateMultilingualJackFields()
{
    global $_config, $multilingualJackFields, $devdb;
    $allLangNotDefault = array_keys($_config['langs']);
    unset($allLangNotDefault[array_search($_config['dlang'], $allLangNotDefault)]);
    $find = '/{:(?>' . implode('|', $allLangNotDefault) . ')}(\X*?){:(?>' . implode('|', $allLangNotDefault) . ')}/m';
    $replacement = '';

    $sql = "SELECT * FROM dev_jack_settings";
    $data = $devdb->get_results($sql);

    foreach ($data as $i => $v) {
        if (in_array($v['settings_key'], $multilingualJackFields) === false) continue;

        $currentData = $v['settings_value'];

        //first check if default language is already there, if there then we ignore
        $matches = array();
        $defaultFind = '/{:' . $_config['dlang'] . '}(\X*?){:' . $_config['dlang'] . '}/';
        $totalMatch = preg_match_all($defaultFind, $currentData, $matches);

        if (!$totalMatch) {
            $matches = array();
            $totalMatch = preg_match_all($find, $currentData, $matches);

            if ($totalMatch) $defaultText = str_replace($matches[0], '', $currentData);
            else $defaultText = $currentData;
            $defaultText = trim($defaultText);

            if (strlen($defaultText)) {
                $defaultText = '{:' . $_config['dlang'] . '}' . $defaultText . '{:' . $_config['dlang'] . '}';
                $dataToUpdate = isset($matches[0]) ? $matches[0] : array();
                array_push($dataToUpdate, $defaultText);

                $updateData = array(
                    'settings_value' => implode('', $dataToUpdate)
                );
                $devdb->insert_update('dev_jack_settings', $updateData, " pk_settings_id = " . $v['pk_settings_id']);
            }
        }
    }
}
//register_shutdown_function('updateMultilingualJackFields');



