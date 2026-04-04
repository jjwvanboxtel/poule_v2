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
class Predictions extends Component
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
        App::openClass('Referee', 'modules/referees');
        App::openClass('Player', 'modules/players');
        App::openClass('Poule', 'modules/poules');
        App::openClass('Round', 'modules/rounds');
        App::openClass('Question', 'modules/questions');
        App::openClass('GamePrediction', 'modules/predictions');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('QuestionPrediction', 'modules/predictions');
        App::openClass('Section', 'modules/sections');
        App::openClass('RoundResult', 'modules/rounds');
        App::openClass('Participant', 'modules/users');
        App::openClass('Ranking', 'modules/table');
        App::openClass('Users', 'modules/users');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':
                $userGroup = UserControl::getCurrentUserGroup();
                if ($userGroup->getId() == 1 && !@$_GET['id']
                        || $userGroup->getId() == 2 && !@$_GET['id'])
                    $this->showUsers();
                else
                    $this->showPrediction();                    
                break;
            case 'edit':
                if (!@$_GET['id'] || !User::exists(@$_GET['id']))
                    throw new Exception("{ERROR_ITEMNOTEXIST}");

                if(!$this->hasAccess(CRUD_EDIT) ||
                    $_GET['id'] != UserControl::getCurrentUser()->getId() && UserControl::getCurrentUser()->getUserGroup()->getId() != 1)
                  throw new Exception('{ERROR_ACCESSDENIED}');

                try
                {
                    if(isset($_POST['save']) || isset($_POST['subscribe']))
                    {
                        if(!$this->doEditPrediction())
                            $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}<br />' . "\n", true);
                        else
                        {
                            if (isset($_POST['subscribe']))
                            {
                                $this->showSubscribeConfirmation();
                            }
                            else
                            {
                                $msg = '{LANG_EDIT_OK}';
                                $this->showPrediction('{LANG_PREDICTION} ' . $msg . '<br />' . "\n", true);
                            }
                        }
                    }
                    else if (isset($_POST['subscribe_confirmation']))
                    {
                        if (!$this->doSubcribePrediction())
                            $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}<br />' . "\n", true);
                        else
                            $this->showPrediction('{LANG_PREDICTION} {LANG_SUBSCRIBE_OK}<br />' . "\n", true);
                    }
                    else
                    {
                        $this->showPrediction('', true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showPrediction('', $iex);
                }
                catch (Exception $ex)
                {
                    $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}: ' . $ex->getMessage() . '<br />' . "\n", true);
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showUsers($msg='')
    {
        $tpl = new Template('user', strtolower(get_class()), 'modules');
        User::getAllUsers(3);

        $c = 0;
        $content  = '<tr>'."\n";
        $content .= '<th style="width: 20px;"></th>'."\n";
        $content .= '<th style="width: 40px;">{LANG_ID}</th>'."\n";
        $content .= '<th>{LANG_USER_FULLNAME}</th>'."\n";
        if($this->hasAccess(CRUD_EDIT))
            $content .= '<th style="width: 75px;">{LANG_ACTIONS}</th>'."\n";
        $content .= '</tr>'."\n";
        while (($user = User::nextUser()) != null)
        {
            $participant = new Participant($user->user_id);
            if (($participant->getSubscribed(@$_GET['competition']) &&
                $participant->getPayed(@$_GET['competition'])) ||
                Usercontrol::getCurrentUserGroup()->getId() == ADMIN)
            {
                $currentClass = (($c % 2) ? 'odd' : 'even');
                $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
                $content .= '<td><img alt="{LANG_USERGROUP}" src="templates/{TEMPLATE_NAME}/icons/'.($user->user_enabled ? 'user' : 'user_red').'.png" class="icon" /></td>' . "\n";
                $content .= '<td>' . $user->user_id . '</td>' . "\n";
                $content .= '<td><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com=' . $this->componentId . '&id=' . $user->user_id . '">' . $user->user_firstname . ' ' . $user->user_lastname . '</a></td>' . "\n";
                $content .= '<td>' . "\n";
                if ($this->hasAccess(CRUD_EDIT))
                {
                    $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$user->user_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_USER} {LANG_EDIT}" class="actions" /></a>' . "\n";
                }
                $content .= '</td>' . "\n";
                $content .= '</tr>' . "\n";
                $c++;
            }
        }

        $content .= '<tr><td colspan="4">{LANG_USER_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_PREDICTIONS}';
        $replaceArr['USER_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showUserGroups
    
    private function showPrediction($msg='', $edit=false)
    {
        $tpl = new Template('prediction', strtolower(get_class()), 'modules');

        $submission_date_expired = Competition::checkSubmissionDateExpired(@$_GET['competition'], time());
        $user = UserControl::getCurrentUser();                
        $participant = new Participant((@$_GET['id'] ? $_GET['id'] : $user->getId()));

        $resultSection = new Section(Section::$_SECTION_RESULTS);
        $cardsSection = new Section(Section::$_SECTION_CARDS);
        $knockoutSection = new Section(Section::$_SECTION_KNOCK_OUT_FASE);
        $questionSection = new Section(Section::$_SECTION_QUESTIONS);
        
        $post = array();
        if ($edit && $edit instanceof InputException)
            $post = $this->parse(@$_POST);

        $replaceArr = array();
        $replaceArr['USER_CONTENT'] = '<h3>{LANG_PARTICIPANT}: '.$participant->getFirstName().' ' .$participant->getLastName().'</h3>';
        $replaceArr['GAME_CONTENT'] = (!(!$resultSection->getEnabled(@$_GET['competition']) && !$cardsSection->getEnabled(@$_GET['competition'])) ? $this->showGames($participant, $edit, $post, false) : '');
        $replaceArr['ROUND_CONTENT'] = ($knockoutSection->getEnabled(@$_GET['competition']) ? $this->showRounds($participant, $edit, $post, false) : '');
        $replaceArr['QUESTION_CONTENT'] = ($questionSection->getEnabled(@$_GET['competition']) ? $this->showQuestions($participant, $edit, $post, false) : '');
        $replaceArr['COM_NAME'] = '{LANG_PREDICTIONS}';
        $replaceArr['PREDICTION_MSG'] = $msg . '<br />' . (!$submission_date_expired ? 'Inschrijfgeld kan betaald worden via de volgende betaallink: <a href="' . App::$_CONF->getValue('PAYMENT_LINK') . '" target="new">klik hier om te betalen</a>.<br />' : '');
        $replaceArr['SUBMISSION_MSG'] = ((@$edit && $submission_date_expired && $participant->getSubscribed(@$_GET['competition']) == 0 && UserControl::getCurrentUserGroup()->getId() != 1) ? '{LANG_SUBMISSION_EXPIRED}' : '');
        $replaceArr['ERROR_MSG'] = ((@$edit && $edit instanceof InputException) ? $edit->getMessage() : '');
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['PREDICTION_EDIT'] = (!@$edit && @$user && $participant->getId() == $user->getId() && !$submission_date_expired ? '<img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_PREDICTION} {LANG_EDIT}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$participant->getId().'" class="button">{LANG_PREDICTION} {LANG_EDIT}</a><br /><br />' : '');        
        $subscribe = ($participant->getSubscribed(@$_GET['competition']) == 0 ? '<input class="btn btn-primary" type="submit" name="subscribe" value="{LANG_SUBSCRIBE}" />' : '');
        $replaceArr['PREDICTION_BUTTONS'] = ($edit && (($participant->getSubscribed(@$_GET['competition']) == 0 && !$submission_date_expired) || UserControl::getCurrentUserGroup()->getId() == 1) ? '<input class="btn btn-primary" type="submit" name="save" value="{LANG_SAVE_PREDICTION}" /> '.$subscribe.'' : '');

        $tpl->replace($replaceArr);
        echo $tpl;

    } // showPredictions

    private function showSubscribeConfirmation()
    {
        $tpl = new Template('subscribe_confirmation', strtolower(get_class()), 'modules');

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_PREDICTIONS}';
        $replaceArr['CONFIRMATION_MESSAGE'] = '{LANG_CONFIRMATION_MESSAGE}';
        $replaceArr['PREDICTION_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showSubscribeConfirmation    
    
    private function showGames($participant, $edit, $post, $hideAnwsers) 
    {
        $userGroupId = UserControl::getCurrentUserGroup()->getId();
        $subscribed = ($participant->getSubscribed(@$_GET['competition']) || Competition::checkSubmissionDateExpired(@$_GET['competition'], time()));

        $resultSection = new Section(Section::$_SECTION_RESULTS);
        $cardsSection = new Section(Section::$_SECTION_CARDS);
        
        Game::getAllGames(@$_GET['competition']);

        $c = 0;
        $content  = '<h3>{LANG_POULE}</h3>'."\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th style="width: 80px;">{LANG_DATE}</th>'."\n";
        $content .= '<th>{LANG_CITY}</th>'."\n";
        $content .= '<th>{LANG_POULE}</th>'."\n";
        $content .= '<th colspan="2"></th>'."\n";
        $content .= '<th style="width: 30px;"></th>'."\n";
        $content .= '<th colspan="2"></th>'."\n";
        if ($resultSection->getEnabled(@$_GET['competition']))
            $content .= '<th style="width: 130px;">{LANG_PREDICTION}</th>'."\n";
        if ($cardsSection->getEnabled(@$_GET['competition']))
        {   
            $content .= '<th colspan="2"><img src="' . App::$_CONF->getValue('DOMAIN') . 'templates/{TEMPLATE_NAME}/images/yellow_card.jpg" alt="{LANG_YELLOW_CARDS}" /></th>'."\n";
            $content .= '<th colspan="2"><img src="' . App::$_CONF->getValue('DOMAIN') . 'templates/{TEMPLATE_NAME}/images/red_card.jpg" alt="{LANG_RED_CARDS}" /></th>'."\n";
        }
        $content .= '</tr>'."\n";
        while (($game = Game::nextGame()) != null)
        {
            if ($edit && $edit instanceof InputException)
            {
                //the post went wrong, get previous values
                $predictionResult[0] = $post['games'][$game->game_id]['home'];
                $predictionResult[1] = $post['games'][$game->game_id]['away'];
                $predictionYellowCards = $post['games'][$game->game_id]['yellowcards'];
                $predictionRedCards = $post['games'][$game->game_id]['redcards'];
            }
            else
            {
                //on edit read all values from db
                $gamePrediction = new GamePrediction($participant->getId(), $game->game_id);
                $predictionResult = explode('-', $gamePrediction->getResult());
                $predictionYellowCards = $gamePrediction->getYellowCards();
                $predictionRedCards = $gamePrediction->getRedCards();
            }
                    
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $game->game_date . '</td>' . "\n";
            $content .= '<td>' . $game->city_name . '</td>' . "\n";
            $content .= '<td>' . $game->poule_name . '</td>' . "\n";
            $content .= '<td>' . '<img src="' . App::$_CONF->getValue('DOMAIN') . ''.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->home_country_flag.'" width="16" alt="'.$game->home_country_name.'" class="actions" /></td>' . "\n";
            $content .= '<td>' . $game->home_country_name . '</td>' . "\n";
            
            if ($edit || $game->game_result == 'empty-empty' || $hideAnwsers)
                $content .= '<td style="color: green;">-</td>' . "\n";
            else
                $content .= '<td style="color: green;">'.$game->game_result.'</td>' . "\n";
            $content .= '<td>' . '<img src="' . App::$_CONF->getValue('DOMAIN') . ''.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->away_country_flag.'" width="16" alt="'.$game->away_country_name.'" class="actions" /></td>' . "\n";
            $content .= '<td>' . $game->away_country_name . '</td>' . "\n";
        
            if ($resultSection->getEnabled(@$_GET['competition']))
            {
                if ($edit)
                {
                    $content .= '<td><select name="gamepredictionhome_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[0] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select>-' . "\n";
                    $content .= '<select name="gamepredictionaway_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[1] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select></td>' . "\n";
                }
                else
                    $content .= '<td>'.$predictionResult[0].'-'.$predictionResult[1].'</td>';
            }
            if ($cardsSection->getEnabled(@$_GET['competition'])) 
            {
                if ($edit)
                {
                    $content .= '<td><select name="gamepredictionyellowcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionYellowCards == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select></td>' . "\n";            
                    $content .= '<td></td>' . "\n";
                    $content .= '<td><select name="gamepredictionredcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionRedCards == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select></td>' . "\n";   

                    $content .= '<td></td>' . "\n";
                }
                else
                {
                    $content .= '<td>'.$predictionYellowCards.'</td>'."\n";
                    $content .= ($game->game_yellow_cards != 'empty' && !$hideAnwsers ? '<td style="color: green;">(' . $game->game_yellow_cards . ')</td>' . "\n" : '<td></td>');
                    $content .= '<td>'.$predictionRedCards.'</td>'."\n";
                    $content .= ($game->game_red_cards != 'empty' && !$hideAnwsers ? '<td style="color: green;">(' . $game->game_red_cards . ')</td>' . "\n" : '<td></td>');
                }
            }
            $content .= '</tr>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="13">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        $content .= '</table><br /><br />'."\n";
        
        return $content;
    }
    
    private function showRounds($participant, $edit, $post, $hideAnwsers) 
    {
        $userGroupId = UserControl::getCurrentUserGroup()->getId();
        $subscribed = ($participant->getSubscribed(@$_GET['competition']) || Competition::checkSubmissionDateExpired(@$_GET['competition'], time()));

        Round::getAllRounds(@$_GET['competition']);

        $content = '';
        while (($round = Round::nextRound()) != null)
        {
            $content .= '<h3>'.$round->round_name.'</h3>';

            $currentClass = 'odd';
            $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>'."\n";

            if (!$edit)
                $prediction = '{LANG_PREDICTION}: ';

            for ($i=0; $i<$round->round_count; $i++) 
            {
                if ($edit && $edit instanceof InputException)
                {
                    //the post went wrong, get previous values
                    $predictionCountry = $post['rounds'][$round->round_id][$i];
                }
                else
                {
                    //on edit read all values from db
                    $roundPrediction = new RoundPrediction($participant->getId(), $round->round_id, $i);
                    $country = $roundPrediction->getCountry();
                    if ($country != null)
                        $predictionCountry = $country->getId();
                    else 
                        $predictionCountry = 0;
                }
                
                if ($edit)
                {
                    $content .= '<select name="roundprediction_'.$round->round_id.'_'.$i.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').' ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'round_'.$round->round_id.'') ? 'class="error" ' : ' ') . '>' . "\n";
                    $content .= '<option value="0" ' . (@$edit && ($predictionCountry == 0) ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    
                    Country::getAllCountries(@$_GET['competition']);
                    while (($country = Country::nextCountry()) != null)
                    {
                        $content .= '<option value="' . $country->country_id . '" ' . (@$edit && ($predictionCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
                    }
                    $content .= '</select>' . "\n";  
                    if (!(($i+1)%3))
                        $content .= '<br />'."\n";
                }
                else 
                {
                    if ($predictionCountry != 0)
                    {
                        $country = new Country($predictionCountry);
                        $prediction .= ($i>0 ? ', ' : ''). $country->getName();
                    }
                }
            }
            if (!$edit)
                $content .= $prediction."<br />\n";

            if (!$edit && !$hideAnwsers)
            {
                $answer = '<div style="color: green;">{LANG_GAME_ANWSER}: ';
                RoundResult::getAllRoundResults($round->round_id);
                $i=0;
                while (($roundResult = RoundResult::nextRoundResult()) != null)
                {
                    if ($roundResult->Country_country_id > 0)
                    {
                        $country = new Country($roundResult->Country_country_id);
                        $answer .= ($i>0 ? ', ' : ''). $country->getName();
                        $i++;
                    }
                }
                $content .= $answer.'</div>'."\n";
            }
            $content .= '</td>'."\n";
            $content .= '</tr>'."\n";
            $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $round->round_count . '</td></tr>' . "\n";            
            $content .= '</table><br /><br />'."\n";
        }
        
        return $content;
    }
    
    private function showQuestions($participant, $edit, $post, $hideAnwsers) 
    {
        $userGroupId = UserControl::getCurrentUserGroup()->getId();        
        $subscribed = ($participant->getSubscribed(@$_GET['competition']) || Competition::checkSubmissionDateExpired(@$_GET['competition'], time()));

        Question::getAllQuestions(@$_GET['competition']);

        $c = 0;
        $content  = '<h3>{LANG_QUESTIONS}</h3>'."\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th>{LANG_QUESTION}</th>'."\n";
        $content .= '<th>{LANG_PREDICTION}</th>'."\n";
        if (!$edit && !$hideAnwsers)
            $content .= '<th>{LANG_QUESTION_ANWSER}</th>'."\n";
        $content .= '</tr>'."\n";
        while (($question = Question::nextQuestion()) != null)
        {
            if ($edit && $edit instanceof InputException)
            {
                //the post went wrong, get previous values
                $predictionQuestion = $post['questions'][$question->question_id];
            }
            else
            {
                //on edit read all values from db
                $questionPrediction = new QuestionPrediction($participant->getId(), $question->question_id);
                $predictionQuestion = $questionPrediction->getAnswer();
            }
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $question->question_question . '</td>' . "\n";
                        
            if ($edit)
            {
                $content .= '<td><select name="questionanwser_'.$question->question_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').' ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'question_'.$question->question_id.'') ? 'class="error" ' : ' ') . '>' . "\n";
            
                switch ($question->question_type) 
                {
                    case 'yesno':
                        $content .= '<option value="empty" ' . (@$edit && ($predictionQuestion == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                        $content .= '<option value="1" ' . (@$edit && ($predictionQuestion == "1") ? ' selected' : '') . '>{LANG_YES}</option>' . "\n";
                        $content .= '<option value="0" ' . (@$edit && ($predictionQuestion == "0") ? ' selected' : '') . '>{LANG_NO}</option>' . "\n";
                        break;
                    case 'country':
                        $content .= '<option value="empty" ' . (@$edit && ($predictionQuestion == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                        Country::getAllCountries(@$_GET['competition']);
                        while (($country = Country::nextCountry()) != null)
                        {
                            $content .= '<option value="' . $country->country_name . '"' . (@$edit && ($predictionQuestion == $country->country_name) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
                        }
                        break;
                    case 'referee':
                        $content .= '<option value="empty" ' . (@$edit && ($predictionQuestion == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                        Referee::getAllReferees(@$_GET['competition']);
                        while (($referee = Referee::nextReferee()) != null)
                        {
                            $content .= '<option value="' . $referee->referee_name . '"' . (@$edit && ($predictionQuestion == $referee->referee_name) ? ' selected' : '') . '>' . $referee->referee_name . '</option>' . "\n";
                        }
                        break;
                    case 'player':
                    case 'dutch_player':
                        $content .= '<option value="empty" ' . (@$edit && ($predictionQuestion == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                        if ($question->question_type == 'dutch_player')
                            Country::getCountriesByName(App::$_LANG->getValue('LANG_NETHERLANDS'));
                        else
                            Country::getAllCountries(@$_GET['competition']);
                    
                        while (($country = Country::nextCountry()) != null)
                        {
                            $content .= '<optgroup label="'.$country->country_name.'">';
                            Player::getAllPlayers(@$_GET['competition'], $country->country_id);
                            while (($player = Player::nextPlayer()) != null)
                            {
                                $content .= '<option value="' . $player->player_name . '"' . (@$edit && ($predictionQuestion == $player->player_name) ? ' selected' : '') . '>' . $player->player_name . '</option>' . "\n";
                            }
                            $content .= '</optgroup>';
                        }
                        break;
                    case 'number':
                        $content .= '<option value="empty" ' . (@$edit && ($predictionQuestion == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_QUESTION'); $i++)
                        {
                            $anwser = "" . $i;
                            $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionQuestion == $anwser) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                        }
                        break;
                    default:
                        $content .= '{LANG_QUESTIONTYPE_ERROR}';
                }
                $content .= '</select></td>' . "\n";
            }
            else
            {
                switch ($question->question_type) 
                {
                    case 'yesno':
                        if ($predictionQuestion == "0")
                            $content .= '<td>{LANG_NO}</td>'."\n";
                        else if ($predictionQuestion == "1")
                            $content .= '<td>{LANG_YES}</td>'."\n";
                        else
                            $content .= '<td>&nbsp;</td>'."\n";
                        if (!$hideAnwsers)
                        {
                            if ($question->question_anwser == "0")
                                $content .= '<td style="color: green;">{LANG_NO}</td>'."\n";
                            else if ($question->question_anwser == "1")
                                $content .= '<td style="color: green;">{LANG_YES}</td>'."\n";
                            else
                                $content .= '<td>&nbsp;</td>'."\n";
                        }
                        break;
                    case 'country':
                    case 'player':
                    case 'dutch_player':
                    case 'referee':
                    case 'number':
                        if ($predictionQuestion == 'empty')
                            $content .= '<td>{LANG_EMPTY}</td>'."\n";
                        else
                            $content .= '<td>'.$predictionQuestion.'</td>'."\n";
                            
                        if (!$hideAnwsers)
                            $content .= '<td style="color: green;">'.(strstr($question->question_anwser, 'empty') === false ? $question->question_anwser : '').'</td>'."\n";
                        break;
                }
            }
            $content .= '</td>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        $content .= '</tr>' . "\n";
        $content .= '</table><br />' . "\n";
        
        return $content;
    }

    private function doEditPrediction()
    {
        $userGroupId = UserControl::getCurrentUser()->getUserGroup()->getId();        
        $participant = new Participant(@$_GET['id']);
        
        if (($participant->getSubscribed(@$_GET['competition']) || Competition::checkSubmissionDateExpired(@$_GET['competition'], time())) && $userGroupId != 1) 
            throw new Exception("{ERROR_ACCESSDENIED}");
       
        $post = $this->parse(@$_POST);
        
        if (isset($_POST['subscribe']))
        {
            foreach ($post['rounds'] as $roundId => $predictions)
            {
                $haystack = array();
                foreach ($predictions as $predictionId => $prediction) {
                    if (in_array($prediction, $haystack))
                        throw new InputException('{ERROR_DUPLICATE_COUNTRY}', 'round_'.$roundId);

                    array_push($haystack, $prediction);
                }
            }
            
            foreach ($post['questions'] as $questionId => $prediction)
            {
                if ($prediction == 'empty')
                    throw new InputException('{ERROR_QUESTION_EMPTY}', 'question_'.$questionId);
            }
        }
         
        $games = $post['games'];
        foreach ($games as $gameId => $prediction)
        {
            $gamePrediction = new GamePrediction(@$_GET['id'], $gameId);
            $gamePrediction->setResult($games[$gameId]['home'].'-'.$games[$gameId]['away']);
            $gamePrediction->setYellowCards($games[$gameId]['yellowcards']);
            $gamePrediction->setRedCards($games[$gameId]['redcards']);
            $gamePrediction->save();
        }        
        
        $rounds = $post['rounds'];
        foreach ($rounds as $roundId => $predictions)
        {
            foreach ($predictions as $predictionId => $prediction) {
               $roundPrediction = new RoundPrediction(@$_GET['id'], $roundId, $predictionId);
               $roundPrediction->setCountry($prediction);
               $roundPrediction->save();
            }
        }
        
        $questions = $post['questions'];
        foreach ($questions as $questionId => $prediction)
        {
            $questionPrediction = new QuestionPrediction(@$_GET['id'], $questionId);
            $questionPrediction->setAnswer($prediction);
            $questionPrediction->save();
        }     
        $participant->save();
                
        return true;
    } //doEditPrediction

    private function doSubcribePrediction()
    {
        $userGroupId = UserControl::getCurrentUser()->getUserGroup()->getId();        
        $participant = new Participant(@$_GET['id']);
        $participant->setSubscribed(@$_GET['competition'], 1);
        $participant->save();
        
        Ranking::updateRanking(@$_GET['competition']);
        
        if (App::$_CONF->getValue('TEST_MODE') != 'true')
            $this->sendPouleByMail($participant);
        
        return true;
    }
    
    private function parse()
    {
        $post = array();

        $games = array();
        $rounds = array();
        $questions = array();
        
        foreach (@$_POST as $key => $value)
        {
            $key = explode('_', $key);
            switch ($key[0])
            {
                case 'gamepredictionhome':
                    $games[$key[1]]['home'] = $value;
                    break;
                case 'gamepredictionaway':
                    $games[$key[1]]['away'] = $value;
                    break;
                case 'gamepredictionyellowcards':
                    $games[$key[1]]['yellowcards'] = $value;
                    break;
                case 'gamepredictionredcards':
                    $games[$key[1]]['redcards'] = $value;
                    break;
                case 'roundprediction':
                    $rounds[$key[1]][$key[2]] = $value;
                    break;
                case 'questionanwser':
                    $questions[$key[1]] = $value;
                    break;
                default:
                    break;
            }
        }
        
        $post['games'] = $games;
        $post['rounds'] = $rounds;
        $post['questions'] = $questions;
        
        return $post;
    }
    
    private function sendPouleByMail($participant)
    {
        $tpl = new Template('prediction_mail', strtolower(get_class()), 'modules');
        
        $resultSection = new Section(Section::$_SECTION_RESULTS);
        $cardsSection = new Section(Section::$_SECTION_CARDS);
        $knockoutSection = new Section(Section::$_SECTION_KNOCK_OUT_FASE);
        $questionSection = new Section(Section::$_SECTION_QUESTIONS);
        
        $header = 'From: ' . App::$_CONF->getValue('MAIL') . "\n";
        $header .= 'Cc: ' . App::$_CONF->getValue('MAIL') . "\n";
        $header .= 'MIME-Version: 1.0' . "\n";
		$header .= 'Content-Type: text/html; charset=utf-8' . "\n";
		$header .= 'X-Priority: 3' . "\n";
		$header .= 'X-MSMail-Priority: Normal' . "\n";
		$header .= 'X-Mailer: PHP / ' . phpversion() . "\n";
		$subject = '' . App::$_LANG->getValue('LANG_SUBSCRIPTION') . ''."\n";
        
        $replaceArr = array();
        $replaceArr['USER_CONTENT'] = Users::showUser($participant->getId());
        $replaceArr['GAME_CONTENT'] = (!(!$resultSection->getEnabled(@$_GET['competition']) && !$cardsSection->getEnabled(@$_GET['competition'])) ? $this->showGames($participant, false, array(), true) : '');
        $replaceArr['ROUND_CONTENT'] = ($knockoutSection->getEnabled(@$_GET['competition']) ? $this->showRounds($participant, false, array(), true) : '');
        $replaceArr['QUESTION_CONTENT'] = ($questionSection->getEnabled(@$_GET['competition']) ? $this->showQuestions($participant, false, array(), true) : '');

        $replaceArr['TEMPLATE_NAME'] = App::$_CONF->getValue('TEMPLATE');

        $replaceArr = array_merge($replaceArr, App::$_LANG->toArray());
        $tpl->replace($replaceArr);
        
		if(mail($participant->getEmail(), $subject, $tpl->__toString(), $header))
          return true;

        return false;
    } // sendPouleByMail
    
} // Predictions

?>