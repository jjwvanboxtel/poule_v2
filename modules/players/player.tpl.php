<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{PLAYER_MSG}
<br />
{PLAYER_ADD} {PLAYERS_ADD}
<br />

<div id="player" class="card card-body">
    <div class="table-responsive">
        <table class="list" cellpadding="0" cellspacing="0">
            <tr>
                <th style="width: 40px;">{LANG_ID}</th>
                <th>{LANG_PLAYER_FULLNAME}</th>
                <th>{LANG_COUNTRY}</th>
                <th style="width: 75px;">{LANG_ACTIONS}</th>
            </tr>

            {CONTENT}
        </table>
    </div>
</div>
