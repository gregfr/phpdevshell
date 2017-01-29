<?php

/**
 * Controller Class: Handles registration.
 * @author Jason Schoeman
 * @return string
 * @var $crud crud
 */
class Register extends PHPDS_controller
{
    protected $custom_prefix;

	public function execute()
	{
		$email = $this->factory('mailer');

		/* @var $userAction userActions */
		$userAction = $this->factory('userActions');

		/* @var $crud crud */
		$crud = $this->factory('crud');

		/* @var $spam botBlock */
		$spam = $this->factory('botBlock');

		$settings = $this->db->getSettings(array('allow_registration', 'verify_registration', 'move_verified_group', 'move_verified_role', 'registration_group', 'registration_role', 'email_new_registrations', 'setting_admin_email', 'languages_available', 'registration_message', 'reg_email_direct', 'reg_email_verify', 'reg_email_approve', 'reg_email_admin'), $this->custom_prefix);

		$approval_url_inset = '';
		$ban_url_inset = '';
		$token_key_field = '';
		$token_key_field_type = '';
		$token_id = '';
		$registration_selection = '';
		$optional_token = '';

		switch ($settings['allow_registration']) {
			// No registrations accepted.
			case 0:
				$this->template->heading(_('Account Registration Disabled'));
				$this->template->info(_('You cannot register on this system, public registration was disabled by the System Administrator.'));
				$this->template->warning(_('Please contact the System Administrator requesting to remove the registration link or to enable public registrations on this system.'));
				break;
			// All, allow default registrations and token registrations.
			case 1:
				$this->template->heading(_('Register Special or Private Account'));
				$this->template->info(_('You may register a new special or private account. After successful registration you can log-in to use the system.'));
				$optional_token = _('(Optional)');

				if (!$crud->GET('token_key')) {
                    $registration_selection = $this->db->invokeQuery('PHPDS_SelectTokensQuery');
                }

				$field_registration_tokens = $this->db->invokeQuery('PHPDS_CountTokensQuery');

				if ($field_registration_tokens > 0) {
                    $reg_info = _('If you received a registration token key, please enter this key into "Registration Token Key", if it was not automatically completed already.');
                }

				if ($crud->GET('token_key')) {
                    $token_key_field_type = 'class="boxdisabled" readonly';
                } else {
                    $token_key_field_type = 'class="boxnormal"';
                }

				if (!$crud->POST('save') && !empty($reg_info)) {
				    $this->template->notice($reg_info);
                }
				break;
			// Default registrations only.
			case 2:
				$this->template->heading(_('Register Private Account'));
				$this->template->info(_('You may register a new account. After successful registration you can log-in to use the system.'));

				break;
			// Token registrations only, only users with registration tokens can register.
			case 3:
				$this->template->heading(_('Register Special Account'));
				$this->template->info(_('You may register a special or invitation account only. After successful registration you can log-in to use the system.'));

				if ($crud->GET('token_key'))
					$token_key_field_type = 'class="boxdisabled" readonly';
				else
					$token_key_field_type = 'class="boxmand" required="required"';

				break;
		}

		if (!empty($settings['registration_message']))
				$this->template->message($settings['registration_message']);
		if ($settings['allow_registration'] == 1 || $settings['allow_registration'] == 3) {
			if ($crud->REQUEST('token_key'))
				$token_key___ = '<input type="text" size="50" name="token_key" value="' . $crud->REQUEST('token_key') . '" %s>';
			else
				$token_key___ = '<input type="text" size="50" name="token_key" value="" %s>';

			$token_key_field = $token_key___;
		} else {
			$token_key_field = false;
		}

		if ((boolean) $settings['allow_registration'] == true) {
			if ($crud->POST('save')) {
				$crypt_user_password = (string) md5($crud->POST('user_password'));
				$crypt_verify_password = (string) md5($crud->POST('verify_password'));
				$crud->addField('token_key');
				$crud->addField('language');
				$crud->addField('user_timezone');
				$crud->addField('region');

				if (!$crud->isAlphaNumeric('user_name') && !$crud->isEmail('user_name')) {
                    $crud->error(_('Please provide a clean alpha numeric string or email as username'));
                }

				if (!$crud->is('user_password')) {
                    $crud->error(_('Please provide a password'));
                }

				if (!$crud->is('verify_password')) {
                    $crud->error(_('Please provide a password verification'));
                }

				if (!$crud->is('user_display_name') && !$crud->isEmail('user_display_name')) {
                    $crud->error(_('Please provide a clean alpha numeric display name'));
                }

				if (!$crud->isEmail('user_email')) {
                    $crud->error(_('Please provide a valid email'));
                }

				if (!$crud->ok()) {
					$this->template->warning(_('You have provided incorrect #registration details, please try again.'));
					$crud->errorShow();
				} else {
					// Do the matching query to check if any users of this name, email or display name exists.
					$check_user_array = $this->db->invokeQuery('PHPDS_UserDetailQuery', $crud->f->user_name, $crud->f->user_display_name, $crud->f->user_email);

					// Get the results.
					$user_name_ = $check_user_array['user_name'];
					$user_display_name_ = $check_user_array['user_display_name'];
					$user_email_ = $check_user_array['user_email'];
					/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					/////////////////////// Below is a set of error checkings that the user must be approved by before he is submitted //////////////////////
					/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					// Check if user already exists.
					if ($user_name_ == $crud->f->user_name) {
                        $crud->error(_('The username you entered already exists'), 'user_name');
                    }

					// Check if this email already exists.
					if ($user_email_ == $crud->f->user_email) {
                        $crud->error(_('The user email you entered already exists'), 'user_email');
                    }

					// Check email string validity.
					if (!$crud->isEmail('user_email')) {
                        $crud->error(_('The email address you specified seems to be invalid'), 'user_email');
                    }

					// Check if password is correct length.
					if (!$crud->isMinLength('user_password', 4)) {
                        $crud->error(_('Your password is too short, make it at least 4 characters.'), 'user_password');
                    }

					// Check if password compares with verification password.
					if ($crypt_user_password !== $crypt_verify_password) {
                        $crud->error(_('The password does not match the second entered password'), 'user_password');
                    }

					// Switch registration types.
					switch ($settings['allow_registration']) {
						case 0:
							break;
						// All registrations allowed.
						case 1:
							if ($crud->is('token_key')) {
								$token_array = $this->db->invokeQuery('PHPDS_CheckTokenQuery', $crud->f->token_key);
								if (empty($token_array['token_id'])) {
									$crud->error(_('The #token key you entered was incorrect or #depleted.'));
								} else {
									$token_id = $token_array['token_id'];
									$token_user_role_id = $token_array['user_role_id'];
									$token_user_group_id = $token_array['user_group_id'];
								}
							} else if ($crud->is('token_id_option')) {
								$token_array = $this->db->invokeQuery('PHPDS_CheckTokenByIdQuery', $crud->f->token_id_option);
								if (empty($token_array['token_id'])) {
									$crud->error(_('The option you have chosen has depleted and is not available anymore, please select a new option.'));
								} else {
									$token_id = $token_array['token_id'];
									$token_user_role_id = $token_array['user_role_id'];
									$token_user_group_id = $token_array['user_group_id'];
								}
							}
							break;
						// Default registrations only.
						case 2:
							$token_id = 0;
							break;
						// Token registrations only, only users with registration tokens can register.
						case 3:
							if ($crud->is('token_key')) {
								$token_array = $this->db->invokeQuery('PHPDS_CheckTokenQuery', $crud->f->token_key);
								if (empty($token_array['token_id'])) {
									$crud->error(_('The token key you entered was not correct, please try again.'));
								} else {
									$token_id = $token_array['token_id'];
									$token_user_role_id = $token_array['user_role_id'];
									$token_user_group_id = $token_array['user_group_id'];
								}
							} else {
								$crud->error(_('The token key field is mandatory, please enter your key.'));
							}
							break;
					}
					if ($crud->ok() && $spam->block()) {
						// Phase 1 : Create data and save into db.
						switch ($settings['verify_registration']) {
							// Directly submitted.
							case 0:

								if (empty($token_id)) {
									$db_user_role = $settings['move_verified_role'];
									$db_user_group = $settings['move_verified_group'];
								} else {
									$db_user_role = $token_user_role_id;
									$db_user_group = $token_user_group_id;
									$deduct_token = true;
								}
								break;
							// Needs email verification.
							case 1:
								$db_user_role = $settings['registration_role'];
								$db_user_group = $settings['registration_group'];
								break;
							// Needs approval.
							case 2:
								$db_user_role = $settings['registration_role'];
								$db_user_group = $settings['registration_group'];
								break;
						}
						// Set some time variables for database and time submition check.
						$time_now = $this->configuration['time'];

						$crud->f->user_id = $this->db->invokeQuery('PHPDS_WriteRegQuery', $crud->f->user_display_name, $crud->f->user_name, $crypt_user_password, $crud->f->user_email, $db_user_group, $db_user_role, $time_now, $crud->f->language, $crud->f->user_timezone, $crud->f->region);
						$userAction->userRegister($crud->f);

						if ($crud->f->user_id) {
							// Deduct token for direct registration.
							if (isset($deduct_token) && !empty($token_id) && $crud->f->user_id)
								$this->db->invokeQuery('PHPDS_UpdateTokensQuery', $token_id);

							// Insert registration approval queue.
							if ($crud->f->user_id && ! empty($settings['verify_registration']))
								$this->db->invokeQuery('PHPDS_UpdateRegQueueQuery', $crud->f->user_id, $settings['verify_registration'], $token_id);

							// Phase 2 : Send out mail for success.
							switch ($settings['verify_registration']) {
								// Directly submitted.
								case 0:
									$delete_url = $this->navigation->buildURL('131201277', "du={$crud->f->user_id}");
									$delete_url_inset = "\r\n\r\n" . sprintf(_("Click on the url to DELETE this user: %s"), $delete_url);
									// Create email message for notification.
									$verification_message = sprintf(_($settings['reg_email_direct']), $crud->f->user_display_name, $this->configuration['scripts_name_version'], $this->configuration['absolute_url']);
									$verification_subject = sprintf(_('Registration request approved at %s.'), $this->configuration['scripts_name_version']);
									$verification_to = $crud->f->user_email;

									$email->sendmail("$verification_to", $verification_subject, $verification_message);

									$this->template->ok(sprintf(_('%s, your application for registration was successful, you may now log in.'), $crud->f->user_display_name));

									break;
								// Needs email verification.
								case 1:

									$delete_url = $this->navigation->buildURL('131201277', "du={$crud->f->user_id}");
									$delete_url_inset = "\r\n\r\n" . sprintf(_("Click on the url below to DELETE this user: %s"), $delete_url);
									$encrypted_url = (string) md5($crud->f->user_id.$crud->f->user_name.$crud->f->user_email);

									$registration_url = $this->navigation->buildURL('2143500606', "fa=$encrypted_url");
									$verification_message = sprintf(_($settings['reg_email_verify']), $crud->f->user_display_name, $this->configuration['scripts_name_version'], $registration_url, $this->configuration['absolute_url']);
									$verification_subject = sprintf(_('Registration verification at %s.'), $this->configuration['scripts_name_version']);
									$verification_to = $crud->f->user_email;
									// Send the verification email.
									if ($email->sendmail("$verification_to", $verification_subject, $verification_message)) {
										$this->template->ok(sprintf(_('%s, your application for registration was submitted, please verify your account by responding to the verification email that was sent to the specified email address.'), $crud->f->user_display_name));
									} else {
										$this->template->warning(_('An error occurred while trying to send this email. Please notify the Administrator of this problem.'));
										$this->db->invokeQuery('PHPDS_RollbackQuery');
									}

									break;
								// Needs approval.
								case 2:
									$ban_url = $this->navigation->buildURL('1210756465', "bu={$crud->f->user_id}");
									$ban_url_inset = "\r\n\r\n" . sprintf(_("Click on the url below to BAN this user: %s"), $ban_url);
									$approve_url = $this->navigation->buildURL('1210756465', "aue={$crud->f->user_id}");
									$approval_url_inset = "\r\n\r\n" . sprintf(_("Click on the url below to APPROVE this user: %s"), $approve_url);
									$delete_url = $this->navigation->buildURL('1210756465', "du={$crud->f->user_id}");
									$delete_url_inset = "\r\n\r\n" . sprintf(_("Click on the url below to DELETE this user: %s"), $delete_url);
									$verification_message = sprintf(_($settings['reg_email_approve']), $crud->f->user_display_name, $this->configuration['scripts_name_version'], $this->configuration['absolute_url']);
									$verification_subject = sprintf(_('Registration request pending at %s.'), $this->configuration['scripts_name_version']);
									$verification_to = $crud->f->user_email;

									$email->sendmail("$verification_to", $verification_subject, $verification_message);

									$this->template->ok(sprintf(_('%s, your application for registration was submitted, an authorized person will now review your submission for approval. Please allow some time for the approval process to be completed.'), $crud->f->user_display_name));

									break;
							}
							// Send a email to the system administrator.
							if ((boolean) $settings['email_new_registrations'] == true) {
								// Create email to admin message.
								$message_reg = sprintf(_($settings['reg_email_admin']), $this->configuration['scripts_name_version'], $crud->f->user_display_name, $this->core->formatTimeDate($this->configuration['time']), $crud->f->user_name, $this->configuration['scripts_name_version'], $approval_url_inset, $ban_url_inset, $delete_url_inset);
								$subject_reg = sprintf(_('New registration received at %s.'), $this->configuration['scripts_name_version']);
								$admin_to = $settings['setting_admin_email'];
								// Send the verification email.
								if ($email->sendmail("$admin_to", $subject_reg, $message_reg))
									$this->template->ok(_('The administrator was notified of this registration.'), false, false);
								else
									$this->template->warning(_('An error occurred while trying to send this email. Please notify the Administrator of this problem.'));
							}
						}
					} else $crud->errorShow();
				}
			}

			$iana = $this->factory('iana');
			$language_options = $iana->languageOptions($crud->f->language);
			$region_options = $iana->regionOptions($crud->f->region);

			$timezone = $this->factory('timeZone');
			$timezone_options = $timezone->timezoneOptions($crud->f->user_timezone);

			$token_key_field = sprintf($token_key_field, $token_key_field_type);


			// Load views.
			$view = $this->factory('views');

			$view->set('submit_registration', _('Submit Registration'));
			$view->set('self_url', $this->navigation->selfUrl());
			$view->set('user_name', $crud->f->user_name);
			$view->set('user_display_name', $crud->f->user_display_name);
			$view->set('user_email', $crud->f->user_email);
			$view->set('registration_selection', $registration_selection);
			$view->set('token_key_field', $token_key_field);
			$view->set('language_options', $language_options);
			$view->set('region_options', $region_options);
			$view->set('timezone_options', $timezone_options);
			$view->set('optional_token', $optional_token);
			$view->set('date_format_show', $this->core->formatTimeDate($this->configuration['time'], 'default', $crud->f->user_timezone));
			$view->set('botBlockFields', $spam->botBlockFields());
			// Output Template.
			$view->show();
		}
	}
}

return 'Register';
