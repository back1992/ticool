<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * RPC-Based class for Joomla's VirtueMart
 *
 * @package XApp-Connect\Joomla
 * @class VMart
 * @error @TODO
 * @author  mc007
 * @TODO : all of it
 */
class VMart extends Xapp_Joomla_Plugin
{
    /**
     * option to specify a cache config
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS = 'VMART_CACHE_NS';

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    private function init()
    {

    }

    /***
     * We override base search because we've got multiple types to search
     * @param $query
     * @return array|null
     */
    public function searchMultiple($query,$customTypes){

    }

    /***
     * @param $query
     * @param $customTypes
     */
    private function toIndexParameters($customTypeName){
        $cType= CustomTypeManager::instance()->getType(
            $customTypeName,
            null,
            null,
            null,//platform : defaults to IPHONE_NATIVE
            'debug',
            false);

        if($cType){
            return $this->getIndexOptions($cType);
        }else{
            $this->log('have no ct');
        }
        return null;
    }

    /***
     * We override base search because we've got multiple types to search
     * @param $query
     * @return array|null
     */
    public function search($query){

        $indexParameters = array(
            $this->toIndexParameters('VMartCategory'),
            $this->toIndexParameters('VMartProduct')
        );

        $searchResults = array();

        foreach($indexParameters as $iParams){
            $foundItems=true;
            if(!is_array($iParams)){
                continue;
            }

            $cTypeSearchResults = $this->searchEx($query,$iParams,'Shopping');
            if(count($cTypeSearchResults)){
                $searchResults= array_merge($searchResults,$cTypeSearchResults);
            }else{
                $searchResults=array_merge($searchResults,array());
            }
        }
        return $searchResults;
    }

    public function searchTest($query){
        $this->search($query);
    }

    /***
     * @param string $params
     * @return mixed
     */
    public function getFeatured($params = '{}')
    {
        $this->onBeforeCall($params);
        $jsonRes = array();
        $limit = 100;
        $pageno = 0;
        if ($this->shop_is_offline == 0) {
            if (VIRTUEMART_FEATURED == 1) {
                $feature_id = FEATURED_PRODUCT;

                $query_OverrideFeature = " SELECT virtuemart_product_id FROM #__virtuemart_products ";
                $query_OverrideFeature .= " WHERE virtuemart_product_id IN ($feature_id) AND published = 1 ";
                $this->db->setQuery($query_OverrideFeature);
                $featured_products = $this->db->loadResultArray();


            } else {

                $featured_products = $this->productModel->getProductListing('featured', 5, $withCalc = true, $onlyPublished = true, $single = false, $filterCategory = true);
            }

            if ($featured_products) {
                if (VIRTUEMART_FEATURED == 1) {
                    for ($j = 0; $j < count($featured_products); $j++) {
                        $overrideFeature = $this->productModel->getProducts($featured_products, $front = true, $withCalc = true, $onlyPublished = true, $single = false);
                    }
                }

                $array_feature = array();
                if (VIRTUEMART_FEATURED == 1) {
                    $array_feature = $overrideFeature;
                } else {
                    $array_feature = $featured_products;
                }
                $cntFeature = 0;
                if ($array_feature) {
                    $count_FeatureProduct = count($array_feature);

                    /**
                     *Jesus, this bs is not from us !
                     */
                    if ($limit == '' || $limit == 0) {
                        $limit = GENERAL_PAGINATION_LIMIT;
                    }

                    if ($pageno == 0 || $pageno == 1) {
                        $start_to = 0;
                    } else {
                        if ($limit == 1) {
                            $start_to = ($pageno - 1);
                        } else {
                            $start_to = $limit * ($pageno - 1);
                        }

                        if ($start_to > $count_FeatureProduct) {
                            $start_to = $count_FeatureProduct;
                        }
                        $limit = ($start_to + $limit);

                    }

                    for ($j = $start_to; $j < $limit; $j++) {

                        if ($pageno > 1) {
                            $cnt = $j;
                        } else {
                            $cnt = 0;
                        }

                        if ($limit > $count_FeatureProduct) {
                            $start_to = $j;
                            $limit = $count_FeatureProduct;
                        }

                        for ($i = $j; $i < $limit; $i++) {
                            $jsonRes['products'][$cnt]['refId'] = $array_feature[$cnt]->virtuemart_product_id;
                            $jsonRes['products'][$cnt]['title'] = $array_feature[$cnt]->product_name;
                            $jsonRes['products'][$cnt]['product_sku'] = $array_feature[$cnt]->product_sku;
                            $jsonRes['products'][$cnt]['description'] = strip_tags($array_feature[$cnt]->product_s_desc);
                            $jsonRes['products'][$cnt]['inStock'] = 'In Stock : ' . $array_feature[$cnt]->product_in_stock;
                            $currency_symbol = $this->currency->getSymbol();
                            $jsonRes['products'][$cnt]['currency_symbol'] = $currency_symbol;

                            // used For Display Thumb and Full Image
                            //$this->default_image = JURI::ROOT().'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'vmgeneral'.DS.'noimage.gif';
                            //$check_parent = $this->productModel->getProductParent($array_feature[$cnt]->product_parent_id);

                            // Display Thumb and Full Image
                            if (isset($array_feature[$cnt]->virtuemart_media_id[0])) {
                                $media_id = $array_feature[$cnt]->virtuemart_media_id[0];
                                //echo $media_id; exit;
                                if (!empty($media_id)) {
                                    //$display_images = $this->class_media->displayImage($media_id[0]);
                                    $query = "SELECT virtuemart_media_id,file_url,file_url_thumb FROM #__virtuemart_medias
															WHERE virtuemart_media_id='" . $media_id . "' ";
                                    $this->db->setQuery($query);
                                    $res_images = $this->db->loadObject();
                                    if (!empty($res_images)) {
                                        $product_full = $this->siteUrl() . $res_images->file_url;
                                        $product_thumbs = $this->siteUrl() . $res_images->file_url_thumb;

                                        $check_full = JPATH_SITE . DS . $res_images->file_url;
                                        $check_thumb1 = JPATH_SITE . DS . $res_images->file_url_thumb;

                                        if (file_exists($check_thumb1)) {
                                            $jsonRes['products'][$cnt]['product_thumb_image'] = $product_thumbs;
                                        } else {
                                            $jsonRes['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                        }

                                        if (file_exists($check_full)) {
                                            $jsonRes['products'][$cnt]['iconUrl'] = $product_full;

                                        } else {
                                            $jsonRes['products'][$cnt]['product_full_image'] = $this->default_image;

                                        }

                                    } else {
                                        $jsonRes['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                        $jsonRes['products'][$cnt]['product_full_image'] = $this->default_image;
                                    }

                                } else {
                                    $jsonRes['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                    $jsonRes['products'][$cnt]['product_full_image'] = $this->default_image;

                                }
                            } else {
                                $jsonRes['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                $jsonRes['products'][$cnt]['product_full_image'] = $this->default_image;
                            }

                            //$jsonRes['products'][$cnt]['category_id'] = $array_feature[$cnt]->virtuemart_category_id;

                            // Display Symbol of Currency and decimal Gross Price
                            if (isset($array_feature[$cnt]->prices['salesPrice'])) {
                                $decimal_gross = $this->currency->priceDisplay($array_feature[$cnt]->prices['salesPrice']);

                            }

                            // Display Symbol of currency and Decimal from Discount Amount
                            if (isset($array_feature[$cnt]->prices['discountAmount'])) {
                                $decimal_discount = $this->currency->priceDisplay($array_feature[$cnt]->prices['discountAmount']);

                            }

                            // Display Symbol of Currency and Decimal from PriceWithoutTax 
                            if (isset($array_feature[$cnt]->prices['priceWithoutTax'])) {
                                $decimal_beforeTax = $this->currency->priceDisplay($array_feature[$cnt]->prices['priceWithoutTax']);

                            }

                            if (FEATURED_PRODUCT_PRICE == "global") {
                                $jsonRes['products'][$cnt]['price'] = $decimal_gross;
                            } else {
                                if (FEATURED_PRODUCT_PRICE == 1) {
                                    $jsonRes['products'][$cnt]['price'] = $decimal_gross;

                                } else {
                                    $jsonRes['products'][$cnt]['price'] = $decimal_gross;
                                }
                            }

                            $jsonRes['products'][$cnt]['rawPrice'] = floatval($array_feature[$cnt]->prices['salesPrice']);


                            if (FEATURED_PRODUCT_DISCOUNT == "global") {
                                if (GENERAL_DISCOUNT == 1) {
                                    $jsonRes['products'][$cnt]['discount_amount'] = $decimal_discount;
                                } else {
                                    $jsonRes['products'][$cnt]['discount_amount'] = '';
                                }

                            } else {
                                if (FEATURED_PRODUCT_DISCOUNT == 1) {
                                    $jsonRes['products'][$cnt]['discount_amount'] = $decimal_discount;
                                } else {
                                    $jsonRes['products'][$cnt]['discount_amount'] = '';
                                }

                            }

                            if (FEATURED_PRODUCT_TAX == "global") {
                                if (GENERAL_TAX == 1) {
                                    $jsonRes['products'][$cnt]['after_tax_amount'] = $decimal_gross;
                                } else {
                                    $jsonRes['products'][$cnt]['before_tax_amount'] = $decimal_beforeTax;
                                }

                            } else {
                                if (FEATURED_PRODUCT_TAX == 1) {
                                    $jsonRes['products'][$cnt]['after_tax_amount'] = $decimal_gross;
                                } else {
                                    $jsonRes['products'][$cnt]['before_tax_amount'] = $decimal_beforeTax;
                                }

                            }
                            if (isset($array_feature[$cnt]->categories)) {
                                $cntcat = 0;
                                foreach ($array_feature[$cnt]->categories as $disp_cat) {
                                    $catId = intval($disp_cat);
                                    $jsonRes['products'][$cnt]['groupId'] = $catId;
                                    $func_category = $this->class_category->getCategory($catId, false);
                                    if ($func_category) {
                                        $jsonRes['products'][$cnt]['groupIdStr'] = $func_category->category_name;
                                    }
                                    $cntcat++;
                                    break;
                                }

                            }
                            $product_reviews = $this->ratingModel->getRatingByProduct($array_feature[$cnt]->virtuemart_product_id);
                            if (!empty($product_reviews)) {
                                $jsonRes['products'][$cnt]['rating'] = $product_reviews->rating;
                                $jsonRes['products'][$cnt]['ratings'] = $product_reviews->ratingcount;
                            }

                            $cnt++;
                        }
                    }
                }
            }
        } else {
            $jsonRes['code'] = 2;
            $jsonRes['invalid_data'] = "Our Shop is currently down for maintenance. Please check back again soon.";
        }
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $jsonRes['products']);

        //$this->dumpObject($res);


        //$this->searchTest('Nic*');

        $this->onAfterCall($res);

        return $res;
    }


    /***
     * Turn off the lights
     * @param $result
     */
    public function onAfterCall($result){
        /*$this->productModel=null;
        $this->class_category=null;
        $this->version=null;
        $this->db=null;
        $this->class_media=null;
        $this->currency=null;
        $this->ratingModel=null;*/
        parent::onAfterCall($result);


    }
    /***
     *
     * @param $refId
     * @return array
     */
    private function getProductPrices($refId)
    {
        $result = array();
        $product_detail = $this->productModel->getProduct($refId, $front = true, $withCalc = true, $onlyPublished = true);
        if (!$product_detail) {
            return $result;
        }

        define(GENERAL_PRICE, 1);
        define(GENERAL_DISCOUNT, 1);
        define(GENERAL_TAX, 1);

        $currency_symbol1 = $this->currency->getSymbol();
        $grossValue = null;


        if (GENERAL_PRICE == 1) {
            $grossValue = $this->currency->priceDisplay($product_detail->prices['salesPrice']);
            $result['salesPrice'] = $grossValue;
        } else {
            $result['price'] = '';
        }

        $discountValue = $this->currency->priceDisplay($product_detail->prices['discountAmount']);

        if (GENERAL_DISCOUNT == 1) {
            $result['discount_amount'] = $discountValue;

        } else {
            $result['discount_amount'] = $discountValue;
        }

        if (GENERAL_TAX == 1) {
            $afterTaxValue = $this->currency->priceDisplay($product_detail->prices['salesPrice']);
            $result['aftre_tax_amount'] = $afterTaxValue;

        } else {
            $beforeTaxValue = $this->currency->priceDisplay($product_detail->prices['priceWithoutTax']);
            $result['before_tax_amount'] = $beforeTaxValue;

        }

        if (isset($product_detail->prices['taxAmount'])) {
            $taxAmount = $this->currency->priceDisplay($product_detail->prices['taxAmount']);
            $result['tax_amount'] = $taxAmount;
        } else {
            $result['tax_amount'] = '';
        }

        if (isset($product_detail->prices['Tax'])) {
            $cntTax = 0;
            foreach ($product_detail->prices['Tax'] as $valTax) {
                $tax_rate = $valTax[1] . $valTax[2];
                $result['tax_rate'] = $tax_rate;
                $cntTax++;
            }
        }
        $result['product_types_val'] = 0;
        $result['product_types'] = '';
        $result['discount_rate'] = 0;
        $result['currency'] = $currency_symbol1;


        //$this->dumpObject($result);

        return $result;

    }


    /***
     * @param $refId
     * @param $sourceType
     * @return array
     */
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

    /***
     * @param string $params
     * @return string
     */
    public function getReviews($params = '{}')
    {

        $this->onBeforeCall($params);

        $product_id = $this->xcRefId;
        $jsonRes = array();
        if ($product_id > 0) {
            if ($this->shop_is_offline == 0) {
                $product_detail = $this->productModel->getProduct($product_id, $front = true, $withCalc = true, $onlyPublished = true);

                if ($product_detail) {

                    /***
                     * Ratings & Reviews
                     */
                    $product_reviews = $this->ratingModel->getReviewsByProduct($product_detail->virtuemart_product_id);
                    if ($product_reviews) {
                        $cntReviews = 0;
                        foreach ($product_reviews as $val_reviews) {
                            $jsonRes['product']['reviews']['items'][$cntReviews]['description'] = addslashes($val_reviews->comment);
                            $query_uname = " SELECT username FROM #__users WHERE id=" . $val_reviews->created_by;
                            $this->db->setQuery($query_uname);
                            $res_uname = $this->db->loadResult();
                            $jsonRes['product']['reviews']['items'][$cntReviews]['title'] = $res_uname;
                            $reviewtime = date("l ,d F Y", strtotime($val_reviews->created_on));
                            $jsonRes['product']['reviews']['items'][$cntReviews]['dataString'] = $reviewtime;
                            $jsonRes['product']['reviews']['items'][$cntReviews]['rating'] = round($val_reviews->review_rating);
                            $cntReviews++;
                        }
                    }

                    $product_reviews = $this->ratingModel->getRatingByProduct($product_detail->virtuemart_product_id);
                    if (!empty($product_reviews)) {
                        $jsonRes['product']['rating'] = $product_reviews->rating;
                        $jsonRes['product']['ratings'] = $product_reviews->ratingcount;
                    }

                }
            } else {
                $jsonRes['code'] = 2;
                $jsonRes['invalid_data'] = "Our Shop is currently down for maintenance. Please check back again soon.";
            }
        } else {
            $jsonRes['code'] = 2;
        }
        $resData = $jsonRes['product']['reviews']['items'];
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $resData);
        return $res;
    }

    /***
     * @param string $params
     * @return string
     */
    public function getProductDetail($params = '{}')
    {

        xapp_print_memory_stats('xapp-connect-vmart-before schema filter getProductDetail');

        $this->onBeforeCall($params);
        $product_id = $this->xcRefId;

        $prices = $this->getProductPrices($product_id);
        //$this->dumpObject($prices);

        $jsonRes = array();
        if ($product_id > 0) {
            if ($this->shop_is_offline == 0) {
                $product_detail = $this->productModel->getProduct($product_id, $front = true, $withCalc = true, $onlyPublished = true);

                if ($product_detail) {
                    $jsonRes['product']['shareUrl'] = $this->getShareUrl($product_detail->link);


                    $jsonRes['product']['refId'] = $product_detail->virtuemart_product_id;
                    $jsonRes['product']['product_sku'] = $product_detail->product_sku;
                    $jsonRes['product']['title'] = $product_detail->product_name;

                    //$jsonRes['product']['introText'] 	= strip_tags($product_detail->product_s_desc);
                    //$jsonRes['product']['product_long_desc'] 	= strip_tags($product_detail->product_desc);

                    //$jsonRes['product']['introText'] 	= $product_detail->product_s_desc);
                    $jsonRes['product']['introText'] = $product_detail->product_desc;


                    //$jsonRes['product']['category_id'] 		= $product_detail->virtuemart_category_id;
                    $jsonRes['product']['product_stock'] = $product_detail->product_in_stock;
                    $jsonRes['product']['min_purchase'] = $product_detail->min_order_level;
                    $jsonRes['product']['max_purchase'] = $product_detail->max_order_level;


                    //$jsonRes['product']['category_id'] 		= $product_detail->virtuemart_category_id;

                    define(GENERAL_PRICE, 1);
                    define(GENERAL_DISCOUNT, 1);
                    define(GENERAL_TAX, 1);

                    // Display Currency Symbol
                    $currency_symbol1 = $this->currency->getSymbol();
                    $grossValue = null;
                    $doPrice = true;
                    if ($doPrice) {
                        if (GENERAL_PRICE == 1) {
                            $grossValue = $this->currency->priceDisplay($product_detail->prices['salesPrice']);
                            $jsonRes['product']['title2'] = $grossValue;
                            //$jsonRes['product']['price']= $product_detail->prices['salesPrice'];

                        } else {
                            $jsonRes['product']['price'] = '';
                        }

                        $discountValue = $this->currency->priceDisplay($product_detail->prices['discountAmount']);
                        if (GENERAL_DISCOUNT == 1) {

                            $jsonRes['product']['discount_amount'] = $discountValue;

                        } else {
                            $jsonRes['product']['discount_amount'] = $discountValue;
                        }

                        if (GENERAL_TAX == 1) {
                            $afterTaxValue = $this->currency->priceDisplay($product_detail->prices['salesPrice']);
                            $jsonRes['product']['aftre_tax_amount'] = $afterTaxValue;

                        } else {
                            $beforeTaxValue = $this->currency->priceDisplay($product_detail->prices['priceWithoutTax']);
                            $jsonRes['product']['before_tax_amount'] = $beforeTaxValue;

                        }

                        if (isset($product_detail->prices['taxAmount'])) {
                            $taxAmount = $this->currency->priceDisplay($product_detail->prices['taxAmount']);
                            $jsonRes['product']['tax_amount'] = $taxAmount;
                        } else {
                            $jsonRes['product']['tax_amount'] = '';
                        }

                        if (isset($product_detail->prices['Tax'])) {
                            $cntTax = 0;
                            foreach ($product_detail->prices['Tax'] as $valTax) {
                                $tax_rate = $valTax[1] . $valTax[2];
                                $jsonRes['product']['tax_rate'] = $tax_rate;
                                $cntTax++;
                            }
                        }
                        $jsonRes['product']['product_types_val'] = 0;
                        $jsonRes['product']['product_types'] = '';
                        $jsonRes['product']['discount_rate'] = 0;


                        $jsonRes['product']['currency'] = $currency_symbol1;
                    }

                    /****
                     * new : insertions
                     */

                    $inserts = array();
                    $inserts[0]['insertNodeQuery'] = '.TitleBackground';
                    $inserts[0]['insertPlacement'] = 'after';
                    //$inserts[0]['insert']='<div>';

                    $insert = '<div class="itemBackground" style="width:100%;padding: 8px" >';
                    $insert .= '<p><span class="Text" style="font-weight:normal">Sales Price : </span><span class="Text">' . $grossValue . '</span></p>';
                    $insert .= '<p><span class="Text" style="font-weight:normal">Tax : </span><span class="Text">' . $jsonRes['product']['tax_amount'] . '</span></p>';

                    if ($product_detail->prices['discountAmount'] != 0) {
                        $insert .= '<p><span class="Text" style="font-weight:normal">Discount : </span><span class="Text">' . $jsonRes['product']['discount_amount'] . '</span></p>';
                    }
                    $insert .= '</div';

                    $inserts[0]['insert'] = $insert;

                    /*
                                        $doc = phpQuery::newDocumentHTML($inserts[0]['insert']);
                                        phpQuery::selectDocument($doc);
                                        pq('div')->attr('style','width:100%');
                                        pq('div')->append('<span></span>');
                                        //$this->dumpObject($label);
                                        pq('div span')->addClass('Title');
                                        $inserts[0]['insert']='' .phpQuery::getDocument()->html();
                    */


                    //$this->dumpObject($jsonRes['product']['insertions'],'final insert');

                    // Display Thumb and Full Image
                    if (isset($product_detail->virtuemart_media_id[0])) {
                        $media_id = $product_detail->virtuemart_media_id[0];
                        //echo "<pre/>"; print_r($media_id[0]); exit;
                        if (!empty($media_id)) {
                            $query = "SELECT * FROM #__virtuemart_medias where `virtuemart_media_id`= " . $media_id;
                            $this->db->setQuery($query);
                            $res_images = $this->db->loadObject();
                            if (!empty($res_images)) {
                                $product_full = $this->siteUrl() . $res_images->file_url;
                                $product_thumbs = $this->siteUrl() . $res_images->file_url_thumb;

                                $check_full = JPATH_SITE . DS . $res_images->file_url;
                                $check_thumb1 = JPATH_SITE . DS . $res_images->file_url_thumb;

                                if (file_exists($check_thumb1)) {
                                    $jsonRes['product']['product_thumb_image'] = $product_thumbs;
                                } else {
                                    $jsonRes['product']['product_thumb_image'] = $this->default_image;
                                }

                                if (file_exists($check_full)) {
                                    $jsonRes['product']['product_full_image'] = $product_full;
                                    $newIntroText = '<img src="' . $product_full . '">' . $jsonRes['product']['introText'];
                                    $jsonRes['product']['introText'] = $newIntroText;

                                } else {
                                    $jsonRes['product']['product_full_image'] = $this->default_image;
                                }

                            } else {
                                $jsonRes['product']['product_thumb_image'] = $this->default_image;
                                $jsonRes['product']['product_full_image'] = $this->default_image;
                            }
                        } else {
                            $jsonRes['product']['product_thumb_image'] = $this->default_image;
                            $jsonRes['product']['product_full_image'] = $this->default_image;
                        }
                    } else {
                        $jsonRes['product']['product_thumb_image'] = $this->default_image;
                        $jsonRes['product']['product_full_image'] = $this->default_image;
                    }

                    // Display Category Id
                    if (isset($product_detail->categories)) {
                        $cntcat = 0;
                        foreach ($product_detail->categories as $disp_cat) {
                            $jsonRes['product']['groupId'] = $disp_cat;
                            $cntcat++;
                            $catId = intval($disp_cat);
                            $func_category = $this->class_category->getCategory($catId, false);
                            if ($func_category) {
                                $jsonRes['product']['groupIdStr'] = $func_category->category_name;
                            }
                            break;
                        }
                    }
                    define(GENERAL_RELATED_PRODUCTS_ON_PRODUCT_DETAIL, 1);
                    //$this->dumpObject($product_detail);
                    // Display Releted Products and categories
                    if (GENERAL_RELATED_PRODUCTS_ON_PRODUCT_DETAIL == 1) {
                        /***
                         * Related categories
                         */
                        if (isset($product_detail->customfieldsRelatedCategories)) {
                            for ($relcat = 0; $relcat < count($product_detail->customfieldsRelatedCategories); $relcat++) {
                                $relCatPath = $product_detail->customfieldsRelatedCategories[$relcat];
                                $jsonRes['product']['related_categories'][$relcat]['category']['category_id'] = $relCatPath->custom_value;
                            }
                        } else {
                            $jsonRes['product']['related_categories'] = '';
                        }


                        /***
                         * Related producs
                         */
                        if (isset($product_detail->customfieldsRelatedProducts)) {
                            for ($relpro = 0; $relpro < count($product_detail->customfieldsRelatedProducts); $relpro++) {
                                $relProductPath = $product_detail->customfieldsRelatedProducts[$relpro];
                                $jsonRes['product']['related'][$relpro]['refId'] = $relProductPath->custom_value;

                                $jsonRes['product']['related'][$relpro]['headerText'] = trim(strip_tags($relProductPath->display));
                                $func_relatedImage = $this->class_media->getFiles($onlyPublished = false, $noLimit = false, $relProductPath->custom_value, $cat_id = null, $where = array(), $nbr = false);

                                $jsonRes['product']['related'][$relpro]['sourceType'] = 'vmProductDetail';
                                $jsonRes['product']['related'][$relpro]['dsUid'] = 'DSUID';

                                $jsonRes['product']['related'][$relpro]['footerText'] = '';

                                $relProductPrice = $this->getProductPrices($relProductPath->custom_value);

                                if ($relProductPrice && count($relProductPrice)) {
                                    $jsonRes['product']['related'][$relpro]['footerText'] = $relProductPrice['salesPrice'];
                                }

                                if (!empty($func_relatedImage)) {
                                    if (!empty($func_relatedImage[0]->virtuemart_media_id)) {
                                        $check_relatedThumb = JPATH_SITE . DS . $func_relatedImage[0]->file_url_thumb;
                                        $related_thumbimage = $this->siteUrl() . $func_relatedImage[0]->file_url_thumb;

                                        if (file_exists($check_relatedThumb)) {
                                            $jsonRes['product']['related'][$relpro]['src'] = $related_thumbimage;
                                        } else {
                                            $jsonRes['product']['related'][$relpro]['src'] = $this->default_image;
                                        }

                                    } else {
                                        $jsonRes['product']['related'][$relpro]['src'] = $this->default_image;
                                    }
                                } else {
                                    $jsonRes['product']['related'][$relpro]['src'] = $this->default_image;
                                }

                            }
                        } else {
                            $jsonRes['product']['related'] = '';
                        }
                    } else {
                        $jsonRes['product']['related_categories'] = '';
                        $jsonRes['product']['related'] = '';
                    }


                    /**
                     * finalize related
                     */
                    if (is_array($jsonRes['product']['related'])) {
                        $jsonRes['product']['related'] = json_encode($jsonRes['product']['related']);

                        $inserts[1]['insertNodeQuery'] = '.TextWrapper';
                        $inserts[1]['insertPlacement'] = 'after';
                        $inserts[1]['insert'] = '';
                        $inserts[1]['insertDataRef'] = 'related';
                        $inserts[1]['insertClass'] = 'xapp.widgets.Carousel';

                        $inserts[1]['insertMixin'] = '{cssClass:"itemBackground",height:"150px",navButton:false,pageIndicator:true,numVisible:2, title:"Related Products"}';
                    }


                    /***
                     * Close inserts
                     */
                    $jsonRes['product']['insertions'] = json_encode($inserts);
                    $jsonRes['product']['insertions'] = str_replace('\/', '/', $jsonRes['product']['insertions']);
                    $jsonRes['product']['insertions'] = str_replace('[[', '[', $jsonRes['product']['insertions']);
                    $jsonRes['product']['insertions'] = str_replace(']]', ']', $jsonRes['product']['insertions']);
                    $jsonRes['product']['insertions'] = preg_replace('/[\x00-\x1F\x7F]/', '', $jsonRes['product']['insertions']);


                    /***
                     * Ratings & Reviews
                     */
                    $product_reviews = $this->ratingModel->getReviewsByProduct($product_detail->virtuemart_product_id);
                    if ($product_reviews) {
                        $cntReviews = 0;
                        foreach ($product_reviews as $val_reviews) {
                            $jsonRes['product']['reviews']['items'][$cntReviews]['review_content'] = addslashes(strip_tags($val_reviews->comment));
                            $query_uname = " SELECT username FROM #__users WHERE id=" . $val_reviews->created_by;
                            $this->db->setQuery($query_uname);
                            $res_uname = $this->db->loadResult();
                            $jsonRes['product']['reviews']['items'][$cntReviews]['ownerRefStr'] = $res_uname;
                            $reviewtime = date("l ,d F Y", strtotime($val_reviews->created_on));
                            $jsonRes['product']['reviews']['items'][$cntReviews]['create_date'] = $reviewtime;
                            $jsonRes['product']['reviews']['items'][$cntReviews]['rating'] = round($val_reviews->review_rating);
                            $cntReviews++;
                        }


                        $jsonRes['product']['reviews']['dsParams'] = $this->createDSParamsStruct($product_id, 'vmReviews');
                        //$this->dumpObject($jsonRes['product']['reviews']);
                        $reviewsEncoded = json_encode($jsonRes['product']['reviews'], true);
                        //$this->log('reviews encoded : ' . $reviewsEncoded . " : " . $this->getLastJSONError());
                        //$this->dumpObject($reviewsEncoded);
                        $jsonRes['product']['reviews'] = $reviewsEncoded;
                        //$this->dumpObject($jsonRes['product']['reviews']);
                    }

                    $product_reviews = $this->ratingModel->getRatingByProduct($product_detail->virtuemart_product_id);
                    if (!empty($product_reviews)) {
                        $jsonRes['product']['rating'] = $product_reviews->rating;
                        $jsonRes['product']['ratings'] = $product_reviews->ratingcount;
                    }


                    // Display Automatic child Products and Sub Products
                    if (strcmp($this->check_version, '2.0.3') == 0 || strcmp($this->check_version, '2.0.4') == 0 || strcmp($this->check_version, '2.0.6') == 0 || strcmp($this->check_version, '2.0.8') == 0) {
                        if (isset($product_detail->customfields)) {
                            //echo "<pre/>"; print_r($product_detail->customfields); exit;
                            $cntSubPro = 0;
                            foreach ($product_detail->customfields as $valSUbPro) {
                                //$jsonRes['product']['custom_fields'][$cntCField]['custom_field']['title'] = $valCField->custom_title;
                                //$jsonRes['product']['custom_fields'][$cntCField]['custom_field']['value'] = $valCField->virtuemart_customfield_id;
                                if ($valSUbPro->field_type == 'A' || $valSUbPro->virtuemart_custom_id == 15) {
                                    // Display Sub Products
                                    $display_subProducts = $this->productModel->getProductChilds($product_detail->virtuemart_product_id);
                                    $no_of_child = $this->productModel->getProductChildIds($product_detail->virtuemart_product_id);
                                    if ($no_of_child > 0) {
                                        $jsonRes['product']['no_child_product'] = count($no_of_child);
                                    } else {
                                        $jsonRes['product']['no_child_product'] = '';
                                    }
                                    //echo "<pre/>"; print_r($display_subProducts); exit;
                                    if (!empty($display_subProducts)) {
                                        //$cntSubPro= 0;
                                        $cntSubProducts = 0;
                                        foreach ($display_subProducts as $valSubProducts) //for($cntSubPro=0;$cntSubPro=count($display_subProducts);$cntSubPro++)
                                        {
                                            $jsonRes['product']['subproducts'][$cntSubProducts]['subproduct']['refId'] = $valSubProducts->virtuemart_product_id;
                                            $jsonRes['product']['subproducts'][$cntSubProducts]['subproduct']['title'] = $valSubProducts->product_name;
                                            $cntSubProducts++;

                                        }
                                    } else {
                                        $jsonRes['product']['subproducts'] = '';
                                    }
                                }
                                $cntSubPro++;
                            }
                        }
                    }
                    if (isset($product_detail->customfieldsCart)) {
                        //echo "<pre/>"; print_r($product_detail->customfieldsCart); exit;
                        $cntAttr = 0;
                        foreach ($product_detail->customfieldsCart as $valattr) {
                            // Add cart format :- 9:472;13:468;14:468;
                            $jsonRes['product']['attributes'][$cntAttr]['attribute']['title'] = JText::_($valattr->custom_title);
                            $jsonRes['product']['attributes'][$cntAttr]['attribute']['required'] = 1;
                            //$jsonRes['product']['custom_attribute'][$cntAttr]['attributes']['display'] = htmlspecialchars(strip_tags($valattr->display), ENT_QUOTES);
                            $jsonRes['product']['attributes'][$cntAttr]['attribute']['attribute_id'] = $valattr->virtuemart_custom_id;
                            /*if($valattr->virtuemart_custom_id == 9)
                            {
                                $jsonRes['product']['attributes'][$cntAttr]['attribute']['type'] = 'select';
                            }*/
                            $jsonRes['product']['attributes'][$cntAttr]['attribute']['type'] = 'select';
                            if ($valattr->field_type == "V") {
                                $explode_size = explode('</option>', $valattr->display);

                                $arr = array();
                                foreach ($explode_size as $size) {
                                    if (!empty($size)) {
                                        $arr[] = strip_tags(trim($size, "\n"));
                                        $str = implode("<br />", $arr);
                                    }
                                }
                            } else if ($valattr->field_type == "S") {
                                $explode_color = explode('</label>', $valattr->display);
                                $arr = array();

                                foreach ($explode_color as $color) {
                                    $arr[] = strip_tags(trim($color, "\n"));
                                    $str = implode("<br />", $arr);
                                }

                            } else if ($valattr->field_type == "M") {
                                $explode_shovel = explode('</label>', $valattr->display);
                                $arr = array();

                                foreach ($explode_shovel as $shovel) {
                                    $arr[] = strip_tags(trim($shovel, "\n"));
                                    $str = implode("<br />", $arr);
                                }
                            }

                            $explode_field = explode('<br />', $str);

                            $cntAttrVal = 0;

                            foreach ($valattr->options as $key => $attrValue) {
                                //echo "<pre/>"; print_r($attrValue);
                                $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntAttrVal]['property']['property_id'] = $key;

                                //$jsonRes['product']['custom_attribute'][$cntAttr]['attributes'][$cntAttrVal]['attribute']['custom_value'] = $attrValue->custom_value;
                                if ($valattr->field_type == "M") {
                                    $disp_media = $this->displayCustomMedia($attrValue->custom_value, $table = 'product');
                                    $media_url = JURI::root() . $disp_media->file_url_thumb;
                                    $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntAttrVal]['property']['media'] = $media_url;
                                    //$jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntAttrVal]['property']['name'] = $media_url;
                                }
                                $cntField = 0;

                                foreach ($explode_field as $valField) {
                                    if (!empty($valField)) {
                                        if ($valattr->field_type == "V") {
                                            /*if(strcmp($this->check_version,'2.0.0')==0)
                                            {
                                                $explode_fieldprice 	= explode(':',$valField);
                                            } else if((strcmp($this->check_version,'2.0.2')==0) || (strcmp($this->check_version,'2.0.3')==0)) {

                                                $explode_fieldprice 	= explode(' ',$valField);
                                            }*/
                                            if (strcmp($this->check_version, '2.0.0') == 0) {
                                                $explode_fieldprice = explode(':', $valField);
                                            } else {

                                                $explode_fieldprice = explode(' ', $valField);
                                            }
                                            //$explode_fieldprice 	= explode(' ',$valField);
                                            //echo "<pre/>"; print_r($explode_fieldprice[1]); echo "<br/>";
                                            $replace_decimal = str_replace(',', '.', $explode_fieldprice[1]);
                                            $display_attrprice = $this->currency->priceDisplay($replace_decimal);
                                            //$display_custonname 	= $explode_fieldprice[0]."  ".$display_attrprice;
                                            //$jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['name'] = trim($display_custonname);
                                            $display_custonname = $explode_fieldprice[0];
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['name'] = trim($display_custonname);
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['operand'] = '';
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['amount'] = $display_attrprice;
                                            /*if(strcmp($this->check_version,'2.0.0')==0)
                                            {
                                                $explode_value = explode(" ",trim($explode_fieldprice[1]));
                                                $str_attr1 = str_replace(",",'.',$explode_value[0]);
                                            } else if((strcmp($this->check_version,'2.0.2')==0) || (strcmp($this->check_version,'2.0.3')==0)) {
                                                $str_attr1 = str_replace(",",'.',$explode_fieldprice[1]);
                                            }*/
                                            if (strcmp($this->check_version, '2.0.0') == 0) {
                                                $explode_value = explode(" ", trim($explode_fieldprice[1]));
                                                $str_attr1 = str_replace(",", '.', $explode_value[0]);
                                            } else {
                                                $str_attr1 = str_replace(",", '.', $explode_fieldprice[1]);
                                            }
                                            //$str_attr1 = str_replace(",",'.',$explode_fieldprice[1]);
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['value'] = is_numeric($str_attr1) ? $str_attr1 : '';

                                        } else {
                                            // changes of 14 mar 2012
                                            //echo $valField; exit;
                                            /*if(strcmp($this->check_version,'2.0.0')==0)
                                            {
                                                $explode_attr  = explode(":",$valField);
                                            } else if((strcmp($this->check_version,'2.0.2')==0) || (strcmp($this->check_version,'2.0.3')==0)) {
                                                $explode_attr  = explode(" ",$valField);
                                            }*/
                                            if (strcmp($this->check_version, '2.0.0') == 0) {
                                                $explode_attr = explode(":", $valField);
                                            } else {
                                                $explode_attr = explode(" ", $valField);
                                            }
                                            //$explode_attr  = explode(" ",$valField);
                                            //echo "<pre/>"; print_r($explode_attr); exit;
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['name'] = trim($explode_attr[0]);
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['operand'] = '';
                                            if (strcmp($this->check_version, '2.0.0') == 0) {
                                                $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['amount'] = trim($explode_attr[1]);
                                            } else {
                                                $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['amount'] = trim($explode_attr[1] . " " . $explode_attr[2]);
                                            }
                                            //$jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['amount'] = trim($explode_attr[1]);
                                            /*if(strcmp($this->check_version,'2.0.0')==0)
                                            {
                                                $explode_value = explode(" ",trim($explode_attr[1]));
                                                $str_attr = str_replace(",",".",$explode_value[0]);
                                            } else if((strcmp($this->check_version,'2.0.2')==0) || (strcmp($this->check_version,'2.0.3')==0)) {
                                                $str_attr = str_replace(",",".",$explode_attr[1]);
                                            }*/
                                            if (strcmp($this->check_version, '2.0.0') == 0) {
                                                $explode_value = explode(" ", trim($explode_attr[1]));
                                                $str_attr = str_replace(",", ".", $explode_value[0]);
                                            } else {
                                                $str_attr = str_replace(",", ".", $explode_attr[1]);
                                            }
                                            $jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['value'] = is_numeric($str_attr) ? $str_attr : '';
                                            // changes end 14 march 2012

                                            //$jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['name'] = trim($valField);

                                        }
                                        //$jsonRes['product']['attributes'][$cntAttr]['attribute'][$cntField]['property']['name'] = trim($valField);
                                    }
                                    $cntField++;
                                }

                                $cntAttrVal++;

                            }
                            //$jsonRes['product']['custom_attribute'][$cntAttr]['attributes']['title'] = $valattr['options']->custom_title;
                            $cntAttr++;

                        }
                    } else {
                        $jsonRes['product']['attributes']['advanced_attributes'] = "";
                    }

                    if (isset($product_detail->customfields)) {
                        $cntCField = 0;
                        foreach ($product_detail->customfields as $valCField) {
                            $jsonRes['product']['custom_fields'][$cntCField]['custom_field']['title'] = $valCField->custom_title;
                            $jsonRes['product']['custom_fields'][$cntCField]['custom_field']['value'] = $valCField->virtuemart_customfield_id;
                            if ($valCField->field_type == 'p' || $valCField->virtuemart_custom_id == 11) {
                                $jsonRes['product']['custom_fields'][$cntCField]['custom_field']['display'] = strip_tags($valCField->display);
                            }
                            if ($valCField->field_type == 'M' || $valCField->virtuemart_custom_id == 7) {
                                //$jsonRes['product']['custom_fields'][$cntCField]['custom_field']['custom_value'] = $valCField->custom_value;
                                $disp_Fieldmedia = $this->displayCustomMedia($valCField->custom_value, $table = 'product');
                                $media_Fieldurl = JURI::root() . $disp_Fieldmedia->file_url_thumb;
                                $jsonRes['product']['custom_fields'][$cntCField]['custom_field']['name'] = $media_Fieldurl;
                            }
                            $cntCField++;
                        }
                    }
                }
            } else {
                $jsonRes['code'] = 2;
                $jsonRes['invalid_data'] = "Our Shop is currently down for maintenance. Please check back again soon.";
            }
        } else {
            $jsonRes['code'] = 2;
        }

        $resData = array();
        $resData[0] = $jsonRes['product'];

        //$this->dumpObject($resData);

        $func_media_id = $this->class_media->getFiles($onlyPublished = true, $noLimit = true, $product_id, $cat_id = null, $where = array(), $nbr = false);

        HTMLFilter::$forceMultiplePictures = count($func_media_id) > 1 ? true : false;


        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $resData);

        //$this->log('final output');
        //$this->dumpObject($res);

        /***
         * Update Picture items
         */
        $resDec = json_decode($res, true);
        if ($resDec && $resDec['pictureItems']) {
            $pictureItems = json_decode($resDec['pictureItems'], true);

            //$this->dumpObject($pictureItems,'pics');

            if (count($func_media_id)) {
                $pictureItems = $this->addMediaItems($pictureItems, $func_media_id);
                if ($pictureItems) {
                    //$this->dumpObject($pictureItems,'pics2');
                    $resDec['displayPictureIndicator'] = true;
                    $resDec['pictureItems'] = json_encode($pictureItems);
                    return json_encode($resDec);
                }
            }
        }


        xapp_print_memory_stats('xapp-connect-vmart-after schema filter getProductDetail');

        return $res;
    }

    /***
     * @param $dst
     * @param $inData
     * @return mixed
     */
    private function addMediaItems($dst, $inData)
    {
        $cnt = 0;
        foreach ($inData as $media) {
            $product_full = $this->siteUrl() . $media->file_url;
            $product_thumbs = $this->siteUrl() . $media->file_url_thumb;

            $check_full = JPATH_SITE . DS . $media->file_url;
            $check_thumb1 = JPATH_SITE . DS . $media->file_url_thumb;

            //$dst[$cnt]['title']=$media->title;

            if (file_exists($check_thumb1)) {
                $dst[$cnt]['product_thumb_image'] = $product_thumbs;
            } else {
                $dst[$cnt]['product_thumb_image'] = $this->default_image;
            }

            if (file_exists($check_full)) {
                $dst[$cnt]['fullSizeLocation'] = $product_full;
            } else {
                $dst[$cnt]['fullSizeLocation'] = $this->default_image;
            }
            $cnt++;
        }
        return $dst;
    }

    /***
     * @param string $params
     * @return mixed
     */
    function getProducts($params = '{}')
    {

        $this->onBeforeCall($params);


        $cat_id = $this->xcRefId;
        $limit = 100;
        $pageno = 0;
        $products = array();
        if ($cat_id > 0) {
            $products['code'] = 1;
            if ($this->shop_is_offline == 0) {
                if(!$this->loaded){
                    error_log('######### not loaded ');
                }
                $cat_products = $this->productModel->getProductsInCategory($cat_id);
                if ($cat_products) {
                    $count_ProductInCat = count($cat_products);
                    if ($limit == '' || $limit == 0) {
                        $limit = GENERAL_PAGINATION_LIMIT;
                    }

                    if ($pageno == 0 || $pageno == 1) {
                        $start_to = 0;
                    } else {
                        if ($limit == 1) {
                            $start_to = ($pageno - 1);
                        } else {
                            $start_to = $limit * ($pageno - 1);
                        }

                        if ($start_to > $count_ProductInCat) {
                            $start_to = $count_ProductInCat;
                        }
                        $limit = ($start_to + $limit);

                    }

                    for ($j = $start_to; $j < $limit; $j++) {

                        if ($pageno > 1) {
                            $cnt = $j;
                        } else {
                            $cnt = 0;
                        }

                        if ($limit > $count_ProductInCat) {
                            $start_to = $j;
                            $limit = $count_ProductInCat;
                            //$products['code'] = 2;
                            //return $jsonRes;
                        }

                        for ($i = $j; $i < $limit; $i++) {
                            $products['products'][$cnt]['refId'] = $cat_products[$cnt]->virtuemart_product_id;
                            $products['products'][$cnt]['title'] = $cat_products[$cnt]->product_name;
                            $products['products'][$cnt]['description'] = strip_tags($cat_products[$cnt]->product_s_desc);

                            //$this->default_image = JURI::ROOT().'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'vmgeneral'.DS.'noimage.gif';
                            if (isset($cat_products[$cnt]->virtuemart_media_id[0])) {
                                $media_id = $cat_products[$cnt]->virtuemart_media_id[0];
                                if (!empty($media_id)) {
                                    $query = "SELECT * FROM #__virtuemart_medias where `virtuemart_media_id`= " . $media_id;
                                    $this->db->setQuery($query);
                                    $res_images = $this->db->loadObject();
                                    if (!empty($res_images)) {
                                        $product_full = $this->siteUrl() . $res_images->file_url;
                                        $product_thumbs = $this->siteUrl() . $res_images->file_url_thumb;

                                        $check_full = JPATH_SITE . DS . $res_images->file_url;
                                        $check_thumb1 = JPATH_SITE . DS . $res_images->file_url_thumb;

                                        if (file_exists($check_thumb1)) {
                                            $products['products'][$cnt]['product_thumb_image'] = $product_thumbs;
                                        } else {
                                            $products['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                        }

                                        if (file_exists($check_full)) {
                                            $products['products'][$cnt]['iconUrl'] = $product_full;

                                        } else {
                                            $products['products'][$cnt]['iconUrl'] = $this->default_image;

                                        }

                                        //A0-F3-C1-68-EB-99
                                    } else {
                                        $products['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                        $products['products'][$cnt]['iconUrl'] = $this->default_image;
                                    }
                                } else {
                                    $products['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                    $products['products'][$cnt]['iconUrl'] = $this->default_image;

                                }
                            } else {
                                $products['products'][$cnt]['product_thumb_image'] = $this->default_image;
                                $products['products'][$cnt]['iconUrl'] = $this->default_image;
                            }

                            $currency_symbol = $this->currency->getSymbol();
                            $products['products'][$cnt]['currency_symbol'] = $currency_symbol;
                            //
                            // rating, no of sub product
                            //if(GENERAL_PRICE == 1){
                            //$this->log('prices!');
                            $grossValue = $this->currency->priceDisplay($cat_products[$cnt]->prices['salesPrice']);
                            $products['products'][$cnt]['price'] = $grossValue;

                            $products['products'][$cnt]['rawPrice'] = $cat_products[$cnt]->prices['salesPrice'];

                            //}
                            $products['products'][$cnt]['inStock'] = 'In Stock : ' . $cat_products[$cnt]->product_in_stock;
                            // Used For Display Votes
                            $products_votes = $this->ratingModel->getRatingByProduct($cat_products[$cnt]->virtuemart_product_id);
                            //echo "<pre/>"; print_r($products_votes); exit;
                            if (!empty($products_votes)) {
                                $products['products'][$cnt]['rating'] = $products_votes->rating;
                                $products['products'][$cnt]['ratings'] = $products_votes->ratingcount;
                            }
                            // Used For Display child Products

                            //$child_products = $this->productModel->getProductChilds($cat_products[$cnt]->virtuemart_product_id);
                            //echo "<pre/>"; print_r($child_products); exit;
                            /*
                            if($child_products)
                            {
                                $products['products'][$cnt]['no_sub_pro'] = count($child_products);
                            } else {
                                $products['products'][$cnt]['no_sub_pro'] = "";
                            }
                            */
                            $cnt++;
                        }
                    }

                }
            } else {
                $products['code'] = 2;
                $products['invalid_data'] = "Our Shop is currently down for maintenance. Please check back again soon.";
            }
        } else {
            $products['code'] = 2;

        }
        //$dall = print_r($products['products'],true);
        //$this->log($dall);
        $this->dumpObject($products['products']);
        //xapp_print_memory_stats('xapp-connect-vmart-before schema filter');
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $products['products']);
        //$this->dumpObject($products['products'],'getProducts');
        //$this->dumpObject($res,'getProducts result');
        //$this->dumpObject($this->xcOptions,'xc options');
        //error_log(json_encode($this->xcType));

        $this->onAfterCall($res);
        //xapp_print_memory_stats('xapp-connect-vmart-after schema filter');
        return $res;
        //return $products;
    }

    /***
     * @param string $params
     * @return string
     */

    public function getCategoryDetail($params = "{}")
    {
        $this->onBeforeCall($params);
        $jsonRes = array();
        if ($this->shop_is_offline == 0) {
            //public function getCategory($virtuemart_category_id=0,$childs=TRUE){
            $func_category = $this->class_category->getCategory($this->xcRefId, false);
            if ($func_category) {
                $jsonRes[0]['refId'] = $func_category->virtuemart_category_id;
                $jsonRes[0]['title'] = $func_category->category_name;
                $jsonRes[0]['published'] = $func_category->published;
                $jsonRes[0]['groupId'] = $func_category->category_parent_id;
                $jsonRes[0]['introText'] = $func_category->category_description;
                //$this->log('cat description : '.$jsonRes[0]['introText']);
                $jsonRes[0]['shareUrl'] = $this->siteUrl().'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$func_category->virtuemart_category_id;
                return $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $jsonRes);
            }
        }
        return "{}";
    }

    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getCategories($params = "{}")
    {

        $this->onBeforeCall($params);
        $jsonRes = array();
        $limit = 100;
        $pageno = 0;
        $jsonRes['code'] = 1;
        if ($this->shop_is_offline == 0) {
            $func_category = $this->class_category->getCategories($onlyPublished = true, $parentId = $this->xcRefId, $childId = false, $keyword = "");
            if ($func_category) {
                $count_cat = count($func_category);

                if ($limit == '' || $limit == 0) {
                    $limit = GENERAL_PAGINATION_LIMIT;
                }

                if ($pageno == 0 || $pageno == 1) {
                    $start_to = 0;
                } else {
                    if ($limit == 1) {
                        $start_to = ($pageno - 1);
                    } else {
                        $start_to = $limit * ($pageno - 1);
                    }

                    if ($start_to > $count_cat) {
                        $start_to = $count_cat;
                    }
                    $limit = ($start_to + $limit);

                }
                for ($j = $start_to; $j < $limit; $j++) {
                    //$this->log('inner loop0');

                    if ($pageno > 1) {
                        $cnt = $j;
                    } else {
                        $cnt = 0;
                    }

                    if ($limit > $count_cat) {
                        $start_to = $j;
                        $limit = $count_cat;
                    }
                    for ($i = $j; $i < $limit; $i++) {
                        //$jsonRes['categories']['total_categories'] = $count_cat;

                        $jsonRes['categories'][$cnt]['refId'] = $func_category[$cnt]->virtuemart_category_id;
                        $jsonRes['categories'][$cnt]['title'] = $func_category[$cnt]->category_name;
                        $jsonRes['categories'][$cnt]['published'] = $func_category[$cnt]->published;
                        $jsonRes['categories'][$cnt]['groupId'] = $func_category[$cnt]->category_parent_id;
                        $jsonRes['categories'][$cnt]['description'] = strip_tags($func_category[$cnt]->category_description);


                        $query_mediaId = "SELECT virtuemart_media_id FROM #__virtuemart_category_medias
														WHERE virtuemart_category_id='" . $func_category[$cnt]->virtuemart_category_id . "' ";
                        $this->db->setQuery($query_mediaId);
                        $res_mediaId = $this->db->loadResult();
                        if (isset($res_mediaId) && $res_mediaId > 0) {
                            $query_imagepath = "SELECT virtuemart_media_id,file_url,file_url_thumb FROM #__virtuemart_medias
															WHERE virtuemart_media_id='" . $res_mediaId . "' ";
                            $this->db->setQuery($query_imagepath);
                            $res_imagepath = $this->db->loadObject();

                            //echo "<pre/>"; print_r($res_imagepath); exit;
                            if (!empty($res_imagepath)) {
                                $check_filefull = JPATH_SITE . DS . $res_imagepath->file_url;
                                $check_filethumb = JPATH_SITE . DS . $res_imagepath->file_url_thumb;

                                // Url of full and thumb image path
                                $category_thumbimage = $this->siteUrl() . $res_imagepath->file_url_thumb;
                                $category_fullimage = $this->siteUrl() . $res_imagepath->file_url;

                                if (file_exists($check_filethumb)) {
                                    $jsonRes['categories'][$cnt]['iconUrl'] = $category_thumbimage;
                                } else {
                                    $jsonRes['categories'][$cnt]['iconUrl'] = $this->default_image;
                                }

                                if (file_exists($check_filefull)) {
                                    $jsonRes['categories'][$cnt]['category_full_image'] = $category_fullimage;
                                } else {
                                    $jsonRes['categories'][$cnt]['category_full_image'] = $this->default_image;
                                }

                            } else {
                                $jsonRes['categories'][$cnt]['category_thumb_image'] = $this->default_image;
                                $jsonRes['categories'][$cnt]['category_full_image'] = $this->default_image;
                            }
                        } else {
                            $jsonRes['categories'][$cnt]['category_thumb_image'] = $this->default_image;
                            $jsonRes['categories'][$cnt]['category_full_image'] = $this->default_image;
                        }

                        $count_product = $this->class_category->countProducts($func_category[$cnt]->virtuemart_category_id);

                        $vendorId = 1;
                        $count_subcategory = $this->class_category->getChildCategoryList($vendorId, $func_category[$cnt]->virtuemart_category_id);


                        if ($count_subcategory) {
                            $cnt_subcat = count($count_subcategory);
                            $jsonRes['categories'][$cnt]['no_sub_cat'] = $cnt_subcat;
                            $jsonRes['categories'][$cnt]['numberOfItems'] = $count_product . ' Items' . ', ' . $cnt_subcat . ' Categories';
                        } else {
                            /*$jsonRes['categories'][$cnt]['no_sub_cat'] = "0";*/
                            $jsonRes['categories'][$cnt]['numberOfItems'] = $count_product . ' Items';
                        }
                        //$this->log('cat_picture : ' . $jsonRes['categories'][$cnt]['iconUrl']);


                        //find
                        if(!$jsonRes['categories'][$cnt]['iconUrl'] || strlen($jsonRes['categories'][$cnt]['iconUrl'])==0){
                            //$this->log('have no picture');

                            //$this->log('cat_descr : ' . $func_category[$cnt]->category_description);
                            $iconUrl = xapp_findPicture($func_category[$cnt]->category_description);
                            if($iconUrl){
                                $jsonRes['categories'][$cnt]['iconUrl']=$this->completeUrl($iconUrl);
                            }
                            //$this->log('found pic : ' . $iconUrl);
                        }



                        $cnt++;
                    }
                }
            }
        } else {

            $jsonRes['code'] = 2;
            $jsonRes['invalid_data'] = "Our Shop is currently down for maintenance. Please check back again soon.";
        }


        //$this->dumpObject($jsonRes['categories']);
        //xapp_print_memory_stats('xapp-connect-vmart-before schema filter');
        $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $jsonRes['categories']);
        //xapp_print_memory_stats('xapp-connect-vmart-after schema filter');
        //$this->dumpObject($res);

        //on after call will trigger index
        $this->onAfterCall($res);
        return $res;

    }

    /***
     *
     * @return integer
     */
    function load()
    {
        parent::load();
        xapp_hide_errors();
        $vmartTestFile = JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php';
        if (file_exists($vmartTestFile)) {
            require_once($vmartTestFile);
        } else {
            return false;
        }


        /***
         * Common variables
         */
        $this->vm_langdb1 = VmConfig::get('vmlang');
        $this->shop_is_offline = VmConfig::get('shop_is_offline');

        //used for display Full and Thumb image
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'tables' . DS . 'medias.php');
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'media.php');
        $this->class_media = new VirtueMartModelMedia();


        // Add model of product
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'tables' . DS . 'products.php');
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'product.php');
        $this->productModel = new VirtueMartModelProduct();

        // Used For category model
        //require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmmodel.php');
        //require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmtable.php');
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'tables' . DS . 'categories.php');
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'category.php');
        $this->class_category = new VirtueMartModelCategory();

        $this->db = & JFactory::getDBO();
        if (!class_exists('CurrencyDisplay')) {
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
        }
        $this->currency = CurrencyDisplay::getInstance();

        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'ratings.php');
        $this->ratingModel = new VirtueMartModelRatings();

        $this->default_image = JURI::ROOT() . 'components' . DS . 'com_virtuemart' . DS . 'assets' . DS . 'images' . DS . 'vmgeneral' . DS . 'noimage.gif';
        return true;
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