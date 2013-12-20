<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Utils
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

class CIUtils {

    public static function getCIStringValue($ctype,$name){
        if(!$ctype){
            return null;
        }

        $inputs = null;

        //weird
        if(is_object($ctype) && isset($ctype->inputs)){
            $inputs=$ctype->inputs;
        }else if(is_array($ctype) && is_array($ctype['inputs'])){
            $inputs=$ctype['inputs'];
        }

        //still very weird
        foreach($inputs as $ci) {
            if(is_array($ci)){
                if($ci['name']===$name){
                    return $ci['value'];
                }
            }else if(is_object($ci)){
                if($ci->name===$name){
                    return $ci->value;
                }
            }
        }
        return null;
    }
}