<?php

class generalSettingsView extends PHPDS_view
{
    /**
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath() ; improved readability
     */
	public function execute()
	{
		$template = $this->template;

		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();

		// Require JS.
		// Why call it biscuit and not cookie you ask? Well filename cookie gets blocked by mod_security.

        $template->addJsFileToHead($this->template->mod->bower()."/jquery-ui/ui/widgets/tabs.js");
        $template->addJsFileToHead($this->core->themePath().'/js/biscuit/jquery.biscuit.js');
        $template->addJsFileToHead($this->core->themePath().'/js/tabs/jquery.tabs.js');
		$template->styleSelect();

        $template->addCSSToHead('
	        TEXTAREA { width: 94%; }
	        .message_variables { display: none; }
        ');
        $template->addJsToBottom("
	        var mvs = $('.message_variables');
	        $('#user-reg-settings TEXTAREA').on('focus', function () {
	            var mv = $(this).siblings('.message_variables');
	            mvs.not(mv).hide();
	            mv.show();
	        });
	    ");

	}
}

return 'generalSettingsView';