<?php die(); ?>

<div class="title">
    <h2>{USER_TITLE}</h2>
</div>

{USER_MSG}
{ERROR_MSG}

<form action="" method="post">
<table class="list" cellpadding="0" cellspacing="0">
    <tr><td colspan="2">&nbsp;</td></tr>
    {CONTENT}
    <tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" style="text-align: right;">
            <input class="submit" type="button" onclick="window.location='?com={USER_COM_ID}';" value="{LANG_CANCEL}" />
            <input class="submit" type="submit" name="submit" value="{LANG_SAVE}" />
		</td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
</table>
</form>