<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html

 */

/**
 * The class manages custom types and provides some security checks.
 *
 *  Remarks :
 *
 * @package XApp-Connect\Manager
 * @class CustomTypeManager
 * @error 153
 * @author mc007
 */
class CustomTypeManager implements Xapp_Singleton_Interface {

    var $logger=null;

    /***
     * service configuration shortcut.
     */
    var $serviceConfig=null;

    /***
     * internal cache instance
     */
    var $cache=null;

    /**
     * option to specify service config
     *
     * @const SERVICE_CONF
     */
    const SERVICE_CONF         = 'XAPP_CUSTOM_TYPE_MANAGER_SERVICE_CONFIG';
    /**
     * option to specify a cache config
     *
     * @const CACHE_CONF
     */
    const CACHE_CONF         = 'XAPP_CUSTOM_TYPE_MANAGER_CACHE_CONFIG';
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CACHE_CONF       => XAPP_TYPE_ARRAY,
        self::SERVICE_CONF     => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CACHE_CONF         => 0,
        self::SERVICE_CONF       => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CACHE_CONF            => null,
        self::SERVICE_CONF          => null
    );

    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Connect_Plugin
     */
    protected static $_instance = null;

    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Connect_Plugin
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
            self::$_instance->setup();
        }
        return self::$_instance;
    }

    private function _replaceUserVariables($str,$vars){
        $replaceVars = true;

        $result =  $str;
        if($replaceVars){
            $userVars= (array)$vars;
            if($userVars){
                $_keys = array();
                $_values = array();
                foreach ($userVars as $key => $value)
                {
                    array_push($_keys,'%'.$key.'%');
                    array_push($_values,$value);
                }
                $result = str_replace(
                    $_keys,
                    $_values,
                    $result
                );
            }

        }

        return $result;
    }

    /***
     * @param $cType
     * @param $refId
     * @param $options
     * @return string
     */
    public function _runCTypeMySQL($cTypeName,$refId,$options,$fetchRelations=false,$resultOut=null){

        $this->log('run ctype in mysql mode');

        if($resultOut==null){
            $resultOut = new stdClass();
            $resultOut->items = array();
        }

        /***
         * setup parameters
         */
        $platform   = 'IPHONE_NATIVE';
        $rtConfig   = 'debug';
        $appId      = '';
        $uuid       = '';
        if($options){
            if($options->vars->UUID){
                $uuid=$options->vars->UUID;
            }
            if($options->vars->APPID){
                $appId=$options->vars->APPID;
            }
            if($options->vars->RT_CONFIG){
                $rtConfig=$options->vars->RT_CONFIG;
            }

            //fix options
            if(!property_exists($options,'tablePrefix')){
                $options->tablePrefix=null;
            }
            //fix options
            if(!property_exists($options,'database')){
                $options->database=null;
            }
        }

        /**
         * Pickup the custom type
         */
        $cType = $this->getType($cTypeName,$uuid,$appId,$platform,$rtConfig);
        if(!$cType){
            $this->log('    fatal : have no custom type in runCType  : ' . $cTypeName);
            return null;
        }
        /***
         * adjust queries
         */
        $cTypeQueries = CustomTypeManager::getCIStringValue($cType,'queries');
        if($cTypeQueries){
            //$cTypeQueriesNew = $this->_replaceUserVariables($cTypeQueries,$options->vars);
            $cTypeQueries = json_decode($cTypeQueries);
            $cTypeQueries = CustomTypeManager::_completeQueries($cType,$cTypeQueries,$refId);
        }
        /***
         * prepare schemas
         */
        $cTypeSchemas = CustomTypeManager::getCIStringValue($cType,'schemas');
        if($cTypeSchemas){
            $cTypeSchemas = json_decode($cTypeSchemas);
        }

        /***
         * Now run the processor
         */
        $IProcessor = new ISchemaProcessor();

        xapp_hide_errors();

        $result = null;

        $cTypeRelations=null;

        //treat : if recursive but no relations
        if($fetchRelations){

            $cTypeRelations = CustomTypeManager::getCIStringValue($cType,'relations');
            if(is_string($cTypeRelations)){
                $cTypeRelations = json_decode($cTypeRelations,true);
            }

            //something is wrong
            if(!is_array($cTypeRelations)){
                $this->log('    relations are not an array');
                $cTypeRelations=null;
            }

            if($cTypeRelations==null){
                $result = $IProcessor->templatedQuery($cTypeQueries,$cTypeSchemas,$options);
            }
        }

        //xapp_dumpObject($cTypeQueries);
        //xapp_dumpObject($cTypeSchemas);

        if($fetchRelations){

            /***
             * Prepare relations
             */


            //no relations
            if($cTypeRelations==null){
                $this->log('    ctype relations are null, performing single mode');
                if($result){
                    $cTypeResult = array();
                    $cTypeResult['result']=$result;
                    //xapp_dumpObject($cTypeSchemas,'   :: single result');
                    $cTypeResult['fragment']=1;
                    $cTypeResult['fragmentTotal']=1;
                    $cTypeResult['urlSchema']=CustomTypeManager::getCIStringValue($cType,'urlSchema');
                    array_push($resultOut->items,$cTypeResult);
                }else{
                    $this->log('    ctype single mode had no result');
                }
                return $resultOut;
            }



            $relIndex=0;
            //lets work
            foreach($cTypeRelations as $relation){
                //$name = CustomTypeManager::getCIStringValue($relation['type'],'name');
                //error_log('run relation : ' .$relation['type']);
                if($relation['type']!=null){

                    //get the custom type by its url schema
                    $cTypeRel = $this->getTypeByUrlSchema($relation['type'],$uuid,$appId,$platform,$rtConfig);
                    if($cTypeRel==null){
                        $this->log('        couldnt find ctype from relation');
                        continue;
                    }
                    $cTypeRelName = CustomTypeManager::getCIStringValue($cTypeRel,'name');

                    //if its the origin type, store in results and skip execution as we did it already
                    if($cTypeRelName==$cTypeName){
                        $cTypeResult = array();
                        $cTypeResult['result']=$result;
                        $cTypeResult['fragment']=$relIndex +1;
                        $cTypeResult['fragmentTotal']=count($cTypeRelations);
                        $cTypeResult['urlSchema']=$relation['type'];
                        array_push($resultOut->items,$cTypeResult);
                        continue;
                    }

                    //now run it :
                    $customTypeRelResult = $this->runCTypeEx($cTypeRelName,$refId,$options,false,$result);
                    if($customTypeRelResult){
                        $cTypeResult = array();
                        $cTypeResult['result']=$customTypeRelResult;
                        $cTypeResult['fragment']=$relIndex +1;
                        $cTypeResult['fragmentTotal']=count($cTypeRelations);
                        $cTypeResult['urlSchema']=$relation['type'];
                        array_push($resultOut->items,$cTypeResult);
                    }
                }
                $relIndex++;
            }


            return $resultOut;
        }

        return $result;
    }

    private function invokePluginMethod($callee,$options,$cType,$refId){

        $cTypeMethod = CustomTypeManager::getCIStringValue($cType,'method');
        $cTypeUrlSchema = CustomTypeManager::getCIStringValue($cType,'method');
        $cTypeName = CustomTypeManager::getCIStringValue($cType,'name');
        //prepare args
        $args = new stdClass();
        $args->refId=$refId;
        $args->schemas=null;
        $args->options=new stdClass();
        $args->options->tablePrefix='';
        $args->options->database='';
        $args->options->vars=$options->vars;
        $args->options->vars->CTYPE=''.$cTypeName;

        $customTypeResult = null;

        if($cTypeMethod && method_exists($callee,$cTypeMethod)){
            $this->log('calling : ' . $cTypeMethod);
            $customTypeResult =  $callee->$cTypeMethod($args);
            if(!$customTypeResult){
                $customTypeResult="{remove:'true'}";
                $this->log('no ctype results');
            }
        }else{
            error_log('no such ctype method :' . $cTypeMethod . '');
        }
        if($customTypeResult){
            $cTypeResult = array();
            $cTypeResult['result']=$customTypeResult;
            $cTypeResult['urlSchema']=$cTypeUrlSchema;
            return $cTypeResult;
        }

        return null;
    }

    /***
     * Runs a custom type, handling direct RPC classes and pure MySQL types
     *
     * @param $cTypeName
     * @param $refId
     * @param $options
     * @param bool $fetchRelations
     * @param null $resultOut
     * @param null $callee
     * @param null $params
     * @return null|stdClass|string
     */
    public function runCTypeEx($cTypeName,$refId,$options,$fetchRelations=false,$resultOut=null,$callee=null,$params=null){

        //error_log('running ' . $cTypeName . ' for refId : ' . $refId);

        if($resultOut==null){
            $resultOut = new stdClass();
            $resultOut->items = array();
        }

        /** minimum parameters */
        $platform   = 'IPHONE_NATIVE';
        $rtConfig   = 'debug';
        $appId      = '';
        $uuid       = '';
        /** mixin args */
        if($options){
            if($options->vars->UUID){
                $uuid=$options->vars->UUID;
            }
            if($options->vars->APPID){
                $appId=$options->vars->APPID;
            }
            if($options->vars->RT_CONFIG){
                $rtConfig=$options->vars->RT_CONFIG;
            }
        }

        /**
         * Pickup the custom type
         */
        $cType = $this->getType($cTypeName,$uuid,$appId,$platform,$rtConfig);
        if(!$cType){
            //error_log('fatal : have no custom type in runCType  : ' . $cTypeName);
            return null;
        }
        $cTypeDriverType = CustomTypeManager::getCIStringValue($cType,'clientDriverClass');
        if($cTypeDriverType ==='xapp.connect.driver.MySQL'){
            $cTypeManger = CustomTypeManager::instance();
            return $cTypeManger->_runCTypeMySQL($cTypeName,$refId,$options,true,null);
        }


        if($fetchRelations){

            if($callee==null){
                return "{}";
            }
            /***
             * Prepare relations
             */
            $cTypeRelations = CustomTypeManager::getCIStringValue($cType,'relations');

            if(is_string($cTypeRelations)){
                $cTypeRelations = json_decode($cTypeRelations,true);
            }else{

            }
            //something is wrong
            if(!is_array($cTypeRelations)){
                $cTypeRelations=null;
            }

            //has no relations, run the requested type
            if($cTypeRelations==null){

                $cTypeResult = $this->invokePluginMethod($callee,$options,$cType,$refId);
                $cTypeResult['fragment']=1;
                $cTypeResult['fragmentTotal']=1;
                array_push($resultOut->items,$cTypeResult);
                return $resultOut;
            }

            $relIndex=0;
            foreach($cTypeRelations as $relation){
                if($relation['type']!=null){

                    //get the custom type by its url schema
                    $cTypeRel = $this->getTypeByUrlSchema($relation['type'],$uuid,$appId,$platform,$rtConfig);
                    if($cTypeRel==null){
                        continue;
                    }
                    $cTypeRelationResult = $this->invokePluginMethod($callee,$options,$cTypeRel,$refId);
                    $cTypeRelationResult['fragment']=$relIndex +1;
                    $cTypeRelationResult['fragmentTotal']=count($cTypeRelations);
                    array_push($resultOut->items,$cTypeRelationResult);
                }
                $relIndex++;
            }
            return $resultOut;

        }else{
            //run the the custom type only
            $cTypeResult = $this->invokePluginMethod($callee,$options,$cType,$refId);
            $cTypeResult['fragment']=1;
            $cTypeResult['fragmentTotal']=1;
            array_push($resultOut->items,$cTypeResult);
            return $resultOut;

        }
    }

    /***
     * backup only
     * @param $cTypeName
     * @param $refId
     * @param $options
     * @param bool $fetchRelations
     * @param null $resultOut
     * @param null $callee
     * @param null $params
     * @return array|null|stdClass|string
     */
    public function runCTypeEx2($cTypeName,$refId,$options,$fetchRelations=false,$resultOut=null,$callee=null,$params=null){


        if($resultOut==null){
            $resultOut = new stdClass();
            $resultOut->items = array();
        }
        /***
         * setup parameters
         */
        $platform   = 'IPHONE_NATIVE';
        $rtConfig   = 'debug';
        $appId      = '';
        $uuid       = '';
        if($options){
            if($options->vars->UUID){
                $uuid=$options->vars->UUID;
            }
            if($options->vars->APPID){
                $appId=$options->vars->APPID;
            }
            if($options->vars->RT_CONFIG){
                $rtConfig=$options->vars->RT_CONFIG;
            }
        }

        /**
         * Pickup the custom type
         */
        $cType = $this->getType($cTypeName,$uuid,$appId,$platform,$rtConfig);
        if(!$cType){
            //error_log('fatal : have no custom type in runCType  : ' . $cTypeName);
            return null;
        }
        $cTypeDriverType = CustomTypeManager::getCIStringValue($cType,'clientDriverClass');
        if($cTypeDriverType ==='xapp.connect.driver.MySQL'){
            $cTypeManger = CustomTypeManager::instance();
            return $cTypeManger->_runCTypeMySQL($cTypeName,$refId,$options,true,null);
        }

        //error_log('$$$$$$$$$$$$$        run ctype in rpc mode');

        /***
         * adjust queries
         */
        $cTypeQueries = CustomTypeManager::getCIStringValue($cType,'queries');
        if($cTypeQueries){
            $cTypeQueries = json_decode($cTypeQueries);
        }

        if($cTypeQueries){
            $cTypeQueries = CustomTypeManager::_completeQueries($cType,$cTypeQueries,$refId);
        }
        //$dump = print_r($cTypeQueries,true);
        //error_log('ctype queries' . $dump,0);

        /***
         * prepare schemas
         */
        $cTypeSchemas = CustomTypeManager::getCIStringValue($cType,'schemas');
        if($cTypeSchemas){
            $cTypeSchemas = json_decode($cTypeSchemas);
        }
        $cTypeMethod = CustomTypeManager::getCIStringValue($cType,'method');
        $className = get_class($callee);
        //error_log("class name : " . $className);


        /***
         * Now run the processor
         */
        //$IProcessor = new ISchemaProcessor();

        //$result = $IProcessor->templatedQuery($cTypeQueries,$cTypeSchemas,$options);
        $result = array();

        if($fetchRelations){

            if($callee==null){
                return "{}";
            }

            /***
             * Prepare relations
             */
            $cTypeRelations = CustomTypeManager::getCIStringValue($cType,'relations');
            //error_log('relations : ' . $cTypeRelations);
            if(is_string($cTypeRelations)){
                $cTypeRelations = json_decode($cTypeRelations,true);
            }

            //no relations
            if($cTypeRelations==null){
                //error_log('have no relations : ' . $cTypeName);
                return $result;
            }

            //something is wrong
            if(!is_array($cTypeRelations)){
                //error_log('relations are not an array');
                return $result;
            }

            $relIndex=0;
            //lets work
            foreach($cTypeRelations as $relation){
                //$name = CustomTypeManager::getCIStringValue($relation['type'],'name');
                //error_log('run relation : ' .$relation['type']);
                if($relation['type']!=null){

                    //get the custom type by its url schema
                    $cTypeRel = $this->getTypeByUrlSchema($relation['type'],$uuid,$appId,$platform,$rtConfig);
                    if($cTypeRel==null){
                        continue;
                    }
                    $cTypeRelName = CustomTypeManager::getCIStringValue($cTypeRel,'name');
                    $cTypeMethod = CustomTypeManager::getCIStringValue($cTypeRel,'method');
                    //if its the origin type, store in results and skip execution as we did it already
                    /*
                    if($cTypeRelName==$cTypeName){
                        $cTypeResult = array();
                        $cTypeResult['result']=$result;
                        $cTypeResult['fragment']=$relIndex +1;
                        $cTypeResult['fragmentTotal']=count($cTypeRelations);
                        $cTypeResult['urlSchema']=$relation['type'];
                        array_push($resultOut->items,$cTypeResult);
                        continue;
                    }
                    */

                    //now run it :
                    //$customTypeRelResult = $this->runCTypeEx($cTypeRelName,$refId,$options,false,$result);
                    $customTypeRelResult = null;

                    //$options->vars->CTYPE=''.$cTypeRelName;

                    //prepare args
                    $args = new stdClass();
                    $args->refId=$refId;
                    $args->schemas=null;
                    $args->options=new stdClass();
                    $args->options->tablePrefix='';
                    $args->options->database='';
                    $args->options->vars=$options->vars;

                    /*
                    $args->options->vars = new stdClass();


                    $args->$options->vars->{XAPP_CONNECT_VAR_DSUID}=$params[XAPP_CONNECT_VAR_DSUID];
                    $args->$options->vars->{XAPP_CONNECT_VAR_BASEREF}=$params[XAPP_CONNECT_VAR_BASEREF];
                    $args->$options->vars->{XAPP_CONNECT_VAR_RT_CONFIG}=$params[XAPP_CONNECT_VAR_RT_CONFIG];
                    $args->$options->vars->{XAPP_CONNECT_VAR_APPID}=$params[XAPP_CONNECT_VAR_APPID];
                    $args->$options->vars->{XAPP_CONNECT_VAR_UUID}=$params[XAPP_CONNECT_VAR_UUID];
                    $args->$options->vars->{XAPP_CONNECT_VAR_BASEREF}=$params[XAPP_CONNECT_VAR_BASEREF];
                    $args->$options->vars->{XAPP_CONNECT_VAR_CTYPE}=$params[XAPP_CONNECT_VAR_CTYPE];




                    $args->$options->vars->{XAPP_CONNECT_VAR_SERVICE_HOST}=$params[XAPP_CONNECT_VAR_SERVICE_HOST];
                    if(xapp_array_get($params,XAPP_CONNECT_VAR_PREVENT_CACHE)!=null){
                        $args->$options->vars->{XAPP_CONNECT_VAR_PREVENT_CACHE}=$params[XAPP_CONNECT_VAR_PREVENT_CACHE];
                    }

                    */

                    //error_reporting(E_ALL);
                    //ini_set('display_errors', 1);

                    if($cTypeRelName && $callee){
                        $args->options->vars->CTYPE=''.$cTypeRelName;
                        //error_log('updated ctyle');
                        //$callee->forceNewCType=''.$cTypeRelName;

                    }
                    $callee->xcType=null;
                    $callee->xcOptions=null;
                    $this->xcSchemas=null;


                    //error_log('ctype rel var : ' . $args->options->vars->CTYPE . ' || new ' . $cTypeRelName);


                    //$args->options->vars->{XAPP_CONNECT_VAR_CTYPE}=$cTypeRelName;
                    //$callee->_setVar("CTYPE",$cTypeRelName);

                    //xapp_dumpObject($options->vars,'options in');


                    //error_log('run with options ' . json_encode($args));




                    /*
                                            "refId": "0",
                      "schemas": [
                        {
                            "isRoot": true,
                          "schema": "{\\"class\\":\\"pmedia.types.CList\\",\\"items\\":%query_items::schema_items%}",
                          "id": "root"
                        },
                        {
                            "isRoot": false,
                          "id": "schema_items",
                          "schema": {
                            "refId": "%refId%",
                            "groupId": "%groupId%",
                            "title": "%title%",
                            "published": "%published%",
                            "dataSource": "DSUID",
                            "sourceType": "vmProductDetail",
                            "iconUrl": "%iconUrl%",
                            "ownerRefStr": "%price%",
                            "introText": "%description%",
                            "dateString": "%inStock%",
                            "rating": "%rating%",
                            "ratings": "%ratings%",
                            "rawPrice": "%rawPrice%"
                          }
                        }
                      ],
                      "options": "{\\"tablePrefix\\":\\"\\",\\"database\\":\\"\\",\\"vars\\":{\\"DSUID\\":\\"3ae77049-170c-41f6-91f3-d4e1b95654ed\\",\\"BASEREF\\":\\"http://192.168.1.37/joomla251/\\",\\"REFID\\":\\"0\\",\\"CTYPE\\":\\"VMartProduct\\",\\"APPID\\":\\"myeventsapp1d0\\",\\"RT_CONFIG\\":\\"debug\\",\\"UUID\\":\\"11166763-e89c-44ba-aba7-4e9f4fdf97a9\\",\\"SERVICE_HOST\\":\\"http://mc007ibi.dyndns.org:8080/XApp-portlet/\\",\\"IMAGE_RESIZE_URL\\":\\"http://192.168.1.37: 8080/XApp-portlet/servlets/ImageScaleIcon?src=\\",\\"SOURCE_TYPE\\":\\"vmProduct\\",\\"SCREEN_WIDTH\\":320,\\"preventCache\\":true}}"
                    }
                                        */
                    /*
                    if($callee==null){
                        error_log('calle is null');
                    }
                    if($callee->loaded){
                        error_log('calle is loaded');
                    }else{
                        error_log('calle is not loaded');
                    }
                    xapp_dumpObject($callee);
                    */
                    if($cTypeMethod && method_exists($callee,$cTypeMethod)){
                        error_log('calling : ' . $cTypeMethod);
                        $customTypeRelResult =  $callee->$cTypeMethod($args);
                        if($customTypeRelResult){
                            error_log('have ctype rel result !!! : ' . $cTypeRelName);
                            //xapp_dumpObject($customTypeRelResult);
                        }else{
                            error_log('have NO NO NO ctype rel result !!! ' . $cTypeRelName);
                            $customTypeRelResult="{remove:'true'}";
                        }
                    }else{
                        error_log('wrong in relation');
                    }
                    if($customTypeRelResult){
                        $cTypeResult = array();
                        $cTypeResult['result']=$customTypeRelResult;
                        $cTypeResult['fragment']=$relIndex +1;
                        $cTypeResult['fragmentTotal']=count($cTypeRelations);
                        $cTypeResult['urlSchema']=$relation['type'];
                        array_push($resultOut->items,$cTypeResult);
                    }
                }
                $relIndex++;
            }


            return $resultOut;
        }

        return $result;
    }

    public function test(){

        xapp_dumpObject($this," ###  ct manager now !");
        if(xapp_is_option(self::CACHE_CONF, $this) && $this->serviceConfig){
            error_log("## have options !");
        }
   }

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
    }

    private function setup(){
        $this->serviceConfig = xapp_get_option(self::SERVICE_CONF,$this);

        //cache
        if(xapp_is_option(self::CACHE_CONF, $this) && $this->serviceConfig){
            $cacheConfig = xapp_get_option(self::CACHE_CONF);
            if($cacheConfig){
                $this->cache = Xapp_Cache::instance("CTManager","file",array(
                    Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$this->serviceConfig),
                    Xapp_Cache_Driver_File::CACHE_EXTENSION=>"ctmanager",
                    Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>500
                ));
            }
        }

    }

    /***
     * Creates the arguments for the Schema Processor by a give Custom Type and ref id.
     *
     * @param $cType
     * @param $refId
     * @param $options
     * @return stdClass
     */
    public static function toSchemaProcessorArgStructEx($cType,$refId,$options){

        return null;
    }

    /**
     * Appends the CT's queries with the default group select statement and a ref id.
     * @param $cType
     * @param $queries
     * @param $refId
     * @return mixed
     */
    protected static function _completeQueries($cType,$queries,$refId,$vars=null){
        $cTypeGroupSelectStatment = CustomTypeManager::getCIStringValue($cType,'groupSelectStatement');
        $cTypeOderSelectStatment = CustomTypeManager::getCIStringValue($cType,'orderSelectStatement');
        if($cTypeGroupSelectStatment){
            foreach($queries as $q) {

                if(strpos($cTypeGroupSelectStatment,'%REFID%')==false){
                    $q->query = $q->query . ' ' . $cTypeGroupSelectStatment . ' ' . $refId;
                }else{
                    $_cTypeGroupSelectStatment = str_replace('%REFID%',$refId,$cTypeGroupSelectStatment);
                    $q->query = $q->query . ' ' . $_cTypeGroupSelectStatment;
                }

                if($cTypeOderSelectStatment!=null && strlen($cTypeOderSelectStatment)>0){
                    $q->query = $q->query . ' ' . $cTypeOderSelectStatment;
                }
            }
        }
        return $queries;

    }

    private  static function _resolveJSONPathQuery($json,$query){
        $result = null;
        $jsonPathObject = JsonStore::asObj($json);
        if($jsonPathObject){
            $x =&JsonStore::get($jsonPathObject,$query);
            if($x!=null){
                $result=$x;
            }else{
                //error_log('couldnt find anything with ' . $query);
            }
        }else{
            //error_log('$jsonPathObject ==null');
        }
        return $result;
    }
    /***
     * @param $cTypeName
     * @param $refId
     * @param $options
     * @param $query
     * @return null | string
     */
    public static function customTypeQuery($cTypeName,$refId,$options,$query,$subQuery){

        if(CustomTypeManager::$cache==null){
            CustomTypeManager::$cache = CacheFactory::createDefaultCache();
        }

        $cacheKey = md5( $cTypeName . $refId . $query . $subQuery).'.subCTypeQuery';
        $cached = CustomTypeManager::$cache->get_cache($cacheKey);
        if($cached!=null){
            return $cached;
        }

        $result = null;
        $json = CustomTypeManager::runCType($cTypeName,$refId,$options);
        if($json){
            $jsonPathObject = JsonStore::asObj($json);
            if($jsonPathObject){
                $x =&JsonStore::get($jsonPathObject,$query);
                if($x){
                    if(is_string($x) && $subQuery!=null){
                        $y = CustomTypeManager::_resolveJSONPathQuery($x,$subQuery);
                        if($y!=null){
                            $x=''.$y;
                        }
                    }
                    $result=$x;

                }
            }
        }


        //store response in cache
        if($result!=null ){
            CustomTypeManager::$cache->set_cache($cacheKey,$result);
        }
        return $result;
    }



    /***
     * @param $cType
     * @param $refId
     * @param $options
     * @return string
     */
    public static function runCType($cTypeName,$refId,$options){


        $platform   = 'IPHONE_NATIVE';
        $rtConfig   = 'debug';
        $appId      = '';
        $uuid       = '';

        if($options){
            if($options->vars->UUID){
                $uuid=$options->vars->UUID;
            }
            if($options->vars->APPID){
                $appId=$options->vars->APPID;
            }
            if($options->vars->RT_CONFIG){
                $rtConfig=$options->vars->RT_CONFIG;
            }
        }


         /* Pickup the custom type
         */
        $cType = CustomTypeManager::getType($cTypeName,$uuid,$appId,$platform,$rtConfig);
        if(!$cType){
            error_log('fatal : have no custom type in runCType  : ' . $cTypeName);
            return null;
        }

        /***
         * adjust queries
         */
        $cTypeQueries = CustomTypeManager::getCIStringValue($cType,'queries');
        if($cTypeQueries){
            $cTypeQueries = json_decode($cTypeQueries);
        }

        if($cTypeQueries){
            $cTypeQueries = CustomTypeManager::_completeQueries($cType,$cTypeQueries,$refId);
        }
        /***
         * prepare schemas
         */
        $cTypeSchemas = CustomTypeManager::getCIStringValue($cType,'schemas');
        if($cTypeSchemas){
            $cTypeSchemas = json_decode($cTypeSchemas);
        }

        /***
         * Now run the processor
         */
        $IProcessor = new ISchemaProcessor();

        $result = $IProcessor->templatedQuery($cTypeQueries,$cTypeSchemas,$options);


        return $result;
    }


    /**
     * Downloads all custom types as a single array from xapp-studio.com
     * @param $serviceUrl
     * @param string $uuid
     * @param string $appId
     * @param string $platform
     * @param string $rtConfig
     * @return null|string
     */

    public function checkServiceUrl($serviceUrl){

        if( !(bool)xc_conf(XC_CONF_CHECK_SERVICE_HOST)){
            return true;
        }

        xapp_hide_errors();

        //$lDiff =strcmp($serviceUrl,"http://mc007ibi.dyndns.org:8080/XApp-portlet/");
        $rDiff =strcmp($serviceUrl,xapp_get_option(XC_CONF_SERVICE_HOST,$this->serviceConfig));
        if($rDiff>2){
            error_log('wrong service host : ' . $serviceUrl . ' rduff ' . $rDiff);
            return false;
        }else{

        }
        $host = parse_url($serviceUrl);

        if(!$host){
            return false;
        }
        if($host['host']){
            $host=$host['host'];
        }else{
            return false;
        }
        $ip = gethostbyname($host);
        if(!$ip){
            return false;
        }
        $rDiff =strcmp($serviceUrl,"144.76.12.121");
        if($rDiff!=0){
        }else{
            error_log('wrong service host : ' . $ip);
            return false;
        }
        return true;

    }
    public function getCTypesFromUrl($serviceUrl,$uuid='',$appId='',$platform='IPHONE_NATIVE',$rtConfig='debug'){

        if(!$this->checkServiceUrl($serviceUrl)){
            error_log("service url invalid");
            return null;
        }

        $url = $serviceUrl . 'client?action=getCustomTypes&uuid=' . $uuid . '&appIdentifier=' . $appId .'&rtConfig='.$rtConfig;
        //error_log('downloading from service url . ' .$url);
        //xapp_show_errors();
        $content = file_get_contents($url);
        if($content && count($content) >0){

        }else{
            $serviceUrl = 'http://www.xapp-studio.com/XApp-portlet/';
            $url = $serviceUrl . 'client?action=getCustomTypes&uuid=' . $uuid . '&appIdentifier=' . $appId .'&rtConfig='.$rtConfig;
            $content = file_get_contents($url);
        }

        if($content && count($content) >0){

            $cacheKey = md5($platform.$rtConfig).'.ctypes';
            $this->cache->set($cacheKey,$content);
            return $content;
        }else{

            error_log('error downloading ctypes from ' . $serviceUrl);
        }
        return null;
    }
    public static function getCType($ctypes,$name){
        if(!$ctypes){
            return null;
        }
        foreach($ctypes as $ct) {
            $cTypename = self::getCIStringValue($ct,'name');
            if($cTypename && $cTypename===$name){
                return $ct;
            }
        }

        return null;
    }
    public static function getCTypeByUrlSchema($ctypes,$name){
        if(!$ctypes){
            return null;
        }
        foreach($ctypes as $ct) {
            $cTypename = self::getCIStringValue($ct,'urlSchema');
            if($cTypename && $cTypename===$name){
                return $ct;
            }
        }

        return null;
    }
    public static function getCIStringValue($ctype,$name){
        if(!$ctype){
            return null;
        }

        $inputs = null;

        //weird
        if(is_object($ctype) && isset($ctype->inputs)){
            $inputs=$ctype->inputs;
        }else if(is_array($ctype) && is_array($ctype['inputs'])){
            $inputs=$ctype['inputs'];
        }

        //still very weird
        foreach($inputs as $ci) {
            if(is_array($ci)){
                if($ci['name']===$name){
                    return $ci['value'];
                }
            }else if(is_object($ci)){
                if($ci->name===$name){
                    return $ci->value;
                }
            }
        }
        return null;
    }
    /***
     * @param $name
     * @param string $uuid
     * @param string $appId
     * @param string $platform
     * @param string $rtConfig
     * @return CustomType
     */
    public function getTypeFromCache($name,$uuid='',$appId='',$platform='IPHONE_NATIVE',$rtConfig='debug'){

        $cacheKey = md5($platform.$rtConfig).'.ctypes';
        $ctypeContent = $this->cache->get($cacheKey);
        if($ctypeContent){
            $ctype = $this->getCType(json_decode($ctypeContent),$name);
            if($ctype){
                return $ctype;
            }
        }
        return null;
    }
    /***
     * @param $name
     * @param string $uuid
     * @param string $appId
     * @param string $platform
     * @param string $rtConfig
     * @return CustomType
     */
    public function getTypeByUrlSchemaFromCache($name,$uuid='',$appId='',$platform='IPHONE_NATIVE',$rtConfig='debug'){

        $cacheKey = md5($platform.$rtConfig).'.ctypes';
        $ctypeContent = $this->cache->get($cacheKey);
        if($ctypeContent){
            $ctype = $this->getCTypeByUrlSchema(json_decode($ctypeContent),$name);
            if($ctype){
                return $ctype;
            }
        }
        return null;
    }

    /***
     * @param $name
     * @param string $uuid
     * @param string $appId
     * @param string $platform
     * @param string $rtConfig
     * @param bool $preventCache
     * @return CustomType|null
     */
    public function getType($name,$uuid='',$appId='',$platform='IPHONE_NATIVE',$rtConfig='debug',$preventCache=false){


        //error_log('ctype platform : ' . $platform);

        //error_log(json_encode($this->serviceConfig));



        $platform='IPHONE_NATIVE';


        /**
         * 1st trial : try from cache
         */
        if(!$preventCache){
            $cTypeCached = self::getTypeFromCache($name,$uuid,$appId,$platform,$rtConfig);
            if($cTypeCached){
                if($cTypeCached!=null){
                    return $cTypeCached;
                }
            }
        }

        /***
         * 2nd trial : download to cache
         */
        $ctypesServiceUrl = xapp_get_option(XC_CONF_SERVICE_HOST,$this->serviceConfig);

        $ctContent = $this->getCTypesFromUrl($ctypesServiceUrl,$uuid,$appId,$platform,$rtConfig);
        if($ctContent!=null){
            return $this->getCType(json_decode($ctContent),$name);
        }



        $filePath = XAPP_CTYPES . $platform . DS. $rtConfig . DS . $name .'.json';
        if(file_exists($filePath)){
            $ctype = json_decode(file_get_contents($filePath), true);
            if($ctype){
                return $ctype;
            }
        }else{
            error_log('ctype path incorrect ' . $filePath);
        }
        return null;
    }
    /***
     * @param $name
     * @param string $uuid
     * @param string $appId
     * @param string $platform
     * @param string $rtConfig
     * @param bool $preventCache
     * @return CustomType|null
     */
    public function getTypeByUrlSchema($name,$uuid='',$appId='',$platform='IPHONE_NATIVE',$rtConfig='debug',$preventCache=false){

        /**
         * 1st trial : try from cache
         */
        if(!$preventCache){
            $cTypeCached = self::getTypeByUrlSchemaFromCache($name,$uuid,$appId,$platform,$rtConfig);
            if($cTypeCached){
                if($cTypeCached!=null){
                    return $cTypeCached;
                }
            }
        }

        /***
         * 2nd trial : download to cache
         */
        $ctypesServiceUrl = xapp_get_option(XC_CONF_SERVICE_HOST,$this->serviceConfig);

        $ctContent = $this->getCTypesFromUrl($ctypesServiceUrl,$uuid,$appId,$platform,$rtConfig);
        if($ctContent!=null){
            return $this->getCTypeByUrlSchema(json_decode($ctContent),$name);
        }



        $filePath = XAPP_CTYPES . $platform . DS. $rtConfig . DS . $name .'.json';
        if(file_exists($filePath)){
            $ctype = json_decode(file_get_contents($filePath), true);
            if($ctype){
                return $ctype;
            }
        }else{
            error_log('ctype path incorrect ' . $filePath);
        }
        return null;
    }
    public function log($message,$prefix='',$stdError=true){
        if($this->logger){
            $this->logger->log("Custom Type Manager ::".$message);
        }else{
        }
        if($stdError){
            error_log('Custom Type Manager Error : '.$message);
        }
        return null;
    }
}