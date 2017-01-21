<?php

/**
 * Controller Class: Simple readme to introduce PHPDevShell.
 * @author Jason Schoeman
 * @return string
 */
class ReadMe extends PHPDS_controller
{

	/**
	 * Execute Controller
	 * @author Jason Schoeman
	 */
	public function execute()
	{

		$this->notif->add("PHPDevShell will always try to keep true to keep it light, keep it simple, keep it stable. This is so that we can continue to fully support, maintain and improve the system without worrying about cluttering functionality.");
		$this->notif->add(array('Whats More?', 'You are now ready to start your new project! Good luck.'));

		$this->template->heading(_('Starting with PHPDevShell'));
		$this->template->info(_('Welcome to PHPDevShell Development Framework. This is a functioning test page and can now be disabled.'));
		/////////////////
		// Load views.
		$view = $this->factory('views');
		// Enable aggresive caching.
		$view->cachePage();

		// Check skin change.
		if (!empty($this->security->post['skin'])) {
			$skin_selected = $this->security->post['skin'];
			$this->db->invokeQuery('PHPDS_setSkin', $skin_selected);
			$this->template->ok(_('Reloading the skin now... if it does not work, refresh the page.'), 'print', 'nolog');
			$this->navigation->redirect($this->navigation->selfUrl(), 1);
		} else if (!empty($this->configuration['skin'])) {
			$skin_selected = $this->configuration['skin'];
		} else {
			$skin_selected = '';
		}

		// Read skins.
		$skins = $this->db->invokeQuery('PHPDS_readSkinOptions', $skin_selected);

		// Testing Notification Boxes.
		$error = $this->template->error('This is a sample error message, this can be written in log. ', 'return', 'nolog');
		$warning = $this->template->warning('This is a sample warning message, this can be written in log.', 'return', 'nolog');
		$critical = $this->template->critical('This is a sample critical message, this can be written in log and mailed.', 'return', 'nolog', 'nomail');
		$ok = $this->template->ok('This is a sample ok message, this can be written in log.', 'return', 'nolog');
		$notice = $this->template->notice('This is a sample notice message...', 'return');
		$busy = $this->template->busy('This is a sample busy message...', 'return');
		$message = $this->template->message('This is a sample message...', 'return');
		$note = $this->template->note('This is a sample note message...', 'return');
		$scripthead = $this->template->scripthead('Script Heading', 'return');

		$view->set('self_url', $this->navigation->selfUrl());
		$view->set('aurl', $this->configuration['absolute_url']);
		$view->set('error', $error);
		$view->set('skins', $skins);
		$view->set('warning', $warning);
		$view->set('critical', $critical);
		$view->set('ok', $ok);
		$view->set('notice', $notice);
		$view->set('busy', $busy);
		$view->set('message', $message);
		$view->set('note', $note);
		$view->set('scripthead', $scripthead);
		$view->set('urlbutton', "<a href=# class=\"button\">{$this->template->icon('tick', _('a Image with a link.'))}</a>");
		$view->set('img1', $this->template->icon('alarm-clock', _('Image Example 1')));
		$view->set('img2', $this->template->icon('calendar-share', _('Image Example 2')));
		$view->set('img3', $this->template->icon('hammer--plus', _('Image Example 3')));
		$view->set('img4', $this->template->icon('truck--pencil', _('Image Example 4')));

		// Set Values.
		$view->set('script_name', $this->configuration['phpdevshell_version']);

		// Output Template.
		$view->show();
	}
}

return 'ReadMe';
