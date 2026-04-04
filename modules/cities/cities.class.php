<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the cities pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Cities extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('City', 'modules/cities');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':                                            
                $this->showCities();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditCity();
                        $this->showCities('{LANG_CITY} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditCity($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showCities('{LANG_CITY} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditCity();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $city = new City($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditCity($city))
                          $this->showCities('{LANG_CITY} {ERROR_EDIT}');
                        else
                          $this->showCities('{LANG_CITY} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditCity(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditCity($iex);
                }
                catch (Exception $ex)
                {
                    $this->showCities('{LANG_CITY} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && City::exists($_GET['id']))
                    {
                        $city = new City($_GET['id']);

                        if (!$city->delete())
                          $this->showCities('{ERROR_OLD_FILE_REMOVE}<br />{LANG_CITY} {LANG_REMOVE_OK}');
                        else
                          $this->showCities('{LANG_CITY} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showCities('{LANG_CITY} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showCities($msg='')
    {
        $tpl = new Template('city', strtolower(get_class()), 'modules');

        City::getAllCities($_GET['competition']);

        $c = 0;
        $content = '';
        while (($city = City::nextCity()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $city->city_id . '</td>' . "\n";
            $content .= '<td>' . $city->city_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$city->city_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_CITY} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$city->city_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_CITY} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_CITIES}';
        $replaceArr['CITY_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['CITY_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_CITY} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_CITY} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showCities

    private function showEditCity($edit=false)
    {
        $tpl = new Template('city_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !City::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $city = new City(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$city != null)
        {
            //on edit read all values from db
            $cityName = $city->getName();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $cityName = @$_POST['cityname'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_CITY_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'cityname') || (@$edit && !@$cityName) ? 'class="error" ' : ' ') . 'type="text" name="cityname"' . (@$cityName ? ' value="'.@$cityName.'"' : '') . ' /></td></tr>' . "\n";
         
        $replaceArr['CITY_TITLE'] = "{LANG_CITY} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['CITY_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditCity

    private function doEditCity($city=false)
    {
        $fields = array('cityname');
        $status = false;

        if (strlen(@$_POST['cityname']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'cityname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new city, else edit city
        if (!$city)
        {
            City::add($_POST['cityname'], @$_GET['competition']);
        }
        else
        {       
            $city->setName(@$_POST['cityname']);
            $city->save();
            $status = true;
        }
        
        return $status;
    } //doEditCity

} // Cities

?>