<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the rounds pages.
 *
 * @package   vvalemround
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Rounds extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Round', 'modules/rounds');
        App::openClass('RoundResult', 'modules/rounds');
        App::openClass('Country', 'modules/countries');
        App::openClass('Ranking', 'modules/table');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');         
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showRounds();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditRound();
                        $this->showRounds('{LANG_ROUND} {LANG_ADD_OK}');
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditRound($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showRounds('{LANG_ROUND} {ERROR_ADD}: ' . $ex->getMessage());
                    }
                }
                else
                {
                    $this->showEditRound();
                }
                break;
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $round = new Round($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditRound($round))
                          $this->showRounds('{LANG_ROUND} {ERROR_EDIT}');
                        else
                          $this->showRounds('{LANG_ROUND} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditRound(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditRound($iex);
                }
                catch (Exception $ex)
                {
                    $this->showRounds('{LANG_ROUND} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'editcountries':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $round = new Round($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditRoundCountries($round))
                          $this->showRounds('{LANG_ROUND} {ERROR_EDIT}');
                        else
                          $this->showRounds('{LANG_ROUND} {LANG_EDIT_OK}');
                    }
                    else
                    {
                        $this->showEditRoundCountries(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditRoundCountries($iex);
                }
                catch (Exception $ex)
                {
                    $this->showRounds('{LANG_ROUND} {ERROR_EDIT}: ' . $ex->getMessage());
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Round::exists($_GET['id']))
                    {
                        $round = new Round($_GET['id']);

                        if (!$round->delete())
                          $this->showRounds('{ERROR_OLD_FILE_REMOVE}<br />{LANG_ROUND} {LANG_REMOVE_OK}');
                        else
                          $this->showRounds('{LANG_ROUND} {LANG_REMOVE_OK}');
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showRounds('{LANG_ROUND} {ERROR_REMOVE}: ' . $ex->getMessage());
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showRounds($msg='')
    {
        $tpl = new Template('round', strtolower(get_class()), 'modules');

        Round::getAllRounds(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($round = Round::nextRound()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $round->round_id . '</td>' . "\n";
            $content .= '<td>' . $round->round_name . '</td>' . "\n";
            $content .= '<td>' . $round->round_count . '</td>' . "\n";            
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=editcountries&amp;id='.$round->round_id .'"><img src="templates/{TEMPLATE_NAME}/icons/award_star_add.png" alt="{LANG_ROUND_COUNTRY}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$round->round_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_ROUND} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$round->round_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_ROUND} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_ROUNDS}';
        $replaceArr['ROUND_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['ROUND_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_ROUND} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_ROUND} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showRounds

    private function showEditRound($edit=false)
    {
        $tpl = new Template('round_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Round::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $round = new Round(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$round != null)
        {
            //on edit read all values from db
            $roundName = $round->getName();
            $roundCount = $round->getCount();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $roundName = @$_POST['roundname'];
            $roundCount = @$_POST['roundcount'];
                        
            $replaceArr['ERROR_MSG'] = self::buildMsgWrapper($edit->getMessage());
        }
        $content .= '<tr><td>{LANG_ROUND_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'roundname') || (@$edit && !@$roundName) ? 'class="error" ' : ' ') . 'type="text" name="roundname"' . (@$roundName ? ' value="'.@$roundName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_ROUND_COUNT}:</td>' . "\n";
        $content .= '<td><select name="roundcount">' . "\n";
        for ($i=0; $i<100; $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($roundCount == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $replaceArr['ROUND_TITLE'] = "{LANG_ROUND} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";        $replaceArr['CONTENT'] = $content;
        $replaceArr['ROUND_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditRound

    private function showEditRoundCountries($edit=false)
    {
        $tpl = new Template('round_add', strtolower(get_class()), 'modules');
        if (!@$_GET['id'] || !Round::exists(@$_GET['id']))
            throw new Exception("{ERROR_ITEMNOTEXIST}");

        $round = new Round(@$_GET['id']);

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //on edit read all values from db
        $roundName = $round->getName();
        $roundCount = $round->getCount();
        $roundCountries = $round->getCountries();
        
        for ($i=0; $i<$roundCount; $i++)
        {
            $content .= '<tr>' . "\n";
            $content .= '<td>{LANG_COUNTRY} '.($i+1).':</td>' . "\n";
            $content .= '<td><select name="roundcountry_'.$i.'">' . "\n";
            $content .= '<option value="empty" ' . (@$edit && @($roundCountries[$i] == 0) ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
            Country::getAllCountries(@$_GET['competition']);
            while (($country = Country::nextCountry()) != null)
            {
                $content .= '<option value="' . $country->country_id . '" ' . (@$edit && ($roundCountries[$i] instanceof Country) && ($roundCountries[$i]->getId() == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
            }
            $content .= '</select></td>' . "\n";
            $content .= '</tr>' . "\n";        
        }
        
        $replaceArr['ROUND_TITLE'] = "{LANG_ROUND} {LANG_EDIT}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['ROUND_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditRoundCountries
      
    private function doEditRound($round=false)
    {
        $fields = array('roundname');
        $status = false;

        if (strlen(@$_POST['roundname']) < 2)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'roundname');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new round, else edit round
        if (!$round)
        {
            Round::add(@$_GET['competition'], $_POST['roundname'], $_POST['roundcount']);
        }
        else
        {       
            $round->setName(@$_POST['roundname']);
            $round->setCount(@$_POST['roundcount']);
            $round->save();
            $status = true;
        }
        
        return $status;
    } //doEditRound

    private function doEditRoundCountries($round=false)
    {   
        $countries = array();
        $count = $round->getCount();
        for ($i=0; $i<$count; $i++)
        {
            $countries[$i] = @$_POST['roundcountry_'.$i];
        }
        $round->setCountries($countries);
        $round->save();
        
        Ranking::updateRanking(@$_GET['competition']);
        
        return true;
    } //doEditRound    
    
} // Rounds

?>