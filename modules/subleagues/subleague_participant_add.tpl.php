<?php die(); ?>

<div class="title">
    <h2>{SUBLEAGUE_TITLE}</h2>
</div>

{SUBLEAGUE_MSG}

<div id="subleague_participant_add" class="card card-body">
    <form action="?competition={COMPETITION_ID}&com={SUBLEAGUE_COM_ID}&id={SUBLEAGUE_ID}&option=user_edit" method="post">
        <table class="list" cellpadding="0" cellspacing="0">
            <tr><td colspan="3">&nbsp;</td></tr>
            {CONTENT}
            <tr><td colspan="3">&nbsp;</td></tr>
            <tr>
                <td colspan="3" style="text-align: right;">
                    <input class="btn btn-secondary" type="button" onclick="window.location='?competition={COMPETITION_ID}&com={SUBLEAGUE_COM_ID}';" value="{LANG_CANCEL}" />
                </td>
            </tr>
            <tr><td colspan="3">&nbsp;</td></tr>
        </table>
    </form>
</div>