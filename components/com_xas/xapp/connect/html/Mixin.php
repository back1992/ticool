<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\HTML
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * HTML-Mixin
 *
 * @package XApp-Connect\HTML
 * @class Xapp_Connect_HTML_Mixin
 * @error 109
 * @author mc007 <support@xapp-studio.com>
 */
class Xapp_Connect_HTML_Mixin
{
    /***
     * Placement operation constants
     * @link http://dojotoolkit.org/reference-guide/1.7/dojo/place.html
     */
    const MIXIN_PLACEMENT_AFTER = 'after';
    const MIXIN_PLACEMENT_BEFORE = 'before';
    const MIXIN_PLACEMENT_REPLACE = 'replace';
    const MIXIN_PLACEMENT_FIRST = 'first';
    const MIXIN_PLACEMENT_LAST = 'last';
    const MIXIN_PLACEMENT_ONLY = 'only';

    /***
     * Node query constants. Refers to standard xapp-mobile CSS classes
     */
    const MIXIN_QUERY_TITLE = '.itemTitle';
    const MIXIN_QUERY_TEXT = '.TextWrapper';
    const MIXIN_QUERY_DETAIL_TITLE_AFTER = '.TitleBackground';
    const MIXIN_QUERY_DETAIL = '.itemDetail';

    const CSS_ITEM_FOOTER_LEFT = 'itemFooterTextLeft';



    /***
     * Factory to create one insert data struct. The data is consumed by the mobile application.
     * There can be multiple insertions
     *
     * @param $nodeQuery
     * @param $placement
     * @param string $html
     * @param string $dateRef
     * @param string $widgetClass
     * @param array $widgetMixing
     * @return array
     *
     * @link http://dojotoolkit.org/reference-guide/1.9/dojo/query.html
     */
    public static function createMixin($nodeQuery,$placement,$html='',$dateRef='',$widgetClass='',$widgetMixing=array()){

        $insert = array();

        $insert['insertNodeQuery'] = $nodeQuery;
        $insert['insertPlacement'] = $placement;
        $insert['insert'] = $html;
        $insert['insertDataRef'] = $dateRef;
        $insert['insertClass'] = $widgetClass;

        if(is_string($widgetMixing)){
            $insert['insertMixin'] = json_encode($widgetMixing);
        }else{
            $insert['insertMixin'] = $widgetMixing;
        }
        return $insert;

    }

    public static function addMixin($dstItem,$nodeQuery,$placement,$html='',$dateRef='',$widgetClass='',$widgetMixing=array()){

        //prepare insertions
        $inserts = $dstItem->{XC_INSERTIONS};
        if($inserts==null){
            $inserts = array();
        }else{
            if(is_string($inserts)){
                $inserts = json_decode($inserts);
            }
        }

        $insert = self::createMixin($nodeQuery,$placement,$html,$dateRef,$widgetClass,$widgetMixing);
        if($insert){
            array_push($inserts,$insert);
        }
        $dstItem->{XC_INSERTIONS}=json_encode($inserts);
        return $dstItem;
    }
}