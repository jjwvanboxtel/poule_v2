<?php

define('VALID_ACCESS', true);

$time = microtime(true);

error_reporting(0);

//error_reporting(E_ALL ^ E_STRICT);
//ini_set('display_errors',1);

# Session lifetime of 3 hours
ini_set('session.gc_maxlifetime', 10800);

# Enable session garbage collection with a 1% chance of
# running on each session_start()
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

# Our own session save path; it must be outside the
# default system save path so Debian's cron job doesn't
# try to clean it up. The web server daemon must have
# read/write permissions to this directory.
session_save_path(getcwd() . '/sessions');

/**
 * Is used to initiate all application needs and sends the request to the right modules
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
final class App
{
    public static $_LANG = null;
    public static $_CONF = null;
    public static $_DB = null;
    public static $_UPL = null;
    private static $_COM = null;
    private static $_TPL = null;

    /**
     * Default first method that is used to start the application
     */
    public static function main()
    {
        define('CRUD_READ', 1);
        define('CRUD_CREATE', 2);
        define('CRUD_EDIT', 4);
        define('CRUD_DELETE', 8);

        define('ADMIN', 1);
        define('GAST', 2);
        define('PARTICIPANT', 3);

        define('M_FILE_SIZE', 2000);
        define('UPLOAD_DIR', 'upload/');
        define('EXTENTIONS',  '.jpg, .gif, .png');

        session_start();

        //catch fatal exceptions
        try
        {
            self::openClass('Settings', 'modules/');

            self::$_CONF = new Settings('config.cfg.php');
            self::$_LANG = new Settings('languages/' . self::$_CONF->getValue('language') . '.lang.php', false);

            self::openClass('Database', 'modules/');
            self::$_DB = new Database();

            self::openClass('InputException', 'modules/');
            self::openInterface('iUpload', 'modules/');
            self::openClass('Upload', 'modules/');
            self::$_UPL = new Upload(UPLOAD_DIR, M_FILE_SIZE, EXTENTIONS);

            self::openClass('Menu', 'modules/');

            self::openClass('User', 'modules/users');
            self::openClass('UserGroup', 'modules/usergroups');
            self::openClass('Participant', 'modules/users');

            self::openClass('Template', 'modules/');
            self::openClass('Component', 'modules/');
            self::openClass('UserControl');
            self::openClass('Competition', 'modules/competitions');

            self::$_TPL = new Template('index');

            if(isset($_SESSION['user_id'], $_SESSION['logged_in']))
                UserControl::setCurrentUser($_SESSION['user_id']);
                
            if (self::$_CONF->getValue('TEST_MODE') == "true")
                echo '<div style="color: red;">TEST_MODE = enabled, some features will behave differently!</div>'."\n";
                
            $replaceArr = array();
            ob_start();
             
            try
            {
                if(@$_GET['competition'] && !Competition::exists($_GET['competition']))
                    throw new Exception(self::$_LANG->getValue('ERROR_COMPETITION'));   
                    
                if (@$_GET['com'])
                {
                    $comId = self::$_DB->escapeString(@$_GET['com']);
                    $record = self::$_DB->doSQL('SELECT `com_name`
                                                 FROM `component`
                                                 WHERE `com_id` = \'' . $comId . '\'
                                                 LIMIT 1;');

                    if (self::$_DB->numRows($record) != 1)
                      throw new Exception(self::$_LANG->getValue('ERROR_COMNOTEXIST'));

                    $component = self::$_DB->getRecord($record)->com_name;;
                    self::openClass($component);

                    self::$_COM = new $component($comId);
                }
                else
                {
                    if (@$_GET['competition'] && Competition::exists($_GET['competition']))
                    {
                        $competition = new Competition($_GET['competition']);
                        echo '<div class="title">
                        <h2>' . $competition->getName() . '</h2>
                        </div>' . "\n";
                        echo '<br />'."\n";
                        echo $competition->getDescription() . "<br /><br />\n";                    
                    }
                    else 
                    {
                        echo '<div class="title">
                        <h2>' . self::$_LANG->getValue('LANG_HOME') . '</h2>
                        </div>' . "\n";
                        echo self::$_CONF->getValue('HOME_CONTENT') . "<br /><br />\n";
                    }
                }


            }
            catch (Exception $e)
            {
                echo 'Warning: ' . $e->getMessage();
            }


            $replaceArr['CONTENT'] = ob_get_contents();
            ob_clean();
            ob_end_flush();

            $menu = new Menu();
            $replaceArr['MENU'] = $menu->getMenuHTML('menu');
            $replaceArr['LOGIN'] = $menu->getMenuHTML('login');

            if (@$_GET['competition'] && Competition::exists($_GET['competition']))
            {
                $competition = new Competition($_GET['competition']);
                $replaceArr['LOGO'] = '<img src="./'.UPLOAD_DIR.Competition::getHeaderDir(@$_GET['competition']).$competition->getImage().'" alt="'.$competition->getName().'" class="logo" />';
                $replaceArr['SUB_TITLE'] = $competition->getName();
                
                $total = (Participant::getNumberOfParticipants($_GET['competition'], true, true)*$competition->getMoney());
       			
                $user_information = '';
                if (Usercontrol::getCurrentUserGroup()->getId() == PARTICIPANT)
                {
                    $user_information .= '<li><a href="?competition='.$_GET['competition'].'&amp;com='.Component::getComponentId('Users').'&amp;option=edit&amp;id='.UserControl::getCurrentUser()->getId().'">'.self::$_LANG->getValue('LANG_PARTICIPANT_INFORMATION') . '</a></li>'."\n";
                }
                $replaceArr['INFORMATION'] = '<div class="title">
                    <h2>Menu</h2>
                </div>
                <ul>
                <li><a href="?competition='.@$_GET['competition'].'">'.App::$_LANG->getValue('LANG_HOME').'</a></li>
                '.$user_information.'
                '.$menu->getMenuHTML('menu', Component::getComponentId('Competitions')).'
                '.$menu->getMenuHTML('login').'
                </ul>
                <div class="title">
                    <h2>'.self::$_LANG->getValue('LANG_COMPETITION') . ':<br /> ' . $competition->getName() .' </h2>
                </div>
                <p>
                    '.self::$_LANG->getValue('LANG_PARTICIPANT_ENTRIES') .': '.Participant::getNumberOfParticipants($_GET['competition'], false, false).'<br />
                    '.self::$_LANG->getValue('LANG_PARTICIPANT_COUNT') .': '.Participant::getNumberOfParticipants($_GET['competition'], true, true).'<br />
                    '.self::$_LANG->getValue('LANG_TOTAL_DEPOSITS') .': '.$total.' '.self::$_LANG->getValue('LANG_MONETARY_UNIT') .'<br />
                    <br />
                    '.self::$_LANG->getValue('LANG_PRIZE').':<br />
                    '.self::$_LANG->getValue('LANG_COMPETITION_FIRST_PLACE') .' ('.$competition->getFirstPlace().'%): '.(($competition->getFirstPlace()/100)*$total). ' ' .self::$_LANG->getValue('LANG_MONETARY_UNIT') .'<br />
                    '.self::$_LANG->getValue('LANG_COMPETITION_SECOND_PLACE') .' ('.$competition->getSecondPlace().'%): '.(($competition->getSecondPlace()/100)*$total). ' '.self::$_LANG->getValue('LANG_MONETARY_UNIT') .'<br />
                    '.self::$_LANG->getValue('LANG_COMPETITION_THIRD_PLACE') .' ('.$competition->getThirdPlace().'%): '.(($competition->getThirdPlace()/100)*$total). ' '.self::$_LANG->getValue('LANG_MONETARY_UNIT') .'
                </p>';
            }
            else
            {
                $replaceArr['LOGO'] = '';
                $replaceArr['SUB_TITLE'] = '';
       			$competitions = '<div class="title">
                    <h2>'.self::$_LANG->getValue('LANG_COMPETITIONS') . '</h2>
                </div>';
                
                $competitions .= '<ul>';
                Competition::getAllCompetitions();
                while (($competition = Competition::nextCompetition()) != null)
                {
                    $competitions .= '<li><a href="?competition='.$competition->competition_id.'">'.$competition->competition_name.'</a></li>';
                }
                $competitions .= $menu->getMenuHTML('login');
                $competitions .= '</ul>';

                $replaceArr['INFORMATION'] = $competitions;
            }
            $replaceArr['TITLE'] = self::$_CONF->getValue('TITLE');
            $replaceArr['COPYRIGHT'] = self::$_CONF->getValue('COPYRIGHT');
            $replaceArr['TEMPLATE_NAME'] = self::$_CONF->getValue('TEMPLATE');
            $replaceArr = array_merge($replaceArr, self::$_LANG->toArray());
            
            self::$_TPL->addHeader('<script type="text/javascript" src="./modules/tinymce/js/tinymce/tinymce.min.js"></script>
                <script type="text/javascript">
                tinymce.init({
                    selector: "textarea.editor",
                    plugins: [
                        "advlist autolink lists link image charmap print preview anchor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table paste"
                    ],
                    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
                });
                </script>');
                       
            self::$_TPL->replaceHeaders();

            self::$_TPL->replace($replaceArr);
            echo self::$_TPL;

        }
        catch (Exception $ex)
        {
            echo 'Error: ' . $ex->getMessage();
        }

    } //main

    /**
     * Includes a class safely with checks if the file exists and the specified file is valid
     *
     * @param  string $className The classname that has to be included
     * @param  string $folder [optional] The folder where the class resides
     * @return boolean Always true
     * @throws Exception if file does not exists, or the class was not found
     */
    public static function openClass($className, $folder="")
    {
        //check if class already exists, we don't want to include it twice
        if (class_exists($className))
          return;

        //this is for linux support
        $classFile = strtolower($className);

        //check path and use default if not defined
        if ($folder == "")
          $folder = 'modules/' . $classFile . '/';
        else if (substr($folder, -1, 1) != '/')
          $folder .= '/';

        //include the file and throw an error if failed
        $file = $folder . $classFile . '.class.php';      
        if (!file_exists($file))
          throw new Exception($classFile . ' file does not exist!');

        require($file);

        //check if the file was valid an has the specified class
        if (!class_exists($className))
          throw new Exception($className . ' class was not loaded successfully!');

        return true;
    } //openClass

    /**
     * Includes a interface safely with checks if the file exists and the specified file is valid
     *
     * @param  string $interfaceName The interfacename that has to be included
     * @param  string $folder [optional] The folder where the interface resides
     * @return boolean Always true
     * @throws Exception if file does not exists, or the interface was not found
     */
    public static function openInterface($interfaceName, $folder="")
    {
        //check if interface already exists, we don't want to include it twice
        if (interface_exists($interfaceName))
          return;

        //this is for linux support
        $interfaceFile = strtolower($interfaceName);

        //check path and use default if not defined
        if ($folder == "")
          $folder = 'modules/' . $interfaceFile . '/';
        else if (substr($folder, -1, 1) != '/')
          $folder .= '/';

        //include the file and throw an error if failed
        $file = $folder . $interfaceFile . '.interface.php';      
        if (!file_exists($file))
          throw new Exception($interfaceFile . ' file does not exist!');

        require($file);

        //check if the file was valid an has the specified class
        if (!interface_exists($interfaceName))
          throw new Exception($interfaceName . ' interface was not loaded successfully!');

        return true;
    } //openInterface
    
    public static function getMainTemplate()
    {
        return self::$_TPL;
    }

} //App

App::main();

//echo microtime(true)-$time;

?>