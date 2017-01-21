<form action="{$self_url}" method="post" class="validate">
    <div class="row">
        <div class="column grid_4">
			<fieldset>
				<legend>{_e('Recover Account')}</legend>
				<p><label>{_e('Your Username or Email')}<input type="text" size="40" name="user_name" value="{$username}" title="{_e('Your username which must be used in order to log in.')}" required="required"></label></p>
				<p>
					<button type="submit" name="send" value="send"><span class="submit"></span><span>{_e('Send Recovery')}</span></button>
					<button type="reset"><span class="reset"></span><span>{_e('Reset')}</span></button>
				</p>
			</fieldset>
        </div>
    </div>
</form>