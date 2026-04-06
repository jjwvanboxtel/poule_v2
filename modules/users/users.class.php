<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the user pages.
 *
 * @package   poule
 * @author    Jaap van Boxtel
 * @copyright 20-05-2013
 * @version   0.1
 */
class Users extends Component
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
                if (Usercontrol::getCurrentUserGroup()->getId() != ADMIN)
                  throw new Exception('{ERROR_ACCESSDENIED}');                   
                   
                $this->showUsers();
                break;
            case 'newparticipant':
            case 'add': // add user --------------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                if(isset($_POST['submit']))
                {
                    try
                    {
                        Users::doEditUser();
                        $this->showUsers('{LANG_USER} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        Users::showEditUser($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showUsers('{LANG_USER} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    Users::showEditUser();
                }
                break;
            case 'edit': // edit user -------------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_EDIT) ||
                    (Usercontrol::getCurrentUserGroup()->getId() == PARTICIPANT && @$_GET['id'] != UserControl::getCurrentUser()->getId()))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if(isset($_POST['submit']))
                    {
                        Users::doEditUser(true);
                        if (Usercontrol::getCurrentUserGroup()->getId() != PARTICIPANT)
                            $this->showUsers('{LANG_USER} {LANG_EDIT_OK}');
                        else
                            Users::showEditUser(true, '{LANG_USER} {LANG_EDIT_OK}');    
                    }
                    else
                    {
                        Users::showEditUser(true);
                    }
                }
                catch (InputException $iex)
                {
                    Users::showEditUser($iex);
                }
                catch (Exception $ex)
                {
                    $this->showUsers('{LANG_USER} {ERROR_EDIT}: ' . $ex->getMessage());
                }

                break;
            case 'delete': // delete user -----------------------------------------------------------------------------------------------------
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (UserControl::getCurrentUser()->getId() == $_GET['id'])
                      throw new Exception("{ERROR_ACCESSDENIED}");

                    if (@$_GET['id'] && User::exists($_GET['id']))
                    {
                        $user = new User($_GET['id']);

                        App::openClass('UserGroup', 'modules/usergroups');
                        if ($user->getUserGroup()->getId() == 1 && UserGroup::getGroupMemberCount(1) == 1)
                          throw new Exception("{ERROR_DELETE_LASTADMIN}");

                        $user->delete();
                        $this->showUsers('{LANG_USER} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showUsers('{LANG_USER} {ERROR_REMOVE}: ' . $ex->getMessage());
                }

                break;
            case 'show': // show user -----------------------------------------------------------------------------------------------------
                try
                {
                    if (@$_GET['id'] && User::exists($_GET['id']))
                      echo Users::showUser($_GET['id']);
                    else
                      throw new Exception('{ERROR_ITEMNOTEXIST}');
                }
                catch (Exception $ex)
                {
                    $this->showUsers('{LANG_USER} {ERROR_SHOW}: ' . $ex->getMessage());
                }

                break;
            case 'enable':
            case 'disable':
                $enable = ($_GET['option'] == 'enable' ? true : false);

                try
                {
                    if (@$_GET['id'] && User::exists($_GET['id']))
                    {
                        $user = new User($_GET['id']);
                        App::openClass('UserGroup', 'modules/usergroups');                        
                        if ($user->getUserGroup()->getId() == 1 && UserGroup::getGroupEnabledCount(1) == 1 && !$enable)
                          throw new Exception("{ERROR_DISABLE_LASTADMIN}");

                        $enable ? $user->enable() : $user->disable();
                        $user->save();
                        $this->showUsers('{LANG_USER} {LANG_'.($enable ? 'ENABLE' : 'DISABLE').'_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showUsers('{LANG_USER} {LANG_'.($enable ? 'ENABLE' : 'DISABLE').'}: ' . $ex->getMessage());
                }

                break;
            default: //------------------------------------------------------------------------------------------------------------------
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showUsers($msg='')
    {
        $tpl = new Template('user', strtolower(get_class()), 'modules');
        User::getAllUsers(@$_POST['usergroup']);

        $c = 0;
        $content = '';
        while (($user = User::nextUser()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td><img alt="{LANG_USERGROUP}" src="templates/{TEMPLATE_NAME}/icons/'.($user->user_enabled ? 'user' : 'user_red').'.png" class="icon" /></td>' . "\n";
            $content .= '<td>' . $user->user_id . '</td>' . "\n";
            $content .= '<td><a href="?com=' . $this->componentId . '&option=show&id=' . $user->user_id . '">' . $user->user_firstname . ' ' . $user->user_lastname . '</a></td>' . "\n";
            $content .= '<td>' . $user->group_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            $content .= '<a href="?com='.$this->componentId.'&amp;option='.($user->user_enabled ? 'disable' : 'enable').'&amp;id='.$user->user_id.'"><img src="templates/{TEMPLATE_NAME}/icons/'.($user->user_enabled ? 'lock' : 'lock_open').'.png" alt="{LANG_USER} {LANG_'.($user->user_enabled ? 'DISABLE' : 'ENABLE').'}" class="actions" /></a>' . "\n";
            $content .= '<a href="?com='.$this->componentId.'&amp;option=edit&amp;id='.$user->user_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_USER} {LANG_EDIT}" class="actions" /></a>' . "\n";

            if (UserControl::getCurrentUser()->getId() != $user->user_id)
              $content .= '<a href="?com='.$this->componentId.'&amp;option=delete&amp;id='.$user->user_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" class="actions" alt="{LANG_USER} {LANG_REMOVE}" class="actions" /></a>' . "\n";
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_USER_COUNT}: ' . $c . '</td></tr>' . "\n";

        //usergroup list to filter
        $groupList = '<form name="grouplist" action="?com=' . $this->componentId . '" method="post">' . "\n";
        $groupList .= '<select class="form-select" onchange="document.grouplist.submit();" name="usergroup">' . "\n";
        $groupList .= '<option value="">{LANG_ALL}</option>';
        UserGroup::getAllUserGroups((@$_POST['usergroup'] ? $_POST['usergroup'] : false));
        while (($userGroup = UserGroup::nextUserGroup()) != null)
          $groupList .= '<option value="' . $userGroup->group_id . '"' . (@$_POST['usergroup'] == $userGroup->group_id ? ' selected="selected"' : '') . '>' . $userGroup->group_name . '</option>' . "\n";
        $groupList .= '</select>' . "\n";
        $groupList .= '</form><br />' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_USERS}';
        $replaceArr['USERGROUP_LIST'] = $groupList;
        $replaceArr['USER_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['USER_ADD'] = '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_USERGROUP} {LANG_ADD}" class="actions_top" /> <a href="?com={COM_ID}&amp;option=add" class="button">{LANG_USER} {LANG_ADD}</a> 
        <img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_USERGROUP} {LANG_ADD}" class="actions_top" /> <a href="?com={COM_ID}&amp;option=newparticipant" class="button">{LANG_PARTICIPANT} {LANG_ADD}</a>';

        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showUserGroups

    public static function showUser($id)
    {
        $usersComponent = Component::getComponentId("Users");
        
        if (User::isParticipant($id))
          $user = new Participant($id);
        else
          $user = new User($id);

        $content = '<br />' . "\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0" border="0">' . "\n";
        $content .= '<tr><td colspan="2" style="text-decoration: underline;"><h2>' . $user->getFirstName() . ' ' . $user->getLastName() . "</h2></td></tr>\n";
        $content .= '<tr><td style="width: 200px;">{LANG_USER_EMAIL}:</td><td>' . $user->getEmail() . "</td></tr>\n";
        $content .= '<tr><td>{LANG_USER_PHONENR}:</td><td>' . $user->getPhoneNr() . "</td></tr>\n";
        if ($_GET['com']  == $usersComponent)
        {
            $content .= '<tr><td>{LANG_USER_LASTLOGIN}:</td><td>' . ($user->getLastLogin() ? date('H:i:s d-m-y', $user->getLastLogin()) : '{LANG_USER_NOLOGIN}') . "</td></tr>\n";
            $content .= '<tr><td>{LANG_USER_LOGINCOUNT}:</td><td>' . $user->getLoginCount() . "</td></tr>\n";
            $content .= '<tr><td>{LANG_USER_STATUS}:</tr><td>{LANG_' . ($user->getEnabled() ? 'ENABLED' : 'DISABLED') . '}</td>' . "\n";
            $content .= '<tr><td>{LANG_USERGROUP}:</td><td>' . $user->getUserGroup()->getGroupName() . "</td></tr>\n";
        }
        
        if ($user instanceof Participant)
        {
            $content .= '<tr><td colspan="2">&nbsp;</td></tr>' . "\n";
            $content .= '<tr><td>{LANG_USER_STREET}:</td><td>' . $user->getStreet() . ' ' . $user->getHouseNr() . $user->getAddition() . "</td></tr>\n";
            $content .= '<tr><td>{LANG_USER_POSTALCODE}:</td><td>' . $user->getPostalCode() . "</td></tr>\n";
            $content .= '<tr><td>{LANG_USER_TOWN}:</td><td>' . $user->getTown() . "</td></tr>\n";
            $content .= '<tr><td>{LANG_USER_BANKACCOUNT}:</td><td>' . $user->getBankAccount() . "</td></tr>\n";
        }
        if ($_GET['com']  == $usersComponent)
        {
            $content .= '<tr>' . "\n";
            $content .= '<td colspan="7" style="text-align: right;">' . "\n";
            $content .= '<button class="btn btn-secondary" onclick="window.location = \'?com=' . $_GET['com'] . '\';">{LANG_BACK}</button>' . "\n";
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
        }
        $content .= '</table>' . "\n";

        return $content;

    } // showUser

    public static function showEditUser($edit=false, $msg='')
    {
        $tpl = new Template('user_add', strtolower(get_class()), 'modules');
        if ((is_bool($edit) && $edit) || (isset($_GET['id']) && $edit instanceof InputException))
        {
            if (!@$_GET['id'] || !User::exists(@$_GET['id']))
              throw new Exception("{ERROR_ITEMNOTEXIST}");

            if (User::isParticipant(@$_GET['id']))
              $user = new Participant(@$_GET['id']);
            else
              $user = new User(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();

        //get default values
        if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $firstName = @$_POST['firstname'];
            $lastName = @$_POST['lastname'];
            $userGroupId = @$_POST['usergroup'];
            $telNr = @$_POST['telnr'];
            $emailAddress = @$_POST['emailaddress'];

            if (($_GET['option'] == 'edit' || @$_POST['password']) && $edit->getErrorField() != 'password')
              $password = '';

            if (@$_POST['password'] && @$_POST['confirmpassword'] && $edit->getErrorField() != 'confirmpassword')
              $confirmpassword = '';
            if (@$edit instanceof InputException && @$_POST['password'] == "" &&  @$_POST['confirmpassword'] == "")
              $confirmpassword = '';

            if (@$user instanceof Participant || ($edit instanceof InputException && !@$_POST['usergroup']))
            {
                $street = @$_POST['street'];
                $housenr = @$_POST['housenr'];
                $nradd = @$_POST['nradd'];
                $postalcode = @$_POST['postalcode'];
                $town = @$_POST['town'];
                $bankaccount = @$_POST['bankaccount'];
            }
            
            $msg = '<div>'.$edit->getMessage().'</div>';
        }
        else if ($edit)
        {
            //on edit read all values from db
            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $userGroupId = $user->getUserGroup()->getId();
            $telNr = $user->getPhoneNr();
            $emailAddress = $user->getEmail();

            if (@$user instanceof Participant)
            {
                $street = $user->getStreet();
                $housenr = $user->getHouseNr();
                $nradd = $user->getAddition();
                $postalcode = $user->getPostalCode();
                $town = $user->getTown();
                $bankaccount = $user->getBankAccount();
            }

            $password = '';
            $confirmPassword = '';
        }
        
        $content .= '<tr><td>{LANG_USER_FNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'firstname') || (@$edit && !@$firstName)) ? ' error' : '') . '" maxlength="45" type="text" name="firstname"' . (@$firstName ? ' value="'.@$firstName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_USER_LNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'lastname') || (@$edit && !@$lastName)) ? ' error' : '') . '" maxlength="70" type="text" name="lastname"' . (@$lastName ? ' value="'.@$lastName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_USER_PHONENR}:</td><td><input class="form-control' . ((@$edit && $edit instanceof InputException && $edit->getErrorField() == 'telnr') ? ' error' : '') . '" maxlength="10" type="text" name="telnr"' . (@$telNr ? ' value="'.@$telNr.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_USER_EMAIL}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'emailaddress') || (@$edit && !@$emailAddress)) ? ' error' : '') . '" maxlength="128" type="text" name="emailaddress"' . (@$emailAddress ? ' value="'.@$emailAddress.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_USER_PASS}:</td><td><input class="form-control' . ((@$edit instanceof InputException && !isset($password)) ? ' error' : '') . '" maxlength="65" type="password" name="password" /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_USER_PASS} {LANG_USER_CONFIRM}:</td><td><input class="form-control' . ((@$edit instanceof InputException && !isset($confirmpassword)) ? ' error' : '') . '" maxlength="65" type="password" name="confirmpassword" /></td></tr>' . "\n";

        //generate list of usergroups
        if (!@$user instanceof Participant && $_GET['option'] != 'newparticipant')
        {
            $content .= '<tr>' . "\n";
            $content .= '<td>{LANG_USERGROUP}:</td>' . "\n";
            $content .= '<td><select class="form-select" name="usergroup">' . "\n";
            UserGroup::getAllUserGroups();
            while (($userGroup = UserGroup::nextUserGroup()) != null)
            {
                //do not allow to change to participant
                if ($userGroup->group_id != 3)
                  $content .= '<option value="' . $userGroup->group_id . '"' . (@$edit && (@$userGroupId == $userGroup->group_id) ? ' selected' : '') . '>' . $userGroup->group_name . '</option>' . "\n";
            }
            $content .= '</select></td>' . "\n";
            $content .= '</tr>' . "\n";
        }
        else
        {
            $content .= '<tr><td>' . "\n";
            $content .= '{LANG_USER_STREET}:</td><td><div class="d-flex gap-2">' . "\n";
            $content .= '<input class="form-control flex-grow-1' . ((@$edit && $edit instanceof InputException && $edit->getErrorField() == 'address') ? ' error' : '') . '" maxlength="100" type="text" name="street"' . (@$street ? ' value="'.@$street.'"' : '') . ' />' . "\n";
            $content .= '<input class="form-control' . ((@$edit && $edit instanceof InputException && $edit->getErrorField() == 'address') ? ' error' : '') . '" style="width: 80px;" maxlength="6" type="text" name="housenr" placeholder="Nr"' . (@$housenr ? ' value="'.@$housenr.'"' : '') . ' />' . "\n";
            $content .= '<input class="form-control" style="width: 60px;" type="text" maxlength="1" name="nradd" placeholder="+"' . (@$nradd ? ' value="'.@$nradd.'"' : '') . ' />' . "\n";
            $content .= '</div></td></tr>' . "\n";

            $content .= '<tr><td>{LANG_USER_POSTALCODE}:</td><td><input class="form-control' . ((@$edit && $edit instanceof InputException && ($edit->getErrorField() == 'address' || $edit->getErrorField() == 'postalcode')) ? ' error' : '') . '" maxlength="6" type="text" name="postalcode"' . (@$postalcode ? ' value="'.@$postalcode.'"' : '') . ' /></td></tr>' . "\n";
            $content .= '<tr><td>{LANG_USER_TOWN}:</td><td><input class="form-control' . ((@$edit && $edit instanceof InputException && $edit->getErrorField() == 'address') ? ' error' : '') . '" maxlength="100" type="text" name="town"' . (@$town ? ' value="'.@$town.'"' : '') . ' /></td></tr>' . "\n";
            $content .= '<tr><td>{LANG_USER_BANKACCOUNT}:</td><td><input class="form-control' . ((@$edit && $edit instanceof InputException && $edit->getErrorField() == 'bankaccount') ? ' error' : '') . '" maxlength="18" type="text" name="bankaccount"' . (@$bankaccount ? ' value="'.@$bankaccount.'"' : '') . ' /></td></tr>' . "\n";
        }

        $replaceArr['USER_TITLE'] = "{LANG_USER} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($msg);

        $replaceArr['USER_COM_ID'] = $_GET['com'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditUserGroup

    public static function doEditUser($user=false)
    {
        if (isset($_POST['postalcode']))
          $_POST['postalcode'] = strtoupper($_POST['postalcode']);

        App::openClass('Participant', 'modules/users');
        
        if (!isset($_POST['usergroup']))
          $asParticipant = true;
        else
          $asParticipant = false;

        if (!$asParticipant && Usercontrol::getCurrentUserGroup()->getId() == GAST)
          throw new Exception('{ERROR_ACCESSDENIED}');

        if ($user)
        {
            if (!isset($_GET['id']) || !User::exists($_GET['id']))
              throw new Exception('{ERROR_ITEMNOTEXIST}');

            if (User::isParticipant(@$_GET['id']))
              $user = new Participant(@$_GET['id']);
            else
              $user = new User(@$_GET['id']);
        }
        
        $fields = array('firstname' => 2, 'lastname' => 2, 'emailaddress' => 7);
        if (@$user instanceof Participant || $asParticipant)
        {
          //if (UserControl::getCurrentUserGroup()->getId() != ADMIN)
            //$fields = array_merge($fields, array('bankaccount' => 7));
        }
        else if (!@$user instanceof Participant)
          $fields = array_merge($fields, array('usergroup' => 0));

        if (!$user || ($user && $_POST['password'] != ''))
          $fields = array_merge($fields, array('password' => 7, 'confirmpassword' => 0));

        //check for errors
        foreach ($fields as $field => $length)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (@$_POST[$field] == "") { 
                throw new InputException('{ERROR_EMPTY_FIELD}', $field);
            }
            if (strlen(@$_POST[$field]) < $length) {
                throw new InputException('{ERROR_TOO_SHORT} ' . $length . ' {ERROR_CHARS}', $field);
            }
        }

        if (@$_POST['telnr'] && strlen(@$_POST['telnr']) > 0 && !User::isValidPhoneNr(@$_POST['telnr']))
          throw new InputException('{ERROR_INVALID_PHONENR}', 'telnr');           

        if (@$_POST['postalcode'] && strlen(@$_POST['postalcode']) > 0 && !User::isValidPostalCode(@$_POST['postalcode']))
            throw new InputException('{ERROR_INVALID_POSTCODE}', 'postalcode');

        if (@$_POST['emailaddress'] && !User::isValidEmail(@$_POST['emailaddress']))
          throw new InputException('{ERROR_INVALID_EMAIL}', 'emailaddress');

        if (@$_POST['password'] != @$_POST['confirmpassword'])
          throw new InputException("{ERROR_PASS_NOTMATCH}", 'confirmpassword');

        if (@$user instanceof Participant || $asParticipant)
        {
          if (@$_POST['bankaccount'] && strlen(@$_POST['bankaccount']) > 0 && !User::isValidBankAccount(@$_POST['bankaccount']))
            throw new InputException("{ERROR_INVALID_BANKACC}", 'bankaccount');
        }

        //check if we add a participant
        if (!$asParticipant)
        {
            //to add as participant here is not allowed
            if (@$_POST['usergroup'] == 3)
              throw new Exception("{ERROR_ACCESSDENIED}");
            if (isset($_POST['usergroup']) && !UserGroup::exists(@$_POST['usergroup']))
              throw new InputException('{ERROR_WRONG_DATA}', 'usergroup');
        }

        //add new user, else edit user
        if (!$user)
        {
            if ($asParticipant)
            {
                // TODO: fix mail
                //$enabled = 0;
                $enabled = 1;
                if (Usercontrol::getCurrentUserGroup()->getId() == ADMIN
                    || App::$_CONF->getValue('TEST_MODE') == 'true')
                    $enabled = 1;
                
                $userId = Participant::addp($enabled, $_POST['emailaddress'], md5($_POST['password']), $_POST['firstname'],
                                        $_POST['lastname'], $_POST['telnr'], 3, $_POST['postalcode'], $_POST['street'],
                                        $_POST['town'], $_POST['housenr'], $_POST['nradd'], $_POST['bankaccount']);
            }
            else
            {
                $userId = User::add(1, $_POST['emailaddress'], md5($_POST['password']), $_POST['firstname'],
                                    $_POST['lastname'], $_POST['telnr'], $_POST['usergroup']);
            }

        }
        else
        {
            if (@$_POST['usergroup'] != '1' && $user->getUserGroup()->getId() == 1 && UserGroup::getGroupEnabledCount(1) == 1)
              throw new InputException('{ERROR_GRP_LASTADMIN}', 'usergroup');

            $user->setFirstName(@$_POST['firstname']);
            $user->setLastName(@$_POST['lastname']);
            $user->setPhoneNr(@$_POST['telnr']);
            if (!$user->setEmail(@$_POST['emailaddress']))
              throw new InputException('{ERROR_INVALID_EMAIL}', 'emailaddress');
            if ($user->getUserGroup()->getId() != 3)
              $user->setUserGroup(@$_POST['usergroup']);
            if (@$_POST['password'] != '')
                $user->setPassword(md5(@$_POST['password']));

            if ($user instanceof Participant)
            {
                $user->setPostalCode(@$_POST['postalcode']);
                $user->setStreet(@$_POST['street']);
                $user->setAddition(@$_POST['nradd']);
                $user->setTown(@$_POST['town']);
                $user->setHouseNr(@$_POST['housenr']);
                $user->setBankAccount(@$_POST['bankaccount']);
            }

            $user->save();
            
            $userId = $user->getId();
        }
        
        return $userId;
    } //doEditUser

} // Users

?>