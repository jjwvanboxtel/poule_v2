<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the referees pages.
 *
 * @package   vvalemreferee
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Referees extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Referee', 'modules/referees');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');        
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showReferees();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditReferee();
                        $this->showReferees('{LANG_REFEREE} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditReferee($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showReferees('{LANG_REFEREE} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditReferee();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $referee = new Referee($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditReferee($referee))
                          $this->showReferees('{LANG_REFEREE} {ERROR_EDIT}');
                        else
                          $this->showReferees('{LANG_REFEREE} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditReferee(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditReferee($iex);
                }
                catch (Exception $ex)
                {
                    $this->showReferees('{LANG_REFEREE} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Referee::exists($_GET['id']))
                    {
                        $referee = new Referee($_GET['id']);

                        if (!$referee->delete())
                          $this->showReferees('{ERROR_OLD_FILE_REMOVE}<br />{LANG_REFEREE} {LANG_REMOVE_OK}');
                        else
                          $this->showReferees('{LANG_REFEREE} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showReferees('{LANG_REFEREE} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showReferees($msg='')
    {
        $tpl = new Template('referee', strtolower(get_class()), 'modules');

        Referee::getAllReferees(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($referee = Referee::nextReferee()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $referee->referee_id . '</td>' . "\n";
            $content .= '<td>' . $referee->referee_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$referee->referee_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_REFEREE} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$referee->referee_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_REFEREE} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_REFEREES}';
        $replaceArr['REFEREE_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['REFEREE_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="btn btn-primary mb-2"><i class="bi bi-plus-lg me-1"></i>{LANG_REFEREE} {LANG_ADD}</a>'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showReferees

    private function showEditReferee($edit=false)
    {
        $tpl = new Template('referee_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Referee::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $referee = new Referee(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$referee != null)
        {
            //on edit read all values from db
            $refereeName = $referee->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $refereeName = @$_POST['refereename'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_REFEREE_FULLNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'refereename') || (@$edit && !@$refereeName)) ? ' error' : '') . '" maxlength="70" type="text" name="refereename"' . (@$refereeName ? ' value="'.@$refereeName.'"' : '') . ' /></td></tr>' . "\n";
         
        $replaceArr['REFEREE_TITLE'] = "{LANG_REFEREE} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['REFEREE_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditReferee

    private function doEditReferee($referee=false)
    {
        $fields = array('refereename');
        $status = false;

        if (strlen(@$_POST['refereename']) < 1)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'refereename');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new referee, else edit referee
        if (!$referee)
        {
            Referee::add(@$_GET['competition'], $_POST['refereename']);
        }
        else
        {       
            $referee->setName(@$_POST['refereename']);
            $referee->save();
            $status = true;
        }
        
        return $status;
    } //doEditReferee

} // Referees

?>