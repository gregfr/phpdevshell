<?php

/**
 * Class pluginActivationView
 *
 * @date 20160326 (1.0.1) (greg) added a leading slash to the pathes
 */
class pluginActivationView extends PHPDS_view
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

		$template->styleButtons();
		$template->styleTables();
		
		// Require JS.
        $this->template->addJsFileToHead($this->core->themePath().'/js/showhide/jquery.showhide.js');
        $this->template->addJsFileToHead($this->core->themePath().'/js/quickfilter/jquery.quickfilter.js');

	}
}

return 'pluginActivationView';


