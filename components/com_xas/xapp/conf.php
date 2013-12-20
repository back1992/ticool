<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

function xc_conf($mixed = null, $value = 'NIL')
{

    $conf = array
    (
        XC_CONF_LOG_QUERIES               => false,
        XC_CONF_LOG_MYSQL_RESULTS         => false,
        XC_CONF_LOG_REQUEST_OPTIONS       => false,
        XC_CONF_LOG_JSON_ERRORS           => false,
        XC_CONF_JOOMLA                    => false,
        XC_CONF_WORDPRESS                 => false,
        XC_CONF_RESPONSE_CACHE            => false,
        XC_CONF_LOG_TEMPLATE_VARS         => false,
        XC_CONF_CHECK_SCHEMA              => true,
        XC_CONF_CHECK_SERVICE_HOST        => true,
        XC_CONF_CACHE_PATH                => "./",
        XC_CONF_LOGGER                    => null,
        XC_CONF_SERVICE_HOST              => null,
        XC_CONF_ALLOW_REMOTE_SCHEMA       => false,
        XC_CONF_ALLOW_JSONP               => false,
        XC_CONF_HAS_LUCENE                => false,
        XC_CONF_HAS_CMS_AUTH              =>true
    );

    if(!isset($GLOBALS['XC_CONF']))
    {
        $GLOBALS['XC_CONF'] = $conf;
    }
    if($mixed !== null)
    {
        if(is_string($mixed) && $value !== 'NIL')
        {
            $mixed = array($mixed => $value);
        }
        if(is_array($mixed))
        {
            foreach($mixed as $k => $v)
            {
                $k = strtoupper(trim($k));
                if(array_key_exists($k, $conf))
                {
                    $GLOBALS['XC_CONF'][$k] = $v;
                }
            }
            return $GLOBALS['XC_CONF'];
        }
        if(is_string($mixed) && $value === 'NIL')
        {
            return ((array_key_exists($mixed, $GLOBALS['XC_CONF'])) ? $GLOBALS['XC_CONF'][$mixed] : null);
        }
    }
    return $GLOBALS['XC_CONF'];
}