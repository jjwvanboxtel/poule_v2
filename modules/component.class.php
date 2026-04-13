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
     * Sanitizes HTML by allowing only a safe subset of tags and attributes.
     *
     * Uses PHP's built-in DOMDocument to parse and clean the HTML, removing
     * script elements, event-handler attributes, and javascript: URIs.
     * Suitable for displaying TinyMCE-authored rich text without XSS risk.
     *
     * @param  string $html  The HTML to sanitize.
     * @return string        Safe HTML containing only allowed tags and attributes.
     */
    public static function sanitizeHtml($html)
    {
        if (trim((string)$html) === '') {
            return '';
        }

        $allowedTags = [
            'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            'a', 'img', 'div', 'span',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
            'sub', 'sup',
        ];

        $allowedAttributes = [
            'a'   => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'width', 'height', 'title'],
            '*'   => ['class', 'align'],
        ];

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        // LIBXML_NONET prevents network access during parsing (e.g. external DTD/entity fetches).
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><head><meta charset="UTF-8"/></head><body>' . $html . '</body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }

        self::sanitizeDomNode($body, $allowedTags, $allowedAttributes);

        $output = '';
        foreach ($body->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }

        return $output;
    }

    /**
     * Recursively removes disallowed elements and dangerous attributes from a DOM node.
     *
     * @param DOMNode $node               The node to process.
     * @param array   $allowedTags        List of lowercase tag names that may remain.
     * @param array   $allowedAttributes  Map of tag => [attr, ...] plus '*' for globals.
     */
    private static function sanitizeDomNode($node, array $allowedTags, array $allowedAttributes)
    {
        $toRemove = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (!in_array($tag, $allowedTags, true)) {
                $toRemove[] = $child;
                continue;
            }

            // Strip disallowed and dangerous attributes from this element.
            $attrsToRemove = [];
            if ($child->hasAttributes()) {
                foreach ($child->attributes as $attr) {
                    $name = strtolower($attr->nodeName);
                    // Block all event-handler attributes (onclick, onerror, …).
                    if (strncasecmp($name, 'on', 2) === 0) {
                        $attrsToRemove[] = $name;
                        continue;
                    }
                    $allowed = array_merge(
                        isset($allowedAttributes[$tag]) ? $allowedAttributes[$tag] : [],
                        isset($allowedAttributes['*'])  ? $allowedAttributes['*']  : []
                    );
                    if (!in_array($name, $allowed, true)) {
                        $attrsToRemove[] = $name;
                        continue;
                    }
                    // Enforce an allowlist of safe URI schemes for href/src.
                    if (in_array($name, ['href', 'src'], true)) {
                        $val = trim($attr->nodeValue);
                        // Allow relative URLs and safe absolute schemes only.
                        if ($val !== '' && !preg_match('/^(https?:|mailto:|#|\/)/i', $val)) {
                            $attrsToRemove[] = $name;
                        }
                    }
                }
            }
            foreach ($attrsToRemove as $attrName) {
                $child->removeAttribute($attrName);
            }

            // Recurse into the now-clean element.
            self::sanitizeDomNode($child, $allowedTags, $allowedAttributes);
        }

        // Promote text children of removed elements so no content is lost,
        // then remove the disallowed element itself.
        foreach ($toRemove as $child) {
            while ($child->hasChildNodes()) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
        }
    }
}
?>
