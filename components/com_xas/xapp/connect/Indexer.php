<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * XApp-Connect-Plugin base class.
 * @package XApp-Connect
 * @class Xapp_Connect_Plugin
 * @error @TODO
 * @author  mc007
 */
class Xapp_Connect_Indexer
{

    protected $indexer=null;

    public  $indexParameters=null;

    protected $autoCreateIndex=true;
    protected $autoReIndex=true;
    protected $documentData=null;
    protected $indexAtShutdown=true;


    public function onBeforeSearch(){
        /***
         * Now we're fine to index
         */
        require_once("Zend/Search/Lucene.php");
        //setup the indexer
        $this->_init($this->CACHE_NS);
    }

    private function getDocField($doc,$field,$default=''){
        try{
            if($doc->getField($field)!=null){
                return $doc->getFieldValue($field);
            }else{
                return $default;
            }
        }catch (Exception $e){
            return $default;
        }
    }

    public function toResults($hits,$lQuery,$queryStr,$indexParameters,$groupName=''){


        $result = array();
        $val=null;
        foreach ($hits as $hit) {

            $document = $hit->getDocument();
            if(!$document){
                continue;
            }

            $cnt=0;

            foreach ($indexParameters as $param) {

                $val = $this->getDocField($document, xapp_array_get($param,'key','noParam'));

                $highlight = xapp_array_get($param,'highlight',false);

                if($highlight){

                    //$this->log('high s0. ' . strtolower(''.$val));
                    if (stripos(strtolower(''.$val),$queryStr)){

                        $tmp = $lQuery->htmlFragmentHighlightMatches($queryStr);
                        if($tmp){
                            //$input = str_replace($queryRaw,$tmp,$input);
                            $input = ''.$val;
                            $val = str_ireplace($queryStr,$tmp,$input);
                            //$this->log('final : ' . $tmp . ' t: ' . $queryStr);
                            //$this->log('fin' . $val);

                            /*$this->log('tmp : ' . $tmp);
                            $this->log('val : ' . $val);
                            $this->log('input : ' . $input);
                            */
                        }
                        //$this->dumpObject($tmp,'highlighted');
                    }
                }
                $result[$cnt][xapp_array_get($param,'key','noParam')]=$val;
                //$this->log('final : ' . $val);
            }
            $result[$cnt][XC_OWNER_REF_STR]=$groupName;
            $cnt++;

        }

        //$this->dumpObject($result,'lres');

        return $result;
    }

    public function toQuery($query,$indexParameters){

        if($indexParameters==null || !is_array($indexParameters)){
            return null;
        }
        //$this->dumpObject($indexParameters,'$indexParameters2ex');
        //assert($indexParameters);
        $paramStore  = Xapp_Util_Json_Store::create($indexParameters);

        if(!class_exists('Zend_Search_Lucene_Document')){
            require_once("Zend/Search/Lucene.php");
        }

        $searchableTerms = null;

        $lQuery = new Zend_Search_Lucene_Search_Query_Boolean();


        $searchableTerms = $paramStore->get(array(".", array("useForSearch=1")));
        if($searchableTerms==null /*|| !is_array($searchableTerms)*/){
            $this->log('$searchableTerms:false');
            return null;
        }

        foreach ($searchableTerms as $term) {

            $required = xapp_property_get($term,'required',false);
            $queryStr = xapp_property_get($term,'key','') . ':' . $query;

            $lQueryIn = Zend_Search_Lucene_Search_QueryParser::parse($queryStr);

            $lQuery->addSubquery($lQueryIn, $required);


        }
        return $lQuery;
    }
    public function searchEx($query,$indexParameters,$groupName=''){

        xapp_hide_errors();

        if(!class_exists('Zend_Search_Lucene_Document')){
            if(file_exists('Zend_Search_Lucene_Document')){
                require_once("Zend/Search/Lucene.php");
            }else{
                return null;
            }
        }
        //$this->log('searching ex... ' . $query);
        if($indexParameters==null){
            $this->log('searching ex... no index par' . $query);
            return null;
        }

        //$this->dumpObject($indexParameters,'$indexParameters2ex');

        $lQuery = $this->toQuery($query,$indexParameters);
        if($lQuery==null){
           // $this->log('have no lqery : ' . $this->CACHE_NS);
            //$this->dumpObject($indexParameters,'    \t  no lqery');
            return null;
        }

        $index = $this->getIndexer();
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(2);
        if($index==null){
            return null;
        }

        $_hits = $index->find($lQuery);
        if(count($_hits)){
            $queryRaw = str_replace('*','',$query);
            return $this->toResults($_hits,$lQuery,$queryRaw,$indexParameters,$groupName);
        }

        if($index==null){
            $this->log('have no indexer to search');
        }else{

        }
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(2);
        //the indexer
        if($index){
            $query = trim($query);
            if (strlen($query) > 0) {

                $hits = $index->find('title:'.$query);
                $userQuery = Zend_Search_Lucene_Search_QueryParser::parse('title:'.$query, 'utf-8');
                $queryRaw = str_replace('*','',$query);
                //$numHits = count($hits);
                //$this->dumpObject($hits);
                //$this->log('num hits : ' .$numHits);
                $res = array();
                $cnt = 0;
                if(count($hits)>0){
                    foreach ($hits as $hit) {

                        $document = $hit->getDocument();
                        if($document!=null){
                            $res[$cnt][XC_TITLE]=$this->getDocField($document, XC_TITLE);
                            /*
                            if($res[$cnt]['title']!=null){

                                //$this->log('qr' . $queryRaw);
                                //$this->log('qrt' . $hit->title);
                                $term = strtolower(''.$hit->title);
                                $this->log('start highlight . ' . $term . ' for ' . $queryRaw . ' r ' . strpos('shovel','shov'));
                                if (stripos($term,$queryRaw)){

                                    //$tmp = $userQuery->htmlFragmentHighlightMatches("<b>shovel</b>");
                                    $tmp = $userQuery->htmlFragmentHighlightMatches($queryRaw);
                                    if($tmp){
                                        $input = strtolower(''.$res[$cnt]['title']);
                                        $input = str_replace($queryRaw,$tmp,$input);
                                        $res[$cnt]['title']=$input;
                                        $this->log('final : ' . $input);
                                    }
                                    $this->dumpObject($tmp,'highlighted');
                                }else {
                                       //$result[$hit->id]['excerpt'] = $hit->excerpt
                                    $this->log('nothing ' . $hit->title);
                                }
                            }
                            */
                            $res[$cnt][XC_REF_ID]=$this->getDocField($document, XC_REF_ID);
                            $res[$cnt][XC_SOURCE_TYPE]=$this->getDocField($document, XC_DATA_SOURCE);
                            $res[$cnt][XC_ICON_URL]=$this->getDocField($document, XC_ICON_URL);
                            $res[$cnt][XC_DSURL]=$this->getDocField($document, XC_DSURL);
                            $res[$cnt][XC_SOURCE_TYPE]=$this->getDocField($document, XC_SOURCE_TYPE);
                        }
                        $cnt++;
                    }
                    return $res;
                }

                if(count($hits)){
                    //return $hits;
                }
            }
        }else{
            $this->log('have no indexer');
            return null;
        }

        return null;

    }
    public function search($query){

        if(!class_exists('Zend_Search_Lucene_Document')){
            if(file_exists('Zend_Search_Lucene_Document')){
                require_once("Zend/Search/Lucene.php");
            }else{
                return null;
            }
        }
        if(!class_exists('Zend_Search_Lucene_Document')){
            return null;
        }
        if($this->indexParameters!=null){
            return $this->searchEx($query,$this->indexParameters);
        }else{
            if(method_exists($this,'getStandardIndexOptions')){
                $options = $this->getStandardIndexOptions();
            }
        }

        try{
            $index = $this->getIndexer();
            if($index==null){
            $this->log('have no indexer to search');
            }else{

        }
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(2);
        //the indexer
        if($index){
            $query = trim($query);
            if (strlen($query) > 0) {

                $hits = $index->find('title:'.$query);
                $userQuery = Zend_Search_Lucene_Search_QueryParser::parse('title:'.$query, 'utf-8');
                $queryRaw = str_replace('*','',$query);
                //$numHits = count($hits);
                //$this->dumpObject($hits);
                //$this->log('num hits : ' .$numHits);
                $res = array();
                $cnt = 0;
                if(count($hits)>0){
                    foreach ($hits as $hit) {

                        $document = $hit->getDocument();
                        if($document!=null){
                            $res[$cnt]['title']=$this->getDocField($document, 'title');

                            $res[$cnt]['refId']=$this->getDocField($document, 'refId');
                            $res[$cnt]['dataSource']=$this->getDocField($document, 'dataSource');
                            $res[$cnt]['iconUrl']=$this->getDocField($document, 'iconUrl');
                            $res[$cnt]['dsUrl']=$this->getDocField($document, 'dsUrl');
                            $res[$cnt]['sourceType']=$this->getDocField($document, 'sourceType');
                        }
                        $cnt++;
                    }
                    return $res;
                }
            }
        }else{
            $this->log('have no indexer');
            return null;
        }
        }catch (Exception $e){
            return null;
        }
        return null;
    }



    /***
     * Returns true if the bound XApp-Connect-Type is options for indexing documents
     * @return bool
     */
    private function isIndexable(){

        if($this->xcType==null){
            return false;
        }
        $indexParameters = $this->getIndexOptions($this->xcType);
        if($indexParameters==null || count($indexParameters) <1 ){
            return false;
        }
        return true;
    }

    /***
     * @return Zend_Search_Lucene|null
     */
    public function getIndexer(){

        if($this->indexer){
            return $this->indexer->index;
        }

        if($this->isIndexable()){

            if(!class_exists('Zend_Search_Lucene_Document')){
                if(file_exists('Zend_Search_Lucene_Document')){
                    require_once("Zend/Search/Lucene.php");
                }else{
                    return null;
                }
            }

            //setup the indexer
            $this->_init($this->CACHE_NS);

            return $this->indexer->index;
        }
        return null;
    }

    /***
     * @param $xcType
     * @return mixed|null
     */
    public function getIndexOptions($xcType){

        if($xcType==null){
            $this->log('$xcType=null');
            return null;
        }

        if($this->indexParameters!=null){
            return $this->indexParameters;
        }

        $result = null;
        $xcTypeStore  = Xapp_Util_Json_Store::create($xcType);
        if($xcTypeStore){
            $xcTypeUserData = $xcTypeStore->get(array("inputs", array("name=userData")));
            if($xcTypeUserData && count($xcTypeUserData)){
                $indexParams = $xcTypeUserData[0]->value;
                if($indexParams){
                    if(is_string($indexParams)){
                        $result=json_decode($indexParams,true);
                        if(xapp_array_get($result,'index')==null){
                            //$this->log('no index params');
                            return null;
                        }else{
                            return $result['index'];
                        }
                    }elseif(is_array($indexParams) || is_object($indexParams)){
                        $result=$indexParams;
                    }
                }else{
                   // $this->log('Indexer : have no indexParameter in xapp-connect type : ' . get_class($this));
                }
            }else{
                //$this->log('xapp-connect type has no user data');
            }
        }else{
            //$this->log('have no xapp-connect type store');
        }
        return $result;
    }

    public function _init($ns){

        //create an instance to the Lucene Wrapper if needed
        if($this->indexer==null){
            $this->indexer =new LuceneIndexer();
            $this->indexer->indexDirectory=$this->serviceConfig[XC_CONF_CACHE_PATH];
            $this->indexer->loadIndex($ns,$this->autoCreateIndex);
        }
    }
    private function updateLuceneDocument($doc,$item,$mapping){

        //walk over mapping
        foreach($mapping as $mappingItem){

            if($item[$mappingItem['key']]==null){
                continue;
            }
            switch($mappingItem['type'])
            {
                case 'store':{
                    $doc->addField(Zend_Search_Lucene_Field::UnIndexed($mappingItem['key'], $item[$mappingItem['key']]));
                    break;
                }
                case 'index':{
                    $doc->addField(Zend_Search_Lucene_Field::Text($mappingItem['key'], $item[$mappingItem['key']]));
                    break;
                }
            }
        }
        return $doc;
    }

    private function createLuceneDocument($item,$mapping){

        if(class_exists('Zend_Search_Lucene_Document')){
            return null;
        }

        $doc = new Zend_Search_Lucene_Document();
        //$this->dumpObject($mapping,'mapping');
        //$this->dumpObject($item,'items');

        if($mapping==null){
            //$this->dumpObject($this->indexOptions,'indexOptions');
            return null;
        }


        //walk over mapping
        foreach($mapping as $mappingItem){

            if(xapp_array_get($item,xapp_array_get($mappingItem,'key'))==null){
                continue;
            }
            switch($mappingItem['type'])
            {
                case 'store':{
                    $doc->addField(Zend_Search_Lucene_Field::UnIndexed($mappingItem['key'], $item[$mappingItem['key']]));
                    break;
                }
                case 'index':{
                    $doc->addField(Zend_Search_Lucene_Field::Text($mappingItem['key'], $item[$mappingItem['key']]));
                    break;
                }
                /*
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('url', $docUrl));
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('created', $docCreated));
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('teaser', $docTeaser));
                $doc->addField(Zend_Search_Lucene_Field::Text('title', $docTitle));
                $doc->addField(Zend_Search_Lucene_Field::Text('author', $docAuthor));
                $doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $docBody));
                */
            }
        }
        $doc->addField(Zend_Search_Lucene_Field::keyword('dsUrl',$this->toDSURL($item)));
        return $doc;

    }

    private function getIndexedDocumentId($item){

        //the indexer must be ready
        //assert($this->indexer);

        // find the document based on the indexed document_id field
        $index = $this->getIndexer();
        if($index){

            //$this->log('try to find doc in index');
            $term = new Zend_Search_Lucene_Index_Term($this->toDSURL($item),'dsUrl');
            $docIds = $index->termDocs($term);

            //$this->dumpObject($docIds,' found documents');
            if($docIds && count($docIds)>0){
                return $docIds[0];
            }
        }
        return null;
    }

    private function removeItem($item){

        $query = new Zend_Search_Lucene_Search_Query_Term (new Zend_Search_Lucene_Index_Term( $this->toDSURL($item) , 'dsUrl'));
        $index=$this->getIndexer();
        $hits = $index->find($query);
        foreach ($hits as $hit) {
            //$this->log('deleting from index ' . $this->toDSUrl($item));
            $index->delete($hit->id); // $hit->id is not key , it's lucene unique index of the row that has key = $id
        }
        $index->commit();
    }

    /**
     * @param $data
     * @param $path
     * @param $xcType
     * @param string $ns
     */
    private function indexDocumentEx($data,$xcType,$ns='xc'){

        if($data==null){
            return false;
        }
        if($xcType==null){
            return false;
        }
        $this->log("indexing  document : " . $ns);
        //$this->dumpObject($data);
        //$this->dumpObject($xcType);


        $indexParameters = $this->getIndexOptions($xcType);
        //$this->dumpObject($indexParameters,'index parameter');


        if($indexParameters==null || count($indexParameters) <1 ){
            //$this->log("indexing  document failed for : " . $ns . ' have no index options');
            return false;
        }

        //$this->dumpObject($indexParameters,'index options');

        $this->documentData = json_decode($data,true);

        if($this->documentData==null){
            return false;
        }


        /***
         * Now we're fine to index
         */
        if(file_exists('Zend/Search/Lucene.php')){
            require_once("Zend/Search/Lucene.php");
        }else{
            return null;
        }

        //setup the indexer
        $this->_init($ns);

        $index = $this->getIndexer();

        /***
         * 1st. case : items
         */
        if($this->documentData !=null && xapp_array_get($this->documentData,'items')!=null){

            //$this->dumpObject($this->documentData['items'],'iterate items');

            $container = new Xapp_Util_Container($this->documentData['items']);

            foreach($container->toArray() as $item){

                $lDoc=null;
                $docIndexId = $this->getIndexedDocumentId($item);
                if($docIndexId!=null && $this->autoCreateIndex){
                    $lDoc = $index->getDocument($docIndexId);
                    if($lDoc){
                        $this->removeItem($item);
                    }
                }
                $lDoc = $this->createLuceneDocument($item,$indexParameters);
                if($lDoc!=null){
                    $index->addDocument($lDoc);
                    $index->commit();

                }
            }
            return true;
        }
        return false;
    }
    static public $_idata=null;
    static public $_itype=null;
    static public $_ins=null;

    /**
     * @param $data
     * @param $path
     * @param $xcType
     * @param string $ns
     */
    public function indexDocument($data,$xcType,$ns='xc'){

        @set_time_limit(0);
        @ini_set("memory_limit","256M");
        @ini_set("max_input_time","-1");

        //post pone
        if($this->indexAtShutdown){

            //self::$_idata=$data;
            //ignore_user_abort(true);
            //register_shutdown_function(array($this, 'indexDocumentEx'), $data, $xcType,$ns);

        }else{
            //return $this->indexDocumentEx($data,$xcType,$ns);
        }
    }
}