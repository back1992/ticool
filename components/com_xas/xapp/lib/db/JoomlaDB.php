<?php
/**
 * @version 0.1.0
 * @package DB
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class JoomlaDB
{
    var  $dbo=null;
    public function setDBO($db){
        $this->dbo = $db;
    }

    public function run($query)
    {
        $res = $this->dbo->setQuery($query);
        $objectList = $this->dbo->loadObjectList();
        return $objectList;
    }
}
?>