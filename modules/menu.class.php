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
    /** Maps component names to unique Bootstrap Icons classes. */
    private static array $icons = [
        // Top-level
        'UserControl'  => 'bi-box-arrow-in-right',
        'Users'        => 'bi-people-fill',
        'UserGroups'   => 'bi-shield-check',
        'Countries'    => 'bi-globe2',
        'Cities'       => 'bi-buildings',
        'Competitions' => 'bi-trophy-fill',
        // Competition sub-menu
        'Poules'       => 'bi-diagram-3',
        'Games'        => 'bi-play-circle-fill',
        'Questions'    => 'bi-patch-question-fill',
        'Rounds'       => 'bi-arrow-repeat',
        'Referees'     => 'bi-person-badge-fill',
        'Players'      => 'bi-person-fill',
        'Predictions'  => 'bi-lightning-fill',
        'Sections'     => 'bi-puzzle-fill',
        'Scorings'     => 'bi-award-fill',
        'Table'        => 'bi-table',
        'Participants' => 'bi-person-plus-fill',
        'Forms'        => 'bi-file-earmark-text-fill',
        'Statistics'   => 'bi-bar-chart-fill',
        'Subleagues'   => 'bi-diagram-2-fill',
    ];

    private static function getIcon(string $comName): string
    {
        $cls = self::$icons[$comName] ?? 'bi-circle';
        return '<i class="bi ' . $cls . ' nav-icon"></i>';
    }

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
                            $items .= '<li><a href="?competition='.$_GET['competition'].'&amp;com='.$com->com_id.'">'.self::getIcon($com->com_name).'<span class="nav-text">'.htmlspecialchars($com->com_friendlyname, ENT_QUOTES, 'UTF-8').'</span></a></li>'."\n";
                        else
                            $items .= '<li '.($com->com_id == @$_GET['com'] ? 'class="current_page_item"' : '').'><a href="?com='.$com->com_id.'">'.self::getIcon($com->com_name).'<span class="nav-text">'.htmlspecialchars($com->com_friendlyname, ENT_QUOTES, 'UTF-8').'</span></a></li>'."\n";
                    }   
                }
                else if ($mode == 'login')
                {
                    if ($com->com_in_menu == false)
                    {
                        if(UserControl::getCurrentUserGroup()->getId() == GAST)
                          $items .= '<li><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$com->com_id.'&amp;option=login" class="login"><i class="bi bi-box-arrow-in-right nav-icon"></i> {LANG_LOGIN}</a></li>'."\n";
                        else
                          $items .= '<li><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$com->com_id.'&amp;option=logout" class="login"><i class="bi bi-box-arrow-right nav-icon"></i> {LANG_LOGOUT}</a></li>'."\n";
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
