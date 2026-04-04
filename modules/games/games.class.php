<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the games pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Games extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Game', 'modules/games');
        App::openClass('City', 'modules/cities');
        App::openClass('Country', 'modules/countries');
        App::openClass('Poule', 'modules/poules');
        App::openClass('Ranking', 'modules/table');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showGames();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditGame();
                        $this->showGames('<div id="msg">{LANG_GAME} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditGame($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showGames('<div>{LANG_GAME} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditGame();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $game = new Game($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditGame($game))
                          $this->showGames('<div>{LANG_GAME} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showGames('<div>{LANG_GAME} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditGame(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditGame($iex);
                }
                catch (Exception $ex)
                {
                    $this->showGames('<div>{LANG_GAME} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Game::exists($_GET['id']))
                    {
                        $game = new Game($_GET['id']);

                        if (!$game->delete())
                          $this->showGames('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_GAME} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showGames('<div>{LANG_GAME} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showGames('<div>{LANG_GAME} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showGames($msg='')
    {
        $tpl = new Template('game', strtolower(get_class()), 'modules');

        Game::getAllGames(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($game = Game::nextGame()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $game->game_date . '</td>' . "\n";
            $content .= '<td>' . $game->city_name . '</td>' . "\n";
            $content .= '<td>' . $game->poule_name . '</td>' . "\n";
            $content .= '<td>' . '<img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->home_country_flag.'" width="16" alt="'.$game->home_country_name.'" class="icon" /> ' . '</td>' . "\n";
            $content .= '<td>' . $game->home_country_name . '</td>' . "\n";
            $result = ($game->game_result != "empty-empty" ? $game->game_result : "{LANG_EMPTY}");
            $content .= '<td>' . $result . '</td>' . "\n";
            $content .= '<td>' . '<img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->away_country_flag.'" width="16" alt="'.$game->away_country_name.'" class="icon" /> '.'</td>' . "\n";
            $content .= '<td>' . $game->away_country_name . '</td>' . "\n";
            $yellowCards = ($game->game_yellow_cards != "empty" ? $game->game_yellow_cards : "{LANG_EMPTY}");
            $content .= '<td>' . $yellowCards . '</td>' . "\n";
            $redCards = ($game->game_red_cards != "empty" ? $game->game_red_cards : "{LANG_EMPTY}");
            $content .= '<td>' . $redCards . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$game->game_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_GAME} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$game->game_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_GAME} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="11">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_GAMES}';
        $replaceArr['GAME_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['GAME_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_GAME} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_GAME} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showGames

    private function showEditGame($edit=false)
    {
        $tpl = new Template('game_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Game::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $game = new Game(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$game != null)
        {
            //on edit read all values from db
            $gameDate = $game->getDate();
            $result = explode('-', $game->getResult());
            $gameResultHome = $result[0]; 
            $gameResultAway = $result[1]; 
            $gameRedCards = $game->getRedCards();
            $gameYellowCards = $game->getYellowCards();
            $gameCity = $game->getCity()->getId();
            $gameHomeCountry = $game->getHomeCountry()->getId();
            $gameAwayCountry = $game->getAwayCountry()->getId();
            $gamePoule = $game->getPoule()->getId();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $gameDate = @$_POST['gamedate'];
            $gameResultHome = @$_POST['gameresulthome'];
            $gameResultAway = @$_POST['gameresultaway'];            
            $gameRedCards = @$_POST['gameredcards'];
            $gameYellowCards = @$_POST['gameyellowcards'];
            $gameCity = @$_POST['gamecity'];
            $gameHomeCountry = @$_POST['gamehomecountry'];
            $gameAwayCountry = @$_POST['gameawaycountry'];
            $gamePoule = @$_POST['gamepoule'];
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_DATE}:</td><td>
            <input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'gamedate') || (@$edit && !@$gameDate) ? 'class="error" ' : ' ') . 'type="text" name="gamedate"' . (@$gameDate ? ' value="'.@$gameDate.'"' : '') . ' /></td>
        </tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_POULE}:</td>' . "\n";
        $content .= '<td><select name="gamepoule">' . "\n";
        Poule::getAllPoules(@$_GET['competition']);
        while (($poule = Poule::nextPoule()) != null)
        {
            $content .= '<option value="' . $poule->poule_id . '"' . (@$edit && ($gamePoule == $poule->poule_id) ? ' selected' : '') . '>' . $poule->poule_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_CITY}:</td>' . "\n";
        $content .= '<td><select name="gamecity">' . "\n";
        City::getAllCities(@$_GET['competition']);
        while (($city = City::nextCity()) != null)
        {
            $content .= '<option value="' . $city->city_id . '"' . (@$edit && ($gameCity == $city->city_id) ? ' selected' : '') . '>' . $city->city_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_HOME_COUNTRY}:</td>' . "\n";
        $content .= '<td><select name="gamehomecountry">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($gameHomeCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_AWAY_COUNTRY}:</td>' . "\n";
        $content .= '<td><select name="gameawaycountry">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($gameAwayCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_RESULT}:</td>' . "\n";
        $content .= '<td><select name="gameresulthome">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameResultHome == "empty") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameResultHome == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select>-' . "\n";
        $content .= '<select name="gameresultaway">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameResultAway == "emtpy") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameResultAway == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";

        $content .= '</tr>' . "\n";

        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_YELLOW_CARDS}:</td>' . "\n";
        $content .= '<td><select name="gameyellowcards">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameYellowCards == "empty") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameYellowCards == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
 
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_RED_CARDS}:</td>' . "\n";
        $content .= '<td><select name="gameredcards">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameRedCards == "empty") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameRedCards == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
         
        $replaceArr['GAME_TITLE'] = "{LANG_GAME} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['GAME_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditGame

    private function doEditGame($game=false)
    {
        $fields = array('gamedate');
        $status = false;

        if (strlen(@$_POST['gamedate']) < 9)
          throw new InputException('{ERROR_TOO_SHORT} 9 {ERROR_CHARS}', 'gamedate');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
        
        //add new game, else edit game
        if (!$game)
        {
            Game::add(@$_GET['competition'], $_POST['gamedate'], $_POST['gameresulthome'].'-'.$_POST['gameresultaway'], $_POST['gameredcards'], $_POST['gameyellowcards'], $_POST['gamecity'], $_POST['gamehomecountry'], $_POST['gameawaycountry'], $_POST['gamepoule']);
        }
        else
        {       
            $game->setDate(@$_POST['gamedate']);
            $game->setResult(@$_POST['gameresulthome'].'-'.@$_POST['gameresultaway']);
            $game->setRedCards(@$_POST['gameredcards']);
            $game->setYellowCards(@$_POST['gameyellowcards']);
            $game->setCity(@$_POST['gamecity']);
            $game->setHomeCountry(@$_POST['gamehomecountry']);
            $game->setAwayCountry(@$_POST['gameawaycountry']);
            $game->setPoule(@$_POST['gamepoule']);
            $game->save();
            $status = true;
        }

        Ranking::updateRanking(@$_GET['competition']);
        
        return $status;
    } //doEditGame

} // Games

?>