<?php

define('VALID_ACCESS', true);

$time = microtime(true);

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
    public static $_CONF = null;
    public static $_DB = null;
    public static $_LANG = null;
    public static $_UPL = null;

    /**
     * Default first method that is used to start the application
     */
    public static function main()
    {
        define('M_FILE_SIZE', 2000);
        define('UPLOAD_DIR', '../upload/');
        define('EXTENTIONS',  '.jpg, .gif, .png');

        //catch fatal exceptions
        try
        {
            self::openClass('Settings', 'modules/');

            self::$_CONF = new Settings('../config.cfg.php');
            self::$_LANG = new Settings('../languages/' . self::$_CONF->getValue('language') . '.lang.php', false);

            self::openClass('Database', 'modules/');
            self::$_DB = new Database();

            self::openClass('InputException', 'modules/');
            self::openInterface('iUpload', 'modules/');
            self::openClass('UploadMock', 'tests/mock/');
            self::$_UPL = new UploadMock(UPLOAD_DIR, M_FILE_SIZE, EXTENTIONS);
        
            self::openClass('User', 'modules/users/');
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

        $folder = '../' . $folder;
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

        $folder = '../' . $folder;
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
    
    public static function clearAll()
    {
        App::$_DB->doSQL('DELETE FROM `participant_game_prediction`');
        App::$_DB->doSQL('DELETE FROM `participant_round_prediction`');
        App::$_DB->doSQL('DELETE FROM `participant_question_prediction`');
        App::$_DB->doSQL('DELETE FROM `game`');
        App::$_DB->doSQL('DELETE FROM `round_result`');
        App::$_DB->doSQL('DELETE FROM `round`');
        App::$_DB->doSQL('DELETE FROM `player`');
        App::$_DB->doSQL('DELETE FROM `poule`');
        App::$_DB->doSQL('DELETE FROM `question`');
        App::$_DB->doSQL('DELETE FROM `referee`');
        App::$_DB->doSQL('DELETE FROM `scoring_competition`');
        App::$_DB->doSQL('DELETE FROM `scoring` WHERE `Round_round_id` != 0');        
        App::$_DB->doSQL('DELETE FROM `section_competition`');
        App::$_DB->doSQL('DELETE FROM `participant_competition`');
        App::$_DB->doSQL('DELETE FROM `table`');
        App::$_DB->doSQL('DELETE FROM `city`');
        App::$_DB->doSQL('DELETE FROM `country`');
        App::$_DB->doSQL('DELETE FROM `form`');
        App::$_DB->doSQL('DELETE FROM `participant_subleague`');
        App::$_DB->doSQL('DELETE FROM `subleague`');
        App::$_DB->doSQL('DELETE FROM `competition`');
        App::$_DB->doSQL('DELETE FROM `participant`');
        App::$_DB->doSQL('DELETE FROM `user` WHERE `UserGroup_group_id` != 1');
        
        App::$_UPL->deleteDir('../'.UPLOAD_DIR, true);
        
        App::$_DB->doSQL('ALTER TABLE `participant_game_prediction` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `participant_round_prediction` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `participant_question_prediction` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `game` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `round_result` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `round` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `player` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `poule` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `question` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `referee` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `scoring_competition` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `scoring` auto_increment = 1');        
        App::$_DB->doSQL('ALTER TABLE `section_competition` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `participant_competition` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `table` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `city` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `country` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `form` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `participant_subleague` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `subleague` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `competition` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `participant` auto_increment = 1');
        App::$_DB->doSQL('ALTER TABLE `user` auto_increment = 2');
    }

    public static function log($tag, $string)
    {
        if (TEST_DEBUG)
            echo $tag . ': ' . $string . '<br />'."\n";
    }
    
} //App

App::main();

//echo microtime(true)-$time;

?>