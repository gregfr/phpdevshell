<?php

    /**
     * This file is included by theme master php files.
     * It's used to generate the proper "includes" statements for javascript, css, etc
     *
     * PHP version 5
     *
     * @category PHP
     * @package  Cloud
     * @author   jason <titanking@phpdevshell.org>
     * @author   greg <greg@phpdevshell.org>
     * @license  http://www.gnu.org/licenses/lgpl.html LGPL
     * @link     http://www.phpdevshell.org
     *
     * @version 2.0
     *
     * @date 20161126 (2.0) (greg) added support for "jquery_position" flag
     */

    /** @var $this PHPDS_core */
    /** @var $template PHPDS_template */

    try {
        // activate all plugins which are set as "automatic"
        $template->activatePlugins('GUI_JS_AUTOMATIC');
    } catch (Exception $e) {
        $this->debug->warning($e->getMessage());
    }

    /** @noinspection PhpUndefinedVariableInspection */
    $url = !empty($aurl) ? $aurl : $template->outputAbsoluteURL('return');

    if (empty($skin)) {
        /** @noinspection PhpUndefinedVariableInspection */
        $skin = $template->outputSkin('return');
    }

    if ($this->configuration['development']) {
        ?>

        <!-- DEVELOPMENT DEPENDENCIES -->

        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/css/reset.css"
              type="text/css"
              media="screen"/>
        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/jquery/css/<?php echo $skin; ?>/jquery-ui.css?v314a"
              type="text/css"
              media="screen"/>
        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/css/combined.css?v=314a"
              type="text/css"
              media="screen"/>


        <?php

        $js_ref = array($url.'/themes/cloud/jquery/js/jquery.js?v=314a',
                $url.'/themes/cloud/jquery/js/jquery-ui.js?v=314a',
                $url.'/themes/cloud/js/PHPDS.js?v=314a'
        );
    } else {

        ?>
        <!-- PRODUCTION DEPENDENCIES -->

        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/css/reset.min.css"
              type="text/css"
              media="screen"/>
        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/jquery/css/<?php echo $skin; ?>/jquery-ui.min.css"
              type="text/css"
              media="screen"/>
        <link rel="stylesheet"
              href="<?php echo $url ?>/themes/cloud/css/combined.min.css"
              type="text/css"
              media="screen"/>

        <?php

        $js_ref = $url.'/themes/cloud/PHPDS-combined.min.js';

    }

    $js_code = '';
    if (is_scalar($js_ref)) {
        $js_ref = array($js_ref);
    }
    foreach ($js_ref as $onePath) {
        if ($onePath) {
            $js_code .= $this->template->mod->jsFileToHead($this->core->webPath($onePath));
        }
    }

    if ($template->flag('jquery_position') == 'bottom') {
        $this->template->modifyBottom = $js_code.$this->template->modifyBottom;
    } else {
        $this->template->modifyHead = $js_code.$this->template->modifyHead;
    }
?>


