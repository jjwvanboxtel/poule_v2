<?php die(); ?>

<div class="title">
    <h2>{COMPETITION_TITLE}</h2>
</div>

<div>{ERROR_MSG}</div>

<div id="competition_add" class="card card-body">
    <form action="" method="post" enctype="multipart/form-data">
        <table class="list" cellpadding="0" cellspacing="0">
            {CONTENT}
            <tr>
                <td colspan="2" style="text-align: right;">
                    <input class="btn btn-secondary" type="button" onclick="window.location='?competition={COMPETITION_ID}&com={COMPETITION_COM_ID}';" value="{LANG_CANCEL}" />
                    <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SAVE}" />
                </td>
            </tr>
        </table>
    </form>
</div>