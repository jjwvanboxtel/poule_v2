<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Round
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `round` WHERE `round_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the round-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->result->round_name;
    }

    public function getCount()
    {
        return $this->result->round_count;
    }
    
    public function setName($name)
    {
        $this->result->round_name = $name;
    }

    public function setCount($count)
    {
        if ($this->result->round_count != $count)
        {
            $this->result->round_count = $count;           
            
            //get all predictions of this participant
            App::openClass('RoundPrediction', 'modules/predictions');

            //delete all of them
            RoundPrediction::deleteAllPredictionsByRound($this->id);

            //delete all results
            RoundResult::deleteAllRoundResultsByRound($this->id);
            
            for ($i=0; $i<$count; $i++)
            {
                //create results
                RoundResult::add(0, $this->id);
                
                //create predictions
                User::getAllUsers(3);
                while (($user = User::nextUser()) != null)
                {
                    RoundPrediction::add($user->user_id, $this->id, $i, 0);
                }  
            }
        }
    }  
    
    public function getCountries()
    {
        $countries = array();
        
        RoundResult::getAllRoundResults($this->id);
        $c=0;
        while (($roundResult = RoundResult::nextRoundResult()) != null)
        {
            if ($roundResult->Country_country_id > 0)
                $countries[$c++] = new Country($roundResult->Country_country_id);
            else 
                $countries[$c++] = 0;
        }
        
        return $countries;
    }
    
    public function setCountries($countries)
    {
        $count = count($countries);
        if ($count != $this->result->round_count)
            throw new Exception(App::$_LANG->getValue('LANG_ROUND_COUNT_ERROR'));

        RoundResult::getAllRoundResults($this->id);
        $c=0;
        while (($roundResult = RoundResult::nextRoundResult()) != null)
        {
            $result = new RoundResult($roundResult->round_result_id);
            $result->setCountry($countries[$c++]);
            $result->save();
        }
    }

    public function delete()
    {
        App::openClass('RoundPrediction', 'modules/predictions');

        //delete all of them
        RoundPrediction::deleteAllPredictionsByRound($this->id);

        //delete all results
        RoundResult::deleteAllRoundResultsByRound($this->id);
            
        //delete scoring
        App::openClass('Scoring', 'modules/scorings');
        Scoring::deleteScoringByRound($this->id);
            
        App::$_DB->doQuery('DELETE FROM `round` WHERE `round_id` = ?', 'i', $this->id);

        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::openClass('RoundPrediction', 'modules/predictions');

        Round::getAllRounds($competitionId);
        while (($round = Round::nextRound()) != null)
        {
           RoundPrediction::deleteAllPredictionsByRound($round->round_id);
           RoundResult::deleteAllRoundResultsByRound($round->round_id);
        }
        
        App::$_DB->doQuery('DELETE FROM `round` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery('UPDATE `round` SET `round_name` = ?, `round_count` = ? WHERE `round_id` = ? LIMIT 1', 'sii', $this->result->round_name, (int)$this->result->round_count, $this->id);
    }

    public static function getAllRounds($competitionId=false)
    {
        if ($competitionId) {
            self::$resultList = App::$_DB->doQuery('SELECT * FROM `round` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
        } else {
            self::$resultList = App::$_DB->doQuery('SELECT * FROM `round`');
        }
    }

    public static function add($competitionId, $name, $count)
    {
        App::$_DB->doQuery('INSERT INTO `round` (round_name, round_count, Competition_competition_id) VALUES (?, ?, ?)', 'sii', $name, (int)$count, (int)$competitionId);
        
        $roundId = App::$_DB->getLastId();
        
        App::openClass('RoundPrediction', 'modules/predictions');
                          
        for ($i=0; $i<$count; $i++)
        {
            RoundResult::add(0, $roundId);
            
            User::getAllUsers(3);
            while (($user = User::nextUser()) != null)
            {
                RoundPrediction::add($user->user_id, $roundId, $i, 0);
            }       
        }
        
        App::openClass('Scoring', 'modules/scorings');
        Scoring::addScoringByRound($competitionId, $name, $roundId);
        
        return $roundId;
    }

    public static function createPredictions($userId) {
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('RoundResult', 'modules/rounds');
        
        Round::getAllRounds();
        while (($round = Round::nextRound()) != null)
        {
            for ($i=0; $i<$round->round_count; $i++)
            {
                //create predictions
                RoundPrediction::add($userId, $round->round_id, $i, 0);
            }
        }
    }
    
    public static function nextRound()
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `round` WHERE `round_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
