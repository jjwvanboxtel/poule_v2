<?php die(); ?>

<div class="title">
    <h2>{USERGROUP_TITLE}</h2>
</div>

{ERROR_MSG}

<div id="usergroup_add" class="card card-body">
    <form action="" method="post">
        {USERGROUP_NAME}<br /><br />

        <table class="list" cellpadding="0" cellspacing="0">
            <tr>
                <th style="width: 20px;"></th>
                <th style="width: 40px;">{LANG_ID}</th>
                <th>{LANG_COMPONENT}</th>
                <th style="width: 40px;"><img src="templates/{TEMPLATE_NAME}/icons/page.png" alt="{LANG_READ}"></th>
                <th style="width: 40px;"><img src="templates/{TEMPLATE_NAME}/icons/page_add.png" alt="{LANG_ADD}"></th>
                <th style="width: 40px;"><img src="templates/{TEMPLATE_NAME}/icons/page_edit.png" alt="{LANG_EDIT}"></th>
                <th style="width: 40px;"><img src="templates/{TEMPLATE_NAME}/icons/page_delete.png" alt="{LANG_REMOVE}"></th>
            </tr>
            {CONTENT}
            <tr><td colspan="7">{LANG_COMPONENT_COUNT}: {USERGROUP_TOTAL}</td></tr>
            <tr>
                <td colspan="7" style="text-align: right;">
                    <input class="btn btn-secondary" type="button" onclick="window.location='?&com={USERGROUP_COM_ID}';" value="{LANG_CANCEL}" />
                    <input class="btn btn-primary" type="submit" name="submit" value="{LANG_SAVE}" />
                </td>
            </tr>
        </table>
    </form>
</div>
