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
                        $this->showGames('{LANG_GAME} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditGame($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showGames('{LANG_GAME} {ERROR_ADD}: ' . $ex->getMessage());
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
                          $this->showGames('{LANG_GAME} {ERROR_EDIT}');
                        else
                          $this->showGames('{LANG_GAME} {LANG_EDIT_OK}');
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
                    $this->showGames('{LANG_GAME} {ERROR_EDIT}: ' . $ex->getMessage());
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
                          $this->showGames('{ERROR_OLD_FILE_REMOVE}<br />{LANG_GAME} {LANG_REMOVE_OK}');
                        else
                          $this->showGames('{LANG_GAME} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showGames('{LANG_GAME} {ERROR_REMOVE}: ' . $ex->getMessage());
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

        // Store games for both views
        $games = array();
        while (($game = Game::nextGame()) != null)
        {
            $games[] = $game;
        }

        $c = 0;
        $content = '';
        
        // Desktop table view
        $content .= '<div class="d-none d-md-block">'."\n";
        $content .= '<div class="table-responsive">'."\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th>{LANG_DATE}</th>'."\n";
        $content .= '<th>{LANG_CITY}</th>'."\n";
        $content .= '<th>{LANG_POULE}</th>'."\n";
        $content .= '<th colspan="2">{LANG_COUNTRY}</th>'."\n";
        $content .= '<th>{LANG_RESULT}</th>'."\n";
        $content .= '<th colspan="2">{LANG_COUNTRY}</th>'."\n";
        $content .= '<th><img src="templates/{TEMPLATE_NAME}/images/yellow_card.jpg" alt="{LANG_YELLOW_CARDS}" class="icon" /></th>'."\n";
        $content .= '<th><img src="templates/{TEMPLATE_NAME}/images/red_card.jpg" alt="{LANG_RED_CARDS}" class="icon" /></th>'."\n";
        $content .= '<th style="width: 75px;">{LANG_ACTIONS}</th>'."\n";
        $content .= '</tr>'."\n";
        
        foreach ($games as $game)
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
        $content .= '</table>'."\n";
        $content .= '</div>'."\n";
        $content .= '</div>'."\n";
        
        // Mobile card view
        $content .= '<div class="d-md-none">'."\n";
        $c = 0;
        foreach ($games as $game)
        {
            $content .= '<div class="card mb-3">'."\n";
            $content .= '<div class="card-body">'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_DATE}</small><br/>'.$game->game_date.'</div>'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_POULE}</small><br/>'.$game->poule_name.'</div>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="row mb-3">'."\n";
            $content .= '<div class="col-12"><small class="text-muted">{LANG_CITY}</small><br/>'.$game->city_name.'</div>'."\n";
            $content .= '</div>'."\n";
            
            // Match
            $content .= '<div class="row mb-3">'."\n";
            $content .= '<div class="col-5 text-end">'."\n";
            $content .= '<img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->home_country_flag.'" width="24" alt="'.$game->home_country_name.'" class="me-2" />'."\n";
            $content .= '<strong>'.$game->home_country_name.'</strong>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="col-2 text-center">'."\n";
            $result = ($game->game_result != "empty-empty" ? $game->game_result : "{LANG_EMPTY}");
            $content .= '<strong>'.$result.'</strong>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="col-5">'."\n";
            $content .= '<img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->away_country_flag.'" width="24" alt="'.$game->away_country_name.'" class="me-2" />'."\n";
            $content .= '<strong>'.$game->away_country_name.'</strong>'."\n";
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            
            // Cards
            $content .= '<div class="row mb-3">'."\n";
            $content .= '<div class="col-6">'."\n";
            $yellowCards = ($game->game_yellow_cards != "empty" ? $game->game_yellow_cards : "{LANG_EMPTY}");
            $content .= '<img src="templates/{TEMPLATE_NAME}/images/yellow_card.jpg" width="16" alt="{LANG_YELLOW_CARDS}" /> '.$yellowCards."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="col-6">'."\n";
            $redCards = ($game->game_red_cards != "empty" ? $game->game_red_cards : "{LANG_EMPTY}");
            $content .= '<img src="templates/{TEMPLATE_NAME}/images/red_card.jpg" width="16" alt="{LANG_RED_CARDS}" /> '.$redCards."\n";
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            
            if ($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE))
            {
                $content .= '<div class="mt-3">'."\n";
                ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$game->game_id .'" class="btn btn-sm btn-outline-secondary me-2"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" width="16" alt="{LANG_GAME} {LANG_EDIT}" /> {LANG_EDIT}</a>' . "\n" : '');
                ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$game->game_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');" class="btn btn-sm btn-outline-danger"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" width="16" alt="{LANG_GAME} {LANG_REMOVE}" /> {LANG_REMOVE}</a>' . "\n" : '');
                $content .= '</div>'."\n";
            }
            
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            $c++;
        }
        $content .= '<div class="text-muted mt-2">{LANG_COUNT}: ' . $c . '</div>'."\n";
        $content .= '</div>'."\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_GAMES}';
        $replaceArr['GAME_MSG'] = self::buildMsgWrapper($msg);
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
            <input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'gamedate') || (@$edit && !@$gameDate)) ? ' error' : '') . '" maxlength="70" type="text" name="gamedate"' . (@$gameDate ? ' value="'.@$gameDate.'"' : '') . ' /></td>
        </tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_POULE}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gamepoule">' . "\n";
        Poule::getAllPoules(@$_GET['competition']);
        while (($poule = Poule::nextPoule()) != null)
        {
            $content .= '<option value="' . $poule->poule_id . '"' . (@$edit && ($gamePoule == $poule->poule_id) ? ' selected' : '') . '>' . $poule->poule_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_CITY}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gamecity">' . "\n";
        City::getAllCities(@$_GET['competition']);
        while (($city = City::nextCity()) != null)
        {
            $content .= '<option value="' . $city->city_id . '"' . (@$edit && ($gameCity == $city->city_id) ? ' selected' : '') . '>' . $city->city_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_HOME_COUNTRY}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gamehomecountry">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($gameHomeCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_AWAY_COUNTRY}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gameawaycountry">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($gameAwayCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";

        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_RESULT}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gameresulthome">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameResultHome == "empty") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameResultHome == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select>-' . "\n";
        $content .= '<select class="form-select" name="gameresultaway">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameResultAway == "emtpy") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameResultAway == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";

        $content .= '</tr>' . "\n";

        
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_YELLOW_CARDS}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gameyellowcards">' . "\n";
        $content .= '<option value="empty" ' . (@$edit && ($gameYellowCards == "empty") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($gameYellowCards == "$i") ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
 
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_GAME_RED_CARDS}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="gameredcards">' . "\n";
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