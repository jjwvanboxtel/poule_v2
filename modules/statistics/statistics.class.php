<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Statistics module – displays Chart.js pie/bar charts calculated
 * on-the-fly from the database.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 */
class Statistics extends Component
{
    /**
     * Chart.js palette – used for all charts (cycles if there are more slices).
     */
    private static $PALETTE = [
        '#ff6d29', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff',
        '#ff9f40', '#e74c3c', '#2ecc71', '#3498db', '#9b59b6',
        '#1abc9c', '#f39c12', '#e67e22', '#d35400', '#c0392b',
    ];

    public function __construct($id)
    {
        parent::__construct($id);
        App::openClass('InputException', 'modules/');
        App::openClass('Statistic', 'modules/statistics');

        if (!isset($_GET['competition'])) {
            throw new Exception('{ERROR_NO_COMPETITION_SELECTED}');
        }

        $this->showStatistics();
    }

    // ── private methods ────────────────────────────────────────────────

    private function showStatistics()
    {
        $tpl = new Template('statistic', strtolower(get_class()), 'modules');

        $replaceArr = array();
        $replaceArr['COM_NAME'] = '{LANG_STATISTICS}';
        $replaceArr['COM_ID']   = $this->componentId;
        $replaceArr['CONTENT']  = $this->buildChartsHtml(@$_GET['competition']);
        $tpl->replace($replaceArr);
        echo $tpl;
    }

    private function buildChartsHtml($competitionId)
    {
        $rounds    = Statistic::getAllStatistics($competitionId, 'rounds');
        $questions = Statistic::getAllStatistics($competitionId, 'questions');

        if (empty($rounds) && empty($questions)) {
            return '<div class="alert alert-info">{LANG_STATISTICS_EMPTY}</div>';
        }

        $html  = '';
        $jsCharts = '';

        // ── Rounds ──────────────────────────────────────────────────────
        if (!empty($rounds)) {
            $html .= '<h3 class="mt-0 mb-3">{LANG_ROUNDS}</h3>' . "\n";
            $html .= '<div class="row g-4 mb-4">' . "\n";
            foreach ($rounds as $i => $chart) {
                $canvasId = 'chart_round_' . $i;
                $html    .= $this->buildChartCard($canvasId, $chart['title']);
                $jsCharts .= $this->buildChartJs($canvasId, $chart['title'], $chart['labels'], $chart['values'], 'pie');
            }
            $html .= '</div>' . "\n";
        }

        // ── Questions ────────────────────────────────────────────────────
        if (!empty($questions)) {
            $html .= '<h3 class="mb-3">{LANG_QUESTIONS}</h3>' . "\n";
            $html .= '<div class="row g-4">' . "\n";
            foreach ($questions as $i => $chart) {
                $canvasId  = 'chart_question_' . $i;
                $chartType = count($chart['labels']) <= 8 ? 'pie' : 'bar';
                $html     .= $this->buildChartCard($canvasId, $chart['title']);
                $jsCharts  .= $this->buildChartJs($canvasId, $chart['title'], $chart['labels'], $chart['values'], $chartType);
            }
            $html .= '</div>' . "\n";
        }

        // Inline the initialisation script after all canvases
        if ($jsCharts !== '') {
            $html .= '<script>' . "\n" . $jsCharts . '</script>' . "\n";
        }

        return $html;
    }

    private function buildChartCard($canvasId, $title)
    {
        return '<div class="col-12 col-lg-6">' . "\n"
             . '<div class="card">' . "\n"
             . '<div class="card-header"><span class="card-title">' . htmlspecialchars($title) . '</span></div>' . "\n"
             . '<div class="card-body">' . "\n"
             . '<div style="position: relative; min-height: 300px;">' . "\n"
             . '<canvas id="' . $canvasId . '"></canvas>' . "\n"
             . '</div>' . "\n"
             . '</div>' . "\n"
             . '</div>' . "\n"
             . '</div>' . "\n";
    }

    private function buildChartJs($canvasId, $title, $labels, $values, $type)
    {
        if (empty($labels)) {
            return '';
        }

        // Build colour arrays (cycle through palette)
        $palette     = self::$PALETTE;
        $numColours  = count($palette);
        $bgColors    = array();
        $borderColors = array();
        foreach ($labels as $k => $label) {
            $colour       = $palette[$k % $numColours];
            $bgColors[]   = $colour;
            $borderColors[] = $colour;
        }

        $labelsJson  = json_encode($labels, JSON_UNESCAPED_UNICODE);
        $valuesJson  = json_encode($values);
        $bgJson      = json_encode($bgColors);
        $borderJson  = json_encode($borderColors);
        $titleJson   = json_encode($title, JSON_UNESCAPED_UNICODE);
        $typeJson    = json_encode($type);

        $js  = "(function(){\n";
        $js .= "  var ctx = document.getElementById(" . json_encode($canvasId) . ");\n";
        $js .= "  if (!ctx) return;\n";
        $js .= "  new Chart(ctx, {\n";
        $js .= "    type: " . $typeJson . ",\n";
        $js .= "    data: {\n";
        $js .= "      labels: " . $labelsJson . ",\n";
        $js .= "      datasets: [{\n";
        $js .= "        label: " . $titleJson . ",\n";
        $js .= "        data: " . $valuesJson . ",\n";
        $js .= "        backgroundColor: " . $bgJson . ",\n";
        $js .= "        borderColor: " . $borderJson . ",\n";
        $js .= "        borderWidth: 2\n";
        $js .= "      }]\n";
        $js .= "    },\n";
        $js .= "    options: {\n";
        $js .= "      responsive: true,\n";
        $js .= "      maintainAspectRatio: false,\n";
        if ($type === 'bar') {
            $js .= "      indexAxis: 'x',\n";
            $js .= "      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },\n";
        }
        $js .= "      plugins: {\n";
        $js .= "        legend: { position: '" . ($type === 'pie' ? 'right' : 'top') . "' },\n";
        $js .= "        tooltip: {\n";
        $js .= "          callbacks: {\n";
        $js .= "            label: function(ctx) {\n";
        $js .= "              var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);\n";
        $js .= "              var pct = total > 0 ? Math.round(ctx.parsed" . ($type === 'pie' ? '' : '.y') . " / total * 100) : 0;\n";
        $js .= "              return ctx.label + ': ' + ctx.parsed" . ($type === 'pie' ? '' : '.y') . " + ' (' + pct + '%)';\n";
        $js .= "            }\n";
        $js .= "          }\n";
        $js .= "        }\n";
        $js .= "      }\n";
        $js .= "    }\n";
        $js .= "  });\n";
        $js .= "})();\n";

        return $js;
    }
}
?>
