<?php

class userRoleAdminListView extends PHPDS_view
{
    /**
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath()
     */
	public function execute()
	{
		$template = $this->template;

		$template->styleFloatHeaders();
		$template->stylePagination();
		$template->styleTables();
		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();

        $template->addJsFileToHead($this->core->themePath().'/js/quickfilter/jquery.quickfilter.js');
	}
}

return 'userRoleAdminListView';