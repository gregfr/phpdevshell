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
    /** @var themeMods $this->template->mod The mods for the current theme */

    try {
        // activate all plugins which are set as "automatic"
        $template->activatePlugins('GUI_JS_AUTOMATIC');
    } catch (Exception $e) {
        $this->debug->warning($e->getMessage());
    }

    /** @noinspection PhpUndefinedVariableInspection */
    $url = !empty($aurl) ? $aurl : $template->outputAbsoluteURL('return');

    $theme_path = $this->core->themePath();

    if (empty($skin)) {
        /** @noinspection PhpUndefinedVariableInspection */
        $skin = $template->outputSkin('return');
    }

    $skin_path = $this->template->mod->jqueryUIpath($skin);
    $bower_path = $this->template->mod->bower();

    if ($this->configuration['development']) {
        ?>
        <!-- DEVELOPMENT DEPENDENCIES -->
        <?php

        $css_ref = array(
            $theme_path.'css/reset.css',
            $skin_path.'/jquery-ui.css',
            $skin_path.'/theme.css',
            $theme_path.'css/combined.css'
        );

        $js_ref = array(
            $bower_path.'/jquery/dist/jquery.js',
            $bower_path.'/jquery-ui/jquery-ui.js',
            $theme_path.'/js/PHPDS.js'
        );
    } else {
        ?>
        <!-- PRODUCTION DEPENDENCIES -->
        <?php

        $css_ref = array(
            $theme_path.'css/reset.min.css',
            $skin_path.'/jquery-ui.min.css',
            $skin_path.'/theme.min.css',
            $theme_path.'css/combined.min.css'
        );

        $js_ref = $theme_path.'/js/PHPDS-combined.min.js';

    }

    foreach ($css_ref as $onePath) {
        if ($onePath) {
            $this->template->addCssFileToHead('/'.$onePath);
        }
    }

    $js_code = '';
    if (is_scalar($js_ref)) {
        $js_ref = array($js_ref);
    }
    foreach ($js_ref as $onePath) {
        if ($onePath) {
            $js_code .= $this->template->mod->jsFileToHead($this->core->webPath('/'.$onePath));
        }
    }

    if ($template->flag('jquery_position') == 'bottom') {
        $this->template->modifyBottom = $js_code.$this->template->modifyBottom;
    } else {
        $this->template->modifyHead = $js_code.$this->template->modifyHead;
    }
?>


