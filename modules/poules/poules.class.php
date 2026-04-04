<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the poules pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Poules extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Poule', 'modules/poules');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');        
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showPoules();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditPoule();
                        $this->showPoules('{LANG_POULE} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditPoule($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showPoules('{LANG_POULE} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditPoule();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $poule = new Poule($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditPoule($poule))
                          $this->showPoules('{LANG_POULE} {ERROR_EDIT}');
                        else
                          $this->showPoules('{LANG_POULE} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditPoule(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditPoule($iex);
                }
                catch (Exception $ex)
                {
                    $this->showPoules('{LANG_POULE} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Poule::exists($_GET['id']))
                    {
                        $poule = new Poule($_GET['id']);

                        if (!$poule->delete())
                          $this->showPoules('{ERROR_OLD_FILE_REMOVE}<br />{LANG_POULE} {LANG_REMOVE_OK}');
                        else
                          $this->showPoules('{LANG_POULE} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showPoules('{LANG_POULE} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showPoules($msg='')
    {
        $tpl = new Template('poule', strtolower(get_class()), 'modules');

        Poule::getAllPoules(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($poule = Poule::nextPoule()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $poule->poule_id . '</td>' . "\n";
            $content .= '<td>' . $poule->poule_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$poule->poule_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_POULE} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$poule->poule_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_POULE} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_POULES}';
        $replaceArr['POULE_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['POULE_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_POULE} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_POULE} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showPoules

    private function showEditPoule($edit=false)
    {
        $tpl = new Template('poule_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Poule::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $poule = new Poule(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$poule != null)
        {
            //on edit read all values from db
            $pouleName = $poule->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $pouleName = @$_POST['poulename'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_POULE_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'poulename') || (@$edit && !@$pouleName) ? 'class="error" ' : ' ') . 'type="text" name="poulename"' . (@$pouleName ? ' value="'.@$pouleName.'"' : '') . ' /></td></tr>' . "\n";
         
        $replaceArr['POULE_TITLE'] = "{LANG_POULE} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['POULE_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditPoule

    private function doEditPoule($poule=false)
    {
        $fields = array('poulename');
        $status = false;

        if (strlen(@$_POST['poulename']) < 1)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'poulename');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new poule, else edit poule
        if (!$poule)
        {
            Poule::add($_POST['poulename'], @$_GET['competition']);
        }
        else
        {       
            $poule->setName(@$_POST['poulename']);
            $poule->save();
            $status = true;
        }
        
        return $status;
    } //doEditPoule

} // Poules

?>