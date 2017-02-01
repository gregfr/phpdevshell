<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

/**
 * Class PHPDS_core
 */
class PHPDS_core extends PHPDS_dependant
{
    /**
     * Contains controller content.
     * @var string
     */
    public $data;
    /**
     * Used as a bridge between controller to view data.
     * @var mixed
     */
    public $toView;
    /**
     * This variable is used to activate a stop script command, it will be used to end a script immediately while still finishing compiling the template.
     *
     * Usage Example :
     * <code>
     * // This wil make the script stop (not PHPDevShell) while still finishing the template to the end.
     * $this->haltController = array('type'=>'auth','message'=>'The script stopped cause I wanted it to.');
     * </code>
     *
     * @var array
     */
    public $haltController;
    /**
     * Signs the request as an ajax request.
     * @var boolean
     */
    public $ajaxType = null;
    /**
     * The node structure that should be used "theme.php" for normal theme.
     * @var string
     */
    public $themeFile;
    /**
     * Name of the theme folder to use
     * @since v3.1.2
     * @var string
     */
    public $themeName;
    /**
     * Use this to have global available variables throughout scripts. For instance in hooks.
     *
     * @var array
     */
    public $skipLogin = false;

    /**
     * Execute theme structure.
     *
     * @version 1.0.3
     *
     * @data 20161107 (1.0.3) (greg) use currentMenu() to fetch node data
     * @date 20131028 (1.0.1) (greg) fixed a missing "break" on the ajax case
     * @date 20141121 (1.0.2) (greg) fixed a possible "undefined index" when a non-existent menu is requested
     */
    public function setDefaultNodeParams()
    {
        $configuration = $this->configuration;

        $configuration['template_folder'] = $this->core->activeTemplate();
        $this->loadDefaultPluginLanguage();

        $current_node = $this->navigation->currentMenu();
        if ($current_node) {
            // Determine correct menu theme.
            switch ($current_node['menu_type']) {
                // HTML Widget.
                case 9:
                    $this->themeFile = 'widget.php';
                    $this->ajaxType = false;
                    break;
                // HTML Ajax.
                case 10:
                    $this->themeFile = 'ajax.php';
                    $this->ajaxType = false;
                    break;
                // HTML Ajax Lightbox.
                case 11:
                    $this->themeFile = 'lightbox.php';
                    $this->ajaxType = false;
                    break;
                // Raw Ajax (json,xml,etc).
                case 12:
                    $this->themeFile = '';
                    $this->ajaxType = true;
                    break;
                default:
                    $this->ajaxType = PU_isAJAX();
                    $this->themeFile = $this->ajaxType ? '' :  'theme.php';
                    break;
            }
        } else {
            $this->themeFile = 'theme.php';
            $this->ajaxType = false;
        }
        if (!empty($this->themeFile)) {
            $this->loadMods();
        }
    }

    /**
     * Load mods (html snippets for specific theme.)
     * Creates object under $this->mod->...
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath()
     *
     * @date 20120227 V 1.0
     * @author Jason Scheoman
     */
    public function loadMods()
    {
        $configuration = $this->configuration;
        $template_dir = $this->core->themePath();

        if (file_exists($template_dir . 'mods.php')) {
            /** @noinspection PhpIncludeInspection */
            include_once $template_dir . 'mods.php';
            if (class_exists($configuration['template_folder'])) {
                $this->template->mod = $this->factory($configuration['template_folder']);
            } else {
                $this->template->mod = $this->factory('themeMods');
            }
        } else {
            /** @noinspection PhpIncludeInspection */
            include_once $this->core->themePath().'mods.php';
            $this->template->mod = $this->factory('themeMods');
        }
    }

    /**
     * Loads and merges theme with controller.
     *
     * @version 1.1
     *
     * @date 20173101 (1.1) (greg) Using $core->themePath()
     * @date 20120227 V 1.0
     * @author Jason Scheoman
     */
    public function loadTheme()
    {
        $configuration = $this->configuration;

        if (! empty($this->themeName)) {
            $configuration['template_folder'] = $this->themeName;
        }
        $template_dir = $this->core->themePath();

        try {
            ob_start();
            $result = $this->loadFile($template_dir . $this->themeFile, false, true, true, true);
            if (false === $result) {
                $result = $this->loadFile($this->core->themePath().$this->themeFile, false, true, true, true);
            }
            if (false === $result) {
                throw new PHPDS_exception('Unable to find the custom template "' . $this->themeFile . '" in directory "' . $template_dir . '"');
            }
            ob_end_flush();
        } catch (Exception $e) {
            PU_cleanBuffers();
            throw $e;
        }
    }

    /**
     * Run default, custom or no template.
     *
     * @version 2.2
     *
     * @date 20140608 (2.0.2) (greg) correctly handle default route requiring auth
     * @date 20120920 (v2.1.1) (greg) fixed a typo with $url
     * @date 20120312 (v2.1) (greg) added logging of access errors (404 and such)
     * @date 20120223 (v2.0) (jason) rewrite
     * @date 20110308 (v1.2) (greg) allows the new style controller to alter the current template to be used
     * @date20100520 (v1.1) (greg) added merging with modules from the configuration array
     *
     * @author Jason Schoeman
     */
    public function startController ()
    {
        $configuration = $this->configuration;

        // note: $node is valued only if the node exists AND is accessible by the current user
//        $node = $configuration['m'];
        $node = $this->navigation->currentRoute;

        // support for deferred, that is code running before and after a controller
        $deferred = null;
        if (is_a($node, 'iPHPDS_deferred')) {
            /* @var iPHPDS_deferred $deferred */
            $deferred = $node;
            $node = $deferred->reduce();
        }

//        $node = $this->navigation->nodeDefaultID($node);

        $result = false;
        try {
            // check if the user is allowed the requested node
            $check = $this->navigation->checkNode($node, false);
            // if we don't have a proper controller to execute, we need to find out why
            if (empty($check)) {
                if (!$this->navigation->urlAccessError($this->navigation->currentPath, $node)) {
                    $this->stopController();
                }
                throw new PHPDS_controllerNotFoundException($this);
            } else {
                $configuration['m'] = $node;
            }

            $this->setDefaultNodeParams();

            ob_start();
            $this->db->startTransaction();
            $result = $this->executeController();
            $this->db->endTransaction();

            if (empty($this->data)) {
                $this->data = ob_get_clean();
            } else {
                PU_cleanBuffers();
            }
        } catch (Exception $e) {
            if ($deferred) {
                $deferred->failure($result);
                $deferred = null;
            }
            $this->setDefaultNodeParams(); // we still need to set up the defaults

            // pageException() will return if a special exception (such as page not found) has been handled
            $this->pageException($e);
        }

        if ($deferred) {
            $deferred->success($result);
        }

        // Only if we need a theme.
        if (! empty($this->themeFile)) {
            $this->loadTheme();
        } else {
            print $this->data;
        }
    }

    /**
     * Handles the given exception, dealing with some special cases (page not found, unauthorized, etc)
     *
     * @version 1.0
     * @since 3.5
     *
     * @date 20130304 (1.0) (greg) added
     *
     * @author greg <greg@phpdevshell.org>
     *
     * @param Exception $e
     *
     * @throws Exception
     */
    public function pageException(Exception $e)
    {
        PU_cleanBuffers();
        $this->themeFile = '';

        if (is_a($e, 'PHPDS_accessException')) {
            $logger = $this->factory('PHPDS_debug', 'PHPDS_accessException');
                $url = $this->configuration['absolute_url'].$_SERVER['REQUEST_URI'];

            /** @var $e PHPDS_accessException */
            switch ($e->HTTPcode) {
                case 401:
                    $this->db->pushException($e);
                    if (!PU_isAJAX()) {
                        $this->themeFile = 'login.php';
                    }
                    PU_silentHeader("HTTP/1.1 401 Unauthorized");
                    PU_silentHeader("Status: 401");
                        $logger->error('URL unauthorized: '.$url, '401');
                    break;
                case 404:
                    $this->db->pushException($e);
                    if (!PU_isAJAX()) {
                        $this->themeFile = '404.php';
                    }
                    PU_silentHeader("HTTP/1.1 404 Not Found");
                    PU_silentHeader("Status: 404");
                        $logger->error('URL not found: '.$url, '404');
                    break;
                case 403:
                    $this->db->pushException($e);
                    if (!PU_isAJAX()) {
                        $this->themeFile = '403.php';
                    }
                    PU_silentHeader("HTTP/1.1 403 Forbidden");
                    PU_silentHeader("Status: 403");
                        $logger->error('URL forbidden '.$url, '403');
                    break;
                case 418:
                    $this->db->pushException($e);
                    sleep(30); // don't make spambot life live in the fast lane
                    if (!PU_isAJAX()) {
                        $this->themeFile = '418.php';
                    }
                    PU_silentHeader("HTTP/1.1 418 I'm a teapot and you're a spambot");
                    PU_silentHeader("Status: 418");
                        $logger->error('Spambot for '.$url, '418');
                    break;
                default:
                    throw $e;
            }

        } else {
            throw $e;
        }
    }

    /**
     * Executes the controller.
     *
     * // todo: rewrite this in a more concise way
     *
     * @version 1.2.1
     *
     * @date 20140608 (1.2.1) (greg) moved checking of $this->haltController to a new method PHPDS_core::stopController()
     * @date 20131216 (1.2) (greg) correctly handle ajax calls when unauthorized
     * @date 20130304 (1.1) (greg) propagate controller's result, throw exception if something is wrong
     *
     * @author Jason Schoeman
     */
    public function executeController()
    {
        $navigation = $this->navigation->navigation;
        $configuration = $this->configuration;
        $result = false;

        // Are we in demo mode?
        /**
         * @todo Find a better place for this! Its not cool here.
         */
        /*
        if ($configuration['demo_mode'] == true) {
            if ($configuration['user_role'] != $configuration['root_role']) {
                // Show demo mode message for end user.
                $this->template->notice(sprintf(___('%s is currently in a demo mode state, no actual database transactions will occur.'), $configuration['scripts_name_version']));
            } else {
                // Show demo mode message for root user.
                $this->template->notice(sprintf(___('%s is currently in a demo mode state, only Root users are able to save database transactions.'), $configuration['scripts_name_version']));
            }
        }
         *
         */

        // Menu Types:
        // 1. Standard Page from Plugin
        // 2. Link to Existing Node
        // 3. Jump to Existing Node
        // 4. Simple Place Holder Link
        // 5. Load External File
        // 6. External HTTP URL
        // 7. iFrame (Very old fashioned)
        // 8. Automatic Cronjob
        // 9. HTML Ajax Widget (Serves as module inside web page)
        // 10. HTML Ajax (Used for ajax)
        // 11. HTML Ajax Lightbox (Floats overtop of web page)
        // 12. Raw Ajax (json, xml, etc.)
        // Load script to buffer.
        // We need to assign active menu_id.
        $menu_id = $this->navigation->currentMenuID();
        $node = $this->navigation->currentMenu();
        if (!empty($node)) {
            // Determine correct menu action.
            switch ($node['menu_type']) {
                // Plugin File.
                case 1:
                    $menu_case = 1;
                    break;
                // Link.
                case 2:
                    break;
                // Jump.
                case 3:
                    break;
                // External File.
                case 4:
                    $menu_case = 4;
                    break;
                // HTTP URL.
                case 5:
                    $menu_case = 5;
                    break;
                // Placeholder.
                case 6:
                    break;
                // iFrame.
                case 7:
                    $menu_case = 7;
                    break;
                // Cronjob.
                case 8:
                    $menu_case = 8;
                    break;
                // HTML Widget.
                case 9:
                    $menu_case = 9;
                    break;
                // HTML Ajax.
                case 10:
                    $menu_case = 10;
                    break;
                // HTML Ajax Lightbox.
                case 11:
                    $menu_case = 11;
                    break;
                // Raw Ajax (json,xml,etc).
                case 12:
                    $menu_case = 12;
                    break;
                default:
                    // Do case.
                    $menu_case = 1;
                    break;
            }
            ///////////////////////////////////
            // Do further checking on links. //
            ///////////////////////////////////
            if (empty($menu_case)) {
                // So we have some kind of link, we now need to see what kind of link we have.
                // Get menu extended data.
                $extend_id = $node['extend'];
                // Get menu type.
                $linked_node = $this->navigation->node($extend_id);
                if (empty($linked_node)) {
                    // TODO: check for more info
                    throw new PHPDS_extendMenuException($configuration['m'], $extend_id);
//                    throw new PHPDS_controllerNotFoundException($this);
                }
                // We now have the linked menu type and can now work accordingly.
                // Determine correct menu action.
                switch ($linked_node['menu_type']) {
                    // Plugin File.
                    case 1:
                        $menu_case = 1;
                        $menu_id = $extend_id;
                        break;
                    // Link.
                    case 2:
                        $menu_case = 2;
                        $menu_id = $linked_node['extend'];
                        break;
                    // Jump.
                    case 3:
                        $menu_case = 2;
                        $menu_id = $linked_node['extend'];
                        break;
                    // External File.
                    case 4:
                        $menu_case = 4;
                        $menu_id = $extend_id;
                        break;
                    // HTTP URL.
                    case 5:
                        $menu_case = 5;
                        $menu_id = $extend_id;
                        break;
                    // Placeholder.
                    case 6:
                        $menu_case = 2;
                        $menu_id = $linked_node['extend'];
                        break;
                    // iFrame.
                    case 7:
                        $menu_case = 7;
                        $menu_id = $extend_id;
                        break;
                    // Cronjob.
                    case 8:
                        $menu_case = 8;
                        $menu_id = $extend_id;
                        break;
                    // HTML Ajax Widget.
                    case 9:
                        $menu_case = 9;
                        $menu_id = $extend_id;
                        break;
                    // HTML Ajax.
                    case 10:
                        $menu_case = 10;
                        $menu_id = $extend_id;
                        break;
                    // HTML Ajax Lightbox.
                    case 11:
                        $menu_case = 11;
                        $menu_id = $extend_id;
                        break;
                    // Raw Ajax.
                    case 12:
                        $menu_case = 12;
                        $menu_id = $extend_id;
                        break;
                    default:
                        $menu_case = 1;
                        $menu_id = $extend_id;
                        break;
                }
            }
            // Execute repeated menu cases.
            try {
                switch ($menu_case) {
                    // Plugin Script.
                    case 1:
                        $result = $this->loadControllerFile($menu_id);
                        break;
                    // Link, Jump, Placeholder.
                    case 2:
                        // Is this an empty menu item?
                        if (empty($menu_id)) {
                            // Lets take user to the front page as last option.
                            // Get correct frontpage id.
                            ($this->user->isLoggedIn()) ? $menu_id = $configuration['front_page_id_in'] : $menu_id = $configuration['front_page_id'];
                        }
                        $result = $this->loadControllerFile($menu_id);
                        break;
                    // External File.
                    case 4:
                        // Require external file.
                        $result = $this->loadFile($node['menu_link']);
                        if (false == $result) {
                            throw new PHPDS_exception(sprintf(___('File could not be found after trying to execute filename : %s'), $node['menu_link']));
                        }
                        break;
                    // HTTP URL.
                    case 5:
                        // Redirect to external http url.
                        $result = true;
                        $this->template->ok(sprintf(___('You are now being redirected to an external url, %s'), $node['menu_link']), false, false);
                        $this->navigation->redirect($node['menu_link']);
                        break;
                    // iFrame.
                    case 7:
                        $result = true;
                        // Clean up height.
                        $height = preg_replace('/px/i', '', $node['extend']);
                        // Create Iframe.
                        $this->data = $this->template->mod->iFrame($node['menu_link'], $height, '100%');
                        break;
                    // Cronjob.
                    case 8:
                        // Require script.
                        $result = $this->loadControllerFile($menu_id);
                        if ($result) {
                            $time_now = time();
                            // Update last execution.
                            $this->db->invokeQuery('TEMPLATE_cronExecutionLogQuery', $time_now, $menu_id);
                            // Always log manual touched cronjobs.
                            $this->template->ok(sprintf(___('Cronjob %s executed manually.'), $node['menu_name']));
                        }
                        break;
                    // HTML Ajax Widget.
                    case 9:
                        $result = $this->loadControllerFile($menu_id);
                        break;
                    // HTML Ajax.
                    case 10:
                        $result = $this->loadControllerFile($menu_id);
                        break;
                    // HTML Ajax Lightbox.
                    case 11:
                        $result = $this->loadControllerFile($menu_id);
                        break;
                    // HTML Ajax Lightbox.
                    case 12:
                        $result = $this->loadControllerFile($menu_id);
                        break;

                    // something went wrong
                    default:
                        throw new PHPDS_exception('Broken controller node');
                }
            } catch (PHPDS_remoteCallException $e) {
                // special case: we're trying to call a method via ajax but the login page has been substituted
                if (empty($this->haltController)) {
                    throw $e;
                }
            }
        } else {
            throw new PHPDS_controllerNotFoundException($this);
        }

        if (isset($this->haltController)) {
            $this->stopController();
        }

        return $result;
    }

    /**
     * Something went wrong so we need to check $this->haltController for the reason
     *
     * @throws PHPDS_pageException404
     * @throws PHPDS_pageException418
     * @throws PHPDS_securityException
     * @throws PHPDS_securityException403
     */
    public function stopController()
    {
        // Roll back current transaction.
        $this->db->invokeQuery('TEMPLATE_rollbackQuery');
        switch ($this->haltController['type']) {
            case 'auth':
                throw new PHPDS_securityException($this->haltController['message']);
                break;

            case '404':
                throw new PHPDS_pageException404($this->haltController['message'], $this->haltController['type']);
                break;

            case '403':
                throw new PHPDS_securityException403($this->haltController['message'], $this->haltController['type']);
                break;

            case '418':
                throw new PHPDS_pageException418($this->haltController['message'], $this->haltController['type']);
                break;

            default:
                throw new PHPDS_securityException($this->haltController['message'], $this->haltController['type']);
                break;
        }

    }

    /**
     * Will attempt to load controller file from various locations.
     *
     * @version 1.0.4
     *
     * @data 20161107 (1.0.4) (greg) use node() to fetch node data
     * @date 20100917 (v1.0) (Jason)
     * @date 20110308 (v1.0.1) (greg) loadFile returns an exact false when the file is not found
     * @date 20120606 (v1.0.2) (greg) add the "includes/" folder of the plugin in the include path
     * @date 20130304 (1.0.3) (greg) propagate controller's result
     *
     * @author Jason Schoeman
     * @param int $menu_id
     * @param string|boolean $include_model if set, load the model file before the controller is run (either a prefix or true for default "query" prefix) - default is not to
     * @param string|boolean $include_view $include_model if set, run the view file after the controller is run (a prefix) ; default is the "view" prefix)
     *
     * @return mixed controller's result
     */
    public function loadControllerFile($menu_id, $include_model = false, $include_view = 'view')
    {
        $node = $this->navigation->node($menu_id);
        $result = false;

        if (!empty($node)) {
            $plugin_folder = $node['plugin_folder'];
            $old_include_path = PU_addIncludePath($plugin_folder.'/includes/');

            if ($include_model) {
                if ($include_model === true) $include_model = 'query';
                $this->loadFile($plugin_folder . 'models/' . preg_replace("/.php/", '.' . $include_model . '.php', $node['menu_link']));
            }

            $active_dir = $plugin_folder . '%s' . $node['menu_link'];
            $result = $this->loadFile(sprintf($active_dir, 'controllers/'));
            if ($result === false) {
                $result = $this->loadFile(sprintf($active_dir, ''));
            }

            if (is_string($result) && class_exists($result)) {
                /* @var PHPDS_controller $controller */
                $controller = $this->factory($result);
                $result = $controller->run();
            }

            // Load view class.
            if ($include_view && !empty($this->themeFile)) {
                $load_view = preg_replace("/.php/", '.' . $include_view . '.php', $node['menu_link']);
                $view_result = $this->loadFile($plugin_folder . 'views/' . $load_view);
                if (is_string($view_result) && class_exists($view_result)) {
                    $view = $this->factory($view_result);
                    $view->run();
                }
            }

            set_include_path($old_include_path);
        } else {
            $active_dir = '';
        }

        if ($result === false && empty($this->haltController)) {
            throw new PHPDS_exception(sprintf(___('The controller of menu id %d could not be found after trying to execute filename : "%s"'), $menu_id, sprintf($active_dir, '{controllers/}')));
        }
        return $result;
    }

    /**
     * Gets the correct location of a tpl file, will return full path, can be a view.tpl or view.tpl.php files.
     *
     * @version 1.0.1
     *
     * @data 20161107 (1.0.1) (greg) use node() to fetch node data
     *
     * @param string $load_view
     * @param string $plugin_override If another plugin is to be used in the directory.
     */
    public function getTpl($load_view=false, $plugin_override=false)
    {
        $configuration = $this->configuration;
        $navigation = $this->navigation;
        $node = $this->navigation->currentMenu();

        // Menu link.
        if (empty($node['extend'])) {
            $menu_link = $node['menu_link'];
        } else {
            $menu_link = $navigation->navigation[$node['extend']]['menu_link'];
            // Set plugin for this menu item.
            $plugin_extend = $navigation->navigation[$node['extend']]['plugin'];
        }
        // Do template engine.
        $plugin_folder = $configuration['absolute_path'] . 'plugins/' . $this->activePlugin() . '/';
        if (! empty($plugin_override)) {
            if ($plugin_override === true) {
                // leave it, that is use the active plugin as an override
            } else {
                $plugin_folder = $configuration['absolute_path'].'plugins/'.$plugin_override.'/';
            }
        } else if (! empty($plugin_extend)) {
            $plugin_folder = $configuration['absolute_path'] . 'plugins/' . $plugin_extend . '/';
        }

        // Do we have a custom template file?
        if (empty($load_view) && !empty($node['layout'])) {
            $load_view = $node['layout'];
        }
        // Check if we have a custom layout otherwise use default.
        if (empty($load_view)) {
            $tpl_dir = str_replace($menu_link, '%s/' . str_replace('.php', '.tpl', $menu_link), $plugin_folder . $menu_link);
        } else {
            $link = strrchr($menu_link, '/');
            if (empty($link)) {
                $tpl_dir = $plugin_folder . '%s/' . $load_view;
            } else {
                $link = str_replace($link, '', $menu_link);
                $tpl_dir = $plugin_folder . '%s/' . $link . '/' . $load_view;
            }
        }
        // Log to firephp.
        $this->log(__('Loading Template Layout : ', 'core.lang') . $tpl_dir);

        // Return file location.
        if (file_exists(sprintf($tpl_dir, 'views'))) {
            $tpldir = sprintf($tpl_dir, 'views');
            return $tpldir;
        // A custom layout added.
        } else if (file_exists(sprintf($tpl_dir . '.tpl', 'views'))) {
            $tpldir = sprintf($tpl_dir, 'views') . '.tpl';
            return $tpldir;
        // Perhaps we have a php template.
        } else if (file_exists(sprintf($tpl_dir . '.php', 'views'))) {
            return sprintf($tpl_dir . '.php', 'views');
        } else {
            return false;
        }
    }

    /**
     * Check and returns constant if constant is defined or returns normal variable if no constant defined.
     *
     * @param string $is_variable_constant The string to check whether variable or constant.
     * @return string The actual assigned constant value.
     * @author Jason Schoeman
     */
    public function isConstant ($is_variable_constant)
    {
        if (defined($is_variable_constant)) {
            return constant($is_variable_constant);
        } else {
            return $is_variable_constant;
        }
    }

    /**
     * This method will return the correct user time taking DST and users timezone into consideration.
     *
     * @param integer $timestamp Unix timestamp if empty it will return the current users time.
     * @param string $format_type_or_custom User can choose which of the formats to load from the $this->configuration settings, 'default', 'short' or have a custom format.
     * @param string $custom_timezone You can also provide a custom timezone to this method, if false, it will use current users timezone.
     * @return string Will return a formatted date string ex. 1 June 2011 18:05 PM
     * @author Jason Schoeman
     *
     * @version 1.0.1	Converted to OOP
     * @date	2009/05/19
     */
    public function formatTimeDate ($time_stamp, $format_type_or_custom = 'default', $custom_timezone = false)
    {
        $configuration = $this->configuration;
        // Check if we habe an empty time stamp.
        if (empty($time_stamp)) return false;
        // Check if we have a custom timezone.
        if (! empty($custom_timezone)) {
            $timezone = $custom_timezone;
        } else if (! empty($configuration['user_timezone'])) {
            $timezone = $configuration['user_timezone'];
        } else {
            $timezone = $configuration['system_timezone'];
        }

        if ($format_type_or_custom == 'default') {
            $format = $configuration['date_format'];
        } else if ($format_type_or_custom == 'short') {
            $format = $configuration['date_format_short'];
        } else {
            $format = $format_type_or_custom;
        }
        if (phpversion() < '5.2.0') return strftime('%c', $time_stamp);
        try {
            $ut = new DateTime(date('Y-m-d H:i:s', $time_stamp));
            $tz = new DateTimeZone($timezone);
            $ut->setTimezone($tz);
        } catch (Exception $e) {
            // Work around error from old database column.
            $configuration['user_timezone'] = $configuration['system_timezone'];
            return date(DATE_RFC822);
        }

        return $ut->format($format);
    }

    /* Returns the difference in seconds between the currently logged in user's timezone
     * and the server's configured timezone (under General Settings). If the server
     * timezone is 2 hours behind the user timezone, it will return -7200 for example. If
     * the server timezone is 2 hours ahead of the user timezone, it will return 7200.
     *
     * @param integer $custom_timestamp Timestamp to compare dates timezones in the future or past.
     * @return integer The difference between the user's timezone and server timezone (in seconds).
     * @author Don Schoeman
     */
    public function userServerTzDiff ($custom_timestamp = false)
    {
        $configuration = $this->configuration;
        if (empty($custom_timestamp)) {
            $timestamp = $configuration['time'];
        } else {
            $timestamp = $custom_timestamp;
        }
        if (phpversion() < '5.2.0')
            return 0;
        $ut = new DateTime(date('Y-m-d H:i:s', $timestamp));
        $tz = new DateTimeZone($configuration['user_timezone']);
        $ut->setTimezone($tz);
        $user_timezone_sec = $ut->format('Z');
        $tz = new DateTimeZone($configuration['system_timezone']);
        $ut->setTimezone($tz);
        $server_timezone_sec = $ut->format('Z');
        return $server_timezone_sec - $user_timezone_sec;
    }

    /**
     * Function formats locale according to logged in user settings else will default to system.
     *
     * @param boolean $charset Whether the charset should be included in the format.
     * @return string Will return formatted locale.
     * @author Jason Schoeman
     */
    public function formatLocale ($charset = true, $user_language = false, $user_region = false)
    {
        $configuration = $this->configuration;
        if (empty($configuration['charset_format'])) $configuration['charset_format'] = false;
        if (! empty($user_language)) $configuration['user_language'] = $user_language;
        if (! empty($user_region)) $configuration['user_region'] = $user_region;
        if (empty($configuration['user_language'])) $configuration['user_language'] = $configuration['language'];
        if (empty($configuration['user_region'])) $configuration['user_region'] = $configuration['region'];
        if ($charset && ! empty($configuration['charset_format'])) {
            $locale_format = preg_replace('/\{charset\}/', $configuration['charset_format'], $configuration['locale_format']);
            $locale_format = preg_replace('/\{lang\}/', $configuration['user_language'], $locale_format);
            $locale_format = preg_replace('/\{region\}/', $configuration['user_region'], $locale_format);
            $locale_format = preg_replace('/\{charset\}/', $configuration['charset'], $locale_format);
            return $locale_format;
        } else {
            $locale_format = preg_replace('/\{lang\}/', $configuration['user_language'], $configuration['locale_format']);
            $locale_format = preg_replace('/\{region\}/', $configuration['user_region'], $locale_format);
            $locale_format = preg_replace('/\{charset\}/', '', $locale_format);
            return $locale_format;
        }
    }

    /**
     * This methods allows you to load translation by giving their locations and name.
     *
     * @author Jason Schoeman
     * @version 1.0.1
     *
     * @date 20130615 (1.0.1) (greg) missing translation file is not a notice (info) instead of a warning
     *
     * @param $mo_directory string This is the location where language mo file is found.
     * @param $mo_filename string The mo filename the translation is compiled in.
     * @param $textdomain string The actual text domain identifier.
     *
     * @return void
     *
     */
    public function loadTranslation ($mo_directory, $mo_filename, $textdomain)
    {
        $configuration = $this->configuration;
        $bindtextdomain = $configuration['absolute_path'] . $mo_directory;
        $loc_dir = $bindtextdomain . $configuration['locale_dir'] . '/LC_MESSAGES/' . $mo_filename;

        (file_exists($loc_dir)) ? $mo_ok = true : $mo_ok = false;
        if ($mo_ok) {
            $this->log('Found Translation File : ' . $loc_dir);
            bindtextdomain($textdomain, $bindtextdomain);
            bind_textdomain_codeset($textdomain, $configuration['charset']);
            textdomain($textdomain);
        } else {
            $this->debugInstance()->info('MISSING Translation File : ' . $loc_dir);
        }
    }

    /**
     * This method loads the core language array and assigns it to a variable.
     *
     * @author Jason Schoeman
     */
    public function loadCoreLanguage ()
    {
        $this->loadTranslation('language/', 'core.lang.mo', 'core.lang');
    }

    /**
     * This method loads the default menu language array and assigns it to a variable.
     *
     * @author Jason Schoeman
     */
    public function loadMenuLanguage ()
    {
        // Lets loop the installed plugins.
        foreach ($this->db->pluginsInstalled as $installed_plugins_array) {
            $plugin_folder = $installed_plugins_array['plugin_folder'];
            $this->loadTranslation("plugins/$plugin_folder/language/", "$plugin_folder.mo", "$plugin_folder");
        }
    }

    /**
     * This method loads the plugin language with default items and icons array.
     *
     * @author Jason Schoeman
     */
    public function loadDefaultPluginLanguage ()
    {
        $active_plugin = $this->activePlugin();
        textdomain($active_plugin);
    }

    /**
     * Function to return the current running/active plugin.
     *
     * @version 1.0.1
     *
     * @data 20161107 (1.0.1) (greg) use node() to fetch node data
     *
     * @return string
     */
    public function activePlugin ()
    {
        $plugin = $this->navigation->node($this->navigation->currentMenuID, 'plugin');

        return empty($plugin) ? 'PHPDevShell' : $plugin;
    }

    /**
     * Function to return the current running/active template.
     *
     * @version 1.0.1
     *
     * @data 20161107 (1.0.1) (greg) use node() to fetch node data
     *
     * @return string
     */
    public function activeTemplate ()
    {
        $template_folder = $this->navigation->node($this->navigation->currentMenuID, 'template_folder');

        return empty($template_folder) ? $this->configuration['default_template'] : $template_folder;
    }

    /**
     * Convert string unsigned CRC32 value. This is unique and can help predict a entries id beforehand.
     * Use for folder names insuring unique id's.
     *
     * @param string $convert_to_id To convert to integer.
     * @return integer
     * @author Jason Schoeman
     */
    public function nameToId ($convert_to_id)
    {
        return sprintf('%u', crc32($convert_to_id));
    }

    /**
     * Turns any given relative path to the absolute version of the path.
     * @param string $relative_path Provide path like 'test/testpath'
     * @return string
     */
    public function absolutePath ($relative_path)
    {
        $absolute_path = $this->configuration['absolute_path'] . ltrim($relative_path, '/');
        return str_ireplace('//', '/', $absolute_path);
    }

    /**
     * Assumes role of loading files.
     *
     * @date 20100106 (v1.1) (greg) moved from core to PHPDS_core and added a few checks
     *
     * @version 1.1
     * @author jason
     *
     * @param string $path
     * @param boolean $required Should the file be required or else included.
     * @param boolean $relative Is this a relative path, if true, it will be converted to absolute path.
     * @param boolean $once_only Should it be called only once?
     * @param boolean $from_template
     *
     * @return mixed, whatever the file returned when executed or false if it couldn't be found
     */
    public function loadFile($path, $required = false, $relative = true, $once_only = true, $from_template = false)
    {
        // all these variables are defined to be provided to the included/required file, later on

        /** @noinspection PhpUnusedLocalVariableInspection */
        $core = $this->core;
        if ($from_template) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $template = $this->template;
        }
        $configuration = $this->configuration;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $navigation = $this->navigation;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $db = $this->db;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $security = $this->security;

        if (empty($path)) throw new PHPDS_exception('Trying to load a file with an empty path.');

        if ($relative) $path = $configuration['absolute_path'] . $path;

        $this->log('Loading : ' . $path);

        // switch the domain to "user" so the developer can filter to see only its own output
        /** @noinspection PhpUnusedLocalVariableInspection */
        $debug = $this->debugInstance()->domain('user');

        $result = false;

        if (file_exists($path)) {
            if ($required) {
                if (! empty($once_only)) $result = require_once ($path); else $result = require ($path);
            } else {
                if (! empty($once_only)) $result = include_once ($path); else $result = include ($path);
            }
        } else {
            if ($required) throw new PHPDS_exception('Trying to load a non-existant file: "'.$path.'"');
        }

        // revert to the "core" domain since we're out of the developer's code
        $this->debugInstance()->domain('core');

        return $result;
    }

        /**
     * Strip a string from the end of a string.
     * Is there no such function in PHP?
     *
     * @param string $str      The input string.
     * @param string $remove   OPTIONAL string to remove.
     * @deprecated use PU_rightTrim instead
     * @return string the modified string.
     */
    public function rightTrim ($str, $remove = null)
    {
        return PU_rightTrim($str, $remove);
    }

    /**
     * This method simply renames a string to safe unix standards.
     *
     * @param string $name
     * @param string $replace Replace odd characters with what?
     * @deprecated use PU_safeName instead
     * @return string
     */
    public function safeName ($name, $replace = '-')
    {
        return PU_safeName($name, $replace);
    }

    /**
     * Replaces accents with plain text for a given string.
     * @deprecated use PU_replaceAccents instead
     * @param string $string
     */
    public function replaceAccents($string)
    {
        return PU_replaceAccents($string);
    }

    /**
     * This method creates a random string with mixed alphabetic characters.
     *
     * @param integer $length The lenght the string should be.
     * @param boolean $uppercase_only Should the string be uppercase.
     * @deprecated use PU_createRandomString instead
     * @return string Will return required random string.
     * @author Andy Shellam, andy [at] andycc [dot] net
     */
    public function createRandomString ($length = 4, $uppercase_only = false)
    {
        return PU_createRandomString($length, $uppercase_only);
    }

    /**
     * This is a handy little function to strip out a string between two specified pieces of text.
     * This could be used to parse XML text, bbCode, or any other delimited code/text for that matter.
     * Can also return all text with replaced string between tags.
     *
     * @param string $string
     * @param string $start
     * @param string $end
     * @param string $replace Use %s to be replaced with the string between tags.
     * @deprecated use PU_SearchAndReplaceBetween instead
     * @return string
     */
    public function SearchAndReplaceBetween ($string, $start, $end, $replace = '', $replace_char='%')
    {
        return PU_SearchAndReplaceBetween($string, $start, $end, $replace, $replace_char);
    }

        /**
     * This creates a simple confirmation box to ask users input before performing a critical link click.
     *
     * @param string $confirm_what What is the question to be asked in the confirmation box.
     * @return string Javascript popup confirmation box.
     * @author Jason Schoeman
     * @deprecated
     */
    public function confirmLink ($confirm_what)
    {
        return $this->template->confirmLink($confirm_what);
    }

    /**
     * This creates a simple confirmation box to ask users input before performing a critical submit.
     *
     * @param string $confirm_what What is the question to be asked in the confirmation box.
     * @return string Javascript popup confirmation box.
     * @author Jason Schoeman
     * @deprecated use $this->template instead
     */
    public function confirmSubmit ($confirm_what)
    {
        return $this->template->confirmSubmit($confirm_what);
    }

    /**
     * This shows a simple "alert" box which notifies the user about a specified condition.
     *
     * @param string $alert_msg The actual warning message.
     * @return string Javascript popup warning box.
     * @author Don Schoeman
     * @deprecated use $this->template instead
     */
    public function alertSubmit ($alert_msg)
    {
        return $this->template->alertSubmit($alert_msg);
    }

    /**
     * This shows a simple "alert" box which notifies the user about a specified condition.
     *
     * @param string $alert_msg The actual warning message.
     * @return string Javascript popup warning box.
     * @author Don Schoeman
     * @deprecated use $this->template instead
     */
    public function alertLink($alert_msg)
    {
        return $this->template->alertSubmit($alert_msg);
    }

    /**
     * Method is used to wrap the gettext international language conversion tool inside PHPDevShell.
     * Converts text to use gettext PO system.
     *
     * @param string $say_what The string required to output or convert.
     * @param boolean|string $domain Override textdomain that should be looked under for this text string.
     * @return string Will return converted string or same string if not available.
     * @author Jason Schoeman
     */
    public function __ ($say_what, $domain = false)
    {
        return __ ($say_what, $domain);
    }

    /**
     * Will log current configuration data to firephp.
     * @return void
     */
    public function logConfig ()
    {
        $this->log((array) $this->configuration);
    }


    /**
     * Convert a charset name between PHP conventions and MySQL conventions
     *
     * @param string $charset the name of the charset to lookup
     *
     * @return null|string
     */
    public function mangleCharset($charset)
    {
        $configuration = $this->configuration;

        $charsetList = !empty($configuration['charsetList']) ? $configuration['charsetList'] :
            array(
                    // MySQL to PHP
                    'utf8' => 'UTF-8',
                    'latin1' => 'ISO-8859-1',
                    'latin5' => 'ISO-8859-5',
                    'big5' => 'BIG5',
                    'koi8r' => 'KOI8-R',
                    'macroman' => 'MacRoman',
                    'sjis' => 'Shift_JIS',

                    // PHP to MySQL
                    'UTF-8' => 'utf8',
                    'ISO-8859-1' => 'latin1',
                    'ISO-8859-5' => 'latin5',
                    'BIG5' => 'big5',
                    'KOI8-R' => 'koir8r',
                    'MacRoman' => 'macroman',
                    'Shift_JIS' => 'sjis'
            );
        return empty($charsetList[$charset]) ? null : $charsetList[$charset];
    }

    /**
     * Returns the complete url path for the given/current plugin file
     *
     * A relative plugin file is considered from the plugin folder perspective
     *
     * You provide the file relative path as an array (plugin, relative path), or just a string for the current plugin
     *
     * If you provide an complete URL as a string (i.e. with a scheme), it's returned untouched
     *
     * If useCDN is true and the CDN framework parameter is set, it's used
     *
     * @version 1.1.1
     *
     * @date 20170121 (1.1.1) (greg) trust on template to get the top web path
     * @date 20151217 (1.1) (greg) added support for complete URLs and CDN
     *
     * @param array|string $object relative URL, complete path, or array (plugin, relative path)
     * @param bool $useCDN should we try to use the framework CDN parameter?
     *
     * @return bool|string a web path or a complete URL (or false in case we didn't understand the parameter)
     */
    public function webPath($object, $useCDN = false)
    {
        $path = false;

        if (is_array($object)) {

            $plugins = $this->db->pluginsInstalled;

            if (!isset($plugins[$object[0]])) {
                return false;
            }
            // TODO: use a centralized way to find out the plugin folder
            $folder = $plugins[$object[0]]['plugin_folder'];
            $path = '/plugins/'.$folder.'/'.$object[1];
        } elseif (is_string($object)) {
            $components = parse_url($object);
            if (!empty($components['scheme'])) {
                return $object;
            }
            if (substr($object, 0, 1) == '/') {
                $path = $object;
            } else {
                if (file_exists(BASEPATH.$object)) {
                    $path = '/'.$object;
                } else {
                    $path = '/plugins/' . $this->activePlugin() . '/' . $object;
                }
            }
        }
        return $this->template->outputAbsoluteURL('return').$path;
    }

    /**
     * Returns the path to use to access files from a theme
     *
     * @version 1.0
     *
     * @date 20170130 (1.0) (greg) Added
     *
     * @param string $theme The theme name (optional, defaults to node's theme)
     */
    public function themePath($theme = '')
    {
        if (empty($theme)) {
            $theme = $this->configuration['template_folder'];
        }
        $path = "themes/$theme/";
        return $path;
    }
}
