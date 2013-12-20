<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * XApp-Connect-Filter base class.
 *
 *
 * @package XApp-Connect\Filter
 * @class Xapp_Connect_Filter
 * @error @TODO
 * @author  mc007
 */
class Xapp_Connect_Schema extends Xapp_Connect_Filter
{
    /***
     * @var Our delegate, containing all data and references
     */
    var $delegate=null;

    public static $options=null;
    public static $debug=false;

    private  function replaceUserVariables($str){
        $replaceVars = true;

        $result =  $str;
        if($replaceVars){


            if($this->user!=null)
            {
                $userVars= is_object($this->user) ? (array)$this->user->vars : $this->user['vars'];

                if($userVars){
                    //xapp_dumpObject($userVars);
                    $_keys = array();
                    $_values = array();
                    foreach ($userVars as $key => $value)
                    {
                        array_push($_keys,$key);
                        array_push($_values,$value);
                    }
                    $result = str_replace(
                        $_keys,
                        $_values,
                        $result
                    );
                }
            }
        }

        return $result;
    }

    public function apply($inData,$encode=true){

        $rootSchema = $this->getRootSchema();
        if($rootSchema){

            $subSchemasResolved = null;
            try
            {
                $subSchemasResolved = $this->resolveSchemaDirectives($rootSchema[0]->schema,$inData,$this->getSchemas(),$this->user);

            }catch (Exception $e) {
                error_log('Caught exception: ',  $e->getMessage(), "\n");
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
            $resultStr = $this->replaceUserVariables($resultStr);
            if($encode==false){
                return json_decode($resultStr);
            }
            return $resultStr;
        }else{
            if(self::$debug){
                error_log('have no root schema');
            }
        }
        return $inData;
    }


    /***
     * Returns the key of a given schema by its value
     * @param $schema
     * @param $valIn
     * @return string
     */
    private function getSchemaVariable($schema,$valIn){

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


    /***
     * @param $d
     * @return array
     */
    /**
     * @param $queries
     * @return array
     */
    private function resolveSubSchema($schema,$queryRawResult,$dbOptions)
    {
        $rowsTransformed = array();

        if(!is_array($queryRawResult))
        {
            //$this->dumpObject($queryRawResult);
            return $rowsTransformed;
        }
        $_schema = json_encode($schema);
        $max = 1000;
        $index=0;
        foreach ($queryRawResult as $row)
        {
            $_rowArray = $this->xapp_objectToArray($row);
            


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
                        $rowValueKey = substr($matchgroup[0],1,strlen($matchgroup[0])-2);
                        if($rowValueKey==null){
                            $tIndex++;
                            continue;
                        }
                        $inValue = '' . $rowValueKey;
                        $_tStart = strpos($inValue,'{');
                        $_tEnd = strpos($inValue,'}');
                        $isTemplated = is_numeric($_tStart) && is_numeric($_tEnd) ? 1 : 0;
                        if($isTemplated){

                            $sVar= $this->getSchemaVariable($schema,'%' . $inValue .'%');

                            $_templateCodePost = substr($inValue,$_tEnd+1,strlen($inValue));
                            $_templateCode = substr($inValue,$_tStart,$_tEnd+1 - $_tStart);
                            $_templateCodePre = substr($inValue,0,$_tStart);

                            $resolved = $this->_resolvePHPCode($_templateCode,$row,$dbOptions,$dynaVars);

                            if($resolved!=null){

                                if((strcasecmp($resolved,'0') == 0) || (strcasecmp($resolved,'') == 0)){
                                    $searchReplaceArray[$matchgroup[0]]='';
                                }else{

                                    $final = $_templateCodePre . $resolved . $_templateCodePost;
                                    $searchReplaceArray[$matchgroup[0]]=$final;
                                    if($sVar){
                                        $_keys = array();
                                        $_values = array();

                                        //@TODO VARS - Normalization!

                                        //xapp_dumpObject($dbOptions,'$dbOptions');
                                        if(is_array($dbOptions)){
                                            foreach ($dbOptions['vars'] as $key => $value)
                                            {
                                                array_push($_keys,$key);
                                                array_push($_values,$value);
                                            }
                                        }else if(is_object($dbOptions)){

                                            $_vars = xapp_objectToArray($dbOptions->vars);

                                            foreach ($_vars as $key => $value)
                                            {
                                                array_push($_keys,$key);
                                                array_push($_values,$value);
                                            }
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
                                $arrayValue = xapp_array_get($row,$rowValueKey);
                                if($arrayValue==null){
                                    $arrayValue='';
                                }
                            }else{
                                if($_rowArray){
                                    if(xapp_array_isset($_rowArray,$rowValueKey)){
                                        $arrayValue = $_rowArray[$rowValueKey];
                                        $arrayValue = str_replace('\'', '`', $arrayValue);
                                    }else{
                                        if(self::$debug){
                                            error_log('no such key in result '. $rowValueKey);
                                        }
                                    }
                                }
                            }
                            if($arrayValue!=null)
                            {
                                if(count($arrayValue)>0){
                                    $val = $arrayValue;

                                    if(is_string($val) || is_numeric($val)){
                                        $val = preg_replace('/[\x00-\x1F\x7F]/', '', $val);
                                        $val = addslashes($val);
                                        $searchReplaceArray[$matchgroup[0]]=$val;
                                    }elseif(is_object($val) || is_array($val)){
                                        $searchReplaceArray[$matchgroup[0]]=json_encode($val);
                                        //$this->dumpObject($searchReplaceArray);
                                    }
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


                /***
                 * Thats it, all resolved on the schema
                 */
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
    private function resolveSchemaDirectives($rootSchema,$allQueryResults,$schemas,$dbOptions)
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
            $schemaStr2 =''. $elements[1];
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
            $subSchema=$this->getSubSchema($schemas,$schemaStr);
            if($subSchema==null){
                $subSchema=$this->getSubSchema($schemas,$schemaStr2);
                if($subSchema){
                }else{
                    continue;
                }

                /*$this->log('Schemas dump ' . json_encode($schemas));
                $this->log('alternate schema : ' . $schemaStr2);*/

                //$this->log('Schemas dump ' . xapp_dumpObject($schemas));
                //xapp_dumpObject($elements);


            }

            if($subSchema!=null  && $schemaStr!=null && $queryId!=null)
            {

                $queryResolved = $this->resolveSubSchema($subSchema[0]->schema,$allQueryResults,$dbOptions);

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

    /***
     * @param $template
     * @param $row
     * @param $dbOptions
     * @param $dynaVars
     * @return string
     */
    private function _resolvePHPCode($template,$row,$dbOptions,$dynaVars)
    {
        $_tpl = new Tpl();
        $vars  = array();

        HTMLFilter::$vars = array();


        if($dynaVars){
            while(list($var, $val) = each($dynaVars)) {
                $val = str_replace('\'', '`', $val);
                $vars[$var]=$val;
                $varBase = new stdClass();
                $varBase->key = $var;
                $varBase->value = $val;
                array_push(HTMLFilter::$vars,$varBase);
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
                if(!(is_string($val) || !is_numeric($val)) || is_object($val) || is_object($val)){
                    //$this->dumpObject($val);
                    continue;
                }

                if(is_array($val)){
                    continue;
                }

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

        if(isset($dbOptions) && is_array($dbOptions) && $dbOptions['vars'])
        {
            $dbOptionsVars = $dbOptions['vars'];
        }else{

            if(is_string($dbOptions)){
                $inVars =  json_decode($dbOptions);
                //$dump = print_r($inVars,true);
                if($inVars!=null){
                    $dbOptionsVars=$inVars->vars;
                }
            }
        }

        Xapp_Connect_Schema::$options=$dbOptions;

        if($dbOptionsVars!=null)
        {
            global $rootUrl;
            if($dbOptionsVars['BASEREF']){
                $vars['BASEREF']='' . $dbOptionsVars['BASEREF'];
                $rootUrl =''. $dbOptionsVars['BASEREF'];

                $varBase = new stdClass();
                $varBase->key = 'BASEREF';
                $varBase->value = $dbOptionsVars['BASEREF'];
                array_push(HTMLFilter::$vars,$varBase);
            }

            if($dbOptionsVars['DSUID']){
                $varBase = new stdClass();
                $varBase->key = 'DSUID';
                $varBase->value = $dbOptionsVars['DSUID'];
                array_push(HTMLFilter::$vars,$varBase);
            }

            if($dbOptionsVars['SCREEN_WIDTH']){
                $varBase = new stdClass();
                $varBase->key = 'SCREEN_WIDTH';
                $varBase->value = $dbOptionsVars['SCREEN_WIDTH'];
                array_push(HTMLFilter::$vars,$varBase);
            }

            if($dbOptionsVars['APPID']){
                $varBase = new stdClass();
                $varBase->key = 'APPID';
                $varBase->value = $dbOptionsVars['APPID'];
                array_push(HTMLFilter::$vars,$varBase);
            }

            if($dbOptionsVars['UUID']){
                $varBase = new stdClass();
                $varBase->key = 'UUID';
                $varBase->value = $dbOptionsVars['UUID'];
                array_push(HTMLFilter::$vars,$varBase);
            }

            if($dbOptionsVars['SOURCE_TYPE']){
                $varBase = new stdClass();
                $varBase->key = 'SOURCE_TYPE';
                $varBase->value = $dbOptionsVars['SOURCE_TYPE'];
                array_push(HTMLFilter::$vars,$varBase);
            }
        }else{

        }

        if( (bool)xc_conf(XC_CONF_LOG_TEMPLATE_VARS)){

            $this->dumpObject(HTMLFilter::$vars,'HTML Filter Variables ');
            $this->dumpObject($vars,'Variables In');
        }
        $_tpl->assign( $vars );
        $template= stripslashes($template);
        try{
            $result = $_tpl->draw_string($template,true) ;
        }catch (Exception $e){
            $this->log("Error whilst resolving PHP template : " . $template . " | Message : " . $e->getMessage());
            $result ='';
        }
        return $result;
    }
    /***
     * @return null|array
     */
    private function getSchemas(){

        if($this->schemas){
            return $this->schemas;
        }

        return null;
    }
    /***
     * @param $d
     * @return array
     */
    public function xapp_objectToArray($d) {
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

    /***
     * @return array|null
     */
    private function getRootSchema(){
        $result = null;

        $schemas = $this->getSchemas();
        if($schemas!=null && $schemas[0]!=null && $schemas[0]->isRoot ==1){
            if(is_array($schemas[0]->schema)||is_object($schemas[0]->schema)){
                $schemas[0]->schema = json_encode($schemas[0]->schema);
            }

            $result=array($schemas[0]);

        }
        return $result;
    }

    /***
     * @param $schemas
     * @param $schemaStr
     * @return array|null
     */
    private function getSubSchema($schemas,$schemaStr){
        $result = null;
        if($schemas!=null && $schemas[1]!=null && $schemas[1]->id ==$schemaStr){
            $result=array($schemas[1]);
        }
        return $result;
    }

}