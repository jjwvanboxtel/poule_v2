<?php die(); ?>

<div class="title">
    <h2>{LANG_LOGIN}</h2>
</div>

{LOGIN_MSG_WRAPPER}

<div id="login_container" class="card card-body">
    <form action="{LOGIN_ACTION}" method="post">
        <table class="list" cellpadding="0" cellspacing="0">
            <tr>
                <td><span class="form-label">{LANG_EMAIL}:</span></td>
                <td><input id="geb" class="form-control" type="text" name="geb" /></td>
            </tr>
            <tr>
                <td><span class="form-label">{LANG_PASSWORD}:</span></td>
                <td><input id="wac" class="form-control" type="password" name="wac" /></td>
            </tr>
            <tr>
                <td colspan="2" class="text-end">
                    <input class="btn btn-primary" type="submit" name="submit" value="{LANG_LOGIN}" />
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="login_lost" class="card card-body">
    <span style="white-space:nowrap">{NEW_CUSTOMER}&nbsp;-&nbsp;{LOGIN_LOST}</span>
</div>
