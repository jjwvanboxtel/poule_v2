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
                            $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}', true);
                        else
                        {
                            if (isset($_POST['subscribe']))
                            {
                                $this->showSubscribeConfirmation();
                            }
                            else
                            {
                                $msg = '{LANG_EDIT_OK}';
                                $this->showPrediction('{LANG_PREDICTION} ' . $msg, true);
                            }
                        }
                    }
                    else if (isset($_POST['subscribe_confirmation']))
                    {
                        if (!$this->doSubcribePrediction())
                            $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}', true);
                        else
                            $this->showPrediction('{LANG_PREDICTION} {LANG_SUBSCRIBE_OK}', true);
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
                    $this->showPrediction('{LANG_PREDICTION} {ERROR_EDIT}: ' . $ex->getMessage(), true);
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
                $cells  = '<td><img alt="{LANG_USERGROUP}" src="templates/{TEMPLATE_NAME}/icons/'.($user->user_enabled ? 'user' : 'user_red').'.png" class="icon" /></td>' . "\n";
                $cells .= '<td>' . $user->user_id . '</td>' . "\n";
                $cells .= '<td><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com=' . $this->componentId . '&id=' . $user->user_id . '">' . $user->user_firstname . ' ' . $user->user_lastname . '</a></td>' . "\n";
                $cells .= '<td>' . "\n";
                if ($this->hasAccess(CRUD_EDIT))
                {
                    $cells .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$user->user_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_USER} {LANG_EDIT}" class="actions" /></a>' . "\n";
                }
                $cells .= '</td>' . "\n";
                $content .= self::buildOverviewRow($cells, $c);
                $c++;
            }
        }

        $content .= '<tr><td colspan="4">{LANG_USER_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_PREDICTIONS}';
        $replaceArr['USER_MSG'] = $msg !== '' ? self::buildMsgWrapper($msg) : '';
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showUsers

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

        $editLink = '';
        if (!@$edit && @$user && $participant->getId() == $user->getId() && !$submission_date_expired) {
            $editHref = '?' . (@$_GET['competition'] ? 'competition=' . @$_GET['competition'] . '&amp;' : '')
                      . 'com=' . $this->componentId . '&amp;option=edit&amp;id=' . $participant->getId();
            $editLink = '<div class="mb-3"><a href="' . $editHref . '" class="btn btn-primary">'
                      . '<i class="bi bi-pencil-square me-1"></i>{LANG_PREDICTION} {LANG_EDIT}</a></div>';
        }

        $showButtons = $edit && (
            ($participant->getSubscribed(@$_GET['competition']) == 0 && !$submission_date_expired)
            || UserControl::getCurrentUserGroup()->getId() == 1
        );
        $subscribe = ($participant->getSubscribed(@$_GET['competition']) == 0
            ? '<input class="btn btn-outline-primary" type="submit" name="subscribe" value="{LANG_SUBSCRIBE}" />'
            : '');
        $predictionButtons = $showButtons
            ? '<div class="d-flex gap-2"><input class="btn btn-primary" type="submit" name="save" value="{LANG_SAVE_PREDICTION}" /> ' . $subscribe . '</div>'
            : '';

        $replaceArr = array();
        $replaceArr['USER_CONTENT'] = '<h3>{LANG_PARTICIPANT}: '.$participant->getFirstName().' ' .$participant->getLastName().'</h3>';
        $replaceArr['GAME_CONTENT'] = (!(!$resultSection->getEnabled(@$_GET['competition']) && !$cardsSection->getEnabled(@$_GET['competition'])) ? $this->showGames($participant, $edit, $post, false) : '');
        $replaceArr['ROUND_CONTENT'] = ($knockoutSection->getEnabled(@$_GET['competition']) ? $this->showRounds($participant, $edit, $post, false) : '');
        $replaceArr['QUESTION_CONTENT'] = ($questionSection->getEnabled(@$_GET['competition']) ? $this->showQuestions($participant, $edit, $post, false) : '');
        $replaceArr['COM_NAME'] = '{LANG_PREDICTIONS}';
        $replaceArr['PREDICTION_MSG'] = ($msg !== '' ? self::buildMsgWrapper(trim($msg)) : '');
        $replaceArr['SUBMISSION_MSG'] = ((@$edit && $submission_date_expired && $participant->getSubscribed(@$_GET['competition']) == 0 && UserControl::getCurrentUserGroup()->getId() != 1) ? '<div class="alert alert-warning">{LANG_SUBMISSION_EXPIRED}</div>' : '');
        $replaceArr['ERROR_MSG'] = ((@$edit && $edit instanceof InputException) ? '<div class="alert alert-danger">' . $edit->getMessage() . '</div>' : '');
        $replaceArr['PAYMENT_MSG'] = (!$submission_date_expired ? '<div class="alert alert-info">Inschrijfgeld kan betaald worden via de volgende betaallink: <a href="' . App::$_CONF->getValue('PAYMENT_LINK') . '" target="new">klik hier om te betalen</a>.</div>' : '');
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['PREDICTION_EDIT'] = $editLink;
        $replaceArr['PREDICTION_BUTTONS'] = $predictionButtons;

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
        
        // Desktop table view (hidden on mobile)
        $content .= '<div class="table-responsive d-none d-md-block">'."\n";
        $content .= '<table class="list">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th style="width: 120px;">{LANG_DATE}</th>'."\n";
        $content .= '<th>{LANG_CITY}</th>'."\n";
        $content .= '<th>{LANG_POULE}</th>'."\n";
        $content .= '<th colspan="2"></th>'."\n";
        $content .= '<th style="width: 40px;"></th>'."\n";
        $content .= '<th colspan="2"></th>'."\n";
        if ($resultSection->getEnabled(@$_GET['competition']))
            $content .= '<th style="width: 200px;">{LANG_PREDICTION}</th>'."\n";
        if ($cardsSection->getEnabled(@$_GET['competition']))
        {   
            $content .= '<th colspan="2"><i class="bi bi-square-fill card-yellow" aria-label="{LANG_YELLOW_CARDS}"></i></th>'."\n";
            $content .= '<th colspan="2"><i class="bi bi-square-fill card-red" aria-label="{LANG_RED_CARDS}"></i></th>'."\n";
        }
        $content .= '</tr>'."\n";
        
        // Store games for both views
        $games = array();
        while (($game = Game::nextGame()) != null)
        {
            $games[] = $game;
            
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
            $content .= '<td>' . '<img src="./'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->home_country_flag.'" width="16" alt="'.$game->home_country_name.'" class="actions" /></td>' . "\n";
            $content .= '<td>' . $game->home_country_name . '</td>' . "\n";
            
            if ($edit || $game->game_result == 'empty-empty' || $hideAnwsers)
                $content .= '<td class="text-success">-</td>' . "\n";
            else
                $content .= '<td class="text-success">'.$game->game_result.'</td>' . "\n";
            $content .= '<td>' . '<img src="./'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->away_country_flag.'" width="16" alt="'.$game->away_country_name.'" class="actions" /></td>' . "\n";
            $content .= '<td>' . $game->away_country_name . '</td>' . "\n";
        
            if ($resultSection->getEnabled(@$_GET['competition']))
            {
                if ($edit)
                {
                    $content .= '<td class="text-center"><div class="d-flex align-items-center justify-content-center gap-2"><select class="form-select select-score" name="gamepredictionhome_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[0] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select><span>-</span>' . "\n";
                    $content .= '<select class="form-select select-score" name="gamepredictionaway_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[1] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select></div></td>' . "\n";
                }
                else
                    $content .= '<td>'.$predictionResult[0].'-'.$predictionResult[1].'</td>';
            }
            if ($cardsSection->getEnabled(@$_GET['competition'])) 
            {
                if ($edit)
                {
                    $content .= '<td><select class="form-select select-score" name="gamepredictionyellowcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
                    {
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionYellowCards == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    $content .= '</select></td>' . "\n";            
                    $content .= '<td></td>' . "\n";
                    $content .= '<td><select class="form-select select-score" name="gamepredictionredcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
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
                    $content .= ($game->game_yellow_cards != 'empty' && !$hideAnwsers ? '<td class="text-success">(' . $game->game_yellow_cards . ')</td>' . "\n" : '<td></td>');
                    $content .= '<td>'.$predictionRedCards.'</td>'."\n";
                    $content .= ($game->game_red_cards != 'empty' && !$hideAnwsers ? '<td class="text-success">(' . $game->game_red_cards . ')</td>' . "\n" : '<td></td>');
                }
            }
            $content .= '</tr>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="13">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        $content .= '</table>'."\n";
        $content .= '</div>'."\n";
        
        // Mobile card view (hidden on desktop)
        $content .= '<div class="d-md-none">'."\n";
        $c = 0;
        foreach ($games as $game) {
            if ($edit && $edit instanceof InputException)
            {
                $predictionResult[0] = $post['games'][$game->game_id]['home'];
                $predictionResult[1] = $post['games'][$game->game_id]['away'];
                $predictionYellowCards = $post['games'][$game->game_id]['yellowcards'];
                $predictionRedCards = $post['games'][$game->game_id]['redcards'];
            }
            else
            {
                $gamePrediction = new GamePrediction($participant->getId(), $game->game_id);
                $predictionResult = explode('-', $gamePrediction->getResult());
                $predictionYellowCards = $gamePrediction->getYellowCards();
                $predictionRedCards = $gamePrediction->getRedCards();
            }
            
            $content .= '<div class="card mb-3">'."\n";
            $content .= '<div class="card-body">'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_DATE}</small><br/>'.$game->game_date.'</div>'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_POULE}</small><br/>'.$game->poule_name.'</div>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-12"><small class="text-muted">{LANG_CITY}</small><br/>'.$game->city_name.'</div>'."\n";
            $content .= '</div>'."\n";
            
            // Countries
            $content .= '<div class="row mb-3">'."\n";
            $content .= '<div class="col-5 text-end">'."\n";
            $content .= '<img src="./'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->home_country_flag.'" width="24" alt="'.$game->home_country_name.'" class="me-2" />'."\n";
            $content .= '<strong>'.$game->home_country_name.'</strong>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="col-2 text-center">'."\n";
            if ($edit || $game->game_result == 'empty-empty' || $hideAnwsers)
                $content .= '<span class="text-success">-</span>'."\n";
            else
                $content .= '<span class="text-success">'.$game->game_result.'</span>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="col-5">'."\n";
            $content .= '<img src="./'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$game->away_country_flag.'" width="24" alt="'.$game->away_country_name.'" class="me-2" />'."\n";
            $content .= '<strong>'.$game->away_country_name.'</strong>'."\n";
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            
            // Prediction and Cards on one line for mobile
            $showPrediction = $resultSection->getEnabled(@$_GET['competition']);
            $showCards = $cardsSection->getEnabled(@$_GET['competition']);
            
            if ($showPrediction || $showCards)
            {
                if ($edit)
                {
                    $content .= '<div class="row">'."\n";
                    $content .= '<div class="col-12">'."\n";
                    $content .= '<div class="d-flex flex-wrap align-items-center gap-3">'."\n";
                    
                    // Prediction
                    if ($showPrediction)
                    {
                        $content .= '<div class="d-flex align-items-center gap-2">'."\n";
                        $content .= '<strong class="text-nowrap">{LANG_PREDICTION}:</strong>'."\n";
                        $content .= '<select class="form-select form-select-sm select-score" name="gamepredictionhome_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                        {
                            $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[0] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                        }
                        $content .= '</select>'."\n";
                        $content .= '<span>-</span>'."\n";
                        $content .= '<select class="form-select form-select-sm select-score" name="gamepredictionaway_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_RESULT'); $i++)
                        {
                            $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionResult[1] == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                        }
                        $content .= '</select>'."\n";
                        $content .= '</div>'."\n";
                    }
                    
                    // Cards
                    if ($showCards)
                    {
                        $content .= '<div class="d-flex align-items-center gap-2">'."\n";
                        $content .= '<div class="d-flex align-items-center gap-1">'."\n";
                        $content .= '<i class="bi bi-square-fill card-yellow" aria-label="{LANG_YELLOW_CARDS}"></i>'."\n";
                        $content .= '<select class="form-select form-select-sm select-score" name="gamepredictionyellowcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
                        {
                            $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionYellowCards == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                        }
                        $content .= '</select>'."\n";
                        $content .= '</div>'."\n";
                        $content .= '<div class="d-flex align-items-center gap-1">'."\n";
                        $content .= '<i class="bi bi-square-fill card-red" aria-label="{LANG_RED_CARDS}"></i>'."\n";
                        $content .= '<select class="form-select form-select-sm select-score" name="gamepredictionredcards_'.$game->game_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').'>' . "\n";
                        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_GAME_CARDS'); $i++)
                        {
                            $content .= '<option value="' . $i . '" ' . (@$edit && ($predictionRedCards == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                        }
                        $content .= '</select>'."\n";
                        $content .= '</div>'."\n";
                        $content .= '</div>'."\n";
                    }
                    
                    $content .= '</div>'."\n";
                    $content .= '</div>'."\n";
                    $content .= '</div>'."\n";
                }
                else
                {
                    $content .= '<div class="row">'."\n";
                    $content .= '<div class="col-12">'."\n";
                    $content .= '<div class="d-flex flex-wrap align-items-center gap-3">'."\n";
                    
                    if ($showPrediction)
                    {
                        $content .= '<div><strong>{LANG_PREDICTION}:</strong> '.$predictionResult[0].' - '.$predictionResult[1].'</div>'."\n";
                    }
                    
                    if ($showCards)
                    {
                        $content .= '<div class="d-flex gap-3">'."\n";
                        $content .= '<div>'."\n";
                        $content .= '<i class="bi bi-square-fill card-yellow"></i> '.$predictionYellowCards;
                        $content .= ($game->game_yellow_cards != 'empty' && !$hideAnwsers ? ' <span class="text-success">(' . $game->game_yellow_cards . ')</span>' : '');
                        $content .= '</div>'."\n";
                        $content .= '<div>'."\n";
                        $content .= '<i class="bi bi-square-fill card-red"></i> '.$predictionRedCards;
                        $content .= ($game->game_red_cards != 'empty' && !$hideAnwsers ? ' <span class="text-success">(' . $game->game_red_cards . ')</span>' : '');
                        $content .= '</div>'."\n";
                        $content .= '</div>'."\n";
                    }
                    
                    $content .= '</div>'."\n";
                    $content .= '</div>'."\n";
                    $content .= '</div>'."\n";
                }
            }
            
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            $c++;
        }
        $content .= '<div class="text-muted mt-2">{LANG_COUNT}: ' . $c . '</div>'."\n";
        $content .= '</div>'."\n";

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
            $content .= '<table class="list">'."\n";
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
                    $content .= '<select class="form-select" name="roundprediction_'.$round->round_id.'_'.$i.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').' ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'round_'.$round->round_id.'') ? 'class="error" ' : ' ') . '>' . "\n";
                    $content .= '<option value="0" ' . (@$edit && ($predictionCountry == 0) ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    
                    Country::getAllCountries(@$_GET['competition']);
                    while (($country = Country::nextCountry()) != null)
                    {
                        $content .= '<option value="' . $country->country_id . '" ' . (@$edit && ($predictionCountry == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
                    }
                    $content .= '</select>' . "\n";  
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
                $answer = '<div class="text-success">{LANG_GAME_ANWSER}: ';
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
            $content .= '</table>'."\n";
        }
        
        return $content;
    }
    
    private function showQuestions($participant, $edit, $post, $hideAnwsers) 
    {
        $userGroupId = UserControl::getCurrentUserGroup()->getId();        
        $subscribed = ($participant->getSubscribed(@$_GET['competition']) || Competition::checkSubmissionDateExpired(@$_GET['competition'], time()));

        Question::getAllQuestions(@$_GET['competition']);

        // Store questions for both views
        $questions = array();
        while (($question = Question::nextQuestion()) != null)
        {
            $questions[] = $question;
        }

        $c = 0;
        $content  = '<h3>{LANG_QUESTIONS}</h3>'."\n";
        
        // Desktop table view
        $content .= '<div class="d-none d-md-block">'."\n";
        $content .= '<table class="list">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th>{LANG_QUESTION}</th>'."\n";
        $content .= '<th>{LANG_PREDICTION}</th>'."\n";
        if (!$edit && !$hideAnwsers)
            $content .= '<th>{LANG_QUESTION_ANWSER}</th>'."\n";
        $content .= '</tr>'."\n";
        foreach ($questions as $question)
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
                $content .= '<td><select class="form-select" name="questionanwser_'.$question->question_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').' ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'question_'.$question->question_id.'') ? 'class="error" ' : ' ') . '>' . "\n";
            
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
                                $content .= '<td class="text-success">{LANG_NO}</td>'."\n";
                            else if ($question->question_anwser == "1")
                                $content .= '<td class="text-success">{LANG_YES}</td>'."\n";
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
                            $content .= '<td class="text-success">'.(strstr($question->question_anwser, 'empty') === false ? $question->question_anwser : '').'</td>'."\n";
                        break;
                }
            }
            $content .= '</td>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        $content .= '</table>'."\n";
        $content .= '</div>'."\n";
        
        // Mobile card view
        $content .= '<div class="d-md-none">'."\n";
        $c = 0;
        foreach ($questions as $question)
        {
            if ($edit && $edit instanceof InputException)
            {
                $predictionQuestion = $post['questions'][$question->question_id];
            }
            else
            {
                $questionPrediction = new QuestionPrediction($participant->getId(), $question->question_id);
                $predictionQuestion = $questionPrediction->getAnswer();
            }
            
            $content .= '<div class="card mb-3">'."\n";
            $content .= '<div class="card-body">'."\n";
            $content .= '<h6 class="card-title">'.$question->question_question.'</h6>'."\n";
            
            if ($edit)
            {
                $content .= '<label class="form-label">{LANG_PREDICTION}</label>'."\n";
                $content .= '<select class="form-select" name="questionanwser_'.$question->question_id.'" '.($subscribed && $userGroupId != 1 ? 'disabled' : '').' ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'question_'.$question->question_id.'') ? 'class="error" ' : ' ') . '>' . "\n";
            
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
                $content .= '</select>'."\n";
            }
            else
            {
                $content .= '<div class="mb-2"><strong>{LANG_PREDICTION}:</strong> ';
                switch ($question->question_type) 
                {
                    case 'yesno':
                        if ($predictionQuestion == "0")
                            $content .= '{LANG_NO}';
                        else if ($predictionQuestion == "1")
                            $content .= '{LANG_YES}';
                        else
                            $content .= '{LANG_EMPTY}';
                        break;
                    case 'country':
                    case 'player':
                    case 'dutch_player':
                    case 'referee':
                    case 'number':
                        if ($predictionQuestion == 'empty')
                            $content .= '{LANG_EMPTY}';
                        else
                            $content .= $predictionQuestion;
                        break;
                }
                $content .= '</div>'."\n";
                
                if (!$hideAnwsers)
                {
                    $content .= '<div class="text-success"><strong>{LANG_QUESTION_ANWSER}:</strong> ';
                    switch ($question->question_type) 
                    {
                        case 'yesno':
                            if ($question->question_anwser == "0")
                                $content .= '{LANG_NO}';
                            else if ($question->question_anwser == "1")
                                $content .= '{LANG_YES}';
                            break;
                        default:
                            $content .= (strstr($question->question_anwser, 'empty') === false ? $question->question_anwser : '');
                    }
                    $content .= '</div>'."\n";
                }
            }
            
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            $c++;
        }
        $content .= '<div class="text-muted mt-2">{LANG_COUNT}: ' . $c . '</div>'."\n";
        $content .= '</div>'."\n";

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