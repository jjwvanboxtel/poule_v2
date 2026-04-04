<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the forms pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Forms extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Form', 'modules/forms');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');

        
        switch(@$_GET['option'])
        {
            case '':
                $this->showForms();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditForm();
                        $this->showForms('<div id="msg">{LANG_FORM} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditForm($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showForms('<div>{LANG_FORM} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditForm();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $form = new Form($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditForm($form))
                          $this->showForms('<div>{LANG_FORM} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showForms('<div>{LANG_FORM} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditForm(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditForm($iex);
                }
                catch (Exception $ex)
                {
                    $this->showForms('<div>{LANG_FORM} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Form::exists($_GET['id']))
                    {
                        $form = new Form($_GET['id']);

                        if (!$form->delete())
                          $this->showForms('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_FORM} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showForms('<div>{LANG_FORM} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showForms('<div>{LANG_FORM} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showForms($msg='')
    {
        $tpl = new Template('form', strtolower(get_class()), 'modules');

        Form::getAllForms(@$_GET['competition']);

        $actions = '';
        if ($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE))
            $actions = '<th style="width: 75px;">{LANG_ACTIONS}</th>';
        
        $content = '<tr>
            <th style="width: 40px;">{LANG_ID}</th>
            <th>{LANG_FORM}</th>
            '.$actions.'
        </tr>';       
        
        $c = 0;
        while (($form = Form::nextForm()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $form->form_id . '</td>' . "\n";
            $content .= '<td><a href="'.UPLOAD_DIR.Form::getFormDir(@$_GET['competition']).$form->form_file.'" target="new">' . $form->form_name . '</a></td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$form->form_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_FORM} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$form->form_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_FORM} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_FORMS}';
        $replaceArr['FORM_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['FORM_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_FORM} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_FORM} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showForms

    private function showEditForm($edit=false)
    {
        $tpl = new Template('form_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Form::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $form = new Form(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$form != null)
        {
            //on edit read all values from db
            $formName = $form->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $formName = @$_POST['formname'];
                        
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
        $content .= '<tr><td>{LANG_FORM}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'formname') || (@$edit && !@$formName) ? 'class="error" ' : ' ') . 'type="text" name="formname"' . (@$formName ? ' value="'.@$formName.'"' : '') . ' /></td></tr>' . "\n";
 
        if(is_bool($edit) && $edit && Form::exists(@$_GET['id']))
        {
            $form = new Form(@$_GET['id']);
            $formFile = $form->getFile();

            $content .= '<tr><td>&nbsp;</td><td>'.$formFile.'<br />{LANG_FILE_DESC}</td></tr>';
            $_FILES['file']['name'] = $formFile;
        }
        $content .= '<tr><td>{LANG_FORM_FILE}:</td><td><input ' . ((@$edit && !@$_FILES['file']['name']) || ($edit instanceof InputException && $edit->getErrorField() == 'file') ? 'class="error" ' : ' ') . 'type="file" name="file" id="file" style="width: 300px;" /></td></tr>' . "\n";
        
        $replaceArr['FORM_TITLE'] = "{LANG_FORM} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['FORM_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $msg = isset($replaceArr['ERROR_MSG']) ? $replaceArr['ERROR_MSG'] : '';
        $msg = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $msg);
        $replaceArr['ERROR_MSG_WRAPPER'] = self::buildMsgWrapper(rtrim($msg));
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditForm

    private function doEditForm($form=false)
    {
        $fields = array('formname');
        $status = false;

        if (strlen(@$_POST['formname']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'formname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }

        if (!$form && $_FILES['file']['name'] == '')
          throw new InputException('{ERROR_EMPTY_FIELD}', 'image');
          
        //add new form, else edit form
        if (!$form)
        {
            Form::add($_POST['formname'], $_FILES['file'], @$_GET['competition']);
        }
        else
        {       
            $form->setName(@$_POST['formname']);

            if($_FILES['file']['name'] != '')
              $status = $form->setFile(@$_FILES['file']);
            else
              $status = true;

            $form->save();
        }
        
        return $status;
    } //doEditForm

} // Forms

?>