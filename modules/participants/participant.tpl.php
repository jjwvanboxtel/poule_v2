<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{PARTICIPANT_MSG}
<div class="mb-3">
    {LANG_FILTER}: {FILTER_LIST}
</div>

<div id="participant" class="card">
    <div class="table-responsive">
        <table class="list">
            <tr>
                <th style="width: 40px;">{LANG_ID}</th>
                <th>{LANG_PARTICIPANT}</th>
                <th style="width: 75px;">{LANG_ACTIONS}</th>
            </tr>

            {CONTENT}
        </table>
    </div>
</div>
