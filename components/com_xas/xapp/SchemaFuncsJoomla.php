<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @package XApp-Connect\SchemaOld
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('JPATH_ADMINISTRATOR')) {
}

/***
 * Set of functions being used in json output schemas, but Joomla specific.
 * By nature of the template engine, they need to be global.
 */
/***
 * Joomla func to get a XApp-List-Item-Label from a tag
 * @param $string
 * @param bool $_stripHTML
 * @return string
 */
function getJTagLabel($string,$_stripHTML=true)
{
    require (XAPP_LIB .'html/tagsReplacements.php');
    $grabTags = str_replace("(","",str_replace(")","",implode(array_keys($tagReplace),"|")));
    if(preg_match("#{(".$grabTags.")}#s",$string)==false){
        return '' + 0;
    }

    //error_log('checking for title' . $string);

    $plg_name= 'jw_allvideos';
    $plugin = JPluginHelper::getPlugin('content', $plg_name);
    $params=null;
    if (!$params)
        $params = class_exists('JParameter') ? new JParameter(null) : new JRegistry(null);

    $pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);
    $parsedInModule = $params->get('parsedInModule');

    $awidth 								= 480;
    $aheight 								= 24;
    $autoplay = true;
    $result='';
    $sitePath = JPATH_SITE;
    $siteUrl  = JURI::root(true);
    $afolder= $pluginParams->get('afolder','images/stories/audio');
    $vfolder 								= ($params->get('vfolder')) ? $params->get('vfolder') : $pluginParams->get('vfolder','images/stories/videos');
    if (version_compare(JVERSION, '1.6.0', 'ge'))
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name.'/'.$plg_name;
    }
    else
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name;
    }


    $tagResolved = null;
    $siteUrl = str_replace('/components/com_xas/xapp','/',$siteUrl);
    $compReplaceSearch = '/images/audio/';
    $compReplacement = '/media/k2/audio/';

    // error_log($pluginLivePath);

    // Loop throught the found tags
    foreach ($tagReplace as $plg_tag => $value) {

        // expression to search for
        $regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#s";

        if(preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER)) {

            // start the replace loop
            foreach ($matches[0] as $key => $match) {

                $tagcontent 		= preg_replace("/{.+?}/", "", $match);
                $tagparams 			= explode('|',$tagcontent);
                $tagsource 			= trim(strip_tags($tagparams[0]));

                // Prepare the HTML
                $output = new JObject;

                // Width/height/source folder split per media type


                //error_log('plg tag : ' . $plg_tag);


                if(in_array($plg_tag, array(
                    'mp3',
                    'mp3remote',
                    'aac',
                    'aacremote',
                    'm4a',
                    'm4aremote',
                    'ogg',
                    'oggremote',
                    'wma',
                    'wmaremote',
                    'soundcloud'
                ))){
                    return 'Play Audio';
                } else {
                    //return ''+52;
                }
                // Special treatment for specific video providers
                if($plg_tag=="dailymotion"){
                    $tagsource = preg_replace("~(http|https):(.+?)dailymotion.com\/video\/~s","",$tagsource);
                    $tagsourceDailymotion = explode('_',$tagsource);
                    $tagsource = $tagsourceDailymotion[0];

                }

                if($plg_tag=="ku6"){
                    $tagsource = str_replace('.html','',$tagsource);
                }

                if($plg_tag=="metacafe" && substr($tagsource,-1,1)=='/'){
                    $tagsource = substr($tagsource,0,-1);
                }

                if($plg_tag=="tnaondemand"){
                    $tagsource = parse_url($tagsource);
                    $tagsource = explode('&',$tagsource['query']);
                    $tagsource = str_replace('vidid=','',$tagsource[0]);
                }

                if($plg_tag=="twitvid"){
                    $tagsource = preg_replace("~(http|https):(.+?)twitvid.com\/~s","",$tagsource);
                    if($final_autoplay=='true'){
                        $tagsource = $tagsource.'&amp;autoplay=1';
                    }
                }

                if($plg_tag=="vidiac"){
                    $tagsourceVidiac = explode(';',$tagsource);
                    $tagsource = $tagsourceVidiac[0];
                }

                if($plg_tag=="vimeo"){
                    $result = 'Play Video';
                }

                if($plg_tag=="yahoo"){
                    $tagsourceYahoo = explode('-',str_replace('.html','',$tagsource));
                    $tagsourceYahoo = array_reverse($tagsourceYahoo);
                    $tagsource = $tagsourceYahoo[0];
                }

                if($plg_tag=="yfrog"){
                    $tagsource = preg_replace("~(http|https):(.+?)yfrog.com\/~s","",$tagsource);
                }

                if($plg_tag=="youmaker"){
                    $tagsourceYoumaker = explode('-',str_replace('.html','',$tagsource));
                    $tagsource = $tagsourceYoumaker[1];
                }

                if($plg_tag=="youku"){
                    $tagsource = str_replace('.html','',$tagsource);
                    $tagsource = substr($tagsource,3);
                }

                if($plg_tag=="youtube"){
                    $result = 'Play Video';
                }
                // Set a unique ID
                $output->playerID = 'AVPlayerID_'.substr(md5($tagsource),1,8).'_'.rand();

                // Placeholder elements
                $findAVparams = array(
                    "{SOURCE}",
                    "{SOURCEID}",
                    "{FOLDER}",
                    "{WIDTH}",
                    "{HEIGHT}",
                    "{PLAYER_AUTOPLAY}",
                    "{PLAYER_TRANSPARENCY}",
                    "{PLAYER_BACKGROUND}",
                    "{PLAYER_BACKGROUNDQT}",
                    "{PLAYER_CONTROLBAR}",
                    "{SITEURL}",
                    "{SITEURL_ABS}",
                    "{FILE_EXT}",
                    "{PLUGIN_PATH}",
                    "{PLAYER_POSTER_FRAME}",
                    "{PLAYER_SKIN}",
                    "{PLAYER_ABACKGROUND}",
                    "{PLAYER_AFRONTCOLOR}",
                    "{PLAYER_ALIGHTCOLOR}"
                );


                // Replacement elements
                /*
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    $output->folder,
                    $output->playerWidth,
                    $output->playerHeight,
                    $final_autoplay,
                    $transparency,
                    $background,
                    $backgroundQT,
                    $controlBarLocation,
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    $output->posterFrame,
                    $skin,
                    $abackground,
                    $afrontcolor,
                    $alightcolor
                );
                */



                //error_log('jroot : ' . $root);
                //$siteUrl = '' . $root;
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    '',
                    '',
                    '',
                    '',
                    ''
                );
                $firstPass =str_replace($findAVparams, $replaceAVparams, $tagReplace[$plg_tag]);
                //$result.=$firstPass;
            } // End second foreach

        } // End if
    }

    /*
    $root = JURI::root(false,'');
    if($tagResolved!=null){
        $result = $root.'//'.$tagResolved;
    }
    error_log('tag result : ' . $result);
    if($_stripHTML){
        //$result=stripHTML($result);
    }
    */
   // error_log('tag title result : ' . $result);
    return $result;
}

/***
 * Joomla func to determine a XApp-Type from a tag
 * @param $string
 * @param bool $_stripHTML
 * @return int|string
 */
function getJTagSourceType($string,$_stripHTML=true)
{
    require (XAPP_LIB .'html/tagsReplacements.php');
    $grabTags = str_replace("(","",str_replace(")","",implode(array_keys($tagReplace),"|")));
    if(preg_match("#{(".$grabTags.")}#s",$string)==false){
        //error_log('no tags');
        return '' + 0;
    }

    //error_log('checking ' . $string);

    $plg_name= 'jw_allvideos';
    $plugin = JPluginHelper::getPlugin('content', $plg_name);
    $params=null;
    if (!$params)
        $params = class_exists('JParameter') ? new JParameter(null) : new JRegistry(null);

    $pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);
    $parsedInModule = $params->get('parsedInModule');

    $awidth 								= 480;
    $aheight 								= 24;
    $autoplay = true;
    $result='';
    $sitePath = JPATH_SITE;
    $siteUrl  = JURI::root(true);
    $afolder= $pluginParams->get('afolder','images/stories/audio');
    $vfolder 								= ($params->get('vfolder')) ? $params->get('vfolder') : $pluginParams->get('vfolder','images/stories/videos');
    if (version_compare(JVERSION, '1.6.0', 'ge'))
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name.'/'.$plg_name;
    }
    else
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name;
    }


    $tagResolved = null;
    $siteUrl = str_replace('/components/com_xas/xapp','/',$siteUrl);
    $compReplaceSearch = '/images/audio/';
    $compReplacement = '/media/k2/audio/';

    // error_log($pluginLivePath);

    // Loop throught the found tags
    foreach ($tagReplace as $plg_tag => $value) {

        // expression to search for
        $regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#s";

        if(preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER)) {

            // start the replace loop
            foreach ($matches[0] as $key => $match) {

                $tagcontent 		= preg_replace("/{.+?}/", "", $match);
                $tagparams 			= explode('|',$tagcontent);
                $tagsource 			= trim(strip_tags($tagparams[0]));

                // Prepare the HTML
                $output = new JObject;
                if(in_array($plg_tag, array(
                    'mp3',
                    'mp3remote',
                    'aac',
                    'aacremote',
                    'm4a',
                    'm4aremote',
                    'ogg',
                    'oggremote',
                    'wma',
                    'wmaremote'

                ))){
                    $result = ''+52;
                    return 52;
                } else {

                    $result = 52;
                }

                if(in_array($plg_tag, array(
                    'soundcloud'
                ))){
                    return 18;
                }

                // Special treatment for specific video providers
                if($plg_tag=="dailymotion"){
                    $tagsource = preg_replace("~(http|https):(.+?)dailymotion.com\/video\/~s","",$tagsource);
                    $tagsourceDailymotion = explode('_',$tagsource);
                    $tagsource = $tagsourceDailymotion[0];

                }

                if($plg_tag=="ku6"){
                    $tagsource = str_replace('.html','',$tagsource);
                }



                if($plg_tag=="metacafe" && substr($tagsource,-1,1)=='/'){
                    $tagsource = substr($tagsource,0,-1);
                }

                if($plg_tag=="tnaondemand"){
                    $tagsource = parse_url($tagsource);
                    $tagsource = explode('&',$tagsource['query']);
                    $tagsource = str_replace('vidid=','',$tagsource[0]);
                }

                if($plg_tag=="twitvid"){
                    $tagsource = preg_replace("~(http|https):(.+?)twitvid.com\/~s","",$tagsource);
                    if($final_autoplay=='true'){
                        $tagsource = $tagsource.'&amp;autoplay=1';
                    }
                }

                if($plg_tag=="vidiac"){
                    $tagsourceVidiac = explode(';',$tagsource);
                    $tagsource = $tagsourceVidiac[0];
                }

                if($plg_tag=="vimeo"){
                    return 15;
                }

                if($plg_tag=="yahoo"){
                    $tagsourceYahoo = explode('-',str_replace('.html','',$tagsource));
                    $tagsourceYahoo = array_reverse($tagsourceYahoo);
                    $tagsource = $tagsourceYahoo[0];
                }

                if($plg_tag=="yfrog"){
                    $tagsource = preg_replace("~(http|https):(.+?)yfrog.com\/~s","",$tagsource);
                }

                if($plg_tag=="youmaker"){
                    $tagsourceYoumaker = explode('-',str_replace('.html','',$tagsource));
                    $tagsource = $tagsourceYoumaker[1];
                }

                if($plg_tag=="youku"){
                    $tagsource = str_replace('.html','',$tagsource);
                    $tagsource = substr($tagsource,3);
                }

                if($plg_tag=="youtube"){
                    return 15;
                }
                if($plg_tag=="podcast"){
                    //error_log('is pod cast type');
                    return 18;
                }
                // Set a unique ID
                $output->playerID = 'AVPlayerID_'.substr(md5($tagsource),1,8).'_'.rand();

                // Placeholder elements
                $findAVparams = array(
                    "{SOURCE}",
                    "{SOURCEID}",
                    "{FOLDER}",
                    "{WIDTH}",
                    "{HEIGHT}",
                    "{PLAYER_AUTOPLAY}",
                    "{PLAYER_TRANSPARENCY}",
                    "{PLAYER_BACKGROUND}",
                    "{PLAYER_BACKGROUNDQT}",
                    "{PLAYER_CONTROLBAR}",
                    "{SITEURL}",
                    "{SITEURL_ABS}",
                    "{FILE_EXT}",
                    "{PLUGIN_PATH}",
                    "{PLAYER_POSTER_FRAME}",
                    "{PLAYER_SKIN}",
                    "{PLAYER_ABACKGROUND}",
                    "{PLAYER_AFRONTCOLOR}",
                    "{PLAYER_ALIGHTCOLOR}"
                );


                // Replacement elements
                /*
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    $output->folder,
                    $output->playerWidth,
                    $output->playerHeight,
                    $final_autoplay,
                    $transparency,
                    $background,
                    $backgroundQT,
                    $controlBarLocation,
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    $output->posterFrame,
                    $skin,
                    $abackground,
                    $afrontcolor,
                    $alightcolor
                );
                */



                //error_log('jroot : ' . $root);
                //$siteUrl = '' . $root;
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    '',
                    '',
                    '',
                    '',
                    ''
                );
                $firstPass =str_replace($findAVparams, $replaceAVparams, $tagReplace[$plg_tag]);
                //$result.=$firstPass;
            } // End second foreach

        } // End if
    }

    /*
    $root = JURI::root(false,'');
    if($tagResolved!=null){
        $result = $root.'//'.$tagResolved;
    }

    error_log('tag result : ' . $result);
    if($_stripHTML){
        //$result=stripHTML($result);
    }
    */
    //error_log('tag type result : ' . $result);
    return $result;
}
/***
 * Strips Joomla specific tags from html markup
 * @param $string
 * @return mixed
 */


function stripDesktopTags($string){

    $plg_tag = 'desktopOnly';
    $regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#s";
    if(preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER)) {

        // start the replace loop
        foreach ($matches[0] as $key => $match) {

            $tagcontent 		= preg_replace("/{.+?}/", "", $match);
            $string = str_replace($tagcontent,'',$string);
        }
    }
    return $string;

}
function stripJTags($string){

    $string = stripDesktopTags($string);
    /**
     * 1st pass : ZHMaps
     */
    if (defined('JPATH_ADMINISTRATOR')){
        $zhMapPath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_zhgooglemap'.DIRECTORY_SEPARATOR.'zhgooglemap.php';
        if(file_exists($zhMapPath)){

            require_once (XAPP_LIB .'joomla/ZHMapFilter.php');
            if(function_exists("xappJFilterZHMaps")){
                $string = xappJFilterZHMaps($string);
            }
        }
    }


    /**
     * 2nd pass : WidgetKit
     */
    if (defined('JPATH_ADMINISTRATOR')){
        $wkPath = JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit.php';
        if(file_exists($wkPath)) {
            preg_match_all('#\[widgetkit id=(\d+)\]#', $string, $matchesWK);

            if(count($matchesWK[1]))
            {
                require_once (XAPP_LIB .'joomla/WKMapFilter.php');
                if(function_exists("xappJFilterWKMaps")){
                    $string = xappJFilterWKMaps($string);

                }
            }
        }
    }
    //error_log($string);
    /**
     * 3th pass, clean up and turn the lights off
     */
    $string = stripJTag2($string);
    $string = preg_replace('/\{([^\[]+?)\}/', '<$1>', $string);

    //un-implemented
    $string = preg_replace('#\[widgetkit id=(\d+)\]#','',$string);

    return $string;
}

function stripJTag2($string,$_stripHTML=true)
{
    require (XAPP_LIB .'html/tagsReplacements.php');
    $result = '' . $string;
    $grabTags = str_replace("(","",str_replace(")","",implode(array_keys($tagReplace),"|")));
    if(preg_match("#{(".$grabTags.")}#s",$result)==false){
        return $result;
    }

    $tagResolved = null;
    foreach ($tagReplace as $plg_tag => $value) {


        // expression to search for
        $regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#s";

        if(preg_match_all($regex, $result, $matches, PREG_PATTERN_ORDER)) {

            // start the replace loop
            foreach ($matches[0] as $key => $match) {
                $tagcontent 		= preg_replace("/{.+?}/", "", $match);

                $tagparams 			= explode('|',$tagcontent);
                $tagsource 			= trim(strip_tags($tagparams[0]));
                $result = preg_replace("#{".$plg_tag."}".preg_quote($tagcontent)."{/".$plg_tag."}#s", $tagsource , $result);
                $result = str_replace($tagcontent,'',$result);
            }
        }
    }
    return $result;
}
/***
 * Joomla func to find a podcast manager tag
 * @param $string
 * @return string
 */
function parseJPodcastTag($string){

    // Simple performance check to determine whether plugin should process further
    if (strpos($string, 'podcast') === false)
    {
        return '';
    }

    $result ='0';

    // Expression to search for
    $regex = '/\{(podcast)\s+(.*?)}/i';

    // Find all instances of plugin and put in $matches
    preg_match_all($regex, $string, $matches);
    $podCastId = null;
    foreach ($matches as $id => $podcast)
    {
        foreach ($podcast as $episode)
        {
            if (strpos($episode, 'id') === 0)
            {
                $podCastIdInt = substr($episode,3, 3);
                if(is_numeric($podCastIdInt)){
                    $podCastId = '' . $podCastIdInt;
                }
            }
        }
    }
    if($podCastId!=null){
        $result = 'index.php?option=com_podcastmanager&format=raw&feedname=' . $podCastId;
    }
    return $result;

}
/***
 * Joomla func to convert a tag into a reference url
 * @param $string
 * @param bool $_stripHTML
 * @return string
 */
function parseJTag($string,$_stripHTML=true)
{
    require (XAPP_LIB .'html/tagsReplacements.php');
    $grabTags = str_replace("(","",str_replace(")","",implode(array_keys($tagReplace),"|")));
    if(preg_match("#{(".$grabTags.")}#s",$string)==false){
        return '';
    }

    $plg_name= 'jw_allvideos';
    $plugin = JPluginHelper::getPlugin('content', $plg_name);
    $params=null;
    if (!$params)
        $params = class_exists('JParameter') ? new JParameter(null) : new JRegistry(null);

    $pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);
    $parsedInModule = $params->get('parsedInModule');

    $awidth 								= 480;
    $aheight 								= 24;
    $autoplay = true;
    $result='';
    $sitePath = JPATH_SITE;
    $siteUrl  = JURI::root(true);
    $afolder= $pluginParams->get('afolder','images/stories/audio');
    $vfolder 								= ($params->get('vfolder')) ? $params->get('vfolder') : $pluginParams->get('vfolder','images/stories/videos');
    if (version_compare(JVERSION, '1.6.0', 'ge'))
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name.'/'.$plg_name;
    }
    else
    {
        $pluginLivePath = $siteUrl.'/plugins/content/'.$plg_name;
    }


    $tagResolved = null;
    $siteUrl = str_replace('/components/com_xas/xapp','/',$siteUrl);
    $compReplaceSearch = '/images/audio/';
    $compReplacement = '/media/k2/audio/';

   // error_log($pluginLivePath);

    // Loop throught the found tags
    foreach ($tagReplace as $plg_tag => $value) {

        // expression to search for
        $regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#s";

        if(preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER)) {

            // start the replace loop
            foreach ($matches[0] as $key => $match) {

                $tagcontent 		= preg_replace("/{.+?}/", "", $match);
                $tagparams 			= explode('|',$tagcontent);
                $tagsource 			= trim(strip_tags($tagparams[0]));

                // Prepare the HTML
                $output = new JObject;

                // Width/height/source folder split per media type



                if(in_array($plg_tag, array(
                    'mp3',
                    'mp3remote',
                    'aac',
                    'aacremote',
                    'm4a',
                    'm4aremote',
                    'ogg',
                    'oggremote',
                    'wma',
                    'wmaremote',
                    'soundcloud'
                ))){
                    $final_awidth 	= (@$tagparams[1]) ? $tagparams[1] : $awidth;
                    $final_aheight 	= (@$tagparams[2]) ? $tagparams[2] : $aheight;
                    //error_log('seems audio');

                    //$output->playerWidth = $final_awidth;
                    //$output->playerHeight = $final_aheight;
                    $output->folder = $afolder;

                    /*
                    if($plg_tag=='soundcloud'){
                        if(strpos($tagsource,'/sets/')!==false){
                            $output->mediaTypeClass = ' avSoundCloudSet';
                        } else {
                            $output->mediaTypeClass = ' avSoundCloudSong';
                        }
                        $output->mediaType = '';
                    } else {
                        $output->mediaTypeClass = ' avAudio';
                        $output->mediaType = 'audio';
                    }
                    */

                    if(in_array($plg_tag, array('mp3','aac','m4a','ogg','wma'))){
                        $afolder = str_replace($afolder,$compReplacement,$afolder);
                        $output->source = "$siteUrl/$afolder/$tagsource.$plg_tag";
                        $tagResolved  = "$siteUrl/$afolder/$tagsource.$plg_tag";

                    } elseif(in_array($plg_tag, array('mp3remote','aacremote','m4aremote','oggremote','wmaremote'))){
                        $output->source = $tagsource;

                    } else {
                        $output->source = '';
                    }
                } else {
                   // $final_vwidth 	= (@$tagparams[1]) ? $tagparams[1] : $vwidth;
                  //  $final_vheight 	= (@$tagparams[2]) ? $tagparams[2] : $vheight;

                    /*
                    $output->playerWidth = $final_vwidth;
                    $output->playerHeight = $final_vheight;
                    $output->folder = $vfolder;
                    $output->mediaType = 'video';
                    $output->mediaTypeClass = ' avVideo';
                    */
                    $output->folder = $vfolder;
                }

                /*
                error_log('tag resolved : ' . $tagResolved);
                error_log('tag afolder : ' . $afolder);
                error_log('tag site Url : ' . $siteUrl);
                error_log('tag site Path : ' . $sitePath);

                */



                // Autoplay
                $final_autoplay = (@$tagparams[3]) ? $tagparams[3] : $autoplay;
                $final_autoplay	= ($final_autoplay) ? 'true' : 'false';

                // Special treatment for specific video providers
                if($plg_tag=="dailymotion"){
                    $tagsource = preg_replace("~(http|https):(.+?)dailymotion.com\/video\/~s","",$tagsource);
                    $tagsourceDailymotion = explode('_',$tagsource);
                    $tagsource = $tagsourceDailymotion[0];
                    if($final_autoplay=='true'){
                        if(strpos($tagsource,'?')!==false){
                            $tagsource = $tagsource.'&amp;autoPlay=1';
                        } else {
                            $tagsource = $tagsource.'?autoPlay=1';
                        }
                    }
                }

                if($plg_tag=="ku6"){
                    $tagsource = str_replace('.html','',$tagsource);
                }

                if($plg_tag=="metacafe" && substr($tagsource,-1,1)=='/'){
                    $tagsource = substr($tagsource,0,-1);
                }

                if($plg_tag=="tnaondemand"){
                    $tagsource = parse_url($tagsource);
                    $tagsource = explode('&',$tagsource['query']);
                    $tagsource = str_replace('vidid=','',$tagsource[0]);
                }

                if($plg_tag=="twitvid"){
                    $tagsource = preg_replace("~(http|https):(.+?)twitvid.com\/~s","",$tagsource);
                    if($final_autoplay=='true'){
                        $tagsource = $tagsource.'&amp;autoplay=1';
                    }
                }

                if($plg_tag=="vidiac"){
                    $tagsourceVidiac = explode(';',$tagsource);
                    $tagsource = $tagsourceVidiac[0];
                }

                if($plg_tag=="vimeo"){
                    $tagsource = preg_replace("~(http|https):(.+?)vimeo.com\/~s","",$tagsource);
                    if(strpos($tagsource,'?')!==false){
                        $tagsource = $tagsource.'&amp;portrait=0';
                    } else {
                        $tagsource = $tagsource.'?portrait=0';
                    }
                    if($final_autoplay=='true'){
                        $tagsource = $tagsource.'&amp;autoplay=1';
                    }
                }

                if($plg_tag=="yahoo"){
                    $tagsourceYahoo = explode('-',str_replace('.html','',$tagsource));
                    $tagsourceYahoo = array_reverse($tagsourceYahoo);
                    $tagsource = $tagsourceYahoo[0];
                }

                if($plg_tag=="yfrog"){
                    $tagsource = preg_replace("~(http|https):(.+?)yfrog.com\/~s","",$tagsource);
                }

                if($plg_tag=="youmaker"){
                    $tagsourceYoumaker = explode('-',str_replace('.html','',$tagsource));
                    $tagsource = $tagsourceYoumaker[1];
                }

                if($plg_tag=="youku"){
                    $tagsource = str_replace('.html','',$tagsource);
                    $tagsource = substr($tagsource,3);
                }

                if($plg_tag=="youtube"){
                    $tagsource = preg_replace("~(http|https):(.+?)youtube.com\/watch\?v=~s","",$tagsource);
                    $tagsourceYoutube = explode('&',$tagsource);
                    $tagsource = $tagsourceYoutube[0];

                    /*
                    if(strpos($tagsource,'?')!==false){
                        $tagsource = $tagsource.'&amp;rel=0&amp;fs=1&amp;wmode=transparent';
                    } else {
                        $tagsource = $tagsource.'?rel=0&amp;fs=1&amp;wmode=transparent';
                    }
                    if($final_autoplay=='true'){
                        $tagsource = $tagsource.'&amp;autoplay=1';
                    }
                    */
                }


                // Poster frame
                $posterFramePath = $sitePath.DS.str_replace('/',DS,$vfolder);
                if(JFile::exists($posterFramePath.DS.$tagsource.'.jpg')){
                    $output->posterFrame = $siteUrl.'/'.$vfolder.'/'.$tagsource.'.jpg';
                } elseif(JFile::exists($posterFramePath.DS.$tagsource.'.png')){
                    $output->posterFrame = $siteUrl.'/'.$vfolder.'/'.$tagsource.'.png';
                } elseif(JFile::exists($posterFramePath.DS.$tagsource.'.gif')){
                    $output->posterFrame = $siteUrl.'/'.$vfolder.'/'.$tagsource.'.gif';
                } else {
                    $output->posterFrame = '';
                }


                // Set a unique ID
                $output->playerID = 'AVPlayerID_'.substr(md5($tagsource),1,8).'_'.rand();

                // Placeholder elements
                $findAVparams = array(
                    "{SOURCE}",
                    "{SOURCEID}",
                    "{FOLDER}",
                    "{WIDTH}",
                    "{HEIGHT}",
                    "{PLAYER_AUTOPLAY}",
                    "{PLAYER_TRANSPARENCY}",
                    "{PLAYER_BACKGROUND}",
                    "{PLAYER_BACKGROUNDQT}",
                    "{PLAYER_CONTROLBAR}",
                    "{SITEURL}",
                    "{SITEURL_ABS}",
                    "{FILE_EXT}",
                    "{PLUGIN_PATH}",
                    "{PLAYER_POSTER_FRAME}",
                    "{PLAYER_SKIN}",
                    "{PLAYER_ABACKGROUND}",
                    "{PLAYER_AFRONTCOLOR}",
                    "{PLAYER_ALIGHTCOLOR}"
                );


                // Replacement elements
                /*
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    $output->folder,
                    $output->playerWidth,
                    $output->playerHeight,
                    $final_autoplay,
                    $transparency,
                    $background,
                    $backgroundQT,
                    $controlBarLocation,
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    $output->posterFrame,
                    $skin,
                    $abackground,
                    $afrontcolor,
                    $alightcolor
                );
                */



                //error_log('jroot : ' . $root);
                //$siteUrl = '' . $root;
                $replaceAVparams = array(
                    $tagsource,
                    $output->playerID,
                    $output->folder,
                    '',
                    '',
                    $final_autoplay,
                    '',
                    '',
                    '',
                    '',
                    $siteUrl,
                    substr(JURI::root(false),0,-1),
                    $plg_tag,
                    $pluginLivePath,
                    $output->posterFrame,
                    '',
                    '',
                    '',
                    ''
                );



                // Do the element replace
                //$output->player = JFilterOutput::ampReplace(str_replace($findAVparams, $replaceAVparams, $tagReplace[$plg_tag]));
                $firstPass =str_replace($findAVparams, $replaceAVparams, $tagReplace[$plg_tag]);
                //error_log('first pass ' . $firstPass);
                $result.=$firstPass;
                // Fetch the template
                //ob_start();
                //$getTemplatePath = $AllVideosHelper->getTemplatePath($this->plg_name,'default.php',$playerTemplate);
                //$getTemplatePath = $getTemplatePath->file;
                //include($getTemplatePath);
                //$getTemplate = $this->plg_copyrights_start.ob_get_contents().$this->plg_copyrights_end;
                //ob_end_clean();
                /*
                error_log('tag source : ' . $tagsource);
                error_log('tag player : ' . $tagReplace[$plg_tag]);
                $getTemplate='';
                // Output
                $result = preg_replace("#{".$plg_tag."}".preg_quote($tagcontent)."{/".$plg_tag."}#s", $tagsource , $string);
                error_log('tag result : ' . $result);
                */


            } // End second foreach

        } // End if


    }

    $root = JURI::root(false,'');
    if($tagResolved!=null){
        $result = $root.'//'.$tagResolved;
    }

    //error_log('tag result : ' . $result);
    if($_stripHTML){
        //$result=stripHTML($result);
    }

    global $xapp_logger;
    if($xapp_logger){
        $xapp_logger->log('jtag parser : ' . $result);
    }else{
        error_log('hav no logger');
    }

    error_log('returning jtagpers : ' . $result);
    //return '';
    return $result;
}

?>