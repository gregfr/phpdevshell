<?php

class readmeView extends PHPDS_view
{
	public function execute()
	{
		$template = $this->template;
		$template->styleTables();
		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();
	}
}

return 'readmeView';
