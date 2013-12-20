<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Utils\Debugging
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * @param int $decimals
 * @return string
 */
function XApp_Memory_Usage($decimals = 2)
{
    $result = 0;
    ///return;

    if (function_exists('memory_get_usage'))
    {
        $result = memory_get_usage() / 1024;
    }

    else
    {
        if (function_exists('exec'))
        {
            $output = array();

            if (substr(strtoupper(PHP_OS), 0, 3) == 'WIN')
            {
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

                $result = preg_replace('/[\D]/', '', $output[5]);
            }

            else
            {
                exec('ps -eo%mem,rss,pid | grep ' . getmypid(), $output);

                $output = explode('  ', $output[0]);

                $result = $output[1];
            }
        }
    }

    return number_format(intval($result) / 1024, $decimals, '.', '');
}
/***
 * @param $obj
 * @param string $prefix
 * @return mixed
 */
function xapp_dumpObject($obj,$prefix=''){
    $d = print_r($obj,true);
    error_log(' dump : ' .$prefix . ' : ' . $d);
    error_log('\n::');
    return $d;
}


function xapp_show_errors(){
    ini_set('display_errors', '1');     # don't show any errors...
    error_reporting(E_ALL | E_STRICT);
}
function xapp_hide_errors(){
    ini_set('display_errors', '');     # don't show any errors...
    error_reporting(E_ERROR);


}
/***
 * @param $obj
 * @param string $prefix
 * @return mixed
 */
function xapp_cdump($prefix='',$obj,$trace=false){
    xapp_console('xapp console message',$prefix,'dump', $obj);
    if($trace){
        xapp_console('xapp console message',$prefix,'trace', $obj);
    }
    return;
}
function xapp_prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $prev_char = '';
    $in_quotes = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if( $char === '"' && $prev_char != '\\' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                $level--;
                $ends_line_level = NULL;
                $new_line_level = $level;
                break;

                case '{': case '[':
                $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                $char = "";
                $ends_line_level = $new_line_level;
                $new_line_level = NULL;
                break;
            }
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
        $prev_char = $char;
    }

    return $result;

}
function xapp_print_json($obj,$prefix=''){
    $json_string = json_encode($obj);
    //$json_string = xapp_prettyPrint($json_string);
    return $json_string;
}


/***
 * @param string $section
 */
function xapp_print_memory_stats($section=""){


    return;
    if(XAPP_CONNECT_CONFIG==='conf.inc.debug.php'){
        global $xapp_profile_time_last;
        $now = microtime(true);
        if($xapp_profile_time_last==null){
            $xapp_profile_time_last=$now;
        }
        $diff = $now-$xapp_profile_time_last;
        error_log($section . ' :: memory : ' . XApp_Memory_Usage(). ' :: diff : ' . $diff);
        $xapp_profile_time_last = $now;
        global $xapp_logger;
        if($xapp_logger!=null){
            $xapp_logger->log($section . ' :: memory : ' . XApp_Memory_Usage() . ' :: diff : ' . $diff);
        }
    }

}