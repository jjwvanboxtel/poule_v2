<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html of the user pages.
 */
class UserControl extends Component
{
    private static $currentUser = null;

    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        
        switch(@$_GET['option'])
        {
            case 'confirm':
                if(@$_GET['com'] != parent::getComponentId('UserControl'))
                  throw new Exception(htmlspecialchars(@$_GET['option'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
                break;
            default:
            case 'login':
                if(isset($_POST['submit']))
                {
                    self::validateCsrfToken();
                    try
                    {
                        self::$currentUser = User::loginUser();
                        
                        if(self::$currentUser instanceof User)
                          $this->showLoginSucces();
                        else
                          $this->showLoginScreen('<div id="msg">{LANG_LOGIN} {ERROR_LOGIN_FAILED}</div>' . "\n");
                    }
                    catch(Exception $ex)
                    {
                        $this->showLoginScreen('<div id="msg">{LANG_LOGIN}: ' . $ex->getMessage() . '</div>' . "\n");
                    }

                }
                else
                {
                    if(!self::$currentUser instanceof User)
                      $this->showLoginScreen();
                    else
                      throw new Exception('{ERROR_ALREADY_LOGGEDIN}');
                }
                break;
            case 'newparticipant':
                $this->showRegisterScreen();
                break;
            case 'login_lost':
                if (!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                try
                {
                    if (isset($_POST['submit']))
                    {
                        self::validateCsrfToken();
                        $userId = User::getUserId($_POST['email']);
                        $password = User::setTempPassword($_POST['email']);
                        
                    if ($this->sendTempPassword($userId, $_POST['email'], $password))
                          $this->showLoginScreen('<div id="msg">{LANG_LOGIN_LOST}: {LANG_TEMP_PASSWORD}</div>' . "\n");
                        else
                          $this->showLoginLostScreen('<div id="msg">{LANG_LOGIN_LOST}: {ERROR_TEMP_PASSWORD}</div>' . "\n");
                    }
                    else
                    {
                        $this->showLoginLostScreen();
                    }
                }
                catch(Exception $ex)
                {
                    $this->showLoginLostScreen('<div id="msg">{LANG_LOGIN_LOST}: ' . $ex->getMessage() . '</div>' . "\n");
                }
                break;
            case 'logout':
                self::logOut();
                $this->showLoginScreen();
                break;
            case 'accept':
                if (!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                if (!User::exists(@$_GET['userId']))
                  throw new Exception('{ERROR_ITEMNOTEXIST}');

                $user = new User($_GET['userId']);
                $hashParam = isset($_GET['hash']) ? (string)$_GET['hash'] : '';
                if ($hashParam !== '' && hash_equals((string)$user->getTempPassword(), hash('sha256', $hashParam)))
                {
                    $user->setPassword(password_hash($hashParam, PASSWORD_ARGON2ID, [
                        'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2,
                    ]));
                    $user->save();
                    $this->showLoginScreen('<div id="msg">{LANG_LOGIN_LOST}: {LANG_LOGIN_LOST_SUCCES}</div>' . "\n");
                }
                else
                    $this->showLoginScreen('<div id="msg">{LANG_LOGIN_LOST}: {LANG_LOGIN_LOST_HASH_ERROR}</div>' . "\n");
                break;
            case 'activate':
                if (!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                if (!User::exists(@$_GET['userId']))
                  throw new Exception('{ERROR_ITEMNOTEXIST}');

                $user = new User($_GET['userId']);
                if (!$user->getEnabled())
                {
                    $hashParam = isset($_GET['hash']) ? (string)$_GET['hash'] : '';
                    if ($hashParam !== '' && hash_equals((string)$user->getTempPassword(), hash('sha256', $hashParam)))
                    {
                        $user->enable();
                        $user->save();
                        $this->showLoginScreen('<div id="msg">{LANG_ACCOUNT_ACTIVATION}: {LANG_ACCOUNT_ACTIVATION_SUCCES}</div>' . "\n");
                    }
                    else
                        $this->showLoginScreen('<div id="msg">{LANG_ACCOUNT_ACTIVATION}: {LANG_ACCOUNT_ACTIVATION_HASH_ERROR}</div>' . "\n");
                }
                else
                {
                    $this->showLoginScreen('<div id="msg">{LANG_ACCOUNT_ACTIVATION}: {LANG_ACCOUNT_ACTIVATION_ALREADY_ENABLED}</div>' . "\n");
                }
                break;
            //throw new Exception(htmlspecialchars(@$_GET['option'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    /**
     * Gets the current usergroup.
     *
     * @return User the current user
     */
    public static function getCurrentUserGroup()
    {
        if(self::$currentUser != NULL)
          return self::$currentUser->getUserGroup();
        else
          return new UserGroup(GAST);
    } // getCurrentUserGroup

    /**
     * Gets the current user.
     *
     * @return User $user
     */
    public static function getCurrentUser()
    {
        return self::$currentUser;
    } // getCurrentUser

    /**
     * Logs the current user out.
     */
    public static function logOut()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['logged_in']);

        self::$currentUser = null;
    } // logOut

    /**
     * Sets the current user.
     *
     * @param int $id
     */
    public static function setCurrentUser($id)
    {
        self::$currentUser = new User($id);
    } // setCurrentUser

    public function showLoginScreen($msg='')
    {
        $tpl = new Template('loginscreen', strtolower(get_class()), 'modules');

        $replaceArr = array();
        $replaceArr['LOGIN_MSG_WRAPPER'] = self::buildMsgWrapper($msg);
        $replaceArr['LOGIN_ACTION'] = '?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=login';
        $replaceArr['LOGIN_LOST'] = '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=login_lost">{LANG_LOGIN_LOST}</a>';
        $replaceArr['NEW_CUSTOMER'] = '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=newparticipant">{LANG_PARTICIPANT_NEW}</a>';
        $replaceArr['CSRF_TOKEN'] = self::getCsrfTokenField();
        $tpl->replace($replaceArr);

        echo $tpl;
    } //showLoginScreen

    public function showMailSendConfirmation($msg='')
    {
        $tpl = new Template('confirmation', strtolower(get_class()), 'modules');

        $replaceArr = array();
        $replaceArr['LOGIN_MSG_WRAPPER'] = self::buildMsgWrapper($msg);
        $tpl->replace($replaceArr);

        echo $tpl;
    } //showMailSendConfirmation    
    
    private function showLoginSucces()
    {
        if (self::getCurrentUserGroup()->getId() == ADMIN)
        {
            header('Location: index.php'.(@$_GET['competition'] ? '?competition='.@$_GET['competition'].'' : ''));
        }
        else
        {
            header('Location: index.php?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&' : '').'com='.Component::getComponentId('Predictions').'&option=edit&id='.self::getCurrentUser()->getId());
        }
    }

    private function showRegisterScreen()
    {
        App::openClass('Users');
        App::openClass('InputException', 'modules');
        
        if(isset($_POST['submit']))
        {
            self::validateCsrfToken();
            try
            {
                $userId = Users::doEditUser();
                
                if (App::$_CONF->getValue('TEST_MODE') == 'true' 
                        || Usercontrol::getCurrentUserGroup()->getId() == ADMIN)
                  $this->showLoginScreen('<div id="msg">{LANG_USER} {LANG_ADD_OK}</div><br />' . "\n");
                else
                {
                  $user = new User($userId);  
                  
                  // TODO: fix mail
                  //$this->sendAccountActivation($user);
                  //$this->showMailSendConfirmation('<div id="msg">{LANG_USER} {LANG_ACTIVATION_MAIL_SEND}</div>' . "\n");
                  //$this->showLoginScreen('<div id="msg">{LANG_USER} {LANG_ACTIVATION_MAIL_SEND}</div><br />' . "\n");                  

                  $this->showLoginScreen('<div id="msg">{LANG_USER} {LANG_ADD_OK}</div><br />' . "\n");
                }
            }
            catch (InputException $iex)
            {
                Users::showEditUser($iex);
            }
            catch (Exception $ex)
            {
                $this->showLoginScreen('<div>{LANG_USER} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
            }
        }
        else
        {
            Users::showEditUser();
        }

    } //showRegisterScreen

    private function showLoginLostScreen($msg='')
    {
        $tpl = new Template('login_lost', strtolower(get_class()), 'modules');
        
        $replaceArr = array();
        $replaceArr['LOGIN_MSG_WRAPPER'] = self::buildMsgWrapper($msg);
        $replaceArr['USER_COM_ID'] = $_GET['com'];
        $replaceArr['CSRF_TOKEN'] = self::getCsrfTokenField();
        $tpl->replace($replaceArr);

         echo $tpl;
    } //showLoginLostScreen

    private function sendTempPassword($userId, $email, $password)
    {
        $header = 'From: ' . App::$_CONF->getValue('MAIL') . "\n";
        $header .= 'MIME-Version: 1.0' . "\n";
		$header .= 'Content-Type: text/html; charset=iso-8859-1' . "\n";
		$header .= 'X-Priority: 3' . "\n";
		$header .= 'X-MSMail-Priority: Normal' . "\n";
		$header .= 'X-Mailer: PHP / ' . phpversion() . "\n";
		$subject = '' . App::$_CONF->getValue('LOGIN_LOST_SUBJECT') . ''."\n";
		$body = "<html><body>"."\n";
		$body .= "<font face=\"Tahoma\" size=\"2\">"."\n";
		$body .= '' . App::$_CONF->getValue('LOGIN_LOST_BODY') . ''."\n";
        $body .= '<br /><br />'."\n";
        $body .= App::$_LANG->getValue('LANG_NEW_PASSWORD').': ' . $password . '<br />'."\n";
        $body .= '<a href="' . App::$_CONF->getValue('DOMAIN') . '?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=accept&amp;userId='.$userId.'&amp;hash='.$password.'">'.App::$_LANG->getValue('LANG_ACCEPT_PASSWORD').'</a>'."\n";
        
		if(mail($email, $subject, $body, $header))
          return true;

        return false;
    } // sendTempPassword
    
    private function sendAccountActivation($user)
    {
        App::openClass('Users');

        $tpl = new Template('activation_mail', strtolower(get_class()), 'modules');
    
        $header = 'From: ' . App::$_CONF->getValue('MAIL') . "\n";
        $header .= 'MIME-Version: 1.0' . "\n";
		$header .= 'Content-Type: text/html; charset=iso-8859-1' . "\n";
		$header .= 'X-Priority: 3' . "\n";
		$header .= 'X-MSMail-Priority: Normal' . "\n";
		$header .= 'X-Mailer: PHP / ' . phpversion() . "\n";
		$subject = '' . App::$_CONF->getValue('ACCOUNT_ACTIVATION') . ''."\n";

        $body = '' . App::$_LANG->getValue('LANG_WELCOME').': ' . $user->getFirstName() . ' ' . $user->getLastName() . '<br />'."\n";

        $body .= Users::showUser($user->getId());
        $body .= '<br />'."\n";
		$body .= '' . App::$_CONF->getValue('ACCOUNT_ACTIVATION_BODY') . ''."\n";
        $body .= '<br /><br />'."\n";
        $body .= '<a href="' . App::$_CONF->getValue('DOMAIN') . '?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=activate&amp;userId='.$user->getId().'&amp;hash='.$user->getTempPassword().'">'.App::$_LANG->getValue('LANG_ACCOUNT_ACTIVATION').'</a>'."\n";
        
        $body .= '<br /><br />'."\n";
        
        $body .= 'Veel Plezier en succes!<br />'."\n"; 
        $body .= 'De organisatie,<br />'."\n";
        $body .= 'www.vvalem.nl'."\n";
        
        $replaceArr = array();
        $replaceArr['CONTENT'] = $body;
        
        $replaceArr = array_merge($replaceArr, App::$_LANG->toArray());
        $tpl->replace($replaceArr);
        
		if(mail($user->getEmail(), $subject, $tpl->__toString(), $header))
          return true;

        return false;
    } // sendAccountActivation
    
} //UserControl

?>
