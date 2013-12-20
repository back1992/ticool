<?php
/**
 * @package HTML
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * private function used by xapp_sanitize_html to remove or modify each html tag.
 * @return filtered tag
 */
function xapp_filter_tag($opening, $name, $attr, $closing) {

    $allow_tags = array('se-attachment','seattachment','se:attachment','attachment','a','blockquote','h1','h2','h3','h4','h5',
        'p','br','b','strong','em','i','a','ul','li','ol');
    if(!in_array($name, $allow_tags)) {
        return '';
    }

    $filtered_attr = '';

    if (strcmp($name,'attachment') == 0) {
        $filtered_attr = $attr;
    } else if (strcmp($name,'se-attachment') == 0) {
        $filtered_attr = $attr;
    } else if (strcmp($name,'img') == 0) {
        $filtered_attr = xapp_get_sanitized_attr('src',$attr);
        //error_log('$$$$$$$$$$   have image : ' . $name . ' att ' . $attr . ' filtered :: ' .$filtered_attr);

    } else if (strcmp($name,'a') == 0) {
        $filtered_attr = xapp_get_sanitized_attr('href',$attr);
    }

    $tag = '<'.$opening.$name.$filtered_attr.$closing.'>';
    $tag = str_replace("\\\"", "\"", $tag);
    return $tag;
}

function xapp_sanitize_html($html, &$attachments = null) {
    //NextGen gallery plugin fix. Sanitize images included inside of dl tags before image strip command.
    $forbiden_elements = "/<(dl).*?>.*?<\/(\\1)>/si";
    $filtered_html = preg_replace($forbiden_elements, "",$html);

    //remove comments
    $filtered_html = preg_replace("/<!--(.*?)-->/si", "",$filtered_html);

    if (isset($attachments)) {
        //$attachments = strip_attachments(&$filtered_html);
    }

    $forbiden_elements = "/<(style|script|iframe|object|embed|dl).*?>.*?<\/(\\1)>/si";
    $filtered_html = preg_replace($forbiden_elements, "",$filtered_html);


    //Limited support for tables: each table row starts from a new line
    $filtered_html = preg_replace("(</tr.*?>)", "<br/>",$filtered_html);

    //first try wp_kses for removal of html elements
    if (function_exists('wp_kses')) {
        $allowed_html = array(
            'attachment' => array('id'=>true,'type'=>true,'xmlns'=>true),
            'a' => array('href'=>true,'style'=>true),
            'blockquote' => array(),
            'h1' => array(),
            'img' => array('src'=>true,'width'=>true,'height'=>true,'style'=>true,'oriWidth'=>true),
            'h2' => array(),
            'div' => array('class'=>true,'style'=>true),
            'span' => array('class'=>true,'style'=>true,'onClick'=>true),
            'h3' => array('style'=>true),
            'h4' => array('style'=>true),
            'h5' => array('style'=>true),
            'p' => array('class'=>true),
            'br' => array(),
            'hr' => array(),
            'b' => array(),
            'strong' => array(),
            'em' => array(),
            'i' => array(),
            'ul' => array(),
            'li' => array(),
            'ol' => array()
        );
        $filtered_html = wp_kses($filtered_html, $allowed_html);
    } else {
        $all_tags = "/<(\/)?\s*([\w-_]+)(.*?)(\/)?>/ie";
        $filtered_html = preg_replace($all_tags, "xapp_filter_tag('\\1','\\2','\\3','\\4')",$filtered_html);
    }

    /*
      * This is needed because wp_kses always removes 'se-attachment' or 'se:attachment' tag regardles of $allowed_html parameter.
      * To circumvent this, strip_attacments inserts <seattachment id=''/> instead of<se-attachment .../> into html.
      * Here, seattachment label is replaced with the proper label
      */
    $filtered_html = preg_replace("/xmlns=\"v1\"(\s)*\/>/i","xmlns=\"urn:xmlns:shoutem-com:cms:v1\"></attachment>",$filtered_html);
    return $filtered_html;
}



function xapp_is_attr_forbidden($attr) {
    if (preg_match("/\s*javascript.*/i",$attr['value']) > 0) {
        return true;
    }
    return false;
}

function xapp_get_attr($name, $string) {
    $attr = false;
    //$string = str_replace("\\\"","\"",$string);
    $match_rule = "/\s*(".$name."=\s*([\"']).*?(\\2))/i";

    if (preg_match("/\s*(".$name."=([\"'])(.*?)(\\2))/i", $string, $matches) > 0) {
        $quote = $matches[2];
        $value = $matches[3];
        $attr['value'] = $value;
        $attr['name'] = $name;
        $attr['html_attr'] = ' '.$name.'='.$quote.$value.$quote;
    }
    return $attr;
}

function xapp_get_sanitized_attr($name, $string) {
    $tagAttr = xapp_get_attr($name,$string);
    if ($tagAttr && !xapp_is_attr_forbidden($tagAttr)) {
        return $tagAttr['html_attr'];
    }
    return '';
}

?>