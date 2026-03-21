<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Statistic
{
    private static $chart_dir = 'statistics/';
    private static $chart_file = 'charts.xml';
    private static $_CHART_HEIGTH = 400;
    private static $_CHART_WIDTH = 750;
    
    public static function getAllStatistics($competitionId, $type)
    {
        $path = UPLOAD_DIR.$competitionId.'/'.self::$chart_dir;

        $domDocument = new DOMDocument();
        $domDocument->load($path.self::$chart_file);
        $domRoot = $domDocument->documentElement;

        return self::getCharts($domRoot->childNodes, $type);
    }
    
    public static function getLastUpdated($competitionId)
    {
        $path = UPLOAD_DIR.$competitionId.'/'.self::$chart_dir;

        $domDocument = new DOMDocument();
        $domDocument->load($path.self::$chart_file);
        $domRoot = $domDocument->documentElement;

        return $domRoot->attributes->item(0)->value;
    }
    
    public static function generateAllStatistics($competitionId)
    {
        // include 3th party library to generate charts
        require_once('./modules/statistics/libchart/classes/libchart.php');

        $path = UPLOAD_DIR.$competitionId.'/'.self::$chart_dir;
        if (file_exists($path)) {
            App::$_UPL->deleteDir($path);
        }

        // create dir to store the generated charts
        mkdir($path, 0777, true);

        $time = time();
        
        $rounds = self::generateRoundStatistics($competitionId, $path, $time);
        $questions = self::generateQuestionStatistics($competitionId, $path, $time);

        self::createChartsXml($path, $time, $rounds, $questions);  
        
        echo "Charts genereated!";
    }
      
    public static function getStatisticsDir($competitionId)
    {
        return $competitionId.'/'.self::$chart_dir;
    }
      
    private static function generateRoundStatistics($competitionId, $path, $time)
    {
        App::openClass('Round', 'modules/rounds');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('Country', 'modules/countries');
        
        $rounds = array();
        
        Round::getAllRounds($competitionId);

        while (($round = Round::nextRound()) != null)
        {
            RoundPrediction::getPredictionCountryCount($round->round_id);

            $chart = new PieChart(self::$_CHART_WIDTH, self::$_CHART_HEIGTH);    
            $dataSet = new XYDataSet();
            //echo '=== Start round: ' . $round->round_name . '===<br />';
            while (($prediction = RoundPrediction::nextPrediction()) != null)
            {           
                $country = new Country($prediction->Country_country_id);
                $dataSet->addPoint(new Point($country->getName() . ' (' . $prediction->count . ') ', $prediction->count));
                
                //echo $prediction->Country_country_id . " (" . $prediction->count . ") " . $prediction->count . "<br />";
            }
            //echo '=== End round: ' . $round->round_name . '===<br />';

            $file = $time.'_round_'.$round->round_id.'.png';
            $chart->setDataSet($dataSet);
            $chart->setTitle($round->round_name);
            $chart->render($path.$file);
            
            array_push($rounds, array('title'=>$round->round_name, 'file'=>$file));
        }
        
        return $rounds;
    }
    
    private static function generateQuestionStatistics($competitionId, $path, $time)
    {
        App::openClass('Question', 'modules/questions');
        App::openClass('QuestionPrediction', 'modules/predictions');
        
        $questions = array();
        
        Question::getAllQuestions($competitionId);

        while (($question = Question::nextQuestion()) != null)
        {
            //echo "Question type: " .$question->question_type . "<br />";

            QuestionPrediction::getPredictionAnswerCount($question->question_id);

            $chart = new PieChart(self::$_CHART_WIDTH, self::$_CHART_HEIGTH);    
            $dataSet = new XYDataSet();
            //echo '=== Start question: ' . $question->question_question . '===<br />';
            while (($prediction = QuestionPrediction::nextQuestionPrediction()) != null)
            {               
                $name = $prediction->Participant_Question_answer;
                if ($question->question_type == "yesno")
                {
                    $name = "Ja";
                    if ($prediction->Participant_Question_answer == "0")
                        $name = "Nee";
                }
                //echo $name . " (" . $prediction->count . ") " . $prediction->count . "<br />";

                $dataSet->addPoint(new Point($name . ' (' . $prediction->count . ') ', $prediction->count));
            }
            //echo '=== End question: ' . $question->question_question . '===<br />';

            $file = $time.'_question_'.$question->question_id.'.png';
            $chart->setDataSet($dataSet);
            $chart->setTitle($question->question_question);
            $chart->render($path.$file);

            array_push($questions, array('title'=>$question->question_question, 'file'=>$file));
        }    
        
        return $questions;
    }

    private static function createChartsXml($path, $last_updated, $rounds, $questions) {
        // create a dom document with encoding utf8
        $domDocument = new DOMDocument('1.0', 'UTF-8');

        // format the xml output
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;
        
        // create the root element of the xml tree
        $domRoot = $domDocument->createElement('statistics');
        $domDocument->appendChild($domRoot);
        
        $domAttribute = $domDocument->createAttribute('last_updated');
        $domAttribute->value = $last_updated;
        $domRoot->appendChild($domAttribute);

        $domRounds = $domDocument->createElement('rounds');
        $domRoot->appendChild($domRounds);
        
        foreach ($rounds as $round)
        {
            $domChart = $domDocument->createElement('chart');
            $domRounds->appendChild($domChart);    
            $domAttribute = $domDocument->createAttribute('title');
            $domAttribute->value = $round['title'];
            $domChart->appendChild($domAttribute);
            $domAttribute = $domDocument->createAttribute('file');
            $domAttribute->value = $round['file'];
            $domChart->appendChild($domAttribute);
        }
        
        $domQuestions = $domDocument->createElement('questions');
        $domRoot->appendChild($domQuestions);
                
        foreach ($questions as $question)
        {
            $domChart = $domDocument->createElement('chart');
            $domQuestions->appendChild($domChart);    
            $domAttribute = $domDocument->createAttribute('title');
            $domAttribute->value = $question['title'];
            $domChart->appendChild($domAttribute);
            $domAttribute = $domDocument->createAttribute('file');
            $domAttribute->value = $question['file'];
            $domChart->appendChild($domAttribute);
        }

        $domDocument->save($path.self::$chart_file);
    }
    
    private static function getCharts($childNodes, $type) 
    {
        $charts = array();
        
        foreach ($childNodes as $childNode) 
        {
            if ($childNode->nodeType == 1)
            {
                if ($childNode->nodeName == $type)
                {
                    if(@$childNode->childNodes && $childNode->childNodes->length > 0) {
                        $charts = self::getCharts($childNode->childNodes, $type);
                    }    
                } 
                else if ($childNode->nodeName == 'chart')
                {
                    $chart = array();
                    $chart[$childNode->attributes->item(0)->name] = $childNode->attributes->item(0)->value;
                    $chart[$childNode->attributes->item(1)->name] = $childNode->attributes->item(1)->value;
                    array_push($charts, $chart);
                }   
            }
        }
    
        return $charts;
    } 
}
?>
