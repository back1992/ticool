<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\SchemaOld
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class SchemaProcessor
 *
 * Needs executed SQL query results, user vars and schemas to resolve. Entry point : templatedQuery, see
 * bottom.
 *
 */

function xapp_objectToArray($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}

class SchemaProcessor {


    var $logger=null;

    /***
     * @param null $logger
     * @param string $defaultName
     */
    public function __construct($logger=null, $defaultName = '') {

        if($logger==null){
            $writer =  new Xapp_Log_Writer_File(XAPP_DEFAULT_LOG_PATH);
            $logging_options = array(
                Xapp_Log::PATH  => XAPP_BASEDIR .'/log/',
                Xapp_Log::EXTENSION  => 'log',
                Xapp_Log::NAME  => 'schema-processor',
                Xapp_Log::WRITER  => array($writer)
            );
            $log = new Xapp_Log_Error($logging_options);
            $this->logger=$log;
        }else{
            $this->logger=$logger;
        }
    }

    public static $options=null;


    public function log($message,$stdError=true){
        if($this->logger){
            $this->logger->log($message);
        }

        if($stdError){
            error_log('Error : '.$message);
        }
    }

    private  function toArray($obj)
    {
        $array = array();
        foreach(get_object_vars($obj) as $k => $v)
        {
            $array[$k] = $obj->$k;
        }
        return $array;
    }
    protected function _selectRootSchema($schemas){
        $result = null;
        if($schemas!=null && $schemas[0]!=null && $schemas[0]->isRoot ==1){
            $result=array($schemas[0]);
        }
        return $result;
    }
    /***
     * @param $schemas
     * @param $queryResults
     * @param $options
     * @return mixed|string
     */
    public function processArgs($schemas, $queryResults,$options)
    {
        /***
        * As next, we walk over the root schema and resolve all sub schemas
         */
        $rootSchema = $this->_selectRootSchema($schemas);
        if(!$rootSchema){
            $this->log("Have no root schema");
            return null;
        }
        //xapp_dumpObject($schemas,'$$ schemass in');
        //$d = print_r($rootSchema,true);
        //$e = print_r($schemas,true);
        //error_log('root schema ' . $d);
        $subSchemasResolved = null;
        try
        {
            $subSchemasResolved = $this->_resolveSchemaDirectives($rootSchema[0]->schema,$queryResults,$schemas,$options);

        }catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        $resolvedAll = '';

        foreach($subSchemasResolved as $sResolved)
        {
            $jsonError = null;
            $addTo = json_encode($sResolved);
            $addTo = str_replace('\/','/',$addTo);
            $addTo = str_replace('[[','[',$addTo);
            $addTo = str_replace(']]',']',$addTo);
            $addTo = preg_replace('/[\x00-\x1F\x7F]/', '', $addTo);

            if( (bool)xc_conf(XC_CONF_LOG_JSON_ERRORS))
            {
                $jsonError = $this->getLastJSONError();
                if($jsonError!=JSON_ERROR_NONE){
                    $this->log('have json encoder error : ' . $jsonError);
                }
            }
            if(isset($sResolved->escapeArray) && $sResolved->escapeArray)
            {
                $addTo = substr($addTo,1,strlen($addTo));
                $addTo = substr($addTo,0,strlen($addTo)-1);
            }else{


            }
            $addTo = str_replace("openExternalLocation(","openExternalLocation('",$addTo);
            $addTo = str_replace("openUrl(","openUrl('",$addTo);
            $addTo = str_replace(",null)","',null)",$addTo);
            $addTo = str_replace("\n","",$addTo);
            $addTo = preg_replace('/\r\n?/', "", $addTo);
            $addTo = str_replace(array("\n", "\r"), "", $addTo);
            $resolvedAll = $resolvedAll . $addTo;
        }
        /**
         * now composite the final response
         */
        $resultStr =''. $rootSchema[0]->schema;

        //now merge sub schema queries into root schema
        if(count($subSchemasResolved))
        {
            $resultStr = str_replace(
                array_keys($subSchemasResolved),
                $resolvedAll,
                $resultStr
            );
        }
        return $resultStr;

    }
    /***
     * Returns the key of a given schema by its value
     * @param $schema
     * @param $valIn
     * @return string
     */
    private function _getSchemaVar($schema,$valIn){

        if($valIn && $schema){
            $valIn = str_replace('\/','/',$valIn);
            while(list($var, $val) = each($schema))
            {
                if($val!=null && is_string($val) && addslashes($val)===$valIn){
                    return $var;
                }
            }
        }
        return null;
    }
    private  function getLastJSONError(){

        $result = null;
        if( (bool)xc_conf(XC_CONF_LOG_JSON_ERRORS))
        {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $result = ' - No errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $result = ' - Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $result =  ' - Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $result = ' - Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $result = ' - Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $result = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $result = ' - Unknown error';
                    break;
            }
        }

        return $result;
    }
    protected function _selectSubSchema($schemas,$schemaStr){
        //xapp_profile('__selectSubSchema:0');
        if($schemas!=null && $schemas[1]!=null && $schemas[1]->id ==$schemaStr){
            $result=array($schemas[1]);
        }else{
            /*
            $result = null;
            try {
                $result = from('$root')->in($schemas)
                    ->where('$root => $root->id =="'.$schemaStr.'"' )
                    ->select('$root');

            }catch (PHPLinq_Exception $e){
                $this->log("PHPLinq Exception whilst selecting sub schema : " . $schemaStr . ' :: ' . $e->getMessage(),true);
            }
            */

        }
        //xapp_profile('__selectSubSchema:1');
        return $result;
    }
    /***
     * @param $d
     * @return array
     */
    /**
     * @param $queries
     * @return array
     */
    private function _resolveSubSchema($schema,$queryRawResult,$dbOptions)
    {
        $rowsTransformed = array();

        //xapp_dumpObject($schema,'###    resolve sub schema');


        if(!is_array($queryRawResult))
        {
            return $rowsTransformed;
        }
        $_schema = json_encode($schema);
        $max = 1000;
        $index=0;

        //error_log('row count : ' . count($queryRawResult));
        foreach ($queryRawResult as $row)
        {
            $_rowArray = xapp_objectToArray($row);

            while(list($var, $val) = each($row))
            {
                if( (bool)xc_conf(XC_CONF_LOG_MYSQL_RESULTS))
                {
                    error_log('     ### have row value ' . $var . ' :: ' .  $val,0);
                }
            }

            $tIndex=0;
            if($index<$max)
            {
                /***
                 * build the search and replace map
                 */
                $searchReplaceArray = array();
                $pattern = '/%.*?%/s';

                $dynaVars = array();

                if(preg_match_all($pattern, $_schema, $matches, PREG_OFFSET_CAPTURE, 3)){


                    foreach ($matches[0] as $matchgroup)
                    {

                        //$dump = print_r($matchgroup,true);
                        //error_log('matchgroup : ' . $dump);

                        $rowValueKey = substr($matchgroup[0],1,strlen($matchgroup[0])-2);
                        if($rowValueKey==null){
                            $tIndex++;
                            continue;
                        }
                        $inValue = '' . $rowValueKey;
                        $_tStart = strpos($inValue,'{');
                        $_tEnd = strpos($inValue,'}');
                        $isTemplated = is_numeric($_tStart) && is_numeric($_tEnd) ? 1 : 0;

                        if(!$isTemplated){
                           // error_log('is not templated!!!');
                        }
                        //$isTemplated=false;
                        if($isTemplated){

                            $sVar= $this->_getSchemaVar($schema,'%' . $inValue .'%');



                            $_templateCodePost = substr($inValue,$_tEnd+1,strlen($inValue));
                            $_templateCode = substr($inValue,$_tStart,$_tEnd+1 - $_tStart);
                            $_templateCodePre = substr($inValue,0,$_tStart);

                            $dbg=false;
                            /*
                            if($_templateCode==='{function=\\"checkUrl($var0)\\"}'){
                                $dbg=true;
                                //xapp_dumpObject($dynaVars,'dynavars before');
                                xapp_dumpObject($schema,'###            schema  ');
                                error_log('sVar ' . $sVar . ' :: ' . $inValue);
                            }
                            */
                            $resolved = $this->_resolvePHPCode($_templateCode,$row,$dbOptions,$dynaVars,$dbg);


                            if($resolved!=null){

                                if((strcasecmp($resolved,'0') == 0) || (strcasecmp($resolved,'') == 0)){
                                    $searchReplaceArray[$matchgroup[0]]='';
                                }else{

                                    $final = $_templateCodePre . $resolved . $_templateCodePost;
                                    $searchReplaceArray[$matchgroup[0]]=$final;
                                    if($sVar){
                                        $_keys = array();
                                        $_values = array();

                                        //xapp_dumpObject($dbOptions->vars,' in vars');
                                        //xapp_dumpObject($dynaVars,' dyna vars');

                                        foreach ($dbOptions->vars as $key => $value)
                                        {
                                            array_push($_keys,$key);
                                            array_push($_values,$value);
                                        }
                                        $finalPost = str_replace(
                                            $_keys,
                                            $_values,
                                            $final
                                        );

                                        $finalPost = str_replace('\/','/',$finalPost);
                                        $dynaVars[$sVar]= stripslashes($finalPost);
                                    }
                                }
                            }else{
                                $searchReplaceArray[$matchgroup[0]]='';
                                if($sVar!=null){
                                    $dynaVars[$sVar]= '';
                                }
                            }
                            $tIndex++;
                            continue;
                            //}
                        }

                        //now if there is value in the mysql row for the key
                        try {
                            $arrayValue = null;
                            if(is_array($row)){
                                $arrayValue = $row[$rowValueKey];
                            }else{
                                if($_rowArray){
                                    $arrayValue = $_rowArray[$rowValueKey];
                                    //$arrayValue = addslashes($arrayValue);
                                    $arrayValue = str_replace('\'', '`', $arrayValue);
                                    //$arrayValue='';
                                /*
                                $rD = print_r($_rowArray,true);
                                error_log('stranger : ' .$rD);

                                error_log('is obj');
                                if(property_exists($row,''.$rowValueKey)){
                                    $arrayValue = $row->$rowValueKey;
                                }else{
                                    /*
                                    ob_start();

                                    var_dump($row);
                                    var_dump($rowValueKey);

                                    $o = ob_get_contents();
                                    ob_end_clean();
                                    file_put_contents("/tmp/a.log", "$o\n", FILE_APPEND);
                                    */
                                    //error_log('prop ' . $rowValueKey . ' doesnt exists');
                                    //$arrayValue = 'dummy';
                                }
                                //$row->$rowValueKey;
                                /*
                                $rD = print_r($row,true);
                                error_log('stranger : ' .$rD);
                                */

                            }


                            if($arrayValue!=null)
                            {
                                if(count($arrayValue)>0){
                                    $val = $arrayValue;
                                    $val = preg_replace('/[\x00-\x1F\x7F]/', '', $val);
                                    $val = addslashes($val);
                                    $searchReplaceArray[$matchgroup[0]]=$val;
                                }else{
                                    $searchReplaceArray[$matchgroup[0]]='';
                                }
                            }else{
                                $searchReplaceArray[$matchgroup[0]]='';
                            }
                        } catch (Exception $e) {
                            $this->log("Error assign resolved sub schema " . $e->getMessage());
                        }

                        $tIndex++;
                    }
                }else{
                    $tIndex++;
                }


                if(count($searchReplaceArray))
                {
                    $rowTransformed = str_replace(
                        array_keys($searchReplaceArray),
                        array_values($searchReplaceArray),
                        $_schema
                    );
                    $p = json_decode($rowTransformed);
                    array_push($rowsTransformed,$p);

                }
            }
            $index++;
        }
        return $rowsTransformed;
    }
    /**
     * @param $queries
     * @return array
     */
    private function _resolveSchemaDirectives($rootSchema,$allQueryResults,$schemas,$dbOptions)
    {
        $allSubSchemasResolved= array();
        $pattern = '/%.*?%/s';
        preg_match($pattern, $rootSchema, $matches, PREG_OFFSET_CAPTURE, 3);
        foreach ($matches as $matchgroup) {

            /*
             *
             */

            $elements = explode('::', $matchgroup[0]);
            if(!$elements){
                continue;
            }
            if(count($elements)<2){
                //continue;
            }

            $schemaStr = substr($elements[1],0,strlen($elements[0]));
            $queryId = substr($elements[0],1,strlen($elements[0]));

            $options = null ;
            if(count($elements)>2)
            {
                $options=substr($elements[2],0,strlen($elements[0]));
            }

            $escapeArray=false;
            if($options!=null){
                if (strpos($options,'escapeArr') !== false) {
                    $escapeArray=true;
                }
            }



            $queryResolved = null;
            /***
             * pick the sub schema
             */
            $subSchema=$this->_selectSubSchema($schemas,$schemaStr);
            if($subSchema==null){
                $this->log('Couldn`t select sub schema ' .$schemaStr);
                continue;
            }

            if($subSchema!=null  && $schemaStr!=null && $queryId!=null)
            {

                $queryResolved = $this->_resolveSubSchema((array)$subSchema[0]->schema,$allQueryResults[$queryId],$dbOptions);

                if(count($queryResolved) > 0)
                {
                    if($escapeArray==1)
                    {
                        if(count($queryResolved)==1){

                            $queryResolvedS=$queryResolved[0];
                            $queryResolvedS->escapeArray=true;
                            $queryResolved=$queryResolvedS;

                        }else if(count($queryResolved)==0){
                            $queryResolvedS='';
                            $queryResolvedS->escapeArray=true;
                        }
                    }else{

                    }
                    $allSubSchemasResolved[$matchgroup[0]]=$queryResolved;
                }else{

                    if($escapeArray==1)
                    {
                        $newEmptyObject = new stdClass();
                        $newEmptyObject->escapeArray=true;
                        $allSubSchemasResolved[$matchgroup[0]]=$newEmptyObject;

                    }else{
                        //error_log('set dummy  '. $subSchema[0]->schema);
                        $allSubSchemasResolved[$matchgroup[0]]=array();
                    }
                }
            }else{
                error_log('no sub schema or schema or queryId');
            }
        }

        return $allSubSchemasResolved;
    }

    public function dumpObject($obj,$prefix=''){
        $d = print_r($obj,true);
        error_log(' dump : ' .$prefix . ' : ' . $d);
    }
    /***
     * @param $template
     * @param $row
     * @param $dbOptions
     * @param $dynaVars
     * @return string
     */
    private function _resolvePHPCode($template,$row,$dbOptions,$dynaVars,$debug=false)
    {
        $_tpl = new Tpl();
        $vars  = array();
        $result = '';

        HTMLFilter::$vars = array();

        if($dynaVars){

            if($debug){
                //xapp_dumpObject($dynaVars,'dynavars in ' . ' for : ' . $template);
            }


            while(list($var, $val) = each($dynaVars)) {
                $val = str_replace('\'', '`', $val);
                $vars[$var]=$val;
                $varBase = new stdClass();
                $varBase->key = $var;
                $varBase->value = $val;
                array_push(HTMLFilter::$vars,$varBase);
            }
        }else{
            if($debug){
                //error_log("########3    have no dyna vars for " . $template);
                //xapp_dumpObject($dynaVars,'dyn in empty');
            }
        }

        if(!(bool)xc_conf(XC_CONF_JOOMLA))
        {
            while(list($var, $val) = each($row)) {

                $val = preg_replace('/[\x00-\x1F\x7F]/', '', $val);
                $val = str_replace('\'', '`', $val);
                $vars[$var]=$val;
                $varBase = new stdClass();
                $varBase->key = $var;
                $varBase->value = $val;
                array_push(HTMLFilter::$vars,$varBase);
            }
        }else{
            foreach($row as $var => $val)
            {
                $val = preg_replace('/[\x00-\x1F\x7F]/', '', $val);
                $val = str_replace('\'', '`', $val);
                $vars[$var]=$val;
                $varBase = new stdClass();
                $varBase->key = $var;
                $varBase->value = $val;
                array_push(HTMLFilter::$vars,$varBase);
            }
        }

        $dbOptionsVars = null;

        if(isset($dbOptions) && isset($dbOptions->vars))
        {
            $dbOptionsVars = $dbOptions->vars;


        }else{

            if(is_string($dbOptions)){
                $inVars =  json_decode($dbOptions);
                //$dump = print_r($inVars,true);
                if($inVars!=null){
                    $dbOptionsVars=$inVars->vars;
                }
            }
        }

        //$this->dumpObject($dbOptions,'$dbOptions');
        //$this->dumpObject($dbOptionsVars,'$dbOptionsVars');
/*
        [31-Jul-2013 13:33:36]  dump : $dbOptions : stdClass Object
    (
        [tablePrefix] =>
    [database] =>
    [vars] => stdClass Object
    (
        [DSUID] => 44053850-26ff-42d2-85f1-77b110977a29
            [BASEREF] => http://192.168.1.37:8888/joomla257/
            [REFID] => 71
            [CTYPE] => JArticleDetail
            [APPID] => myeventsapp1d0
            [RT_CONFIG] => debug
            [UUID] => 11166763-e89c-44ba-aba7-4e9f4fdf97a9
            [SERVICE_HOST] => http://mc007ibi.dyndns.org:8080/XApp-portlet/
            [IMAGE_RESIZE_URL] => http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=
            [SOURCE_TYPE] => jArticleDetail
            [SCREEN_WIDTH] => 320
            [preventCache] => 1
        )

)

[31-Jul-2013 13:33:36]  dump : $dbOptionsVars : stdClass Object
    (
        [DSUID] => 44053850-26ff-42d2-85f1-77b110977a29
    [BASEREF] => http://192.168.1.37:8888/joomla257/
    [REFID] => 71
    [CTYPE] => JArticleDetail
    [APPID] => myeventsapp1d0
    [RT_CONFIG] => debug
    [UUID] => 11166763-e89c-44ba-aba7-4e9f4fdf97a9
    [SERVICE_HOST] => http://mc007ibi.dyndns.org:8080/XApp-portlet/
    [IMAGE_RESIZE_URL] => http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=
    [SOURCE_TYPE] => jArticleDetail
    [SCREEN_WIDTH] => 320
    [preventCache] => 1
)*/

        /*
        global $xappQueryOptions;
        $xappQueryOptions=$dbOptions;
        $vars['options']=$dbOptions;
        */

        SchemaProcessor::$options=$dbOptions;


        if($dbOptionsVars!=null)
        {
            global $rootUrl;
            if(isset($dbOptionsVars->BASEREF)){
                $vars['BASEREF']='' . $dbOptionsVars->BASEREF;
                $rootUrl =''. $dbOptionsVars->BASEREF;

                $varBase = new stdClass();
                $varBase->key = 'BASEREF';
                $varBase->value = $dbOptionsVars->BASEREF;
                array_push(HTMLFilter::$vars,$varBase);
            }

            if(isset($dbOptionsVars->DSUID)){
                $varBase = new stdClass();
                $varBase->key = 'DSUID';
                $varBase->value = $dbOptionsVars->DSUID;
                array_push(HTMLFilter::$vars,$varBase);
            }

            if(isset($dbOptionsVars->SCREEN_WIDTH)){
                $varBase = new stdClass();
                $varBase->key = 'SCREEN_WIDTH';
                $varBase->value = $dbOptionsVars->SCREEN_WIDTH;
                array_push(HTMLFilter::$vars,$varBase);
            }

            if(isset($dbOptionsVars->APPID)){
                $varBase = new stdClass();
                $varBase->key = 'APPID';
                $varBase->value = $dbOptionsVars->APPID;
                array_push(HTMLFilter::$vars,$varBase);
            }

            if(isset($dbOptionsVars->UUID)){
                $varBase = new stdClass();
                $varBase->key = 'UUID';
                $varBase->value = $dbOptionsVars->UUID;
                array_push(HTMLFilter::$vars,$varBase);
            }

            if(isset($dbOptionsVars->SOURCE_TYPE)){
                $varBase = new stdClass();
                $varBase->key = 'SOURCE_TYPE';
                $varBase->value = $dbOptionsVars->SOURCE_TYPE;
                array_push(HTMLFilter::$vars,$varBase);
            }
        }else{

        }
        //$dump = print_r(HTMLFilter::$vars,true);
        //error_log('HTMLFilter vars' . $dump,0);
        if( (bool)xc_conf(XC_CONF_LOG_TEMPLATE_VARS)){

            /*
            $dump = print_r(HTMLFilter::$vars,true);
            error_log('HTMLFilter vars' . $dump,0);
            $dump = print_r($vars,true);
            error_log('template vars' . $dump,0);
            */
        }
        $_tpl->assign( $vars );
        $template= stripslashes($template);
        //$this->log('draw string' .$template,true);
        try{
            $result = $_tpl->draw_string($template,true) ;
            //$result='';
        }catch (Exception $e){
            $this->log("Error whilst resolving PHP template : " . $template . " | Message : " . $e->getMessage());
            $result ='';
        }

        return $result;
    }
}