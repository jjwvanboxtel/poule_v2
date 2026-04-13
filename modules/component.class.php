<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for a prototype component. All components extend this class.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 02-12-2008
 * @version   0.1
 */
class Component
{
    private static $resultList = null;
    protected $componentId;

    public function __construct($id)
    {
        $this->componentId = $id;

        if(!$this->hasAccess(CRUD_READ))
          throw new Exception('{ERROR_ACCESSDENIED}');
    }

    protected function hasAccess($type)
    {
        $usergroup = UserControl::getCurrentUserGroup();

        if($usergroup->getRight($this->componentId, $type))
          return true;
        else
          return false;
    }
   
    /**
     * Gets all the components form the database.
     */
    public static function getAllComponents($parent=false)
    {
        $query = '';
        if (is_numeric($parent))
          $query = ' WHERE `com_menu_parent` = ' . (int)$parent;

        self::$resultList = App::$_DB->doSQL('SELECT *
                                             FROM `component` 
                                             '.$query.'
                                             ORDER BY `com_friendlyname` ASC;');
                                             
        return self::$resultList; 
    } //getAllComponents

    /**
     * Gets the next component from the resultList.
     *
     * @return $record if there is an next component than return the next component.
     */
    public static function nextComponent()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    } //nextComponent



    public function __toString()
    {
        
    }

    public static function getComponentId($name)
    {
        $record = App::$_DB->doSQL('SELECT `com_id` FROM `component` WHERE `com_name` = "'.App::$_DB->escapeString($name).'"');
        $rec = App::$_DB->getRecord($record);

        return $rec->com_id;
    }

    public static function hasDefaultChRights($com_id, $option)
    {
        $record = App::$_DB->doSQL('SELECT `com_defchrights`
                                    FROM `component`
                                    WHERE `com_id` = ' . (int)$com_id . '
                                    LIMIT 1;');
        $rights = (int)App::$_DB->getRecord($record)->com_defchrights;
        if (($rights&$option) == $option)
          return false;

        return true;
    }

    public static function getDefaultRights($com_id, $option)
    {
        $record = App::$_DB->doSQL('SELECT `com_defrights`
                                    FROM `component`
                                    WHERE `com_id` = ' . (int)$com_id . '
                                    LIMIT 1;');
        
        $rights = (int)App::$_DB->getRecord($record)->com_defrights;
        if (($rights&$option) == $option)
          return true;

        return false;
    }

    public static function hasChilds($com_id)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `component`
                                    WHERE `com_menu_parent` = ' . (int)$com_id);
        
        return (boolean)App::$_DB->getRecord($record)->total;
    }

    /**
     * Wraps a status message in a styled card element.
     *
     * Returns an empty string when $msg is empty. When the message contains
     * the word "error" (case-insensitive) the card is styled as a danger/error
     * state; otherwise it is styled as a success state.
     *
     * @param  string $msg  The raw message text or HTML to wrap.
     * @return string       The wrapped HTML, or '' when $msg is empty.
     */
    protected static function buildMsgWrapper($msg)
    {
        if ($msg === '') {
            return '';
        }
        $isError     = stripos(strtolower($msg), 'error') !== false;
        $borderClass = $isError ? 'border-danger'  : 'border-success';
        $textClass   = $isError ? 'text-danger'    : 'text-success';
        $bg          = $isError ? '#fdecea'         : '#e9f7ef';
        return '<div class="card ' . $borderClass . ' mb-3" style="background-color:' . $bg . ';">'
             . '<div class="card-body ' . $textClass . '">' . $msg . '</div></div>';
    }

    /**
     * Generates (or returns the existing) CSRF token for the current session.
     *
     * Stores the token in $_SESSION['csrf_token'] so it persists across requests.
     *
     * @return string 64-character hex CSRF token.
     */
    public static function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    } // generateCsrfToken

    /**
     * Returns an HTML hidden input field carrying the current CSRF token.
     *
     * Embed the returned string inside every <form> that POSTs to the server.
     * The template placeholder {CSRF_TOKEN} is replaced with this value.
     *
     * @return string  <input type="hidden" name="csrf_token" value="..." />
     */
    public static function getCsrfTokenField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="'
             . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />';
    } // getCsrfTokenField

    /**
     * Validates the CSRF token submitted with a POST request.
     *
     * Compares $_POST['csrf_token'] against the session token using a
     * timing-safe comparison. Throws an Exception (rendered as a 403 by the
     * outer error handler) when the tokens do not match.
     *
     * @throws Exception  When the token is missing or does not match.
     */
    public static function validateCsrfToken()
    {
        $sessionToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
        $postToken    = isset($_POST['csrf_token'])    ? $_POST['csrf_token']    : '';

        if (!hash_equals($sessionToken, $postToken)) {
            http_response_code(403);
            throw new Exception('{ERROR_CSRF_INVALID}');
        }
    } // validateCsrfToken

    /**
     * Builds a table row with the proper even/odd class based on a zero-based row index.
     *
     * Use this helper instead of repeating the inline <tr> construction pattern
     * throughout class-based overview builders.
     *
     * @param string $cells  The inner HTML (one or more <td> elements) for this row.
     * @param int    $index  Zero-based row index to determine the even/odd class.
     * @return string        The complete <tr> HTML string.
     */
    protected static function buildOverviewRow($cells, $index)
    {
        $cls = ($index % 2) ? 'odd' : 'even';
        return '<tr class="' . $cls . '" onmouseover="this.className = \'hover\';" '
             . 'onmouseout="this.className = \'' . $cls . '\';">' . "\n"
             . $cells
             . '</tr>' . "\n";
    }
}


?>
