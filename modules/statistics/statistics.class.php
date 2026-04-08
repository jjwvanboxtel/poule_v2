<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Is the class for generating the html for the Statistics pages.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Statistics extends Component
{
    /**
     * Sends the right template to the template parser.
     */
    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Statistic', 'modules/statistics');
        
        if(!isset($_GET['competition']))
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
 
        switch(@$_GET['option'])
        {
            case '':
                $this->showStatistics();
                break;
            case 'generate':
                Statistic::generateAllStatistics(@$_GET['competition']);
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    } //__construct

    private function showStatistics($msg='')
    {
        $tpl = new Template('statistic', strtolower(get_class()), 'modules');
        
        $last_updated = date('d-m-Y H:i:s', Statistic::getLastUpdated(@$_GET['competition']));
        
        $rounds = Statistic::getAllStatistics(@$_GET['competition'], 'rounds');

        $content = '<h3>'.App::$_LANG->getValue('LANG_ROUNDS') . '</h3>'."\n";
        $content .= '<div style="text-align: center;">'."\n";
        foreach ($rounds as $round)
        {
            $content .= '<img src="'.UPLOAD_DIR.Statistic::getStatisticsDir(@$_GET['competition']).$round['file'].'" width="750" alt="'.$round['title'].'" /><br /><br />'."\n";
        }
        $content .= '</div>'."\n";

        $questions = Statistic::getAllStatistics(@$_GET['competition'], 'questions');

        $content .= '<h3>'.App::$_LANG->getValue('LANG_QUESTIONS') . '</h3>'."\n";
        $content .= '<div style="text-align: center;">'."\n";
        foreach ($questions as $question)
        {
            $content .= '<img src="'.UPLOAD_DIR.Statistic::getStatisticsDir(@$_GET['competition']).$question['file'].'" width="750" alt="'.$question['title'].'" /><br /><br />'."\n";
        }
        $content .= '</div>'."\n";
        
        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_STATISTICS}';
        $replaceArr['COM_ID'] = $this->componentId;
        $replaceArr['LAST_UPDATED'] = App::$_LANG->getValue('LANG_LAST_UPDATED') . ' ' . $last_updated;
        $replaceArr['CONTENT'] = $content;
        $tpl->replace($replaceArr);
        echo $tpl;

    } // showStatistics

} // Statistics

?>