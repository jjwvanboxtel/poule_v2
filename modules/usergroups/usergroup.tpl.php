<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>

{USERGROUP_MSG}
<br />
{LINK_ADD}
<br />

<div id="usergroup" class="card card-body">
    <div class="table-responsive">
        <table class="list" cellpadding="0" cellspacing="0">
            <tr>
                <th style="width: 20px;"></th>
                <th style="width: 40px;">{LANG_ID}</th>
                <th>{LANG_USERGROUP_NAME}</th>
                <th style="width: 110px;">{LANG_MEMBERCOUNT}</th>
                {ACTIONS}
            </tr>

            {CONTENT}
        </table>
    </div>
</div>
