<?php
/**
 * Statistic data access and generation helper.
 *
 * Generation writes a single `charts.json` file to the upload directory;
 * display reads it back.  No server-side image rendering is required.
 *
 * JSON schema:
 * {
 *   "last_updated": <unix timestamp>,
 *   "rounds": [
 *     { "title": "...", "labels": [...], "values": [...] }, ...
 *   ],
 *   "questions": [
 *     { "title": "...", "labels": [...], "values": [...] }, ...
 *   ]
 * }
 */
class Statistic
{
    private static $chart_dir  = 'statistics/';
    private static $chart_file = 'charts.json';

    /**
     * Returns the chart data array for the given type ('rounds' or 'questions').
     * Returns an empty array when no data file exists yet.
     */
    public static function getAllStatistics($competitionId, $type)
    {
        $file = self::getJsonPath($competitionId);
        if (!file_exists($file)) {
            return array();
        }
        $data = json_decode(file_get_contents($file), true);
        return isset($data[$type]) ? $data[$type] : array();
    }

    /**
     * Returns the last-updated unix timestamp, or 0 when no data file exists.
     */
    public static function getLastUpdated($competitionId)
    {
        $file = self::getJsonPath($competitionId);
        if (!file_exists($file)) {
            return 0;
        }
        $data = json_decode(file_get_contents($file), true);
        return isset($data['last_updated']) ? (int)$data['last_updated'] : 0;
    }

    /**
     * Generates statistics from the database and persists them as JSON.
     * Outputs a brief status message (shown inside the page content area).
     */
    public static function generateAllStatistics($competitionId)
    {
        $path = UPLOAD_DIR . $competitionId . '/' . self::$chart_dir;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $time      = time();
        $rounds    = self::buildRoundChartData($competitionId);
        $questions = self::buildQuestionChartData($competitionId);

        $payload = array(
            'last_updated' => $time,
            'rounds'       => $rounds,
            'questions'    => $questions,
        );

        file_put_contents(
            $path . self::$chart_file,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        echo '{LANG_STATISTICS_GENERATED}';
    }

    public static function getStatisticsDir($competitionId)
    {
        return $competitionId . '/' . self::$chart_dir;
    }

    // ── private helpers ────────────────────────────────────────────────

    private static function getJsonPath($competitionId)
    {
        return UPLOAD_DIR . $competitionId . '/' . self::$chart_dir . self::$chart_file;
    }

    private static function buildRoundChartData($competitionId)
    {
        App::openClass('Round', 'modules/rounds');
        App::openClass('RoundPrediction', 'modules/predictions');
        App::openClass('Country', 'modules/countries');

        $charts = array();

        Round::getAllRounds($competitionId);
        while (($round = Round::nextRound()) != null) {
            RoundPrediction::getPredictionCountryCount($round->round_id);

            $labels = array();
            $values = array();

            while (($prediction = RoundPrediction::nextPrediction()) != null) {
                $country  = new Country($prediction->Country_country_id);
                $labels[] = $country->getName();
                $values[] = (int)$prediction->count;
            }

            $charts[] = array(
                'title'  => $round->round_name,
                'labels' => $labels,
                'values' => $values,
            );
        }

        return $charts;
    }

    private static function buildQuestionChartData($competitionId)
    {
        App::openClass('Question', 'modules/questions');
        App::openClass('QuestionPrediction', 'modules/predictions');

        $charts = array();

        Question::getAllQuestions($competitionId);
        while (($question = Question::nextQuestion()) != null) {
            QuestionPrediction::getPredictionAnswerCount($question->question_id);

            $labels = array();
            $values = array();

            while (($prediction = QuestionPrediction::nextQuestionPrediction()) != null) {
                $name = $prediction->Participant_Question_answer;
                if ($question->question_type === 'yesno') {
                    $name = ($prediction->Participant_Question_answer === '0') ? '{LANG_NO}' : '{LANG_YES}';
                }
                $labels[] = $name;
                $values[] = (int)$prediction->count;
            }

            $charts[] = array(
                'title'  => $question->question_question,
                'labels' => $labels,
                'values' => $values,
            );
        }

        return $charts;
    }
}
?>
