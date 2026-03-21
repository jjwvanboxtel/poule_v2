<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class QuestionPrediction
{
    private $result = null;
    private $userId = 0;
    private $questionId = 0;
    
    private static $resultList = null;
    
    public function __construct($userId, $questionId)
    {
        $this->userId = App::$_DB->escapeString($userId);
        $this->questionId = App::$_DB->escapeString($questionId);
        
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `participant_question_prediction`
                                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                                          AND `Question_question_id` = ' . $this->questionId . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the prediction-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getUserId()
    {
        return $this->userId;
    }

    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    public function getAnswer()
    {
        return $this->result->Participant_Question_answer;
    }

    public function setAnswer($answer)
    {
        $this->result->Participant_Question_answer = $answer;
    }

    public function delete()
    {
        App::$_DB->doSQL('DELETE FROM `participant_question_prediction` 
                            WHERE `Participant_User_user_id` = ' . $this->userId . '
                            AND `Question_question_id` = ' . $this->questionId . '');

        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doSQL('UPDATE `participant_question_prediction` SET
                          `Participant_Question_answer` = "'.App::$_DB->escapeString($this->result->Participant_Question_answer).'"
                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                          AND `Question_question_id` = ' . $this->questionId . ' LIMIT 1;');
    }

    public static function getAllQuestionPredictions($userId, $questionId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT * FROM `participant_question_prediction`
                          WHERE `Participant_User_user_id` = ' . $userId);
    }
    
    public static function getPredictionAnswerCount($questionId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT `Participant_Question_answer`, COUNT( Participant_Question_answer ) AS count
                                FROM `participant_question_prediction` 
                                INNER JOIN `participant_competition`
                                ON `participant_question_prediction`.`Participant_User_user_id`=`participant_competition`.`Participant_User_user_id`
                                WHERE `participant_question_prediction`.`Question_question_id` = '.$questionId.'
                                AND `participant_competition`.`Participant_Competition_payed` = 1 
                                AND `participant_competition`.`Participant_Competition_subscribed` = 1
                                GROUP BY `Participant_Question_answer`
                                ORDER BY `count` DESC
                                LIMIT 15');
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_question_prediction`
                          WHERE `Participant_User_user_id` = ' . $userId);
    }
    
    public static function deleteAllPredictionsByQuestion($questionId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_question_prediction`
                          WHERE `Question_question_id` = ' . $questionId . '');
    }
    
    public static function add($userId, $questionId, $answer)
    {
        App::$_DB->doSQL('INSERT INTO `participant_question_prediction` (Participant_User_user_id, Question_question_id, Participant_Question_answer)
                          VALUES (
                            '.$userId.',
                            '.$questionId.',
                            "'.App::$_DB->escapeString($answer).'")
                          ');
    }

    public static function nextQuestionPrediction()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($userId, $questionId)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `participant_question_prediction`
                                    WHERE `Participant_User_user_id` = ' . $userId . '
                                    AND `Question_question_id` = ' . $questionId . '');

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
