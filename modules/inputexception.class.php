<?php
/**
 * Is catched when the user enters information that is not valid
 *
 * @package   pizzaproject
 * @author    MI3TIa
 * @copyright 15-12-2008
 * @version   0.1
 */
class InputException extends Exception
{
    private $errorField = '';

    /**
     * The constructor for InputException
     *
     * @param string $msg The message for the user
     * @param string $errorField The field where the error occured
     */
    public function __construct($msg, $errorField)
    {
        parent::__construct($msg);
        $this->errorField = $errorField;
    } //__construct

    /**
     * Returns the field name where the error occured
     *
     * @return string the field name where the error occured
     */
    public function getErrorField()
    {
        return $this->errorField;
    } //getErrorField

} //InputException

?>