<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
<div>{PARTICIPANT_MSG}</div>
<br />
{LANG_FILTER}:<br />
{FILTER_LIST}

<div id="participant" class="card card-body">
    <table class="list" cellpadding="0" cellspacing="0">
        <tr>
            <th style="width: 40px;">{LANG_ID}</th>
            <th>{LANG_PARTICIPANT}</th>
            <th style="width: 75px;">{LANG_ACTIONS}</th>
        </tr>

        {CONTENT}
    </table>
</div>