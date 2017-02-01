<?php

class registerView extends PHPDS_view
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

		$template->styleForms();
		$template->validateForms();
		$template->styleButtons();

        $template->addJsFileToHead($this->core->themePath().'/js/password/jquery.password.js');

		$shortPass = _('Too Short');
		$badPass = _('Weak');
		$goodPass = _('Good');
		$strongPass = _('Strong');
		$samePass = _('Username and Password identical!');
		$passwordMeter = <<<JS
           $(document).ready(function() {
                    $.fn.shortPass = '{$shortPass}';
                    $.fn.badPass = '{$badPass}';
                    $.fn.goodPass = '{$goodPass}';
                    $.fn.strongPass = '{$strongPass}';
                    $.fn.samePassword = '{$samePass}';
                    $.fn.resultStyle = "";
                    $(".password_test").passStrength({
                        shortPass: 		"critical",
                        badPass:		"warning",
                        goodPass:		"notice",
                        strongPass:		"ok",
                        baseStyle:		"passwordMeter",
                        userid:         "#user_name_test"
                    });
            });
JS;
		$template->addJsToHead($passwordMeter);
		$template->styleSelect();
	}
}

return 'registerView';