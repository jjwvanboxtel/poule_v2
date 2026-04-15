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
        $this->userId = (int)$userId;
        $this->questionId = (int)$questionId;
        
        $this->result = App::$_DB->doQuery('SELECT * FROM `participant_question_prediction` WHERE `Participant_User_user_id` = ? AND `Question_question_id` = ? LIMIT 1', 'ii', $this->userId, $this->questionId);
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
        App::$_DB->doQuery('DELETE FROM `participant_question_prediction` WHERE `Participant_User_user_id` = ? AND `Question_question_id` = ?', 'ii', $this->userId, $this->questionId);
        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doQuery('UPDATE `participant_question_prediction` SET `Participant_Question_answer` = ? WHERE `Participant_User_user_id` = ? AND `Question_question_id` = ? LIMIT 1', 'sii', $this->result->Participant_Question_answer, $this->userId, $this->questionId);
    }

    public static function getAllQuestionPredictions($userId, $questionId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `participant_question_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function getPredictionAnswerCount($questionId)
    {
        self::$resultList = App::$_DB->doQuery(
            'SELECT `Participant_Question_answer`, COUNT(`Participant_Question_answer`) AS count FROM `participant_question_prediction` INNER JOIN `participant_competition` ON `participant_question_prediction`.`Participant_User_user_id` = `participant_competition`.`Participant_User_user_id` WHERE `participant_question_prediction`.`Question_question_id` = ? AND `participant_competition`.`Participant_Competition_payed` = 1 AND `participant_competition`.`Participant_Competition_subscribed` = 1 GROUP BY `Participant_Question_answer` ORDER BY `count` DESC LIMIT 15',
            'i', (int)$questionId
        );
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_question_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function deleteAllPredictionsByQuestion($questionId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_question_prediction` WHERE `Question_question_id` = ?', 'i', (int)$questionId);
    }
    
    public static function add($userId, $questionId, $answer)
    {
        App::$_DB->doQuery('INSERT INTO `participant_question_prediction` (Participant_User_user_id, Question_question_id, Participant_Question_answer) VALUES (?, ?, ?)', 'iis', (int)$userId, (int)$questionId, $answer);
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `participant_question_prediction` WHERE `Participant_User_user_id` = ? AND `Question_question_id` = ?', 'ii', (int)$userId, (int)$questionId);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
