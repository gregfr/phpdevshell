
<form action="{$self_url}" method="post" class="validate">
	<div class="row">
		<div class="column grid_4">
			<fieldset>
				<legend>{_e('Identification')}</legend>
				<p><label>{_e('Username')}<input id="user_name_test" type="text" size="30" name="user_name" value="{$user_name}" title="{_e('Your username which must be used in order to log in.')}" required="required"></label></p>
				<p><label>{_e('Password')}<input class="password_test" type="password" size="30" name="user_password" value="" title="{_e('Your user password is used in conjunction with your username to log into the system.')}" required="required"></label></p>
				<p><label>{_e('Verify Password')}<input type="password" size="30" name="verify_password" value="" title="{_e('Re-type the password entered above again, this is to verify that the password you entered is indeed correct.')}" required="required"></label></p>
				<p><label>{_e('User Display Name')}<input type="text" size="30" name="user_display_name" value="{$user_display_name}" title="{_e('This is your display name and is not used to log in with. It is used as a more appropriate identification method.')}" required="required"></label></p>
				<p><label>{_e('Email')}<input type="email" size="30" name="user_email" value="{$user_email}" title="{_e('Users email address is entered here.')}" required="required"></label></p>
			</fieldset>
		</div>
		<div class="column grid_4">
			<fieldset>
				<legend>{_e('Other Preferences')}</legend>
				{if $registration_selection != false}
				<p>
					<label>{_e('Select Registration Type')}
						<select name="token_id_option" title="{_e('Allows you to select in which group you will be added to when registration is completed successfully.')}">
							<option>...</option>
							{$registration_selection}
						</select>
					</label>
				</p>

				{/if}
				{if $token_key_field != false}
				<p>
					<label>
						{_e('Registration Token Key')} {$optional_token}
						{$token_key_field}
					</label>
				</p>
				{/if}
				{if $language_options == true}
				<p>
					<label>
						{_e('Preferred Language')}
						<select name="language" title="{_e('Your preferred language.')}">
							<option value="">...</option>
							{$language_options}
						</select>
					</label>
				</p>
				{/if}
				{if $region_options == true}
				<p>
					<label>
						{_e('Preferred Region')}
						<select name="region" title="{_e('Your preferred region.')}">
							<option value="">...</option>
							{$region_options}
						</select>
					</label>
				</p>
				{/if}
				{if $timezone_options == true}
				<p>
					<label>
						{_e('Preferred Timezone')}
						<select class="select" name="user_timezone" title="{_e('Your preferred timezone.')}">
							{$timezone_options}
						</select>
					</label>
				</p>
				<p><label>{_e('Format')}<input type="text" size="40" name="date_format_show" value="{$date_format_show}" readonly title="{_e('Date format preview.')}"></label></p>
				{/if}
				{$botBlockFields}
			</fieldset>
		</div>
		<div class="column grid_4 last">
			<fieldset>
				<legend>{_e('Submit')}</legend>
				<p>
					<button type="submit" name="save" value="save"><span class="submit"></span><span>{_e('Submit Registration')}</span></button>
					<button type="reset"><span class="reset"></span><span>{_e('Reset')}</span></button>
				</p>
			</fieldset>
		</div>
    </div>
</form>