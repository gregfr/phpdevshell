<?php

    /**
     * Adds Pines Notify to PHPDevShell
     *
     * @see   http://pinesframework.org/pnotify/
     *
     * The theme first activate the plugin then instructs the template to gather the notifications
     * and inject them, like this:
     *     - theme.php calls
     *     - theme.php calls $template->outputNotifications()
     *     - $template->outputNotifications() uses $mod->notifications() to build the javascript calls
     *     - $template->outputNotifications() then inject the JS snippet with $template->addJsToHead($html);
     *
     * @since 3.5
     * @date 20130612 (1.0) (greg) added
     *
     */
class GUI_pnotify extends PHPDS_dependant implements iPHPDS_activableGUI
{

    /**
     * Inform the template about the files to add
     *
     * @since 3.5
     * @date 20130612 (1.0) (greg) added
     *
     * @return void
     */
    public function activate($path, $parameters = null)
    {
        $file = $this->configuration['development']
            ? 'jquery.pnotify.js'
            : 'jquery.pnotify.min.js';

        $template = $this->template;

        $template->addJsFileToBottom($path.'/public/'.$file);

        $template->addCssFileToHead($path.'/public/jquery.pnotify.default.css');
        $template->addCssFileToHead($path.'/public/jquery.pnotify.default.icons.css');
        $template->addCssFileToHead($path.'/public/oxygen/icons.css');

        $template->addToHead('
            <style>
                html > body .ui-pnotify { margin-top: 30px; }
                header #info { margin-top: 10px; }
            </style>
        ');

        $template->addJsToBottom(
         '
            try {
                $(function(){
                    $.pnotify.defaults.styling = "jqueryui";
                });
            }
            catch(e) { }
        '
        );
    }
}
