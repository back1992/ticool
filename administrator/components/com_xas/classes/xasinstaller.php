<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

function qxapp_version()
{
	return XASInstaller::qxapp_version();
}
function isJoomla15()
{
	return XASInstaller::isJoomla15();
}

class XASInstaller
{
	static function qxapp_version()
	{
		return '1.0.0';
	}
	static function isJoomla15()
	{
		static $is_joomla15;
		if(!isset($is_joomla15))
			$is_joomla15 = (substr(JVERSION,0,3) == '1.5');
		return $is_joomla15;
	}

	static function getConfig($name, $default=null)
	{
		$config = JFactory::getConfig();
		if(self::isJoomla15())
			return $config->getValue('config.'.$name, $default);
		else
			return $config->get($name, $default);
	}

	static function getExtensionId($type, $name, $group='')
	{
		$db = JFactory::getDBO();
		if(!self::isJoomla15())
		{
			if($type=='plugin')
				$db->setQuery("SELECT extension_id FROM #__extensions WHERE `type`='$type' AND `folder`='$group' AND `element`='$name'");
			else
				$db->setQuery("SELECT extension_id FROM #__extensions WHERE `type`='$type' AND `element`='$name'");
			return $db->loadResult();
		}
		//Joomla!1.5
		switch($type)
		{
		case 'plugin':
			$db->setQuery("SELECT id FROM #__plugins WHERE `folder`='$group' AND `element`='$name'");
			return $db->loadResult();
		case 'module':
			$db->setQuery("SELECT id FROM #__modules WHERE `module`='$name'");
			return $db->loadResult();
		case 'template':
			return $name;
		default:
			return false;
		}
	}

	static function InstallPlugin($group, $sourcedir, $name, $publish = 0, $ordering = -99)
	{
		try
		{
			$upgrade = self::getExtensionId('plugin', $name, $group);
			$installer = new JInstaller();
			if(!$installer->install($sourcedir.'/'.$name))
				return false;
			if(!$upgrade)
			{
				$db = JFactory::getDBO();
				if(!self::isJoomla15())
					$db->setQuery("UPDATE `#__extensions` SET `enabled`=$publish, `ordering`=$ordering WHERE `type`='plugin' AND `element`='$name' AND `folder`='$group'");
				else
					$db->setQuery("UPDATE `#__plugins` SET `published`=$publish, `ordering`=$ordering WHERE `element`='$name' AND `folder`='$group'");
				$db->query();
			}
			return true;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	static function UninstallPlugin($group, $name)
	{
		try
		{
			$id = self::getExtensionId('plugin', $name, $group);
			$installer = new JInstaller();
			if(!$installer->uninstall('plugin', $id))
				return false;
			return true;
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	static function InstallTemplate($sourcedir, $name)
	{
		try
		{
			//hide warnings of template installing in Joomla!2.5.0-2.5.3
			$bugfix = (JVERSION>='2.5.0' && JVERSION<='2.5.3');
			if($bugfix)
			{
				$error_reporting = error_reporting();
				error_reporting($error_reporting & (E_ALL ^ E_WARNING));
			}

			$installer = new JInstaller();
			if(!$installer->install($sourcedir.'/'.$name))
				return false;

			if($bugfix)
			{
				error_reporting($error_reporting);
				$db = JFactory::getDBO();
				$qName = $db->Quote($name);
				$db->setQuery('SELECT MIN(id) FROM #__template_styles WHERE template='.$qName.' AND client_id=0 GROUP BY template');

				$id = $db->loadResult();
				$db->setQuery('DELETE FROM #__template_styles WHERE template='.$qName.' AND client_id=0 AND id<>'.(int)$id);
				$db->query();

				$db->setQuery('SELECT MAX(extension_id) FROM #__extensions WHERE element='.$qName.' AND type=\'template\' AND client_id=0 GROUP BY element');
				$id = $db->loadResult();
				$db->setQuery('DELETE FROM #__extensions WHERE element='.$qName.' AND type=\'template\' AND client_id=0 AND extension_id<>'.(int)$id);
				$db->query();
			}

			if(self::isJoomla15())
			{
				$db = JFactory::getDBO();
				$db->setQuery('SELECT COUNT(*) FROM #__templates_menu WHERE template = '.$db->Quote($name));
				if($db->loadResult()==0)
				{
					$db->setQuery('INSERT INTO #__templates_menu (template, menuid) VALUES ('.$db->Quote($name).', -1)');
					$db->query();
				}
				$params_ini = JPATH_SITE.'/templates/'.$name.'/params.ini';
				if(!is_file($params_ini))
				{
					$data = '';
					JFile::write($params_ini, $data);
				}
			}
			$path_css = JPATH_SITE.'/templates/'.$name.'/css';
			if(is_dir($path_css))
			{
				$custom_css = $path_css.'/custom.css';
				if(!is_file($custom_css))
				{
					$data = '';
					JFile::write($custom_css, $data);
				}
			}
			return true;
		}
		catch(Exception $e)
		{
			JError::raiseError(0, $e->getMessage());
			return false;
		}
	}

	static function UninstallTemplate($name)
	{
		try
		{
			$id = self::getExtensionId('template', $name);
			$installer = new JInstaller();
			if(!$installer->uninstall('template', $id))
				return false;
			if(self::isJoomla15())
			{
				$db = JFactory::getDBO();
				$db->setQuery('DELETE FROM #__templates_menu WHERE template = '.$db->Quote($name));
				$db->query();
			}
			return true;
		}
		catch(Exception $e)
		{
			JError::raiseError(0, $e->getMessage());
			return false;
		}
	}


    static function installXAPPBoot()
    {
        $status = true;
        /***
         * Fix xml installer files
         */
        $xm_files = JFolder::files(JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins', '\.xm_$', 3, true);

        if(!empty($xm_files)) foreach($xm_files as $file)
        {
            $newfile = str_replace('.xm_', '.xml', $file);
            JFile::move($file, $newfile);
            if(self::isJoomla15())
            {
                $content = JFile::read($newfile);
                $content = str_replace('<extension ', '<install ', $content);
                $content = str_replace('</extension>', '</install>', $content);
                JFile::write($newfile, $content);
            }
        }


        /***
         * Move xml installer files into place :
         */
        if(version_compare(JVERSION, '3.0', '>='))
        {
            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/xappbot';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/versions/30';
            JFile::move($TemplateVersionPath. '/xappbot.xml', $PluginSource.'/xappbot.xml');
            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins';
        }
        elseif(version_compare(JVERSION, '1.6', '>='))
        {
            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/xappbot';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/versions/25';
            JFile::move($TemplateVersionPath. '/xappbot.xml', $PluginSource.'/xappbot.xml');
            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins';
        }
        else
        {

            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/xappbot';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins/versions/15';
            JFile::move($TemplateVersionPath. '/xappbot.xml', $PluginSource.'/xappbot.xml');
            $PluginSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/plugins';
        }

        if(!self::InstallPlugin('system', $PluginSource, 'xappbot'))
        {
            $status = false;
            echo "Installing Plugin System-XAPP-BOT :" . "<span style='color:red'> Failed</span><br/>";
        }else{
            echo "Installing Plugin System-XAPP-BOT :" . "<span style='color:green'> Success</span><br/>";
        }
        return $status;

    }

	static function install()
	{
        /***
         * Install the xapp-bot plugin
         */
        //echo "Install Plugin ";

        $status = self::installXAPPBoot();

        //echo "Install Templates ";


        /***
         * Install the qxapp-template
         */

        $xm_files = JFolder::files(JPATH_ADMINISTRATOR.'/components/com_xas/packages', '\.xm_$', 3, true);
        if(!empty($xm_files)) foreach($xm_files as $file)
        {
            $newfile = str_replace('.xm_', '.xml', $file);
            JFile::move($file, $newfile);
            if(self::isJoomla15())
            {
                $content = JFile::read($newfile);
                $content = str_replace('<extension ', '<install ', $content);
                $content = str_replace('</extension>', '</install>', $content);
                JFile::write($newfile, $content);
            }
        }

        if(version_compare(JVERSION, '3.0', '>='))
        {
            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/qxapp';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/versions/30';
            JFile::move($TemplateVersionPath. '/templateDetails.xml', $TemplateSource.'/templateDetails.xml');
            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates';
        }
        elseif(version_compare(JVERSION, '1.6', '>='))
        {
            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/qxapp';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/versions/25';
            JFile::move($TemplateVersionPath. '/templateDetails.xml', $TemplateSource.'/templateDetails.xml');
            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates';
        }
        else
        {

            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/qxapp';
            $TemplateVersionPath = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates/versions/15';
            JFile::move($TemplateVersionPath. '/templateDetails.xml', $TemplateSource.'/templateDetails.xml');
            $TemplateSource = JPATH_ADMINISTRATOR.'/components/com_xas/packages/templates';
        }

        $templates = array ('qxapp');
        foreach($templates as $template)
        {
            if(!self::InstallTemplate($TemplateSource, $template))
            {
                $status = false;
                echo "Installing Mobile Template \"" . $template . "\" : " . "<span style='color:red'>Failed</span>";
            }else{
                echo "Installing Mobile Template " . $template . " : " . "<span style='color:green'>Success</span>";
            }
        }

		return $status;
	}

	static function uninstall()
	{

		if(!self::UninstallPlugin('system', 'xappbot')){
            echo "Uninstall Plugin System-XAPP-BOT :" . "<span style='color:red'> Failed</span><br/>";
        }else{
            echo "Uninstall Plugin System-XAPP-BOT :" . "<span style='color:green'> Success</span><br/>";
        }


        $templateslist = array ('qxapp');
		foreach($templateslist as $t){
			if(!self::UninstallTemplate($t))
            {
                echo "Uninstall Template " . $t. "<span style='color:red'> : Failed</span><br/>";
            }else{
                echo "Uninstall Template " . $t. "<span style='color:green'> : Success</span><br/>";
            }
        }
		return true;
	}
}