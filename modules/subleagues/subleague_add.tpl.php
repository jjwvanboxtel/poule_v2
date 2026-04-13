<?php die(); ?>

<div class="title">
    <h2>{SUBLEAGUE_TITLE}</h2>
</div>

{ERROR_MSG}

<div id="subleague_add" class="card card-body">
    <form action="" method="post">
        {CSRF_TOKEN}
        <table class="list" cellpadding="0" cellspacing="0">
            <tr><td colspan="2">&nbsp;</td></tr>
            {CONTENT}
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <input class="btn btn-secondary" type="button" onclick="window.location='?competition={COMPETITION_ID}&com={SUBLEAGUE_COM_ID}';" value="{LANG_CANCEL}" />
                    <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SAVE}" />
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
        </table>
    </form>
</div>