<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{USER_MSG}
<div class="action-row">
    {USER_ADD}
</div>
<div class="mb-3">
    {LANG_USERGROUP}: {USERGROUP_LIST}
</div>

<div id="user" class="card">
    <div class="table-responsive">
        <table class="list">
        <tr>
            <th style="width: 20px;"></th>
            <th style="width: 40px;">{LANG_ID}</th>
            <th>{LANG_USER_FULLNAME}</th>
            <th style="width: 20%;">{LANG_USERGROUP}</th>
            <th style="width: 75px;">{LANG_ACTIONS}</th>
        </tr>

        {CONTENT}
        </table>
    </div>
</div>