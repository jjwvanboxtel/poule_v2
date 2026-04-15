<?php
if (!defined('VALID_ACCESS')) die();

/**
 * This class is the model for a user.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 06-12-2008
 * @version   0.1
 */
class User
{
    private $result = null;
    protected $id = 0;
    private $userGroup = null;
    
    private static $resultList = null;

    public function __construct($id)
    {
        App::openClass('Participant', 'modules/users');

        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `user` WHERE `user_id` = ? LIMIT 1', 'i', $this->id);

        $this->result = App::$_DB->getRecord($this->result);        
        $this->userGroup = new UserGroup($this->result->UserGroup_group_id);
    } //__construct

    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    /**
     * Updates the user in the database.
     */
    public function save()
    {
        App::$_DB->doQuery(
            'UPDATE `user` SET
                `user_enabled` = ?,
                `user_email` = ?,
                `user_firstname` = ?,
                `user_lastname` = ?,
                `user_password` = ?,
                `user_phonenr` = ?,
                `user_lastlogin` = ?,
                `user_logincount` = ?,
                `UserGroup_group_id` = ?
             WHERE `user_id` = ? LIMIT 1',
            'ssssssiiii',
            $this->result->user_enabled,
            $this->result->user_email,
            $this->result->user_firstname,
            $this->result->user_lastname,
            $this->result->user_password,
            $this->result->user_phonenr,
            (int)$this->result->user_lastlogin,
            (int)$this->result->user_logincount,
            (int)$this->result->UserGroup_group_id,
            $this->id
        );
    } //save

    /**
     * Remove the user from de database
     */
    public function delete()
    {
        //first check if it is a participant
        if (User::isParticipant($this->id))
        {
            //get all predictions of this participant
            App::openClass('GamePrediction', 'modules/predictions');
            App::openClass('RoundPrediction', 'modules/predictions');
            App::openClass('QuestionPrediction', 'modules/predictions');
            App::openClass('Ranking', 'modules/table');
            App::openClass('Subleague', 'modules/subleagues');
            
            //delete all of them
            GamePrediction::deleteAllPredictionsByUser($this->id);
            RoundPrediction::deleteAllPredictionsByUser($this->id);
            QuestionPrediction::deleteAllPredictionsByUser($this->id);

            Ranking::deleteAllByUser($this->id);

            Subleague::deleteAllByUser($this->id);
            
            //remove participant information
            App::$_DB->doQuery('DELETE FROM `participant_competition` WHERE `Participant_User_user_id` = ?', 'i', $this->id);
            App::$_DB->doQuery('DELETE FROM `participant` WHERE `User_user_id` = ?', 'i', $this->id);
        }

        App::$_DB->doQuery('DELETE FROM `user` WHERE `user_id` = ?', 'i', $this->id);
        $this->__destruct();
    } //delete

    public static function getAllUsers($usergroup=false)
    {
        if ($usergroup) {
            self::$resultList = App::$_DB->doQuery(
                'SELECT `user`.*, `usergroup`.`group_name` FROM `user` LEFT OUTER JOIN `usergroup` ON `user`.`UserGroup_group_id` = `usergroup`.`group_id` WHERE `UserGroup_group_id` = ?',
                'i', (int)$usergroup
            );
        } else {
            self::$resultList = App::$_DB->doQuery(
                'SELECT `user`.*, `usergroup`.`group_name` FROM `user` LEFT OUTER JOIN `usergroup` ON `user`.`UserGroup_group_id` = `usergroup`.`group_id`'
            );
        }
    } //getAllUsers

    public static function nextUser()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    } //nextUser

    /**
     * Creates a new user and adds it to the database.
     *
     * @param String $email
     * @param String $password
     * @param String $firstName
     * @param String $lastName
     * @param String $phoneNr
     * @param int $userGroup
     * @param int $lastLogin
     * @param int $loginCount
     */
    public static function add($enabled, $email, $password, $firstName, $lastName, $phoneNr, $userGroup)
    {
        App::openClass('InputException', 'modules');
        if (self::emailExists($email))
          throw new InputException('{ERROR_USER_EMAILEXISTS}', 'emailaddress');

        App::$_DB->doQuery(
            'INSERT INTO `user` (user_enabled, user_email, user_password, user_temp_password, user_firstname, user_lastname, user_phonenr, user_lastlogin, user_logincount, UserGroup_group_id) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)',
            'issssssi',
            (int)$enabled,
            $email,
            $password,
            hash('sha256', bin2hex(random_bytes(32))),
            $firstName,
            $lastName,
            $phoneNr,
            (int)$userGroup
        );
        return App::$_DB->getLastId();
    } //add

    /**
     * Check if user exists
     *
     * @param int $id the ID of the requested user
     * @return boolean true or false if the user exists / not exists
     */
    public static function exists($id)
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `user` WHERE `user_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    } //exists

    /**
     * Checks in the DB if the user has participant data
     */
    public static function isParticipant($id)
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `participant` WHERE `User_user_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    } //isParticipant

    /**
     * Get the id of an user.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->result->user_id;
    } // getId

    /**
     * Get if the user is enabled or disabled.
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return (boolean)$this->result->user_enabled;
    } // getEnabled

    /**
     * Gets the email of an user.
     *
     * @return String $email
     */
    public function getEmail()
    {
        return $this->result->user_email;
    } // getEmail

    /**
     * Gets the password of an user.
     *
     * @return String $password.
     */
    public function getPassword()
    {
        return $this->result->user_password;
    } // getPassword

    /**
     * Gets the temp password of an user.
     *
     * @return String $password.
     */
    public function getTempPassword()
    {
        return $this->result->user_temp_password;
    } // getTempPassword    
    
    /**
     *  Gets the firstname of an user.
     *
     * @return String $firstName
     */
    public function getFirstName()
    {
        return $this->result->user_firstname;
    } // getFirstName

    /**
     * Gets the lastname of an user.
     *
     * @return String $lastName
     */
    public function getLastName()
    {
        return $this->result->user_lastname;
    } // getLastName

    /**
     * Gets the phonenumber of an user.
     *
     * @return int $phoneNr
     */
    public function getPhoneNr()
    {
        return $this->result->user_phonenr;
    } // getPhoneNr

    /**
     * Gets the lastLogin date of an user.
     *
     * @return int $lastLogin
     */
    public function getLastLogin()
    {
        return $this->result->user_lastlogin;
    } // getLastLogin

    /**
     * Gets the number of logins of an user.
     *
     * @return int $loginCount
     */
    public function getLoginCount()
    {
        return $this->result->user_logincount;
    } // getLoginCount

    /**
     * Gets the usergroup-object of an user.
     *
     * @return int $userGroup
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    } // getUserGroup

    /**
     * Sets the email of an user.
     *
     * Deze nog netter maken met een Exception throwen!!!
     *
     * @param String $email
     * @return Boolean true or false
     */
    public function setEmail($email)
    {
        App::openClass('InputException', 'modules');
        if (self::emailExists($email, $this->id))
          throw new InputException('{ERROR_USER_EMAILEXISTS}', 'emailaddress');

        if (!self::isValidEmail($email))
          return false;
          
        $this->result->user_email = $email;

        return true;
    } // setEmail

    public static function emailExists($email, $id=-1)
    {
        if ($id >= 0) {
            $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `user` WHERE `user_email` = ? AND NOT `user_id` = ?', 'si', $email, (int)$id);
        } else {
            $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `user` WHERE `user_email` = ?', 's', $email);
        }
        return (boolean)App::$_DB->getRecord($record)->total;
    } //emailExists

    public static function isValidEmail($email)
    {
        if(preg_match('(^[0-9a-zA-Z_\.-]{1,}@([0-9a-zA-Z_\-]{1,}\.)+[0-9a-zA-Z_\-]{2,}$)', $email))
          return true;

        return false;
    } //isValidEmail

    public static function isValidPhoneNr($nr)
    {
        if(preg_match('((^06((\s{0,1})|(\-{0,1}))[0-9]{8}$)|(^[0-9]{3,4}(\s{0,1}|\-{0,1})[0-9]{6,7}$)|(^\+{1}[0-9]{2}(\s{0,1}|\-{0,1})[0-9]{2,3}(\s{0,1}|\-{0,1})[0-9]{6,7}$))', $nr))
          return true;

        return false;
    } //isValidPhoneNr

    public static function isValidPostalCode($postalcode)
    {
        if(preg_match('(^[1-9]{1}[0-9]{3}\s?[A-Z]{2}$)', $postalcode))
          return true;

        return false;
    } //isValidPostalCode

    public static function isValidBankAccount($bankaccount)
    {
        // TODO: validate IBAN
        if(strlen($bankaccount) == 18)
          return true;

        return false;
    } //isValidBankAccount
    
    /**
     * Sets the password of an user.
     *
     * @param String $password
     */
    public function setPassword($password)
    {
        if ($password == '')
          return;
          
        $this->result->user_password = $password;
    } // setPassword

    /**
     * Sets the phonenumber of an user.
     *
     * @param String $phonenumber
     */
    public function setPhoneNr($phonenumber)
    {
        $this->result->user_phonenr = $phonenumber;
    } // setPhoneNr

    /**
     * Sets the firstname of an user.
     *
     * @param String $firstname
     */
    public function setFirstName($firstname)
    {
        $this->result->user_firstname = $firstname;
    } // setFirstName

    /**
     * Sets the lastname of an user.
     *
     * @param String $lastname
     */
    public function setLastName($lastname)
    {
        $this->result->user_lastname = $lastname;
    } // setLastName

    /**
     * Sets the usergroup of an user.
     *
     * @param int $usergroup
     */
    public function setUserGroup($usergroup)
    {
        $this->result->UserGroup_group_id = $usergroup;
    } // setUserGroup

    /**
     * Sets the lastlogin date of an user.
     *
     * @param int $date
     */
    public function setLastLogin($date)
    {
        $this->result->user_lastlogin = $date;
    } // setLastLogin

    /**
     * Sets the number of logins of an user.
     *
     * @param int $number
     */
    public function setLoginCount($number)
    {
        $this->result->user_logincount = $number;
    } // setLoginCount
    
    /**
     * Enable the user
     */
    public function enable()
    {
        $this->result->user_enabled = 1;
    } //enable

    /**
     * Disable the user
     */
    public function disable()
    {
        $this->result->user_enabled = 0;
    } //disable

    /**
     * Increments the number of logins of an user.
     */
    public function incrementLoginCount()
    {
        $this->result->user_logincount++;
    } // incrementLoginCount

    /**
     * Checks if an user has fill in the right login-data.
     */
    public static function loginUser()
    {
        if(isset($_POST['wac'],  $_POST['geb']))
        {
            $failKey = 'login_fails_' . hash('sha256', $_SERVER['REMOTE_ADDR']);
            if (($_SESSION[$failKey] ?? 0) >= 5)
            {
                throw new \Exception('{LANG_TOO_MANY_ATTEMPTS}');
            }

            $username = $_POST['geb'];
            $plainPassword = $_POST['wac'];

            $result = App::$_DB->doQuery('SELECT `user_enabled`, `user_id`, `user_password` FROM `user` WHERE `user_email` = ?', 's', $username);

            $row = App::$_DB->getRecord($result);

            $authenticated = false;
            if ($row != null)
            {
                $stored = $row->user_password;
                if (strlen($stored) === 32 && hash_equals($stored, md5($plainPassword)))
                {
                    // Legacy MD5 hash — migrate to Argon2id on successful login
                    $authenticated = true;
                    $user = new User($row->user_id);
                    $user->setPassword(password_hash($plainPassword, PASSWORD_ARGON2ID, [
                        'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2,
                    ]));
                }
                elseif (password_verify($plainPassword, $stored))
                {
                    $authenticated = true;
                    $user = new User($row->user_id);
                }
            }

            if ($authenticated)
            {
                if ($row->user_enabled != 1)
                  throw new Exception('{LANG_ACCOUNT_DISABLED}');

                unset($_SESSION[$failKey]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row->user_id;
                $_SESSION['logged_in'] = true;

                $user->incrementLoginCount();
                $user->setLastLogin(time());
                $user->save();
                
                return $user;
            }
            else
            {
                $_SESSION[$failKey] = ($_SESSION[$failKey] ?? 0) + 1;
                return null;
            }
        }
    } // loginUser

    public static function getUserId($email)
    {
        $result = App::$_DB->doQuery('SELECT `user_id` FROM `user` WHERE `user_email` = ? LIMIT 1', 's', $email);
        $result = App::$_DB->getRecord($result);
        return $result->user_id;
    }
    
    /**
     * Sets the temp password of an user.
     *
     * @param $email the email of an user
     * @return int $password that is set
     */
    public static function setTempPassword($email)
    {
        if(!self::emailExists($email))
          throw new Exception('{ERROR_EMAIL_NOT_EXISTS}');

        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        App::$_DB->doQuery('UPDATE `user` SET `user_temp_password` = ? WHERE `user_email` = ? LIMIT 1', 'ss', $hash, $email);

        return $token;
    } //setTempPassword


} //User

?>
