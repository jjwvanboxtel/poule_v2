<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
<div>{USER_MSG}</div>
{USER_ADD}
<br /><br />
{LANG_USERGROUP}:<br />
{USERGROUP_LIST}

<div id="user" class="card card-body">
    <table class="list" cellpadding="0" cellspacing="0">
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