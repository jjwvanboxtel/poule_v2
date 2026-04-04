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
                          $this->showScorings('<div>{LANG_SCORING} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showScorings('<div>{LANG_SCORING} {LANG_EDIT_OK}</div><br />' . "\n");
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
                    $this->showScorings('<div>{LANG_SCORING} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
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

        $c = 0;
        $content  = '<tr>'."\n";
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
        while (($scoring = Scoring::nextScoring()) != null)
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

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_SCORINGS}';
        $replaceArr['SCORING_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showScorings

    private function showEditScoring($edit=false)
    {
        $tpl = new Template('scoring_add', strtolower(get_class()), 'modules');

        // Always try to load the scoring object when an ID is available so that
        // references like $scoring->getSection()->getName() work for both the
        // initial edit (bool true) and the error-redisplay (InputException) path.
        if (@$_GET['id'] && Scoring::exists(@$_GET['id']))
        {
            $scoring = new Scoring(@$_GET['id']);
        }
        else if (is_bool($edit) && $edit)
        {
            throw new Exception("{ERROR_ITEMNOTEXIST}");
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
                        
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
        $content .= '<tr><td>{LANG_SCORING_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'scoringname') || (@$edit && !@$scoringName) ? 'class="error" ' : ' ') . 'type="text" name="scoringname"' . (@$scoringName ? ' value="'.@$scoringName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_ENABLED}:</td><td><input type="checkbox" name="scoringenabled" value="1" ' . (@$scoringEnabled == 1 ? ' checked' : '') . '></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_SCORING_POINTS}:</td><td><select name="scoringpoints">' . "\n";
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
        $msg = isset($replaceArr['ERROR_MSG']) ? $replaceArr['ERROR_MSG'] : '';
        $msg = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $msg);
        $replaceArr['ERROR_MSG_WRAPPER'] = self::buildMsgWrapper(rtrim($msg));
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