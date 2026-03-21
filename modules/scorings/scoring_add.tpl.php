<?php die(); ?>

<div class="title">
    <h2>{SCORING_TITLE}</h2>
</div>

<div>{ERROR_MSG}</div>

<form action="" method="post" enctype="multipart/form-data">
<table class="list" cellpadding="0" cellspacing="0">
    <tr><td colspan="2">&nbsp;</td></tr>
    {CONTENT}
    <tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" style="text-align: right;">
           <input class="submit" type="button" onclick="window.location='?competition={COMPETITION_ID}&com={SCORING_COM_ID}';" value="{LANG_CANCEL}" />
           <input class="submit" type="submit" name="submit" value="{LANG_SAVE}" />
		</td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
</table>
</form>