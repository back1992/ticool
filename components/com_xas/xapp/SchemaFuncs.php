<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\SchemaOld
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/***
 * Set of functions being used in json output schemas.
 * By nature of the template engine, they need to be global.
 */

function xapp_json_query($json,$query){
    $result = null;
    xapp_hide_errors();
    $jsonPathObject = JsonStore::asObj($json);
    if($jsonPathObject){
        $x =&JsonStore::get($jsonPathObject,$query);
        if($x!=null){
            $result=$x;
        }
    }
    return $result;
}

/**
 * @param $cTypeName : the custom type's name (url-schema)
 * @param $refId : self explaining
 * @param $query : json-path-php query
 * @return json string
 */
function customTypeQuery($cTypeName,$refId,$query,$subQuery=null)
{
    //$start = microtime(true);
    $result = '';
    if($cTypeName && $refId && $query){
        $cTypeRes= CustomTypesUtils::customTypeQuery($cTypeName,$refId,SchemaProcessor::$options,$query,$subQuery);
        if($cTypeRes){
            $result = '' . $cTypeRes . '';
            $result= str_replace("'",'\"',$result);
            $result = addslashes($result);
            $result= str_replace('\/','/',$result);
            $result= str_replace(",null)","',null)",$result);
            $result= str_replace("\n","",$result);
            $result = preg_replace('/\r\n?/', "", $result);
            $result = str_replace(array("\n", "\r"), "", $result);
        }
    }
    //error_log("cType Query" . (microtime(true) - $start) . "\n");
    return $result;
}
/***
 *
 */
/***
 * Check a web url for 200
 * @param $url
 * @param string $alternate
 * @return string
 */
function checkUrl($url,$alternate='')
{
    if($url){
        $headers = @get_headers($url);
        if(strpos($headers[0],'200')===false){
            return $alternate;
        }
    }
    return $url;
}

function assertInsert($string,$condition,$alternate=''){

    //error_log('assert ' . $string . ' for ' . $condition);
    $set = true;
    if(is_numeric($condition)){
        if($condition==0){
            $set=false;
        }
    }elseif(is_bool($condition)){
        $set=$condition;
    }elseif (!$condition){
        $set=false;
    }
    if($set===true){
        return $string;
    }else{


    }
    return $alternate;
}
/***
 * Tag-Replacements
 */
function insertVar($string){
    if($string){
        return '' . $string;
    }
    return $string;
}
/***
 * @param $string, html markup
 * @return string, array of picture items, json formatted
 */
function xapp_findPicture($string)
{
    $htmlFilter = new HTMLFilter($string);
    $items = $htmlFilter->pictureItems();
    if($items && count($items) > 0){
        return $items[0]->fullSizeLocation;
    }
    return false;
}
/***
 * @param $string, html markup
 * @return string, array of picture items, json formatted
 */
function toPictureItems($string,$addSlashes=true)
{
    $htmlFilter = new HTMLFilter($string);
    $items = $htmlFilter->pictureItems();
    $result  = json_encode(array_values($items));
    if($addSlashes){
        $result =''.addslashes($result);
    }
    $result = preg_replace('/[\x00-\x1F\x7F]/', '', $result);
    return $result;
}
/***
 * Cleans HTML code and filters images and links to xapp-format
 * @param $string, html markup
 * @return string
 */
function htmlMobile($string)
{
    $htmlFilter = new HTMLFilter($string);
    $result = $htmlFilter->htmlMobile();
    $result= stripJTags($result);
    return $result;
}
/***
 * @param $string
 * @return mixed|string
 */
function stripHTML($string)
{

    /***
     * 1st pass, strip all html tags
     */
    $result = strip_tags($string);

    /***
     * 2nd pass, convert Joomla specific tags into xapp widgets
     */
    if(function_exists("stripJTags")){
        $result = stripJTags($result);
    }

    /**
     * 3th pass, common clean up
     */
    $result= preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $result);
    $result = addslashes($result);


    return $result;
}
/***
 * @param $date
 * @return string
 */
function nicetime($date)
{
    if(empty($date)) {
        return "No date provided";
    }
    //$ma = xapp_data_max($date,$date);

    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");

    $now             = time();
    $unix_date         = strtotime($date);
    //DATE_FORMAT(#__easyblog_post.created,'%a, %D %b %H:%i')

    // check validity of date
    if(empty($unix_date)) {
        error_log("bad date : " . $date);
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {
        $difference     = $now - $unix_date;
        $tense         = "ago";

    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }

    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if($difference != 1) {
        $periods[$j].= "s";
    }
    $result =  "$difference $periods[$j] {$tense}";
    //error_log('nice time : ' . $date . ' :: ' . $result);
    return $result;
}

/*
$shortcode_tags = array();
function get_shortcode_regex() {
    global $shortcode_tags;
    $tagnames = array_keys($shortcode_tags);
    $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

    // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
    // Also, see shortcode_unautop() and shortcode.js.
    return
        '\\['                              // Opening bracket
        . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
        . "($tagregexp)"                     // 2: Shortcode name
        . '(?![\\w-])'                       // Not followed by word character or hyphen
        . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
        .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
        .     '(?:'
        .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
        .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
        .     ')*?'
        . ')'
        . '(?:'
        .     '(\\/)'                        // 4: Self closing tag ...
        .     '\\]'                          // ... and closing bracket
        . '|'
        .     '\\]'                          // Closing bracket
        .     '(?:'
        .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
        .             '[^\\[]*+'             // Not an opening bracket
        .             '(?:'
        .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
        .                 '[^\\[]*+'         // Not an opening bracket
        .             ')*+'
        .         ')'
        .         '\\[\\/\\2\\]'             // Closing shortcode tag
        .     ')?'
        . ')'
        . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
}
function strip_shortcode_tag( $m ) {
    if ( $m[1] == '[' && $m[6] == ']' ) {
        return substr($m[0], 1, -1);
    }
    return $m[1] . $m[6];
}
function strip_shortcodes( $content ) {
    global $shortcode_tags;

    //  if (empty($shortcode_tags) || !is_array($shortcode_tags)){
    //     error_log('short code tags empty');
    //return $content;
    //  }

    $pattern = get_shortcode_regex();

    return preg_replace_callback( "/$pattern/s", 'strip_shortcode_tag', $content );
}
*/
?>