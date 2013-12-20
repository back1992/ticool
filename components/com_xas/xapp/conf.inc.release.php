<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

$xappConnectServiceConf=array(
    XC_CONF_LOG_QUERIES         =>false,
    XC_CONF_LOG_MYSQL_RESULTS   =>false,
    XC_CONF_LOG_REQUEST_OPTIONS =>false,
    XC_CONF_LOG_JSON_ERRORS     =>false,
    XC_CONF_JOOMLA              =>true,
    XC_CONF_WORDPRESS           =>false,
    XC_CONF_RESPONSE_CACHE      =>false,
    XC_CONF_LOG_TEMPLATE_VARS   =>false,
    XC_CONF_CHECK_SCHEMA        =>true,
    XC_CONF_CHECK_SERVICE_HOST  =>true,
    XC_CONF_CACHE_PATH          =>XAPP_BASEDIR .'/cache/',
    XC_CONF_LOGGER              =>null,
    XC_CONF_SERVICE_HOST        =>"http://www.xapp-studio.com/XApp-portlet/",
    XC_CONF_ALLOW_REMOTE_SCHEMA =>false,
    XC_CONF_ALLOW_JSONP         =>true,
    XC_CONF_HAS_LUCENE => false,
    XC_CONF_ALLOW_REMOTE_SCHEMA =>false,
    XC_CONF_ALLOW_JSONP         =>true,
    XC_CONF_HAS_CMS_AUTH        =>true
);
xc_conf($xappConnectServiceConf);
