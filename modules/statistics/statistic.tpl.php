<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>

{STAT_MSG}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" integrity="sha256-R9e6V8VWDE1jREQBdJW8fRg0P39UD5ZE37eCY3EWZS4=" crossorigin="anonymous"></script>

<div id="statistic" class="card card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">{LAST_UPDATED}</small>
        {GENERATE_LINK}
    </div>
    {CONTENT}
</div>
