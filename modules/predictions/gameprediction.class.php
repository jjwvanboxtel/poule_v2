<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class GamePrediction
{
    private $result = null;
    private $userId = 0;
    private $gameId = 0;
    
    private static $resultList = null;
    
    public function __construct($userId, $gameId)
    {
        $this->userId = (int)$userId;
        $this->gameId = (int)$gameId;
        
        $this->result = App::$_DB->doQuery('SELECT * FROM `participant_game_prediction` WHERE `Participant_User_user_id` = ? AND `Game_game_id` = ? LIMIT 1', 'ii', $this->userId, $this->gameId);
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

    public function getGameId()
    {
        return $this->gameId;
    }
    
    public function getResult()
    {
        return $this->result->Participant_Game_result;
    }
    
    public function getRedCards()
    {
        return $this->result->Participant_Game_red_cards;
    }
    
    public function getYellowCards()
    {
        return $this->result->Participant_Game_yellow_cards;
    }

    public function setResult($result)
    {
        $this->result->Participant_Game_result = $result;
    }

    public function setRedCards($cards)
    {
        $this->result->Participant_Game_red_cards = $cards;
    }

    public function setYellowCards($cards)
    {
        $this->result->Participant_Game_yellow_cards = $cards;
    }

    public function delete()
    {
        App::$_DB->doQuery('DELETE FROM `participant_game_prediction` WHERE `Participant_User_user_id` = ? AND `Game_game_id` = ?', 'ii', $this->userId, $this->gameId);
        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doQuery(
            'UPDATE `participant_game_prediction` SET `Participant_Game_result` = ?, `Participant_Game_red_cards` = ?, `Participant_Game_yellow_cards` = ? WHERE `Participant_User_user_id` = ? AND `Game_game_id` = ? LIMIT 1',
            'siiiii',
            $this->result->Participant_Game_result,
            (int)$this->result->Participant_Game_red_cards,
            (int)$this->result->Participant_Game_yellow_cards,
            $this->userId,
            $this->gameId
        );
    }

    public static function getAllPredictions($userId)
    {
        self::$resultList = App::$_DB->doQuery('SELECT * FROM `participant_game_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_game_prediction` WHERE `Participant_User_user_id` = ?', 'i', (int)$userId);
    }

    public static function deleteAllPredictionsByGame($gameId)
    {
        App::$_DB->doQuery('DELETE FROM `participant_game_prediction` WHERE `Game_game_id` = ?', 'i', (int)$gameId);
    }
        
    public static function add($userId, $gameId, $result, $red_cards, $yellow_cards)
    {
        App::$_DB->doQuery(
            'INSERT INTO `participant_game_prediction` (Participant_User_user_id, Game_game_id, Participant_Game_result, Participant_Game_red_cards, Participant_Game_yellow_cards) VALUES (?, ?, ?, ?, ?)',
            'iisii',
            (int)$userId,
            (int)$gameId,
            $result,
            (int)$red_cards,
            (int)$yellow_cards
        );
    }

    public static function nextPrediction()
    {
        if (self::$resultList == null)
          return null;

        $record = App::$_DB->getRecord(self::$resultList);
        if ($record == null)
          self::$resultList = null;

        return $record;
    }

    public static function exists($userId, $gameId)
    {
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `participant_game_prediction` WHERE `Participant_User_user_id` = ? AND `Game_game_id` = ?', 'ii', (int)$userId, (int)$gameId);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
