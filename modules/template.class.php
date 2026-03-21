<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is used to to parse a template file. The vars included are replaced by the corresponding values in de array
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 03-12-2008
 * @version   0.1
 */
final class Template
{
    private static $headers = "";
    private $contents = "";

    /**
     * Checks if the template file exists
     *
     * @param string $tpl The template file that has to be parsed
     * @param string $component The component folder where the template file resides
     * @param string $vars The variables that are replaced in the template
     * @param boolean $isFile Sets if the $tpl var is a file or direct input
     * @param string $folder The folder where the file exists
     */
    public function __construct($tpl, $component="", $folder="")
    {
        //set default folder
        if ($folder == '')
          $folder = 'templates/' . App::$_CONF->getValue('template') . '/';
        else if (substr($folder, -1, 1) != '/')
          $folder .= '/';

        if ($component != "")
          $folder .= $component . '/';

        //include the file and throw an error if failed
        $file = $folder . $tpl . '.tpl.php';
        if (!file_exists($file))
          throw new Exception($tpl . ' file does not exist!');

        //read file
        $this->contents = file_get_contents($file);

        //remove first line
        $pos = strpos($this->contents, "\n");
        if ($pos !== false)
          $this->contents = substr($this->contents, ++$pos);

    } //__construct

    /**
     * Add vars to replace the specified containers in the template
     *
     * @param stringArray $vars The vars that have to be replaced in the template
     * @return void when array is empty
     */
    public function replace(&$vars)
    {
        if (count($vars) == 0)
          return;

        //search for capital-sensitive keys
        foreach($vars as $key => $var)
          $this->contents = str_replace('{' . strtoupper($key) . '}', $var, $this->contents);
    } //addVars

    /**
     * Add a header, this function is required because you can add headers through the application
     *
     * @param String $header The header to be added to the final template
     */
    public static function addHeader($header)
    {
        if (substr($header, -1, 1) != "\n")
          $header .= "\n";

        self::$headers .= $header;
    } //addHeader

    /**
     * Now, replace the headers in the template file
     */
    public function replaceHeaders()
    {
        $vars = array("HEADERS" => self::$headers);
        $this->replace($vars);
    }

    /**
     * Parses the template by replacing all the vars in the file by the array values
     */
    public function __toString()
    {
        return $this->contents;
    } //__toString

} //Template

?>
