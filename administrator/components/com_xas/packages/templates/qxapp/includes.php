<?php

//set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/lib/');

/***
 * Simple Cache
 */
include_once XAPP_LIB . "cache/SimpleCache.php";
include_once XAPP_LIB . "cache/CacheFactory.php";
/***
 * Utils
 */
require_once (XAPP_LIB .'utils/StringUtils.php');

/**
 * CType Utils
 */
require_once (XAPP_LIB .'ctypes/CustomTypesUtils.php');

/**
 * XApp-Core
 */
require_once (XAPP_LIB.'/rpc/lib/vendor/autoload.php');
require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Core/core.php');













