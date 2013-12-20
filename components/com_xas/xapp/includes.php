<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Main
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/lib/');

/**
 * RPC Service Class
 */
require_once(XAPP_BASEDIR . "SQL2JSONService.php");

/***
 * Maps global functions to service class
 */
require_once(XAPP_BASEDIR . "serviceRouter.php");
/**
 * Database includes
 */
include_once XAPP_LIB . "db/db.php";
/***
 * Simple Cache
 */
include_once XAPP_LIB . "cache/SimpleCache.php";


/***
 * Simple Cache Factory : @TODO : replace with new xapp-php cache, maybe
 */
include_once XAPP_LIB . "cache/CacheFactory.php";

/***
 * New : HTML Parser
 */
require_once (XAPP_LIB .'html/ganon.php');

/***
 * HTML Processor
 */
require_once (XAPP_LIB .'html/HTMLFilter.php');


/***
 * Utils
 */
require_once (XAPP_LIB .'utils/StringUtils.php');
require_once (XAPP_LIB .'utils/JSONUtils.php');

require_once (XAPP_BASEDIR.'connect/html/Mixin.php');

/**
 * Diff Utils
 */
/*
require_once (XAPP_LIB .'utils/diff_match_patch.php');
*/

/**
 * JSON Store
 */
require_once (XAPP_LIB .'json/JSON.php');
/**
 * JSON Store
 */
require_once (XAPP_LIB .'json/jsonstore.php');
/**
 * JSON PATH
 */
require_once (XAPP_LIB .'json/jsonpath-0.8.1.php');

/**
 * CType Utils
 */
require_once (XAPP_LIB .'ctypes/CustomTypesUtils.php');


/***
 * Template Engine
 */
/*include (XAPP_BASEDIR.'TplNew.php');*/

/***
 * Custom RPC error
 */
include (XAPP_BASEDIR.'RpcError.php');

/***
 * Functions for being used in schema templates
 */
include (XAPP_BASEDIR.'SchemaFuncs.php');

/***
 * Functions for being used in schema templates
 */
include (XAPP_BASEDIR.'SchemaFuncsJoomla.php');

/***
 * Functions for being used in schema templates
 */
include (XAPP_BASEDIR.'SchemaFuncsDate.php');


/**
 * The schema processor
 */
require_once(XAPP_BASEDIR . "SchemaProcessor.php");

/**
 * The schema processor interface
 */
require_once(XAPP_BASEDIR . "ISchemaProcessor.php");

/**
 * The XApp-Connect constants
 */
require_once(XAPP_BASEDIR . "connect/defines.php");

require_once(XAPP_BASEDIR . "connect/utils/Debugging.php");

/***
 * XApp-PHP related
 */
require_once (XAPP_LIB.'/rpc/lib/vendor/autoload.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Core/core.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Cache.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Driver.php');

/***
 * Import JSON-Store classes from 'xapp/Util'
 */

require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Storage.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Std.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Query.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Store.php');


require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Json.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Query.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Store.php');






