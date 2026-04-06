<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the scorings pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Scorings extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Scoring', 'modules/scorings');
        App::openClass('Section', 'modules/sections');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');         
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showScorings();
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $scoring = new Scoring($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditScoring($scoring))
                          $this->showScorings('{LANG_SCORING} {ERROR_EDIT}');
                        else
                          $this->showScorings('{LANG_SCORING} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditScoring(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditScoring($iex);
                }
                catch (Exception $ex)
                {
                    $this->showScorings('{LANG_SCORING} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showScorings($msg='')
    {
        $tpl = new Template('scoring', strtolower(get_class()), 'modules');

        Scoring::getAllScorings(@$_GET['competition']);

        // Store scorings for both views
        $scorings = array();
        while (($scoring = Scoring::nextScoring()) != null)
        {
            $scorings[] = $scoring;
        }

        $c = 0;
        $content = '';
        
        // Desktop table view
        $content .= '<div class="d-none d-md-block">'."\n";
        $content .= '<div class="table-responsive">'."\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th style="width: 40px;">{LANG_ID}</th>'."\n";
        $content .= '<th>{LANG_SCORING_FULLNAME}</th>'."\n";
        if ($this->hasAccess(CRUD_EDIT))
            $content .= '<th>{LANG_ENABLED}</th>'."\n";
        $content .= '<th>{LANG_SCORING_POINTS}</th>'."\n";
        if ($this->hasAccess(CRUD_EDIT))
        {
            $content .= '<th>{LANG_SECTION}</th>'."\n";
            $content .= '<th style="width: 75px;">{LANG_ACTIONS}</th>'."\n";
        }
        $content .= '</tr>'."\n";
        
        foreach ($scorings as $scoring)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $scoring->scoring_id . '</td>' . "\n";
            $content .= '<td>' . $scoring->scoring_name . '</td>' . "\n";
            if ($this->hasAccess(CRUD_EDIT))
            {            
                $content .= '<td>' . ($scoring->Scoring_Competition_enabled ? '<img src="templates/{TEMPLATE_NAME}/icons/tick.png" alt="{LANG_ENABLED}" class="icon" />' : '<img src="templates/{TEMPLATE_NAME}/icons/cross.png" alt="{LANG_DISABLED}" class="icon" />') .'</td>' . "\n";
            }
            $content .= '<td>' . $scoring->Scoring_Competition_points . '</td>' . "\n";
            if ($this->hasAccess(CRUD_EDIT))
            {
                $content .= '<td>' . $scoring->section_name . '</td>' . "\n";
                $content .= '<td>' . "\n";
                $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$scoring->scoring_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_SCORING} {LANG_EDIT}" class="actions" /></a>' . "\n";
                $content .= '</td>' . "\n";
            }
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="6">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        $content .= '</table>'."\n";
        $content .= '</div>'."\n";
        $content .= '</div>'."\n";
        
        // Mobile card view
        $content .= '<div class="d-md-none">'."\n";
        $c = 0;
        foreach ($scorings as $scoring)
        {
            $content .= '<div class="card mb-3">'."\n";
            $content .= '<div class="card-body">'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_ID}</small><br/><strong>'.$scoring->scoring_id.'</strong></div>'."\n";
            if ($this->hasAccess(CRUD_EDIT))
            {
                $enabledIcon = $scoring->Scoring_Competition_enabled ? '<img src="templates/{TEMPLATE_NAME}/icons/tick.png" alt="{LANG_ENABLED}" width="16" />' : '<img src="templates/{TEMPLATE_NAME}/icons/cross.png" alt="{LANG_DISABLED}" width="16" />';
                $content .= '<div class="col-6"><small class="text-muted">{LANG_ENABLED}</small><br/>'.$enabledIcon.'</div>'."\n";
            }
            else
            {
                $content .= '<div class="col-6"><small class="text-muted">{LANG_SCORING_POINTS}</small><br/><strong>'.$scoring->Scoring_Competition_points.'</strong></div>'."\n";
            }
            $content .= '</div>'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-12"><small class="text-muted">{LANG_SCORING_FULLNAME}</small><br/><strong>'.$scoring->scoring_name.'</strong></div>'."\n";
            $content .= '</div>'."\n";
            $content .= '<div class="row mb-2">'."\n";
            $content .= '<div class="col-6"><small class="text-muted">{LANG_SCORING_POINTS}</small><br/>'.$scoring->Scoring_Competition_points.'</div>'."\n";
            if ($this->hasAccess(CRUD_EDIT))
            {
                $content .= '<div class="col-6"><small class="text-muted">{LANG_SECTION}</small><br/>'.$scoring->section_name.'</div>'."\n";
            }
            $content .= '</div>'."\n";
            
            if ($this->hasAccess(CRUD_EDIT))
            {
                $content .= '<div class="mt-3">'."\n";
                $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$scoring->scoring_id .'" class="btn btn-sm btn-outline-secondary"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" width="16" alt="{LANG_SCORING} {LANG_EDIT}" /> {LANG_EDIT}</a>' . "\n";
                $content .= '</div>'."\n";
            }
            
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            $c++;
        }
        $content .= '<div class="text-muted mt-2">{LANG_COUNT}: ' . $c . '</div>'."\n";
        $content .= '</div>'."\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_SCORINGS}';
        $replaceArr['SCORING_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showScorings

    private function showEditScoring($edit=false)
    {
        $tpl = new Template('scoring_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Scoring::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $scoring = new Scoring(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$scoring != null)
        {
            //on edit read all values from db
            $scoringName = $scoring->getName();
            $scoringEnabled = $scoring->getEnabled(@$_GET['competition']);
            $scoringPoints = $scoring->getPoints(@$_GET['competition']);
            $sectionName = $scoring->getSection()->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $scoringName = @$_POST['scoringname'];
            $scoringEnabled = @$_POST['scoringenabled'];
            $scoringPoints = @$_POST['scoringpionts'];
            $sectionName = $scoring->getSection()->getName();
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_SCORING_FULLNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'scoringname') || (@$edit && !@$scoringName)) ? ' error' : '') . '" maxlength="70" type="text" name="scoringname"' . (@$scoringName ? ' value="'.@$scoringName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_ENABLED}:</td><td><input class="form-check-input" type="checkbox" name="scoringenabled" value="1" ' . (@$scoringEnabled == 1 ? ' checked' : '') . '></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_SCORING_POINTS}:</td><td><select class="form-select" name="scoringpoints">' . "\n";
        for ($i=0; $i<=App::$_CONF->getValue('MAX_SELECTION_SCORING'); $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($scoringPoints == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_SECTION}:</td><td>'.$sectionName.'</td></tr>';
        
        $replaceArr['SCORING_TITLE'] = "{LANG_SCORING} {LANG_EDIT}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['SCORING_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditScoring

    private function doEditScoring($scoring=false)
    {
        $fields = array('scoringname');

        if (strlen(@$_POST['scoringname']) < 5)
          throw new InputException('{ERROR_TOO_SHORT} 5 {ERROR_CHARS}', 'scoringname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        $scoring->setName(@$_POST['scoringname']);
        $scoring->setEnabled(@$_GET['competition'], @$_POST['scoringenabled']);
        $scoring->setPoints(@$_GET['competition'], @$_POST['scoringpoints']);
        $scoring->save();
        
        return true;
    } //doEditScoring

} // Scorings

?>