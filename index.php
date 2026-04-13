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
                    $comId = (int)@$_GET['com'];
                    $record = self::$_DB->doSQL('SELECT `com_name`
                                                 FROM `component`
                                                 WHERE `com_id` = ' . $comId . '
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
                        $countParticipants = Participant::getNumberOfParticipants($_GET['competition'], true, true);
                        $total = $countParticipants * $competition->getMoney();
                        $prize1 = round(($competition->getFirstPlace() / 100) * $total, 2);
                        $prize2 = round(($competition->getSecondPlace() / 100) * $total, 2);
                        $prize3 = round(($competition->getThirdPlace() / 100) * $total, 2);

                        // Stat cards row — 2 per row on small screens, 4 per row on large
                        echo '<div class="row g-3 mb-5">' . "\n";

                        echo '<div class="col-6 col-lg-3">'
                           . '<div class="card stat-card text-center h-100"><div class="card-body">'
                           . '<div class="stat-icon-wrap"><i class="bi bi-person-plus-fill"></i></div>'
                           . '<div class="fs-3 fw-bold">' . Participant::getNumberOfParticipants($_GET['competition'], false, false) . '</div>'
                           . '<div class="text-muted small">' . self::$_LANG->getValue('LANG_PARTICIPANT_ENTRIES') . '</div>'
                           . '</div></div></div>' . "\n";

                        echo '<div class="col-6 col-lg-3">'
                           . '<div class="card stat-card text-center h-100"><div class="card-body">'
                           . '<div class="stat-icon-wrap"><i class="bi bi-people-fill"></i></div>'
                           . '<div class="fs-3 fw-bold">' . $countParticipants . '</div>'
                           . '<div class="text-muted small">' . self::$_LANG->getValue('LANG_PARTICIPANT_COUNT') . '</div>'
                           . '</div></div></div>' . "\n";

                        echo '<div class="col-6 col-lg-3">'
                           . '<div class="card stat-card text-center h-100"><div class="card-body">'
                           . '<div class="stat-icon-wrap"><i class="bi bi-cash-stack"></i></div>'
                           . '<div class="fs-3 fw-bold">' . $total . ' ' . self::$_LANG->getValue('LANG_MONETARY_UNIT') . '</div>'
                           . '<div class="text-muted small">' . self::$_LANG->getValue('LANG_TOTAL_DEPOSITS') . '</div>'
                           . '</div></div></div>' . "\n";

                        echo '<div class="col-6 col-lg-3">'
                           . '<div class="card stat-card text-center h-100"><div class="card-body">'
                           . '<div class="stat-icon-wrap"><i class="bi bi-trophy-fill"></i></div>'
                           . '<div class="text-muted small fw-bold mb-1">' . self::$_LANG->getValue('LANG_PRIZE') . '</div>'
                           . '<div class="small">🥇 ' . self::$_LANG->getValue('LANG_COMPETITION_FIRST_PLACE') . ': &euro;' . $prize1 . '</div>'
                           . '<div class="small">🥈 ' . self::$_LANG->getValue('LANG_COMPETITION_SECOND_PLACE') . ': &euro;' . $prize2 . '</div>'
                           . '<div class="small">🥉 ' . self::$_LANG->getValue('LANG_COMPETITION_THIRD_PLACE') . ': &euro;' . $prize3 . '</div>'
                           . '</div></div></div>' . "\n";

                        echo '</div>' . "\n"; // end stat row

                        // Description card
                        echo '<div class="card stat-card">'
                           . '<div class="card-header"><h5 class="mb-0">' . htmlspecialchars($competition->getName()) . '</h5></div>'
                           . '<div class="card-body">' . Component::sanitizeHtml($competition->getDescription()) . '</div>'
                           . '</div>' . "\n";
                    }
                    else 
                    {
                        echo '<div class="card stat-card">'
                           . '<div class="card-header"><h5 class="mb-0"><i class="bi bi-house-fill me-2"></i>' . self::$_LANG->getValue('LANG_HOME') . '</h5></div>'
                           . '<div class="card-body">' . self::$_CONF->getValue('HOME_CONTENT') . '</div>'
                           . '</div>' . "\n";
                    }
                }


            }
            catch (Exception $e)
            {
                echo '<div class="alert alert-warning">' . htmlspecialchars($e->getMessage()) . '</div>';
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
                
                $user_information = '';
                if (Usercontrol::getCurrentUserGroup()->getId() == PARTICIPANT)
                {
                    $user_information .= '<li><a href="?competition='.$_GET['competition'].'&amp;com='.Component::getComponentId('Users').'&amp;option=edit&amp;id='.UserControl::getCurrentUser()->getId().'"><i class="bi bi-info-circle-fill nav-icon"></i><span class="nav-text">'.self::$_LANG->getValue('LANG_PARTICIPANT_INFORMATION') . '</span></a></li>'."\n";
                }
                $replaceArr['INFORMATION'] = '<ul>
                <li><a href="?competition='.@$_GET['competition'].'"><i class="bi bi-house-fill nav-icon"></i><span class="nav-text">'.App::$_LANG->getValue('LANG_HOME').'</span></a></li>
                '.$user_information.'
                '.$menu->getMenuHTML('menu', Component::getComponentId('Competitions')).'
                </ul>';
            }
            else
            {
                $replaceArr['LOGO'] = '';
                $replaceArr['SUB_TITLE'] = '';
                
                $competitions = '';
                $competitionItems = '';
                Competition::getAllCompetitions();
                while (($competition = Competition::nextCompetition()) != null)
                {
                    $competitionItems .= '<li><a href="?competition='.$competition->competition_id.'"><i class="bi bi-trophy nav-icon"></i><span class="nav-text">'.$competition->competition_name.'</span></a></li>';
                }
                
                if ($competitionItems) {
                    $competitions = '<ul>' . $competitionItems . '</ul>';
                }

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
            echo '<div class="alert alert-danger">' . htmlspecialchars($ex->getMessage()) . '</div>';
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