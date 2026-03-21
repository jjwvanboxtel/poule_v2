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
        $this->userId = App::$_DB->escapeString($userId);
        $this->gameId = App::$_DB->escapeString($gameId);
        
        $this->result = App::$_DB->doSQL('SELECT *
                                          FROM `participant_game_prediction`
                                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                                          AND `Game_game_id` = ' . $this->gameId . ' LIMIT 1;');
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
        App::$_DB->doSQL('DELETE FROM `participant_game_prediction` 
                            WHERE `Participant_User_user_id` = ' . $this->userId . '
                            AND `Game_game_id` = ' . $this->gameId . '');

        $this->__destruct();
        return true;
    }

    public function save()
    {
        App::$_DB->doSQL('UPDATE `participant_game_prediction` SET
                          `Participant_Game_result` = "'.App::$_DB->escapeString($this->result->Participant_Game_result).'",
                          `Participant_Game_red_cards` = "'.App::$_DB->escapeString($this->result->Participant_Game_red_cards).'",
                          `Participant_Game_yellow_cards` = "'.App::$_DB->escapeString($this->result->Participant_Game_yellow_cards).'"
                          WHERE `Participant_User_user_id` = ' . $this->userId . '
                          AND `Game_game_id` = ' . $this->gameId . ' LIMIT 1;');
    }

    public static function getAllPredictions($userId)
    {
        self::$resultList = App::$_DB->doSQL('SELECT * FROM `participant_game_prediction`
                          WHERE `Participant_User_user_id` = ' . $userId);
    }
    
    public static function deleteAllPredictionsByUser($userId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_game_prediction`
                          WHERE `Participant_User_user_id` = ' . $userId);
    }

    public static function deleteAllPredictionsByGame($gameId)
    {
        self::$resultList = App::$_DB->doSQL('DELETE FROM `participant_game_prediction`
                          WHERE `Game_game_id` = ' . $gameId . '');
    }
        
    public static function add($userId, $gameId, $result, $red_cards, $yellow_cards)
    {
        App::$_DB->doSQL('INSERT INTO `participant_game_prediction` (Participant_User_user_id, Game_game_id, Participant_Game_result, Participant_Game_red_cards, Participant_Game_yellow_cards)
                          VALUES (
                            '.$userId.',
                            '.$gameId.',
                            "'.App::$_DB->escapeString($result).'",
                            '.App::$_DB->escapeString($red_cards).',
                            '.App::$_DB->escapeString($yellow_cards).')
                          ');                         
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
        $record = App::$_DB->doSQL('SELECT count( * ) AS total
                                    FROM `participant_game_prediction`
                                    WHERE `Participant_User_user_id` = ' . $userId . '
                                    AND `Game_game_id` = ' . $gameId . '');

        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
