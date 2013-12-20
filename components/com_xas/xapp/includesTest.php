<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @package XApp-Connect\Main
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/lib/');

/**
 * RPC Service Class
 */
echo "      include SQL Service<br/>";
require_once(XAPP_BASEDIR . "SQL2JSONService.php");

/***
 * Maps global functions to service class
 */
echo "      include Service Router<br/>";
require_once(XAPP_BASEDIR . "serviceRouter.php");
/**
 * Database includes
 */
echo "      include db.php<br/>";
include_once XAPP_LIB . "db/db.php";
/***
 * Simple Cache
 */
echo "      include simple cache<br/>";
include_once XAPP_LIB . "cache/SimpleCache.php";

/***
 * Simple Cache
 */
echo "      include cache factory<br/>";
include_once XAPP_LIB . "cache/CacheFactory.php";
/***
 * HTML Processor
 */
echo "      include html filter <br/>";
require_once (XAPP_LIB .'html/HTMLFilter.php');

/***
 * Utils
 */
echo "      include strinrg utils <br/>";
require_once (XAPP_LIB .'utils/StringUtils.php');
echo "      include json utils<br/>";
require_once (XAPP_LIB .'utils/JSONUtils.php');

/**
 * Diff Utils
 */
/*
require_once (XAPP_LIB .'utils/diff_match_patch.php');
*/

/**
 * JSON Store
 */
echo "      include json<br/>";
require_once (XAPP_LIB .'json/JSON.php');
/**
 * JSON Store
 */
echo "      include json store<br/>";
require_once (XAPP_LIB .'json/jsonstore.php');
/**
 * JSON PATH
 */
echo "      include json path<br/>";
require_once (XAPP_LIB .'json/jsonpath-0.8.1.php');

/**
 * CType Utils
 */
echo "      include custom type utils<br/>";
require_once (XAPP_LIB .'ctypes/CustomTypesUtils.php');


/***
 * Template Engine
 */
echo "      include tpl <br/>";
include (XAPP_BASEDIR.'TplNew.php');

/***
 * Template Engine
 */
echo "      include rpc error<br/>";
include (XAPP_BASEDIR.'RpcError.php');


/***
 * Functions for being used in schema templates
 */
echo "      include schema funcs<br/>";
include (XAPP_BASEDIR.'SchemaFuncs.php');

/***
 * Functions for being used in schema templates
 */
echo "      include schema funcs<br/>";
include (XAPP_BASEDIR.'SchemaFuncsJoomla.php');

/***
 * Functions for being used in schema templates
 */
echo "      include schmema funcs date <br/>";
include (XAPP_BASEDIR.'SchemaFuncsDate.php');


/***
 * ORM
 */
/**
 * The schema processor
 */
echo "      include schema processor<br/>";
require_once(XAPP_BASEDIR . "SchemaProcessor.php");

/**
 * The schema processor interface
 */
echo "      include ischema processor<br/>";
require_once(XAPP_BASEDIR . "ISchemaProcessor.php");

/**
 * The XApp-Connect constants
 */
echo "      include defines<br/>";
require_once(XAPP_BASEDIR . "connect/defines.php");

echo "      include debugging<br/>";
require_once(XAPP_BASEDIR . "connect/utils/Debugging.php");

/***
 * XApp-PHP related
 */
echo "      include autoloader<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/autoload.php');
echo "      include xp core<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Core/core.php');
echo "      include xp cache<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Cache.php');
echo "      include xp driver<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Cache/Driver.php');

/***
 * Import JSON-Store classes from 'xapp/Util'
 */

echo "      include xp storage<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Storage.php');
echo "      include xp std:std<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Std.php');
echo "      include xp std:query<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Query.php');
echo "      include xp std:store<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Store.php');

echo "      include xp json:json<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Json.php');
echo "      include xp json:query<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Query.php');
echo "      include xp json:store<br/>";
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Store.php');






