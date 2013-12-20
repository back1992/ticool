<?php
/**
 * @version 0.1.0
 * @package Cache
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class SimpleCache {

    //Path to cache folder (with trailing /)
    var $cache_path = 'cache/';
    //Length of time to cache a file in seconds
    var $cache_time = 1000;

    //This is just a functionality wrapper function
    function get_data($label, $url)
    {
        if(!isset($url)){
            return null;
        }
        if($data = $this->get_cache($label)){
            return $data;
        } else {
            $data = $this->do_curl($url);
            $this->set_cache($label, $data);
            return $data;
        }
    }

    function set_cache($label, $data)
    {

        if(file_put_contents($this->cache_path . $this->safe_filename($label) .'.cache', $data)==false){
            error_log('caching of : '. $label . ' failed in ' . $this->cache_path . ' data ');
            return false;
        }else{
          //  error_log('caching of : '. $label . ' success in ' . $this->cache_path);
        }
        return true;
    }

    function get_cache($label)
    {
        if($this->is_cached($label)){
            $filename = $this->cache_path . $this->safe_filename($label) .'.cache';
            return file_get_contents($filename);
        }

        return null;
    }

    function flush_cache($label)
    {
        try{
            if($this->is_cached($label)){
                $filename = $this->cache_path . $this->safe_filename($label) .'.cache';
                if(file_exists($filename)){
                    unlink($filename);
                }
            }
        }catch (Exception $e){

        }
    }

    function is_cached($label)
    {
        $filename = $this->cache_path . $this->safe_filename($label) .'.cache';
        if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;
        return false;
    }

    //Helper function for retrieving data from url
    function do_curl($url)
    {
        if(function_exists("curl_init")){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            $content = curl_exec($ch);
            curl_close($ch);
            return $content;
        } else {
            return file_get_contents($url);
        }
    }

    //Helper function to validate filenames
    function safe_filename($filename)
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}