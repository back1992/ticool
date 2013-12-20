<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * RPC-Based class for Wordpress core types
 *
 * @package XApp-Connect\Wordpress
 * @class Wordpress
 * @error @TODO
 * @author  mc007
 */
class Wordpress extends Xapp_Wordpress_Plugin
{

    protected function default_paging_params() {
        return array (
            'offset' => 0,
            'limit' => 100,
            'version' => 1
        );
    }
    /**
     * option to specify a cache config, also used for the indexer : important
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS = 'WP_CACHE_NS';

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    private function init()
    {
    }


    private function _emptyData($title=null){
        $res = "{\"class\":\"pmedia.types.CList\",\"title\":\"" .$title ."\",\"order\":\"0\",\"items\":[]}";
        return $res;
    }

    private function createDSParamsStruct($refId, $sourceType)
    {
        $res = array();
        $res['dsUid'] = 'DSUID';
        $res['dsRef'] = $refId;
        $res['dsType'] = $sourceType;

        return $res;
    }

    /***
     * @param $inUrl
     * @return mixed
     */
    private function getShareUrl($inUrl)
    {
        return $this->cleanUrl($this->rootUrl() . $inUrl);
    }

    /**
     * @param array
     * @param map
     * @return array array with remaped keys map(old_key_val=>new_key_val)
     */
    protected function array_remap_keys($input, $map) {
        $remaped_array = array();
        foreach($input as $key => $val) {
            if(array_key_exists($key, $map)) {
                $remaped_array[$map[$key]] = $val;
            }
        }
        return $remaped_array;
    }

    public function get_leading_image_src($post_id) {

        //Post thumbnail is the wordpress term for leading-image
        $post_thumbnail = null;
        if (function_exists("get_the_post_thumbnail")) {
            $post_thumbnail = get_the_post_thumbnail($post_id);
        }
        if ($post_thumbnail) {
            $post_thumbnail_id = get_post_thumbnail_id($post_id);
            $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
            return $post_thumbnail_url;
        }
        return $post_thumbnail;
        /*
        if ($post_thumbnail) {
            $images = xapp_strip_images($post_thumbnail);
            $this->dumpObject($images,'#images');
            if (count($images) > 0) {
                $image = $images[0];
                $image['id'] = "";
                return $image;
            }
        }
        return false;*/

    }

    private  function get_leading_image($post_id) {
        //Post thumbnail is the wordpress term for iconUrl

        $post_thumbnail = false;
        if (function_exists("get_the_post_thumbnail")) {
            $post_thumbnail = get_the_post_thumbnail($post_id);
        }
        if ($post_thumbnail) {
            return $post_thumbnail;
        }

        /*
        if ($post_thumbnail) {
            $images = xapp_strip_images($post_thumbnail);
            $this->dumpObject($images,'#images');
            if (count($images) > 0) {
                $image = $images[0];
                $image['id'] = "";
                return $image;
            }
        }
        return false;*/
        return null;
    }
    private  function include_leading_image_in_attachments(&$attachments, $post_id) {
        $leading_image = false;
        $thumbnail_image = false;
        //if ($this->options['include_featured_image']) {
        $thumbnail_image = $this->get_leading_image($post_id);
        $thumbnail_image = apply_filters('shoutem_leading_image',$thumbnail_image,$post_id);
        //}

        $se_leading_img = get_post_meta($post_id, 'se_leading_img', true);
        //$this->dumpObject($se_leading_img,'$se_leading_img');
        if ($se_leading_img) {
            $leading_image = array(
                'src' => $se_leading_img
            );
        } else if ($thumbnail_image) {
            $leading_image = $thumbnail_image;
        } else if (!empty($this->options['lead_img_custom_field_regex'])) {
            $custom_field_regex = $this->options['lead_img_custom_field_regex'];
            $post_keys = get_post_custom_keys($post_id);
            if ($post_keys) {
                foreach( $post_keys as $custom_key) {

                    if (preg_match($custom_field_regex, $custom_key) > 0) {
                        $leading_image = array(
                            'src' => get_post_meta($post_id, $custom_key, true)
                        );
                        break;
                    }
                }
            }
        }

        if ($leading_image) {
            $leading_image['attachment-type'] = "leading_image";
            array_unshift($attachments['images'],$leading_image);
        }
    }

    private function get_post_detail($post, $params) {
        $attachments = array (
            'images' => array (),
            'videos' => array (),
            'audio' => array ()
        );
        $this->attachments = & $attachments;
        $is_user_logged_in = isset ($params['session_id']);
        $include_raw_post = isset ($params['include_raw_post']);
        $is_reqistration_required = ('1' == get_option('comment_registration'));

        $remaped_post = $this->array_remap_keys($post, array (
            'ID' => 'post_id',
            'post_date_gmt' => 'published_at',
            'post_title' => 'title',
            'post_excerpt' => 'summary',
            'post_content' => 'body',
            'comment_status' => 'commentable',
            'comment_count' => 'comments_count',

        ));

        $post_categories = wp_get_post_categories($remaped_post['post_id']);
        $categories = array ();
        $tags = array ();
        foreach ($post_categories as $category) {
            $cat = get_category($category);
            $categories[] = array (
                'id' => $cat->cat_ID,
                'name' => $cat->name
            );
        }
        $remaped_post['categories'] = $categories;
        //*** ACTION  shoutem_get_post_start ***//
        //Integration with external plugins will usually hook to this action to
        //substitute shortcodes or generate appropriate attachments from the content.
        //For example: @see ShoutemNGGDao, @see ShoutemFlaGalleryDao.
        do_action('shoutem_get_post_start', array (
            'wp_post' => $post,
            'attachments_ref' => & $attachments
        ));

        $body = apply_filters('the_content', do_shortcode($remaped_post['body']));

        if ($include_raw_post) {
            $remaped_post['raw_post'] = $body;
        }

        $striped_attachments = array ();
        $remaped_post['body'] = xapp_sanitize_html($body, $striped_attachments);

        $user_data = get_userdata($post->post_author);
        $remaped_post['author'] = $user_data->display_name;
        $remaped_post['likeable'] = 0;
        $remaped_post['likes_count'] = 0;
        $remaped_post['link'] = get_permalink($remaped_post['post_id']);

        $this->include_leading_image_in_attachments($attachments, $post->ID);

        $attachments['images'] = array_merge($attachments['images'], $striped_attachments['images']);
        $attachments['videos'] = array_merge($attachments['videos'], $striped_attachments['videos']);
        $attachments['audio'] = array_merge($attachments['audio'], $striped_attachments['audio']);

        xapp_sanitize_attachments($attachments);
        $remaped_post['attachments'] = $attachments;
        $remaped_post['image_url'] = '';

        $images = $attachments['images'];
        if (count($images) > 0) {
            $remaped_post['image_url'] = $images[0]['src'];
        }

        $post_commentable = ($remaped_post['commentable'] == 'open');

        if (!$this->options['enable_wp_commentable']) {
            $remaped_post['commentable'] = 'no';
        } else
            if (array_key_exists('commentable', $params)) {
                $remaped_post['commentable'] = $params['commentable'];
            } else {
                $remaped_post['commentable'] = $this->get_commentable($post_commentable, $is_user_logged_in, $is_reqistration_required);
            }

        if ($this->options['enable_fb_commentable']) {
            $remaped_post['fb_commentable'] = 'yes';
        }

        if (!$remaped_post['summary']) {
            $remaped_post['summary'] = wp_trim_excerpt(apply_filters('the_excerpt', get_the_excerpt()));
            $remaped_post['summary'] = xapp_html_to_text($remaped_post['summary']);

        }

        $remaped_post[XC_TITLE] = xapp_html_to_text($remaped_post[XC_TITLE]);

        $leadingImage = $this->get_leading_image($post->ID);
        if($leadingImage){
            $remaped_post['leadingImage']=$leadingImage;
            $remaped_post['image_url']=$this->get_leading_image_src($post->ID);
        }

        $remaped_posts[] = $remaped_post;
        return $remaped_post;
    }

    private function get_post2($post, $params) {
        $attachments = array (
            'images' => array (),
            'videos' => array (),
            'audio' => array ()
        );
        $this->attachments = & $attachments;
        $is_user_logged_in = isset ($params['session_id']);
        $include_raw_post = isset ($params['include_raw_post']);
        $is_reqistration_required = ('1' == get_option('comment_registration'));
        $remaped_post = $this->array_remap_keys($post, array (
            'ID' => 'post_id',
            'post_date_gmt' => 'published_at',
            'post_title' => 'title',
            'post_excerpt' => 'summary',
            'post_content' => 'body',
            'comment_status' => 'commentable',
            'comment_count' => 'comments_count',

        ));

        $post_categories = wp_get_post_categories($remaped_post['post_id']);
        $categories = array ();
        $tags = array ();
        foreach ($post_categories as $category) {
            $cat = get_category($category);
            $categories[] = array (
                'id' => $cat->cat_ID,
                'name' => $cat->name
            );
        }
        $remaped_post['categories'] = $categories;
        //*** ACTION  shoutem_get_post_start ***//
        //Integration with external plugins will usually hook to this action to
        //substitute shortcodes or generate appropriate attachments from the content.
        //For example: @see ShoutemNGGDao, @see ShoutemFlaGalleryDao.
        do_action('shoutem_get_post_start', array (
            'wp_post' => $post,
            'attachments_ref' => & $attachments
        ));

        $body = apply_filters('the_content', do_shortcode($remaped_post['body']));

        if ($include_raw_post) {
            $remaped_post['raw_post'] = $body;
        }

        $striped_attachments = array ();
        $remaped_post['body'] = sanitize_html($body, $striped_attachments);

        $user_data = get_userdata($post->post_author);
        $remaped_post['author'] = $user_data->display_name;
        $remaped_post['likeable'] = 0;
        $remaped_post['likes_count'] = 0;
        $remaped_post['link'] = get_permalink($remaped_post['post_id']);

        //$this->include_leading_image_in_attachments($attachments, $post->ID);

        $attachments['images'] = array_merge($attachments['images'], $striped_attachments['images']);
        $attachments['videos'] = array_merge($attachments['videos'], $striped_attachments['videos']);
        $attachments['audio'] = array_merge($attachments['audio'], $striped_attachments['audio']);

        sanitize_attachments($attachments);
        $remaped_post['attachments'] = $attachments;
        $remaped_post['image_url'] = '';

        $images = $attachments['images'];
        if (count($images) > 0) {
            $remaped_post['image_url'] = $images[0]['src'];
        }

        $post_commentable = ($remaped_post['commentable'] == 'open');

        if (!$this->options['enable_wp_commentable']) {
            $remaped_post['commentable'] = 'no';
        } else
            if (array_key_exists('commentable', $params)) {
                $remaped_post['commentable'] = $params['commentable'];
            } else {
                $remaped_post['commentable'] = $this->get_commentable($post_commentable, $is_user_logged_in, $is_reqistration_required);
            }

        if ($this->options['enable_fb_commentable']) {
            $remaped_post['fb_commentable'] = 'yes';
        }

        if (!$remaped_post['summary']) {
            $remaped_post['summary'] = wp_trim_excerpt(apply_filters('the_excerpt', get_the_excerpt()));
            $remaped_post['summary'] = html_to_text($remaped_post['summary']);
        }

        $remaped_post['title'] = html_to_text($remaped_post['title']);

        $remaped_posts[] = $remaped_post;
        return $remaped_post;
    }

    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getPostDetail($params = "{}")
    {

        $this->onBeforeCall($params);
        $postIn = get_post($this->xcRefId);
        if($postIn){
            $postDetail = (array)$this->get_post_detail($postIn,null);
            if($postDetail){
                $post=array();

                if(isset($postDetail['image_url'])){
                    $post[XC_ICON_URL]=$postDetail['image_url'];
                }
                if(isset($postDetail['title'])){
                    $post[XC_TITLE]=$postDetail['title'];
                }
                if(isset($postDetail['post_id'])){
                    $post[XC_REF_ID]=$postDetail['post_id'];
                }

                if(isset($postDetail['author'])){
                    $post[XC_OWNER_REF_STR]=$postDetail['author'];
                }
                if(isset($postDetail['link'])){
                    $post['link']=$postDetail['link'];
                }

                if(isset($postDetail['body'])){
                    //$post['pictureItems']=toPictureItems($postDetail['body']);
                    //$content = apply_filters ("the_content", $postDetail['body']);
                    //$content = apply_filters('the_content', do_shortcode($postDetail['body']));
                    //$striped_attachments = array ();

                    //$content = xapp_sanitize_html($content, $postDetail['attachments']);
                    //$content = xapp_sanitize_html($content, $striped_attachments);

                    //$this->log('content after filter ' . $content);

                    $wpcontent = $postIn->post_content;
                    $wpcontent = apply_filters('the_content', $wpcontent);

                    if(isset($postDetail['leadingImage'])){
                        $wpcontent = $postDetail['leadingImage'] . $wpcontent;
                        $post[XC_ICON_URL] = $this->get_leading_image_src($post[XC_REF_ID]);
                    }
                    //$post['introText']=$postDetail['body'];
                    $post[XC_INTRO]=$wpcontent;
                }

                if(isset($postDetail['published_at'])){
                    $post[XC_DATE_STRING]=xapp_nicetime($postDetail['published_at']);
                }


                $resData = array();
                $resData[0] = $post;

                $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $resData);

                //$this->dumpObject($postDetail,'postDetail inner');
                //$this->dumpObject($res,'result');
                //$this->dumpObject(json_decode($res),'result');
                return $res;
            }else{
                $this->log('have no post detail');
            }
        }else{
            $this->log('no such post!');
        }

        return "{}";
    }

    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getPageDetail($params = "{}")
    {
        return $this->getPostDetail($params);
    }
    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getPages($params = "{}")
    {

        $this->onBeforeCall($params);
        $args = array(
            'parent'   => '' . $this->xcRefId, // Where 1 and 2 are the category ids of Category A and Category B that you want posts containing either of.
        );
        $items = get_pages( $args );
        $itemsOut = (array)$items;

        foreach ($itemsOut as $post) {

            $postDetail = (array)$this->get_post_detail($post,null);

            if($postDetail){

                //$this->log('hve post detail');

                if(isset($postDetail['image_url']) && strlen($postDetail['image_url'] > 5)){
                    $post->iconUrl=$postDetail['image_url'];
                }elseif(isset($postDetail['leadingImage']) && strlen($postDetail['leadingImage'] > 5)){
                    $post->iconUrl= $this->get_leading_image_src($postDetail['refId']);
                }elseif(isset($postDetail['body'])){
                    $iconUrl = xapp_findPicture($postDetail['body']);
                    if($iconUrl){
                        $post->iconUrl=$iconUrl;
                    }
                }

                if(isset($postDetail['author'])){
                    $post->ownerRefStr=$postDetail['author'];
                }
                if(isset($postDetail['title'])){
                    $post->name=$postDetail['title'];
                }
                if(isset($postDetail['post_id'])){
                    $post->id=$postDetail['post_id'];
                }

                if(isset($postDetail['body'])){
                    $post->introText=strip_tags($postDetail['body']);
                }
                if(isset($postDetail['published_at'])){
                    $post->dateString=xapp_nicetime($postDetail['published_at']);
                }
                    /*
                    $iconUrl = xapp_findPicture($wpcontent);
                    if($iconUrl){
                        $post[XC_ICON_URL]=$iconUrl;
                    }
                    */

                //$this->dumpObject($post[XC_ICON_URL],'iurl');
            }else{
                //$this->log('have no post detail!');
            }
            //$this->dumpObject($postDetail,'post entry detail');
            //$this->dumpObject((array)$post,'post entry');
        }

        if(count($items)==0){
            return $this->_emptyData();
        }

        $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $itemsOut);
        return $res;
    }

    private function _cleanJSONString($string){
        $string = str_replace('\/', '/', $string);
        $string= str_replace('[[', '[', $string);
        $string= str_replace(']]', ']', $string);
        $string= preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        return $string;

    }
    private function _insertCommentCount($postDetail,$dstData,$encode=true){

        assert($postDetail && $dstData);

        $nbComments = intval(xapp_array_get($postDetail,'comments_count'));
        if($nbComments > 0){
            $insertions = xapp_property_get($dstData,XC_INSERTIONS);
            if($insertions==null){
                $insertions = $dstData->{XC_INSERTIONS}=array();
            }
            $insertionIndex = count($insertions);


            /***
             *
             */
            $insert = array();
            $insert['insertNodeQuery'] = '.itemDetail';
            $insert['insertPlacement'] = 'after';
            $insert['insert'] = '<div class="itemDetail bubble"><span class="bubbleText">' . $nbComments . '</span></div>';
            $insertions[$insertionIndex]=$insert;

            if($encode){
                $insertions = json_encode($insertions);
                $insertions = $this->_cleanJSONString($insertions);
            }
            $dstData->{XC_INSERTIONS}=$insertions;
        }
    }
    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getPosts($params = "{}")
    {

        $this->onBeforeCall($params);

        $args = array(
            'cat'   => '' . $this->xcRefId, // Where 1 and 2 are the category ids of Category A and Category B that you want posts containing either of.
        );
        $items = get_posts( $args );
        $itemsOut = (array)$items;

        //$this->dumpObject($items);

        foreach ($itemsOut as $post) {

            $postDetail = (array)$this->get_post_detail($post,null);

            if($postDetail){

                if(isset($postDetail['image_url']) && count($postDetail['image_url'] > 5)){
                    $post->{XC_ICON_URL}=$postDetail['image_url'];
                }elseif(isset($postDetail['leadingImage'])){
                    $post->{XC_ICON_URL}= $this->get_leading_image_src($postDetail[XC_REF_ID]);
                }
                if(isset($postDetail['author'])){
                    $post->{XC_OWNER_REF_STR}=$postDetail['author'];
                }

                if(isset($postDetail['body'])){
                    $post->{XC_INTRO}=strip_tags($postDetail['body']);
                }
                if(isset($postDetail['published_at'])){
                    $post->{XC_DATE_STRING}=xapp_nicetime($postDetail['published_at']);
                }

                $this->_insertCommentCount($postDetail,$post);
            }
            //$this->dumpObject($postDetail,'post entry detail');
            //$this->dumpObject((array)$post,'post entry');
        }

        if(count($items)==0){
            return $this->_emptyData();
        }

        $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $itemsOut);
        return $res;
    }

    private function getCategoryDetail($id)
    {
        $res = get_the_category_by_ID(intval($id));
        return $res;
    }

    /***
     *
     * @link http://192.168.1.37/wordpress//wp-content/plugins/xapp1/xapp/indexWP.php?service=Wordpress.customTypeQuery&params={%22DSUID%22:%22d69834ac-ba4f-48df-b837-3c5d730cf236%22,%22BASEREF%22:%22http://192.168.1.37/wordpress/%22,%22REFID%22:%220%22,%22CTYPE%22:%22WPCategory%22,%22APPID%22:%22mygeneralapp83%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://192.168.1.37:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22wpCategory%22,%22SCREEN_WIDTH%22:320}&callback=asd
     * @link http://192.168.1.37/wordpress//wp-content/plugins/xapp1/xapp/indexWP.php?service=Wordpress.testMedthod&method=getCategories&id=0
     * @appCall : http://192.168.1.37/wordpress//wp-content/plugins/xapp1/xapp/indexWP.php?service=Wordpress.customTypeQuery&params={"DSUID":"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d","BASEREF":"http://192.168.1.37/zoo254/","REFID":"2","CTYPE":"WordpressCategory","APPID":"myeventsapp1d0","RT_CONFIG":"debug","UUID":"11166763-e89c-44ba-aba7-4e9f4fdf97a9","SERVICE_HOST":"http://mc007ibi.dyndns.org:8080/XApp-portlet/","IMAGE_RESIZE_URL":"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=","SOURCE_TYPE":"wpCategory","SCREEN_WIDTH":320}&callback=bla
     * @param $refId
     * @return array|null
     */
    public function getCategories($params = "{}")
    {

        $this->onBeforeCall($params);

        //$page = array();
        $pageNumber = $this->xcRefId;

        $catLimit = 100;

        $limitForRequest = $catLimit * 2;
        $offset = $catLimit * $pageNumber;

        /*
         * 'number' => $limitForRequest,
            'offset' => $offset,
            'hierarchical' => FALSE,
            'pad_counts' => 1,
         */
        $cat_args = array(
            'child_of' => intval($this->xcRefId),
            'hierarchical' => 0,
            'hide_empty' =>1
        );
        $categories=null;
        if($this->xcRefId==0){
            $categories = get_categories();
        }else{
            $categories = get_categories($cat_args);
        }

        //$this->dumpObject($categories,'wp categories');

        if(count($categories)==0){
            return $this->_emptyData($this->getCategoryDetail($this->xcRefId));
        }

        if($categories){


            //in concrete categories, we need to add the title!
            //TODO : check WP category plugins !
            $encodeFilterResult=true;
            $wpCat=null;
            if(strlen($this->xcRefId)>0){
                $wpCat = $this->getCategoryDetail($this->xcRefId);
                $encodeFilterResult=false;
            }

            $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $categories,$encodeFilterResult);
            if($wpCat!=null){
                $res->title=$wpCat;
            }
            $res =  $encodeFilterResult ? $res : json_encode($res);
            return $res;

        }
        return "{}";
    }

    /***
     *
     * @return integer
     */
    function load()
    {
        parent::load();
        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
        return true;
    }

    /**
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message, $ns = "", $stdError = true)
    {
        parent::log($message, "Wordpress", $stdError);
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
