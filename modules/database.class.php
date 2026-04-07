<?php
/**
 * Is the class to make the database connection.
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 03-12-2008
 * @version   0.1
 */
class Database
{
	private $conn;
	private $queryCount = 0;

    /**
     * Connect to de database.
     */
	public function __construct()
	{      
		try
        {
            $this->connect();
        }
        catch(Exception $ex)
        {
            echo $ex->getMessage();
        }
	} // __construct

    /**
     * Delete this object.
     */
    public function __destruct()
    {
        $this->close();
    } // __destruct

    /**
     * Connect to the database.
     */
    public function connect()
	{
		$database = App::$_CONF->getValue('DB_NAME');
        $username = App::$_CONF->getValue('DB_USERNAME');
		$hostname = App::$_CONF->getValue('DB_HOSTNAME');
        $password = App::$_CONF->getValue('DB_PASSWORD');

        $this->conn = new mysqli($hostname, $username, $password, $database);

        if($this->conn->connect_errno != 0) //er gaat iets fout ...
          throw new Exception(App::$_LANG->getValue('ERROR_DB_CONNECT'));
	} // connect

    /**
     * Closes the database connection
     */
	public function close()
	{
		$this->conn->close();
	} // close

    /**
     * Excutes the sql-query.
     *
     * @param string $sql.
     * @return mixed $result.
     * @throws Exception if the sql-query can't excucted.
     */
	public function doSQL($sql)
	{		
        //echo $sql . '<br />';
		$result = $this->conn->query($sql);
        
        if(!$result)
        {
          if (App::$_CONF->getValue('TEST_MODE') != 'true')
            throw new Exception(App::$_LANG->getValue('ERROR_DB_EXECSQL'));
          else
            throw new Exception(App::$_LANG->getValue('ERROR_DB_EXECSQL') . '; ' . $sql);          
        }
        else
          $this->queryCount++;

        return $result;
	} // doSQL

    /**
     * Fetches a mixed object.
     *
     * @param mixed $record.
     * @return object $record.
     */
	public function getRecord($record)
	{
		return $record->fetch_object();
	} // getRecord

    /**
     * Free's the memory assiocated with the result.
     *
     * @param $mem Free's the memory assiocated with the result.
     * @return true if $mem is a valid mysqli_result
     */
	public function freeQuery($mem)
	{
		if (!($mem instanceOf MySQLi_Result))
          return false;
        
        return $mem->free();
	} // freeQuery

    /**
     * Escapes illegal characters to store it safely in the database
     *
     * @param string $var The string to be escaped
     * @return string the escaped string
     */
    public function escapeString(string $var): string
    {
        return $this->conn->real_escape_string($var);
    } //escapeString

    /**
     * Gets the number of rows in a result.
     *
     * @param  Identifer returned by doSQL() $query
     * @return int
     */
	public function numRows($query)
	{
		return $query->num_rows;
	} // numRows
    
    /**
     * Gets the auto generated id used in the last query.
     * 
     * @return int The last inserted id.
     */
    public function getLastId()
    {
        return $this->conn->insert_id;
    } // getLastId
} // Database

?>
