<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the countries pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Countries extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Country', 'modules/countries');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');

        
        switch(@$_GET['option'])
        {
            case '':
                $this->showCountries();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditCountry();
                        $this->showCountries('{LANG_COUNTRY} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditCountry($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showCountries('{LANG_COUNTRY} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditCountry();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $country = new Country($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditCountry($country))
                          $this->showCountries('{LANG_COUNTRY} {ERROR_EDIT}');
                        else
                          $this->showCountries('{LANG_COUNTRY} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditCountry(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditCountry($iex);
                }
                catch (Exception $ex)
                {
                    $this->showCountries('{LANG_COUNTRY} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Country::exists($_GET['id']))
                    {
                        $country = new Country($_GET['id']);

                        if (!$country->delete())
                          $this->showCountries('{ERROR_OLD_FILE_REMOVE}<br />{LANG_COUNTRY} {LANG_REMOVE_OK}');
                        else
                          $this->showCountries('{LANG_COUNTRY} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showCountries('{LANG_COUNTRY} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showCountries($msg='')
    {
        $tpl = new Template('country', strtolower(get_class()), 'modules');

        Country::getAllCountries(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($country = Country::nextCountry()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $country->country_id . '</td>' . "\n";
            $content .= '<td><img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$country->country_flag.'" width="16" alt="'.$country->country_name.'" class="icon" /></td>';
            $content .= '<td>' . $country->country_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$country->country_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_COUNTRY} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$country->country_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_COUNTRY} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_COUNTRIES}';
        $replaceArr['COUNTRY_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['COUNTRY_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_COUNTRY} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_COUNTRY} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showCountries

    private function showEditCountry($edit=false)
    {
        $tpl = new Template('country_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Country::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $country = new Country(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$country != null)
        {
            //on edit read all values from db
            $countryName = $country->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $countryName = @$_POST['countryname'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_COUNTRY_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'countryname') || (@$edit && !@$countryName) ? 'class="error" ' : ' ') . 'type="text" name="countryname"' . (@$countryName ? ' value="'.@$countryName.'"' : '') . ' /></td></tr>' . "\n";
 
        if(is_bool($edit) && $edit && Country::exists(@$_GET['id']))
        {
            $country = new Country(@$_GET['id']);
            $countryImage = $country->getImage();

            $content .= '<tr><td>&nbsp;</td><td><img src="'.UPLOAD_DIR.Country::getCountryDir(@$_GET['competition']).$countryImage.'" alt="'.$countryImage.'" /><br />{LANG_IMG_DESC}</td></tr>';
            $_FILES['file']['name'] = $countryImage;
        }
        $content .= '<tr><td>{LANG_COUNTRY_IMAGE}:</td><td><input ' . ((@$edit && !@$_FILES['file']['name']) || ($edit instanceof InputException && $edit->getErrorField() == 'file') ? 'class="error" ' : ' ') . 'type="file" name="file" id="file" style="width: 300px;" /></td></tr>' . "\n";
        
        $replaceArr['COUNTRY_TITLE'] = "{LANG_COUNTRY} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['COUNTRY_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditCountry

    private function doEditCountry($country=false)
    {
        $fields = array('countryname');
        $status = false;

        if (strlen(@$_POST['countryname']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'countryname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }

        if (!$country && $_FILES['file']['name'] == '')
          throw new InputException('{ERROR_EMPTY_FIELD}', 'image');
          
        //add new country, else edit country
        if (!$country)
        {
            Country::add($_POST['countryname'], $_FILES['file'], @$_GET['competition']);
        }
        else
        {       
            $country->setName(@$_POST['countryname']);

            if($_FILES['file']['name'] != '')
              $status = $country->setImage(@$_FILES['file']);
            else
              $status = true;

            $country->save();
        }
        
        return $status;
    } //doEditCountry

} // Countries

?>