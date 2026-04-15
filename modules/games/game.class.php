<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Game
{
    private $result = null;
    private $id = 0;
    
    private static $resultList = null;
    
    public function __construct($id)
    {
        $this->id = (int)$id;
        $this->result = App::$_DB->doQuery('SELECT * FROM `game` WHERE `game_id` = ? LIMIT 1', 'i', $this->id);
        $this->result = App::$_DB->getRecord($this->result);
    }

    /**
     * Destructs the game-object.
     */
    public function __destruct()
    {
        App::$_DB->freeQuery($this->result);
    } //__destruct

    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->result->game_date;
    }
    
    public function getResult()
    {
        return $this->result->game_result;
    }
    
    public function getRedCards()
    {
        return $this->result->game_red_cards;
    }
    
    public function getYellowCards()
    {
        return $this->result->game_yellow_cards;
    }

    public function getCity()
    {
        return new City($this->result->City_city_id);
    }

    public function getHomeCountry()
    {
        return new Country($this->result->Country_country_id_home);
    }  
    
    public function getAwayCountry()
    {
        return new Country($this->result->Country_country_id_away);
    }

    public function getPoule()
    {
        return new Poule($this->result->Poule_poule_id);
    }
   
    public function setDate($date)
    {
        $this->result->game_date = $date;
    }

    public function setResult($result)
    {
        $this->result->game_result = $result;
    }

    public function setRedCards($cards)
    {
        $this->result->game_red_cards = $cards;
    }

    public function setYellowCards($cards)
    {
        $this->result->game_yellow_cards = $cards;
    }
    
    public function setCity($city_id)
    {
        $this->result->City_city_id = $city_id;
    }

    public function setHomeCountry($country_id)
    {
        $this->result->Country_country_id_home = $country_id;
    }

    public function setAwayCountry($country_id)
    {
        $this->result->Country_country_id_away = $country_id;
    }

    public function setPoule($poule_id)
    {
        $this->result->Poule_poule_id = $poule_id;
    }

    public function delete()
    {
        //get all predictions of this participant
        App::openClass('GamePrediction', 'modules/predictions');

        //delete all of them
        GamePrediction::deleteAllPredictionsByGame($this->id);
    
        App::$_DB->doQuery('DELETE FROM `game` WHERE `game_id` = ?', 'i', $this->id);
        
        $this->__destruct();
        return true;
    }

    public static function deleteAllByCompetition($competitionId)
    {
        App::openClass('GamePrediction', 'modules/predictions');

        Game::getAllGames($competitionId);
        while (($game = Game::nextGame()) != null)
        {
            GamePrediction::deleteAllPredictionsByGame($game->game_id);
        }
        
        App::$_DB->doQuery('DELETE FROM `game` WHERE `Competition_competition_id` = ?', 'i', (int)$competitionId);
    }
    
    public function save()
    {
        App::$_DB->doQuery(
            'UPDATE `game` SET `game_date` = ?, `game_result` = ?, `game_red_cards` = ?, `game_yellow_cards` = ?, `City_city_id` = ?, `Country_country_id_home` = ?, `Country_country_id_away` = ?, `Poule_poule_id` = ? WHERE `game_id` = ? LIMIT 1',
            'sssiiiii',
            $this->result->game_date,
            $this->result->game_result,
            $this->result->game_red_cards,
            $this->result->game_yellow_cards,
            (int)$this->result->City_city_id,
            (int)$this->result->Country_country_id_home,
            (int)$this->result->Country_country_id_away,
            (int)$this->result->Poule_poule_id,
            $this->id
        );
    }

    public static function getAllGames($competitionId=false)
    {
        $baseSql = 'SELECT `game`.*, `home`.`country_name` AS `home_country_name`, `home`.`country_flag` AS `home_country_flag`, `away`.`country_name` AS `away_country_name`, `away`.`country_flag` AS `away_country_flag`, `city`.`city_name`, `poule`.`poule_name` FROM `game` INNER JOIN `country` AS `home` ON `game`.`Country_country_id_home` = `home`.`country_id` INNER JOIN `country` AS `away` ON `game`.`Country_country_id_away` = `away`.`country_id` INNER JOIN `city` ON `game`.`City_city_id` = `city`.`city_id` INNER JOIN `poule` ON `game`.`Poule_poule_id` = `poule`.`poule_id`';

        if ($competitionId) {
            self::$resultList = App::$_DB->doQuery($baseSql . ' WHERE `game`.`Competition_competition_id` = ? ORDER BY `poule`.`poule_name` ASC, `game`.`game_date` ASC, `game`.`game_id` ASC', 'i', (int)$competitionId);
        } else {
            self::$resultList = App::$_DB->doQuery($baseSql . ' ORDER BY `poule`.`poule_name` ASC, `game`.`game_date` ASC, `game`.`game_id` ASC');
        }
    }

    public static function add($competitionId, $date, $result, $red_cards, $yellow_cards, $city_id, $country_id_home, $country_id_away, $poule_id)
    {
        App::$_DB->doQuery(
            'INSERT INTO `game` (game_date, game_result, game_red_cards, game_yellow_cards, City_city_id, Country_country_id_home, Country_country_id_away, Poule_poule_id, Competition_competition_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'sssiiiiii',
            (int)$city_id,
            (int)$country_id_home,
            (int)$country_id_away,
            (int)$poule_id,
            (int)$competitionId
        );

        $gameId = App::$_DB->getLastId();
        
        App::openClass('GamePrediction', 'modules/predictions');
                          
        User::getAllUsers(3);
        while (($user = User::nextUser()) != null)
        {
            GamePrediction::add($user->user_id, $gameId, "0-0", 0, 0);
        }
        
        return $gameId;
    }

    public static function createPredictions($userId) {
        App::openClass('GamePrediction', 'modules/predictions');

        Game::getAllGames();
        while (($game = Game::nextGame()) != null)
        {
           GamePrediction::add($userId, $game->game_id, "0-0", 0, 0);
        }
    }
    
    public static function nextGame()
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
        $record = App::$_DB->doQuery('SELECT count(*) AS total FROM `game` WHERE `game_id` = ?', 'i', (int)$id);
        return (boolean)App::$_DB->getRecord($record)->total;
    }

}
?>
