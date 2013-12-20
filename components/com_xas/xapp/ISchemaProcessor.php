<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\SchemaOld
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Class ISchemaProcessor is a interface for the actual SQL and schema processor.
 *
 * + It does caching
 * + Does executes SQL
 * + Does security checks !
 */

class ISchemaProcessor {

    public static function templatedQuery($queries,$schemas,$options)
    {
        $srv = new SQL2JSONService();


        //$srv->setWhiteList(SQLConfig::$sqlWhiteList);
        //$srv->setBlackList(SQLConfig::$sqlBlackList);

        /***
         * Security : check schemas against local authorized CustomType::Schemas definition
         */
        if( (bool)xc_conf(XC_CONF_CHECK_SCHEMA))
        {
            //error_log('check schema '. (bool)xc_conf(XC_CONF_CHECK_SCHEMA));
            if(!$options->vars->RT_CONFIG||
                !$options->vars->CTYPE)
            {
                error_log('couldn`t verify schema, parameters missing > ctype : ' . $options->vars->CTYPE . ' rtc : ' . $options->vars->RT_CONFIG );
                return false;
            }


            //refresh local copy of custom types.
            $cTypeCached = CustomTypesUtils::getTypeFromCache($options->vars->CTYPE,$options->vars->UUID,$options->vars->APPID,'IPHONE_NATIVE',$options->vars->RT_CONFIG);
            if($cTypeCached==null){
                CustomTypesUtils::getCTypesFromUrl($options->vars->SERVICE_HOST,$options->vars->UUID,$options->vars->APPID,'IPHONE_NATIVE',$options->vars->RT_CONFIG);
            }

            //now get the custom type's schemas and verify with the user's requested schema
            $_schemaStr = json_encode($schemas);
            $ctype = CustomTypesUtils::getType($options->vars->CTYPE);
            if($ctype){
                $_schemaStr = str_replace('\/','/',$_schemaStr);
                $ctypeSchemas = CustomTypesUtils::getCIStringValue($ctype,'schemas');
                if(!$ctypeSchemas){
                    $ctypeSchemas='';
                }

                //cmp and abort
                $comp = strcmp($ctypeSchemas,$_schemaStr);
                if($comp > 10){
                    error_log('schema doesnt match ' . $options->vars->CTYPE . ' diff : ' . $comp);
                    return '';
                }
            }else{
                error_log('couldn`t verify schema, cType missing');
                return false;
            }
        }
        $result =  $srv->templatedQuery($queries,$schemas,$options);
        /**
         * Replaces user variables in the final output.
         */
        $replaceVars = true;
        if($replaceVars){

            if($options!=null){
                $_keys = array();
                $_values = array();
                foreach ($options->vars as $key => $value)
                {
                    array_push($_keys,$key);
                    array_push($_values,$value);
                }
                $result = str_replace(
                    $_keys,
                    $_values,
                    $result
                );
            }
        }
        return $result;

    }
}