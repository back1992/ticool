<?php
/**
 * @version 0.1.0
 * @package HTML
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
require_once realpath(dirname(__FILE__)) . '/filter.php';
require_once realpath(dirname(__FILE__)) . '/kses.php';
require_once realpath(dirname(__FILE__)) . '/query.php';
require_once realpath(dirname(__FILE__)) . '/MediaItemBase.php';

class HTMLFilter
{

    public $delegate;

    public static $forceMultiplePictures=false;

    public static function reset(){
        HTMLFilter::$forceMultiplePictures=false;
    }

    public $reg;

    public static $vars;

    public $string;
    public $cleanedUp;
    public $doc;
    public $gHTML;

    public $currentHtml;

    public $imageDefaultWidth = "97%";
    public $imageDefaultClass = "PictureItems";
    public $linkDefaultClass="articleLink";

    public $galleryFunc = "ctx.getUrlHandler().openUrl(tt://pictureGallery/";
    public $pictureFunc = "ctx.getUrlHandler().openUrl(tt://pictureView/";

    public function resolveVar($var)
    {
        if (isset(HTMLFilter::$vars) && HTMLFilter::$vars != null) {
            foreach (HTMLFilter::$vars as $k) {
                if ($k->key === $var) {
                    return $k->value;
                }
            }
        }
        return '';
    }

    public function  toDSUrl(
        $uuid,
        $applicationId,
        $dsUID,
        $cType,
        $refId,
        $language,
        $internalIndex)
    {

        $result = $uuid . "/" . $applicationId . "/" . $dsUID . "/" . $cType . "/" . $refId;
        if ($internalIndex != -1) {
            $result = $result . "/" . $internalIndex;
        }
        return $result;
    }

    public function  toDSUrlEx(
        $url,
        $uuid,
        $applicationId,
        $dsUID,
        $baseRef)
    {

        //$result = $uuid . "/" . $applicationId . "/" . $dsUID . "/" . $cType . "/" . $refId;

        return '';
    }

    public function isExternalLink($url, $appId, $baseRef)
    {
        if (strstr($url, "http")||strstr($url, "https")) {
            if (strstr($url, $baseRef)) {
                return false;
            } else {
                return true;
            }
        } else {

        }
        return false;
    }

    public function links()
    {
        $baseref = $this->resolveVar('BASEREF');
        if ($this->doc == null) {
            return;
        }

        $pIndex = 0;
        foreach (pq('a') as $link) {
            $url = pq($link)->attr('href');


            $deleteA = false;
            $linkInnerHTML = pq($link)->html();

            if ($this->isExternalLink($url, $this->resolveVar('UUID'), $this->resolveVar('APPID'), $this->resolveVar('BASEREF')))
            {
                $onClickUrl = "ctx.getUrlHandler().openExternalLocation(" . $url . ",null)";
                $newNode = pq($link)->before("<span onClick=" .$onClickUrl. "  class=\"" . $this->linkDefaultClass . "\">" .$linkInnerHTML. "</span>");
                pq($newNode)->addClass($this->linkDefaultClass);
                $deleteA = true;
            }else {

            }
            $onClickUrl = '';
            if($deleteA)
            {
               pq($link)->remove();
            }
        }
    }




    /***
     * images
     */
    public function images()
    {
        $baseref = $this->resolveVar('BASEREF');
        if ($this->doc == null) {
            return;
        }


        $multiplePictures = count(pq('img')) > 1;

        //for gallery mode instead single picture display. This could happen when an item
        //has pictures outside of the html markup
        if(!$multiplePictures && HTMLFilter::$forceMultiplePictures){
            $multiplePictures=true;
        }

        $pictureIndex = 0;


        /*
        if($this->gHTML){
            $ghtml = $this->gHTML;
            foreach($ghtml('img') as $index => $element) {
                //xapp_dumpObject($element,' img');
                //error_log($element->getAttribute('src'));



            }
        }
        */


        //http://192.168.1.37/joomla251/components/com_xas/xapp/index.php?service=VMart.customTypeQuery&params={%22DSUID%22:%2249592067-8d1d-41ab-aa09-5fd4b3234b1b%22,%22BASEREF%22:%22http://www.pearls-media.com/joomla25/%22,%22REFID%22:%222%22,%22CTYPE%22:%22VMartCategory%22,%22APPID%22:%22myeventsapp108%22,%22RT_CONFIG%22:%22release%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://www.xapp-studio.com/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://www.xapp-studio.com/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22vmCategory%22,%22SCREEN_WIDTH%22:320}&callback=dummy
        //http://192.168.1.37/joomla251/index.php?option=com_xas&view=rpc&service=XAPP_JSONP_Plugin.customTypeQuery&params={%22DSUID%22:%22d03967ff-fa96-49f7-808c-68aa7015e3d6%22,%22BASEREF%22:%22http://192.168.1.37/joomla251/%22,%22REFID%22:%223%22,%22CTYPE%22:%22JArticleDetail%22,%22APPID%22:%22myeventsapp108%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22jArticleDetail%22,%22SCREEN_WIDTH%22:320}&callback=asd


        //xapp_print_memory_stats('xapp-html-filter-images:start');
        foreach (pq('img') as $img) {
            $src = pq($img)->attr('src');


            pq($img)->attr('oriWidth', pq($img)->attr('width'));
            pq($img)->attr('width', $this->imageDefaultWidth);


            $addGalleryLink=true;
            $parent = pq($img)->parent();
            if(pq($parent)->attr('href')){
                $addGalleryLink=false;
            }


            pq($img)->addClass($this->imageDefaultClass);
            $imgUrl = $src;
            //adjust img src url
            if ($baseref != null && !startsWith($src, 'http')) {
                $imgUrl = $baseref . '/' . $src;

            }
            pq($img)->attr('src', $imgUrl);
            /***
             * add gallery function
             */
            if($addGalleryLink){

                if (!$multiplePictures) {
                    $onClickUrl = $this->pictureFunc . $imgUrl . ",null)";
                } else {
                    $onClickUrl = $this->galleryFunc . $this->toDSUrl($this->resolveVar('UUID'), $this->resolveVar('APPID'), $this->resolveVar('DSUID'), $this->resolveVar('SOURCE_TYPE'), $this->resolveVar('refId'), null, $pictureIndex) . ",null)";
                }
                pq($img)->attr('onClick', $onClickUrl);
            }
            $onErrorUrl = 'destroyMe(this)';
            pq($img)->attr('onError', $onErrorUrl);
            $pictureIndex++;
        }
        //xapp_print_memory_stats('xapp-html-filter-images:end');
    }


    public function getPictureItems()
    {
        $baseref = $this->resolveVar('BASEREF');


        if ($this->doc == null) {
            return array();
        }

        $items = array();
        $pIndex = 0;
        foreach (pq('img') as $img) {

            $src = pq($img)->attr('src');

            $imgUrl = $src;

            //adjust img src url
            if ($baseref != null && !startsWith($src, 'http')) {
                $imgUrl = $baseref . '/' . $src;
            }


            $item = new MediaItemBase();
            $item->fullSizeLocation = $imgUrl;
            array_push($items, $item);

            $pIndex++;
        }

        return $items;
    }

    public function mobileBase($str)
    {
        $res = '' . xapp_sanitize_html($str);
        $res = str_replace('&nbsp;', ' ', $res);
        $res = str_replace('&amp;', ' &', $res);
        return '' . $res;
    }

    public function pictureItems()
    {
        $this->cleanedUp = $this->mobileBase($this->string);
        $this->doc = phpQuery::newDocumentHTML($this->cleanedUp);
        phpQuery::selectDocument($this->doc);
        $items = $this->getPictureItems();
        return $items;
    }

    public function htmlMobile()
    {
        //xapp_print_memory_stats('xapp-html-filter-mobile:start');
        $this->cleanedUp = $this->mobileBase($this->string);
        //error_log('after mobile base' . $this->string);
        $this->doc = phpQuery::newDocumentHTML($this->cleanedUp);

        //$this->gHTML = ganon_str_get_dom($this->cleanedUp);

        phpQuery::selectDocument($this->doc);
        $this->images();
        $this->links();
        $result = '' . phpQuery::getDocument()->html();
        $result = '' . addslashes($result);

        //xapp_print_memory_stats('xapp-html-filter-mobile:end');
        /***
         * Big Article :

         * xapp-html-filter-mobile:start :: memory : 13.59 :: diff : 0.009087085723877, referer: http://mc007ibi.dyndns.org:8080/XApp-portlet/mobileClientBoot.jsp?appId=myeventsapp108&uuid=11166763-e89c-44ba-aba7-4e9f4fdf97a9&height=480&width=320&noSim=true&preventCache=true
         * xapp-html-filter-mobile:end :: memory : 13.64 :: diff : 0.051712036132812, referer: http://mc007ibi.dyndns.org:8080/XApp-portlet/mobileClientBoot.jsp?appId=myeventsapp108&uuid=11166763-e89c-44ba-aba7-4e9f4fdf97a9&height=480&width=320&noSim=true&preventCache=true

         * 3 times bigger;
        xapp-html-filter-mobile:start :: memory : 13.59 :: diff : 0.0061390399932861, referer: http://mc007ibi.dyndns.org:8080/XApp-portlet/mobileClientBoot.jsp?appId=myeventsapp108&uuid=11166763-e89c-44ba-aba7-4e9f4fdf97a9&height=480&width=320&noSim=true&preventCache=true
        xapp-html-filter-mobile:end :: memory : 13.64 :: diff : 0.053094148635864, referer: http://mc007ibi.dyndns.org:8080/XApp-portlet/mobileClientBoot.jsp?appId=myeventsapp108&uuid=11166763-e89c-44ba-aba7-4e9f4fdf97a9&height=480&width=320&noSim=true&preventCache=true

         *
         */

        return $result;
    }


    public function __construct($string)
    {
        //$this->vars = $vars;
        $this->string = $string;
    }
}

?>