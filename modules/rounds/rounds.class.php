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
                    self::validateCsrfToken();
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
                        self::validateCsrfToken();
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
                        self::validateCsrfToken();
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

        // Store rounds for both views
        $rounds = array();
        while (($round = Round::nextRound()) != null)
        {
            $rounds[] = $round;
        }

        $c = 0;
        $content = '';
        
        // Desktop table view
        $content .= '<div class="d-none d-md-block">'."\n";
        $content .= '<div class="table-responsive">'."\n";
        $content .= '<table class="list" cellpadding="0" cellspacing="0">'."\n";
        $content .= '<tr>'."\n";
        $content .= '<th style="width: 40px;">{LANG_ID}</th>'."\n";
        $content .= '<th>{LANG_ROUND_FULLNAME}</th>'."\n";
        $content .= '<th>{LANG_ROUND_COUNT}</th>'."\n";
        $content .= '<th style="width: 75px;">{LANG_ACTIONS}</th>'."\n";
        $content .= '</tr>'."\n";
        
        foreach ($rounds as $round)
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
        $content .= '</table>'."\n";
        $content .= '</div>'."\n";
        $content .= '</div>'."\n";
        
        // Mobile card view
        $content .= '<div class="d-md-none">'."\n";
        $c = 0;
        foreach ($rounds as $round)
        {
            $content .= '<div class="card mb-3">'."\n";
            $content .= '<div class="card-body">'."\n";
            $content .= '<h6 class="card-title">'.$round->round_name.'</h6>'."\n";
            $content .= '<div class="mb-2"><small class="text-muted">{LANG_ID}:</small> '.$round->round_id.'</div>'."\n";
            $content .= '<div class="mb-2"><small class="text-muted">{LANG_ROUND_COUNT}:</small> '.$round->round_count.'</div>'."\n";
            
            if ($this->hasAccess(CRUD_EDIT) || $this->hasAccess(CRUD_DELETE))
            {
                $content .= '<div class="mt-3">'."\n";
                ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=editcountries&amp;id='.$round->round_id .'" class="btn btn-sm btn-outline-primary me-2"><img src="templates/{TEMPLATE_NAME}/icons/award_star_add.png" width="16" alt="{LANG_ROUND_COUNTRY}" /> {LANG_ROUND_COUNTRY}</a>' . "\n" : '');
                ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$round->round_id .'" class="btn btn-sm btn-outline-secondary me-2"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" width="16" alt="{LANG_ROUND} {LANG_EDIT}" /> {LANG_EDIT}</a>' . "\n" : '');
                ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$round->round_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');" class="btn btn-sm btn-outline-danger"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" width="16" alt="{LANG_ROUND} {LANG_REMOVE}" /> {LANG_REMOVE}</a>' . "\n" : '');
                $content .= '</div>'."\n";
            }
            
            $content .= '</div>'."\n";
            $content .= '</div>'."\n";
            $c++;
        }
        $content .= '<div class="text-muted mt-2">{LANG_COUNT}: ' . $c . '</div>'."\n";
        $content .= '</div>'."\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_ROUNDS}';
        $replaceArr['ROUND_MSG'] = self::buildMsgWrapper($msg);
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['ROUND_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="btn btn-primary mb-2"><i class="bi bi-plus-lg me-1"></i>{LANG_ROUND} {LANG_ADD}</a>'. "\n" : '');
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
        $content .= '<tr><td>{LANG_ROUND_FULLNAME}:</td><td><input class="form-control' . (((@$edit instanceof InputException && $edit->getErrorField() == 'roundname') || (@$edit && !@$roundName)) ? ' error' : '') . '" maxlength="70" type="text" name="roundname"' . (@$roundName ? ' value="'.@$roundName.'"' : '') . ' /></td></tr>' . "\n";
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_ROUND_COUNT}:</td>' . "\n";
        $content .= '<td><select class="form-select" name="roundcount">' . "\n";
        for ($i=0; $i<100; $i++)
        {
            $content .= '<option value="' . $i . '" ' . (@$edit && ($roundCount == $i) ? ' selected' : '') . '>' . $i . '</option>' . "\n";
        }
        $content .= '</select></td>' . "\n";
        $content .= '</tr>' . "\n";
        
        $replaceArr['ROUND_TITLE'] = "{LANG_ROUND} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";        $replaceArr['CONTENT'] = $content;
        $replaceArr['ROUND_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $replaceArr['CSRF_TOKEN'] = self::getCsrfTokenField();
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
            $content .= '<td><select class="form-select" name="roundcountry_'.$i.'">' . "\n";
            $content .= '<option value="0" ' . (@$edit && @($roundCountries[$i] == 0) ? ' selected' : '') . '>{LANG_EMPTY}</option>' . "\n";
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
        $replaceArr['CSRF_TOKEN'] = self::getCsrfTokenField();
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