<?php

interface iPHPDS_activableGUI
{
    public function activate($path, $parameters = null);
}

/**
 * Class responsible to deal with the visual representation of a page.
 * 
 * Interact with various other components such as views, themes, ...
 * 
 */
class PHPDS_template extends PHPDS_dependant
{
    /**
     * Contains script HTML data.
     *
     * @var string
     */
    public $HTML = '';
    /**
     * Contains script HOOK data.
     *
     * @var string
     */
    public $HOOK = '';
    /**
     * Use to manage the view class.
     *
     * @var object
     */
    public $view;
    /**
     * Adds content to head of page.
     * @var string
     */
    public $modifyHead = '';
    /**
     * Adds content to the bottom of the page (useful for javascript plugins)
     * @var string
     */
    public $modifyBottom = '';
    /**
     * Modify Output Text Logo
     * @var mixed
     */
    public $modifyOutputTextLogo = false;
    /**
     * Modify Output Logo
     * @var mixed
     */
    public $modifyOutputLogo = false;
    /**
     * Modify Output Time
     * @var mixed
     */
    public $modifyOutputTime = false;
    /**
     * Modify Output Login Link.
     * @var mixed
     */
    public $modifyOutputLoginLink = false;
    /**
     * Modify Output User.
     * @var mixed
     */
    public $modifyOutputUser = false;
    /**
     * Modify Output Role.
     * @var mixed
     */
    public $modifyOutputRole = false;
    /**
     * Modify Output Group.
     * @var mixed
     */
    public $modifyOutputGroup = false;
    /**
     * Modify Output Title.
     * @var mixed
     */
    public $modifyOutputTitle = false;
    /**
     * Modify Output Menu.
     * @var mixed
     */
    public $modifyOutputMenu = false;
    /**
     * Modify Output Breadcrumbs.
     * @var mixed
     */
    public $modifyOutputBreadcrumbs = false;
    /**
     * Modify Output Footer.
     * @var mixed
     */
    public $modifyOutputFooter = false;
    /**
     * Modify Output Controller.
     * @var mixed
     */
    public $modifyOutputController = false;
    /**
     * Check if lightbox headers should be added for lightbox node.
     * @var type
     */
    public $lightbox = false;
    /**
     * Use this to have global available variables throughout scripts. For instance in hooks.
     *
     * @var array
     */
    public $global;
    /**
     * Sends a message to login form.
     * @var string
     */
    public $loginMessage;
    /**
     * Stores module methods.
     *
     * @var object
     */
    public $mod;
    /**
     * Content Distribution Network.
     * If you are running a very large site, you might want to consider running a dedicated light http server (httpdlight, nginx) that
     * only serves static content like images and static files, call it a CDN if you like.
     * By adding a host here 'http://192.34.22.33/project/cdn', all images etc, of PHPDevShell will be loaded from this address.
     * @var string
     */
    public $CDN;

    /**
     * Main template system constructor.
     */
    public function construct()
    {
        $configuration = $this->configuration;
        if (!empty($configuration['static_content_host'])) {
            $this->CDN = $configuration['static_content_host'];
        } else {
            $this->CDN = isset($configuration['absolute_url']) ? $configuration['absolute_url'] : '';
        }
    }

    /**
     * Will add any css path to the <head></head> tags of your document.
     *
     * @param string $cssRelativePath
     * @param string $media
     * @param boolean $delayed
     */
    public function addCssFileToHead ($cssRelativePath = '', $media='screen', $delayed = false)
    {
        if (is_scalar($cssRelativePath)) {
            $cssRelativePath = array($cssRelativePath);
        }
        foreach ($cssRelativePath as $oneCssRelativePath) {
            $onePath = $this->core->webPath($oneCssRelativePath);
            if ($delayed) {
                $this->modifyHead .= $this->mod->cssFileDelayed($onePath, $media);
            } else {
                $this->modifyHead .= $this->mod->cssFileToHead($onePath, $media);
            }
        }
    }

    /**
     * Will add any js path to the <head></head> tags of your document.
     *
     * @date 20151217 (1.0.1) (greg) rewrite
     *
     * @param string|array $jsRelativePath
     */
    public function addJsFileToHead ($jsRelativePath = '')
    {
        if (is_scalar($jsRelativePath)) {
            $jsRelativePath = array($jsRelativePath);
        }
        foreach ($jsRelativePath as $onePath) {
            $this->modifyHead .= $this->mod->jsFileToHead($this->core->webPath($onePath));
        }
    }

    /**
     * Will add any content to the <head></head> tags of your document.
     *
     * @param string $extraHead
     */
    public function addToHead ($giveHead = '') {
        $this->modifyHead .= $this->mod->addToHead($giveHead);
    }

    /**
     * Will add any js to the <head></head> tags of your document adding script tags.
     *
     * @param string $js
     */
    public function addJsToHead ($js = '') {
        $this->modifyHead .=  $this->mod->addJsToHead($js);
    }

    /**
     * Will add any js to the bottom of the page, after the controller's content
     *
     * @version 1.0
     *
     * @date    20140611 (1.0) (greg) added
     *
     * @param string $js
     */
    public function addJsToBottom($js = '')
    {
        $this->modifyBottom .= $this->mod->addJsToHead($js);
    }

    /**
     * Add a reference to the JS file at the bottom of the page, as per good practices
     *
     * @version 1.1
     *
     * @date 20151217 (1.1) (greg) using core->webPath to allow more flexible path
     * @date    20140611 (1.0) (greg) added
     *
     * @param string|array $pathToJs
     *
     */
    public function addJsFileToBottom($pathToJs = '')
    {
        if (is_scalar($pathToJs)) {
            $pathToJs = array($pathToJs);
        }
        foreach ($pathToJs as $onePath) {
            if ($onePath) {
                $this->modifyBottom .= $this->mod->jsFileToHead($this->core->webPath($onePath));
            }
        }
    }

    /**
     * Will add any css to the <head></head> tags of your document adding script tags.
     *
     * @param string $css
     */
    public function addCSSToHead ($css = '') {
        $this->modifyHead .= $this->mod->addCssToHead($css);
    }

    /**
     * Activate a GUI plugin, i.e. give the plugin the opportunity to do
     * whatever is needed so be usable from the Javascript code
     *
     * @param string $plugin     the name of the plugin
     * @param mixed  $parameters (optional) parameters if the plugin have ones
     *
     * @return iPHPDS_activableGUI the plugin
     *
     * @version 1.1.1
     *
     * @date 20151217 (1.1.1) (greg) make path absolute
     * @date    20130614 (1.1) (greg) check for non-activated plugins
     *
     * @throws PHPDS_exception
     */
    public function activatePlugin($plugin, $parameters = null)
    {
        $parameters = func_get_args();
        $path = $this->classFactory->classFolder($plugin);
        if (empty($path)) {
            $msg = sprintf(__('Cannot start plugin "%s" with an empty path: a plugin must be activated to be used'), $plugin);
            throw new PHPDS_exception($msg);
        }
        $plugin = $this->factory(array('classname' => $plugin, 'factor' => 'singleton'));
        if (is_a($plugin, 'iPHPDS_activableGUI')) {
            $plugin->activate('/'.$path, $parameters);
        }

        return $plugin;
    }


    /**
     * Activates all GUI plugins which have the given name as an alias
     *
     * @date 20150310 (1.0) (greg) added
     * @version 1.0
     * @since 3.5
     * @author greg <greg@phpdevshell.org>
     *
     * @param string $alias the alias name
     * @param mixed $parameters (optional)
     *
     */
    public function activatePlugins($alias, $parameters = null)
    {
        $plugins = $this->classFactory->aliasList($alias);
        foreach ($plugins as $plugin) {
            $this->activatePlugin($plugin['class_name'], $parameters);
        }
    }

    /**
     * Changes head output.
     * @param boolean $return
     * @return string|null
     */
    public function outputHead ($return = false)
    {
        if (! empty($this->configuration['custom_css'])) {
            $this->addCssFileToHead($this->configuration['custom_css']);
        }

        // Check if we should return or print.
        if ($return == false) {
            // Simply output charset.
            print $this->modifyHead;
        }
        return $this->modifyHead;
    }

    /**
     * Outputs current language identifier being used.
     *
     * @author Jason Schoeman
     */
    public function outputLanguage ($return = false)
    {
        // Check if we should return or print.
        if ($return == false) {
            // Simply output charset.
            print $this->configuration['language'];
        }
        return $this->configuration['language'];
    }

    /**
     * Outputs charset.
     *
     * @author Jason Schoeman
     */
    public function outputCharset ($return = false)
    {
        // Check if we should return or print.
        if ($return == false) {
            // Simply output charset.
            print $this->configuration['charset'];
        } else {
            return $this->configuration['charset'];
        }
    }

    /**
     * Outputs the active scripts title.
     *
     * @author Jason Schoeman
     */
    public function outputTitle ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputTitle == false) {
            $navigation = $this->navigation->navigation;
            if (isset($navigation[$this->configuration['m']]['menu_name'])) {
                print $this->mod->title($navigation[$this->configuration['m']]['menu_name'], $this->configuration['scripts_name_version']);
            } else {
                print $this->core->haltController['message'];
            }
        } else {
            print $this->modifyOutputTitle;
        }
    }

    /**
     * Outputs the active scripts title.
     *
     * @author Jason Schoeman
     */
    public function outputName ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputTitle == false) {
            $navigation = $this->navigation->navigation;
            if (isset($navigation[$this->configuration['m']]['menu_name'])) {
                print $navigation[$this->configuration['m']]['menu_name'];
            } else {
                print $this->core->haltController['message'];
            }
        } else {
            print $this->modifyOutputTitle;
        }
    }

    /**
     * This returns/prints the skin for inside theme usage.
     *
     * @param mixed default is print, can be set true, print, return.
     * @return string Skin.
     * @author Jason Schoeman
     */
    public function outputSkin ($return = 'print')
    {
        // Create HTML.
        $html = $this->configuration['skin'];

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            print $html;
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints the absolute url for inside theme usage.
     *
     * @param mixed default is print, can be set true, print, return.
     * @return string Absolute url.
     * @author Jason Schoeman
     */
    public function outputAbsoluteURL ($return = 'print')
    {
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            print $this->CDN;
        } else if ($return === 'return' || $return == true) {
            return $this->CDN;
        }
    }

    /**
     * This returns/prints the meta keywords for inside theme usage.
     *
     * @param mixed default is print, can be set true, print, return.
     * @return string Meta Keywords.
     * @author Jason Schoeman
     */
    public function outputMetaKeywords ($return = 'print')
    {
        // Create HTML.
        $html = $this->configuration['meta_keywords'];

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            print $html;
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints the meta description for inside theme usage.
     *
     * @param mixed default is print, can be set true, print, return.
     * @return string Meta Description.
     * @author Jason Schoeman
     */
    public function outputMetaDescription ($return = 'print')
    {
        // Create HTML.
        $html = $this->configuration['meta_description'];

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            print $html;
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * Gets the desired logo and displays it. This method will try its best to deliver a logo, whatever the case.
     *
     * @author Jason Schoeman
     */
    public function outputLogo ()
    {
        $configuration = $this->configuration;

        if ($this->modifyOutputLogo == false) {
            // First we need to see if we will be using the custom logo.
            if (! empty($configuration['custom_logo'])) {
                // Give him his custom logo.
                $logo = $this->mod->logo($configuration['absolute_url']. '/', $this->CDN . '/' . $configuration['custom_logo'], $configuration['scripts_name_version'], $configuration['scripts_name_version']);
            } else {
                // Ok so we have no set logo, does the developer want a custom logo?
                if (! empty($this->db->pluginLogo)) {
                        // Ok lets get the logo that the user wishes to display.
                        $logo = $this->mod->logo($configuration['absolute_url'], "{$this->CDN}/plugins/{$this->db->pluginLogo}/images/logo.png", $configuration['scripts_name_version'], $configuration['scripts_name_version']);
                } else if (! empty($configuration['scripts_name_version'])) {
                        $logo = $this->mod->logoText($configuration['scripts_name_version']);
                } else {
                        // Oops we have no logo, so lets just default to the orginal PHPDevShell logo.
                        $logo = $this->mod->logo($configuration['absolute_url'], "{$this->CDN}/plugins/PHPDevShell/images/logo.png", $configuration['scripts_name_version'], $configuration['scripts_name_version']);
                }
            }
            // Ok return the logo.
            print $logo;
        } else {
            print $this->modifyOutputLogo;
        }
    }

    /**
     * Acquire script identification image or logo.
     *
     * @param string $menu_link
     * @param string $active_plugin
     * @param string $alias
     * @param int $is_parent
     */
    public function scriptLogo ($menu_link, $active_plugin, $alias = null, $is_parent=null)
    {
        // Find last occurance.
        $filename_from = strrchr($menu_link, '/');
        if (empty($filename_from)) $filename_from = $menu_link;
        // Set image name.
        $image_name = ltrim(PU_rightTrim($filename_from, '.php'), '/');
        // Create image url.
        $img_url_alias =!empty($alias) ?  "plugins/$active_plugin/images/$alias.png": '';
        $img_url = "plugins/$active_plugin/images/$image_name.png";
        $image_url_plugin_default = "plugins/$active_plugin/images/default.png";
        $image_url_root_default = "plugins/$active_plugin/images/default-root.png";
        // Lets check if image exists, if not, we need to set it to use default.
        if ($img_url_alias && file_exists($img_url_alias)) {
            return $this->CDN . '/' . $img_url_alias;
        } elseif (file_exists($img_url)) {
            return $this->CDN . '/' . $img_url;
        } elseif (file_exists($image_url_plugin_default) && !$is_parent) {
            return $this->CDN . '/' . $image_url_plugin_default;
        } elseif (file_exists($image_url_root_default) && $is_parent) {
            return $this->CDN . '/' . $image_url_root_default;
        } elseif (!file_exists($image_url_root_default) && $is_parent) {
            return $this->CDN . '/plugins/PHPDevShell/images/default-root.png';
        } else {
            return $this->CDN . '/plugins/PHPDevShell/images/default.png';
        }
    }

    /**
     * Sets template time.
     *
     * @author Jason Schoeman
     * @date 20120306 (greg) replace double equal with triple equal
     */
    public function outputTime ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputTime === false) {
            // Output active info.
            print $this->mod->formatTimeDate($this->configuration['time']);
        } else {
            print $this->modifyOutputTime;
        }
    }

    /**
     * Sets template login link.
     *
     * @author Jason Schoeman
     */
    public function outputLoginLink ()
    {
        $navigation = $this->navigation;
        $configuration = $this->configuration;

        // Check if output should be modified.
        if ($this->modifyOutputLoginLink == false) {
            if ($this->user->isLoggedIn()) {
                $login_information = $this->mod->loggedInInfo($navigation->buildURL($configuration['loginandout'], 'logout=1'), $configuration['user_display_name']);
            } else {
                $inoutpage = isset($navigation->navigation[$configuration['loginandout']]) ?
                    $navigation->navigation[$configuration['loginandout']]['menu_name'] : ___('Login');
                $login_information = $this->mod->logInInfo($navigation->buildURL($configuration['loginandout']), $inoutpage);
            }
            // Output active info.
            print $login_information;
        } else {
            print $this->modifyOutputLoginLink;
        }
    }

    /**
     * Sets template role.
     *
     * @author Jason Schoeman
     */
    public function outputRole ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputRole == false) {
            // Set active role.
            $active_role = '';
            if ($this->user->isLoggedIn())
                $active_role = $this->mod->role(___('Role'), $this->configuration['user_role_name']);
            // Output active info.
            print $active_role;
        } else {
            print $this->modifyOutputRole;
        }
    }

    /**
     * Sets template group.
     *
     * @author Jason Schoeman
     */
    public function outputGroup ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputGroup == false) {
            // Set active role.
            $active_group = '';
            if ($this->user->isLoggedIn())
                $active_group = $this->mod->group(___('Group'), $this->configuration['user_group_name']);
            // Output active info.
            print $active_group;
        } else {
            print $this->modifyOutputGroup;
        }
    }

    /**
     * This returns/prints an image of the current script running.
     *
     * @param boolean Default is false, if set true, the heading will return instead of print.
     * @return string Returns image tag with image url.
     * @author Jason Schoeman
     */
    public function outputScriptIcon ($return = false)
    {
        $navigation = $this->navigation->navigation;
        // Create script logo ////////////////////////////////////////////////////////////////////////////
        if (! empty($navigation[$this->configuration['m']]['menu_id'])) {
            $script_logo_url = $this->scriptLogo($navigation[$this->configuration['m']]['menu_link'], $navigation[$this->configuration['m']]['plugin']);
            //////////////////////////////////////////////////////////////////////////////////////////////////
            $menu_name = $navigation[$this->configuration['m']]['menu_name'];
            // Create HTML.
            $html = $this->mod->scriptIcon($script_logo_url, $menu_name);
            // Return or print to browser.
            if ($return == false) {
                print $html;
            } else if ($return == true) {
                return $html;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns "breadcrumbs" to the template system. Intended to be used by the engine.
     *
     * @author Jason Schoeman
     */
    public function outputBreadcrumbs ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputBreadcrumbs == false) {
            print $this->navigation->createBreadcrumbs();
        } else {
            print $this->modifyOutputBreadcrumbs;
        }
    }

    /**
     * Returns "menus" to the template system. Intended to be used by the engine.
     *
     * @author Jason Schoeman
     */
    public function outputMenu ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputMenu == false) {
            print $this->navigation->createMenuStructure();
        } else {
            print $this->modifyOutputMenu;
        }
    }

    /**
     * Returns "output script" to the template system. Intended to be used by the engine.
     *
     * @author Jason Schoeman
     */
    public function outputScript ()
    {
        $this->outputController();
    }

    /**
     * Returns "output script" to the template system. Intended to be used by the engine.
     *
     * @author Jason Schoeman
     */
    public function outputController ()
    {
        if ($this->modifyOutputController == false) {
            print $this->core->data;
        } else {
            print $this->modifyOutputController;
        }
    }

    /**
     * Sets template system logo or name.
     *
     * @author Jason Schoeman
     */
    public function outputTextLogo ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputTextLogo == false) {
            // Output active info.
            print $this->configuration['scripts_name_version'];
        } else {
            print $this->modifyOutputTextLogo;
        }
    }

    /**
     * Returns the last footer string to the template system. Intended to be used by the engine.
     *
     * @author Jason Schoeman
     */
    public function outputFooter ()
    {
        // Check if output should be modified.
        if ($this->modifyOutputFooter == false) {
            print $this->configuration['footer_notes'];
        } else {
            print $this->modifyOutputFooter;
        }
    }

    /**
     * Will add code from configuration to theme closing body tag.
     *
     * @author Jason Schoeman
     */
    public function outputFooterJS ()
    {

        if (!empty($this->modifyBottom)) {
            print $this->modifyBottom;
        }
        print $this->configuration['footer_js'];
    }

    /**
     * This method is used to load a widget at into a certain location of your page.
     *
     * @author Jason Schoeman
     * @since V 3.0.5
     */
    public function requestWidget ($menu_id_to_load, $element_id, $extend_url = '', $settings = '')
    {
        if (! empty($this->navigation->navigation["$menu_id_to_load"])) {

            $widget_url = $this->navigation->buildURL($menu_id_to_load, $extend_url, true);
            $text = sprintf(___('Busy Loading <strong>%s</strong>...'), $this->navigation->navigation["$menu_id_to_load"]['menu_name']);

            // Widget ajax code...
            $JS = $this->mod->widget($widget_url, $element_id, $text, $settings);

            $this->addJsToHead($JS);

            return true;
        } else {
            return false;
        }
    }

    /**
     * This method is used to load ajax into a certain location of your page.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     * @since V 3.0.5
     */
    public function requestAjax ($menu_id_to_load, $element_id, $extend_url = '', $settings = '')
    {
        if (! empty($this->navigation->navigation["$menu_id_to_load"])) {

            $ajax_url = $this->navigation->buildURL($menu_id_to_load, $extend_url, true);
            $text = sprintf(___('Busy Loading <strong>%s</strong>...'), $this->navigation->navigation["$menu_id_to_load"]['menu_name']);

            // Ajax code...
            $JS = $this->mod->ajax($ajax_url, $element_id, $text, $settings);

            $this->addJsToBottom($JS);

            return true;
        } else {
            return false;
        }
    }

    /**
     * This method is used to load a lightbox page.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     * @since V 3.0.5
     */
    public function requestLightbox ($menu_id_to_load, $element_id, $extend_url = '', $settings = '')
    {
        if (! empty($this->navigation->navigation["$menu_id_to_load"])) {

            $this->lightbox = true;

            $this->addJsFileToBottom($this->mod->lightBoxScript());
            $this->addCssFileToHead($this->mod->lightBoxCss());

            $lightbox_url = $this->navigation->buildURL($menu_id_to_load, $extend_url, true);

            // Jquery code...
            $JS = $this->mod->lightBox($element_id, $settings = '');

            $this->addJsToBottom($JS);

            return $lightbox_url;
        } else {
            return false;
        }
    }

    /**
     * This returns/prints a heading discription of the script being executed. Intended to be used by the developer.
     *
     * @version 1.1
     *
     * @date 20110309 (v1.1) (greg) changed to use the pieces repository
     * @date 20110309 (v1.2) (jason) good idea but it wont work as heading is not mandatory in controllers.
     *
     * @param string $heading This is the message that will be displayed as the heading.
     * @param string $return should 'return' or 'print'? (optional, default is 'print')
     * @return string|null
     * @author Jason Schoeman
     */
    public function heading ($heading, $return = 'print')
    {
        $html = $this->mod->heading($heading);
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            print $html;
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
        return null;
    }

    /**
     * Pushes javascript to <head> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function styleButtons ()
    {
        $this->addJsToBottom($this->mod->styleButtons());
    }

    /**
     * Pushes javascript to <head> for validationg purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function validateForms ()
    {
        $this->addJsFileToBottom($this->mod->formsValidateJs());
        $this->addJsToBottom($this->mod->formsValidate());
    }

    /**
     * Pushes javascript to <head> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function styleForms ()
    {
        $this->addJsToBottom($this->mod->styleForms());
    }

    /**
     * Pushes javascript to <head> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function styleFloatHeaders ()
    {
        $this->addJsFileToBottom($this->mod->styleFloatHeadersScript());
        $this->addJsToBottom($this->mod->styleFloatHeaders());
    }

    /**
     * Pushes javascript to <head> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function styleTables ()
    {
        $this->addJsToBottom($this->mod->styleTables());
    }

    /**
     * Pushes javascript to <head> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Jason Schoeman
     */
    public function stylePagination ()
    {
        $this->addJsToBottom($this->mod->stylePagination());
    }

    /**
     * Pushes javascript to <select> for styling purposes.
     *
     * @version 1.1
     *
     * @date 20150319 (1.1) (greg) add JS to bottom instead of top
     *
     * @author Don Schoeman
     */
    public function styleSelect ()
    {
        $this->addJsFileToBottom($this->mod->styleSelectJs());
        $this->addJsToBottom($this->mod->styleSelectHeader());

    }

    /**
     * Calls a single jquery-ui effect plugin and includes it inside head.
     *
     * @version 1.1.1
     *
     * @date 20150319 (1.1.1) (greg) add JS to bottom instead of top
     * @date 20120606 (1.1) (greg) added support for multiple times
     *
     * @param string $plugin Plugin name (multiple times)
     * @author Jason Schoeman
     */
    public function jqueryEffect($plugin)
    {
        foreach(func_get_args() as $plugin) {
            $this->addJsFileToBottom($this->mod->jqueryEffect($plugin));
        }
    }

    /**
     * Calls a single jquery-ui plugin and includes it inside head.
     *
     * @version 1.1.1
     *
     * @date 20150319 (1.1.1) (greg) add JS to bottom instead of top
     * @date 20120606 (1.1) (greg) added support for multiple times
     *
     * @param string $plugin Plugin name (multiple times)
     * @author Jason Schoeman
     */
    public function jqueryUI($plugin)
    {
        foreach(func_get_args() as $plugin) {
            $this->addJsFileToBottom($this->mod->jqueryUI($plugin));
        }
    }

    /**
     * Ability to call and display notifications pushed to the notification system.
     *
     * @author greg <greg@phpdevshell.org>
     * @version 1.1.2
     * @since v3.0.5
     *
     * @date 20150319 (1.1.2) (greg) add JS to bottom instead of top
     * @date 20130610 (1.1.1) (greg) removed dependency on the JS notification system (the theme will deal with it)
     * @date 20120308 (1.1) (greg) added html and mod support
     * @date 20110706 (1.0) (greg) added
     */
    public function outputNotifications()
    {
        $notifications = $this->notif->fetch();
        $mod = $this->mod;

        $html = '';

        if (! empty($notifications)) {
            foreach($notifications as $notification) {
                if (is_array($notification)) {
                    switch ($notification[0]) {
                        case 'info':
                            $title = ___('Info');
                        break;
                        case 'warning':
                            $title = ___('Warning');
                        break;
                        case 'ok':
                            $title = ___('Ok');
                        break;
                        case 'critical':
                            $title = ___('Critical');
                        break;
                        case 'notice':
                            $title = ___('Notice');
                        break;
                        case 'busy':
                            $title = ___('Busy');
                        break;
                        case 'message':
                            $title = ___('Message');
                        break;
                        case 'note':
                            $title = ___('Note');
                        break;
                        default:
                            $title = ___('Info');
                        break;
                    }
                    $html .= $mod->notifications($title, $notification[1], $notification[0]);
                } else {
                    $html .= $mod->notifications(___('Info'), $notification);
                }
            }
            $html = <<<EOJ

                $(function(){
                    $(document).on('PHPDS_start', function() {
                        {$html}
                    });
                });
EOJ;
            $this->template->addJsToBottom($html);

        }
    }

    /**
     * This returns/prints info of the script being executed. Intended to be used by the developer.
     *
     * @version 1.3
     *
     * @date 20110309 (v1.1) (greg) changed to use the pieces repository
     * @date 20110309 (v1.2) (jason) good idea but it wont work as info is not mandatory in controllers.
     * @date 20120308 (v1.3) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed as the info.
     * @return nothing
     * @author Jason Schoeman
     */
    public function info ($information, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->info($information);
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('info', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This method will load given png icon from icon database,
     *
     * @param string Icon name without extention.
     * @param Title of given image.
     * @param int The size folder to look within.
     * @param string If an alternative class must be added to image.
     * @param string File type.
     * @param boolean Default is false, if set true, the heading will return instead of print.
     */
    public function icon($name, $title=false, $size=16, $class='class', $type='.png', $return=true)
    {
        $navigation = $this->navigation->navigation;
        // Create icon dir.
        $script_url = $this->CDN . '/themes/' . $navigation[$this->configuration['m']]['template_folder'] . '/images/icons-' . $size . '/' . $name . $type;
        if (empty ($title))
            $title = '';
        // Create HTML.
        $html = $this->mod->icon($script_url, $class, $title);

        // Return or print to browser.
        if ($return == false) {
            print $html;
        } else if ($return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a warning message regarding the active script. Intended to be used by the developer.
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @param mixed default is log, can be set true, print, return.
     * @return string Warning string.
     * @author Jason Schoeman
     */
    public function warning ($warning, $return = 'print', $log = 'log')
    {
        if ($log === true || $log == 'log') {
            // Log types are : ////////////////
            // 1 = OK /////////////////////////
            // 2 = Warning ////////////////////
            // 3 = Critical ///////////////////
            // 4 = Log-in /////////////////////
            // 5 = Log-out ////////////////////
            ///////////////////////////////////
            $log_type = 2; ////////////////////
            // Log the event //////////////////
            $this->db->logArray[] = array('log_type' => $log_type , 'log_description' => $warning);
        }
        // Create HTML.
        $html = $this->mod->warning($warning);
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('warning', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a ok message regarding the active script. Intended to be used by the developer.
     *
     * @version 1.1
     *
     * @date 20120308 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @param mixed default is log, can be set true, print, return.
     * @return string Ok string.
     * @author Jason Schoeman
     */
    public function ok ($ok, $return = 'print', $log = 'log')
    {
        if ($log === true || $log == 'log') {
            // Log types are : ////////////////
            // 1 = OK /////////////////////////
            // 2 = Warning ////////////////////
            // 3 = Critical ///////////////////
            // 4 = Log-in /////////////////////
            // 5 = Log-out ////////////////////
            ///////////////////////////////////
            $log_type = 1; ////////////////////
            // Log the event //////////////////
            $this->db->logArray[] = array('log_type' => $log_type , 'log_description' => $ok);
        }
        // Create HTML.
        $html = $this->mod->ok($ok);
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('ok', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a error message regarding the active script. Intended to be used by the developer where exceptions are caught.
     *
     * @version 1.1
     * @date 20120308 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @param mixed default is log, can be set true, print, return.
     * @return string Error string.
     * @author Jason Schoeman
     */
    public function error ($error, $return = 'print', $log = 'log')
    {
        if ($log === true || $log == 'log') {
            // Log types are : ////////////////
            // 1 = OK /////////////////////////
            // 2 = Warning ////////////////////
            // 3 = Critical ///////////////////
            // 4 = Log-in /////////////////////
            // 5 = Log-out ////////////////////
            // 6 = Error //////////////////////
            ///////////////////////////////////
            $log_type = 6; ////////////////////
            // Log the event //////////////////
            $this->db->logArray[] = array('log_type' => $log_type , 'log_description' => $error);
        }
        // Create HTML.
        $html = $this->mod->error($error);
        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('error', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a critical message regarding the active script. Intended to be used by the developer.
     *
     * @version 1.1
     * @date 20120308 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @param mixed default is log, can be set true, print, return.
     * @return string Critical string.
     * @author Jason Schoeman
     */
    public function critical ($critical, $return = 'print', $log = 'log', $mail = 'mailadmin')
    {
        $navigation = $this->navigation->navigation;
        if ($log === true || $log == 'log') {
            // Log types are : ////////////////
            // 1 = OK /////////////////////////
            // 2 = Warning ////////////////////
            // 3 = Critical ///////////////////
            // 4 = Log-in /////////////////////
            // 5 = Log-out ////////////////////
            ///////////////////////////////////
            $log_type = 3; ////////////////////
            // Log the event //////////////////
            $this->db->logArray[] = array('log_type' => $log_type , 'log_description' => $critical);
        }
        // Check if we need to email admin.
        if ($this->configuration['email_critical']) {
            // Subject.
            $subject = sprintf(___("CRITICAL ERROR NOTIFICATION %s"), $this->configuration['scripts_name_version']);
            // Message.
            $broke_script = $navigation[$this->configuration['m']]['menu_name'];
            $broken_url = $this->configuration['absolute_url'] . '/index.php?m=' . $this->configuration['m'];
            $message = sprintf(___("Admin,")) . "\r\n\r\n";
            $message .= sprintf(___("THERE WAS A CRITICAL ERROR IN %s:"), $this->configuration['scripts_name_version']) . "\r\n\r\n" . $critical . "\r\n\r\n";
            $message .= sprintf(___("Click on url to access broken script called %s:"), $broke_script) . "\r\n" . $broken_url . "\r\n";
            $message .= sprintf(___("Script error occurred for user : %s"), $this->configuration['user_display_name']);

            if ($mail === true || $mail == 'mailadmin') {
                // Initiate email class.
                $email = $this->factory('mailer');
                // Ok we can now send the critical email message.
                $email->sendmail("{$this->configuration['setting_admin_email']}", $subject, $message);
            }
        }
        // Create HTML.
        $html = $this->mod->critical($critical);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('critical', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a notice of the script being executed. Intended to be used by the developer.
     *
     * @version 1.1
     * @date 20120308 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @return string Notice string.
     * @author Jason Schoeman
     */
    public function notice ($notice, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->notice($notice);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('notice', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a busy of the script being executed. Intended to be used by the developer.
     *
     * @version 1.1
     * @date 20120308 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @return string Busy string.
     * @author Jason Schoeman
     */
    public function busy ($busy, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->busy($busy);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('busy', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a message of the script being executed. Intended to be used by the developer.
     *
     * @version 1.1
     * @date 20120312 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @return string Message string.
     * @author Jason Schoeman
     */
    public function message ($message, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->message($message);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('message', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a note of the script being executed. Intended to be used by the developer.
     *
     * @version 1.1
     * @date 20120312 (v1.1) (greg) switched to notifications queue
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @return string Note string.
     * @author Jason Schoeman
     */
    public function note ($note, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->note($note);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            $this->notif->add(array('note', $html));
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This returns/prints a heading of the script being executed. Intended to be used by the developer.
     *
     * @param string This is the message that will be displayed.
     * @param mixed default is print, can be set true, print, return.
     * @return string Heading string.
     * @author Jason Schoeman
     */
    public function scripthead ($scripthead, $return = 'print')
    {
        // Create HTML.
        $html = $this->mod->scriptHead($scripthead);

        // Return or print to browser.
        if ($return === 'print' || $return == false) {
            //print $html;
            $this->notif->add($html);
        } else if ($return === 'return' || $return == true) {
            return $html;
        }
    }

    /**
     * This creates an the [i] when over with mouse a popup with a message appears, this can be placed anywhere. Intended to be used by the developer.
     *
     * @param string The message to diplay when mouse goes over the [i].
     * @param boolean Sets to print out confirm link instead of return.
     * @author Jason Schoeman
     */
    public function tip ($text, $print = false)
    {
        // This is yet another IE Fix !
        $text_clean = preg_replace('/"/', '', $text);
        $info = $this->mod->toolTip($text_clean);
        if ($print == false) {
            return $info;
        } else {
            print $info;
        }
    }

    /**
     * Login heading messages.
     *
     * @author Jason Schoeman
     */
    public function loginFormHeading($return = false)
    {
        $HTML = '';
        $message= '';
        if (! empty($this->loginMessage))
            $message = $this->notice(___($this->loginMessage), 'return');

        // Create headings for login.
        if (! empty($this->core->haltController)) {
            $HTML .= $this->heading(___('Authentication Required'), 'return');
            $HTML .= $message;
        } else {
            // Get some default settings.
            $settings = $this->db->getSettings(array('login_message'));

            // Check if we have a login message to display.
            if (! empty($settings['login_message'])) {
                $login_message = $this->message(___($settings['login_message']), 'return');
            } else {
                $login_message = '';
            }

            $HTML .= $this->heading(___('Login'), 'return');
            $HTML .= $login_message;
            $HTML .= $message;
        }

        if ($return == false) {
            print $HTML;
        } else {
            return $HTML;
        }
    }

    /**
     * Executes the login.
     *
     * @author Jason Schoeman
     */
    public function loginForm($return = false)
    {
        $HTML = $this->factory('StandardLogin')->loginForm($return);

        if ($return == false) {
            print $HTML;
        } else {
            return $HTML;
        }
    }

    /**
     * Get and return the supposed to run template.
     *
     * @return string if not found, return default.
     * @author Jason Schoeman
     */
    public function getTemplate ()
    {
        $settings['default_template'] = '';

        // Check if the menu has a defined template.
        if (! empty($this->navigation->navigation[$this->configuration['m']]['template_folder'])) {
            $settings['default_template'] = $this->navigation->navigation[$this->configuration['m']]['template_folder'];
        } else {
            // If not check if the gui system settings was set with a default template.
            $settings['default_template'] = $this->configuration['default_template'];
        }

        // Return the complete template.
        return $settings['default_template'];
    }

    /**
     * Gets the correct location of a tpl file, will return full path, can be a view.tpl or view.tpl.php files.
     *
     * @param string $load_view
     * @param string $plugin_override If another plugin is to be used in the directory.
     */
    public function getTpl($load_view=false, $plugin_override=false)
    {
        return $this->core->getTpl($load_view, $plugin_override);
    }

    /**
     * Prints some debug info to the frontend, at the bottom of the page
     *
     */
    public function debugInfo ()
    {
        if ($this->configuration['development']) {
            if (! empty($this->db->countQueries)) {
                $count_queries = $this->db->countQueries;
            } else {
                $count_queries = 0;
            }
            if ($this->configuration['queries_count']) {
                if (!empty($this->core->themeFile)) {
                    $memory_used = memory_get_peak_usage();
                    $time_spent = intval((microtime(true) - $GLOBALS['start_time']) * 1000);
                    print $this->mod->debug($count_queries, number_format($memory_used / 1000000, 2, '.', ' '), $time_spent);
                }
            }
        }
    }

    /**
     * Convert all HTML entities to their applicable characters.
     *
     * @param string $string_to_decode
     * @return string
     */
    public function htmlEntityDecode ($string_to_decode)
    {
        // Decode characters.
        return html_entity_decode($string_to_decode, ENT_QUOTES, $this->configuration['charset']);
    }

    /**
     * This creates a simple confirmation box to ask users input before performing a critical link click.
     *
     * @param string What is the question to be asked in the confirmation box.
     * @return string Javascript popup confirmation box.
     * @author Jason Schoeman
     */
    public function confirmLink ($confirm_what)
    {
        $onclick = "onClick=\"return confirm('$confirm_what')\"";
        return eval('return $onclick;');
    }

    /**
     * This creates a simple confirmation box to ask users input before performing a critical submit.
     *
     * @param string What is the question to be asked in the confirmation box.
     * @return string Javascript popup confirmation box.
     * @author Jason Schoeman
     */
    public function confirmSubmit ($confirm_what)
    {
        $onclick = "onSubmit=\"return confirm('$confirm_what')\"";
        return eval('return $onclick;');
    }

    /**
     * This shows a simple "alert" box which notifies the user about a specified condition.
     *
     * @param string The actual warning message.
     * @return string Javascript popup warning box.
     * @author Don Schoeman
     */
    public function alertSubmit ($alert_msg)
    {
        $onclick = "onSubmit=\"alert('$alert_msg')\"";
        return eval('return $onclick;');
    }

    /**
     * This shows a simple "alert" box which notifies the user about a specified condition.
     *
     * @param string The actual warning message.
     * @return string Javascript popup warning box.
     * @author Don Schoeman
     */
    public function alertLink ($alert_msg)
    {
        $onclick = "onClick=\"alert('$alert_msg')\"";
        return eval('return $onclick;');
    }

    /*
     * print the canonical URL of the current page
     *
     * @date 20160112 (1.0) (greg)
     * @author greg <greg@phpdevshell.org>
     * @since 3.5
     */
    public function outputCanonicalURL()
    {
        $configuration = $this->configuration;
        $url = $this->navigation->currentPath;
        print $this->mod->outputCanonicalURL($configuration['canonical_root'], $url);
    }
}

/**
 * Creates a language tooltip string and prints it out to the template.
 *
 * @param string $info_mark
 */
function tip ($text)
{
    print _($text);
}
/**
 * Creates a language tooltip string inside a text domain and prints it out to the template.
 *
 * @param string $info_mark
 */
function dtip ($text, $domain)
{
    print dgettext($domain, $text);
}
