<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the subleagues pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Subleagues extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Subleague', 'modules/subleagues');

        switch(@$_GET['option'])
        {
            case '':
                $this->showSubleagues();
                break;
            case 'show':
                $this->showSubleague();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditSubleague();
                        $this->showSubleagues('<div id="msg">{LANG_SUBLEAGUE} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditSubleague($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showSubleagues('<div>{LANG_SUBLEAGUE} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditSubleague();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $subleague = new Subleague($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditSubleague($subleague))
                          $this->showSubleagues('<div>{LANG_SUBLEAGUE} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showSubleagues('<div>{LANG_SUBLEAGUE} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditSubleague(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditSubleague($iex);
                }
                catch (Exception $ex)
                {
                    $this->showSubleagues('<div>{LANG_SUBLEAGUE} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
          case 'user_edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $subleague = new Subleague($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditSubleagueParticipants($subleague))
                          $this->showEditSubleagueParticipants('<div>{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {ERROR_ADD}</div><br />' . "\n");
                        else
                          $this->showEditSubleagueParticipants('<div>{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditSubleagueParticipants();
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditSubleagueParticipants();
                }
                catch (Exception $ex)
                {
                    $this->showSubleagues('<div>{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'user_delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && @$_GET['participantId'] && Subleague::participantExists($_GET['id'], $_GET['participantId']))
                    {
                        $subleague = new Subleague($_GET['id']);

                        if (!$subleague->deleteParticipant($_GET['participantId']))
                          $this->showEditSubleagueParticipants('<div>{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showEditSubleagueParticipants('<div>{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Subleague::exists($_GET['id']))
                    {
                        $subleague = new Subleague($_GET['id']);

                        if (!$subleague->delete())
                          $this->showSubleagues('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_SUBLEAGUE} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showSubleagues('<div>{LANG_SUBLEAGUE} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showSubleagues('<div>{LANG_SUBLEAGUE} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showSubleagues($msg='')
    {
        $tpl = new Template('subleague', strtolower(get_class()), 'modules');

        Subleague::getAllSubleagues();

        $c = 0;
        $content = '<tr>'."\n";
        $content .= '<th style="width: 40px;">{LANG_ID}</th>'."\n";
        $content .= '<th>{LANG_SUBLEAGUE}</th>'."\n";
        $content .= '<th style="width: 75px;">'.($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE) ? '{LANG_ACTIONS}' : '').'</th>'."\n";
        $content .= '</tr>'. "\n";
    
        while (($subleague = Subleague::nextSubleague()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $subleague->subleague_id . '</td>' . "\n";
            $content .= '<td><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=show&amp;id='.$subleague->subleague_id .'">' . $subleague->subleague_name . '</a></td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=user_edit&amp;id='.$subleague->subleague_id .'"><img src="templates/{TEMPLATE_NAME}/icons/user_edit.png" alt="{LANG_SUBLEAGUE} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$subleague->subleague_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_SUBLEAGUE} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$subleague->subleague_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_SUBLEAGUE} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_SUBLEAGUES}';
        $replaceArr['SUBLEAGUE_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $replaceArr['SUBLEAGUE_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_SUBLEAGUE} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_SUBLEAGUE} {LANG_ADD}</a>'. "\n" : '');
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showSubleagues

    private function showSubleague($msg='')
    {
        $tpl = new Template('subleague_table', strtolower(get_class()), 'modules');

        if (!@$_GET['id'] || !Subleague::exists(@$_GET['id']))
            throw new Exception("{ERROR_ITEMNOTEXIST}");

        $subleague = new Subleague(@$_GET['id']);

        $content = '';
        $replaceArr = array();
               
        $c = 0;
        foreach ($subleague->getParticipants() as $participantArr)
        {
            $participant = new Participant($participantArr['id']);
            
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>'.($c+1).'</td>'."\n";
            $content .= '<td>'.$participantArr['position'].'</td>'."\n";
            $content .= '<td><a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.Component::getComponentId('Predictions').'&id=' . $participant->getId() . '">' . $participant->getFirstName() . ' ' . $participant->getLastName() . '</a></td>' . "\n";
            $content .= '<td>' .$participantArr['points'] . '</td>'."\n";
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        
        $replaceArr['COM_NAME'] = "{LANG_SUBLEAGUE}: " . $subleague->getName();
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showSubleague
    
    private function showEditSubleagueParticipants($msg='')
    {
        $tpl = new Template('subleague_participant_add', strtolower(get_class()), 'modules');

        if (!@$_GET['id'] || !Subleague::exists(@$_GET['id']))
            throw new Exception("{ERROR_ITEMNOTEXIST}");

        $subleague = new Subleague(@$_GET['id']);

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';
               
        $content .= '<tr><td>{LANG_PARTICIPANT}:</td>'."\n";
        $content .= '<td><select name="participant">' . "\n";
        User::getAllUsers(3);
        while (($user = User::nextUser()) != null)
        {
             $content .= '<option value="' . $user->user_id . '">' . $user->user_firstname . ' ' . $user->user_lastname. '</option>' . "\n";
        }
        $content .= '</select>'."\n";
        $content .= '&nbsp;<input class="submit" type="submit" name="submit" value="{LANG_ADD}" /></td>'."\n";
        $content .= '<td>&nbsp;</td>'."\n";
        $content .= '</tr>' . "\n";
        
        $content .= '<tr><td colspan="3">&nbsp;</td></tr>'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th colspan="2">{LANG_PARTICIPANT}</td>'."\n";
        $content .= '<th style="width: 20px;">{LANG_ACTIONS}</td>'."\n";
        $content .= '</tr>'."\n";
        $c = 0;
        foreach ($subleague->getParticipants() as $participantArr)
        {
            $participant = new Participant($participantArr['id']);
            
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td colspan="2">' . $participant->getFirstName() . ' ' . $participant->getLastName() . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=user_delete&amp;id='.@$_GET['id'].'&amp;participantId='.$participant->getId().'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_PARTICIPANT} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }
        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";
        
        $replaceArr['SUBLEAGUE_TITLE'] = "{LANG_SUBLEAGUE} {LANG_PARTICIPANT} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['SUBLEAGUE_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $replaceArr['SUBLEAGUE_ID'] = @$_GET['id'];
        $replaceArr['SUBLEAGUE_MSG'] = $msg;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showSubleagueParticipants    

    private function showEditSubleague($edit=false)
    {
        $tpl = new Template('subleague_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Subleague::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $subleague = new Subleague(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$subleague != null)
        {
            //on edit read all values from db
            $subleagueName = $subleague->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $subleagueName = @$_POST['subleaguename'];
            
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
                
        $content .= '<tr><td>{LANG_SUBLEAGUE}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'subleaguename') || (@$edit && !@$subleagueName) ? 'class="error" ' : ' ') . 'type="text" name="subleaguename"' . (@$subleagueName ? ' value="'.@$subleagueName.'"' : '') . ' /></td></tr>' . "\n";
         
        /*if(is_bool($edit) && $edit)
        {
            $subleagueImage = $subleague->getImage();

            $content .= '<tr><td>&nbsp;</td><td><img src="'.UPLOAD_DIR.Subleague::getHeaderDir(@$_GET['competition'], $subleague->getId()).$subleagueImage.'" alt="'.$subleagueImage.'" style="width: 200px;" /><br />{LANG_IMG_DESC}</td></tr>';
            $_FILES['file']['name'] = $subleagueImage;
        } 
        $content .= '<tr><td>{LANG_SUBLEAGUE_HEADER}:</td><td><input ' . ((@$edit && !@$_FILES['file']['name']) || ($edit instanceof InputException && $edit->getErrorField() == 'file') ? 'class="error" ' : ' ') . 'type="file" name="file" id="file" style="width: 300px;" /></td></tr>' . "\n";*/
         
        $replaceArr['SUBLEAGUE_TITLE'] = "{LANG_SUBLEAGUE} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['SUBLEAGUE_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditSubleague

    private function doEditSubleague($subleague=false)
    {
        $fields = array('subleaguename');
        $status = false;

        if (strlen(@$_POST['subleaguename']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'subleaguename');
          
        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
                  
        //add new subleague, else edit subleague
        if (!$subleague)
        {
            Subleague::add($_POST['subleaguename'], /*$_FILES['file'],*/ @$_GET['competition']);
        }
        else
        {       
            $subleague->setName(@$_POST['subleaguename']);
            
            /*if($_FILES['file']['name'] != '')
              $status = $subleague->setImage(@$_FILES['file']);
            else
              $status = true;
            */
            $subleague->save();
            $status = true;
        }
        
        return $status;
    } //doEditSubleague

    private function doEditSubleagueParticipants($subleague=false)
    {          
        if (!Subleague::participantExists(@$_GET['id'], @$_POST['participant']))
        {
            $subleague->addParticipant(@$_POST['participant']);        
            return true;
        }
        
        return false;       
    } //doEditSubleagueParticipants    
    
} // Subleagues

?>