<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
{PLAYER_MSG}
<div class="action-row">
    {PLAYER_ADD} {PLAYERS_ADD}
</div>

<div id="player" class="card">
    <div class="table-responsive">
        <table class="list">
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
