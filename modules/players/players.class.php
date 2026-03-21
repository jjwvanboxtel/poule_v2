<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the players pages.
 *
 * @package   vvalemplayer
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Players extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Player', 'modules/players');
        App::openClass('Country', 'modules/countries');

        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        
        switch(@$_GET['option'])
        {
            case '':
                $this->showPlayers();
                break;
            case 'add':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditPlayer();
                        $this->showPlayers('<div id="msg">{LANG_PLAYER} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditPlayer($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showPlayers('<div>{LANG_PLAYER} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditPlayer();
                }
                break;
            case 'add_more':
                if(!$this->hasAccess(CRUD_CREATE))
                  throw new Exception('{ERROR_ACCESSDENIED}');

                if(isset($_POST['submit']))
                {
                    try
                    {
                        $this->doEditPlayers();
                        $this->showPlayers('<div id="msg">{LANG_PLAYER} {LANG_ADD_OK}</div><br />' . "\n");
                    }
                    catch (InputException $iex)
                    {
                        $this->showEditPlayers($iex);
                    }
                    catch (Exception $ex)
                    {
                        $this->showPlayers('<div>{LANG_PLAYER} {ERROR_ADD}: ' . $ex->getMessage() . '</div><br />' . "\n");
                    }
                }
                else
                {
                    $this->showEditPlayers();
                }
                break;                
            case 'edit':
                if(!$this->hasAccess(CRUD_EDIT))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    $player = new Player($_GET['id']);

                    if(isset($_POST['submit']))
                    {
                        if(!$this->doEditPlayer($player))
                          $this->showPlayers('<div>{LANG_PLAYER} {ERROR_EDIT}</div><br />' . "\n");
                        else
                          $this->showPlayers('<div>{LANG_PLAYER} {LANG_EDIT_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        $this->showEditPlayer(true);
                    }
                }
                catch (InputException $iex)
                {
                    $this->showEditPlayer($iex);
                }
                catch (Exception $ex)
                {
                    $this->showPlayers('<div>{LANG_PLAYER} {ERROR_EDIT}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            case 'delete': 
                if(!$this->hasAccess(CRUD_DELETE))
                  throw new Exception('{ERROR_ACCESSDENIED}');
                  
                try
                {
                    if (@$_GET['id'] && Player::exists($_GET['id']))
                    {
                        $player = new Player($_GET['id']);

                        if (!$player->delete())
                          $this->showPlayers('<div>{ERROR_OLD_FILE_REMOVE}<br />{LANG_PLAYER} {LANG_REMOVE_OK}</div><br />' . "\n");
                        else
                          $this->showPlayers('<div>{LANG_PLAYER} {LANG_REMOVE_OK}</div><br />' . "\n");
                    }
                    else
                    {
                        throw new Exception('{ERROR_ITEMNOTEXIST}');
                    }
                }
                catch (Exception $ex)
                {
                    $this->showPlayers('<div>{LANG_PLAYER} {ERROR_REMOVE}: ' . $ex->getMessage() . '</div><br />' . "\n");
                }
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showPlayers($msg='')
    {
        $tpl = new Template('player', strtolower(get_class()), 'modules');

        Player::getAllPlayers(@$_GET['competition']);

        $c = 0;
        $content = '';
        while (($player = Player::nextPlayer()) != null)
        {
            $currentClass = (($c % 2) ? 'odd' : 'even');
            $content .= '<tr class="' . $currentClass . '" onmouseover="this.className = \'hover\';" onmouseout="this.className = \'' . $currentClass . '\';">' . "\n";
            $content .= '<td>' . $player->player_id . '</td>' . "\n";
            $content .= '<td>' . $player->player_name . '</td>' . "\n";
            
            $content .= '<td>' . $player->country_name . '</td>' . "\n";
            $content .= '<td>' . "\n";
            ($this->hasAccess(CRUD_EDIT) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=edit&amp;id='.$player->player_id .'"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_PLAYER} {LANG_EDIT}" class="actions" /></a>' . "\n" : '');
            ($this->hasAccess(CRUD_DELETE) ? $content .= '<a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=delete&amp;id='.$player->player_id.'" onclick="return confirm(\'{LANG_CONFIRM_DELETE}\');"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_PLAYER} {LANG_REMOVE}" class="actions" /></a>' . "\n" : '');
            $content .= '</td>' . "\n";
            $content .= '</tr>' . "\n";
            $c++;
        }

        $content .= '<tr><td colspan="4">{LANG_COUNT}: ' . $c . '</td></tr>' . "\n";

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_PLAYERS}';
        $replaceArr['PLAYER_MSG'] = $msg;
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['PLAYER_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_PLAYER} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add" class="button">{LANG_PLAYER} {LANG_ADD}</a> |'. "\n" : '');
        $replaceArr['PLAYERS_ADD'] = ($this->hasAccess(CRUD_CREATE) ? '<img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_PLAYER} {LANG_ADD}" class="actions_top" /> <a href="?'.(@$_GET['competition'] ? 'competition='.@$_GET['competition'].'&amp;' : '').'com='.$this->componentId.'&amp;option=add_more" class="button">{LANG_PLAYERS} {LANG_ADD}</a><br />'. "\n" : '');
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showPlayers

    private function showEditPlayer($edit=false)
    {
        $tpl = new Template('player_add', strtolower(get_class()), 'modules');
        if (is_bool($edit) && $edit)
        {
            if (!@$_GET['id'] || !Player::exists(@$_GET['id']))
                throw new Exception("{ERROR_ITEMNOTEXIST}");

            $player = new Player(@$_GET['id']);
        }

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        //get default values
        if ($edit && @$player != null)
        {
            //on edit read all values from db
            $playerName = $player->getName();
            $playerCountry = $player->getCountry();
        }
        else if ($edit && $edit instanceof InputException)
        {
            //the post went wrong, get previous values
            $playerName = @$_POST['playername'];
            $playerCountry = @$_POST['playercountry'];
                        
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
        $content .= '<tr><td>{LANG_PLAYER_FULLNAME}:</td><td><input maxlength="70" ' . ((@$edit instanceof InputException && $edit->getErrorField() == 'playername') || (@$edit && !@$playerName) ? 'class="error" ' : ' ') . 'type="text" name="playername"' . (@$playerName ? ' value="'.@$playerName.'"' : '') . ' /></td></tr>' . "\n";

        $content .= '<tr><td>{LANG_COUNTRY}:</td><td>'."\n";
        $content .= '<select name="playercountry">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($playerCountry->getId() == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select>' . "\n";
        $content .= '</td></tr>'. "\n";
        
        $replaceArr['PLAYER_TITLE'] = "{LANG_PLAYER} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['PLAYER_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditPlayer

    private function showEditPlayers($edit=false)
    {
        $tpl = new Template('player_add', strtolower(get_class()), 'modules');

        $content = '';
        $replaceArr = array();
        $replaceArr['ERROR_MSG'] = '';

        if ($edit && $edit instanceof InputException)
        {
            $replaceArr['ERROR_MSG'] = $edit->getMessage();
        }
        $content .= '<tr>' . "\n";
        $content .= '<td>{LANG_COUNTRY}:</td>' . "\n";
        $content .= '<td>'. "\n";
        $content .= '<select name="countryid">' . "\n";
        Country::getAllCountries(@$_GET['competition']);
        while (($country = Country::nextCountry()) != null)
        {
            $content .= '<option value="' . $country->country_id . '"' . (@$edit && ($playerCountry->getId() == $country->country_id) ? ' selected' : '') . '>' . $country->country_name . '</option>' . "\n";
        }
        $content .= '</select>' . "\n";
        $content .= '</td></tr>'. "\n";    
        
        $content .= '<tr><td>{LANG_FORM_FILE}:</td><td><input ' . ((@$edit && !@$_FILES['file']['name']) || ($edit instanceof InputException && $edit->getErrorField() == 'file') ? 'class="error" ' : ' ') . 'type="file" name="file" id="file" accept="application/txt" style="width: 300px;" /></td></tr>' . "\n";
        
        $replaceArr['PLAYER_TITLE'] = "{LANG_PLAYERS} {LANG_" . ((@$_GET['option'] == 'edit') ? "EDIT" : "ADD") . "}";
        $replaceArr['CONTENT'] = $content;
        $replaceArr['PLAYER_COM_ID'] = $this->componentId;
        $replaceArr['COMPETITION_ID'] = @$_GET['competition'];
        $tpl->replace($replaceArr);
        echo $tpl;
    } // showEditPlayers  
    
    private function doEditPlayers()
    {        
        $status = false;

        if ($_FILES['file']['name'] == '')
          throw new InputException('{ERROR_EMPTY_FIELD}', 'txt');
          
        if($_FILES['file']['name'] != '')
        {
            App::$_UPL->loadUp($_FILES['file']);

            $players = explode(',', file_get_contents(UPLOAD_DIR.$_FILES['file']['name']));
            foreach ($players as $player)
            {
                Player::add(@$_GET['competition'], $player, @$_POST['countryid']);
            }
            
            $status = App::$_UPL->deleteFile($_FILES['file']['name'], "");
        }
        
        return $status;    
    }
    
    private function doEditPlayer($player=false)
    {
        $fields = array('playername');
        $status = false;

        if (strlen(@$_POST['playername']) < 1)
          throw new InputException('{ERROR_TOO_SHORT} 2 {ERROR_CHARS}', 'playername');

        //check for errors
        foreach ($fields as $field)
        {
            @$_POST[$field] = trim(@$_POST[$field]);
            if (!@$_POST[$field])
              throw new InputException('{ERROR_EMPTY_FIELD}', $field);
        }
          
        //add new player, else edit player
        if (!$player)
        {
            Player::add(@$_GET['competition'], @$_POST['playername'], @$_POST['playercountry']);
        }
        else
        {       
            $player->setName(@$_POST['playername']);
            $player->setCountry(@$_POST['playercountry']);
            $player->save();
            $status = true;
        }
        
        return $status;
    } //doEditPlayer

} // Players

?>