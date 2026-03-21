<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>
<div>{GAME_MSG}</div>
{GAME_ADD}

<table class="list" cellpadding="0" cellspacing="0">
    <tr>
        <th>{LANG_DATE}</th>
        <th>{LANG_CITY}</th>
        <th>{LANG_POULE}</th>
        <th colspan="2">{LANG_COUNTRY}</th>
        <th>{LANG_RESULT}</th>
        <th colspan="2">{LANG_COUNTRY}</th>
        <th><img src="templates/{TEMPLATE_NAME}/images/yellow_card.jpg" alt="{LANG_YELLOW_CARDS}" class="icon" /></th>
        <th><img src="templates/{TEMPLATE_NAME}/images/red_card.jpg" alt="{LANG_RED_CARDS}" class="icon" /></th>
        <th style="width: 75px;">{LANG_ACTIONS}</th>
    </tr>

    {CONTENT}
</table>