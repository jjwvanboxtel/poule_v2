<?php die(); ?>

<h1>{LANG_USER_ADD}</h1>

<div id= "add_user" class="card card-body">
	<form action="?option=add" method="post">
		<table class="listAddEdit" cellpadding="0" cellspacing="0">
			<tr>
				<td style="width: 145px;">Firstname: </td>
				<td style="text-align: right;"><input class="form-control" type="text" name="firstName" /></td>
			</tr>
			<tr>
				<td>Lastname: </td>
				<td style="text-align: right;"><input class="form-control" type="text" name="lastName" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: right;"><input class="btn btn-primary" type="submit" name="submit" value="{LANG_SAVE}" /></td>
			</tr>
		</table>
	</form>
</div>