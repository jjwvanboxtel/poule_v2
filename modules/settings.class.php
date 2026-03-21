<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is used to define all settings, read from a file. ID and value are split by an = char
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 02-12-2008
 * @version   0.1
 */
final class Settings
{
    private $settings = array();
    private $currentFile = "";
    private $currentLine = 0;
    private $strict = true;

    /**
     * Includes the settings file and loads all entries into an array
     *
     * @param string $file The file that contains all settings
     * @param boolean $strict If true it throws an exception if a id does not exist
     * @throws Exception if the requested settings file is missing
     */
    public function __construct($file, $strict=true)
    {
        if (!file_exists($file))
          throw new Exception('Setting file \'' . $file . '\' is missing');

        $this->currentFile = $file;
        $this->strict = $strict;

        //open the file and read it line by line
        $handle = @fopen($file, 'r');
        if (!$handle)
          throw new Exception('Could not open the settings file \'' . $file . '\'');

        while (!feof($handle))
        {
            $line = fgets($handle);

            //count next and skip the first line (for php die(), prevent direct access)
            $this->currentLine++;
            if ($this->currentLine == 1)
              continue;

            $value = '';
            $id = '';

            //parse the line
            if ($this->parseLine($line, $id, $value))
              $this->settings[$id] = $value;
        }
        
    } //__construct

    /**
     * Searches for a setting value for the given identifier
     * 
     * @param  string $identifier The identifier used in the settings file
     * @return string The setting value or an empty string when not found
     */
    public function getValue($identifier)
    {
        $identifier = strtolower($identifier);
        if (isset($this->settings[$identifier]))
          return $this->settings[$identifier];

        if ($this->strict)
          throw new Exception($identifier . ' is not defined in \'' . $this->currentFile . '\'!');
        else
          return '';
    } //getValue

    /**
     * Checks for comments and splits the identifier from the value
     *
     * @param string $line The line to be parsed
     * @param string $identifier Reference to the variable to be used as the string identifier
     * @param string $value Reference to the variable that is used to store the value
     * @return boolean If the parser has found a valid line it will return true, false otherwise
     * @throws Exception if a line could not be parsed
     */
    private function parseLine($line, &$identifier, &$value)
    {
        //check for comments and empty lines
        $line = trim($line);
        if (!strlen($line) || substr($line, 0, 1) == '#')
          return false;

        //search for an =
        $pos = strpos($line, '=');
        if ($pos === false || strlen(trim(substr($line, $pos))) == 0)
          throw new Exception('Error in settings file \'' . $this->currentFile . '\' on line ' . $this->currentLine);

        //find the value
        $identifier = strtolower(trim(substr($line, 0, $pos)));
        $value = trim(substr($line, ++$pos));

        return true;
    } //parseLine

    /**
     * Returns the array with settings
     */
    public function &toArray()
    {
        return $this->settings;
    }

}
?>
