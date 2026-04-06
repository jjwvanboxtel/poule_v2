<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the participant pages.
 *
 * @package   poule
 * @author    Jaap van Boxtel
 * @copyright 20-05-2013
 * @version   0.1
 */
class Participants extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Ranking', 'modules/table');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');

        switch(@$_GET['option'])
        {
            case '':
                $this->showParticipants();
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if(isset($_POST['submit']))
                    {
                        Participants::doEditParticipant();
                        $this->showParticipants('{LANG_PARTICIPANT} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        Participants::showEditParticipant(true);
                    }
                }
                catch (InputException $iex)
                {
                    Participants::showEditParticipant($iex);
                }
                catch (Exception $ex)
                {
                    $this->showParticipants('<div>{LANG_PARTICIPANT} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default: //------------------------------------------------------------------------------------------------------------------
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showParticipants($msg='')
    {
        $tpl = new Template('participant', strtolower(get_class()), 'modules');
        User::getAllUsers(3);
        
        $c = 0;
        $content = '';
        while (($user = User::nextUser()) != null)
        {
            $participant = new Participant($user->user_id);
            if (!isset($_POST['filter'])
                    || @$_POST['filter'] == "0"
                    || @$_POST['filter'] == "1" && $participant->getPayed(@$_GET['competition'])
                    || @$_POST['filter'] == "2" && $participant->getSubscribed(@$_GET['competition'])
                    || @$_POST['filter'] == "3" && $participant->getPayed(@$_GET['competition']) && $participant->getSubscribed(@$_GET['competition'])
                    || @$_POST['filter'] == "4" && $participant->getPayed(@$_GET['competition']) && !$participant->getSubscribed(@$_GET['competition'])
                    || @$_POST['filter'] == "5" && !$participant->getPayed(@$_GET['competition']) && $participant->getSubscribed(@$_GET['competition'])
                    || @$_POST['filter'] == "6" && !$participant->getPayed(@$_GET['competition']) && !$participant->getSubscribed(@$_GET['competition']))
            {
                $currentClass = (($c % 2) ? 'odd' : 'even');
                $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
                $content .= '<td>' . $user->user_id . '</td>' . "\n";
                $content .= '<td>' . $user->user_firstname . ' ' . $user->user_lastname . '</td>' . "\n";
                $content .= '<td>' . "\n";
                $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$user->user_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_USER} {LANG_EDIT}" class="actions" /></a>' . "\n";
                $content .= '</td>' . "\n";
                $content .= '</tr>' . "\n";
                $c++;
            }
        }

        $content .= '<tr><td colspan="4">{LANG_USER_COUNT}: ' . $c . '</td></tr>' . "\n";

        //filter
        $filter  = '<form name="filterlist" action="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com=' . $this->componentId . '" method="post">' . "\n";
        $filter .= '<select class="form-select" onchange="document.filterlist.submit();" name="filter">' . "\n";
        $filter .= '<option value="0" '.(@$_POST['filter'] == "0" ? 'selected' : '').'>{LANG_NO_FILTER}</option>';
        $filter .= '<option value="1" '.(@$_POST['filter'] == "1" ? 'selected' : '').'>{LANG_PARTICIPANT_PAYED}</option>';
        $filter .= '<option value="2" '.(@$_POST['filter'] == "2" ? 'selected' : '').'>{LANG_PARTICIPANT_SUBSCRIBED}</option>';
        $filter .= '<option value="3" '.(@$_POST['filter'] == "3" ? 'selected' : '').'>{LANG_PAYED_AND_SUBSCRIBED}</option>';
        $filter .= '<option value="4" '.(@$_POST['filter'] == "4" ? 'selected' : '').'>{LANG_PAYED_AND_NOT_SUBSCRIBED}</option>';
        $filter .= '<option value="5" '.(@$_POST['filter'] == "5" ? 'selected' : '').'>{LANG_SUBSCRIBED_AND_NOT_PAYED}</option>';
        $filter .= '<option value="6" '.(@$_POST['filter'] == "6" ? 'selected' : '').'>{LANG_NOT_SUBSCRIBED_AND_NOT_PAYED}</option>';
        $filter .= '</select>' . "\n";
        $filter .= '</form><br />' . "\n";
        
        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_PARTICIPANTS}';
        $replaceArr['PARTICIPANT_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['CONTENT'] = $content;
        $replaceArr['FILTER_LIST'] = $filter; 
        $replaceArr['COM_ID'] = $this->componentId;
        $tpl->replace($replaceArr);    

        echo $tpl;
    } // showParticipants

    public static function showEditParticipant($edit=false)
    {
        $tpl = new Template('participant_add', strtolower(get_class()), 'modules');
        if ((is_bool($edit) && $edit) || (isset($_GET['id']) && $edit instanceof InputException))
        {
            if (!@$_GET['id'] || !Participant::exists(@$_GET['id']))
              throw new Exception("{ERROR_ITEMNOTEXIST}");

            $participant = new Participant(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        $firstName = $participant->getFirstName();
        $lastName = $participant->getLastName();
        
        //get default values
        if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $payed = @$_POST['participantpayed'];
            
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        else if ($edit)
        {
            //on edit read all values from db
            $payed = $participant->getPayed(@$_GET['competition']);
        }
        
        $content .= '<tr><td>{LANG_PARTICIPANT}:</td><td>'.$firstName . ' ' . $lastName . '</td></tr>' . "\n";
        $content .= '<tr><td>{LANG_PARTICIPANT_PAYED}:</td><td><input class="form-check-input" type="checkbox" name="participantpayed" value="1" ' . (@$payed == 1 ? ' checked' : '') . '></td></tr>' . "\n";
        
        $replaceArr['PARTICIPANT_TITLE'] = "{LANG_PARTICIPANT} {LANG_EDIT}";
        $replaceArr['CONTENT'] = $content;       
        $replaceArr['PARTICIPANT_COM_ID'] = $_GET['com'];
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];

        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditParticipantGroup

    public static function doEditParticipant()
    {         
        if (!@$_GET['id'] || !Participant::exists(@$_GET['id']))
            throw new Exception("{ERROR_ITEMNOTEXIST}");

        $participant = new Participant(@$_GET['id']);
            
        $participant->setPayed(@$_GET['competition'], @$_POST['participantpayed']);
        $participant->save();
        
        Ranking::updateRanking(@$_GET['competition']);
    } //doEditParticipant
    
} // Participants

?>