<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Question
{
    public static $_TYPES = array(
        'yesno' => '{LANG_YESNO}',
        'country' => '{LANG_COUNTRY}',
        'referee' => '{LANG_REFEREE}',
        'player' => '{LANG_PLAYER}',
        'dutch_player' => '{LANG_DUTCH_PLAYER}',
        'number' => '{LANG_NUMBER}'
    );

    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `question`
                                          WHERE `question_id` = ' . $this->id . ' LIMIT 1;');
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the question-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getId()
    {
        return $this->id;
    }

    public function getQuestion()
    {
        return $this->result->question_question;
    }
    
    public function getAnwserCount()
    {
        return $this->result->question_anwser_count;
    }
    
    public function getAnwser()
    {
        $anwser = array();
        
        $parts = explode(',', $this->result->question_anwser);
        for ($i=0; $i<count($parts); $i++)
        {
            $anwser[$i] = trim($parts[$i]);
        }

        if (count($anwser) != $this->result->question_anwser_count)
            throw new Exception(App::$_LANG->getValue('LANG_QUESTION_COUNT_ERROR'));
        
        return $anwser;
    }

    public function getType()
    {
        return $this->result->question_type;
    }
    
    public function setQuestion($question)
    {
        $this->result->question_question = $question;
    }

    public function setAnwserCount($count)
    {
        if ($this->result->question_anwser_count != $count)
        { 
            $this->result->question_anwser_count = $count;
            
            $question_anwser = '';
            for ($i=0; $i<$count; $i++)
            {
                $question_anwser .= 'empty' . ($i<($count-1) ? ',' : '');
            }
            
            $this->result->question_anwser = $question_anwser;
        }
    }
    
    public function setAnwser($anwser)
    {
        $count = count($anwser);
        if ($count != $this->result->question_anwser_count)
            throw new Exception(App::$_LANG->getValue('LANG_QUESTION_COUNT_ERROR'));

        $question_anwser = '';
        for ($i=0; $i<$count; $i++)
        {
            $question_anwser .= $anwser[$i] . ($i<($count-1) ? ',' : '');
        }
            
        $this->result->question_anwser = $question_anwser;
    }

    public function setType($type)
    {
        if ($this->result->question_type != $type)
        {
            $this->result->question_type = $type;
            $this->result->question_anwser = "";

            //get all predictions of this participant
            App::openClass('QuestionPrediction', 'modules/predictions');

            //delete all of them
            QuestionPrediction::deleteAllPredictionsByQuestion($this->id);
            
            User::getAllUsers(3);
            while (($user = User::nextUser()) != null)
            {
                QuestionPrediction::add($user->user_id, $this->id, "");
            }
            
            $question_anwser = '';
            $count = $this->result->question_anwser_count;
            for ($i=0; $i<$count; $i++)
            {
                $question_anwser .= 'empty' . ($i<($count-1) ? ',' : '');
            }            
            $this->result->question_anwser = $question_anwser;
        }
    }
    
    public function delete()
    {
        //get all predictions of this participant
        App::openClass('QuestionPrediction', 'modules/predictions');

        //delete all of them
        QuestionPrediction::deleteAllPredictionsByQuestion($this->id);
    
        App::$_DB->doSQL('DELETE FROM `question` WHERE `question_id` = ' . $this->id . '');

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::openClass('QuestionPrediction', 'modules/predictions');

        Question::getAllQuestions($competitionId);
        while (($question = Question::nextQuestion()) != null)
        {
            QuestionPrediction::deleteAllPredictionsByQuestion($question->question_id);
        }
    
        App::$_DB->doSQL('DELETE FROM `question`
                          WHERE `Competition_competition_id` = ' . (int)$competitionId . '');
    }
    
    public function save()
    {
        App::$_DB->doSQL('UPDATE `question` SET
                          `question_question` = "'.App::$_DB->escapeString($this->result->question_question).'",
                          `question_anwser_count` = '.App::$_DB->escapeString($this->result->question_anwser_count).',
                          `question_anwser` = "'.App::$_DB->escapeString($this->result->question_anwser).'",
                          `question_type` = "'.App::$_DB->escapeString($this->result->question_type).'"
                         WHERE `question_id` = ' . $this->id . ' LIMIT 1;');
    }

    public static function getAllQuestions($competitionId=false)
    {
        $query = '';
        if ($competitionId)
            $query = 'WHERE `Competition_competition_id` = ' . (int)$competitionId;

       self::$resultList = App::$_DB->doSQL('SELECT * 
                                             FROM `question`
                                             '.$query);
    }

    public static function add($competitionId, $question_anwser_count, $question, $type)
    {
        // Generate comma-separated empty values based on answer count
        $question_anwser = '';
        for ($i=0; $i<$question_anwser_count; $i++)
        {
            $question_anwser .= 'empty' . ($i<($question_anwser_count-1) ? ',' : '');
        }
        
        App::$_DB->doSQL('INSERT INTO `question` (question_question, question_type, question_anwser_count, question_anwser, Competition_competition_id)
                          VALUES (
                            "'.App::$_DB->escapeString($question).'",
                            "'.App::$_DB->escapeString($type).'",
                            '.$question_anwser_count.',
                            "'.App::$_DB->escapeString($question_anwser).'",
                            '.$competitionId.')
                          ');
        
        $questionId = App::$_DB->getLastId();
        
        App::openClass('QuestionPrediction', 'modules/predictions');
                          
        User::getAllUsers(3);
        while (($user = User::nextUser()) != null)
        {
            QuestionPrediction::add($user->user_id, $questionId, "");
        }
        
        return $questionId;
    }

    public static function createPredictions($userId) {
        App::openClass('QuestionPrediction', 'modules/predictions');

        Question::getAllQuestions();
        while (($question = Question::nextQuestion()) != null)
        {
           QuestionPrediction::add($userId, $question->question_id, "");
        }
    }
    
    public static function nextQuestion()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($id)
    {
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `question`
                                    WHERE `question_id` = ' . (int)$id);

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
