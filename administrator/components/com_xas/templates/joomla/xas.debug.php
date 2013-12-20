<?php

/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

/***
 * Important variables :
 */
global $XAPP_ADMIN_ROOT;
global $XAPP_ROOT;

$componentPrefix = "./components/com_xas/";
$docRootPrefix = $componentPrefix ."client/";
$xthemePrefix = "xasthemes/";

$appCSSPrefix = $docRootPrefix. "css/";
$appBasePrefix = $docRootPrefix . "lib/";
$servicePrefix = $docRootPrefix . "../server/service/";


$document->addStyleSheet($docRootPrefix.$xthemePrefix."/claro/document.css");
$document->addStyleSheet($docRootPrefix.$xthemePrefix."/claro/claro.css");




$document->addStyleSheet($appBasePrefix."/dojox/widget/Wizard/Wizard.css");
$document->addStyleSheet($appBasePrefix."/dojox/layout/resources/ExpandoPane.css");
$document->addStyleSheet($appBasePrefix."/dojox/layout/resources/ToggleSplitter.css");

$document->addScript($appBasePrefix ."external/klass.min.js");
$document->addScript($appBasePrefix ."external/jshashtable.min.js");
//$document->addScript($appBasePrefix ."external/external/qr/qrcode.js");
$document->addScript($appBasePrefix ."external/log4javascript.js");
$document->addScript($appBasePrefix ."external/stacktrace.js");

/*
    <script type="text/javascript" charset="utf-8" src="$appBasePrefix/external/klass.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="$appBasePrefix/external/jshashtable.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="$appBasePrefix/external/qr/qrcode.js"></script>
    <script type="text/javascript" charset="utf-8" src="$appBasePrefix/external/log4javascript.js"></script>
    <script type="text/javascript" charset="utf-8" src="$appBasePrefix/external/stacktrace.js"></script>
*/

    ///$document->addStyleSheet($appCSSPrefix."xjoomla/xjoomla.css");
    $document->addStyleSheet($appCSSPrefix."xjoomla/xjoomlaAdmin.css");
    $document->addStyleSheet($appCSSPrefix."Widgets.css");
    $document->addStyleSheet($appCSSPrefix."AppStudio.css");
    $document->addStyleSheet($appCSSPrefix."xappwebfix.css");
    $document->addStyleSheet($appCSSPrefix."xasCommons.css");
    $document->addStyleSheet($appCSSPrefix."xasdijitOverride.css");
    //$document->addStyleSheet($appCSSPrefix."xasFonts.css");

    //$document->addScriptDeclaration($scriptTag);
?>

<div id="main" data-dojo-type="dijit/layout/BorderContainer" data-dojo-props='design:"headline", liveSplitters:false,
		style:"border: 0px solid black;"'>

    <div lang="en" data-dojo-type="dijit.layout.AccordionContainer" data-dojo-props="region:'leading', splitter:true, minSize:20" style="padding-left:0px;width: 320px;" id="settingsRoot" tabIndex="-1" splitter="true" toggleSplitterCollapsedSize="20px" region="left" toggleSplitterState="full"></div>

    <!--div id="left" class="filterOptions" data-dojo-type="dijit/layout/ContentPane" data-dojo-props='minSize:100,splitter:true,region:"leading", style:"background-color: #acb386;"'>

    </div-->

    <!--div id="top" role="banner" data-dojo-type="dijit/layout/ContentPane" data-dojo-props='minSize:100, region:"top", style:"box-sizing: border-box;background-color: #b39b86; border: 0px black solid; height: 50px;box-sizing: border-box;", splitter:true'></div-->

    <div lang="en" class="topMainTabs" data-dojo-type="dijit.layout.TabContainer" data-dojo-props="region:'center', tabStrip:true" id="topTabs" style="padding-left:0px;min-width:400px;width:55%;height:600px" splitter="true">

        <div data-dojo-type="dijit/layout/ContentPane" data-dojo-props='minSize:400,splitter:true, region:"center",style:"background-color: #fff; padding:8px;"' title="Welcome">
            <div style="float: left">
                <br />

                <h1>Important :</h1>
                <ul>
                    <li>1. Make /components/com_xas/xapp/cache writable (777) !</li>
                    <li>2. If changes in content is not displayed early enough, please delete all files in /components/com_xas/xapp/cache</li>
                    <li>3. If you have trouble, please leave us a message <a href="http://144.76.12.102:8080/osqa">here</a> </li>
                    <li>4. Please always update when we say so ! We added an update-notification for Joomla and you can also checkout what's new <a href="http://www.xapp-studio.com/changes-joomla">here</a> </li>
                    <li>5. <b>Disable</b> the "XApp-Bot" plugin after the installation ! </li>
                </ul>

                <h1>Instructions to enable Quick-XApp to download data</h1>
                <ul>
                    <li>1. Create a dedicated user in your Joomla.</li>
                    <li>2. Create an application with Quick-XApp (Free).</li>
                    <li>3. In Quick-XApp-Studio, register your Joomla with the user details from step 1.</li>
                </ul>

                <h1>Option 1 : Instructions to enable mobile forwarding</h1>
                <ul>
                    <li>1. Enable the plugin XAPP-BOT.</li>
                    <li>2. See more instructions here : <a href="http://www.xapp-studio.com/documentation">here</a> </li>
                    <li>3. Enable in the plugin XAPP-BOT "Mobile Forwarding" but leave "Mobile Display" off.</li>
                    <li>4. Set in the plugin XAPP-BOT your application details. You can get the details in Quick-XApp's publishing tab.</li>
                    <li>5. Set in the plugin XAPP-BOT "Forwarding Url" to "http://www.xapp-studio.com/XApp-portlet/mobileClientBoot.jsp</li>
                </ul>

                <h1>Option 2 : Instructions to enable local mobile display (No Forwarding).</h1>

                <b>Notice : </b>The mobile forwarding plugin will forward mobile clients to our template "qxapp".<br/>

                <ul>
                    <li>1. Enable the plugin XAPP-BOT.</li>
                    <li>2. Enable in the plugin XAPP-BOT "Mobile Display" but leave "Mobile Forwarding" off.</li>
                    <li>3. Set in the "qxapp" template your application details. You can get the details in Quick-XApp's publishing tab.</li>

                </ul>
                <br />

            </div>
        </div>
    </div>


    <!--div data-dojo-type="dijit/layout/ContentPane" data-dojo-props='id:"border2-bottom", region:"bottom", style:"background-color: #b39b86; height: 80px;", splitter:true'></div-->

    <!--div data-dojo-type="dijit/layout/ContentPane" data-dojo-props='id:"right",minSize:100,region:"trailing", style:"background-color: #acb386;box-sizing:border-box;-moz-box-sizing: border-box;", splitter:true'></div-->


</div>

<script>
    var isMaster = true;
    var debug=true;
    var device=null;
    var sctx=null;
    var ctx=null;
    var cctx=null;
    var mctx=null;
    var rtConfig="debug";
    var returnUrl= "";
    var dataHost ="<?php echo $servicePrefix; ?>";

</script>
<!-- Dojo include -->
<script type="text/javascript" src="<?php echo $appBasePrefix; ?>dojo/dojo.js"
        djConfig="parseOnLoad:false,
        baseUrl:'<?php echo $appBasePrefix; ?>',
        tlmSiblingOfDojo: 0,
        isDebug:0,
        useCustomLogger:false,
        mblAlwaysHideAddressBar:false,
        async:true,
        has:{
            'dojo-undef-api': true,
            'dojo-firebug': false
            },locale:'en'">
</script>

<!-- Run main-->
<script type="text/javascript" src="<?php echo $appBasePrefix; ?>xjoomla/run.js"></script>

