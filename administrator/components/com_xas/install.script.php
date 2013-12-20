<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined('_JEXEC' ) or die('Restricted access' );

if(!class_exists('XASInstaller')){
    include_once dirname(__FILE__).'/classes/xasinstaller.php';
}

if(!class_exists('Com_XASInstallerScript')){

class Com_XASInstallerScript
{
	/**
	 * @param string $type
	 * @param JInstallerComponent $adapter
	 */

	function preflight($type, $adapter)
	{

        $path = $adapter->getParent()->getPath('source');
        $xmlsrc = $path.'/xas.j2x.xml';
        $xmldest = $path.'/xas.xml';
        if(version_compare(JVERSION, '3.0', '>=')){
            if(JFile::exists($xmlsrc))
            {
                if(JFile::exists($xmldest))
                    JFile::delete($xmldest);
                JFile::move($xmlsrc, $xmldest);
            }
        }else{

            if(JFile::exists($xmlsrc))
                JFile::delete($xmlsrc);

        }
		$adapter->getParent()->setPath('manifest', $xmldest);
	}

	/**
	 * @param JInstallerComponent $adapter
	 * @return bool
	 */
	function install($adapter)
	{
		return XASInstaller::install();
	}
	/**
	 * @param JInstallerComponent $adapter
	 * @return bool
	 */
	function update($adapter)
	{
        //JFactory::getApplication()->enqueueMessage('update');
        return XASInstaller::install();
	}

	/**
	 * @param JInstallerComponent $adapter
	 * @return bool
	 */
	function uninstall($adapter)
	{
		return XASInstaller::uninstall();
	}
}
}