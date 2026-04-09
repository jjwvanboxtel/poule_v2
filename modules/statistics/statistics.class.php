<?php
if (!defined('VALID_ACCESS')) die();

/**
 * Statistics module – displays Chart.js pie/bar charts rendered from a
 * pre-generated JSON file.  Admins can trigger a regeneration via the
 * `?option=generate` action.
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

        switch (@$_GET['option']) {
            case '':
                $this->showStatistics();
                break;
            case 'generate':
                if (!$this->hasAccess(CRUD_EDIT)) {
                    throw new Exception('{ERROR_ACCESSDENIED}');
                }
                $this->doGenerate();
                break;
            default:
                throw new Exception(@$_GET['option'] . ' ' . App::$_LANG->getValue('ERROR_NOTVALIDOPT'));
        }
    }

    // ── private methods ────────────────────────────────────────────────

    private function doGenerate()
    {
        $tpl = new Template('statistic', strtolower(get_class()), 'modules');

        Statistic::generateAllStatistics(@$_GET['competition']);
        $msg = self::buildMsgWrapper('{LANG_STATISTICS_GENERATED}');

        $replaceArr = array();
        $replaceArr['COM_NAME']      = '{LANG_STATISTICS}';
        $replaceArr['COM_ID']        = $this->componentId;
        $replaceArr['STAT_MSG']      = $msg;
        $replaceArr['LAST_UPDATED']  = $this->formatLastUpdated(@$_GET['competition']);
        $replaceArr['CONTENT']       = $this->buildChartsHtml(@$_GET['competition']);
        $replaceArr['GENERATE_LINK'] = $this->buildGenerateLink();
        $tpl->replace($replaceArr);
        echo $tpl;
    }

    private function showStatistics($msg = '')
    {
        $tpl = new Template('statistic', strtolower(get_class()), 'modules');

        $replaceArr = array();
        $replaceArr['COM_NAME']      = '{LANG_STATISTICS}';
        $replaceArr['COM_ID']        = $this->componentId;
        $replaceArr['STAT_MSG']      = $msg !== '' ? self::buildMsgWrapper($msg) : '';
        $replaceArr['LAST_UPDATED']  = $this->formatLastUpdated(@$_GET['competition']);
        $replaceArr['CONTENT']       = $this->buildChartsHtml(@$_GET['competition']);
        $replaceArr['GENERATE_LINK'] = $this->buildGenerateLink();
        $tpl->replace($replaceArr);
        echo $tpl;
    }

    private function buildGenerateLink()
    {
        if (!$this->hasAccess(CRUD_EDIT)) {
            return '';
        }
        $href = '?' . (@$_GET['competition'] ? 'competition=' . @$_GET['competition'] . '&amp;' : '')
              . 'com=' . $this->componentId . '&amp;option=generate';
        return '<a href="' . $href . '" class="btn btn-primary">'
             . '<i class="bi bi-arrow-clockwise me-1"></i>{LANG_STATISTICS_GENERATE}'
             . '</a>';
    }

    private function formatLastUpdated($competitionId)
    {
        $ts = Statistic::getLastUpdated($competitionId);
        if ($ts === 0) {
            return '{LANG_STATISTICS_NOT_GENERATED}';
        }
        return '{LANG_LAST_UPDATED}: ' . date('d-m-Y H:i:s', $ts);
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
             . '<canvas id="' . $canvasId . '" height="300"></canvas>' . "\n"
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
        $js .= "      maintainAspectRatio: true,\n";
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
