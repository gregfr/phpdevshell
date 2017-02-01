<?php

class menuItemAdminPermissionsView extends PHPDS_view
{
    /*
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath()
     */
	public function execute()
	{
		$template = $this->template;

		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();
		$template->styleFloatHeaders();
		$template->styleTables();

        $this->template->addJsFileToHead($this->core->themePath().'/js/quickfilter/jquery.quickfilter.js');
	}
}

return 'menuItemAdminPermissionsView';
