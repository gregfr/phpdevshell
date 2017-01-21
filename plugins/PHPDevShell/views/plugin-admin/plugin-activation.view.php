<?php

/**
 * Class pluginActivationView
 *
 * @date 20160326 (1.0.1) (greg) added a leading slash to the pathes
 */
class pluginActivationView extends PHPDS_view
{
	public function execute()
	{
		$template = $this->template;

		$template->styleButtons();
		$template->styleTables();
		
		// Require JS.
		$template->addJsFileToHead("/themes/cloud/js/showhide/jquery.showhide.js");
		$template->addJsFileToHead("/themes/cloud/js/quickfilter/jquery.quickfilter.js");

	}
}

return 'pluginActivationView';


