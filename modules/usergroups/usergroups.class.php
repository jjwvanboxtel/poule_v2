<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the usergroup pages.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 09-12-2008
 * @version   0.1
 */
class UserGroups extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {  
        parent::__construct($id);
        App::openClass('InputException', 'modules/');

        switch(@$_GET['option'])
        {
            case '':
                $this->showUserGroups();
                break;
            case 'add': // add usergroup ---------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditUserGroup();
                        $this->showUserGroups('{LANG_USERGROUP} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditUserGroup($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showUserGroups('{LANG_USERGROUP} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditUserGroup();
                }
                break;
            case 'edit': // edit usergroup --------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                try
                {
                    if(isset($_POST['submit']))
                    {
                        $userGroup = new UserGroup($_GET['id']);
                        $userGroup->setGroupName($_POST['group']);

                        $this->doEditUserGroup($userGroup);
                        $userGroup->save();
                        $this->showUserGroups('{LANG_USERGROUP} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditUserGroup(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditUserGroup($iex);
                }
                catch (Exception $ex)
                {
                    $this->showUserGroups('{LANG_USERGROUP} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                
                break;
            case 'delete': // delete usergroup ------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && UserGroup::exists($_GET['id']))
                    {
                        $usergroup = new UserGroup($_GET['id']);

                        if ($usergroup->isReadOnly())
                          throw new Exception("{ERROR_ACCESSDENIED}");

                        $usergroup->delete();
                        $this->showUserGroups('{LANG_USERGROUP} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showUserGroups('{LANG_USERGROUP} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                
                break;
            default: //------------------------------------------------------------------------------------------------------------------
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showUserGroups($msg='')
    {
        $tpl = new Template('usergroup', strtolower(get_class()), 'modules');
        UserGroup::getAllUserGroups();

        $c = 0;
        $content = '';
        while (($userGroup = UserGroup::nextUserGroup()) != null)
        {
            //$readOnly = (boolean)$userGroup->group_readonly;
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td><img alt="{LANG_USERGROUP}" src="templates/{TEMPLATE_NAME}/icons/group.png" class="icon" /></td>' . "\n";
            $content .= '<td>' . $userGroup->group_id . '</td>' . "\n";
            $content .= '<td>' . $userGroup->group_name . '</td>' . "\n";
            $content .= '<td>' . $userGroup->member_count . '</td>' . "\n";
            if ($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE))
            {
                $content .= '<td>' . "\n";
                ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?com='.$this->componentId.'&amp;option=edit&amp;id='.$userGroup->group_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_USERGROUP} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
                (($this->hasAccess(CRUD_DELETE) && !$userGroup->group_readonly) ? $content .= '<a href="?com='.$this->componentId.'&amp;option=delete&amp;id='.$userGroup->group_id .'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_USERGROUP} {LANG_REMOVE}" class="actions" /></a>' . "\n" : ''); 
                $content .= '</td>' . "\n";
            }
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_USERGROUP_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_USERGROUPS}';
        $replaceArr['USERGROUP_MSG'] = self::buildMsgWrapper($msg);

        $replaceArr['ACTIONS'] = '';
        $replaceArr['LINK_ADD'] = '';
        if($this->hasAccess(CRUD_CREATE))
          $replaceArr['LINK_ADD'] = '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_USERGROUP} {LANG_ADD}" class="actions_top" />  <a href="?com='.$this->componentId.'&amp;option=add" class="button">{LANG_USERGROUP} {LANG_ADD}</a>';
        if ($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE))
          $replaceArr['ACTIONS'] = '<th style="width: 50px;">{LANG_ACTIONS}</th>';


        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showUserGroups

    private function showEditUserGroup($edit=false)
    {
        $tpl = new Template('usergroup_add', strtolower(get_class()), 'modules');

        if ((is_bool($edit) && $edit) || ($edit instanceof InputException && @$_GET['option']=='edit'))
        {
            if (!@$_GET['id'] || !UserGroup::exists(@$_GET['id']))
              throw new Exception("{ERROR_ITEMNOTEXIST}");

            $userGroup = new UserGroup(@$_GET['id']);
        }

        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';
        if ($edit && @$userGroup != null && !$edit instanceof InputException)
        {
            $groupName = $userGroup->getGroupName();
        }
        else if ($edit instanceof InputException && $edit)
        {
            switch($edit->getErrorField())
            {
                case 'group': $groupName = @$_POST['group']; break;
            }
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }

        $input = '{LANG_USERGROUP_NAME}: <input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'group') || (@$edit && !@$groupName)) ? ' error' : '') . '" maxlength="45" type="text" name="group"' . (@$groupName ? ' value="'.$groupName.'"' : '') . ' />' . "\n";

        $c = 0;
        $content = '';
        parent::getAllComponents();
        while (($component = parent::nextComponent()) != null)
        {
            $check_read = 0;
            $check_create = 0;
            $check_edit = 0;
            $check_delete = 0;

            if ($edit instanceOf InputException)
            {
                $check_read = (boolean)@$_POST['com_' . $component->com_id . '_read'];
                $check_create = (boolean)@$_POST['com_' . $component->com_id . '_create'];
                $check_edit = (boolean)@$_POST['com_' . $component->com_id . '_edit'];
                $check_delete = (boolean)@$_POST['com_' . $component->com_id . '_delete'];
            }

            if (@$userGroup != null)
            {
                $check_read = (!isset($_POST['com_' . $component->com_id . '_read'])) ? $userGroup->getRight($component->com_id, CRUD_READ) : $check_read;
                $check_create = (!isset($_POST['com_' . $component->com_id . '_read'])) ? $userGroup->getRight($component->com_id, CRUD_CREATE) : $check_create;
                $check_edit = (!isset($_POST['com_' . $component->com_id . '_read'])) ? $userGroup->getRight($component->com_id, CRUD_EDIT) : $check_edit;
                $check_delete = (!isset($_POST['com_' . $component->com_id . '_read'])) ? $userGroup->getRight($component->com_id, CRUD_DELETE) : $check_delete;

                $change_read = $userGroup->getChangeAccess($component->com_id, CRUD_READ);
                $change_create = $userGroup->getChangeAccess($component->com_id, CRUD_CREATE);
                $change_edit = $userGroup->getChangeAccess($component->com_id, CRUD_EDIT);
                $change_delete = $userGroup->getChangeAccess($component->com_id, CRUD_DELETE);
            }
            else
            {
                $check_read |= Component::getDefaultRights($component->com_id, CRUD_READ);
                $check_create |= Component::getDefaultRights($component->com_id, CRUD_CREATE);
                $check_edit |= Component::getDefaultRights($component->com_id, CRUD_EDIT);
                $check_delete |= Component::getDefaultRights($component->com_id, CRUD_DELETE);

                $change_read = Component::hasDefaultChRights($component->com_id, CRUD_READ);
                $change_create = Component::hasDefaultChRights($component->com_id, CRUD_CREATE);
                $change_edit = Component::hasDefaultChRights($component->com_id, CRUD_EDIT);
                $change_delete = Component::hasDefaultChRights($component->com_id, CRUD_DELETE);
            }

            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td><img src="templates/{TEMPLATE_NAME}/icons/cog.png" class="icon" /></td>' . "\n";
            $content .= '<td>' . $component->com_id . '</td>' . "\n";
            $content .= '<td>' . $component->com_friendlyname . '</td>' . "\n";
            $content .= '<td><input class="form-check-input" type="checkbox" name="com_' . $component->com_id . '_read"'.(@$check_read ? ' checked="checked"' : "").(@!$change_read ? ' disabled="disabled"' : "").' /></td>' . "\n";
            $content .= '<td><input class="form-check-input" type="checkbox" name="com_' . $component->com_id . '_create"'.(@$check_create ? ' checked="checked"' : "").(@!$change_create ? ' disabled="disabled"' : "").' /></td>' . "\n";
            $content .= '<td><input class="form-check-input" type="checkbox" name="com_' . $component->com_id . '_edit"'.(@$check_edit ? ' checked="checked"' : "").(@!$change_edit ? ' disabled="disabled"' : "").' /></td>' . "\n";
            $content .= '<td><input class="form-check-input" type="checkbox" name="com_' . $component->com_id . '_delete"'.(@$check_delete ? ' checked="checked"' : "").(@!$change_delete ? ' disabled="disabled"' : "").' /></td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $replaceArr['USERGROUP_TITLE'] = "{LANG_USERGROUP} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['USERGROUP_NAME'] = $input;
        $replaceArr['CONTENT'] = $content;
        $replaceArr['USERGROUP_TOTAL'] = $c;
        $replaceArr['USERGROUP_COM_ID'] = $this->componentId;
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditUserGroup      

    private function doEditUserGroup($userGroup=false)
    {
        @$_POST['group'] = trim(@$_POST['group']);
        if (!@$_POST['group'])
          throw new InputException('{ERROR_EMPTY_FIELD}', 'group');
        if (strlen(@$_POST['group']) < 3)
          throw new InputException('{ERROR_TOO_SHORT} 3 {ERROR_CHARS}', 'group');


        //only if we don't have a usergroup yet
        if (!$userGroup)
          $groupId = UserGroup::add($_POST['group']); //posted name

        parent::getAllComponents();
        while(($component = parent::nextComponent()) != null)
        {
            $comId = $component->com_id;

            //get all rights per component from crud
            $rightList = array();
            $rightList['com_'.$comId.'_read'] = CRUD_READ;
            $rightList['com_'.$comId.'_create'] = CRUD_CREATE;
            $rightList['com_'.$comId.'_edit'] = CRUD_EDIT;
            $rightList['com_'.$comId.'_delete'] = CRUD_DELETE;

            //loop trough all rights
            $total = 0;
            foreach($rightList as $rightItem => $right)
            {
                //if ($comId == 2 || $comId == 3)
                //{
                //var_dump($userGroup);
                //echo "<hr>";
                //var_dump(!$userGroup->getChangeAccess($comId, $right)); //false
                //echo "<br>";
                //var_dump($userGroup->getRight($comId, $right)); //true
                //echo "<br>";
                //var_dump(!Component::hasDefaultChRights($comId, $right)); //true
                //}
                //echo "<br>";


                if ($userGroup && $userGroup->getRight($comId, $right) && (!$userGroup->getChangeAccess($comId, $right) || !Component::hasDefaultChRights($comId, $right)))
                  $total += $right;
                else if (@$_POST[$rightItem] && $_POST[$rightItem] == 'on')
                  $total += $right;

                $total |= (Component::getDefaultRights($comId, $right) ? $right : 0);
            }
            //echo $total;

            //set rights per component
            if (!$userGroup)
            {
                UserGroup::newRight($comId, $groupId, $total);
            }
            else
            {
                $userGroup->clearRightArray($comId);
                $userGroup->addRight($comId, $total);
            }
            //echo "<br>";
        }

    } //doEditUserGroup

} // UserGroups

?>