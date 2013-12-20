<?php
function rmcomments($id) {
    if (file_exists($id)) {
        if (is_dir($id)) {
            $handle = opendir($id);
            while($file = readdir($handle)) {
                if (($file != ".") && ($file != "..")) {
                    rmcomments($id."/".$file); }}
            closedir($handle); }
        else if ((is_file($id)) && (end(explode('.', $id)) == "php")) {
            if (!is_writable($id)) { chmod($id,0777); }
            if (is_writable($id)) {
                $fileStr = file_get_contents($id);
                $newStr  = '';
                $commentTokens = array(T_COMMENT);
                if (defined('T_DOC_COMMENT')) { $commentTokens[] = T_DOC_COMMENT; }
                if (defined('T_ML_COMMENT')) { $commentTokens[] = T_ML_COMMENT; }
                $tokens = token_get_all($fileStr);
                foreach ($tokens as $token) {
                    if (is_array($token)) {
                        if (in_array($token[0], $commentTokens)) { continue; }
                        $token = $token[1]; }
                    $newStr .= $token; }
                if (!file_put_contents($id,$newStr)) {
                    $open = fopen($id,"w");
                    fwrite($open,$newStr);
                    fclose($open); }}}}}

rmcomments("/PMaster/PHPStorm/xas-joomla/DIST");
