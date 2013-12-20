<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\SchemaOld
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/***
 * @param $start_date
 * @param $end_date
 * @param $date_from_user
 * @return int
 */
function xapp_date_not_in_range($start_date, $end_date)
{
    if($end_date==='0000-00-00-00-00'){
        return 0;
    }
    // Convert to timestamp
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    $user_ts = time();
    // Check that user date is between start & end
    $res =  (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    if($res){
        return 0;
    }else{
        return 1;
    }

}
/***
 * @param string $date0
 * @param string $date1
 * @return string
 */
function xapp_max_date($date0='0000-00-00-00-00',$date1='0000-00-00-00-00')
{
    $tp0   = strtotime($date0);
    $tp1   = strtotime($date1);
    $res = '' . $date0;
    if($tp0>$tp1){
        $res=$date0;
    }else{
        $res=$date1;
    }
    return $res;
}

function xapp_nicetime($date)
{
    return nicetime($date);
}