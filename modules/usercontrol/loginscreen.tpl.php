<?php die(); ?>

<div class="title">
    <h2>{LANG_LOGIN}</h2>
</div>
{LOGIN_MSG}

<div id="login_container">
	
		<form action="{LOGIN_ACTION}" method="post">
			<table class="list" cellpadding="0" cellspacing="0">
                <tr><td colspan="2">&nbsp;</td></tr>
                <tr>
                    <td><span>{LANG_EMAIL}:</span></td>
                    <td><input id="geb" type="text" name="geb" /></td>
                </tr>
                <tr>
                    <td><span>{LANG_PASSWORD}:</span></td>
                    <td><input id="wac" type="password" name="wac" /></td>
                </tr>
                <tr>
                    <td colspan="2"><input class="button" type="submit" name="submit" value="{LANG_LOGIN}" /></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
            </table>
		</form>
        
</div>
<div id="login_lost">
{NEW_CUSTOMER} - {LOGIN_LOST}
</div>
