<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{POULE_MSG}
<div class="action-row">
    {POULE_ADD}
</div>

<div id="poule" class="card">
    <div class="table-responsive">
        <table class="list">
            <tr>
                <th style="width: 40px;">{LANG_ID}</th>
                <th>{LANG_POULE_FULLNAME}</th>
                <th style="width: 75px;">{LANG_ACTIONS}</th>
            </tr>

            {CONTENT}
        </table>
    </div>
</div>
