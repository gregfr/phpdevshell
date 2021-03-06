<?php

/**
 * Controller Class: Handles registration finalization.
 * @author Jason Schoeman
 * @return string
 */
class RegisterFinalize extends PHPDS_controller
{

	/**
	 * Execute Controller
	 * @author Jason Schoeman
	 */
	public function execute()
	{
		$this->template->heading(_('Finalizing Registration Process'));
		$this->template->info(_('Finalizing a new registered account on this system.'));

		$userAction = $this->factory('userActions');

		// Get setting values.
		$settings = $this->db->getSettings(array('move_verified_group', 'move_verified_role', 'verify_registration', 'allow_registration'));
		// Make sure we are allowed to accept registrations.
		if ($settings['verify_registration'] == 1) {
			// Do qualifying.
			if (! empty($this->security->get['fa'])) {
				$encrypted_url = $this->security->get['fa'];

				$fetch_user_array = $this->db->invokeQuery('PHPDS_ReadUserQuery', $encrypted_url);
				$edit = $fetch_user_array;

				if (!empty($edit['registration_type']) && !empty($edit['user_id']) && !empty($edit['user_display_name'])) {
					// Check if we have a token key, if we have we need to handle it.
					// Default registration approval.
					if (empty($edit['available_tokens']) && empty($edit['user_role_id']) && $settings['allow_registration'] != 3) {
						// Set update role and group.
						$update_role_id = $settings['move_verified_role'];
						$update_group_id = $settings['move_verified_group'];
					} // Token registration approval.
					else if (!empty($edit['available_tokens']) && !empty($edit['user_role_id'])) {
						// Set update role and group.
						$update_role_id = $edit['user_role_id'];
						$update_group_id = $edit['user_group_id'];
					}
					// Update user to correct group and role.
					if (!empty($update_role_id) && !empty($update_group_id) && !empty($edit['user_id'])) {
						// Update the users role and group status.
						$this->db->invokeQuery('PHPDS_UpdateUserQuery', $update_role_id, $update_group_id, $edit['user_id']);
						$userAction->userRegisterVerified($edit);
						// Set variable global for hooks.
						$this->template->global['user_id'] = $edit['user_id'];
						// It was updated so lets clear user from queue.
						$this->db->deleteQuick('_db_core_registration_queue', 'user_id', $edit['user_id']);
						// Deduct token value.
						if (! empty($edit['available_tokens'])) {
							$this->db->invokeQuery('PHPDS_UpdateTokens', $edit['token_id']);
							// Send update message.
							//$this->template->ok(sprintf(_('Registration process completed %s. You have finished registering a new account on %s for %s, you may now proceed to log-in.'), $edit['user_display_name'], $this->configuration['scripts_name_version'], $edit['token_name']));
						} else {
							//$this->template->ok(sprintf(_('Registration process completed %s. You have finished registering a new account on %s, you may now proceed to log-in.'), $edit['user_display_name'], $this->configuration['scripts_name_version']));
						}

                        $l = $this->factory('StandardLogin');
                        $user_array = $l->lookupUser($edit['user_name']);
                        $l->setLogin($user_array);

					} else {
						$this->template->warning('There was a problem with your registration, please contact the System Administrator.');
					}
				} else {
					$this->template->warning(_('Your registration entry was not found. The account you are trying to register does not exist, was already approved, banned or awaiting admin approval.'));
				}
			} else {
				$this->template->warning(_('There seems to be a problem with the finalization process, the system admin should be notified if you think this is an error.'));
			}
		} else {
			$this->template->warning(sprintf(_('%s does not accept any validation requests.'), $this->configuration['scripts_name_version']));
		}
        if ($this->user->isLoggedIn()) {
            header('Location: /');
        } else {
            header('Location: /login.html');
        }
	}
}

return 'RegisterFinalize';
