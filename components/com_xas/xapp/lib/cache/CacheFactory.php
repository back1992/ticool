<?php
/**
 * @version 0.1.0
 * @package Cache
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

class CacheFactory {

    public  static function createDefaultCache(){
        $ctuCache = new SimpleCache();
        $ctuCache->cache_path=XAPP_BASEDIR . 'cache/';
        return $ctuCache;
    }
}