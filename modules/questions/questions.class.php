<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the questions pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Questions extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Question', 'modules/questions');
        App::openClass('Country', 'modules/countries');
        App::openClass('Referee', 'modules/referees');
        App::openClass('Player', 'modules/players');
        App::openClass('Ranking', 'modules/table');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showQuestions();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditQuestion();
                        $this->showQuestions('<div id="msg">{LANG_QUESTION} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditQuestion($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showQuestions('<div>{LANG_QUESTION} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditQuestion();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $question = new Question($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditQuestion($question))
                          $this->showQuestions('<div>{LANG_QUESTION} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showQuestions('<div>{LANG_QUESTION} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditQuestion(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditQuestion($iex);
                }
                catch (Exception $ex)
                {
                    $this->showQuestions('<div>{LANG_QUESTION} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'editanwser':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $question = new Question($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditAnwser($question))
                          $this->showQuestions('<div>{LANG_QUESTION} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showQuestions('<div>{LANG_QUESTION} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditAnwser(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditAnwser($iex);
                }
                catch (Exception $ex)
                {
                    $this->showEditAnwser('<div>{LANG_QUESTION} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Question::exists($_GET['id']))
                    {
                        $question = new Question($_GET['id']);

                        if (!$question->delete())
                          $this->showQuestions('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_QUESTION} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showQuestions('<div>{LANG_QUESTION} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showQuestions('<div>{LANG_QUESTION} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct
   
    private function showQuestions($msg='')
    {
        $tpl = new Template('question', strtolower(get_class()), 'modules');

        Question::getAllQuestions(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($question = Question::nextQuestion()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $question->question_id . '</td>' . "\n";
            $content .= '<td>' . $question->question_question . '</td>' . "\n";
            $content .= '<td>' . Question::$_TYPES[$question->question_type] . '</td>' . "\n";
            if ($question->question_type == 'yesno')
                if ($question->question_anwser == '1') 
                    $content .= '<td>{LANG_YES}</td>' . "\n";            
                else if ($question->question_anwser == '0') 
                    $content .= '<td>{LANG_NO}</td>' . "\n";            
                else
                    $content .= '<td></td>' . "\n";            
            else
                $content .= '<td>' . $question->question_anwser . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=editanwser&amp;id='.$question->question_id .'"><img src="templates/{TEMPLATE_NAME}/icons/award_star_add.png" alt="{LANG_QUESTION} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$question->question_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_QUESTION} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$question->question_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_QUESTION} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="5">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_QUESTIONS}';
        $replaceArr['QUESTION_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['QUESTION_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_QUESTION} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_QUESTION} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showQuestions

    private function showEditQuestion($edit=false)
    {
        $tpl = new Template('question_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Question::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $question = new Question(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$question != null)
        {
            //on edit read all values from db
            $questionQuestion = $question->getQuestion();
            $questionAnwser = $question->getAnwser();
            $questionType = $question->getType();
            $questionAnwserCount = $question->getAnwserCount();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $questionQuestion = @$_POST['questionquestion'];
            $questionAnwser = @$_POST['questionanswer'];
            $questionType = @$_POST['questiontype'];
            $questionAnwserCount = @$_POST['questionanwsercount'];
            
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
        $content .= '<tr><td valign="top">{LANG_QUESTION}:</td><td><textarea ' . (@$edit && !@$questionQuestion ? 'style="background-color: red;" ' : ' ') . 'cols="80" rows="10" name="questionquestion">' . (@$questionQuestion ? ''.@$questionQuestion.'' : '') . '</textarea></td></tr>' . "\n";
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_QUESTIONTYPE_NAME}:</td>' . "\n";
        $content .= '<td><select name="questiontype">' . "\n";
        foreach (Question::$_TYPES as $key => $value)
        {
            $content .= '<option value="' . $key . '" ' . (@$edit && ($questionType == $key) ? ' selected' : '') . '>' . $value . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_QUESTION_ANWSER_COUNT}:</td>' . "\n";
        $content .= '<td><select name="questionanwsercount">' . "\n";
        for ($i=1; $i<=App::$_CONF->getValue('MAX_SELECTION_QUESTION_ANWSER_COUNT'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($questionAnwserCount == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $replaceArr['QUESTION_TITLE'] = "{LANG_QUESTION} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['QUESTION_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $msg = isset($replaceArr['ERROR_MSG']) ? $replaceArr['ERROR_MSG'] : '';
        $msg = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $msg);
        $replaceArr['ERROR_MSG_WRAPPER'] = self::buildMsgWrapper(rtrim($msg));
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditQuestion

    private function doEditQuestion($question=false)
    {
        $fields = array('questionquestion');
        $status = false;

        if (strlen(@$_POST['questionquestion']) < 10)
          throw new InputException('{ERROR_TOO_SHORT} 10 {ERROR_CHARS}', 'questionquestion');
        
        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new question, else edit question
        if (!$question)
        {
            Question::add(@$_GET['competition'], $_POST['questionanwsercount'], $_POST['questionquestion'], $_POST['questiontype']);
        }
        else
        {       
            $question->setQuestion(@$_POST['questionquestion']);
            $question->setType(@$_POST['questiontype']);
            $question->setAnwserCount(@$_POST['questionanwsercount']);
            $question->save();
            $status = true;
        }
        
        return $status;
    } //doEditQuestion

    private function showEditAnwser($edit=false)
    {
        $tpl = new Template('question_add', strtolower(get_class()), 'modules');
        if (!@$_GET['id'] || !Question::exists(@$_GET['id']))
            throw new Exception("{ERROR_ITEMNOTEXIST}");

        $question = new Question(@$_GET['id']);

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //on edit read all values from db
        $questionAnwser = $question->getAnwser();

        $content .= '<tr><td valign="top">{LANG_QUESTION}:</td><td>'.$question->getQuestion().'</td></tr>' . "\n";
        for ($i=0; $i<$question->getAnwserCount(); $i++)
        {
            $content .= '<tr>' . "\n";
            $content .= '<td>{LANG_QUESTION_ANWSER}:</td>' . "\n";
            $content .= '<td><select name="questionanwser_'.$i.'">' . "\n";
            switch ($question->getType()) 
            {
                case 'yesno':
                    $content .= '<option value="empty" ' . (@$edit && ($questionAnwser[$i] == "empty" || $questionAnwser[$i] == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    $content .= '<option value="1" ' . (@$edit && ($questionAnwser[$i] == "1") ? ' selected' : '') . '>{LANG_YES}</option>' . "\n";
                    $content .= '<option value="0" ' . (@$edit && ($questionAnwser[$i] == "0") ? ' selected' : '') . '>{LANG_NO}</option>' . "\n";
                    break;
                case 'country':
                    $content .= '<option value="empty" ' . (@$edit && ($questionAnwser[$i] == "empty" || $questionAnwser[$i] == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    Country::getAllCountries(@$_GET['competition']);
                    while (($country = Country::nextCountry()) != null)
                    {
                        $content .= '<option value="' . $country->country_name . '"' . (@$edit && ($questionAnwser[$i] == $country->country_name) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
                    }
                    break;
                case 'referee':
                    $content .= '<option value="empty" ' . (@$edit && ($questionAnwser[$i] == "empty" || $questionAnwser[$i] == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    Referee::getAllReferees(@$_GET['competition']);
                    while (($referee = Referee::nextReferee()) != null)
                    {
                        $content .= '<option value="' . $referee->referee_name . '"' . (@$edit && ($questionAnwser[$i] == $referee->referee_name) ? ' selected' : '') . '>' . $referee->referee_name . '</option>' . "\n";
                    }
                    break;
                case 'player':
                case 'dutch_player':
                    $content .= '<option value="empty" ' . (@$edit && ($questionAnwser[$i] == "empty" || $questionAnwser[$i] == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    if ($question->getType() == 'dutch_player')
                        Country::getCountriesByName(App::$_LANG->getValue('LANG_NETHERLANDS'));
                    else
                        Country::getAllCountries(@$_GET['competition']);

                    while (($country = Country::nextCountry()) != null)
                    {
                        $content .= '<optgroup label="'.$country->country_name.'">';
                        Player::getAllPlayers(@$_GET['competition'], $country->country_id);
                        while (($player = Player::nextPlayer()) != null)
                        {
                            $content .= '<option value="' . $player->player_name . '"' . (@$edit && ($questionAnwser[$i] == $player->player_name) ? ' selected' : '') . '>' . $player->player_name . '</option>' . "\n";
                        }
                        $content .= '</optgroup>';
                    }
                    break;
                case 'number':
                    $content .= '<option value="empty" ' . (@$edit && ($questionAnwser[$i] == "empty" || $questionAnwser[$i] == "") ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
                    for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_QUESTION'); $i++)
                    {
                        $anwser = "" . $i;
                        $content .= '<option value="' . $i . '" ' . (@$edit && ($questionAnwser[$i] == $anwser) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
                    }
                    break;
                default:
                    $content .= '<td>{LANG_QUESTIONTYPE_ERROR}</td>';
            }
            $content .= '</select></td>' . "\n";
            $content .= '</tr>' . "\n";
        }
        
        $replaceArr['QUESTION_TITLE'] = "{LANG_QUESTION} {LANG_EDIT}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $replaceArr['QUESTION_COM_ID'] = $this->componentId;
        $msg = isset($replaceArr['ERROR_MSG']) ? $replaceArr['ERROR_MSG'] : '';
        $msg = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $msg);
        $replaceArr['ERROR_MSG_WRAPPER'] = self::buildMsgWrapper(rtrim($msg));
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditAnwser
    
    private function doEditAnwser($question=false)
    {    
        $anwsers = array();
        for ($i=0; $i<$question->getAnwserCount(); $i++)
        {
            $anwsers[$i] = @$_POST['questionanwser_'.$i];
        }
        
        $question->setAnwser($anwsers);    
        $question->save();
        
        Ranking::updateRanking(@$_GET['competition']);
        
        return true;
    } //doEditAnwser
    
} // Questions

?>