<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\SQL2JSONService
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 */
class SQL2JSONService
{
    var $logger=null;
    /**
     * Template Engine
     */
    var $tpl=null;

    /**
     *  Database up-link
     */
    var $dbgate=null;

    /**
     * Response cache
     */
    var $cache=null;

    /**
     * Response cache
     */
    var $responseCache=null;

    /**
     * Black and white lists for queries
    */
    var $whiteList=null;
    var $blackList=null;
    public function log($message,$stdError=true){
        if($this->logger){
            $this->logger->log($message);
        }

        if($stdError){
            error_log('Error : '.$message);
        }
    }
    /**
     * Initialize the DataBase connection parameters.
     */
    public function setDBGateWay($dbgate)
    {
        $this->dbgate = $dbgate;
    }

    /**
     * Setter
     * @param $list
     */
    public function setWhiteList($list)
    {
        $this->whiteList = $list;
    }
    /***
     * Settter
     * @param $list
     */
    public function setBlackList($list)
    {
        $this->blackList = $list;
    }

    /***
     * Checks a particular term of a query to be valid against a black list
     * @param $difference
     * @return bool
     */
    public function isValidDifference($difference)
    {
        if($difference==null){
            return true;
        }

        if(strlen($difference)==0){
            return true;
        }
        $diff = strtolower($difference);
        foreach ($this->blackList as $_item)
        {
            $has = strpos($diff,strtolower($_item));
            if($has)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks a SQL Query against a white and black list
     * @param $query
     * @return bool
     */
    public function isValidQuery($query)
    {

        $result = true;

        $isCached= SQLConfig::getFromCache($query);
        if($isCached!=null){
            return $isCached;
        }

        foreach ($this->whiteList as $_item)
        {
            $validQ = base64_decode($_item);
            if($validQ==null){
                continue;
            }

            $validQ = substr($validQ,1,strlen($validQ)-2);

            if($validQ!=null && $query!=null){
                $cdiff  = new diff_match_patch();
                $diffs = $cdiff->diff_main($validQ,$query);
                foreach ($diffs as $diffArray)
                {
                    foreach ($diffArray as $diff)
                    {
                        if(!$this->isValidDifference($diff))
                        {
                            $result = false;
                            break;
                        }
                    }
                }
            }
        }
        SQLConfig::setCache($query,$result);
        return $result;
    }


    /***
     * Nothing to do right now
     */
    public function _init(){

    }


    /***
     * Singleton accessor
     * @static
     * @return null|SQL2JSONService
     */
    public static function Instance()
    {
        $destroy = false;
        static $inst = null;
        if($destroy){
            $inst = null;
        }

        if ($inst === null) {
            $inst = new SQL2JSONService();
            $inst->_init();
        }
        return $inst;
    }
    /**
     * @param $queries
     * @return array
     */
    private function _excecuteQueries($queries,$dbOptions)
    {
        $allQueryResults= array();
        if($this->dbgate==null){
            $this->log('have no data base gate');
            return null;
        }

        /***
         * run all queries and keep the raw results
         */
        foreach($queries as $query)
        {

            $qobject=new stdClass();
            if(is_array($query)){
                foreach ($query as $key => $value)
                {
                    $qobject->$key = $value;
                }
                $query=$qobject;
            }
            $q = '' . $query->query;

            if( (bool)xc_conf(XC_CONF_HAS_CMS_AUTH))
            {
                //xapp_dumpObject($q,'_excecuteQueries::queries');
                //error_log('q::'.$q);
                if((bool)xc_conf(XC_CONF_JOOMLA))
                {
                    $xappAuth = new XAppJoomlaAuth();
                    $q = $xappAuth->adjustSQLQuery($q);
                    //error_log('new q : ' . $q );
                    //SELECT #__content.id refId, #__content.introtext AS introText, #__content.fulltext AS fText, #__content.images AS images, #__content.attribs AS attribs, DATE_FORMAT(#__content.modified,'%Y-%m-%d-%H-%i') AS dateString, DATE_FORMAT(#__content.created,'%Y-%m-%d-%H-%i') AS createdString, DATE_FORMAT(#__content.publish_up,'%Y-%m-%d-%H-%i') AS pubStart, DATE_FORMAT(#__content.publish_down,'%Y-%m-%d-%H-%i') AS pubEnd, #__content.title title, #__content.catid groupId, #__content.state published, #__users.username ownerRefStr FROM #__content LEFT JOIN #__users ON #__users.id = #__content.modified_by  WHERE state = 1 AND access= 1  AND catid  = 2
                    //SELECT #__k2_items.id refId, #__k2_items.introtext AS introText, #__k2_items.fulltext AS fText, DATE_FORMAT(#__k2_items.modified,'%Y-%m-%d-%H-%i') AS dateString, DATE_FORMAT(#__k2_items.created,'%Y-%m-%d-%H-%i') AS createdString, DATE_FORMAT(#__k2_items.publish_up,'%Y-%m-%d-%H-%i') AS pubStart, DATE_FORMAT(#__k2_items.publish_down,'%Y-%m-%d-%H-%i') AS pubEnd, #__k2_items.title title, #__k2_items.catid groupId, #__k2_items.published published, #__users.username ownerRefStr FROM #__k2_items LEFT JOIN #__users ON #__users.id = #__k2_items.modified_by  WHERE #__k2_items.published = 1  AND #__k2_items.catid  = 1 ORDER BY #__k2_items.created DESC, #__k2_items.modified DESC
                    #__k2_items.access = 1  AND #__k2_items.catid  = 1
                    //AND access= 1
                }
            }
            //xapp_dumpObject($dbOptions,'db options');

            if($dbOptions!=null && isset($dbOptions->tablePrefix) && $dbOptions->tablePrefix!=null)
            {
                if($dbOptions->tablePrefix==null){
                    $dbOptions->tablePrefix="";
                }
                if(!(bool)xc_conf(XC_CONF_JOOMLA))
                {
                    $q= str_replace('#__',$dbOptions->tablePrefix,$q);
                }

                $q = str_replace(array("\n", "\r"), array('', ''), $q);
                $q = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $q);
                $q = ltrim($q);
                $q = rtrim($q);

                /***
                 * New : CMS - Auth
                 */

                if( (bool)xc_conf(XC_CONF_LOG_QUERIES))
                {
                    //$qjos= str_replace('#__','jos_',$q);
                    //error_log(" table prefix iterator : " . $dbOptions->tablePrefix . '  : new query :|'.$q.'|'. ' joomla query : \n ' . $qjos,0);
                }
                //$qjos= str_replace('#__','j25_',$q);
                //error_log(" table prefix iterator : " . $dbOptions->tablePrefix . '  : new query :|'.'|'. ' joomla query : \n ' . $qjos,0);
            }else{
                //$this->log(" dbOptions wrong");
            }
            $allQueryResults[''.$query->id]=$this->dbgate->run($q);

            //$this->log(" table prefix iterator : " . $dbOptions->tablePrefix . '  : new query :|'.$q.'|'. ' joomla query : \n ');
            //$this->log(" run query : \n" . $q  . '\n');
            //SELECT i.id refId, i.introtext AS introText, i.fulltext AS fText, DATE_FORMAT(i.modified,'%Y-%m-%d-%H-%i') AS dateString, DATE_FORMAT(i.created,'%Y-%m-%d-%H-%i') AS createdString, DATE_FORMAT(i.publish_up,'%Y-%m-%d-%H-%i') AS pubStart, DATE_FORMAT(i.publish_down,'%Y-%m-%d-%H-%i') AS pubEnd, i.title title, i.catid groupId, i.published published, #__users.username ownerRefStr FROM #__k2_items as i LEFT JOIN #__users ON #__users.id = i.modified_by WHERE i.published = 1 AND  i.access IN(1,1) AND i.trash = 0  AND i.id IN (SELECT itemID FROM #__k2_tags_xref WHERE tagID=2) ORDER BY i.created DESC, i.modified DESC

            if($allQueryResults[''.$query->id]==null)
            {
                $qErr = ''. $q;
                if( (bool)xc_conf(XC_CONF_JOOMLA))
                {
                    $qErr= str_replace('#__','jos_',$q);
                }
                $mysqlErr = mysql_error();
                //$this->log('queries results are zero : ' . $qErr . ' || mysql error: ' . $mysqlErr );
                //$qjos= str_replace('#__','jos_',$q);
                //$this->log('full query : ' . $qjos );
                //$this->log(" table prefix iterator : " . $dbOptions->tablePrefix . '  : new query :|'.$q.'|'. ' joomla query : \n ');
                return $mysqlErr;
            }
        }
        return $allQueryResults;
    }

    /***
     * Prepares the schema processor. Attention ! It does execute also the SQL queries !
     * @param $queries
     * @param $schemas
     * @param $_dbOptions
     */
    private function _prepareSchemaProcessor($queries,$schemas,$_dbOptions){

        $result=new stdClass();






        $result->schemas = $schemas;
        /***
         * convert dbOptions if needed
         */
        $dbOptions = null;
        try{
            if(!is_string($_dbOptions) && get_object_vars($_dbOptions)==null)
            {
                $dbOptions = json_decode($_dbOptions);
            }else{
                $dbOptions = $_dbOptions;
            }
        }catch (Exception $e)
        {

        }
        $result->options = $dbOptions;

        /*
         * now run the mysql queries
        */
        $result->queryResults= $this->_excecuteQueries($queries,$dbOptions);
        if(is_string($result->queryResults))
        {
            $this->log('#### have database errors : ' . $result->queryResults);
            //$this->log('#### have database errors ');
            return new RpcError($result->queryResults,-1);
        }
        /***
         *
         */
        return $result;
    }


    /** @JsonRpcMethod*/
    public function templatedQuery($queries,$schemas,$_dbOptions)
    {

        xapp_hide_errors();
        include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data

        xapp_print_memory_stats('xapp-connect-templatedQuery::start');


        /***
         *Some caching here
         */


        if($this->cache==null){
            $this->cache=CacheFactory::createDefaultCache();
        }

        if($this->responseCache==null){
            $this->responseCache = Xapp_Cache::instance("sql2json","file",array(
                Xapp_Cache_Driver_File::PATH=>XAPP_BASEDIR . 'cache/',
                Xapp_Cache_Driver_File::CACHE_EXTENSION=>'sql2json',
                Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>2000
            ));

        }

        $preventCache = false;
        $dbOptions = null;
        try{
            if(!is_string($_dbOptions) && get_object_vars($_dbOptions)==null)
            {
                $dbOptions = json_decode($_dbOptions);
            }else{
                $dbOptions = $_dbOptions;
            }
        }catch (Exception $e)
        {


        }


        if($dbOptions!=null)
        {
            if(isset($dbOptions->vars)){
                if(isset($dbOptions->vars->preventCache))
                {
                    if($dbOptions->vars->preventCache){
                        $preventCache=$dbOptions->vars->preventCache;
                    }
                }
            }
        }

        if( (bool)xc_conf(XC_CONF_LOG_REQUEST_OPTIONS))
        {

        }

        $queriesStr = null;
        $schemasStr = json_encode($schemas);

        if((bool)xc_conf(XC_CONF_RESPONSE_CACHE))
        {
            if($queries!=null && $schemas!=null)
            {
                $queriesStr = json_encode($queries);
                $schemasStr = json_encode($schemas);
                if($queriesStr!=null && $schemasStr){
                    $cacheKey = md5( $queriesStr . $schemasStr).'.qc';
                    //$cached = $this->cache->get_cache($cacheKey);
                    $cached = $this->responseCache->get($cacheKey);
                    if($cached!=null)
                    {
                        if(!$preventCache){
                            $this->log('return from cache');
                            return $cached;
                        }else{
                            $this->log('drop cache');
                            $this->responseCache->forget($cacheKey);
                        }
                    }else{

                    }
                }
            }
        }

        /***
         * Database init
         */
        if(!(bool)xc_conf(XC_CONF_JOOMLA))
        {
            $dbName = 'ibiza';
            if($dbOptions!=null && isset($dbOptions->database))
            {
                $dbName = $dbOptions->database;
            }
            $dbgate2 = new db("mysql:host=localhost;dbname=".$dbName,"root","asdasd");
            $this->setDBGateWay($dbgate2);
        }else{

            $dbo = JFactory::getDbo();
            $dbgate2 = new JoomlaDB();
            $dbgate2->setDBO($dbo);
            $this->setDBGateWay($dbgate2);

        }


        if($this->dbgate==null){
            $this->log('have no data base gate');
            return 'have no db gate';
        }


        /**
         * run the sql queries and sanitize variables
         */

        //xapp_dumpObject($schemas,'##    schemas');
        //xapp_dumpObject($dbOptions,'##    dbOptions');

        $schemaProcessorArgs = $this->_prepareSchemaProcessor($queries,$schemas,$dbOptions);
        $schemaProcessor = new SchemaProcessor();
        /***
         * final resolving
         */
        $resultStr = $schemaProcessor->processArgs($schemaProcessorArgs->schemas,$schemaProcessorArgs->queryResults,$schemaProcessorArgs->options);

        if( (bool)xc_conf(XC_CONF_RESPONSE_CACHE))
        {
            //store response in cache
            if($queriesStr!=null && $schemasStr!=null){
                $this->cache->set_cache(md5( $queriesStr . $schemasStr).'.qc',$resultStr);
                $this->responseCache->set(md5( $queriesStr . $schemasStr).'.qc',$resultStr);
            }
        }
        xapp_print_memory_stats('xapp-connect-templatedQuery::end');
        return $resultStr;
    }

}
