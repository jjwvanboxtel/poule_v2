<?php
/**
 * Statistic data access helper.
 *
 * Generates chart data on-the-fly from the database.
 * Returns arrays in the format:
 * [
 *   { "title": "...", "labels": [...], "values": [...] }, ...
 * ]
 */
class Statistic
{
    /**
     * Returns the chart data array for the given type ('rounds' or 'questions').
     * Calculates data on-the-fly from the database.
     */
    public static function getAllStatistics($competitionId, $type)
    {
        if ($type === 'rounds') {
            return self::buildRoundChartData($competitionId);
        } elseif ($type === 'questions') {
            return self::buildQuestionChartData($competitionId);
        }
        return array();
    }

    // ── private helpers ────────────────────────────────────────────────

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
