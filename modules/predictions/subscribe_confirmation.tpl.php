<?php die(); ?>

<div class="title">
    <h2>{COM_NAME}</h2>
</div>

<br />
<form action="" method="post" enctype="multipart/form-data">
<table class="list" cellpadding="0" cellspacing="0">
    <tr><td colspan="2">{CONFIRMATION_MESSAGE}</td></tr>
	<tr>
		<td colspan="2" style="text-align: right;">
            <input class="submit" type="button" onclick="window.location='?competition={COMPETITION_ID}&com={PREDICTION_COM_ID}';" value="{LANG_CANCEL}" />
            <input class="submit" type="submit" name="subscribe_confirmation" value="{LANG_SUBSCRIBE}" />
        </td>
    </tr>
</table>
</form>