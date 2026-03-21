<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the competitions pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Competitions extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Competition', 'modules/competitions');

        switch(@$_GET['option'])
        {
            case '':
                $this->showCompetitions();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditCompetition();
                        $this->showCompetitions('<div id="msg">{LANG_COMPETITION} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditCompetition($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showCompetitions('<div>{LANG_COMPETITION} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditCompetition();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $competition = new Competition($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditCompetition($competition))
                          $this->showCompetitions('<div>{LANG_COMPETITION} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showCompetitions('<div>{LANG_COMPETITION} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditCompetition(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditCompetition($iex);
                }
                catch (Exception $ex)
                {
                    $this->showCompetitions('<div>{LANG_COMPETITION} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Competition::exists($_GET['id']))
                    {
                        $competition = new Competition($_GET['id']);

                        if (@$_SESSION['competition'] == $_GET['id'])
                        {
                            unset($_SESSION['competition']);
                        }
                        if (!$competition->delete())
                          $this->showCompetitions('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_COMPETITION} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showCompetitions('<div>{LANG_COMPETITION} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showCompetitions('<div>{LANG_COMPETITION} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showCompetitions($msg='')
    {
        $tpl = new Template('competition', strtolower(get_class()), 'modules');

        Competition::getAllCompetitions();

        $c = 0;
        $content = '';
        while (($competition = Competition::nextCompetition()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $competition->competition_id . '</td>' . "\n";
            $content .= '<td>' . $competition->competition_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?com='.$this->componentId.'&amp;option=edit&amp;id='.$competition->competition_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_COMPETITION} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?com='.$this->componentId.'&amp;option=delete&amp;id='.$competition->competition_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_COMPETITION} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_COMPETITIONS}';
        $replaceArr['COMPETITION_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $replaceArr['COMPETITION_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_COMPETITION} {LANG_ADD}" class="actions_top" /> <a href="?com='.$this->componentId.'&amp;option=add" class="button">{LANG_COMPETITION} {LANG_ADD}</a>'. "\n" : '');
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showCompetitions

    private function showEditCompetition($edit=false)
    {
        $tpl = new Template('competition_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Competition::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $competition = new Competition(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$competition != null)
        {
            //on edit read all values from db
            $competitionName = $competition->getName();
            $competitionMoney = $competition->getMoney(); 
            $competitionFirstPlace = $competition->getFirstPlace(); 
            $competitionSecondPlace = $competition->getSecondPlace(); 
            $competitionThirdPlace = $competition->getThirdPlace(); 
            $competitionDescription = $competition->getDescription();
            $competitionSubmissionDate = date('d-m-Y', $competition->getFinalSubmissionDate());
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $competitionName = @$_POST['competitionname'];
            $competitionMoney = @$_POST['competitionmoney'];
            $competitionFirstPlace = @$_POST['competitionfirstplace'];
            $competitionSecondPlace = @$_POST['competitionsecondplace'];
            $competitionThirdPlace = @$_POST['competitionthirdplace'];
            $competitionDescription = @$_POST['competitiondescription'];
            $competitionSubmissionDate = @$_POST['competitionsubmissiondate'];
            
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
                
        $content .= '<tr><td>{LANG_COMPETITION}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'competitionname') || (@$edit && !@$competitionName) ? 'class="error" ' : ' ') . 'type="text" name="competitionname"' . (@$competitionName ? ' value="'.@$competitionName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td valign="top">{LANG_COMPETITION_DESCRIPTION}:</td><td><textarea class="editor" ' . (@$edit && !@$competitionDescription ? 'style="background-color: red;" ' : ' ') . 'cols="80" rows="10" name="competitiondescription">' . (@$competitionDescription ? ''.@$competitionDescription.'' : '') . '</textarea></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_COMPETITION_FINAL_SUBMISSION_DATE}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'competitionsubmissiondate') || (@$edit && !@$competitionSubmissionDate) ? 'class="error" ' : ' ') . 'type="text" name="competitionsubmissiondate"' . (@$competitionSubmissionDate ? ' value="'.@$competitionSubmissionDate.'"' : '') . ' /> (d-m-Y)</td></tr>' . "\n";
        $content .= '<tr><td>{LANG_COMPETITION_MONEY}:</td><td><select name="competitionmoney">' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_MONEY'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($competitionMoney == $i) ? ' selected' : '') . '>' . $i . ' euro</option>' . "\n";
        }
        $content .= '</select></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_COMPETITION_FIRST_PLACE}:</td><td><select name="competitionfirstplace">' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_PLACE'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($competitionFirstPlace == $i) ? ' selected' : '') . '>' . $i . '%</option>' . "\n";
        }
        $content .= '</select></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_COMPETITION_SECOND_PLACE}:</td><td><select name="competitionsecondplace">' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_PLACE'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($competitionSecondPlace == $i) ? ' selected' : '') . '>' . $i . '%</option>' . "\n";
        }
        $content .= '</select></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_COMPETITION_THIRD_PLACE}:</td><td><select name="competitionthirdplace">' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_PLACE'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($competitionThirdPlace == $i) ? ' selected' : '') . '>' . $i . '%</option>' . "\n";
        }
        $content .= '</select></td></tr>' . "\n";
         
        if(is_bool($edit) && $edit)
        {
            $competitionImage = $competition->getImage();

            $content .= '<tr><td>&nbsp;</td><td><img src="'.UPLOAD_DIR.Competition::getHeaderDir(@$_GET['id']).$competitionImage.'" alt="'.$competitionImage.'" style="width: 200px;" /><br />{LANG_IMG_DESC}</td></tr>';
            $_FILES['file']['name'] = $competitionImage;
        } 
        $content .= '<tr><td>{LANG_COMPETITION_HEADER}:</td><td><input ' . ((@$edit && !@$_FILES['file']['name']) || ($edit instanceof InputException && $edit->getErrorField() == 'file') ? 'class="error" ' : ' ') . 'type="file" name="file" id="file" style="width: 300px;" /></td></tr>' . "\n";
         
        $replaceArr['COMPETITION_TITLE'] = "{LANG_COMPETITION} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['COMPETITION_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditCompetition

    private function doEditCompetition($competition=false)
    {
        $fields = array('competitionname');
        $status = false;

        if (strlen(@$_POST['competitionname']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'competitionname');
          
        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        $submission_date = strtotime(@$_POST['competitionsubmissiondate']);
        if (!$submission_date)
            throw new InputException('{ERROR_DATE_NOT_VALID}', 'competitionsubmissiondate');
          
        //add new competition, else edit competition
        if (!$competition)
        {
            Competition::add($_POST['competitionname'], $_POST['competitiondescription'], $_FILES['file'], $submission_date, $_POST['competitionmoney'], $_POST['competitionfirstplace'], $_POST['competitionsecondplace'], $_POST['competitionthirdplace']);
        }
        else
        {       
            $competition->setName(@$_POST['competitionname']);
            
            if($_FILES['file']['name'] != '')
              $status = $competition->setImage(@$_FILES['file']);
            else
              $status = true;

            $competition->setMoney(@$_POST['competitionmoney']);
            $competition->setFirstPlace(@$_POST['competitionfirstplace']);
            $competition->setSecondPlace(@$_POST['competitionsecondplace']);
            $competition->setThirdPlace(@$_POST['competitionthirdplace']);
            $competition->setDescription(@$_POST['competitiondescription']);
            $competition->setFinalSubmissionDate($submission_date);
            $competition->save();
            $status = true;
        }
        
        return $status;
    } //doEditCompetition

} // Competitions

?>