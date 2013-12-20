<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Filter
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */


/***
 * Standard basic schema for content.
 * @link http://www.pearls-media.com/api/1.8/xappconnect/types/CContent
 */
/*

 The default schema for content :

 [
  {
    "id": "root",
    "schema": "{\"class\":\"pmedia.types.CContent\",\"order\":-1,%query_post::schema_post::escapeArray%}",
    "isRoot": true
  },
  {
    "isRoot": false,
    "id": "schema_post",
    "schema": {
      "title": "%title%",
      "sourceType": "ebPostDetail",
      "baseRef": "BASEREF",
      "dataSource": "DSUID",
      "introText": "%{function=\"htmlMobile($introText)\"}%",
      "refId": "%refId%",
      "customFields": [
        {
          "sourceType": 46,
          "title": "Show on Map",
          "value": "%latitude%|%longitude%"
        }
      ],
      "pictureItems": "%{function=\"toPictureItems($introText)\"}%"
    }
  }
]
 */



/***
 * Standard basic schema for content
 * @link http://www.pearls-media.com/api/1.8/xappconnect/types/CListItem
 */
define("XAPP_TPL_CCONTENT_ITEM_STANDARD_BASIC",  serialize(array (
    'isRoot' => false,
    'id' => 'schema_detail',
    'schema' => array (
        XC_REF_ID => '%refId%',
        XC_TITLE => '%title%',
        XC_PUBLISHED => '%published%',
        XC_GROUP_ID => '%groupId%',
        XC_GROUP_ID_STR => '%groupIdStr%',
        XC_SOURCE_TYPE => '%sourceType%',
        XC_DATA_SOURCE => 'DSUID',
        XC_OWNER_REF => '%ownerRef%',
        XC_OWNER_REF_STR => '%ownerRefStr%',
        XC_BASE_REF => 'BASEREF',
        XC_INTRO => '%description%',
        XC_DATE_STRING => '%dateString%',
        XC_PICTURE_ITEMS => '%pictureItems%',
        XC_INSERTIONS=> '%insertions%'

    )
)));
/***
 * The content header/root schema
 */
define("XAPP_TPL_CCONTENT_STANDARD_BASIC", serialize(array (
        0 => array (
            'isRoot' => true,
            'id' => 'root',
            'schema'=> '{"class":"pmedia.types.CContent","order":-1,%query_post::schema_detail::escapeArray%}',
        ),
        1 =>  unserialize(XAPP_TPL_CCONTENT_ITEM_STANDARD_BASIC))
));


/***
 * Standard basic schema for list items
 * @link http://www.pearls-media.com/api/1.8/xappconnect/types/CListItem
 */
define("XAPP_TPL_CLIST_ITEM_STANDARD_BASIC",  serialize(array (
    'isRoot' => false,
    'id' => 'schema_items',
    'schema' => array (
        XC_REF_ID => '%refId%',
        XC_TITLE => '%title%',
        XC_PUBLISHED => '%published%',
        XC_GROUP_ID => '%groupId%',
        XC_GROUP_ID_STR => '%groupIdStr%',
        XC_SOURCE_TYPE => '%sourceType%',
        XC_DATA_SOURCE => 'DSUID',
        XC_OWNER_REF => '%ownerRef%',
        XC_OWNER_REF_STR => '%ownerRefStr%',
        XC_BASE_REF => 'BASEREF',
        XC_INTRO => '%description%',
        XC_ICON_URL => '%iconUrl%',
        XC_DATE_STRING => '%dateString%',
        XC_RATING=> '%rating%',
        XC_RATINGS=> '%ratings%',
    )
)));
/***
 * Standard basic schema for a list.
 * @link http://www.pearls-media.com/api/1.8/xappconnect/types/CList
 */
define("XAPP_TPL_CLIST_STANDARD_BASIC", serialize(array (
    0 => array (
        'isRoot' => true,
        'id' => 'root',
        'schema' => '{"title":"_LIST_TITLE_", "class":"pmedia.types.CList","order":"0","items":%query_items::schema_items%,"shareUrl":"BASEREF/index.php?option=com_k2&view=itemlist&task=category&id=REFID"}',
    ),
    1 =>  unserialize(XAPP_TPL_CLIST_ITEM_STANDARD_BASIC))
));
/**
 * XApp-Connect-Schema-Factory
 *
 * This class provides standard schemas, being used for custom types without schemas in RPC classes.
 *
 * @package XApp-Connect
 * @class Xapp_Connect_Filter
 * @error @TODO
 * @author  mc007
 */

class Xapp_Connect_Schema_Factory
{
    const SCHEMA_FORMAT_ARRAY='ARRAY';
    const SCHEMA_FORMAT_JSON_STRING='STRING';

    /***
     * @param string $format
     * @return mixed|string
     */
    public static function stdCList($format='STRING'){
        $res = XAPP_TPL_CLIST_STANDARD_BASIC;

        switch($format){
            case Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_ARRAY:
                return unserialize($res);
            case Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING:{
                return json_encode(unserialize($res));
            }
        }
        return $res;
    }

    /***
     * Standard schema for content
     * @param string $format
     * @return mixed|string
     */
    public static function stdCContent($format='STRING'){
        $res = XAPP_TPL_CCONTENT_STANDARD_BASIC;

        switch($format){
            case Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_ARRAY:
                return unserialize($res);
            case Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING:{
                return json_encode(unserialize($res));
            }
        }
        return $res;
    }
    /**
     * Replace identifiers in a schema
     * @param $str
     * @param array $identifiers
     * @return mixed|string
     */
    public static function replaceIdentifiers($str,$identifiers=array()){
        $result =  '' . $str;
        if($identifiers){
            $_keys = array();
            $_values = array();
            foreach ($identifiers as $key => $value)
            {
                array_push($_keys,'%' . $key . '%');
                array_push($_values,$value);
            }
            $result = str_replace(
                $_keys,
                $_values,
                $result
            );
        }
        return $result;
    }
}