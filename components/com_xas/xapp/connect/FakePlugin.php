<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html

 */

/**
 * @Summary Base class to apply "XApp Connect" plugin like functionality to older versions of "XApp Connect" types
 *          which only consist out of a SQL query and a schema. The class is needed to inherit primarily indexing
 *          and search functions.
 *
 *
 *
 * @package XApp-Connect
 * @class XApp_FakePlugin
 * @error @TODO
 * @author  mc007
 */
class XApp_FakePlugin extends Xapp_Joomla_Plugin
{
    /**
     * option to specify a cache config
     *
     * @const DEFAULT_NS
     */
    public $CACHE_NS = 'GENERAL_CACHE_NS';

    private function ignore($type){


    }
    public function onBeforeSearch(){
        parent::onBeforeSearch();
        /*
        if($this->indexOptions==null){
            $this->indexOptions = $this->getStandardIndexOptions();
        }
        */

    }

    public function getStandardIndexOptions(){

        $result = array();

        $result[0]=array("key"=>'refId',
                                  "type"=>'store');

        $result[1]=array("key"=>'title',
            "type"=>'index',
            "highlight"=>true,
            "useForSearch"=>1
        );

        $result[2]=array("key"=>'iconUrl',
            "type"=>'store');

        $result[3]=array("key"=>'sourceType',
            "type"=>'store');

        $result[4]=array("key"=>'dataSource',
            "type"=>'store');

        $result[4]=array("key"=>'dsUrl',
            "type"=>'store');


        return $result;

    }
    public function getIndexOptions($xcType){
        $res = parent::getIndexOptions($xcType);
        if($res==null){
            $res=$this->getStandardIndexOptions();
        }
        return $res;
    }
    public function onBeforeCall($_options=null){

        parent::onBeforeCall($_options);
        $this->updateNS();
        /*
        if($this->getIndexOptions($this->xcType)==null){
            $this->indexOptions = $this->getStandardIndexOptions();
        }
        */

        //$this->dumpObject($this->indexOptions,'indexOptions');
        //error_log("on before call : " . $_options);
        //$this->parseOptions($_options);
    }

    public function onAfterCall($result){
        if($this->xcType !=null && $this->getIndexOptions($this->xcType)!=null){
            parent::onAfterCall($result);
        }
    }

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    private function init()
    {

    }

    public function updateNS()
    {
        $ns = $this->getXCOption(XAPP_CONNECT_VAR_CTYPE);
        if($ns!=null && strlen($ns)>2){
            $this->CACHE_NS=substr($ns,0,2);
        }
        /*
        if($this->xcType){
            $xcTypeStore  = Xapp_Util_Json_Store::create($this->xcType);
            if($xcTypeStore){
                $xcTypeParent = $xcTypeStore->get(array("inputs", array("name=parent")));
                $this->dumpObject($xcTypeParent,'parent');
            }
            error_log('new ns : ' . $ns);
        }
        */

    }

    public function searchTest($query){
        $this->search($query);
    }
    /***
     *
     * @return integer
     */
    function load()
    {
        //parent::load();
        xapp_hide_errors();


        return true;
    }

    public function indexDocumentEx($data,$xcType,$ns='xc'){

    }
    /**
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message, $ns = "", $stdError = true)
    {
        parent::log($message, "VMart", $stdError);
    }

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    function setup()
    {
        parent::setup();
    }
}