<?php
/**
 * This class makes the menu.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Menu
{
    public function __construct()
    {
        App::openClass('Competition', 'modules/competitions');
    }

    /**
     * Gets the menu.
     *
     * @param String $mode
     * @return String the toString of the menu
     */
    public function getMenuHTML($mode, $parent=null)
    {         
        $usergroup = UserControl::getCurrentUserGroup();

        if ($usergroup->getId() != ADMIN && $parent == 0 &&  $mode != 'login')
            return '';
        
        $items = '';
        $components = Component::getAllComponents(($parent != null ? $parent : 0));
        while (($com = App::$_DB->getRecord($components)) != null)
        {
            if($usergroup->getRight($com->com_id, CRUD_READ))
            {
                if ($mode == 'menu')
                {
                    if ($com->com_in_menu == true)
                    {
                        if ($parent == Component::getComponentId('Competitions') && @$_GET['competition'])
                            $items .= '<li><a href="?competition='.$_GET['competition'].'&amp;com='.$com->com_id.'">'.$com->com_friendlyname.'</a></li>'."\n";
                        else
                            $items .= '<li '.($com->com_id == @$_GET['com'] ? 'class="current_page_item"' : '').'><a href="?com='.$com->com_id.'">'.$com->com_friendlyname.'</a></li>'."\n";
                    }   
                }
                else if ($mode == 'login')
                {
                    if ($com->com_in_menu == false)
                    {
                        if(UserControl::getCurrentUserGroup()->getId() == GAST)
                          $items .= '<li><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$com->com_id.'&amp;option=login" class="login">{LANG_LOGIN}</a></li>'."\n";
                        else
                          $items .= '<li><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$com->com_id.'&amp;option=logout" class="login">{LANG_LOGOUT}</a></li>'."\n";
                    }
                }
                else
                {
                    throw new Exception(App::$_LANG->getValue('ERROR_MENU'));
                }
            }
        }

        // Returns a <ul> wrapper around all <li> items, or an empty string when
        // there are no accessible items to render (callers must handle both cases).
        return $items ? '<ul>' . $items . '</ul>' : '';
    } // getMenuHTML
}
?>
