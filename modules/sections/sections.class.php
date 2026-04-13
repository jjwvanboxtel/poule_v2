<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the sections pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Sections extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Section', 'modules/sections');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}'); 
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showSections();
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $section = new Section($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        self::validateCsrfToken();
                        if(!$this->doEditSection($section))
                          $this->showSections('{LANG_SECTION} {ERROR_EDIT}');
                        else
                          $this->showSections('{LANG_SECTION} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditSection(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditSection($iex);
                }
                catch (Exception $ex)
                {
                    $this->showSections('{LANG_SECTION} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(htmlspecialchars(@$_GET['option'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showSections($msg='')
    {
        $tpl = new Template('section', strtolower(get_class()), 'modules');

        Section::getAllSections(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($section = Section::nextSection()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $section->section_id . '</td>' . "\n";
            $content .= '<td>' . $section->section_name . '</td>' . "\n";
            $content .= '<td>' . ($section->Section_Competition_enabled ? '<img src="templates/{TEMPLATE_NAME}/icons/tick.png" alt="{LANG_ENABLED}" class="icon" />' : '<img src="templates/{TEMPLATE_NAME}/icons/cross.png" alt="{LANG_DISABLED}" class="icon" />') .'</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$section->section_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_SECTION} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_SECTIONS}';
        $replaceArr['SECTION_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showSections

    private function showEditSection($edit=false)
    {
        $tpl = new Template('section_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Section::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $section = new Section(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$section != null)
        {
            //on edit read all values from db
            $sectionName = $section->getName(@$_GET['competition']);
            $sectionEnabled = $section->getEnabled(@$_GET['competition']);
       }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $sectionName = @$_POST['sectionname'];
            $sectionEnabled = @$_POST['sectionenabled'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_SECTION_FULLNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'sectionname') || (@$edit && !@$sectionName)) ? ' error' : '') . '" maxlength="70" type="text" name="sectionname"' . (@$sectionName ? ' value="'.@$sectionName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr><td>{LANG_ENABLED}:</td><td><input class="form-check-input" type="checkbox" name="sectionenabled" value="1" ' . (@$sectionEnabled == 1 ? ' checked' : '') . '></td></tr>' . "\n";
         
        $replaceArr['SECTION_TITLE'] = "{LANG_SECTION} {LANG_EDIT}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['SECTION_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $replaceArr['CSRF_TOKEN'] = self::getCsrfTokenField();
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditSection

    private function doEditSection($section=false)
    {
        $fields = array('sectionname');

        if (strlen(@$_POST['sectionname']) < 5)
          throw new InputException('{ERROR_TOO_SHORT} 5 {ERROR_CHARS}', 'sectionname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }

        $section->setName(@$_POST['sectionname']);
        $enabled = $_POST['sectionenabled'] ? 1 : 0;
        $section->setEnabled(@$_GET['competition'], $enabled);
        $section->save();
        
        return true;
    } //doEditSection

} // Sections

?>