<?php

class lostPasswordView extends PHPDS_view
{
	public function execute()
	{
		$template = $this->template;

		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();
	}
}

return 'lostPasswordView';
