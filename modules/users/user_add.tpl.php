<?php die(); ?>

<div class="title">
    <h2>{USER_TITLE}</h2>
</div>

{ERROR_MSG}

<div id="user_add" class="card card-body">
    <form action="" method="post">
        <table class="list" cellpadding="0" cellspacing="0">
            {CONTENT}
            <tr>
                <td colspan="2" style="text-align: right;">
                    <input class="btn btn-secondary" type="button" onclick="window.location='?com={USER_COM_ID}';" value="{LANG_CANCEL}" />
                    <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SAVE}" />
                </td>
            </tr>
        </table>
    </form>
</div>