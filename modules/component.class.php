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

    /**
     * Sanitizes HTML using HTMLPurifier v4.19.0, preserving TinyMCE markup while
     * stripping <script>, event-handler attributes, javascript: URIs, and dangerous CSS.
     *
     * HTMLPurifier is vendored under libs/htmlpurifier/ (no Composer required).
     *
     * @param  string $html  The HTML to sanitize.
     * @return string        Safe HTML containing only the allowed tags and attributes.
     */
    public static function sanitizeHtml($html)
    {
        if (trim((string)$html) === '') {
            return '';
        }

        require_once dirname(__DIR__) . '/libs/htmlpurifier/HTMLPurifier.safe-includes.php';

        $config = HTMLPurifier_Config::createDefault();

        // ── Definition ID (required before maybeGetRawHTMLDefinition) ────────────
        $config->set('HTML.DefinitionID', 'poule_v2-tinymce');
        $config->set('HTML.DefinitionRev', 1);

        // Disable serializer cache — no writable cache directory needed.
        $config->set('Cache.DefinitionImpl', null);

        // ── Allowed elements & attributes (TinyMCE output set) ──────────────────
        $config->set('HTML.Allowed',
            'p[class|id|style|align],'
            . 'br,hr,'
            . 'strong,b,em,i,u,s,del,ins,'
            . 'h1[class|id|style],h2[class|id|style],h3[class|id|style],'
            . 'h4[class|id|style],h5[class|id|style],h6[class|id|style],'
            . 'ul[class|id|style],ol[class|id|style],li[class|id|style],'
            . 'blockquote[class|id|style],pre[class|id|style],code[class|id|style],'
            . 'a[href|title|target|rel],'
            . 'img[src|alt|width|height|title|class|id|style],'
            . 'figure[class|id|style],figcaption[class|id|style],'
            . 'div[class|id|style|align],span[class|id|style],'
            . 'table[class|id|style|align],'
            . 'caption[class|id|style],'
            . 'colgroup[class|id|style],col[span|width|class|id|style],'
            . 'thead[class|id|style],tbody[class|id|style],tfoot[class|id|style],'
            . 'tr[class|id|style],th[class|id|style|colspan|rowspan|scope|headers],'
            . 'td[class|id|style|colspan|rowspan|headers],'
            . 'sub,sup,mark,small,abbr[title|class|id],cite[class|id],'
            . 'q[cite|class|id],address[class|id|style]'
        );

        // ── CSS: allow common TinyMCE formatting properties only ─────────────────
        $config->set('CSS.AllowedProperties', [
            'text-align', 'text-decoration', 'text-indent',
            'color', 'background-color',
            'font-size', 'font-weight', 'font-style', 'font-family',
            'line-height', 'letter-spacing',
            'width', 'height', 'max-width', 'max-height',
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
            'border-color', 'border-style', 'border-width',
            'float', 'clear', 'vertical-align',
            'list-style', 'list-style-type',
        ]);

        // ── URI: allow http(s), mailto, relative paths; block javascript:, data: ─
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // ── Encoding & output ────────────────────────────────────────────────────
        $config->set('Core.Encoding', 'UTF-8');

        // Do not auto-add paragraphs or remove empty nodes — preserve TinyMCE output as-is.
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);

        // ── Register HTML5 elements not in the default HTML4 definition ──────────
        // Must be called after all config->set() calls and before new HTMLPurifier().
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('figure',     'Block',  'Optional: (figcaption, Flow) | (Flow, figcaption?) | Flow', 'Common');
            $def->addElement('figcaption', 'Inline', 'Flow',   'Common');
            $def->addElement('mark',       'Inline', 'Inline', 'Common');
            $def->addElement('address',    'Block',  'Flow',   'Common');
            // Register HTML4 accessibility attributes missing from the bundled definition.
            $def->addAttribute('td', 'headers', 'NMTOKENS');
            $def->addAttribute('th', 'headers', 'NMTOKENS');
        }

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
?>
