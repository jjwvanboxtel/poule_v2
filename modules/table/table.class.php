<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the tables pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Table extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Ranking', 'modules/table');
        App::openClass('Section', 'modules/sections');
        App::openClass('Scoring', 'modules/scorings');
        App::openClass('Game', 'modules/games');
        App::openClass('Question', 'modules/questions');
        App::openClass('Round', 'modules/rounds');
        App::openClass('QuestionPrediction', 'modules/predictions');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('GamePrediction', 'modules/predictions');
        App::openClass('Country', 'modules/countries');
        App::openClass('RoundResult', 'modules/rounds');
        App::openClass('Participant', 'modules/users');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':
                try 
                {
                    if(isset($_POST['submit']))
                    {
                        if(!$this->hasAccess(CRUD_EDIT))
                            throw new Exception('{ERROR_ACCESSDENIED}');

                        if(!Ranking::updateRanking(@$_GET['competition']))
                          $this->showTable('<div>{LANG_TABLE} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showTable('<div>{LANG_TABLE} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showTable();
                    }
                }
                catch (Exception $ex)
                {
                    $this->showTable('<div>{LANG_TABLE} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showTable($msg='')
    {
        $tpl = new Template('table', strtolower(get_class()), 'modules');

        Ranking::getAllRankings(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($ranking = Ranking::nextRanking()) != null)
        {
            $participant = new Participant($ranking->Participant_User_user_id);

            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $ranking->table_position . '</td>' . "\n";
            if ($ranking->table_position == $ranking->table_old_position || $ranking->table_old_position == 0)
                $content .= '<td><img src="templates/{TEMPLATE_NAME}/images/straight_line_steady.png" class="icon" /></td>' . "\n";
            else if ($ranking->table_position < $ranking->table_old_position)
                $content .= '<td style="color: green"><img src="templates/{TEMPLATE_NAME}/images/green_arrow_up.png" class="icon" style="margin-right: 10px;" />(+'.($ranking->table_old_position-$ranking->table_position).')</td>' . "\n";
            else if ($ranking->table_position > $ranking->table_old_position)
                $content .= '<td style="color: red"><img src="templates/{TEMPLATE_NAME}/images/red_arrow_down.png" class="icon" style="margin-right: 10px;" />(-'.($ranking->table_position-$ranking->table_old_position).')</td>' . "\n";
            $content .= '<td><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.Component::getComponentId('Predictions').'&id=' . $participant->getId() . '">' . $participant->getFirstName() . ' ' . $participant->getLastName() . '</a></td>';
            $content .= '<td>' . $ranking->table_points . '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_TABLE}';
        $replaceArr['TABLE_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $replaceArr['TABLE_BUTTONS'] = ($this->hasAccess(CRUD_EDIT) ? '<input class="submit" type="submit" name="submit" value="{LANG_CALCULATE}" />' : ''); 
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showTable
} // Table
?>